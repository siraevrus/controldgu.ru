<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Новая ДГУ</h2>
    </x-slot>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form method="post" action="{{ route('dgus.store') }}" class="w-full bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                @csrf
                @include('dgus._form')
                <div class="flex gap-3">
                    <x-primary-button>Создать</x-primary-button>
                    <a href="{{ route('dgus.index') }}" class="text-sm text-gray-600 hover:underline self-center">Отмена</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
