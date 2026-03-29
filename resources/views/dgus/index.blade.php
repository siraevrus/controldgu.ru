<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-row items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight shrink-0">ДГУ</h2>
            @can('create', App\Models\Dgu::class)
                <a href="{{ route('dgus.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-md hover:bg-gray-700 whitespace-nowrap shrink-0">Добавить ДГУ</a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-green-800 text-sm">{{ session('status') }}</div>
            @endif

            <form method="get" class="bg-white shadow-sm sm:rounded-lg p-3 flex flex-nowrap items-center gap-2 text-sm overflow-x-auto">
                <input type="text" name="serial" value="{{ $filters['serial'] ?? '' }}" placeholder="Серийный номер" class="border-gray-300 rounded-md min-w-[7rem] flex-1 px-2 py-2 shadow-sm" />
                <select name="region" class="border-gray-300 rounded-md min-w-[10rem] max-w-[16rem] shrink-0 px-2 py-2 shadow-sm text-sm">
                    <option value="">Регион: все</option>
                    @foreach ($russianRegions as $regionName)
                        <option value="{{ $regionName }}" @selected(($filters['region'] ?? '') === $regionName)>{{ $regionName }}</option>
                    @endforeach
                </select>
                <input type="text" name="model_name" value="{{ $filters['model_name'] ?? '' }}" placeholder="Модель" class="border-gray-300 rounded-md min-w-[5rem] flex-1 px-2 py-2 shadow-sm" />
                <select name="link" class="border-gray-300 rounded-md min-w-[11rem] max-w-[16rem] shrink-0 px-2 py-2 shadow-sm">
                    <option value="">Связь: все</option>
                    <option value="fresh" @selected(($filters['link'] ?? '') === 'fresh')>Активна (≤ {{ $staleMinutes }} мин)</option>
                    <option value="stale" @selected(($filters['link'] ?? '') === 'stale')>Нет связи / устарела</option>
                    @php
                        $longOfflineHours = (int) config('telemetry.long_offline_hours', 12);
                    @endphp
                    <option value="long_offline" @selected(($filters['link'] ?? '') === 'long_offline')>Долго без данных (≥ {{ $longOfflineHours }} ч)</option>
                </select>
                <select name="operational" class="border-gray-300 rounded-md min-w-[9rem] shrink-0 px-2 py-2 shadow-sm">
                    <option value="">Состояние: все</option>
                    <option value="running" @selected(($filters['operational'] ?? '') === 'running')>В работе</option>
                    <option value="stopped" @selected(($filters['operational'] ?? '') === 'stopped')>Остановлены</option>
                </select>
                <select name="flags" class="border-gray-300 rounded-md min-w-[10rem] shrink-0 px-2 py-2 shadow-sm">
                    <option value="">Дополнительно</option>
                    <option value="manual" @selected(($filters['flags'] ?? '') === 'manual')>Ручное отключение</option>
                    <option value="no_coords" @selected(($filters['flags'] ?? '') === 'no_coords')>Без координат</option>
                </select>
                <div class="flex gap-2 shrink-0 ml-auto">
                    <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md whitespace-nowrap">Фильтр</button>
                    <a href="{{ route('dgus.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white whitespace-nowrap">Сброс</a>
                </div>
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto w-full">
                <table class="w-full min-w-full table-fixed text-sm text-left">
                    <colgroup>
                        <col style="width: 22%">
                        <col style="width: 28%">
                        <col style="width: 20%">
                        <col style="width: 20%">
                        <col style="width: 10%">
                    </colgroup>
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-4 py-2">Серийный номер</th>
                            <th class="px-4 py-2">Название</th>
                            <th class="px-4 py-2">Регион</th>
                            <th class="px-4 py-2">Связь</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dgus as $dgu)
                            @php
                                $fresh = $dgu->last_telemetry_at && $dgu->last_telemetry_at->greaterThanOrEqualTo(now()->subMinutes($staleMinutes));
                            @endphp
                            <tr class="border-t">
                                <td class="px-4 py-2 font-medium text-gray-900 break-words align-top">{{ $dgu->serial_number }}</td>
                                <td class="px-4 py-2 break-words align-top">{{ $dgu->name ?? '—' }}</td>
                                <td class="px-4 py-2 break-words align-top">{{ $dgu->region ?? '—' }}</td>
                                <td class="px-4 py-2 align-top whitespace-nowrap">
                                    @if ($fresh)
                                        <span class="text-green-700">Ок</span>
                                    @else
                                        <span class="text-amber-700">Нет / устарела</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-right align-top whitespace-nowrap">
                                    <a href="{{ route('dgus.show', $dgu) }}" class="text-indigo-600 hover:underline">Открыть</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">Нет записей</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div>{{ $dgus->links() }}</div>
        </div>
    </div>
</x-app-layout>
