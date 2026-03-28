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
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-3">
            @forelse ($notifications as $n)
                <div class="bg-white shadow-sm sm:rounded-lg p-4 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 {{ $n->read_at ? 'opacity-70' : 'border-l-4 border-indigo-500' }}">
                    <div class="text-sm space-y-1">
                        <div class="font-medium text-gray-900">{{ $n->title }}</div>
                        @if($n->body)
                            <div class="text-gray-600">{{ $n->body }}</div>
                        @endif
                        <div class="text-xs text-gray-500">{{ $n->created_at->format('Y-m-d H:i:s') }}</div>
                        @if(isset($n->data['alert_id']))
                            <a href="{{ route('alerts.show', $n->data['alert_id']) }}" class="text-indigo-600 text-sm hover:underline">Открыть тревогу</a>
                        @endif
                    </div>
                    @if(!$n->read_at)
                        <form method="post" action="{{ route('notifications.read', $n) }}">
                            @csrf
                            <x-secondary-button type="submit" class="text-xs">Прочитано</x-secondary-button>
                        </form>
                    @endif
                </div>
            @empty
                <p class="text-gray-500 text-sm">Нет уведомлений.</p>
            @endforelse
            <div>{{ $notifications->links() }}</div>
        </div>
    </div>
</x-app-layout>
