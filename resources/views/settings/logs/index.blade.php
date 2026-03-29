<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('settings.index') }}" class="text-sm text-indigo-600 hover:underline">← Настройки</a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight mt-1">Системные логи</h2>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto text-sm">
                <table class="min-w-full">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-3 py-2 text-left">Время</th>
                            <th class="px-3 py-2 text-left">Источник</th>
                            <th class="px-3 py-2 text-left">Уровень</th>
                            <th class="px-3 py-2 text-left">Сообщение</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr class="border-t">
                                <td class="px-3 py-2 whitespace-nowrap">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                <td class="px-3 py-2">{{ $log->source }}</td>
                                <td class="px-3 py-2">{{ $log->level }}</td>
                                <td class="px-3 py-2">{{ $log->message }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-3 py-6 text-center text-gray-500">Записей пока нет</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div>{{ $logs->links() }}</div>
        </div>
    </div>
</x-app-layout>
