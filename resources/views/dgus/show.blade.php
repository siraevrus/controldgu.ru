@php
    use App\Support\TelemetryParameters;
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex w-full flex-row items-center justify-between gap-4">
            <div class="min-w-0 flex-1">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight truncate">{{ $dgu->serial_number }}</h2>
                <p class="text-sm text-gray-500 mt-1 break-all sm:break-normal">public_id: <code class="bg-gray-100 px-1 rounded">{{ $dgu->public_id }}</code></p>
            </div>
            <div class="flex shrink-0 flex-wrap items-center justify-end gap-2">
                @can('update', $dgu)
                    <a href="{{ route('dgus.edit', $dgu) }}" class="px-3 py-2 text-sm bg-gray-800 text-white rounded-md whitespace-nowrap">Редактировать</a>
                @endcan
                <a href="{{ route('dgus.index') }}" class="px-3 py-2 text-sm border rounded-md text-gray-700 whitespace-nowrap">К списку</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('telemetry_plain_token'))
                <div class="rounded-md bg-amber-50 border border-amber-200 p-4 text-amber-900 text-sm space-y-2">
                    <p class="font-medium">Сохраните токен приёма телеметрии — он показывается один раз.</p>
                    <code class="block break-all bg-white border rounded p-2">{{ session('telemetry_plain_token') }}</code>
                    <p class="text-xs">Передавайте в заголовке <code>Authorization: Bearer …</code> или <code>X-Dgu-Token</code>.</p>
                </div>
            @endif
            @if (session('status') === 'dgu-operational-updated')
                <div class="rounded-md bg-green-50 p-4 text-green-800 text-sm">Состояние ДГУ обновлено.</div>
            @elseif (session('status') === 'dgu-operational-unchanged')
                <div class="rounded-md bg-gray-50 p-4 text-gray-700 text-sm">Уже выбрано это состояние.</div>
            @elseif (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-green-800 text-sm">{{ session('status') }}</div>
            @endif

            @php
                $fresh = $dgu->last_telemetry_at && $dgu->last_telemetry_at->greaterThanOrEqualTo(now()->subMinutes($staleMinutes));
                $longOff = ! $dgu->last_telemetry_at || $dgu->last_telemetry_at->lessThan(now()->subHours($longOfflineHours));
            @endphp
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white shadow-sm sm:rounded-lg p-4 text-sm">
                    <div class="text-gray-500">Связь (≤ {{ $staleMinutes }} мин)</div>
                    <div class="text-lg font-semibold {{ $fresh ? 'text-green-700' : 'text-amber-700' }}">{{ $fresh ? 'Активна' : 'Нет / устарела' }}</div>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-4 text-sm">
                    <div class="text-gray-500">Долго без данных ({{ $longOfflineHours }}+ ч)</div>
                    <div class="text-lg font-semibold {{ $longOff ? 'text-red-700' : 'text-gray-800' }}">{{ $longOff ? 'Да' : 'Нет' }}</div>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-4 text-sm">
                    <div class="text-gray-500">Состояние</div>
                    <div class="text-lg font-semibold">{{ $dgu->operational_state === 'running' ? 'Работает' : 'Остановлен' }}</div>
                </div>
            </div>

            @can('controlOperational', $dgu)
                @php
                    $opChangedAt = $dgu->operational_state_changed_at ?? $dgu->updated_at;
                    $opTz = config('app.timezone');
                    $running = $dgu->operational_state === 'running';
                @endphp
                <div class="bg-white shadow-sm sm:rounded-lg p-6" x-data="{ confirmOpen: false, targetState: 'running', historyOpen: false }">
                    <div class="mb-3 flex items-center justify-between gap-4">
                        <h3 class="text-lg font-medium text-gray-900">Управление</h3>
                        <div class="shrink-0">
                            @if ($dgu->operational_state === 'stopped')
                                <button type="button"
                                    @click="targetState = 'running'; confirmOpen = true"
                                    class="px-4 py-2 text-sm bg-green-700 text-white rounded-md hover:bg-green-800">
                                    Запустить
                                </button>
                            @else
                                <button type="button"
                                    @click="targetState = 'stopped'; confirmOpen = true"
                                    class="px-4 py-2 text-sm bg-amber-600 text-white rounded-md hover:bg-amber-700">
                                    Остановить
                                </button>
                            @endif
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 mb-4">Каждое переключение запуска и остановки сохраняется в журнале ниже.</p>
                    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5 text-sm border-b border-gray-100 pb-5">
                        <div>
                            <dt class="text-gray-500 mb-1">Статус</dt>
                            <dd class="font-semibold {{ $running ? 'text-green-700' : 'text-gray-700' }}">{{ $running ? 'Работает' : 'Остановлен' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 mb-1">Время последнего переключения</dt>
                            <dd class="font-medium text-gray-900 tabular-nums">{{ $opChangedAt->timezone($opTz)->format('d.m.Y H:i:s') }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 mb-1">В текущем состоянии</dt>
                            <dd class="font-medium text-gray-900">{{ $opChangedAt->diffForHumans(null, true, false, 4) }}</dd>
                        </div>
                    </dl>

                    @if ($operationalAuditHistory->isNotEmpty())
                        <div class="mb-5 border-t border-gray-100 pt-5">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Журнал запусков и остановок</h4>
                            <div class="text-sm overflow-x-auto">
                                <table class="min-w-full table-fixed border-collapse">
                                    <thead>
                                        <tr class="border-b border-gray-200 text-left text-xs font-medium text-gray-500">
                                            <th scope="col" class="w-[28%] py-2 pr-3 font-medium">Время</th>
                                            <th scope="col" class="w-[44%] py-2 px-3 font-medium">Событие</th>
                                            <th scope="col" class="w-[28%] py-2 pl-3 font-medium">Пользователь</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach ($operationalAuditHistory as $i => $log)
                                            @php
                                                $props = $log->properties ?? [];
                                                $prev = $props['previous_state'] ?? null;
                                                $new = $props['new_state'] ?? null;
                                                $stateLabel = static fn (?string $s) => match ($s) {
                                                    'running' => 'работа',
                                                    'stopped' => 'остановка',
                                                    default => $s ?? '—',
                                                };
                                                $userEmail = $log->user?->email;
                                            @endphp
                                            <tr class="text-gray-800 align-top @if ($i >= 10) hidden @endif"
                                                :class="{ 'hidden': !historyOpen && {{ $i >= 10 ? 'true' : 'false' }} }">
                                                <td class="py-2.5 pr-3">
                                                    <time class="tabular-nums text-gray-600 whitespace-nowrap" datetime="{{ $log->created_at->toIso8601String() }}">{{ $log->created_at->timezone($opTz)->format('d.m.Y H:i:s') }}</time>
                                                </td>
                                                <td class="py-2.5 px-3 min-w-0">
                                                    <span class="font-medium block">{{ $log->action === 'dgu.operational.start' ? 'Запуск' : 'Остановка' }}</span>
                                                    @if ($prev !== null && $new !== null)
                                                        <span class="text-gray-600 text-xs leading-snug block mt-0.5">{{ $stateLabel($prev) }} → {{ $stateLabel($new) }}</span>
                                                    @endif
                                                </td>
                                                <td class="py-2.5 pl-3 min-w-0 max-w-0">
                                                    <span class="block truncate text-gray-500" title="{{ $userEmail ?? '' }}">{{ $userEmail ?? '—' }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if ($operationalAuditHistory->count() > 10)
                                <button type="button" @click="historyOpen = !historyOpen"
                                    class="mt-3 text-sm font-medium text-indigo-600 hover:text-indigo-800">
                                    <span x-show="!historyOpen">Показать ещё {{ $operationalAuditHistory->count() - 10 }}</span>
                                    <span x-show="historyOpen" x-cloak>Свернуть</span>
                                </button>
                            @endif
                        </div>
                    @endif

                    <div x-show="confirmOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40" @keydown.escape.window="confirmOpen = false">
                        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6 space-y-4" @click.outside="confirmOpen = false">
                            <h4 class="text-lg font-semibold text-gray-900">Подтвердите действие</h4>
                            <p class="text-sm text-gray-600" x-text="targetState === 'running' ? 'Запустить ДГУ?' : 'Остановить ДГУ?'"></p>
                            <div class="flex justify-end gap-2">
                                <button type="button" @click="confirmOpen = false" class="px-3 py-2 text-sm border rounded-md text-gray-700">Отмена</button>
                                <form method="post" action="{{ route('dgus.operational.update', $dgu) }}">
                                    @csrf
                                    <input type="hidden" name="state" :value="targetState">
                                    <button type="submit" class="px-3 py-2 text-sm bg-gray-900 text-white rounded-md">Подтвердить</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endcan

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900">Текущие параметры</h3>
                @php
                    $d = $chartRangeDays;
                    $dayWord = match (true) {
                        $d % 10 === 1 && $d % 100 !== 11 => 'день',
                        in_array($d % 10, [2, 3, 4], true) && ! in_array($d % 100, [12, 13, 14], true) => 'дня',
                        default => 'дней',
                    };
                @endphp
                <p class="text-sm text-gray-700 mt-2 font-medium">Период графиков: {{ $chartPeriodLabel }} <span class="text-gray-500 font-normal">({{ $chartRangeDays }} {{ $dayWord }})</span></p>

                <form method="get" action="{{ route('dgus.show', $dgu) }}" class="mt-4 flex flex-wrap items-start gap-3">
                    <div class="shrink-0">
                        <label for="telemetry-from" class="block text-xs font-medium text-gray-600 mb-1">С даты</label>
                        <input id="telemetry-from" type="date" name="from" value="{{ $chartFromDate }}"
                            max="{{ $chartToDate }}"
                            class="box-border block h-10 w-full rounded-md border border-gray-300 bg-white px-3 text-sm leading-10 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 sm:w-auto" />
                    </div>
                    <div class="shrink-0">
                        <label for="telemetry-to" class="block text-xs font-medium text-gray-600 mb-1">По дату</label>
                        <input id="telemetry-to" type="date" name="to" value="{{ $chartToDate }}"
                            min="{{ $chartFromDate }}"
                            max="{{ now()->format('Y-m-d') }}"
                            class="box-border block h-10 w-full rounded-md border border-gray-300 bg-white px-3 text-sm leading-10 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 sm:w-auto" />
                    </div>
                    <div class="shrink-0">
                        <span class="invisible mb-1 block text-xs font-medium select-none" aria-hidden="true">.</span>
                        <div class="flex gap-2">
                            <button type="submit" class="box-border inline-flex h-10 shrink-0 items-center justify-center rounded-md border border-gray-800 bg-gray-800 px-4 text-sm font-medium leading-none text-white hover:bg-gray-700">Показать</button>
                            <a href="{{ route('dgus.show', $dgu) }}" class="box-border inline-flex h-10 shrink-0 items-center justify-center rounded-md border border-gray-300 bg-white px-4 text-sm font-medium leading-none text-gray-700 hover:bg-gray-50">Сброс</a>
                        </div>
                    </div>
                </form>
                @if ($chartRangeClamped)
                    <p class="text-xs text-amber-800 mt-2">Интервал ограничен {{ $chartMaxSpanDays }} сутками — уточните даты при необходимости.</p>
                @endif

                @if (!$latest)
                    <p class="text-gray-500 text-sm mt-4">Телеметрия ещё не поступала. Пример заполнения: <code class="bg-gray-100 px-1 rounded text-xs">php artisan dgu:seed-week-telemetry</code></p>
                @else
                    <p class="text-xs text-gray-500 mt-3">Последний снимок: {{ $latest->recorded_at->format('Y-m-d H:i:s') }}</p>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
                        @foreach (TelemetryParameters::numericSlugs() as $slug)
                            @php
                                $meta = $catalog[$slug] ?? ['label' => $slug, 'unit' => ''];
                                $val = $latest->values[$slug] ?? null;
                                $t = $thresholds->get($slug);
                                $norma = '—';
                                $ok = true;
                                if ($t) {
                                    $parts = [];
                                    if ($t->min_value !== null) { $parts[] = 'min '.$t->min_value; }
                                    if (! $t->ignore_max && $t->max_value !== null) { $parts[] = 'max '.$t->max_value; }
                                    $norma = implode(', ', $parts);
                                }
                                if (is_numeric($val) && $t && $t->is_active) {
                                    $n = (float) $val;
                                    if ($t->min_value !== null && $n < (float) $t->min_value) { $ok = false; }
                                    if (! $t->ignore_max && $t->max_value !== null && $n > (float) $t->max_value) { $ok = false; }
                                }
                                $cfg = $perParameterCharts[$slug] ?? null;
                            @endphp
                            <div class="border border-gray-200 rounded-lg p-4 flex flex-col gap-3 shadow-sm">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $meta['label'] }}</div>
                                        <div class="text-2xl font-semibold text-gray-800 tabular-nums mt-1">
                                            {{ is_numeric($val) ? $val : ($val ?? '—') }}@if ($meta['unit'])<span class="text-sm text-gray-500 font-normal ml-1">{{ $meta['unit'] }}</span>@endif
                                        </div>
                                    </div>
                                    <div class="text-right text-xs space-y-1">
                                        <div class="text-gray-500">Норма: <span class="text-gray-700">{{ $norma }}</span></div>
                                        <div>
                                            @if (! is_numeric($val) || ! $t)
                                                <span class="text-gray-400">—</span>
                                            @elseif ($ok)
                                                <span class="inline-flex px-2 py-0.5 rounded bg-green-50 text-green-800 font-medium">Норма</span>
                                            @else
                                                <span class="inline-flex px-2 py-0.5 rounded bg-red-50 text-red-800 font-medium">Отклонение</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @if ($cfg && $cfg['hasData'])
                                    <div class="relative h-36 w-full">
                                        <canvas id="chart-{{ $slug }}"></canvas>
                                    </div>
                                @else
                                    <p class="text-xs text-gray-400">Нет точек за выбранный период для графика.</p>
                                @endif
                            </div>
                        @endforeach
                        @php $st = $latest->values[TelemetryParameters::AGGREGATE_STATUS] ?? null; @endphp
                        <div class="border border-gray-200 rounded-lg p-4 flex flex-col justify-center shadow-sm lg:col-span-2">
                            <div class="text-sm font-medium text-gray-900">{{ $catalog[TelemetryParameters::AGGREGATE_STATUS]['label'] ?? 'Статус' }}</div>
                            <p class="text-sm text-gray-600 mt-1">Сводный признак из телеметрии (не число), график не строится.</p>
                            <div class="text-xl font-semibold text-gray-800 mt-2">{{ $st ?? '—' }}</div>
                        </div>
                    </div>
                @endif
            </div>

            @can('delete', $dgu)
                <form method="post" action="{{ route('dgus.destroy', $dgu) }}" onsubmit="return confirm('Удалить ДГУ?');" class="text-right">
                    @csrf
                    @method('delete')
                    <button type="submit" class="text-sm text-red-600 hover:underline">Удалить ДГУ</button>
                </form>
            @endcan
        </div>
    </div>
    @if ($latest && collect($perParameterCharts)->contains(fn ($c) => $c['hasData']))
        @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
            (function () {
                const configs = @json($perParameterCharts);
                Object.keys(configs).forEach(function (slug) {
                    const cfg = configs[slug];
                    if (!cfg.hasData) return;
                    const canvas = document.getElementById('chart-' + slug);
                    if (!canvas) return;
                    const dsLabel = cfg.unit ? cfg.label + ', ' + cfg.unit : cfg.label;
                    new Chart(canvas.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: cfg.labels,
                            datasets: [{
                                label: dsLabel,
                                data: cfg.data,
                                borderColor: cfg.color,
                                backgroundColor: cfg.color + '26',
                                borderWidth: 2,
                                fill: false,
                                tension: 0.2,
                                spanGaps: true,
                                pointRadius: 0,
                                pointHoverRadius: 4,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: { mode: 'index', intersect: false },
                            plugins: { legend: { display: false } },
                            scales: {
                                x: {
                                    ticks: { maxTicksLimit: 8, maxRotation: 0, autoSkip: true },
                                    grid: { display: false },
                                },
                                y: { beginAtZero: false, ticks: { maxTicksLimit: 6 } },
                            },
                        },
                    });
                });
            })();
        </script>
        @endpush
    @endif
</x-app-layout>
