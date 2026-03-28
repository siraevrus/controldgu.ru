<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Активные тревоги</h2>
    </x-slot>
    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-green-800 text-sm">{{ session('status') }}</div>
            @endif
            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-4 py-2 text-left">Время</th>
                            <th class="px-4 py-2 text-left">ДГУ</th>
                            <th class="px-4 py-2 text-left">Тревога</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($alerts as $alert)
                            <tr class="border-t">
                                <td class="px-4 py-2 whitespace-nowrap">{{ $alert->triggered_at->format('Y-m-d H:i:s') }}</td>
                                <td class="px-4 py-2">{{ $alert->dgu->serial_number }}</td>
                                <td class="px-4 py-2">{{ $alert->title }}</td>
                                <td class="px-4 py-2 text-right">
                                    <a href="{{ route('alerts.show', $alert) }}" class="text-indigo-600 hover:underline">Подробнее</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">Нет активных тревог</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div>{{ $alerts->links() }}</div>
        </div>
    </div>
</x-app-layout>
