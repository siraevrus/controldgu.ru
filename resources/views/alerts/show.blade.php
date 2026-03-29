<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Тревога #{{ $alert->id }}</h2>
    </x-slot>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 text-sm space-y-2">
                <p><span class="text-gray-500">ДГУ:</span> <a href="{{ route('dgus.show', $alert->dgu) }}" class="text-indigo-600 hover:underline">{{ $alert->dgu->serial_number }}</a></p>
                <p><span class="text-gray-500">Параметр:</span> {{ $alert->parameter_slug }}</p>
                <p><span class="text-gray-500">Статус:</span> {{ $alert->status }}</p>
                <p><span class="text-gray-500">Время:</span> {{ $alert->triggered_at->format('Y-m-d H:i:s') }}</p>
                @if ($alert->triggered_value)
                    <p><span class="text-gray-500">Значение:</span> {{ $alert->triggered_value }}</p>
                @endif
                @if ($alert->message)
                    <p class="text-gray-800">{{ $alert->message }}</p>
                @endif
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-medium text-gray-900 mb-3">История</h3>
                <ul class="space-y-3 text-sm">
                    @foreach ($alert->events->sortBy('created_at') as $event)
                        <li class="border-l-2 border-gray-200 pl-3">
                            <div class="text-gray-500 text-xs">{{ $event->created_at->format('Y-m-d H:i:s') }} · {{ $event->type }}</div>
                            <div>{{ $event->body ?? '—' }}</div>
                            @if ($event->user)
                                <div class="text-xs text-gray-500">{{ $event->user->name }}</div>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>

            @if ($alert->status === App\Models\Alert::STATUS_OPEN && auth()->user()->hasRole('admin'))
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-medium text-gray-900 mb-3">Подтвердить тревогу</h3>
                    <form method="post" action="{{ route('alerts.acknowledge', $alert) }}" class="space-y-3">
                        @csrf
                        <div>
                            <x-input-label for="comment" value="Комментарий" />
                            <textarea id="comment" name="comment" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>{{ old('comment') }}</textarea>
                            <x-input-error :messages="$errors->get('comment')" class="mt-2" />
                        </div>
                        <x-primary-button>Подтвердить</x-primary-button>
                    </form>
                </div>
            @endif

            <a href="{{ route('alerts.index') }}" class="text-sm text-gray-600 hover:underline">← К списку</a>
        </div>
    </div>
</x-app-layout>
