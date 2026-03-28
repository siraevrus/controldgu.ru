<?php

namespace App\Http\Controllers;

use App\Http\Requests\AcknowledgeAlertRequest;
use App\Models\Alert;
use App\Models\AlertEvent;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AlertController extends Controller
{
    public function index(): View
    {
        $alerts = Alert::query()
            ->with(['dgu'])
            ->where('status', Alert::STATUS_OPEN)
            ->latest('triggered_at')
            ->paginate(20);

        return view('alerts.index', compact('alerts'));
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
