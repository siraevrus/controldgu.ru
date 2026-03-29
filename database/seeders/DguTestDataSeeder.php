<?php

namespace Database\Seeders;

use App\Models\Dgu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DguTestDataSeeder extends Seeder
{
    /**
     * Удаляет все ДГУ и создаёт 25 записей заново (1 — с токеном приёма telemetry-ingest-token, 24 — каталог).
     */
    public function run(): void
    {
        Dgu::query()->delete();

        $plainToken = 'telemetry-ingest-token';

        Dgu::query()->create([
            'name' => 'Стационар «Восточная подстанция»',
            'serial_number' => 'AD100C-MSK-7VKM41',
            'address' => 'Москва, Промзона Север-2, стр. 7',
            'latitude' => 55.751244,
            'longitude' => 37.618423,
            'responsible_name' => 'Иванов И.И.',
            'contact_phone' => '+79990001122',
            'nominal_power_kw' => 100,
            'model_name' => 'АД100С-Т400-50-1РPШ-G1-О0-У3',
            'region' => 'Москва',
            'tags' => ['primary-ingest'],
            'operational_state' => 'running',
            'telemetry_token_hash' => Dgu::hashTelemetryToken($plainToken),
        ]);

        $models = [
            'АД100С-Т400-50-1РPШ-G1-О0-У3',
            'АД200С-Т400-50-2РPШ-G2-О0-У3',
            'АД60С-Т400-50-1РPШ',
            'АД30С-Т400-50-1РPШ',
        ];

        $bases = [
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
        ];

        for ($k = 1; $k <= 24; $k++) {
            $serial = sprintf('КДГ-НК7-%03d', $k);
            [$city, $lat0, $lng0] = $bases[($k - 1) % count($bases)];
            $lat = $lat0 + (fake()->randomFloat(4, -0.08, 0.08));
            $lng = $lng0 + (fake()->randomFloat(4, -0.08, 0.08));

            $lastAt = match ($k % 5) {
                0 => null,
                1, 2 => now()->subMinutes(fake()->numberBetween(0, 8)),
                default => now()->subMinutes(fake()->numberBetween(15, 900)),
            };

            Dgu::query()->create([
                'serial_number' => $serial,
                'name' => sprintf('Площадка №%d — %s', $k, $city),
                'address' => $city.', '.fake()->streetAddress(),
                'latitude' => round($lat, 7),
                'longitude' => round($lng, 7),
                'responsible_name' => fake('ru_RU')->name(),
                'contact_phone' => '+79'.fake()->numerify('#########'),
                'nominal_power_kw' => fake()->randomElement([30, 60, 100, 200]),
                'model_name' => fake()->randomElement($models),
                'region' => $city,
                'tags' => ['fleet-catalog'],
                'is_manually_disabled' => $k % 11 === 0,
                'operational_state' => $k % 2 === 0 ? 'running' : 'stopped',
                'telemetry_token_hash' => Dgu::hashTelemetryToken(Str::random(48)),
                'last_telemetry_at' => $lastAt,
            ]);
        }
    }
}
