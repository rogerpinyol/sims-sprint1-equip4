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
  if (typeof L !== 'undefined' && mapDiv) {
    try {
      var vehicles = [];
      var dataAttr = mapDiv.getAttribute('data-vehicles');
      if (dataAttr) vehicles = JSON.parse(dataAttr);
      if (Array.isArray(vehicles) && vehicles.length) {
        var map = L.map('vehiclesMap').setView([40.713, 0.581], 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          maxZoom: 19,
          attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        vehicles.forEach(function(v){
          if (typeof v.lat === 'number' && typeof v.lng === 'number') {
            var marker = L.marker([v.lat, v.lng]).addTo(map);
            var model = v.model ? String(v.model) : '';
            var batt = (v.battery_level !== undefined && v.battery_level !== null) ? (v.battery_level + '%') : 'N/A';
            marker.bindPopup('<b>' + model + '</b><br>Batería: ' + batt);
          }
        });
      }
    } catch(e) { /* ignore JSON/map errors */ }
  }
})();
