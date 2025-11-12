<?php
function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
$user = $user ?? ['name' => 'Cliente', 'email' => ''];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>EcoMotion - Mapa de Vehículos</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Leaflet CSS & JS -->
    <!-- Leaflet (SRI removed to avoid blocking if hashes mismatch) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <!-- MarkerCluster plugin -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
  <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    <style>
      html, body { height: 100%; }
      #map { width: 100%; height: 100%; min-height: 60vh; }
      .leaflet-popup-content-wrapper { border-radius: 0.75rem; }
    </style>
  <style>
    html, body { height:100%; }
    #map { width:100%; height:100%; }
    .leaflet-popup-content-wrapper { border-radius: 0.75rem; }
  </style>
</head>
<body class="bg-slate-100 text-slate-800 font-sans h-full transition-colors" id="appBody">
  <!-- Layout: sidebar (drawer) + map -->
  <div class="h-full flex flex-col">
  <header class="flex items-center justify-between px-4 py-3 bg-white border-b border-slate-200 shadow-sm">
      <div class="flex items-center gap-3">
  <button id="btnOpenSidebar" class="p-2 rounded-md bg-slate-200 hover:bg-slate-300" aria-label="Menú">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5m-16.5 6h16.5m-16.5 6h16.5" />
          </svg>
        </button>
  <h1 class="text-lg font-semibold tracking-tight">EcoMotion</h1>
      </div>
      <div class="flex items-center gap-2 text-sm">
        <span class="hidden sm:inline">Hola, <?= e($user['name']) ?></span>
        <button id="themeToggle" type="button" class="px-3 py-1 rounded-md bg-slate-200 hover:bg-slate-300" aria-label="Cambiar tema">🌗</button>
        <a href="/profile" class="px-3 py-1 rounded-md bg-blue-600 hover:bg-blue-500 text-white">Perfil</a>
        <form method="post" action="/auth/logout" class="inline">
          <button type="submit" class="px-3 py-1 rounded-md bg-slate-800 text-white hover:bg-slate-700">Salir</button>
        </form>
      </div>
    </header>
    <div class="flex-1 relative">
      <!-- Sidebar -->
  <aside id="sidebar" class="fixed inset-y-0 left-0 w-72 bg-white border-r border-slate-200 z-40 transform -translate-x-full transition-transform duration-200 md:translate-x-0 md:relative md:flex md:flex-col shadow-lg md:shadow-none">
        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200 bg-slate-50">
          <div class="font-medium text-slate-700">Vehículos cercanos</div>
          <button id="btnCloseSidebar" class="p-1 rounded-md hover:bg-slate-200 md:hidden" aria-label="Cerrar">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M5.47 5.47a.75.75 0 011.06 0L12 10.94l5.47-5.47a.75.75 0 111.06 1.06L13.06 12l5.47 5.47a.75.75 0 11-1.06 1.06L12 13.06l-5.47 5.47a.75.75 0 11-1.06-1.06L10.94 12 5.47 6.53a.75.75 0 010-1.06z" clip-rule="evenodd"/></svg>
          </button>
        </div>
        <div class="p-3 space-y-3 overflow-y-auto text-sm" id="vehiclesList">
          <div class="text-slate-500">Cargando vehículos...</div>
        </div>
        <div class="p-3 border-t border-slate-200 bg-slate-50">
          <div class="text-xs mb-2 text-slate-500">Filtrar por estado</div>
          <form id="statusFilterForm" class="grid grid-cols-2 gap-2 text-xs">
            <label class="flex items-center gap-1"><input type="checkbox" name="status" value="available" class="accent-blue-600" checked> <span>Disponible</span></label>
            <label class="flex items-center gap-1"><input type="checkbox" name="status" value="booked" class="accent-yellow-500" checked> <span>Reservado</span></label>
            <label class="flex items-center gap-1"><input type="checkbox" name="status" value="maintenance" class="accent-orange-500" checked> <span>Mantenimiento</span></label>
            <label class="flex items-center gap-1"><input type="checkbox" name="status" value="charging" class="accent-blue-500" checked> <span>Cargando</span></label>
            <button type="submit" class="col-span-2 mt-1 w-full bg-slate-800 text-white hover:bg-slate-700 rounded-md py-1">Aplicar</button>
          </form>
        </div>
        <div class="mt-auto p-3 text-xs text-slate-500 border-t border-slate-200">EcoMotion © <?= date('Y') ?></div>
      </aside>
      <div id="sidebarOverlay" class="fixed inset-0 bg-black/40 opacity-0 pointer-events-none transition-opacity duration-200 z-30 md:hidden"></div>
      <!-- Map container -->
      <div id="map" class="absolute inset-0"></div>
    </div>
  </div>

  <script>
  // Temporarily use mock data until backend DB feed is ready
  const API_URL = '/mock-vehicles.json';
  let map, clusterLayer;
  let darkMode = false;
    const vehiclesById = new Map();

    function initMap() {
        if (typeof L === 'undefined' || !document.getElementById('map')) {
          console.error('Leaflet no cargado o contenedor no encontrado');
          return;
        }
        map = L.map('map');
      const defaultLatLng = [40.4168, -3.7038]; // Madrid fallback
      map.setView(defaultLatLng, 13);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
      }).addTo(map);
      clusterLayer = L.markerClusterGroup({
        showCoverageOnHover: false,
        maxClusterRadius: 45,
        spiderfyOnMaxZoom: true,
        disableClusteringAtZoom: 18
      });
      map.addLayer(clusterLayer);
      fetchAndRender();
      setInterval(fetchAndRender, 15000); // refresh every 15s
    }

    function statusBadge(status) {
      const base = 'px-2 py-0.5 rounded text-[11px] font-medium border';
      const light = {
        available: 'bg-green-100 text-green-700 border-green-200',
        booked: 'bg-yellow-100 text-yellow-700 border-yellow-200',
        maintenance: 'bg-orange-100 text-orange-700 border-orange-200',
        charging: 'bg-blue-100 text-blue-700 border-blue-200',
        default: 'bg-slate-100 text-slate-600 border-slate-200'
      };
      const dark = {
        available: 'bg-green-500/15 text-green-300 border-green-500/30',
        booked: 'bg-yellow-500/15 text-yellow-300 border-yellow-500/30',
        maintenance: 'bg-orange-500/15 text-orange-300 border-orange-500/30',
        charging: 'bg-blue-500/15 text-blue-300 border-blue-500/30',
        default: 'bg-slate-500/15 text-slate-300 border-slate-500/30'
      };
      const palette = darkMode ? dark : light;
      return base + ' ' + (palette[status] || palette.default);
    }

    function renderVehiclesList(vehicles) {
      const listEl = document.getElementById('vehiclesList');
      if (!listEl) return;
      if (!vehicles.length) { listEl.innerHTML = '<div class="text-slate-500">No hay vehículos.</div>'; return; }
      listEl.innerHTML = vehicles.map(v => {
        const battery = (v.battery_level != null) ? Math.round(v.battery_level)+'%' : '—';
        return `<div class="group ${darkMode?'bg-slate-800/60 border-slate-700 hover:border-slate-600':'bg-white border-slate-200 hover:border-slate-300'} border rounded-lg p-3 transition">
          <div class="flex items-start justify-between">
            <div class="font-medium ${darkMode?'text-slate-100':'text-slate-700'} text-sm">${escapeHtml(v.model || 'Vehículo')}</div>
            <span class="${statusBadge(v.status)}">${escapeHtml(v.status)}</span>
          </div>
          <div class="mt-1 text-xs ${darkMode?'text-slate-300':'text-slate-500'} space-y-1">
            <div><span class="${darkMode?'text-slate-400':'text-slate-400'}">ID:</span> ${v.id}</div>
            <div><span class="${darkMode?'text-slate-400':'text-slate-400'}">Batería:</span> ${battery}</div>
            <div><span class="${darkMode?'text-slate-400':'text-slate-400'}">VIN:</span> ${escapeHtml(v.vin || '')}</div>
            <button data-pan="${v.id}" class="mt-2 w-full text-xs ${darkMode?'bg-slate-700 hover:bg-slate-600 text-slate-200':'bg-slate-800 hover:bg-slate-700 text-white'} rounded-md py-1">Ver en mapa</button>
          </div>
        </div>`;
      }).join('');
      listEl.querySelectorAll('button[data-pan]').forEach(btn => {
        btn.addEventListener('click', () => {
          const id = btn.getAttribute('data-pan');
          const v = vehiclesById.get(Number(id));
          if (v && v.lat && v.lng) {
            map.panTo([v.lat, v.lng]);
            const m = v.__marker;
            if (m) m.openPopup();
          }
          closeSidebar();
        });
      });
    }

    function escapeHtml(str){
        const map = {"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#39;"};
        return String(str).replace(/[&<>"']/g, s => map[s]);
    }

    function getSelectedStatuses(){
      const form = document.getElementById('statusFilterForm');
      if (!form) return [];
      return Array.from(form.querySelectorAll('input[name="status"]:checked')).map(cb => cb.value);
    }

    function fetchAndRender() {
      const b = map.getBounds();
      const params = {
        south: b.getSouth().toFixed(6),
        west: b.getWest().toFixed(6),
        north: b.getNorth().toFixed(6),
        east: b.getEast().toFixed(6)
      };
      const statuses = getSelectedStatuses();
      if (statuses.length) params.status = statuses.join(',');
      const q = new URLSearchParams(params).toString();
      // For mock JSON, ignore bounds and status for now; keep structure for later DB integration
      fetch(API_URL)
        .then(r => r.json())
        .then(data => {
          const vehicles = Array.isArray(data) ? data : ((data && data.vehicles) ? data.vehicles : []);
          updateMarkers(vehicles);
          renderVehiclesList(vehicles);
        })
        .catch(() => {});
    }

    function updateMarkers(vehicles) {
      if (!clusterLayer) return;
      clusterLayer.clearLayers();
      vehiclesById.clear();
      vehicles.forEach(v => {
        if (typeof v.lat !== 'number' || typeof v.lng !== 'number') return;
        const marker = L.marker([v.lat, v.lng]);
        const battery = (v.battery_level != null) ? Math.round(v.battery_level)+'%' : '—';
        marker.bindPopup(`<div class='text-sm'><div class='font-semibold mb-1'>${escapeHtml(v.model || 'Vehículo')}</div>
          <div class='text-xs'>Estado: ${escapeHtml(v.status)}</div>
          <div class='text-xs'>Batería: ${battery}</div>
          <div class='text-xs'>VIN: ${escapeHtml(v.vin || '')}</div></div>`);
        clusterLayer.addLayer(marker);
        v.__marker = marker;
        vehiclesById.set(v.id, v);
      });
      // Fit bounds if many markers
      if (vehicles.length) {
        const pts = vehicles.filter(v => typeof v.lat === 'number' && typeof v.lng === 'number').map(v => [v.lat, v.lng]);
        if (pts.length) {
          const bounds = L.latLngBounds(pts);
          // Fit only if user hasn't opted-in geolocation centering recently
          if (!window.__userCenteredRecently) {
            map.fitBounds(bounds.pad(0.2));
          }
        }
      }
    }

    // Sidebar logic
  const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const btnOpen = document.getElementById('btnOpenSidebar');
    const btnClose = document.getElementById('btnCloseSidebar');
  function openSidebar(){ if (sidebar) sidebar.classList.remove('-translate-x-full'); if (overlay){ overlay.classList.remove('pointer-events-none','opacity-0'); overlay.classList.add('opacity-100'); } }
  function closeSidebar(){ if (sidebar) sidebar.classList.add('-translate-x-full'); if (overlay){ overlay.classList.add('pointer-events-none','opacity-0'); overlay.classList.remove('opacity-100'); } }
    if (btnOpen) btnOpen.addEventListener('click', openSidebar);
    if (btnClose) btnClose.addEventListener('click', closeSidebar);
    if (overlay) overlay.addEventListener('click', closeSidebar);

    document.addEventListener('DOMContentLoaded', () => {
      initMap();
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(pos => {
          const lat = pos.coords.latitude, lng = pos.coords.longitude;
          window.__userCenteredRecently = true;
          if (map) map.setView([lat, lng], 15);
          fetchAndRender();
          setTimeout(()=>{ window.__userCenteredRecently = false; }, 4000);
        }, () => {
          // Geolocation denied or failed, still render mock vehicles and fit to them
          fetchAndRender();
        }, { enableHighAccuracy: true, timeout: 8000, maximumAge: 10000 });
      } else {
        fetchAndRender();
      }
      if (map) map.on('moveend', fetchAndRender);
      const form = document.getElementById('statusFilterForm');
      if (form) {
        form.addEventListener('submit', function(e){ e.preventDefault(); fetchAndRender(); });
        form.querySelectorAll('input[name="status"]').forEach(cb => cb.addEventListener('change', fetchAndRender));
      }
      const themeBtn = document.getElementById('themeToggle');
      themeBtn && themeBtn.addEventListener('click', () => {
        darkMode = !darkMode;
        const body = document.getElementById('appBody');
        if (darkMode) {
          body.classList.remove('bg-slate-100','text-slate-800');
          body.classList.add('bg-slate-950','text-slate-100');
          document.getElementById('sidebar').classList.remove('bg-white','border-slate-200');
          document.getElementById('sidebar').classList.add('bg-slate-900','border-slate-800');
        } else {
          body.classList.add('bg-slate-100','text-slate-800');
          body.classList.remove('bg-slate-950','text-slate-100');
          document.getElementById('sidebar').classList.add('bg-white','border-slate-200');
          document.getElementById('sidebar').classList.remove('bg-slate-900','border-slate-800');
        }
        // Rerender list for palette change
        fetchAndRender();
      });
    });
  </script>
</body>
</html>
