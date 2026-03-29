<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('settings.index') }}" class="text-sm text-indigo-600 hover:underline">← Настройки</a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight mt-1">Порог: {{ $label }}</h2>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form method="post" action="{{ route('settings.thresholds.update', $threshold) }}" class="max-w-3xl mx-auto bg-white shadow-sm sm:rounded-lg p-6 space-y-4 text-sm">
                @csrf
                @method('patch')
                <div>
                    <x-input-label for="min_value" value="Минимум @if($unit)({{ $unit }})@endif" />
                    <x-text-input id="min_value" name="min_value" type="text" class="mt-1 block w-full" :value="old('min_value', $threshold->min_value)" />
                </div>
                <div>
                    <x-input-label for="max_value" value="Максимум @if($unit)({{ $unit }})@endif" />
                    <x-text-input id="max_value" name="max_value" type="text" class="mt-1 block w-full" :value="old('max_value', $threshold->max_value)" />
                </div>
                <div class="flex items-center gap-2">
                    <input type="hidden" name="ignore_max" value="0" />
                    <input id="ignore_max" type="checkbox" name="ignore_max" value="1" class="rounded border-gray-300" @checked(old('ignore_max', $threshold->ignore_max)) />
                    <x-input-label for="ignore_max" value="Игнорировать максимум (наработка и т.п.)" />
                </div>
                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0" />
                    <input id="is_active" type="checkbox" name="is_active" value="1" class="rounded border-gray-300" @checked(old('is_active', $threshold->is_active)) />
                    <x-input-label for="is_active" value="Порог активен" />
                </div>
                <div class="flex gap-3">
                    <x-primary-button>Сохранить</x-primary-button>
                    <a href="{{ route('settings.thresholds.index') }}" class="self-center text-gray-600 hover:underline">Назад</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
