<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateDguOperationalStateRequest;
use App\Models\Dgu;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;

class DguOperationalController extends Controller
{
    public function update(UpdateDguOperationalStateRequest $request, Dgu $dgu): RedirectResponse
    {
        $this->authorize('controlOperational', $dgu);

        $state = $request->validated('state');

        if ($dgu->operational_state === $state) {
            return redirect()
                ->route('dgus.show', $dgu)
                ->with('status', 'dgu-operational-unchanged');
        }

        $previous = $dgu->operational_state;
        $dgu->update([
            'operational_state' => $state,
            'operational_state_changed_at' => now(),
        ]);

        $action = $state === 'running' ? 'dgu.operational.start' : 'dgu.operational.stop';
        Audit::record($action, Dgu::class, $dgu->id, [
            'serial_number' => $dgu->serial_number,
            'previous_state' => $previous,
            'new_state' => $state,
        ]);

        return redirect()
            ->route('dgus.show', $dgu)
            ->with('status', 'dgu-operational-updated');
    }
}
