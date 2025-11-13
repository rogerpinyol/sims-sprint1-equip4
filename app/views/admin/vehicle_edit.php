<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
?>

<?php if (!isset($vehicle)) { $vehicle = null; } ?>

<div class="container mx-auto p-4 max-w-6xl">
    <h1 class="text-3xl font-bold mb-6 text-orange-600 text-center">Editar Vehicle</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <form action="/vehicle/update" method="POST" class="bg-white shadow-lg rounded-lg p-4 space-y-4">
        <?php if (!empty($_SESSION['csrf_token'])): ?>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <?php endif; ?>

        <input type="hidden" name="id" value="<?= htmlspecialchars($vehicle['id'] ?? '') ?>">

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block font-bold mb-1">VIN</label>
                <input type="text" name="vin" value="<?= htmlspecialchars($vehicle['vin'] ?? '') ?>" class="w-full px-3 py-1.5 border rounded-lg">
            </div>
            <div>
                <label class="block font-bold mb-1">Model</label>
                <input type="text" name="model" value="<?= htmlspecialchars($vehicle['model'] ?? '') ?>" class="w-full px-3 py-1.5 border rounded-lg">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block font-bold mb-1">Capacitat Bateria (kWh)</label>
                <input type="number" step="0.01" id="battery_capacity" name="battery_capacity" class="w-full px-3 py-1.5 border rounded-lg" value="<?= htmlspecialchars($vehicle['battery_capacity'] ?? '0') ?>">
            </div>
            <div>
                <label class="block font-bold mb-1">Estat</label>
                <select name="status" class="w-full px-3 py-1.5 border rounded-lg">
                    <?php $statuses = ['available','charging','booked','maintenance','offline'];
                    $current = $vehicle['status'] ?? '';
                    foreach ($statuses as $s): ?>
                        <option value="<?= $s ?>" <?= ($current == $s) ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div>
            <label class="block font-bold mb-1">Ubicació (POINT(lat lon))</label>
            <input type="text" name="location" class="w-full px-3 py-1.5 border rounded-lg" value="<?= htmlspecialchars($vehicle['location'] ?? 'POINT(0 0)') ?>">
        </div>

        <div>
            <label class="block font-bold mb-1">Últim Manteniment</label>
            <input type="date" name="last_maintenance" class="w-full px-3 py-1.5 border rounded-lg" value="<?= htmlspecialchars($vehicle['last_maintenance'] ?? '') ?>">
        </div>

        <div>
            <label class="block font-bold mb-1">Dades Sensor (JSON)</label>
            <textarea name="sensor_data" rows="4" class="w-full px-3 py-1.5 border rounded-lg"><?= htmlspecialchars($vehicle['sensor_data'] ?? '{}') ?></textarea>
        </div>

        <div class="flex gap-4 pt-4 justify-center">
            <button type="submit" class="bg-brand hover:bg-brand-variant text-white font-semibold py-2 px-6 rounded-lg">Actualizar</button>
            <a href="/vehicles" class="bg-neutral-900 hover:bg-neutral-700 text-white font-semibold py-2 px-6 rounded-lg">Cancel·lar</a>
        </div>
        </form>

        <!-- Map column for edit view -->
        <div class="bg-white shadow-lg rounded-lg p-4">
            <h2 class="block font-bold mb-1">Ubicació al mapa</h2>
            <div id="edit-map" style="height:520px; width:100%; border-radius:6px; overflow:hidden"></div>
            <p class="text-sm text-neutral-600 mt-2">Fes clic al mapa per seleccionar la ubicació; el camp <code>Ubicació</code> s'emplenarà automàticament.</p>
        </div>
    </div>
</div>

<!-- Leaflet CSS/JS (CDN) - using jsDelivr to avoid SRI/blocking issues -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css">
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
(function(){
    // Default center: Amposta (approx)
    const DEFAULT_CENTER = [40.7190, 0.5160];
    const map = L.map('edit-map').setView(DEFAULT_CENTER, 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    let hoverMarker = null;
    let selectedMarker = null;

    // Use project logo (stored at repo root) as marker icon
    const carIcon = L.icon({
        iconUrl: '/Logo%20EcoMotion%20Transparent%202.png',
        iconSize: [48, 30],
        iconAnchor: [24, 30],
        popupAnchor: [0, -28]
    });

    function formatPoint(lat, lon){
        return `POINT(${lat.toFixed(6)} ${lon.toFixed(6)})`;
    }

    // If location input already has a POINT, initialize marker there
    const locInput = document.querySelector('input[name="location"]');
    if (locInput && locInput.value) {
        const m = locInput.value.match(/POINT\s*\(([-0-9\.]+)\s+([-0-9\.]+)\)/i);
        if (m) {
            const lat = parseFloat(m[1]);
            const lon = parseFloat(m[2]);
            selectedMarker = L.marker([lat, lon], {icon: carIcon}).addTo(map);
            map.setView([lat, lon], 14);
        }
    }

    // Try to center on user geolocation if permitted
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(pos){
            const lat = pos.coords.latitude;
            const lon = pos.coords.longitude;
            map.setView([lat, lon], 14);
        }, function(){ /* ignore errors, keep default */ });
    }

    map.on('mousemove', function(e){
        if (hoverMarker) {
            hoverMarker.setLatLng(e.latlng);
        } else {
            hoverMarker = L.circleMarker(e.latlng, {radius:6, color:'#ff6600', fill:true, fillOpacity:0.8}).addTo(map);
        }
    });

    map.on('click', function(e){
        const lat = e.latlng.lat;
        const lon = e.latlng.lng;
        if (selectedMarker) selectedMarker.setLatLng(e.latlng);
        else selectedMarker = L.marker(e.latlng, {icon: carIcon}).addTo(map);
        if (locInput) locInput.value = formatPoint(lat, lon);
    });

})();
</script>

<script src="/assets/js/leaflet-check.js"></script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>