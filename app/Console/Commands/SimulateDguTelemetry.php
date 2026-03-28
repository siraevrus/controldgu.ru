<?php

namespace App\Console\Commands;

use App\Models\Dgu;
use App\Services\Telemetry\TelemetryIngestService;
use App\Support\TelemetryParameters;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('dgu:simulate-telemetry {--dgu= : public_id ДГУ}')]
#[Description('Отправляет один пакет демо-телеметрии (для локального теста без HTTP)')]
class SimulateDguTelemetry extends Command
{
    public function handle(TelemetryIngestService $ingest): int
    {
        $publicId = $this->option('dgu');
        $query = Dgu::query();
        if ($publicId) {
            $query->where('public_id', $publicId);
        }
        $dgu = $query->first();
        if (! $dgu) {
            $this->error('ДГУ не найдена. Сначала php artisan migrate --seed');

            return self::FAILURE;
        }

        $values = [
            TelemetryParameters::POWER_CURRENT_KW => 85.5,
            TelemetryParameters::CURRENT_A => 150.2,
            TelemetryParameters::VOLTAGE_V => 400.1,
            TelemetryParameters::FREQUENCY_HZ => 50.02,
            TelemetryParameters::ENGINE_RPM => 1498,
            TelemetryParameters::FUEL_CONSUMPTION_LPH => 25.3,
            TelemetryParameters::COOLANT_TEMP_C => 78.5,
            TelemetryParameters::OIL_PRESSURE_BAR => 3.8,
            TelemetryParameters::FUEL_LEVEL_PCT => 75,
            TelemetryParameters::RUNTIME_HOURS => 1245.6,
            TelemetryParameters::AGGREGATE_STATUS => 'ok',
        ];

        $snapshot = $ingest->ingest($dgu, $values, now());
        $this->info("Снимок #{$snapshot->id} записан для ДГУ {$dgu->public_id}");

        return self::SUCCESS;
    }
}
