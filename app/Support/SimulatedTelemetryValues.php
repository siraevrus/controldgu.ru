<?php

namespace App\Support;

use App\Models\Dgu;

/**
 * Случайные значения телеметрии для эмуляции (в пределах, согласованных с демо-сидером и порогами).
 */
final class SimulatedTelemetryValues
{
    public static function randomForDgu(Dgu $dgu): array
    {
        $P = TelemetryParameters::class;
        $running = $dgu->operational_state === 'running' && ! $dgu->is_manually_disabled;

        if (! $running) {
            return [
                $P::POWER_CURRENT_KW => self::rf(0, 2.5, 1),
                $P::CURRENT_A => self::rf(5, 25, 1),
                $P::VOLTAGE_V => self::rf(374, 390, 1),
                $P::FREQUENCY_HZ => self::rf(49.8, 50.05, 2),
                $P::ENGINE_RPM => random_int(80, 160),
                $P::FUEL_CONSUMPTION_LPH => self::rf(0, 0.7, 2),
                $P::COOLANT_TEMP_C => self::rf(36, 48, 1),
                $P::OIL_PRESSURE_BAR => self::rf(2.25, 2.95, 2),
                $P::FUEL_LEVEL_PCT => self::rf(25, 98, 1),
                $P::RUNTIME_HOURS => self::rf(500 + ($dgu->id % 400), 1500 + ($dgu->id % 200), 2),
                $P::AGGREGATE_STATUS => random_int(1, 100) <= 90 ? 'ok' : 'warning',
            ];
        }

        $nominal = max(30.0, (float) $dgu->nominal_power_kw);
        $load = random_int(18, 96) / 100;
        $powerRaw = $nominal * $load;
        $power = round(min(104.0, max(8.0, $powerRaw)), 1);

        $current = round(min(198.0, max(25.0, $power / max(8.0, $nominal) * 175 + self::jitter(12))), 1);
        $voltage = round(min(419.0, max(381.0, self::rf(385, 415, 1))), 1);
        $frequency = round(min(50.45, max(49.55, self::rf(49.6, 50.4, 2))), 2);
        $rpm = min(1517, max(1482, random_int(1485, 1512)));
        $fuelLph = round(min(33.5, max(1.0, 6 + ($power / 104) * 26 + self::jitter(2.5))), 2);
        $coolant = round(min(93.0, max(61.0, 72 + 16 * $load + self::jitter(3.5))), 1);
        $oil = round(min(4.95, max(2.55, 3.4 + 0.9 * $load + self::jitter(0.25))), 2);
        $fuelPct = round(min(98.0, max(14.0, self::rf(35, 96, 1))), 1);
        $runtime = round(800 + ($dgu->id % 400) + random_int(0, 300) / 10 + $load * 50, 2);

        $aggregate = random_int(1, 100) <= 88 ? 'ok' : (random_int(1, 100) <= 50 ? 'warning' : 'ok');

        return [
            $P::POWER_CURRENT_KW => $power,
            $P::CURRENT_A => $current,
            $P::VOLTAGE_V => $voltage,
            $P::FREQUENCY_HZ => $frequency,
            $P::ENGINE_RPM => $rpm,
            $P::FUEL_CONSUMPTION_LPH => $fuelLph,
            $P::COOLANT_TEMP_C => $coolant,
            $P::OIL_PRESSURE_BAR => $oil,
            $P::FUEL_LEVEL_PCT => $fuelPct,
            $P::RUNTIME_HOURS => $runtime,
            $P::AGGREGATE_STATUS => $aggregate,
        ];
    }

    private static function rf(float $min, float $max, int $decimals): float
    {
        $m = 10 ** $decimals;
        $lo = (int) round($min * $m);
        $hi = (int) round($max * $m);

        return round(random_int(min($lo, $hi), max($lo, $hi)) / $m, $decimals);
    }

    private static function jitter(float $amplitude): float
    {
        if ($amplitude <= 0) {
            return 0.0;
        }

        return (random_int(0, 20_000) / 10_000 - 1) * $amplitude;
    }
}
