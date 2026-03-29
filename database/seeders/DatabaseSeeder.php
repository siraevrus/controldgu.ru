<?php

namespace Database\Seeders;

use App\Models\GlobalThreshold;
use App\Models\User;
use App\Support\TelemetryParameters;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::query()->firstOrCreate(['name' => 'operator', 'guard_name' => 'web']);

        $adminRole = Role::query()->where('name', 'admin')->where('guard_name', 'web')->firstOrFail();

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

        $this->call(DguTestDataSeeder::class);
        $this->call(AlertWeekSampleDataSeeder::class);
        $this->call(NotificationSampleDataSeeder::class);
    }
}
