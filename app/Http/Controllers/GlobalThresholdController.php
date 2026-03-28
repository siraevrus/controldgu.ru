<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateGlobalThresholdRequest;
use App\Models\GlobalThreshold;
use App\Support\Audit;
use App\Support\TelemetryParameters;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GlobalThresholdController extends Controller
{
    public function index(): View
    {
        $rows = GlobalThreshold::query()->orderBy('parameter_slug')->get();
        $catalog = TelemetryParameters::catalog();

        return view('settings.thresholds.index', compact('rows', 'catalog'));
    }

    public function edit(GlobalThreshold $threshold): View
    {
        $catalog = TelemetryParameters::catalog();

        return view('settings.thresholds.edit', [
            'threshold' => $threshold,
            'label' => $catalog[$threshold->parameter_slug]['label'] ?? $threshold->parameter_slug,
            'unit' => $catalog[$threshold->parameter_slug]['unit'] ?? '',
        ]);
    }

    public function update(UpdateGlobalThresholdRequest $request, GlobalThreshold $threshold): RedirectResponse
    {
        $threshold->update([
            'min_value' => $request->input('min_value'),
            'max_value' => $request->input('max_value'),
            'ignore_max' => $request->boolean('ignore_max'),
            'is_active' => $request->boolean('is_active'),
        ]);

        Audit::record('threshold.updated', GlobalThreshold::class, $threshold->id, [
            'parameter_slug' => $threshold->parameter_slug,
        ]);

        return redirect()->route('settings.thresholds.index')->with('status', 'threshold-updated');
    }
}
