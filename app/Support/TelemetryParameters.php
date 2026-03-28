<?php

namespace App\Support;

/**
 * Стабильные slug телеметрии (API / БД). Подписи для UI.
 */
final class TelemetryParameters
{
    public const POWER_CURRENT_KW = 'power_current_kw';

    public const CURRENT_A = 'current_a';

    public const VOLTAGE_V = 'voltage_v';

    public const FREQUENCY_HZ = 'frequency_hz';

    public const ENGINE_RPM = 'engine_rpm';

    public const FUEL_CONSUMPTION_LPH = 'fuel_consumption_lph';

    public const COOLANT_TEMP_C = 'coolant_temp_c';

    public const OIL_PRESSURE_BAR = 'oil_pressure_bar';

    public const FUEL_LEVEL_PCT = 'fuel_level_pct';

    public const RUNTIME_HOURS = 'runtime_hours';

    /** Сводный статус агрегата: ok | warning | alarm */
    public const AGGREGATE_STATUS = 'aggregate_status';

    /**
     * @return array<string, array{label: string, unit: string}>
     */
    public static function catalog(): array
    {
        return [
            self::POWER_CURRENT_KW => ['label' => 'Мощность текущая', 'unit' => 'кВт'],
            self::CURRENT_A => ['label' => 'Ток', 'unit' => 'А'],
            self::VOLTAGE_V => ['label' => 'Напряжение', 'unit' => 'В'],
            self::FREQUENCY_HZ => ['label' => 'Частота', 'unit' => 'Гц'],
            self::ENGINE_RPM => ['label' => 'Обороты двигателя', 'unit' => 'об/мин'],
            self::FUEL_CONSUMPTION_LPH => ['label' => 'Расход топлива', 'unit' => 'л/ч'],
            self::COOLANT_TEMP_C => ['label' => 'Температура ОЖ', 'unit' => '°C'],
            self::OIL_PRESSURE_BAR => ['label' => 'Давление масла', 'unit' => 'бар'],
            self::FUEL_LEVEL_PCT => ['label' => 'Уровень топлива', 'unit' => '%'],
            self::RUNTIME_HOURS => ['label' => 'Наработка', 'unit' => 'моточасы'],
            self::AGGREGATE_STATUS => ['label' => 'Статус', 'unit' => ''],
        ];
    }

    /** Числовые параметры для порогов и графиков */
    public static function numericSlugs(): array
    {
        return [
            self::POWER_CURRENT_KW,
            self::CURRENT_A,
            self::VOLTAGE_V,
            self::FREQUENCY_HZ,
            self::ENGINE_RPM,
            self::FUEL_CONSUMPTION_LPH,
            self::COOLANT_TEMP_C,
            self::OIL_PRESSURE_BAR,
            self::FUEL_LEVEL_PCT,
            self::RUNTIME_HOURS,
        ];
    }
}
