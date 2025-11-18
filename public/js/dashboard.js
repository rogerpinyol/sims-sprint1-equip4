// Dashboard map & vehicle logic externalized
(function(){
  const API_URL = '/client/api/vehicles';
  let map, clusterLayer;
  const vehiclesById = new Map();
  let userInteracting = false;
  let userInteractTimer = null;
  let userMarker = null;
  let firstFix = false;
  let initialVehiclesLoaded = false;

  function initMap() {
    if (typeof L === 'undefined' || !document.getElementById('map')) {
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
    const map = {
      available: 'badge badge-status-available',
      booked: 'badge badge-status-booked',
      maintenance: 'badge badge-status-maintenance',
      charging: 'badge badge-status-charging',
      default: 'badge badge-status-default'
    };
    return map[status] || map.default;
  }

  function escapeHtml(str){
    const mapc = {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'};
    return String(str).replace(/[&<>"']/g, s => mapc[s]);
  }

  function renderVehiclesList(vehicles) {
    const listEl = document.getElementById('vehiclesList');
    const tableWrap = document.getElementById('vehiclesTableWrap');
    if (!listEl) return;
    if (!vehicles.length) {
      listEl.innerHTML = '<div class="text-slate-500">No vehicles available.</div>';
      if (tableWrap) tableWrap.innerHTML = '<div class="text-slate-500 text-sm">No vehicles available.</div>';
      return;
    }
    listEl.innerHTML = vehicles.map(v => {
      const battery = (v.battery_level != null) ? Math.round(v.battery_level)+'%' : '—';
      return `<div class="card group">
        <div class="flex items-start justify-between">
          <div class="font-medium text-slate-700 text-sm">${escapeHtml(v.model || 'Vehicle')}</div>
          <span class="${statusBadge(v.status)}">${escapeHtml(v.status)}</span>
        </div>
        <div class="mt-1 text-xs text-slate-500 space-y-1">
          <div><span class="text-slate-400">ID:</span> ${v.id}</div>
          <div><span class="text-slate-400">Battery:</span> ${battery}</div>
          <div><span class="text-slate-400">VIN:</span> ${escapeHtml(v.vin || '')}</div>
          <button data-pan="${v.id}" class="btn btn-primary w-full text-xs py-1">View on map</button>
        </div>
      </div>`;
    }).join('');

    if (tableWrap) {
      tableWrap.innerHTML = `<table class="table text-xs">
        <thead>
          <tr>
            <th>ID</th>
            <th>Model</th>
            <th>Status</th>
            <th>Battery</th>
            <th>VIN</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          ${vehicles.map(v => {
            const battery = (v.battery_level != null) ? Math.round(v.battery_level)+'%' : '—';
            return `<tr>
              <td>${v.id}</td>
              <td>${escapeHtml(v.model || 'Vehicle')}</td>
              <td><span class="${statusBadge(v.status)}">${escapeHtml(v.status)}</span></td>
              <td>${battery}</td>
              <td>${escapeHtml(v.vin || '')}</td>
              <td><button data-pan="${v.id}" class="btn btn-primary text-xs py-1">View</button></td>
            </tr>`;
          }).join('')}
        </tbody>
      </table>`;
    }
    listEl.querySelectorAll('button[data-pan]').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.getAttribute('data-pan');
        const v = vehiclesById.get(Number(id));
        if (v && v.lat && v.lng && map) {
          map.setView([v.lat, v.lng], 18, {animate: true});
          setTimeout(() => {
            const m = v.__marker;
            if (m && m.openPopup) {
              m.openPopup();
            }
          }, 500);
        }
        closeSidebar();
      });
    });
    if (tableWrap) tableWrap.querySelectorAll('button[data-pan]').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.getAttribute('data-pan');
        const v = vehiclesById.get(Number(id));
        if (v && v.lat && v.lng && map) {
          map.setView([v.lat, v.lng], 18, {animate: true});
          setTimeout(() => {
            const m = v.__marker;
            if (m && m.openPopup) {
              m.openPopup();
            }
          }, 500);
        }
      });
    });
  }

  function getSelectedStatuses(){
    const form = document.getElementById('statusFilterForm');
    if (!form) return [];
    return Array.from(form.querySelectorAll('input[name="status"]:checked')).map(cb => cb.value);
  }

  function fetchAndRender() {
    fetch(API_URL)
      .then(r => r.ok ? r.json() : null)
      .then(data => {
        if (data === null) {
          // API failed, use sample data silently
          if (initialVehiclesLoaded) return;
          const sample = [
            { id: 101, vin: 'TESTVIN001', model: 'Tesla Model 3', status: 'available', battery_level: 87, lat: 40.71280, lng: 0.58100 },
            { id: 102, vin: 'TESTVIN002', model: 'Renault Zoe', status: 'charging', battery_level: 45, lat: 40.71015, lng: 0.57982 },
            { id: 103, vin: 'TESTVIN003', model: 'Nissan Leaf', status: 'booked', battery_level: 62, lat: 40.71350, lng: 0.58020 }
          ];
          updateMarkers(sample);
          renderVehiclesList(sample);
          return;
        }
        const all = Array.isArray(data?.vehicles) ? data.vehicles : Array.isArray(data) ? data : [];
        const statuses = getSelectedStatuses();
        const filtered = statuses.length ? all.filter(v => statuses.includes(String(v.status))) : all;
        updateMarkers(filtered);
        renderVehiclesList(filtered);
        if (!initialVehiclesLoaded) { initialVehiclesLoaded = true; }
      })
      .catch(() => {
        // Suppress all errors silently
        if (initialVehiclesLoaded) return;
        const sample = [
          { id: 101, vin: 'TESTVIN001', model: 'Tesla Model 3', status: 'available', battery_level: 87, lat: 40.71280, lng: 0.58100 },
          { id: 102, vin: 'TESTVIN002', model: 'Renault Zoe', status: 'charging', battery_level: 45, lat: 40.71015, lng: 0.57982 },
          { id: 103, vin: 'TESTVIN003', model: 'Nissan Leaf', status: 'booked', battery_level: 62, lat: 40.71350, lng: 0.58020 }
        ];
        updateMarkers(sample);
        renderVehiclesList(sample);
      });
  }

  function updateMarkers(vehicles) {
    if (!clusterLayer) return;
    clusterLayer.clearLayers();
    vehiclesById.clear();
    
    // Custom icon using EcoMotion logo
    const carIcon = L.icon({
      iconUrl: '/images/Logo%20EcoMotion%20Transparent%202.png',
      iconSize: [40, 25],
      iconAnchor: [20, 25],
      popupAnchor: [0, -25]
    });
    
    vehicles.forEach(v => {
      if (typeof v.lat !== 'number' || typeof v.lng !== 'number') return;
      const marker = L.marker([v.lat, v.lng], {icon: carIcon});
      const battery = (v.battery_level != null) ? Math.round(v.battery_level)+'%' : '—';
      marker.bindPopup(`<div class='text-sm'><div class='font-semibold mb-1'>${escapeHtml(v.model || 'Vehicle')}</div>
        <div class='text-xs'>Status: ${escapeHtml(v.status)}</div>
        <div class='text-xs'>Battery: ${battery}</div>
        <div class='text-xs'>VIN: ${escapeHtml(v.vin || '')}</div></div>`);
      clusterLayer.addLayer(marker);
      v.__marker = marker;
      vehiclesById.set(v.id, v);
    });
    if (vehicles.length) {
      const pts = vehicles.filter(v => typeof v.lat === 'number' && typeof v.lng === 'number').map(v => [v.lat, v.lng]);
      if (pts.length) {
        const bounds = L.latLngBounds(pts);
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
  const resizeHandle = document.getElementById('resizeHandle');
  
  function openSidebar(){
    if (sidebar) sidebar.classList.remove('-translate-x-full');
    if (overlay){ overlay.classList.remove('pointer-events-none','opacity-0'); overlay.classList.add('opacity-100'); }
    if (map) setTimeout(()=> map.invalidateSize(), 250);
  }
  function closeSidebar(){
    if (sidebar) sidebar.classList.add('-translate-x-full');
    if (overlay){ overlay.classList.add('pointer-events-none','opacity-0'); overlay.classList.remove('opacity-100'); }
    if (map) setTimeout(()=> map.invalidateSize(), 250);
  }

  // Resize functionality
  if (resizeHandle && sidebar) {
    let isResizing = false;
    let startX = 0;
    let startWidth = 0;

    resizeHandle.addEventListener('mousedown', (e) => {
      isResizing = true;
      startX = e.clientX;
      startWidth = sidebar.offsetWidth;
      sidebar.classList.add('resizing');
      sidebar.style.transition = 'none';
      document.body.style.cursor = 'ew-resize';
      document.body.style.userSelect = 'none';
      e.preventDefault();
    });

    document.addEventListener('mousemove', (e) => {
      if (!isResizing) return;
      const diff = e.clientX - startX;
      const newWidth = Math.min(Math.max(startWidth + diff, 250), 600);
      sidebar.style.width = newWidth + 'px';
      if (map) map.invalidateSize();
    });

    document.addEventListener('mouseup', () => {
      if (isResizing) {
        isResizing = false;
        sidebar.classList.remove('resizing');
        sidebar.style.transition = '';
        document.body.style.cursor = '';
        document.body.style.userSelect = '';
      }
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    initMap();
    // ensure correct sizing after layout paints
    if (map) setTimeout(()=> map.invalidateSize(), 300);
    if (btnOpen) btnOpen.addEventListener('click', () => {
      openSidebar();
    });
    if (btnClose) btnClose.addEventListener('click', () => {
      closeSidebar();
    });
    if (overlay) overlay.addEventListener('click', closeSidebar);
    // close on Esc
    document.addEventListener('keydown', (e)=>{ if (e.key==='Escape') closeSidebar(); });

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
          }, (error) => {
          }, { enableHighAccuracy: false, timeout: 10000 });
        }
      });
    }

    if (navigator.geolocation) {
      const handleGeolocationError = (error) => {
        // Suppress geolocation errors silently
      };
      
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
            })}).addTo(map).bindPopup('Your location');
          } else {
            userMarker.setLatLng([lat, lng]);
          }
        }
      }, handleGeolocationError, { enableHighAccuracy: false, timeout: 10000, maximumAge: 30000 });
      
      navigator.geolocation.getCurrentPosition(pos => {
        const lat = pos.coords.latitude, lng = pos.coords.longitude;
        if (!firstFix && map) {
          map.setView([lat, lng], 15);
          firstFix = true;
        }
      }, handleGeolocationError, { enableHighAccuracy: false, timeout: 10000, maximumAge: 30000 });
    }

    fetchAndRender();
    // handle viewport size changes
    let resizeTimer;
    window.addEventListener('resize', () => {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(()=>{ if (map) map.invalidateSize(); }, 150);
    });
    if (map) map.on('moveend', fetchAndRender);
    const form = document.getElementById('statusFilterForm');
    if (form) {
      form.addEventListener('submit', function(e){ e.preventDefault(); fetchAndRender(); });
      form.querySelectorAll('input[name="status"]').forEach(cb => cb.addEventListener('change', fetchAndRender));
    }
  });
})();
