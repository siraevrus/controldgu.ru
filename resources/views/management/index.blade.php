<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Управление</h2>
        <p class="text-sm text-gray-500 mt-1">Запуск и остановка — на карточке ДГУ. Ниже — заготовки под сценарии ТЗ.</p>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-3">
                <h3 class="text-lg font-medium text-gray-900">Быстрые ссылки</h3>
                <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                    <li><a href="{{ route('map.index') }}" class="text-indigo-600 hover:underline">Карта ДГУ</a></li>
                    <li><a href="{{ route('dgus.index') }}" class="text-indigo-600 hover:underline">Список ДГУ</a> — кнопки «Запустить» / «Остановить» на карточке (роли admin и operator)</li>
                </ul>
            </div>

            <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-sm text-gray-600 space-y-2">
                <p class="font-medium text-gray-800">Планируется</p>
                <p>Режимы работы, расписания, ручной ввод параметров и расширенные сценарии управления — отдельные экраны и API по мере доработки ТЗ.</p>
            </div>
        </div>
    </div>
</x-app-layout>
