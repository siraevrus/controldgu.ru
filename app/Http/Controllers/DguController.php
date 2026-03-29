<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDguRequest;
use App\Http\Requests\UpdateDguRequest;
use App\Models\AuditLog;
use App\Models\Dgu;
use App\Models\GlobalThreshold;
use App\Models\TelemetrySnapshot;
use App\Support\Audit;
use App\Support\RussianRegions;
use App\Support\TelemetryParameters;
use Carbon\Carbon;
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
        $region = $request->string('region')->toString();
        if ($region !== '' && RussianRegions::isAllowed($region)) {
            $like = addcslashes($region, '%_\\');
            $query->where('region', 'like', '%'.$like.'%');
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
            'russianRegions' => RussianRegions::names(),
            'staleMinutes' => $staleMinutes,
        ]);
    }

    public function create(): View
    {
        return view('dgus.create', ['dgu' => new Dgu(['operational_state' => 'stopped'])]);
    }

    public function store(StoreDguRequest $request): RedirectResponse
    {
        $plainToken = Str::random(48);
        $data = $request->validated();
        $data['tags'] = $this->parseTagsFromInput($request->input('tags_input'));
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

    public function show(Request $request, Dgu $dgu): View
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

        $range = $this->resolveTelemetryChartRange($request);

        $snapshots = TelemetrySnapshot::query()
            ->where('dgu_id', $dgu->id)
            ->where('recorded_at', '>=', $range['from'])
            ->where('recorded_at', '<=', $range['to'])
            ->orderBy('recorded_at')
            ->get(['recorded_at', 'values']);

        $labels = $snapshots->map(fn (TelemetrySnapshot $s) => $s->recorded_at->timezone(config('app.timezone'))->format('d.m H:i'))->all();

        $palette = ['#2563eb', '#16a34a', '#dc2626', '#ca8a04', '#9333ea', '#0891b2', '#ea580c', '#4f46e5', '#db2777'];
        $perParameterCharts = [];
        $i = 0;
        foreach (TelemetryParameters::numericSlugs() as $slug) {
            $meta = $catalog[$slug] ?? ['label' => $slug, 'unit' => ''];
            $data = $snapshots->map(function (TelemetrySnapshot $s) use ($slug) {
                $v = $s->values[$slug] ?? null;

                return is_numeric($v) ? round((float) $v, 4) : null;
            })->all();
            $perParameterCharts[$slug] = [
                'slug' => $slug,
                'label' => $meta['label'],
                'unit' => $meta['unit'],
                'labels' => $labels,
                'data' => $data,
                'color' => $palette[$i % count($palette)],
                'hasData' => collect($data)->contains(fn ($v) => $v !== null),
            ];
            $i++;
        }

        $staleMinutes = (int) config('telemetry.stale_minutes', 10);
        $longOfflineHours = (int) config('telemetry.long_offline_hours', 12);

        $operationalAuditHistory = AuditLog::query()
            ->where('auditable_type', Dgu::class)
            ->where('auditable_id', $dgu->id)
            ->whereIn('action', ['dgu.operational.start', 'dgu.operational.stop'])
            ->with('user')
            ->orderByDesc('created_at')
            ->get();

        return view('dgus.show', [
            'dgu' => $dgu,
            'latest' => $latest,
            'thresholds' => $thresholds,
            'catalog' => $catalog,
            'perParameterCharts' => $perParameterCharts,
            'chartFromDate' => $range['fromInput'],
            'chartToDate' => $range['toInput'],
            'chartPeriodLabel' => $range['label'],
            'chartRangeDays' => $range['days'],
            'chartRangeClamped' => $range['rangeClamped'],
            'chartMaxSpanDays' => $range['maxSpanDays'],
            'staleMinutes' => $staleMinutes,
            'longOfflineHours' => $longOfflineHours,
            'operationalAuditHistory' => $operationalAuditHistory,
        ]);
    }

    /**
     * @return array{from: Carbon, to: Carbon, fromInput: string, toInput: string, label: string, days: int, rangeClamped: bool, maxSpanDays: int}
     */
    protected function resolveTelemetryChartRange(Request $request): array
    {
        $tz = config('app.timezone');
        $now = now($tz);
        $maxSpanDays = 90;

        $defaultFrom = $now->copy()->subDays(7)->startOfDay();
        $defaultTo = $now->copy()->endOfDay();

        $fromStr = $request->query('from');
        $toStr = $request->query('to');

        $validFrom = is_string($fromStr) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromStr);
        $validTo = is_string($toStr) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $toStr);

        $from = $defaultFrom->copy();
        $to = $defaultTo->copy();
        $rangeClamped = false;

        try {
            if ($validFrom && $validTo) {
                $from = Carbon::parse($fromStr, $tz)->startOfDay();
                $to = Carbon::parse($toStr, $tz)->endOfDay();
            } elseif ($validFrom) {
                $from = Carbon::parse($fromStr, $tz)->startOfDay();
                $to = $now->copy()->endOfDay();
            } elseif ($validTo) {
                $to = Carbon::parse($toStr, $tz)->endOfDay();
                $from = $to->copy()->subDays(6)->startOfDay();
            }
        } catch (\Throwable) {
            $from = $defaultFrom->copy();
            $to = $defaultTo->copy();
        }

        if ($to->gt($now->copy()->endOfDay())) {
            $to = $now->copy()->endOfDay();
        }

        if ($from->gt($to)) {
            $from = $to->copy()->startOfDay();
        }

        if ($from->diffInDays($to) + 1 > $maxSpanDays) {
            $from = $to->copy()->subDays($maxSpanDays - 1)->startOfDay();
            $rangeClamped = true;
        }

        $days = (int) $from->diffInDays($to) + 1;

        return [
            'from' => $from,
            'to' => $to,
            'fromInput' => $from->format('Y-m-d'),
            'toInput' => $to->format('Y-m-d'),
            'label' => $from->format('d.m.Y').' — '.$to->format('d.m.Y'),
            'days' => $days,
            'rangeClamped' => $rangeClamped,
            'maxSpanDays' => $maxSpanDays,
        ];
    }

    public function edit(Dgu $dgu): View
    {
        return view('dgus.edit', ['dgu' => $dgu]);
    }

    public function update(UpdateDguRequest $request, Dgu $dgu): RedirectResponse
    {
        $previousOperational = $dgu->operational_state;
        $data = $request->validated();
        $data['tags'] = $this->parseTagsFromInput($request->input('tags_input'));
        $data['is_manually_disabled'] = $request->boolean('is_manually_disabled');

        $newOperational = $data['operational_state'];
        $operationalChanged = $newOperational !== $previousOperational;

        if ($operationalChanged) {
            $data['operational_state_changed_at'] = now();
        }

        DB::transaction(function () use ($dgu, $data): void {
            $dgu->update($data);
        });

        if ($operationalChanged) {
            $action = $newOperational === 'running' ? 'dgu.operational.start' : 'dgu.operational.stop';
            Audit::record($action, Dgu::class, $dgu->id, [
                'serial_number' => $dgu->serial_number,
                'previous_state' => $previousOperational,
                'new_state' => $newOperational,
            ]);
        }

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
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        $parts = preg_split('/\\s*,\\s*/', $raw, -1, PREG_SPLIT_NO_EMPTY);
        $parts = array_values(array_filter(array_map('trim', $parts)));

        return $parts === [] ? null : $parts;
    }
}
