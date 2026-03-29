<?php

namespace App\Console\Commands;

use App\Models\Dgu;
use App\Models\TelemetrySnapshot;
use App\Support\TelemetryParameters;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('dgu:seed-week-telemetry {--keep : Не удалять существующие снимки перед заливкой} {--days=7 : Число суток назад от текущего часа}')]
#[Description('Почасовые снимки телеметрии за N суток для всех ДГУ (разнообразные значения в пределах порогов)')]
class SeedDguWeekTelemetry extends Command
{
    public function handle(): int
    {
        if (! $this->option('keep')) {
            TelemetrySnapshot::query()->delete();
            $this->info('Существующие снимки телеметрии удалены.');
        }

        $dgus = Dgu::query()->orderBy('id')->get();
        if ($dgus->isEmpty()) {
            $this->error('Нет ДГУ в базе. Выполните migrate --seed.');

            return self::FAILURE;
        }

        $days = max(1, min(90, (int) $this->option('days')));
        $start = now()->subDays($days)->startOfHour();
        $end = now()->startOfHour();

        $totalRows = 0;
        $now = now();

        foreach ($dgus as $dgu) {
            $phase = (crc32($dgu->serial_number) % 628) / 100.0;
            $noiseSeed = crc32($dgu->public_id);
            $runtimeBase = 800 + ($dgu->id % 400) + ($noiseSeed % 200) / 10.0;
            $fuelLevel = 88 + ($dgu->id % 9) + ($noiseSeed % 7) / 10.0;
            $rows = [];
            $t = $start->copy();

            while ($t->lte($end)) {
                if ($this->shouldSkipHour($dgu, $t, $noiseSeed)) {
                    $t->addHour();

                    continue;
                }

                $elapsedHours = (int) $start->diffInHours($t);
                $values = $this->buildValues($dgu, $t, $phase, $noiseSeed, $elapsedHours, $runtimeBase, $fuelLevel);
                $rows[] = [
                    'dgu_id' => $dgu->id,
                    'recorded_at' => $t->copy(),
                    'values' => json_encode($values, JSON_THROW_ON_ERROR),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $totalRows++;
                $t->addHour();
            }

            foreach (array_chunk($rows, 250) as $chunk) {
                DB::table('telemetry_snapshots')->insert($chunk);
            }
        }

        $this->syncLastTelemetryAt();

        $this->info("Записано снимков: {$totalRows} для {$dgus->count()} ДГУ ({$days} сут., с {$start->toDateTimeString()} по {$end->toDateTimeString()}, шаг 1 ч, часть точек пропущена как «нет связи»).");

        return self::SUCCESS;
    }

    /**
     * Редкие «дыры» в связи (~1,8% часов).
     */
    protected function shouldSkipHour(Dgu $dgu, Carbon $t, int $noiseSeed): bool
    {
        $h = ($noiseSeed + $dgu->id * 17 + (int) ($t->timestamp / 3600)) % 1000;

        return $h < 18;
    }

    protected function syncLastTelemetryAt(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            foreach (Dgu::query()->pluck('id') as $id) {
                $mx = TelemetrySnapshot::query()->where('dgu_id', $id)->max('recorded_at');
                if ($mx) {
                    Dgu::query()->whereKey($id)->update(['last_telemetry_at' => $mx]);
                }
            }

            return;
        }

        DB::statement(<<<'SQL'
            UPDATE dgus d
            INNER JOIN (
                SELECT dgu_id, MAX(recorded_at) AS mx
                FROM telemetry_snapshots
                GROUP BY dgu_id
            ) s ON s.dgu_id = d.id
            SET d.last_telemetry_at = s.mx
            SQL);
    }

    /**
     * @param  float  $runtimeBase  стартовая наработка (моточасы)
     */
    protected function buildValues(
        Dgu $dgu,
        Carbon $t,
        float $phase,
        int $noiseSeed,
        int $elapsedHours,
        float $runtimeBase,
        float $fuelLevel,
    ): array {
        $P = TelemetryParameters::class;
        $nominal = max(30.0, (float) $dgu->nominal_power_kw);
        $running = $dgu->operational_state === 'running' && ! $dgu->is_manually_disabled;

        $wobble = fn (int $salt, float $amp) => $this->pseudoNoise($noiseSeed, $elapsedHours, $salt) * $amp;

        if (! $running) {
            return [
                $P::POWER_CURRENT_KW => round(max(0, $wobble(1, 2.5)), 1),
                $P::CURRENT_A => round(max(0, 15 + $wobble(2, 10)), 1),
                $P::VOLTAGE_V => round(382 + $wobble(3, 8), 1),
                $P::FREQUENCY_HZ => round(49.92 + $wobble(4, 0.12), 2),
                $P::ENGINE_RPM => (int) max(0, round(120 + $wobble(5, 40))),
                $P::FUEL_CONSUMPTION_LPH => round(max(0, 0.3 + $wobble(6, 0.4)), 2),
                $P::COOLANT_TEMP_C => round(42 + $wobble(7, 6), 1),
                $P::OIL_PRESSURE_BAR => round(2.6 + $wobble(8, 0.35), 2),
                $P::FUEL_LEVEL_PCT => round(max(12, min(99, $fuelLevel - $elapsedHours * 0.02)), 1),
                $P::RUNTIME_HOURS => round($runtimeBase + $elapsedHours * 0.02, 2),
                $P::AGGREGATE_STATUS => 'ok',
            ];
        }

        $dayAngle = ($t->dayOfWeek * 24 + $t->hour) / (7 * 24) * 2 * M_PI;
        $hourAngle = $t->hour / 24 * 2 * M_PI;
        $load = 0.22
            + 0.45 * (0.5 + 0.5 * sin($dayAngle + $phase))
            + 0.22 * (0.5 + 0.5 * sin($hourAngle * 2 + $phase * 0.7));
        $load += $wobble(10, 0.08);
        $load = max(0.18, min(0.96, $load));

        $powerRaw = $nominal * $load;
        $power = round(min(104.0, max(8.0, $powerRaw)), 1);

        $current = round($power / max(8.0, $nominal) * 175 + $wobble(11, 12), 1);
        $current = max(25.0, min(198.0, $current));

        $voltage = round(392 + 12 * sin($hourAngle + $phase) + $wobble(12, 4), 1);
        $voltage = max(381.0, min(419.0, $voltage));

        $frequency = round(50.0 + 0.25 * sin($dayAngle * 3) + $wobble(13, 0.08), 2);
        $frequency = max(49.55, min(50.45, $frequency));

        $rpm = (int) round(1500 + 14 * sin($hourAngle * 4 + $phase) + $wobble(14, 6));
        $rpm = max(1482, min(1517, $rpm));

        $fuelLph = round(6 + ($power / 104) * 26 + $wobble(15, 2.5), 2);
        $fuelLph = max(1.0, min(33.5, $fuelLph));

        $coolant = round(72 + 16 * $load + $wobble(16, 3.5), 1);
        $coolant = max(61.0, min(93.0, $coolant));

        $oil = round(3.4 + 0.9 * $load + $wobble(17, 0.25), 2);
        $oil = max(2.55, min(4.95, $oil));

        $fuelPct = $fuelLevel - $elapsedHours * 0.035 - $fuelLph * 0.01;
        $fuelPct = max(14.0, min(98.0, $fuelPct));

        $runtime = $runtimeBase + $elapsedHours * 0.85 * $load + abs($wobble(18, 0.4));

        $statusRoll = ($noiseSeed + $elapsedHours * 13) % 100;
        $aggregate = $statusRoll < 88 ? 'ok' : ($statusRoll < 97 ? 'warning' : 'ok');

        return [
            $P::POWER_CURRENT_KW => $power,
            $P::CURRENT_A => $current,
            $P::VOLTAGE_V => $voltage,
            $P::FREQUENCY_HZ => $frequency,
            $P::ENGINE_RPM => $rpm,
            $P::FUEL_CONSUMPTION_LPH => $fuelLph,
            $P::COOLANT_TEMP_C => $coolant,
            $P::OIL_PRESSURE_BAR => $oil,
            $P::FUEL_LEVEL_PCT => round($fuelPct, 1),
            $P::RUNTIME_HOURS => round($runtime, 2),
            $P::AGGREGATE_STATUS => $aggregate,
        ];
    }

    protected function pseudoNoise(int $seed, int $elapsedHours, int $salt): float
    {
        $x = ($seed ^ ($elapsedHours * 2654435761) ^ ($salt * 1597334677)) & 0x7FFFFFFF;

        return ($x % 10000) / 10000.0 * 2 - 1.0;
    }
}
