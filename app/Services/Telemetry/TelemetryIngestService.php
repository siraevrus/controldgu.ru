<?php

namespace App\Services\Telemetry;

use App\Models\Alert;
use App\Models\AlertEvent;
use App\Models\AppNotification;
use App\Models\Dgu;
use App\Models\GlobalThreshold;
use App\Models\TelemetrySnapshot;
use App\Models\User;
use App\Support\TelemetryParameters;
use Illuminate\Support\Facades\DB;

class TelemetryIngestService
{
    public function ingest(Dgu $dgu, array $values, ?\DateTimeInterface $recordedAt = null): TelemetrySnapshot
    {
        $recordedAt = $recordedAt ? \Illuminate\Support\Carbon::parse($recordedAt) : now();

        return DB::transaction(function () use ($dgu, $values, $recordedAt) {
            $snapshot = TelemetrySnapshot::query()->create([
                'dgu_id' => $dgu->id,
                'recorded_at' => $recordedAt,
                'values' => $values,
            ]);

            $dgu->update(['last_telemetry_at' => $recordedAt]);

            $this->evaluateThresholds($dgu, $values);

            return $snapshot;
        });
    }

    protected function evaluateThresholds(Dgu $dgu, array $values): void
    {
        $thresholds = GlobalThreshold::query()->where('is_active', true)->get()->keyBy('parameter_slug');

        foreach (TelemetryParameters::numericSlugs() as $slug) {
            if (! array_key_exists($slug, $values)) {
                continue;
            }

            $threshold = $thresholds->get($slug);
            if (! $threshold instanceof GlobalThreshold) {
                continue;
            }

            $raw = $values[$slug];
            if (! is_numeric($raw)) {
                continue;
            }

            $num = (float) $raw;

            $violates = false;
            if ($threshold->min_value !== null && $num < (float) $threshold->min_value) {
                $violates = true;
            }
            if (! $threshold->ignore_max && $threshold->max_value !== null && $num > (float) $threshold->max_value) {
                $violates = true;
            }

            if (! $violates) {
                continue;
            }

            $open = Alert::query()
                ->where('dgu_id', $dgu->id)
                ->where('parameter_slug', $slug)
                ->where('status', Alert::STATUS_OPEN)
                ->exists();

            if ($open) {
                continue;
            }

            $catalog = TelemetryParameters::catalog();
            $label = $catalog[$slug]['label'] ?? $slug;

            $alert = Alert::query()->create([
                'dgu_id' => $dgu->id,
                'parameter_slug' => $slug,
                'status' => Alert::STATUS_OPEN,
                'title' => "Отклонение: {$label}",
                'message' => "Значение {$num} вне допустимого диапазона.",
                'triggered_value' => (string) $num,
                'triggered_at' => now(),
            ]);

            AlertEvent::query()->create([
                'alert_id' => $alert->id,
                'type' => 'created',
                'user_id' => null,
                'body' => 'Тревога создана автоматически',
                'meta' => ['value' => $num],
            ]);

            $this->notifyAdminsAboutAlert($dgu, $alert);
        }
    }

    protected function notifyAdminsAboutAlert(Dgu $dgu, Alert $alert): void
    {
        $admins = User::role('admin')->get();
        foreach ($admins as $user) {
            AppNotification::query()->create([
                'user_id' => $user->id,
                'dgu_id' => $dgu->id,
                'type' => 'alert_opened',
                'title' => $alert->title,
                'body' => $dgu->name ?? $dgu->serial_number,
                'data' => ['alert_id' => $alert->id],
            ]);
        }
    }
}
