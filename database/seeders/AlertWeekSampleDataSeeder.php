<?php

namespace Database\Seeders;

use App\Models\Alert;
use App\Models\Dgu;
use App\Models\User;
use App\Support\TelemetryParameters;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Тревоги с датой срабатывания за последние 10 календарных дней (часть закрыта).
 */
class AlertWeekSampleDataSeeder extends Seeder
{
    public function run(): void
    {
        Alert::query()->delete();

        $dgus = Dgu::query()->orderBy('id')->limit(8)->get();
        if ($dgus->isEmpty()) {
            return;
        }

        $admin = User::query()->role('admin')->first();
        $catalog = TelemetryParameters::catalog();

        $tz = config('app.timezone', 'UTC');
        $today = Carbon::now($tz)->startOfDay();

        $rows = [
            // день -6 … 0 и ещё -9 … -7 в конце массива (порядок важен для i % 8 и теста ingest)
            [-6, 8, 15, 'coolant_temp_c', 102.0, true],
            [-6, 11, 40, 'voltage_v', 435.0, false],
            [-5, 9, 5, 'oil_pressure_bar', 2.1, true],
            [-5, 14, 22, 'current_a', 215.0, true],
            [-5, 18, 50, 'fuel_level_pct', 6.0, false],
            [-4, 7, 12, 'frequency_hz', 48.9, true],
            [-4, 10, 30, 'engine_rpm', 1555.0, false],
            [-4, 16, 0, 'power_current_kw', 118.0, true],
            [-3, 6, 45, 'coolant_temp_c', 98.0, true],
            [-3, 12, 10, 'voltage_v', 372.0, false],
            [-3, 19, 35, 'fuel_consumption_lph', 38.0, true],
            [-2, 8, 20, 'oil_pressure_bar', 2.3, false],
            [-2, 13, 55, 'current_a', 208.0, true],
            [-1, 9, 0, 'coolant_temp_c', 96.5, true],
            [-1, 11, 15, 'frequency_hz', 51.2, false],
            [-1, 17, 40, 'fuel_level_pct', 8.0, false],
            [0, 6, 30, 'voltage_v', 428.0, true],
            [0, 10, 0, 'engine_rpm', 1530.0, false],
            [0, 14, 45, 'power_current_kw', 112.0, false],
            [-9, 8, 0, 'power_current_kw', 115.0, true],
            [-9, 14, 30, 'coolant_temp_c', 100.0, true],
            [-8, 9, 15, 'oil_pressure_bar', 2.0, true],
            [-8, 15, 45, 'fuel_level_pct', 7.0, false],
            [-7, 10, 0, 'frequency_hz', 48.8, true],
            [-7, 16, 20, 'current_a', 212.0, true],
            [-7, 19, 50, 'voltage_v', 430.0, false],
        ];

        foreach ($rows as $i => [$dayOffset, $h, $m, $slug, $value, $ack]) {
            $dgu = $dgus[$i % $dgus->count()];
            $triggeredAt = $today->copy()->addDays($dayOffset)->setTime($h, $m, 0);

            $label = $catalog[$slug]['label'] ?? $slug;
            Alert::query()->create([
                'dgu_id' => $dgu->id,
                'parameter_slug' => $slug,
                'status' => $ack ? Alert::STATUS_ACKNOWLEDGED : Alert::STATUS_OPEN,
                'title' => "Отклонение: {$label}",
                'message' => "Значение {$value} вне допустимого диапазона (тестовые данные).",
                'triggered_value' => (string) $value,
                'triggered_at' => $triggeredAt,
                'acknowledged_at' => $ack && $admin ? $triggeredAt->copy()->addHours(2) : null,
                'acknowledged_by_id' => $ack && $admin ? $admin->id : null,
                'acknowledge_comment' => $ack ? 'Проверено, параметры в норме после регулировки.' : null,
            ]);
        }
    }
}
