<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Уведомления</h2>
            @php
                $hasUnread = \App\Models\AppNotification::query()
                    ->where('user_id', auth()->id())
                    ->whereNull('read_at')
                    ->exists();
            @endphp
            @if ($hasUnread)
                <form method="post" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <x-secondary-button type="submit">Отметить все прочитанными</x-secondary-button>
                </form>
            @endif
        </div>
    </x-slot>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-4 py-2 text-left font-medium">Заголовок</th>
                            <th class="px-4 py-2 text-left font-medium">Площадка</th>
                            <th class="px-4 py-2 text-left font-medium whitespace-nowrap">Время</th>
                            <th class="px-4 py-2 text-right font-medium w-[1%] whitespace-nowrap">Действие</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($notifications as $n)
                            <tr class="border-t border-gray-100 {{ $n->read_at ? 'opacity-70 bg-gray-50/50' : 'border-l-4 border-l-indigo-500 bg-white' }}">
                                <td class="px-4 py-3 align-middle font-medium {{ $n->read_at ? 'text-gray-900' : 'text-red-600' }}">{{ $n->title }}</td>
                                <td class="px-4 py-3 align-middle text-gray-600">
                                    @if ($n->dgu)
                                        <a href="{{ route('dgus.show', $n->dgu) }}" class="text-indigo-600 hover:underline">
                                            {{ $n->body ?? ($n->dgu->name ?? $n->dgu->serial_number) }}
                                        </a>
                                    @else
                                        {{ $n->body ?? '—' }}
                                    @endif
                                </td>
                                <td class="px-4 py-3 align-middle text-gray-500 whitespace-nowrap tabular-nums">{{ $n->created_at->format('Y-m-d H:i:s') }}</td>
                                <td class="px-4 py-3 align-middle text-right">
                                    <div class="inline-flex flex-wrap items-center justify-end gap-2">
                                        @if (isset($n->data['alert_id']))
                                            <a href="{{ route('alerts.show', $n->data['alert_id']) }}"
                                                class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                                Открыть тревогу
                                            </a>
                                        @endif
                                        @if (! $n->read_at)
                                            <form method="post" action="{{ route('notifications.read', $n) }}" class="inline">
                                                @csrf
                                                <x-secondary-button type="submit" class="text-xs py-2">Прочитано</x-secondary-button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-500">Нет уведомлений.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div>{{ $notifications->links() }}</div>
        </div>
    </div>
</x-app-layout>
