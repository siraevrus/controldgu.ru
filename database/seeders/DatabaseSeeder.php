<?php

namespace Database\Seeders;

use App\Models\Dgu;
use App\Models\GlobalThreshold;
use App\Models\User;
use App\Support\TelemetryParameters;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $adminRole = Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@controldgu.local'],
            [
                'name' => 'Администратор',
                'phone' => '+70000000000',
                'job_title' => 'Инженер',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles([$adminRole]);

        $thresholds = [
            TelemetryParameters::POWER_CURRENT_KW => [0, 110, false],
            TelemetryParameters::CURRENT_A => [0, 200, false],
            TelemetryParameters::VOLTAGE_V => [380, 420, false],
            TelemetryParameters::FREQUENCY_HZ => [49.5, 50.5, false],
            TelemetryParameters::ENGINE_RPM => [1480, 1520, false],
            TelemetryParameters::FUEL_CONSUMPTION_LPH => [0, 35, false],
            TelemetryParameters::COOLANT_TEMP_C => [60, 95, false],
            TelemetryParameters::OIL_PRESSURE_BAR => [2.5, 5.0, false],
            TelemetryParameters::FUEL_LEVEL_PCT => [10, 100, false],
            TelemetryParameters::RUNTIME_HOURS => [0, null, true],
        ];

        foreach ($thresholds as $slug => [$min, $max, $ignoreMax]) {
            GlobalThreshold::query()->updateOrCreate(
                ['parameter_slug' => $slug],
                [
                    'min_value' => $min,
                    'max_value' => $max,
                    'ignore_max' => $ignoreMax,
                    'is_active' => true,
                ]
            );
        }

        $plainToken = 'demo-ingest-token';

        Dgu::query()->firstOrCreate(
            ['serial_number' => 'AD100C-DEMO-001'],
            [
                'name' => 'АД100С демо',
                'address' => 'Москва, демо-площадка',
                'latitude' => 55.751244,
                'longitude' => 37.618423,
                'responsible_name' => 'Иванов И.И.',
                'contact_phone' => '+79990001122',
                'nominal_power_kw' => 100,
                'model_name' => 'АД100С-Т400-50-1РPШ-G1-О0-У3',
                'region' => 'Москва',
                'tags' => ['demo'],
                'operational_state' => 'running',
                'telemetry_token_hash' => Dgu::hashTelemetryToken($plainToken),
            ]
        );
    }
}
