<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Панель мониторинга ДГУ
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">Всего ДГУ</div>
                    <div class="text-3xl font-semibold text-gray-900">{{ $kpiTotal }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">Активно по связи</div>
                    <div class="text-3xl font-semibold text-green-700">{{ $kpiFresh }}</div>
                    <div class="text-xs text-gray-400 mt-1">Свежая телеметрия ≤ {{ $staleMinutes }} мин</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">Неактивно</div>
                    <div class="text-3xl font-semibold text-amber-700">{{ $kpiInactive }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">Активные тревоги</div>
                    <div class="text-3xl font-semibold text-red-700">{{ $kpiOpenAlerts }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
