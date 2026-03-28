<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDguRequest;
use App\Http\Requests\UpdateDguRequest;
use App\Models\Dgu;
use App\Models\GlobalThreshold;
use App\Models\TelemetrySnapshot;
use App\Support\Audit;
use App\Support\TelemetryParameters;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DguController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Dgu::class, 'dgu');
    }

    public function index(Request $request): View
    {
        $staleMinutes = (int) config('telemetry.stale_minutes', 10);

        $query = Dgu::query()->orderBy('serial_number');

        if ($serial = $request->string('serial')->toString()) {
            $query->where('serial_number', 'like', '%'.$serial.'%');
        }
        if ($region = $request->string('region')->toString()) {
            $query->where('region', 'like', '%'.$region.'%');
        }
        if ($model = $request->string('model_name')->toString()) {
            $query->where('model_name', 'like', '%'.$model.'%');
        }

        $link = $request->string('link')->toString();
        if ($link === 'fresh') {
            $query->whereNotNull('last_telemetry_at')
                ->where('last_telemetry_at', '>=', now()->subMinutes($staleMinutes));
        } elseif ($link === 'stale') {
            $query->where(function ($q) use ($staleMinutes): void {
                $q->whereNull('last_telemetry_at')
                    ->orWhere('last_telemetry_at', '<', now()->subMinutes($staleMinutes));
            });
        }

        $dgus = $query->paginate(15)->withQueryString();

        return view('dgus.index', [
            'dgus' => $dgus,
            'filters' => $request->only(['serial', 'region', 'model_name', 'link']),
            'staleMinutes' => $staleMinutes,
        ]);
    }

    public function create(): View
    {
        return view('dgus.create', ['dgu' => new \App\Models\Dgu(['operational_state' => 'stopped'])]);
    }

    public function store(StoreDguRequest $request): RedirectResponse
    {
        $plainToken = Str::random(48);
        $data = $request->validated();
        $data["tags"] = $this->parseTagsFromInput($request->input("tags_input"));
        $data['telemetry_token_hash'] = Dgu::hashTelemetryToken($plainToken);
        $data['is_manually_disabled'] = $request->boolean('is_manually_disabled');

        $dgu = DB::transaction(function () use ($data) {
            return Dgu::query()->create($data);
        });

        Audit::record('dgu.created', Dgu::class, $dgu->id, [
            'serial_number' => $dgu->serial_number,
            'public_id' => $dgu->public_id,
        ]);

        return redirect()
            ->route('dgus.show', $dgu)
            ->with('telemetry_plain_token', $plainToken)
            ->with('status', 'dgu-created');
    }

    public function show(Dgu $dgu): View
    {
        $latest = TelemetrySnapshot::query()
            ->where('dgu_id', $dgu->id)
            ->latest('recorded_at')
            ->first();

        $thresholds = GlobalThreshold::query()
            ->where('is_active', true)
            ->get()
            ->keyBy('parameter_slug');

        $catalog = TelemetryParameters::catalog();

        $snapshots = TelemetrySnapshot::query()
            ->where('dgu_id', $dgu->id)
            ->where('recorded_at', '>=', now()->subHours(24))
            ->orderBy('recorded_at')
            ->get(['recorded_at', 'values']);

        $labels = $snapshots->map(fn (TelemetrySnapshot $s) => $s->recorded_at->timezone(config('app.timezone'))->format('d.m H:i'))->all();

        $chartSlugs = [
            TelemetryParameters::POWER_CURRENT_KW,
            TelemetryParameters::VOLTAGE_V,
            TelemetryParameters::COOLANT_TEMP_C,
        ];
        $chartDatasets = [];
        $colors = ['#2563eb', '#16a34a', '#dc2626'];
        $i = 0;
        foreach ($chartSlugs as $slug) {
            $data = $snapshots->map(function (TelemetrySnapshot $s) use ($slug) {
                $v = $s->values[$slug] ?? null;

                return is_numeric($v) ? round((float) $v, 2) : null;
            })->all();
            if (collect($data)->contains(fn ($v) => $v !== null)) {
                $chartDatasets[] = [
                    'label' => $catalog[$slug]['label'] ?? $slug,
                    'data' => $data,
                    'borderColor' => $colors[$i % count($colors)],
                    'tension' => 0.2,
                    'spanGaps' => true,
                ];
                $i++;
            }
        }

        $staleMinutes = (int) config('telemetry.stale_minutes', 10);
        $longOfflineHours = (int) config('telemetry.long_offline_hours', 12);

        return view('dgus.show', [
            'dgu' => $dgu,
            'latest' => $latest,
            'thresholds' => $thresholds,
            'catalog' => $catalog,
            'chartLabels' => $labels,
            'chartDatasets' => $chartDatasets,
            'staleMinutes' => $staleMinutes,
            'longOfflineHours' => $longOfflineHours,
        ]);
    }

    public function edit(Dgu $dgu): View
    {
        return view('dgus.edit', ['dgu' => $dgu]);
    }

    public function update(UpdateDguRequest $request, Dgu $dgu): RedirectResponse
    {
        $data = $request->validated();
        $data["tags"] = $this->parseTagsFromInput($request->input("tags_input"));
        $data['is_manually_disabled'] = $request->boolean('is_manually_disabled');

        DB::transaction(function () use ($dgu, $data): void {
            $dgu->update($data);
        });

        Audit::record('dgu.updated', Dgu::class, $dgu->id, [
            'serial_number' => $dgu->serial_number,
        ]);

        return redirect()->route('dgus.show', $dgu)->with('status', 'dgu-updated');
    }

    public function destroy(Dgu $dgu): RedirectResponse
    {
        $id = $dgu->id;
        $serial = $dgu->serial_number;
        $dgu->delete();

        Audit::record('dgu.deleted', Dgu::class, $id, ['serial_number' => $serial]);

        return redirect()->route('dgus.index')->with('status', 'dgu-deleted');
    }
    private function parseTagsFromInput(?string $raw): ?array
    {
        if ($raw === null || trim($raw) === "") {
            return null;
        }

        $parts = preg_split("/\\s*,\\s*/", $raw, -1, PREG_SPLIT_NO_EMPTY);
        $parts = array_values(array_filter(array_map("trim", $parts)));

        return $parts === [] ? null : $parts;
    }
}
