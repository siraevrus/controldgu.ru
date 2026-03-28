<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">ДГУ</h2>
            @can('create', App\Models\Dgu::class)
                <a href="{{ route('dgus.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-md hover:bg-gray-700">Добавить ДГУ</a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-green-800 text-sm">{{ session('status') }}</div>
            @endif

            <form method="get" class="bg-white shadow-sm sm:rounded-lg p-4 grid grid-cols-1 md:grid-cols-5 gap-3 text-sm">
                <input type="text" name="serial" value="{{ $filters['serial'] ?? '' }}" placeholder="Серийный номер" class="border-gray-300 rounded-md w-full" />
                <input type="text" name="region" value="{{ $filters['region'] ?? '' }}" placeholder="Регион" class="border-gray-300 rounded-md w-full" />
                <input type="text" name="model_name" value="{{ $filters['model_name'] ?? '' }}" placeholder="Модель" class="border-gray-300 rounded-md w-full" />
                <select name="link" class="border-gray-300 rounded-md w-full">
                    <option value="">Связь: все</option>
                    <option value="fresh" @selected(($filters['link'] ?? '') === 'fresh')>Активна (≤ {{ $staleMinutes }} мин)</option>
                    <option value="stale" @selected(($filters['link'] ?? '') === 'stale')>Нет связи / устарела</option>
                </select>
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md">Фильтр</button>
                    <a href="{{ route('dgus.index') }}" class="px-4 py-2 border rounded-md text-gray-700">Сброс</a>
                </div>
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full text-sm text-left">
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
                                <td class="px-4 py-2 font-medium text-gray-900">{{ $dgu->serial_number }}</td>
                                <td class="px-4 py-2">{{ $dgu->name ?? '—' }}</td>
                                <td class="px-4 py-2">{{ $dgu->region ?? '—' }}</td>
                                <td class="px-4 py-2">
                                    @if ($fresh)
                                        <span class="text-green-700">Ок</span>
                                    @else
                                        <span class="text-amber-700">Нет / устарела</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-right">
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
