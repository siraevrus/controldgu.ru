<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Редактирование: {{ $dgu->serial_number }}</h2>
    </x-slot>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form method="post" action="{{ route('dgus.update', $dgu) }}" class="w-full bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                @csrf
                @method('patch')
                @include('dgus._form')
                <div class="flex gap-3">
                    <x-primary-button>Сохранить</x-primary-button>
                    <a href="{{ route('dgus.show', $dgu) }}" class="text-sm text-gray-600 hover:underline self-center">Назад</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
