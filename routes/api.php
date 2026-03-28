<?php

use App\Http\Controllers\Api\TelemetryIngestController;
use Illuminate\Support\Facades\Route;

Route::post('v1/dgus/{public_id}/telemetry', [TelemetryIngestController::class, 'store'])
    ->middleware('throttle:120,1');
