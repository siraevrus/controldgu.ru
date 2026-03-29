<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Карта ДГУ</h2>
        <p class="text-sm text-gray-500 mt-1">Один маркер на установку; цвет по связи, ручному отключению и состоянию.</p>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg p-4 text-sm text-gray-700 space-y-3">
                <p class="text-xs text-gray-500">Снимите галочку, чтобы скрыть группу точек на карте.</p>
                <div class="flex flex-wrap gap-x-6 gap-y-2">
                    <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" id="map-filter-green" checked class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" data-map-filter="green" />
                        <span class="inline-flex items-center gap-2"><span class="inline-block w-3 h-3 rounded-full bg-green-600 shrink-0"></span> Связь ок, работает</span>
                    </label>
                    <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" id="map-filter-blue" checked class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" data-map-filter="blue" />
                        <span class="inline-flex items-center gap-2"><span class="inline-block w-3 h-3 rounded-full bg-blue-600 shrink-0"></span> Связь ок, остановлен</span>
                    </label>
                    <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" id="map-filter-orange" checked class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" data-map-filter="orange" />
                        <span class="inline-flex items-center gap-2"><span class="inline-block w-3 h-3 rounded-full bg-orange-500 shrink-0"></span> Нет связи ≤ {{ $staleMinutes }} мин</span>
                    </label>
                    <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" id="map-filter-red" checked class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" data-map-filter="red" />
                        <span class="inline-flex items-center gap-2"><span class="inline-block w-3 h-3 rounded-full bg-red-600 shrink-0"></span> Ручное откл. или ≥ {{ $longOfflineHours }} ч без данных</span>
                    </label>
                </div>
            </div>

            @if (count($markers) === 0)
                <div class="bg-white shadow-sm sm:rounded-lg p-8 text-center text-gray-500">
                    Нет ДГУ с заданными координатами. Укажите широту и долготу в карточке ДГУ.
                </div>
            @else
                <div id="map" class="w-full rounded-lg border border-gray-200 shadow-sm z-0" style="height: 56rem; min-height: 56rem;"></div>
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

            const markerEntries = [];
            markers.forEach((m) => {
                const fill = palette[m.color] || '#6b7280';
                const layer = L.circleMarker([m.lat, m.lng], {
                    radius: 11,
                    color: '#ffffff',
                    weight: 2,
                    fillColor: fill,
                    fillOpacity: 0.92,
                });
                const stateLabel = m.operational_state === 'running' ? 'Работает' : 'Остановлен';
                layer.bindPopup(
                    '<strong>' + (m.name || m.serial_number) + '</strong><br>' +
                    m.serial_number + '<br>' +
                    stateLabel + '<br>' +
                    '<a class="text-indigo-600 underline" href="' + m.url + '">Карточка</a>'
                );
                markerEntries.push({ color: m.color, layer: layer });
            });

            function filterChecked(color) {
                const el = document.querySelector('input[data-map-filter="' + color + '"]');
                return el ? el.checked : true;
            }

            function applyMapFilters() {
                const colors = ['green', 'blue', 'orange', 'red'];
                markerEntries.forEach(({ color, layer }) => {
                    if (filterChecked(color)) {
                        if (!map.hasLayer(layer)) {
                            layer.addTo(map);
                        }
                    } else if (map.hasLayer(layer)) {
                        map.removeLayer(layer);
                    }
                });

                const visible = markerEntries.filter(({ color }) => filterChecked(color));
                const bounds = visible.map(({ layer }) => {
                    const ll = layer.getLatLng();
                    return [ll.lat, ll.lng];
                });
                if (bounds.length === 1) {
                    map.setView(bounds[0], 12);
                } else if (bounds.length > 1) {
                    map.fitBounds(bounds, { padding: [40, 40] });
                }
            }

            document.querySelectorAll('input[data-map-filter]').forEach((input) => {
                input.addEventListener('change', applyMapFilters);
            });

            applyMapFilters();

            requestAnimationFrame(function () {
                map.invalidateSize();
            });
        </script>
        @endpush
    @endif
</x-app-layout>
