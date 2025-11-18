(function(){
  // Mobile sidebar toggle
  var openBtn = document.getElementById('btnOpenSidebar');
  var closeBtn = document.getElementById('btnCloseSidebar');
  var drawer = document.getElementById('mobileSidebar');
  var overlay = document.getElementById('mobileSidebarOverlay');
  function openDrawer(){ if (drawer) drawer.classList.remove('-translate-x-full'); if (overlay) { overlay.classList.remove('pointer-events-none'); overlay.classList.add('opacity-100'); } }
  function closeDrawer(){ if (drawer) drawer.classList.add('-translate-x-full'); if (overlay) { overlay.classList.add('pointer-events-none'); overlay.classList.remove('opacity-100'); } }
  if (openBtn) openBtn.addEventListener('click', openDrawer);
  if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
  if (overlay) overlay.addEventListener('click', closeDrawer);

  // Leaflet map for vehicles (data embedded in DOM)
  var mapDiv = document.getElementById('vehiclesMap');
  var map = null;
  var markers = {};
  
  if (typeof L !== 'undefined' && mapDiv) {
    try {
      var vehicles = [];
      var dataAttr = mapDiv.getAttribute('data-vehicles');
      if (dataAttr) vehicles = JSON.parse(dataAttr);
      if (Array.isArray(vehicles) && vehicles.length) {
        map = L.map('vehiclesMap').setView([40.713, 0.581], 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          maxZoom: 19,
          attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        
        var carIcon = L.icon({
          iconUrl: '/images/Logo%20EcoMotion%20Transparent%202.png',
          iconSize: [40, 25],
          iconAnchor: [20, 25],
          popupAnchor: [0, -25]
        });
        
        vehicles.forEach(function(v){
          if (typeof v.lat === 'number' && typeof v.lng === 'number') {
            var marker = L.marker([v.lat, v.lng], {icon: carIcon}).addTo(map);
            var model = v.model ? String(v.model) : '';
            var batt = (v.battery_level !== undefined && v.battery_level !== null) ? (v.battery_level + '%') : 'N/A';
            marker.bindPopup('<b>' + model + '</b><br>Battery: ' + batt);
            markers[v.id] = marker;
          }
        });
      }
    } catch(e) { /* ignore JSON/map errors */ }
  }
  
  // Handle View button clicks to locate vehicles on map
  document.addEventListener('click', function(e) {
    if (e.target && e.target.hasAttribute('data-locate')) {
      var vehicleId = e.target.getAttribute('data-locate');
      var lat = parseFloat(e.target.getAttribute('data-lat'));
      var lng = parseFloat(e.target.getAttribute('data-lng'));
      var model = e.target.getAttribute('data-model');
      var battery = e.target.getAttribute('data-battery');
      
      if (map && !isNaN(lat) && !isNaN(lng)) {
        map.setView([lat, lng], 18, {animate: true});
        
        setTimeout(function() {
          var marker = markers[vehicleId];
          if (marker) {
            marker.openPopup();
          }
        }, 500);
      }
    }
  });
})();
