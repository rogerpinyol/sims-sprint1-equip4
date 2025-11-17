<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
  <meta charset="utf-8">
  <title>Afegir Vehicle - EcoMotion</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="/images/logo.jpg" type="image/jpeg">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <link rel="stylesheet" href="/css/brand.css" />
  <link rel="stylesheet" href="/assets/css/cookie-manager.css" />
  <style>
    .btn {
      display: inline-flex;
      align-items: center;
      padding: 0.5rem 1rem;
      border-radius: 0.375rem;
      font-weight: 500;
      transition: all 0.2s;
      cursor: pointer;
      border: none;
      text-decoration: none;
    }
    .btn-primary {
      background-color: #3b82f6;
      color: white;
    }
    .btn-primary:hover {
      background-color: #2563eb;
    }
    .btn-secondary {
      background-color: #64748b;
      color: white;
    }
    .btn-secondary:hover {
      background-color: #475569;
    }
  </style>
</head>
<body class="bg-slate-100 h-full">
<?php 
// Include helper functions
require_once __DIR__ . '/../../core/Controller.php';
if (!function_exists('e')) {
    function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('manager_base')) {
    function manager_base(): string {
        $mb = getenv('MANAGER_BASE') ?: '/ecomotion-manager';
        if ($mb === '' || $mb === false) return '/ecomotion-manager';
        if ($mb[0] !== '/') $mb = '/' . $mb;
        if ($mb !== '/' && substr($mb, -1) === '/') $mb = rtrim($mb, '/');
        return $mb;
    }
}
$active = 'vehicles'; 
?>
<div class="bg-slate-50 text-slate-900">
  <?php include __DIR__ . '/../partials/manager/mobileSidebar.php'; ?>

  <div class="min-h-screen grid grid-cols-1 md:grid-cols-[240px_1fr]">
    <?php include __DIR__ . '/../partials/manager/sidebar.php'; ?>

    <!-- Main -->
    <main class="flex flex-col">
      <?php include __DIR__ . '/../partials/manager/header.php'; ?>

      <div class="p-4 space-y-4">
        <div class="flex justify-between items-center">
          <h2 class="text-2xl font-bold">Afegir Nou Vehicle</h2>
          <a href="/vehicles" class="btn btn-secondary">Tornar</a>
        </div>

        <!-- Flash alerts are shown centrally under the navbar (partials/navbar.php) -->

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <form action="/vehicle/store" method="POST" class="bg-white shadow-lg rounded-lg p-4 space-y-4" novalidate>
        <?php if (!empty($_SESSION['csrf_token'])): ?>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <?php endif; ?>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block font-bold mb-1">VIN (17 caràcters)</label>
                <input type="text" name="vin" maxlength="17" minlength="17" required class="w-full px-3 py-1.5 border rounded-lg focus:ring-2 focus:ring-orange-500" placeholder="WF0XXXXXXXXXXXXXX" pattern="[A-HJ-NPR-Z0-9]{17}" title="17 alphanumeric characters (no I, O, Q)">
            </div>
            <div>
                <label class="block font-bold mb-1">Model</label>
                <input type="text" name="model" required minlength="2" maxlength="100" class="w-full px-3 py-1.5 border rounded-lg focus:ring-2 focus:ring-orange-500" placeholder="Model 3">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block font-bold mb-1">Capacitat Bateria (kWh)</label>
                <input type="number" step="0.1" min="0.1" max="200" name="battery_capacity" required class="w-full px-3 py-1.5 border rounded-lg" placeholder="75">
            </div>
            <div>
                <label class="block font-bold mb-1">Estat</label>
                <select name="status" required class="w-full px-3 py-1.5 border rounded-lg">
                    <option value="available">Disponible</option>
                    <option value="charging">Carregant</option>
                    <option value="booked">Reservat</option>
                    <option value="maintenance">Manteniment</option>
                    <option value="offline">Fora de servei</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block font-bold mb-1">Ubicació (POINT(lat lon))</label>
            <input type="text" name="location" required class="w-full px-3 py-1.5 border rounded-lg" placeholder="POINT(41.3851 2.1734)">
        </div>

        <div>
            <label class="block font-bold mb-1">Últim Manteniment (YYYY-MM-DD)</label>
            <input type="date" name="last_maintenance" class="w-full px-3 py-1.5 border rounded-lg">
        </div>

        <div>
            <label class="block font-bold mb-1">Dades Sensor (JSON)</label>
            <textarea name="sensor_data" rows="4" class="w-full px-3 py-1.5 border rounded-lg" placeholder='{"temp": 25, "battery_level": 90}'></textarea>
        </div>

        <div class="flex gap-4 pt-4 justify-center">
            <button type="submit" class="bg-brand hover:bg-brand-variant text-white font-semibold py-2 px-6 rounded-lg transition">
                Guardar Vehicle
            </button>
            <a href="/vehicles" class="bg-neutral-900 hover:bg-neutral-700 text-white font-semibold py-2 px-6 rounded-lg transition">
                Cancel·lar
            </a>
        </div>
        </form>

        <!-- Map column -->
        <div class="bg-white shadow-lg rounded-lg p-4">
            <h2 class="block font-bold mb-1">Ubicació al mapa</h2>
            <div id="create-map" style="height:520px; width:100%; border-radius:6px; overflow:hidden"></div>
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
    const map = L.map('create-map').setView(DEFAULT_CENTER, 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Marker that follows the mouse (preview)
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

    // Show hover marker
    map.on('mousemove', function(e){
        if (hoverMarker) {
            hoverMarker.setLatLng(e.latlng);
        } else {
            hoverMarker = L.circleMarker(e.latlng, {radius:6, color:'#ff6600', fill:true, fillOpacity:0.8}).addTo(map);
        }
    });

    // On click, set selected marker and update input
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
<script src="/assets/js/cookie-manager.js"></script>
<script src="/assets/js/vehicle-form.js"></script>

</script>

<script>
// Mobile sidebar toggle
const btnOpen = document.getElementById('btnOpenSidebar');
const btnClose = document.getElementById('btnCloseSidebar');
const mobileSidebar = document.getElementById('mobileSidebar');
const overlay = document.getElementById('mobileSidebarOverlay');
if (btnOpen) btnOpen.addEventListener('click', () => {
  if (mobileSidebar) mobileSidebar.classList.remove('-translate-x-full');
  if (overlay) { overlay.classList.remove('pointer-events-none', 'opacity-0'); }
});
if (btnClose) btnClose.addEventListener('click', () => {
  if (mobileSidebar) mobileSidebar.classList.add('-translate-x-full');
  if (overlay) { overlay.classList.add('pointer-events-none', 'opacity-0'); }
});
if (overlay) overlay.addEventListener('click', () => {
  if (mobileSidebar) mobileSidebar.classList.add('-translate-x-full');
  overlay.classList.add('pointer-events-none', 'opacity-0');
});
</script>
      </div>
    </main>
  </div>
</div>
</body>
</html>