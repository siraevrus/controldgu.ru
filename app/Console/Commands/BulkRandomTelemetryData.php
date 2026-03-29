<?php

namespace App\Console\Commands;

use App\Models\Dgu;
use App\Models\TelemetrySnapshot;
use App\Services\Telemetry\TelemetryIngestService;
use App\Support\SimulatedTelemetryValues;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('dgu:bulk-random-data {--per-dgu=1 : Сколько случайных снимков на каждую ДГУ} {--within-hours=0 : Разнести время снимков случайно за последние N часов (0 = все в «сейчас», при per-dgu>1 — за последние 2 мин)}')]
#[Description('Для каждой ДГУ записывает случайные пакеты телеметрии (через ingest)')]
class BulkRandomTelemetryData extends Command
{
    public function handle(TelemetryIngestService $ingest): int
    {
        $perDgu = max(1, min(500, (int) $this->option('per-dgu')));
        $withinHours = max(0, min(24 * 30, (int) $this->option('within-hours')));

        $dgus = Dgu::query()->orderBy('id')->get();
        if ($dgus->isEmpty()) {
            $this->error('В базе нет ДГУ.');

            return self::FAILURE;
        }

        $effectiveWindowHours = $withinHours;
        if ($effectiveWindowHours === 0 && $perDgu > 1) {
            $effectiveWindowHours = 1;
            $this->warn('per-dgu > 1: время снимков разносится случайно за последний час (задайте --within-hours явно для другого окна).');
        }

        $total = 0;
        foreach ($dgus as $dgu) {
            $times = $this->buildSortedTimes($perDgu, $effectiveWindowHours);
            foreach ($times as $at) {
                $values = SimulatedTelemetryValues::randomForDgu($dgu);
                $ingest->ingest($dgu, $values, $at);
                $total++;
            }
        }

        $this->syncLastTelemetryAt();

        $this->info("Записано случайных снимков: {$total} (ДГУ: {$dgus->count()}, по {$perDgu} шт.).");

        return self::SUCCESS;
    }

    /**
     * @return list<Carbon>
     */
    protected function buildSortedTimes(int $count, int $withinHours): array
    {
        $now = now();

        if ($withinHours === 0) {
            return [$now];
        }

        $maxSec = max(1, $withinHours * 3600);
        $stamps = [];
        for ($i = 0; $i < $count; $i++) {
            $stamps[] = $now->copy()->subSeconds(random_int(1, $maxSec));
        }
        usort($stamps, fn (Carbon $a, Carbon $b) => $a <=> $b);

        return $stamps;
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
}
