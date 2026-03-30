<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Панель мониторинга ДГУ
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 [&>div]:min-w-0">
                <div class="bg-slate-100 border border-slate-200/90 overflow-hidden shadow-sm sm:rounded-lg p-4 sm:p-6 ring-1 ring-slate-200/60">
                    <div class="text-sm font-medium text-slate-600">Всего ДГУ</div>
                    <div class="text-2xl sm:text-3xl font-semibold text-slate-900 tabular-nums">{{ $kpiTotal }}</div>
                    <a href="{{ route('dgus.index') }}" class="text-xs font-medium text-slate-700 hover:text-slate-900 hover:underline mt-2 inline-block">Список ДГУ</a>
                </div>
                <div class="bg-emerald-50 border border-emerald-200/90 overflow-hidden shadow-sm sm:rounded-lg p-4 sm:p-6 ring-1 ring-emerald-100">
                    <div class="text-sm font-medium text-emerald-800/80">Активно по связи</div>
                    <div class="text-2xl sm:text-3xl font-semibold text-emerald-800 tabular-nums">{{ $kpiFresh }}</div>
                    <div class="text-xs text-emerald-700/70 mt-1">Свежая телеметрия ≤ {{ $staleMinutes }} мин</div>
                    <a href="{{ route('dgus.index', ['link' => 'fresh']) }}" class="text-xs font-medium text-emerald-900 hover:underline mt-2 inline-block">Список ДГУ</a>
                </div>
                <div class="bg-amber-50 border border-amber-200/90 overflow-hidden shadow-sm sm:rounded-lg p-4 sm:p-6 ring-1 ring-amber-100">
                    <div class="text-sm font-medium text-amber-900/75">Неактивно</div>
                    <div class="text-2xl sm:text-3xl font-semibold text-amber-800 tabular-nums">{{ $kpiInactive }}</div>
                    <a href="{{ route('dgus.index', ['link' => 'stale']) }}" class="text-xs font-medium text-amber-950 hover:underline mt-2 inline-block">Список ДГУ</a>
                </div>
                <div class="bg-rose-50 border border-rose-200/90 overflow-hidden shadow-sm sm:rounded-lg p-4 sm:p-6 ring-1 ring-rose-100">
                    <div class="text-sm font-medium text-rose-800/85">Активные тревоги</div>
                    <div class="text-2xl sm:text-3xl font-semibold text-rose-700 tabular-nums">{{ $kpiOpenAlerts }}</div>
                    <a href="{{ route('alerts.index') }}" class="text-xs font-medium text-rose-900 hover:underline mt-2 inline-block">Журнал тревог</a>
                </div>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 [&>div]:min-w-0">
                <div class="bg-red-50 border border-red-200/90 overflow-hidden shadow-sm sm:rounded-lg p-4 sm:p-6 ring-1 ring-red-100">
                    <div class="text-sm font-medium text-red-900/80">Долго без данных</div>
                    <div class="text-2xl sm:text-3xl font-semibold text-red-700 tabular-nums">{{ $kpiLongOffline }}</div>
                    <div class="text-xs text-red-800/65 mt-1">Нет телеметрии ≥ {{ $longOfflineHours }} ч</div>
                    <div class="mt-2 flex flex-wrap gap-x-3 gap-y-1">
                        <a href="{{ route('dgus.index', ['link' => 'long_offline']) }}" class="text-xs font-medium text-red-900 hover:underline">Список ДГУ</a>
                        <a href="{{ route('map.index') }}" class="text-xs font-medium text-red-900 hover:underline">Карта</a>
                    </div>
                </div>
                <div class="bg-lime-50 border border-lime-200/90 overflow-hidden shadow-sm sm:rounded-lg p-4 sm:p-6 ring-1 ring-lime-100">
                    <div class="text-sm font-medium text-lime-900/80">В работе</div>
                    <div class="text-2xl sm:text-3xl font-semibold text-lime-900 tabular-nums">{{ $kpiRunning }}</div>
                    <a href="{{ route('dgus.index', ['operational' => 'running']) }}" class="text-xs font-medium text-lime-950 hover:underline mt-2 inline-block">Список ДГУ</a>
                </div>
                <div class="bg-zinc-100 border border-zinc-200/90 overflow-hidden shadow-sm sm:rounded-lg p-4 sm:p-6 ring-1 ring-zinc-200/50">
                    <div class="text-sm font-medium text-zinc-600">Остановлены</div>
                    <div class="text-2xl sm:text-3xl font-semibold text-zinc-800 tabular-nums">{{ $kpiStopped }}</div>
                    <a href="{{ route('dgus.index', ['operational' => 'stopped']) }}" class="text-xs font-medium text-zinc-800 hover:underline mt-2 inline-block">Список ДГУ</a>
                </div>
                <div class="bg-orange-50 border border-orange-200/90 overflow-hidden shadow-sm sm:rounded-lg p-4 sm:p-6 ring-1 ring-orange-100">
                    <div class="text-sm font-medium text-orange-900/80">Ручное отключение</div>
                    <div class="text-2xl sm:text-3xl font-semibold text-orange-800 tabular-nums">{{ $kpiManualDisabled }}</div>
                    <a href="{{ route('dgus.index', ['flags' => 'manual']) }}" class="text-xs font-medium text-orange-950 hover:underline mt-2 inline-block">Список ДГУ</a>
                </div>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 [&>div]:min-w-0">
                <div class="bg-sky-50 border border-sky-200/90 overflow-hidden shadow-sm sm:rounded-lg p-4 sm:p-6 ring-1 ring-sky-100">
                    <div class="text-sm font-medium text-sky-900/80">Без координат</div>
                    <div class="text-2xl sm:text-3xl font-semibold text-sky-900 tabular-nums">{{ $kpiWithoutCoords }}</div>
                    <div class="text-xs text-sky-800/65 mt-1">Не попадут на карту</div>
                    <div class="mt-2 flex flex-wrap gap-x-3 gap-y-1">
                        <a href="{{ route('dgus.index', ['flags' => 'no_coords']) }}" class="text-xs font-medium text-sky-950 hover:underline">Список ДГУ</a>
                        <a href="{{ route('map.index') }}" class="text-xs font-medium text-sky-950 hover:underline">Карта</a>
                    </div>
                </div>
                <div class="bg-violet-50 border border-violet-200/90 overflow-hidden shadow-sm sm:rounded-lg p-4 sm:p-6 ring-1 ring-violet-100">
                    <div class="text-sm font-medium text-violet-900/80">Тревоги за 24 ч</div>
                    <div class="text-2xl sm:text-3xl font-semibold text-violet-900 tabular-nums">{{ $kpiAlertsLast24h }}</div>
                    <div class="text-xs text-violet-800/65 mt-1">Все статусы</div>
                    <a href="{{ route('alerts.index') }}" class="text-xs font-medium text-violet-950 hover:underline mt-2 inline-block">Журнал тревог</a>
                </div>
                <div class="bg-cyan-50 border border-cyan-200/90 overflow-hidden shadow-sm sm:rounded-lg p-4 sm:p-6 ring-1 ring-cyan-100 col-span-2 lg:col-span-1">
                    <div class="text-sm font-medium text-cyan-900/80">Снимков за час</div>
                    <div class="text-2xl sm:text-3xl font-semibold text-cyan-900 tabular-nums">{{ $kpiSnapshotsLastHour }}</div>
                    <div class="text-xs text-cyan-800/65 mt-1">По всем ДГУ</div>
                    <a href="{{ route('dgus.index') }}" class="text-xs font-medium text-cyan-950 hover:underline mt-2 inline-block">Список ДГУ</a>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-rose-50/40 border border-rose-200/70 shadow-sm sm:rounded-lg overflow-hidden ring-1 ring-rose-100/80">
                    <div class="px-6 py-4 border-b border-rose-200/60 bg-rose-100/50 flex items-center justify-between gap-4">
                        <h3 class="text-base font-medium text-rose-950">Открытые тревоги</h3>
                        <a href="{{ route('alerts.index') }}" class="text-sm font-medium text-rose-900 hover:underline shrink-0">В журнал</a>
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

                <div class="bg-amber-50/50 border border-amber-200/70 shadow-sm sm:rounded-lg p-6 ring-1 ring-amber-100/80">
                    <div class="flex items-center justify-between gap-4 mb-4">
                        <h3 class="text-base font-medium text-amber-950">Уведомления</h3>
                        <a href="{{ route('notifications.index') }}" class="text-sm font-medium text-amber-900 hover:underline shrink-0">Все уведомления</a>
                    </div>
                    <div class="text-3xl font-semibold {{ $unreadNotifications > 0 ? 'text-amber-800' : 'text-amber-400' }}">{{ $unreadNotifications }}</div>
                    <p class="text-sm text-amber-900/70 mt-2">Непрочитанных в вашем аккаунте</p>
                </div>
            </div>

            <div class="bg-indigo-50/40 border border-indigo-200/70 shadow-sm sm:rounded-lg overflow-hidden ring-1 ring-indigo-100/80">
                <div class="px-6 py-4 border-b border-indigo-200/60 bg-indigo-100/40 flex items-center justify-between gap-4">
                    <h3 class="text-base font-medium text-indigo-950">Топ регионов по числу ДГУ</h3>
                    <a href="{{ route('dgus.index') }}" class="text-sm font-medium text-indigo-900 hover:underline shrink-0">Все ДГУ</a>
                </div>
                @if ($regionsTop->isEmpty())
                    <p class="px-6 py-6 text-sm text-gray-500">Нет заполненных регионов.</p>
                @else
                    <table class="min-w-full text-sm">
                        <thead class="bg-indigo-100/50 text-indigo-900/75">
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
                <div class="bg-teal-50/35 border border-teal-200/60 shadow-sm sm:rounded-lg overflow-hidden ring-1 ring-teal-100/70">
                    <div class="px-6 py-4 border-b border-teal-200/50 bg-teal-100/35 flex items-center justify-between gap-4">
                        <h3 class="text-base font-medium text-teal-950">Последние действия (аудит)</h3>
                        <a href="{{ route('settings.audit.index') }}" class="text-sm font-medium text-teal-900 hover:underline shrink-0">Полный журнал</a>
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
