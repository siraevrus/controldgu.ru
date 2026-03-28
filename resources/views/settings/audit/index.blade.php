<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Аудит действий</h2>
    </x-slot>
    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto text-sm">
                <table class="min-w-full">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-3 py-2 text-left">Время</th>
                            <th class="px-3 py-2 text-left">Пользователь</th>
                            <th class="px-3 py-2 text-left">Действие</th>
                            <th class="px-3 py-2 text-left">Объект</th>
                            <th class="px-3 py-2 text-left">IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                            <tr class="border-t">
                                <td class="px-3 py-2 whitespace-nowrap">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                <td class="px-3 py-2">{{ $log->user?->email ?? '—' }}</td>
                                <td class="px-3 py-2 font-mono text-xs">{{ $log->action }}</td>
                                <td class="px-3 py-2 text-xs">{{ $log->auditable_type ? class_basename($log->auditable_type).' #'.$log->auditable_id : '—' }}</td>
                                <td class="px-3 py-2 text-xs">{{ $log->ip_address ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $logs->links() }}</div>
        </div>
    </div>
</x-app-layout>
