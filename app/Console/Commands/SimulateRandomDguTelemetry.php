<?php

namespace App\Console\Commands;

use App\Models\Dgu;
use App\Services\Telemetry\TelemetryIngestService;
use App\Support\SimulatedTelemetryValues;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('dgu:simulate-random-telemetry {--dgu= : public_id одной ДГУ} {--all : записать для всех ДГУ}')]
#[Description('Принимает пакет телеметрии со случайными значениями (локально, без HTTP)')]
class SimulateRandomDguTelemetry extends Command
{
    public function handle(TelemetryIngestService $ingest): int
    {
        $all = (bool) $this->option('all');
        $publicId = $this->option('dgu');

        if ($all && $publicId) {
            $this->error('Укажите либо --all, либо --dgu=, не оба.');

            return self::FAILURE;
        }

        $query = Dgu::query()->orderBy('serial_number');
        if ($publicId) {
            $query->where('public_id', $publicId);
        }

        $dgus = $query->get();
        if ($dgus->isEmpty()) {
            $this->error('ДГУ не найдены. Сначала migrate --seed или проверьте --dgu=');

            return self::FAILURE;
        }

        if (! $all && ! $publicId && $dgus->count() > 1) {
            $this->warn('Несколько ДГУ в базе: записываю только первую по серийному номеру. Используйте --all или --dgu=public_id.');
            $dgus = $dgus->take(1);
        }

        $recordedAt = now();
        $n = 0;
        foreach ($dgus as $dgu) {
            $values = SimulatedTelemetryValues::randomForDgu($dgu);
            $ingest->ingest($dgu, $values, $recordedAt);
            $n++;
        }

        $this->info("Записано случайных снимков: {$n} (время записи {$recordedAt->toDateTimeString()}).");

        return self::SUCCESS;
    }
}
