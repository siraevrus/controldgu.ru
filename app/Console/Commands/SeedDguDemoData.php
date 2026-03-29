<?php

namespace App\Console\Commands;

use Database\Seeders\AlertWeekSampleDataSeeder;
use Database\Seeders\NotificationSampleDataSeeder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

#[Signature('dgu:seed-demo {--days=14 : Суток почасовой телеметрии для всех ДГУ} {--keep : Не удалять существующие снимки телеметрии перед заливкой} {--telemetry-only : Только телеметрия, без тревог и уведомлений}')]
#[Description('Тестовый пакет: телеметрия за N суток + демо-тревоги и уведомления за 14 дней (если не указан --telemetry-only)')]
class SeedDguDemoData extends Command
{
    public function handle(): int
    {
        $days = max(1, min(90, (int) $this->option('days')));

        $params = ['--days' => (string) $days];
        if ($this->option('keep')) {
            $params['--keep'] = true;
        }

        $code = Artisan::call('dgu:seed-week-telemetry', $params);
        $this->output->write(Artisan::output());
        if ($code !== 0) {
            return $code;
        }

        if ($this->option('telemetry-only')) {
            $this->info('Готово: только телеметрия.');

            return self::SUCCESS;
        }

        $this->call(AlertWeekSampleDataSeeder::class);
        $this->call(NotificationSampleDataSeeder::class);

        $this->info('Готово: телеметрия за '.$days.' сут. + демо-тревоги и уведомления (14 дней).');

        return self::SUCCESS;
    }
}
