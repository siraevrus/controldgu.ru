<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Панель мониторинга ДГУ
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 [&>div]:min-w-0">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 sm:p-6">
                    <div class="text-sm text-gray-500">Всего ДГУ</div>
                    <div class="text-2xl sm:text-3xl font-semibold text-gray-900 tabular-nums">{{ $kpiTotal }}</div>
                    <a href="{{ route('dgus.index') }}" class="text-xs text-indigo-600 hover:underline mt-2 inline-block">Список ДГУ</a>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 sm:p-6">
                    <div class="text-sm text-gray-500">Активно по связи</div>
                    <div class="text-2xl sm:text-3xl font-semibold text-green-700 tabular-nums">{{ $kpiFresh }}</div>
                    <div class="text-xs text-gray-400 mt-1">Свежая телеметрия ≤ {{ $staleMinutes }} мин</div>
                    <a href="{{ route('dgus.index', ['link' => 'fresh']) }}" class="text-xs text-indigo-600 hover:underline mt-2 inline-block">Список ДГУ</a>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 sm:p-6">
                    <div class="text-sm text-gray-500">Неактивно</div>
                    <div class="text-2xl sm:text-3xl font-semibold text-amber-700 tabular-nums">{{ $kpiInactive }}</div>
                    <a href="{{ route('dgus.index', ['link' => 'stale']) }}" class="text-xs text-indigo-600 hover:underline mt-2 inline-block">Список ДГУ</a>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 sm:p-6">
                    <div class="text-sm text-gray-500">Активные тревоги</div>
                    <div class="text-2xl sm:text-3xl font-semibold text-red-700 tabular-nums">{{ $kpiOpenAlerts }}</div>
                    <a href="{{ route('alerts.index') }}" class="text-xs text-indigo-600 hover:underline mt-2 inline-block">Журнал тревог</a>
                </div>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 [&>div]:min-w-0">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 sm:p-6">
                    <div class="text-sm text-gray-500">Долго без данных</div>
                    <div class="text-2xl sm:text-3xl font-semibold text-red-700 tabular-nums">{{ $kpiLongOffline }}</div>
                    <div class="text-xs text-gray-400 mt-1">Нет телеметрии ≥ {{ $longOfflineHours }} ч</div>
                    <div class="mt-2 flex flex-wrap gap-x-3 gap-y-1">
                        <a href="{{ route('dgus.index', ['link' => 'long_offline']) }}" class="text-xs text-indigo-600 hover:underline">Список ДГУ</a>
                        <a href="{{ route('map.index') }}" class="text-xs text-indigo-600 hover:underline">Карта</a>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 sm:p-6">
                    <div class="text-sm text-gray-500">В работе</div>
                    <div class="text-2xl sm:text-3xl font-semibold text-green-800 tabular-nums">{{ $kpiRunning }}</div>
                    <a href="{{ route('dgus.index', ['operational' => 'running']) }}" class="text-xs text-indigo-600 hover:underline mt-2 inline-block">Список ДГУ</a>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 sm:p-6">
                    <div class="text-sm text-gray-500">Остановлены</div>
                    <div class="text-2xl sm:text-3xl font-semibold text-gray-800 tabular-nums">{{ $kpiStopped }}</div>
                    <a href="{{ route('dgus.index', ['operational' => 'stopped']) }}" class="text-xs text-indigo-600 hover:underline mt-2 inline-block">Список ДГУ</a>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 sm:p-6">
                    <div class="text-sm text-gray-500">Ручное отключение</div>
                    <div class="text-2xl sm:text-3xl font-semibold text-rose-800 tabular-nums">{{ $kpiManualDisabled }}</div>
                    <a href="{{ route('dgus.index', ['flags' => 'manual']) }}" class="text-xs text-indigo-600 hover:underline mt-2 inline-block">Список ДГУ</a>
                </div>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 [&>div]:min-w-0">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 sm:p-6">
                    <div class="text-sm text-gray-500">Без координат</div>
                    <div class="text-2xl sm:text-3xl font-semibold text-gray-900 tabular-nums">{{ $kpiWithoutCoords }}</div>
                    <div class="text-xs text-gray-400 mt-1">Не попадут на карту</div>
                    <div class="mt-2 flex flex-wrap gap-x-3 gap-y-1">
                        <a href="{{ route('dgus.index', ['flags' => 'no_coords']) }}" class="text-xs text-indigo-600 hover:underline">Список ДГУ</a>
                        <a href="{{ route('map.index') }}" class="text-xs text-indigo-600 hover:underline">Карта</a>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 sm:p-6">
                    <div class="text-sm text-gray-500">Тревоги за 24 ч</div>
                    <div class="text-2xl sm:text-3xl font-semibold text-gray-900 tabular-nums">{{ $kpiAlertsLast24h }}</div>
                    <div class="text-xs text-gray-400 mt-1">Все статусы</div>
                    <a href="{{ route('alerts.index') }}" class="text-xs text-indigo-600 hover:underline mt-2 inline-block">Журнал тревог</a>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 sm:p-6">
                    <div class="text-sm text-gray-500">Снимков за час</div>
                    <div class="text-2xl sm:text-3xl font-semibold text-gray-900 tabular-nums">{{ $kpiSnapshotsLastHour }}</div>
                    <div class="text-xs text-gray-400 mt-1">По всем ДГУ</div>
                    <a href="{{ route('dgus.index') }}" class="text-xs text-indigo-600 hover:underline mt-2 inline-block">Список ДГУ</a>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-4">
                        <h3 class="text-base font-medium text-gray-900">Открытые тревоги</h3>
                        <a href="{{ route('alerts.index') }}" class="text-sm text-indigo-600 hover:underline shrink-0">В журнал</a>
                    </div>
                    @if ($recentOpenAlerts->isEmpty())
                        <p class="px-6 py-8 text-sm text-gray-500 text-center">Открытых тревог нет.</p>
                    @else
                        <ul class="divide-y divide-gray-100 text-sm">
                            @foreach ($recentOpenAlerts as $alert)
                                <li class="px-6 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                    <div class="min-w-0">
                                        <a href="{{ route('alerts.show', $alert) }}" class="font-medium text-indigo-600 hover:underline">{{ $alert->title }}</a>
                                        <div class="text-gray-600 text-xs mt-0.5">
                                            {{ $alert->dgu?->serial_number ?? 'ДГУ' }}
                                            · {{ $alert->triggered_at->timezone($tz)->format('d.m.Y H:i') }}
                                        </div>
                                    </div>
                                    @if ($alert->dgu)
                                        <a href="{{ route('dgus.show', $alert->dgu) }}" class="text-xs text-gray-500 hover:text-gray-800 shrink-0">Карточка ДГУ</a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between gap-4 mb-4">
                        <h3 class="text-base font-medium text-gray-900">Уведомления</h3>
                        <a href="{{ route('notifications.index') }}" class="text-sm text-indigo-600 hover:underline shrink-0">Все уведомления</a>
                    </div>
                    <div class="text-3xl font-semibold {{ $unreadNotifications > 0 ? 'text-amber-700' : 'text-gray-400' }}">{{ $unreadNotifications }}</div>
                    <p class="text-sm text-gray-500 mt-2">Непрочитанных в вашем аккаунте</p>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-4">
                    <h3 class="text-base font-medium text-gray-900">Топ регионов по числу ДГУ</h3>
                    <a href="{{ route('dgus.index') }}" class="text-sm text-indigo-600 hover:underline shrink-0">Все ДГУ</a>
                </div>
                @if ($regionsTop->isEmpty())
                    <p class="px-6 py-6 text-sm text-gray-500">Нет заполненных регионов.</p>
                @else
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-6 py-2 text-left font-medium">Регион</th>
                                <th class="px-6 py-2 text-right font-medium w-24">ДГУ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($regionsTop as $row)
                                <tr>
                                    <td class="px-6 py-2 text-gray-900">
                                        <a href="{{ route('dgus.index', ['region' => $row->region]) }}" class="text-indigo-600 hover:underline">{{ $row->region }}</a>
                                    </td>
                                    <td class="px-6 py-2 text-right tabular-nums text-gray-700">{{ $row->c }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            @if ($recentAuditLogs->isNotEmpty())
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-4">
                        <h3 class="text-base font-medium text-gray-900">Последние действия (аудит)</h3>
                        <a href="{{ route('settings.audit.index') }}" class="text-sm text-indigo-600 hover:underline shrink-0">Полный журнал</a>
                    </div>
                    <ul class="divide-y divide-gray-100 text-sm">
                        @foreach ($recentAuditLogs as $log)
                            <li class="px-6 py-3 flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-1">
                                <div>
                                    <span class="font-mono text-xs text-gray-800">{{ $log->action }}</span>
                                    <span class="text-gray-500 text-xs sm:ml-2">{{ $log->user?->email ?? '—' }}</span>
                                </div>
                                <time class="text-xs text-gray-400 tabular-nums shrink-0" datetime="{{ $log->created_at->toIso8601String() }}">{{ $log->created_at->format('d.m.Y H:i:s') }}</time>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
