<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Пригласить пользователя</h2>
    </x-slot>
    <div class="py-8">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <form method="post" action="{{ route('settings.users.store') }}" class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4 text-sm">
                @csrf
                <div>
                    <x-input-label for="name" value="ФИО" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="email" value="Email" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="phone" value="Телефон" />
                    <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone')" />
                </div>
                <div>
                    <x-input-label for="job_title" value="Должность" />
                    <x-text-input id="job_title" name="job_title" type="text" class="mt-1 block w-full" :value="old('job_title')" />
                </div>
                <div>
                    <x-input-label for="role" value="Роль" />
                    <select id="role" name="role" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <option value="operator" @selected(old('role') === 'operator')>Оператор (только просмотр / без админ-настроек)</option>
                        <option value="admin" @selected(old('role') === 'admin')>Администратор</option>
                    </select>
                    <x-input-error :messages="$errors->get('role')" class="mt-2" />
                </div>
                <p class="text-xs text-gray-500">На email уйдёт ссылка для установки пароля (стандартный сброс Laravel).</p>
                <div class="flex gap-3">
                    <x-primary-button type="submit">Создать и отправить ссылку</x-primary-button>
                    <a href="{{ route('settings.users.index') }}" class="self-center text-gray-600 hover:underline">Назад</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
