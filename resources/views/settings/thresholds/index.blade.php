<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Глобальные пороги</h2>
    </x-slot>
    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-green-800 text-sm">{{ session('status') }}</div>
            @endif
            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-4 py-2 text-left">Параметр</th>
                            <th class="px-4 py-2 text-left">Min</th>
                            <th class="px-4 py-2 text-left">Max</th>
                            <th class="px-4 py-2 text-left">Активен</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            @php $label = $catalog[$row->parameter_slug]['label'] ?? $row->parameter_slug; @endphp
                            <tr class="border-t">
                                <td class="px-4 py-2">{{ $label }}</td>
                                <td class="px-4 py-2">{{ $row->min_value ?? '—' }}</td>
                                <td class="px-4 py-2">{{ $row->ignore_max ? '∞' : ($row->max_value ?? '—') }}</td>
                                <td class="px-4 py-2">{{ $row->is_active ? 'Да' : 'Нет' }}</td>
                                <td class="px-4 py-2 text-right">
                                    <a href="{{ route('settings.thresholds.edit', $row) }}" class="text-indigo-600 hover:underline">Изменить</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
