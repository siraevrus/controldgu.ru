<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Dgu;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $staleMinutes = (int) config('telemetry.stale_minutes', 10);

        $total = Dgu::query()->count();
        $fresh = Dgu::query()->whereNotNull('last_telemetry_at')
            ->where('last_telemetry_at', '>=', now()->subMinutes($staleMinutes))
            ->count();
        $inactive = max(0, $total - $fresh);
        $openAlerts = Alert::query()->open()->count();

        return view('dashboard', [
            'kpiTotal' => $total,
            'kpiFresh' => $fresh,
            'kpiInactive' => $inactive,
            'kpiOpenAlerts' => $openAlerts,
            'staleMinutes' => $staleMinutes,
        ]);
    }
}
