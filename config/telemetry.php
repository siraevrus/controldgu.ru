<?php

return [
    'stale_minutes' => (int) env('TELEMETRY_STALE_MINUTES', 10),
    'long_offline_hours' => (int) env('TELEMETRY_LONG_OFFLINE_HOURS', 12),
    'retention_days' => (int) env('TELEMETRY_RETENTION_DAYS', 14),
];
