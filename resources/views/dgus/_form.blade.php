<div class="space-y-6">
    <div>
        <x-input-label for="name" value="Название (опционально)" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $dgu->name ?? '')" />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="serial_number" value="Серийный номер" />
        <x-text-input id="serial_number" name="serial_number" type="text" class="mt-1 block w-full" :value="old('serial_number', $dgu->serial_number ?? '')" required />
        <x-input-error :messages="$errors->get('serial_number')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="address" value="Адрес" />
        <textarea id="address" name="address" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('address', $dgu->address ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('address')" class="mt-2" />
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="latitude" value="Широта" />
            <x-text-input id="latitude" name="latitude" type="text" class="mt-1 block w-full" :value="old('latitude', $dgu->latitude ?? '')" />
            <x-input-error :messages="$errors->get('latitude')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="longitude" value="Долгота" />
            <x-text-input id="longitude" name="longitude" type="text" class="mt-1 block w-full" :value="old('longitude', $dgu->longitude ?? '')" />
            <x-input-error :messages="$errors->get('longitude')" class="mt-2" />
        </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="responsible_name" value="Ответственный" />
            <x-text-input id="responsible_name" name="responsible_name" type="text" class="mt-1 block w-full" :value="old('responsible_name', $dgu->responsible_name ?? '')" />
        </div>
        <div>
            <x-input-label for="contact_phone" value="Контактный телефон" />
            <x-text-input id="contact_phone" name="contact_phone" type="text" class="mt-1 block w-full" autocomplete="tel" placeholder="+7 999-123-45-67" x-phone-mask :value="old('contact_phone', $dgu->contact_phone ?? '')" />
        </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <x-input-label for="nominal_power_kw" value="Мощность номинальная, кВт" />
            <x-text-input id="nominal_power_kw" name="nominal_power_kw" type="text" class="mt-1 block w-full" :value="old('nominal_power_kw', $dgu->nominal_power_kw ?? '')" />
        </div>
        <div>
            <x-input-label for="model_name" value="Модель" />
            <x-text-input id="model_name" name="model_name" type="text" class="mt-1 block w-full" :value="old('model_name', $dgu->model_name ?? '')" />
        </div>
        <div>
            <x-input-label for="region" value="Регион" />
            <x-text-input id="region" name="region" type="text" class="mt-1 block w-full" :value="old('region', $dgu->region ?? '')" />
        </div>
    </div>
    <div>
        <x-input-label for="tags" value="Теги (через запятую)" />
        @php
            $tagVal = old('tags_input');
            if ($tagVal === null && isset($dgu) && is_array($dgu->tags)) {
                $tagVal = implode(', ', $dgu->tags);
            }
        @endphp
        <x-text-input id="tags" name="tags_input" type="text" class="mt-1 block w-full" :value="$tagVal ?? ''" />
        <p class="text-xs text-gray-500 mt-1">Сохраняются как список строк.</p>
    </div>
    <div class="flex items-center gap-2">
        <input id="is_manually_disabled" type="hidden" name="is_manually_disabled" value="0" />
        <input id="is_manually_disabled_cb" type="checkbox" name="is_manually_disabled" value="1" class="rounded border-gray-300"
            @checked(old('is_manually_disabled', $dgu->is_manually_disabled ?? false)) />
        <x-input-label for="is_manually_disabled_cb" value="Ручное отключение на карте / учёте" />
    </div>
    <div>
        <x-input-label for="operational_state" value="Состояние" />
        <select id="operational_state" name="operational_state" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            <option value="running" @selected(old('operational_state', $dgu->operational_state ?? 'stopped') === 'running')>Работает</option>
            <option value="stopped" @selected(old('operational_state', $dgu->operational_state ?? 'stopped') === 'stopped')>Остановлен</option>
        </select>
    </div>
</div>
