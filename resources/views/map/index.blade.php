<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Карта ДГУ</h2>
        <p class="text-sm text-gray-500 mt-1">Один маркер на установку; цвет по связи, ручному отключению и состоянию (демо).</p>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white shadow-sm sm:rounded-lg p-4 text-sm text-gray-700 flex flex-wrap gap-4">
                <span class="inline-flex items-center gap-2"><span class="inline-block w-3 h-3 rounded-full bg-green-600"></span> Связь ок, работает</span>
                <span class="inline-flex items-center gap-2"><span class="inline-block w-3 h-3 rounded-full bg-blue-600"></span> Связь ок, остановлен</span>
                <span class="inline-flex items-center gap-2"><span class="inline-block w-3 h-3 rounded-full bg-orange-500"></span> Нет связи ≤ {{ $staleMinutes }} мин</span>
                <span class="inline-flex items-center gap-2"><span class="inline-block w-3 h-3 rounded-full bg-red-600"></span> Ручное откл. или ≥ {{ $longOfflineHours }} ч без данных</span>
            </div>

            @if (count($markers) === 0)
                <div class="bg-white shadow-sm sm:rounded-lg p-8 text-center text-gray-500">
                    Нет ДГУ с заданными координатами. Укажите широту и долготу в карточке ДГУ.
                </div>
            @else
                <div id="map" class="w-full rounded-lg border border-gray-200 shadow-sm z-0" style="min-height: 28rem;"></div>
            @endif
        </div>
    </div>

    @if (count($markers) > 0)
        @push('scripts')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css">
        <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            const markers = @json($markers);
            const palette = { red: '#dc2626', orange: '#ea580c', green: '#16a34a', blue: '#2563eb' };
            const map = L.map('map');
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);

            const bounds = [];
            markers.forEach((m) => {
                const fill = palette[m.color] || '#6b7280';
                const layer = L.circleMarker([m.lat, m.lng], {
                    radius: 11,
                    color: '#ffffff',
                    weight: 2,
                    fillColor: fill,
                    fillOpacity: 0.92,
                }).addTo(map);
                const stateLabel = m.operational_state === 'running' ? 'Работает' : 'Остановлен';
                layer.bindPopup(
                    '<strong>' + (m.name || m.serial_number) + '</strong><br>' +
                    m.serial_number + '<br>' +
                    stateLabel + '<br>' +
                    '<a class="text-indigo-600 underline" href="' + m.url + '">Карточка</a>'
                );
                bounds.push([m.lat, m.lng]);
            });

            if (bounds.length === 1) {
                map.setView(bounds[0], 12);
            } else {
                map.fitBounds(bounds, { padding: [40, 40] });
            }
        </script>
        @endpush
    @endif
</x-app-layout>
