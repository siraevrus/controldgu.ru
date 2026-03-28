<?php

namespace App\Services\Telemetry;

use App\Mail\AlertOpenedMail;
use App\Models\Alert;
use App\Models\AlertEvent;
use App\Models\AppNotification;
use App\Models\Dgu;
use App\Models\GlobalThreshold;
use App\Models\SystemLog;
use App\Models\TelemetrySnapshot;
use App\Models\User;
use App\Support\TelemetryParameters;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TelemetryIngestService
{
    public function ingest(Dgu $dgu, array $values, ?\DateTimeInterface $recordedAt = null): TelemetrySnapshot
    {
        $recordedAt = $recordedAt ? Carbon::parse($recordedAt) : now();

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

            SystemLog::query()->create([
                'source' => 'alerts',
                'level' => 'warning',
                'message' => "Открыта тревога #{$alert->id} ({$slug}) — ДГУ {$dgu->serial_number}",
                'context' => [
                    'alert_id' => $alert->id,
                    'dgu_id' => $dgu->id,
                    'value' => $num,
                ],
                'created_at' => now(),
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

            try {
                Mail::to($user->email)->send(new AlertOpenedMail($alert, $dgu));
            } catch (\Throwable $e) {
                Log::error('alert.mail_failed', [
                    'alert_id' => $alert->id,
                    'user_id' => $user->id,
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }
}
