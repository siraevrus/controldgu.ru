@php
    use App\Support\TelemetryParameters;
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $dgu->serial_number }}</h2>
                <p class="text-sm text-gray-500 mt-1">public_id: <code class="bg-gray-100 px-1 rounded">{{ $dgu->public_id }}</code></p>
            </div>
            <div class="flex flex-wrap gap-2">
                @can('update', $dgu)
                    <a href="{{ route('dgus.edit', $dgu) }}" class="px-3 py-2 text-sm bg-gray-800 text-white rounded-md">Редактировать</a>
                @endcan
                <a href="{{ route('dgus.index') }}" class="px-3 py-2 text-sm border rounded-md text-gray-700">К списку</a>
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
            @if (session('status'))
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
                    <div class="text-gray-500">Состояние (демо)</div>
                    <div class="text-lg font-semibold">{{ $dgu->operational_state === 'running' ? 'Работает' : 'Остановлен' }}</div>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Текущие параметры</h3>
                @if (!$latest)
                    <p class="text-gray-500 text-sm">Телеметрия ещё не поступала.</p>
                @else
                    <p class="text-xs text-gray-500 mb-3">Снимок: {{ $latest->recorded_at->format('Y-m-d H:i:s') }}</p>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 text-gray-600">
                                <tr>
                                    <th class="px-3 py-2 text-left">Параметр</th>
                                    <th class="px-3 py-2 text-left">Значение</th>
                                    <th class="px-3 py-2 text-left">Норма</th>
                                    <th class="px-3 py-2 text-left">Статус</th>
                                </tr>
                            </thead>
                            <tbody>
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
                                    @endphp
                                    <tr class="border-t">
                                        <td class="px-3 py-2">{{ $meta['label'] }}</td>
                                        <td class="px-3 py-2">{{ is_numeric($val) ? $val : ($val ?? '—') }} @if($meta['unit'])<span class="text-gray-500">{{ $meta['unit'] }}</span>@endif</td>
                                        <td class="px-3 py-2 text-gray-600">{{ $norma }}</td>
                                        <td class="px-3 py-2">
                                            @if (! is_numeric($val) || ! $t)
                                                <span class="text-gray-500">—</span>
                                            @elseif($ok)
                                                <span class="text-green-700">Норма</span>
                                            @else
                                                <span class="text-red-700">Отклонение</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                @php $st = $latest->values[TelemetryParameters::AGGREGATE_STATUS] ?? null; @endphp
                                <tr class="border-t">
                                    <td class="px-3 py-2">{{ $catalog[TelemetryParameters::AGGREGATE_STATUS]['label'] ?? 'Статус' }}</td>
                                    <td class="px-3 py-2">{{ $st ?? '—' }}</td>
                                    <td class="px-3 py-2">—</td>
                                    <td class="px-3 py-2 text-gray-500">Инфо</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Тренды (24 ч, шаг данных — как приходит)</h3>
                @if (count($chartDatasets) === 0)
                    <p class="text-sm text-gray-500">Недостаточно точек для графика.</p>
                @else
                    <canvas id="telemetryChart" height="120"></canvas>
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
    @if (count($chartDatasets) > 0)
        @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
            const ctx = document.getElementById('telemetryChart');
            const chartData = @json(['labels' => $chartLabels, 'datasets' => $chartDatasets]);
            new Chart(ctx, {
                type: 'line',
                data: chartData,
                options: {
                    responsive: true,
                    interaction: { mode: 'index', intersect: false },
                    scales: { y: { beginAtZero: false } }
                }
            });
        </script>
        @endpush
    @endif
</x-app-layout>
