<?php

return [
    'stale_minutes' => (int) env('TELEMETRY_STALE_MINUTES', 10),
    'long_offline_hours' => (int) env('TELEMETRY_LONG_OFFLINE_HOURS', 12),
    'retention_days' => (int) env('TELEMETRY_RETENTION_DAYS', 14),

    /**
     * Планировщик Laravel: каждую минуту — случайная телеметрия для всех ДГУ.
     * Включайте только локально / на стенде (php artisan schedule:work или системный cron).
     */
    'random_simulator_schedule' => (bool) env('TELEMETRY_RANDOM_SIMULATOR_SCHEDULE', false),
];
