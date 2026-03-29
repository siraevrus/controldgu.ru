<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Пользователи</h2>
            <a href="{{ route('settings.users.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-md hover:bg-gray-700">Пригласить</a>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-green-800 text-sm">{{ session('status') }}</div>
            @endif
            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto text-sm">
                <table class="min-w-full">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-3 py-2 text-left">Имя</th>
                            <th class="px-3 py-2 text-left">Email</th>
                            <th class="px-3 py-2 text-left">Роли</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $u)
                            <tr class="border-t">
                                <td class="px-3 py-2">{{ $u->name }}</td>
                                <td class="px-3 py-2">{{ $u->email }}</td>
                                <td class="px-3 py-2">{{ $u->roles->pluck('name')->join(', ') ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div>{{ $users->links() }}</div>
        </div>
    </div>
</x-app-layout>
