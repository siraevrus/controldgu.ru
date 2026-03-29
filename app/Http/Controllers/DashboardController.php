<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\AppNotification;
use App\Models\AuditLog;
use App\Models\Dgu;
use App\Models\TelemetrySnapshot;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $staleMinutes = (int) config('telemetry.stale_minutes', 10);
        $longOfflineHours = (int) config('telemetry.long_offline_hours', 12);
        $tz = config('app.timezone', 'UTC');

        $total = Dgu::query()->count();
        $fresh = Dgu::query()->whereNotNull('last_telemetry_at')
            ->where('last_telemetry_at', '>=', now()->subMinutes($staleMinutes))
            ->count();
        $inactive = max(0, $total - $fresh);
        $openAlerts = Alert::query()->open()->count();

        $longOffline = Dgu::query()->where(function ($q) use ($longOfflineHours): void {
            $q->whereNull('last_telemetry_at')
                ->orWhere('last_telemetry_at', '<', now()->subHours($longOfflineHours));
        })->count();

        $running = Dgu::query()->where('operational_state', 'running')->count();
        $stopped = Dgu::query()->where('operational_state', 'stopped')->count();
        $manualDisabled = Dgu::query()->where('is_manually_disabled', true)->count();

        $withoutCoords = Dgu::query()
            ->where(function ($q): void {
                $q->whereNull('latitude')->orWhereNull('longitude');
            })
            ->count();

        $alertsLast24h = Alert::query()
            ->where('triggered_at', '>=', now()->subDay())
            ->count();

        $snapshotsLastHour = TelemetrySnapshot::query()
            ->where('recorded_at', '>=', now()->subHour())
            ->count();

        $regionsTop = Dgu::query()
            ->selectRaw('region, COUNT(*) as c')
            ->whereNotNull('region')
            ->where('region', '!=', '')
            ->groupBy('region')
            ->orderByDesc('c')
            ->limit(5)
            ->get();

        $recentOpenAlerts = Alert::query()
            ->open()
            ->with('dgu')
            ->latest('triggered_at')
            ->limit(10)
            ->get();

        $unreadNotifications = AppNotification::query()
            ->where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->count();

        $recentAuditLogs = collect();
        if ($request->user()->hasRole('admin')) {
            $recentAuditLogs = AuditLog::query()
                ->with('user')
                ->latest('created_at')
                ->limit(5)
                ->get();
        }

        return view('dashboard', [
            'kpiTotal' => $total,
            'kpiFresh' => $fresh,
            'kpiInactive' => $inactive,
            'kpiOpenAlerts' => $openAlerts,
            'staleMinutes' => $staleMinutes,
            'longOfflineHours' => $longOfflineHours,
            'kpiLongOffline' => $longOffline,
            'kpiRunning' => $running,
            'kpiStopped' => $stopped,
            'kpiManualDisabled' => $manualDisabled,
            'kpiWithoutCoords' => $withoutCoords,
            'kpiAlertsLast24h' => $alertsLast24h,
            'kpiSnapshotsLastHour' => $snapshotsLastHour,
            'regionsTop' => $regionsTop,
            'recentOpenAlerts' => $recentOpenAlerts,
            'unreadNotifications' => $unreadNotifications,
            'recentAuditLogs' => $recentAuditLogs,
            'tz' => $tz,
        ]);
    }
}
