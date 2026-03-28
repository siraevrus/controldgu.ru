<?php

namespace App\Http\Controllers;

use App\Models\Dgu;
use Illuminate\View\View;

class MapController extends Controller
{
    public function index(): View
    {
        $staleMinutes = (int) config('telemetry.stale_minutes', 10);
        $longOfflineHours = (int) config('telemetry.long_offline_hours', 12);

        $dgus = Dgu::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('serial_number')
            ->get();

        $markers = $dgus->map(function (Dgu $d) use ($staleMinutes, $longOfflineHours) {
            $fresh = $d->isTelemetryFresh($staleMinutes);
            $longOff = $d->last_telemetry_at === null
                || $d->last_telemetry_at->lessThan(now()->subHours($longOfflineHours));

            if ($d->is_manually_disabled || $longOff) {
                $color = 'red';
            } elseif (! $fresh) {
                $color = 'orange';
            } elseif ($d->operational_state === 'running') {
                $color = 'green';
            } else {
                $color = 'blue';
            }

            return [
                'public_id' => $d->public_id,
                'name' => $d->name,
                'serial_number' => $d->serial_number,
                'lat' => (float) $d->latitude,
                'lng' => (float) $d->longitude,
                'color' => $color,
                'fresh' => $fresh,
                'long_offline' => $longOff,
                'manually_disabled' => $d->is_manually_disabled,
                'operational_state' => $d->operational_state,
                'url' => route('dgus.show', $d, absolute: false),
            ];
        })->values()->all();

        return view('map.index', [
            'markers' => $markers,
            'staleMinutes' => $staleMinutes,
            'longOfflineHours' => $longOfflineHours,
        ]);
    }
}
