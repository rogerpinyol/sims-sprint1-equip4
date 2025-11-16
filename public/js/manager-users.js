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

  // Delete user actions
  function closestRow(el){ while (el && el.tagName !== 'TR') el = el.parentElement; return el; }
  function bindDelete(){
    var forms = document.querySelectorAll('.js-delete-user');
    forms.forEach(function(f){
      if (f.__bound) return; f.__bound = true;
      f.addEventListener('submit', function(ev){
        ev.preventDefault();
        Swal.fire({
          title: 'Are you sure?',
          text: 'This action will delete the user.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#3085d6',
          confirmButtonText: 'Yes, delete',
          cancelButtonText: 'Cancel'
        }).then((result) => {
          if (result.isConfirmed) {
            var action = f.getAttribute('action');
            fetch(action, { method: 'POST', headers: { 'Accept': 'application/json' } })
              .then(function(res){
                var ct = res.headers.get('content-type') || '';
                if (ct.includes('application/json')) return res.json();
                return { deleted: res.ok && !res.redirected };
              })
              .then(function(data){
                if (data && data.deleted) {
                  var row = closestRow(f);
                  if (row && row.parentElement) row.parentElement.removeChild(row);
                } else {
                  Swal.fire({
                    title: 'Error',
                    text: 'The user could not be deleted.',
                    icon: 'error',
                    confirmButtonText: 'Accept'
                  });
                }
              });
          }
        });
      });
    });
  }
  bindDelete();
})();
