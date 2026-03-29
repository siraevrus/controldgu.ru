<?php

namespace App\Console\Commands;

use App\Models\Dgu;
use App\Support\RussianRegions;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('dgu:generate-random-stations {--count=30 : Сколько ДГУ создать} {--fresh : Удалить все существующие ДГУ перед созданием}')]
#[Description('Создаёт случайные ДГУ (станции) с координатами в РФ — без Faker, подходит для prod с --no-dev')]
class GenerateRandomDguStations extends Command
{
    /** @var list<array{0: string, 1: float, 2: float}> */
    protected array $anchors = [
        ['Москва', 55.751244, 37.618423],
        ['Санкт-Петербург', 59.934280, 30.335098],
        ['Казань', 55.796127, 49.106414],
        ['Екатеринбург', 56.838011, 60.597474],
        ['Новосибирск', 55.030204, 82.920430],
        ['Нижний Новгород', 56.326797, 44.006516],
        ['Челябинск', 55.164442, 61.436843],
        ['Самара', 53.200066, 50.150085],
        ['Ростов-на-Дону', 47.235714, 39.701505],
        ['Уфа', 54.734768, 55.957838],
        ['Краснодар', 45.035470, 38.975313],
        ['Воронеж', 51.672834, 39.184296],
        ['Пермь', 58.010455, 56.229443],
        ['Волгоград', 48.719390, 44.501831],
        ['Красноярск', 56.009387, 92.852086],
        ['Тюмень', 57.153033, 65.534328],
        ['Иркутск', 52.286387, 104.280660],
        ['Хабаровск', 48.480229, 135.071917],
        ['Владивосток', 43.115542, 131.885494],
        ['Мурманск', 68.958524, 33.082660],
    ];

    /** @var list<string> */
    protected array $models = [
        'АД100С-Т400-50-1РPШ-G1-О0-У3',
        'АД200С-Т400-50-2РPШ-G2-О0-У3',
        'АД60С-Т400-50-1РPШ',
        'АД30С-Т400-50-1РPШ',
        'АД150С-Т400-50-1РPШ',
    ];

    public function handle(): int
    {
        $count = max(1, min(500, (int) $this->option('count')));

        if ($this->option('fresh')) {
            Dgu::query()->delete();
            $this->warn('Все ДГУ удалены (--fresh).');
        }

        $regions = RussianRegions::names();
        $usedSerials = Dgu::query()->pluck('serial_number')->flip()->all();

        $created = 0;
        for ($i = 0; $i < $count; $i++) {
            $serial = $this->uniqueSerial($usedSerials);
            $usedSerials[$serial] = true;

            [$city, $lat0, $lng0] = $this->anchors[array_rand($this->anchors)];
            $region = $regions[array_rand($regions)];
            $lat = round($lat0 + $this->jitterDeg(0.12), 7);
            $lng = round($lng0 + $this->jitterDeg(0.12), 7);

            $running = random_int(0, 1) === 1;
            $disabled = random_int(0, 14) === 0;

            Dgu::query()->create([
                'serial_number' => $serial,
                'name' => sprintf('Станция %s — %s', $serial, $city),
                'address' => $region.', площадка №'.random_int(1, 999),
                'latitude' => $lat,
                'longitude' => $lng,
                'responsible_name' => 'Ответственный '.random_int(100, 999),
                'contact_phone' => '+79'.str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT),
                'nominal_power_kw' => [30, 60, 100, 150, 200][random_int(0, 4)],
                'model_name' => $this->models[array_rand($this->models)],
                'region' => $region,
                'tags' => ['random-station'],
                'is_manually_disabled' => $disabled,
                'operational_state' => $running ? 'running' : 'stopped',
                'telemetry_token_hash' => Dgu::hashTelemetryToken(Str::random(48)),
                'last_telemetry_at' => null,
            ]);
            $created++;
        }

        $this->info("Создано ДГУ: {$created} (всего в базе: ".Dgu::query()->count().').');

        return self::SUCCESS;
    }

    /**
     * @param  array<string, true>  $used
     */
    protected function uniqueSerial(array &$used): string
    {
        for ($attempt = 0; $attempt < 50; $attempt++) {
            $serial = 'RNG-'.strtoupper(bin2hex(random_bytes(4))).'-'.strtoupper(bin2hex(random_bytes(2)));
            if (! isset($used[$serial])) {
                return $serial;
            }
        }

        return 'RNG-'.Str::uuid()->toString();
    }

    protected function jitterDeg(float $max): float
    {
        return (random_int(-10000, 10000) / 10000.0) * $max * 2;
    }
}
