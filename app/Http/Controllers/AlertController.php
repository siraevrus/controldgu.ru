<?php

namespace App\Http\Controllers;

use App\Http\Requests\AcknowledgeAlertRequest;
use App\Models\Alert;
use App\Models\AlertEvent;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AlertController extends Controller
{
    public function index(Request $request): View
    {
        $tz = config('app.timezone', 'UTC');
        $now = Carbon::now($tz);

        if ($request->filled('from') || $request->filled('to')) {
            $fromRaw = $request->date('from');
            $toRaw = $request->date('to');
            if ($fromRaw && $toRaw) {
                $rangeStart = $fromRaw->copy()->timezone($tz)->startOfDay();
                $rangeEnd = $toRaw->copy()->timezone($tz)->endOfDay();
            } elseif ($fromRaw) {
                $rangeStart = $fromRaw->copy()->timezone($tz)->startOfDay();
                $rangeEnd = $now->copy()->endOfDay();
            } else {
                $rangeStart = $now->copy()->subDays(9)->startOfDay();
                $rangeEnd = $toRaw->copy()->timezone($tz)->endOfDay();
            }
            if ($rangeStart->greaterThan($rangeEnd)) {
                [$rangeStart, $rangeEnd] = [$rangeEnd->copy()->startOfDay(), $rangeStart->copy()->endOfDay()];
            }
            if ($rangeStart->isSameDay($rangeEnd)) {
                $filterDescription = 'За день '.$rangeStart->translatedFormat('d.m.Y');
            } else {
                $filterDescription = 'С '.$rangeStart->translatedFormat('d.m.Y').' по '.$rangeEnd->translatedFormat('d.m.Y');
            }
            $filterFrom = $rangeStart->format('Y-m-d');
            $filterTo = $rangeEnd->format('Y-m-d');
        } else {
            $rangeStart = $now->copy()->subDays(9)->startOfDay();
            $rangeEnd = $now->copy()->endOfDay();
            $filterDescription = 'Последние 10 дней';
            $filterFrom = $rangeStart->format('Y-m-d');
            $filterTo = $rangeEnd->format('Y-m-d');
        }

        $alerts = Alert::query()
            ->with(['dgu'])
            ->whereBetween('triggered_at', [$rangeStart, $rangeEnd])
            ->latest('triggered_at')
            ->paginate(20)
            ->withQueryString();

        return view('alerts.index', compact(
            'alerts',
            'filterDescription',
            'filterFrom',
            'filterTo',
        ));
    }

    public function show(Alert $alert): View
    {
        $alert->load(['dgu', 'events.user', 'acknowledgedBy']);

        return view('alerts.show', compact('alert'));
    }

    public function acknowledge(AcknowledgeAlertRequest $request, Alert $alert): RedirectResponse
    {
        if ($alert->status !== Alert::STATUS_OPEN) {
            return redirect()->route('alerts.show', $alert)->withErrors(['comment' => 'Тревога уже закрыта.']);
        }

        $user = $request->user();

        DB::transaction(function () use ($alert, $request, $user): void {
            $alert->update([
                'status' => Alert::STATUS_ACKNOWLEDGED,
                'acknowledged_at' => now(),
                'acknowledged_by_id' => $user->id,
                'acknowledge_comment' => $request->validated('comment'),
            ]);

            AlertEvent::query()->create([
                'alert_id' => $alert->id,
                'type' => 'acknowledged',
                'user_id' => $user->id,
                'body' => $request->validated('comment'),
                'meta' => null,
            ]);
        });

        Audit::record('alert.acknowledged', Alert::class, $alert->id, [
            'dgu_id' => $alert->dgu_id,
            'parameter_slug' => $alert->parameter_slug,
        ]);

        return redirect()->route('alerts.index')->with('status', 'alert-acknowledged');
    }
}
