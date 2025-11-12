<?php
$user = $user ?? ['name' => 'Cliente', 'email' => ''];
?>
<!doctype html>
<html lang="en" class="h-full">
<head>
  <meta charset="utf-8">
  <title>EcoMotion - Mapa de Vehículos</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
  <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
  <style>
    html, body { height: 100%; }
    #map { width: 100%; height: 100%; z-index: 0; background: #e5e7eb; transition: background 0.3s; }
    .leaflet-popup-content-wrapper { border-radius: 0.75rem; }
    /* Brand palette */
    :root {
      --brand-orange: #F37A3D; /* subtle orange */
      --brand-orange-dark: #DE541E; /* hover */
      --brand-green: #78866B; /* muted green */
      --brand-green-light: #A6B093; /* soft border/bg */
      --brand-beige: #C2B098; /* accent border */
      --brand-text: #333333; /* primary text */
    }
  </style>
</head>
<body class="bg-slate-100 text-[color:var(--brand-text)] font-sans h-full" id="appBody">
  <div class="h-full min-h-screen flex flex-col">
  <header class="flex items-center justify-between px-4 py-3 bg-white border-b border-[color:var(--brand-beige)] shadow-sm relative z-50">
      <div class="flex items-center gap-3">
  <button id="btnOpenSidebar" class="p-2 rounded-md bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600" aria-label="Menú">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5m-16.5 6h16.5m-16.5 6h16.5" />
          </svg>
        </button>
  <h1 class="text-lg font-semibold tracking-tight">EcoMotion</h1>
      </div>
      <div class="flex items-center gap-2 text-sm">
        <span class="hidden sm:inline">Hola, <?= e($user['name']) ?></span>
        <a href="/profile" class="px-3 py-1 rounded-md bg-[color:var(--brand-orange)] hover:bg-[color:var(--brand-orange-dark)] text-white">Perfil</a>
        <form method="post" action="/auth/logout" class="inline">
          <button type="submit" class="px-3 py-1 rounded-md bg-[color:var(--brand-orange)] hover:bg-[color:var(--brand-orange-dark)] text-white">Salir</button>
        </form>
      </div>
    </header>
  <div class="flex-1 relative flex min-h-0">
    <!-- Sidebar -->
    <aside id="sidebar" class="w-72 bg-white border-r border-[color:var(--brand-green-light)] z-40 transform -translate-x-full transition-transform duration-200 md:translate-x-0 md:relative md:flex md:flex-col shadow-lg md:shadow-none flex-shrink-0">
      <div class="flex flex-col h-full">
        <div class="flex items-center justify-between px-4 py-3 border-b border-[color:var(--brand-green-light)] bg-slate-50 shrink-0">
          <div class="font-medium text-slate-700">Vehículos cercanos</div>
          <button id="btnCloseSidebar" class="p-1 rounded-md hover:bg-slate-200 md:hidden" aria-label="Cerrar">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M5.47 5.47a.75.75 0 011.06 0L12 10.94l5.47-5.47a.75.75 0 111.06 1.06L13.06 12l5.47 5.47a.75.75 0 11-1.06 1.06L12 13.06l-5.47 5.47a.75.75 0 11-1.06-1.06L10.94 12 5.47 6.53a.75.75 0 010-1.06z" clip-rule="evenodd"/></svg>
          </button>
        </div>
        <div class="flex-1 min-h-0 p-3 space-y-3 overflow-y-auto text-sm" id="vehiclesList">
        <div class="text-slate-500">Cargando vehículos...</div>
        </div>
        <div class="p-3 border-t border-[color:var(--brand-green-light)] bg-slate-50 shrink-0">
          <div class="text-xs mb-2 text-slate-500">Filtrar por estado</div>
          <form id="statusFilterForm" class="grid grid-cols-2 gap-2 text-xs">
            <label class="flex items-center gap-1"><input type="checkbox" name="status" value="available" class="accent-blue-600" checked> <span>Disponible</span></label>
            <label class="flex items-center gap-1"><input type="checkbox" name="status" value="booked" class="accent-yellow-500" checked> <span>Reservado</span></label>
            <label class="flex items-center gap-1"><input type="checkbox" name="status" value="maintenance" class="accent-orange-500" checked> <span>Mantenimiento</span></label>
            <label class="flex items-center gap-1"><input type="checkbox" name="status" value="charging" class="accent-blue-500" checked> <span>Cargando</span></label>
            <button type="submit" class="col-span-2 mt-1 w-full bg-[color:var(--brand-orange)] hover:bg-[color:var(--brand-orange-dark)] text-white rounded-md py-1">Aplicar</button>
          </form>
        </div>
      </div>
    </aside>
    <div id="sidebarOverlay" class="fixed inset-0 bg-black/40 opacity-0 pointer-events-none transition-opacity duration-200 z-30 md:hidden"></div>
    <!-- Map container -->
  <div id="map" class="flex-1 min-h-0 h-full"></div>
    <button id="btnRecenter" class="absolute z-40 bottom-4 right-4 bg-[color:var(--brand-orange)] hover:bg-[color:var(--brand-orange-dark)] text-white text-sm px-3 py-2 rounded shadow">Ubicarme</button>
  </div>
  </div>

  <script>
  // Temporarily use mock data until backend DB feed is ready
  const API_URL = '/mock-vehicles.json';
  let map, clusterLayer;
    const vehiclesById = new Map();
  let userInteracting = false;
  let userInteractTimer = null;
  let userMarker = null;
  let firstFix = false;
  // State
  let initialVehiclesLoaded = false;

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
      // Respect user interactions (zoom/pan) and avoid auto-fit right after
      function markUserInteracting(){
        userInteracting = true;
        if (userInteractTimer) clearTimeout(userInteractTimer);
        userInteractTimer = setTimeout(()=>{ userInteracting = false; }, 3500);
      }
      map.on('zoomstart', markUserInteracting);
      map.on('movestart', markUserInteracting);
      fetchAndRender();
      setInterval(fetchAndRender, 15000); // refresh every 15s
    }

    function statusBadge(status) {
      const base = 'px-2 py-0.5 rounded text-[11px] font-medium border';
      const classes = {
        available: 'bg-green-100 text-green-700 border-green-200',
        booked: 'bg-yellow-100 text-yellow-700 border-yellow-200',
        maintenance: 'bg-orange-100 text-orange-700 border-orange-200',
        charging: 'bg-blue-100 text-blue-700 border-blue-200',
        default: 'bg-slate-100 text-slate-600 border-slate-200'
      };
      return base + ' ' + (classes[status] || classes.default);
    }

    function renderVehiclesList(vehicles) {
      const listEl = document.getElementById('vehiclesList');
      if (!listEl) return;
      if (!vehicles.length) { listEl.innerHTML = '<div class="text-slate-500">No hay vehículos.</div>'; return; }
      listEl.innerHTML = vehicles.map(v => {
        const battery = (v.battery_level != null) ? Math.round(v.battery_level)+'%' : '—';
        return `<div class="group bg-white border border-[color:var(--brand-green-light)] hover:border-[color:var(--brand-green)] rounded-lg p-3 transition">
          <div class="flex items-start justify-between">
            <div class="font-medium text-slate-700 text-sm">${escapeHtml(v.model || 'Vehículo')}</div>
            <span class="${statusBadge(v.status)}">${escapeHtml(v.status)}</span>
          </div>
          <div class="mt-1 text-xs text-slate-500 space-y-1">
            <div><span class="text-slate-400">ID:</span> ${v.id}</div>
            <div><span class="text-slate-400">Batería:</span> ${battery}</div>
            <div><span class="text-slate-400">VIN:</span> ${escapeHtml(v.vin || '')}</div>
            <button data-pan="${v.id}" class="mt-2 w-full text-xs bg-[color:var(--brand-orange)] hover:bg-[color:var(--brand-orange-dark)] text-white rounded-md py-1">Ver en mapa</button>
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
      // Fetch mock file (fallback) and filter client-side
      fetch(API_URL)
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(data => {
          const all = Array.isArray(data?.vehicles) ? data.vehicles : Array.isArray(data) ? data : [];
          const statuses = getSelectedStatuses();
          const filtered = statuses.length ? all.filter(v => statuses.includes(String(v.status))) : all;
          updateMarkers(filtered);
          renderVehiclesList(filtered);
          if (!initialVehiclesLoaded) { initialVehiclesLoaded = true; }
        })
        .catch(() => {
          // Fallback static sample only if nothing loaded yet
          if (initialVehiclesLoaded) return;
          const sample = [
            { id: 101, vin: "TESTVIN001", model: "Tesla Model 3", status: "available", battery_level: 87, lat: 40.71280, lng: 0.58100 },
            { id: 102, vin: "TESTVIN002", model: "Renault Zoe", status: "charging", battery_level: 45, lat: 40.71015, lng: 0.57982 },
            { id: 103, vin: "TESTVIN003", model: "Nissan Leaf", status: "booked", battery_level: 62, lat: 40.71350, lng: 0.58020 }
          ];
          updateMarkers(sample);
          renderVehiclesList(sample);
        });
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
          // Fit only if user hasn't opted-in geolocation centering recently and not interacting
          if (!window.__userCenteredRecently && !userInteracting) {
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
      const btnRecenter = document.getElementById('btnRecenter');
      if (btnRecenter) {
        btnRecenter.addEventListener('click', () => {
          if (userMarker && map) {
            window.__userCenteredRecently = true;
            map.setView(userMarker.getLatLng(), 15);
            setTimeout(()=>{ window.__userCenteredRecently = false; }, 2500);
          } else if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(pos => {
              const latlng = [pos.coords.latitude, pos.coords.longitude];
              window.__userCenteredRecently = true;
              if (map) map.setView(latlng, 15);
              setTimeout(()=>{ window.__userCenteredRecently = false; }, 2500);
            });
          }
        });
      }
      if (navigator.geolocation) {
        // Continuous updates of user location
        navigator.geolocation.watchPosition(pos => {
          const lat = pos.coords.latitude, lng = pos.coords.longitude;
          if (!firstFix) {
            window.__userCenteredRecently = true;
            if (map) map.setView([lat, lng], 15);
            setTimeout(()=>{ window.__userCenteredRecently = false; }, 4000);
            firstFix = true;
          }
          if (map) {
            if (!userMarker) {
              userMarker = L.marker([lat, lng], {icon: L.icon({
                iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
                shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
                iconSize: [25, 41], iconAnchor: [12, 41]
              })}).addTo(map).bindPopup('Tu ubicación');
            } else {
              userMarker.setLatLng([lat, lng]);
            }
          }
        }, () => {
          // ignore errors; we'll still fetch vehicles and keep default center
        }, { enableHighAccuracy: true, timeout: 8000, maximumAge: 10000 });
        // Initial single read as fallback
        navigator.geolocation.getCurrentPosition(pos => {
          const lat = pos.coords.latitude, lng = pos.coords.longitude;
          if (!firstFix && map) {
            map.setView([lat, lng], 15);
            firstFix = true;
          }
        }, ()=>{}, { enableHighAccuracy: true, timeout: 5000 });
      }
      fetchAndRender();
      if (map) map.on('moveend', fetchAndRender);
      const form = document.getElementById('statusFilterForm');
      if (form) {
        form.addEventListener('submit', function(e){ e.preventDefault(); fetchAndRender(); });
        form.querySelectorAll('input[name="status"]').forEach(cb => cb.addEventListener('change', fetchAndRender));
      }
      // theme toggle removed per new brand guidelines
    });
  </script>
</body>
</html>
