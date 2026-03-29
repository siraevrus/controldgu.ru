<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Тревоги</h2>
            <p class="text-sm text-gray-500 mt-1">{{ $filterDescription }}</p>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-green-800 text-sm">{{ session('status') }}</div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-4 space-y-4">
                <form method="get" action="{{ route('alerts.index') }}" class="space-y-2">
                    <p class="text-sm font-medium text-gray-700">Период</p>
                    <div class="flex flex-wrap items-end gap-3">
                        <div>
                            <label for="filter-from" class="block text-xs text-gray-500 mb-1">С даты</label>
                            <input id="filter-from" type="date" name="from" value="{{ $filterFrom }}"
                                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
                        </div>
                        <div>
                            <label for="filter-to" class="block text-xs text-gray-500 mb-1">По дату</label>
                            <input id="filter-to" type="date" name="to" value="{{ $filterTo }}"
                                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
                        </div>
                        <button type="submit"
                            class="inline-flex items-center px-3 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            Показать период
                        </button>
                    </div>
                    <p class="text-xs text-gray-500">Для одного дня укажите одинаковую дату в обоих полях.</p>
                </form>
                <div class="pt-2 border-t border-gray-100">
                    <a href="{{ route('alerts.index') }}"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-xs font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Сброс (последние 10 дней)
                    </a>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-4 py-2 text-left">Время</th>
                            <th class="px-4 py-2 text-left">ДГУ</th>
                            <th class="px-4 py-2 text-left">Тревога</th>
                            <th class="px-4 py-2 text-left">Статус</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($alerts as $alert)
                            <tr class="border-t">
                                <td class="px-4 py-2 whitespace-nowrap">{{ $alert->triggered_at->timezone(config('app.timezone'))->format('Y-m-d H:i:s') }}</td>
                                <td class="px-4 py-2">{{ $alert->dgu->serial_number }}</td>
                                <td class="px-4 py-2">{{ $alert->title }}</td>
                                <td class="px-4 py-2">
                                    @if ($alert->status === \App\Models\Alert::STATUS_OPEN)
                                        <span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-900">Открыта</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">Подтверждена</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-right">
                                    <a href="{{ route('alerts.show', $alert) }}" class="text-indigo-600 hover:underline">Подробнее</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">Нет тревог за выбранный интервал</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div>{{ $alerts->links() }}</div>
        </div>
    </div>
</x-app-layout>
