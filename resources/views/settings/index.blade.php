<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Настройки</h2>
    </x-slot>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <a href="{{ route('settings.thresholds.index') }}"
                    class="block rounded-lg border border-gray-200 bg-white p-6 shadow-sm transition hover:border-indigo-300 hover:shadow-md">
                    <h3 class="text-base font-semibold text-gray-900">Глобальные пороги</h3>
                    <p class="mt-2 text-sm text-gray-600">Пороги телеметрии по параметрам для всех ДГУ.</p>
                </a>
                <a href="{{ route('settings.audit.index') }}"
                    class="block rounded-lg border border-gray-200 bg-white p-6 shadow-sm transition hover:border-indigo-300 hover:shadow-md">
                    <h3 class="text-base font-semibold text-gray-900">Аудит действий</h3>
                    <p class="mt-2 text-sm text-gray-600">Журнал действий пользователей в системе.</p>
                </a>
                <a href="{{ route('settings.logs.index') }}"
                    class="block rounded-lg border border-gray-200 bg-white p-6 shadow-sm transition hover:border-indigo-300 hover:shadow-md">
                    <h3 class="text-base font-semibold text-gray-900">Системные логи</h3>
                    <p class="mt-2 text-sm text-gray-600">Сообщения приложения и фоновых процессов.</p>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
