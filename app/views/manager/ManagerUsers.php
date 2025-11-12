<?php
$users = $users ?? [];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>EcoMotion Manager - Users</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <style> body{font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;} </style>
</head>
<body class="bg-slate-50 text-slate-900">
  <!-- Mobile sidebar (drawer) -->
  <div id="mobileSidebar" class="fixed inset-y-0 left-0 w-64 bg-slate-900 text-slate-200 transform -translate-x-full transition-transform duration-200 z-50 md:hidden">
    <div class="p-4 flex items-center justify-between border-b border-slate-800">
      <div class="flex items-center gap-2 font-bold">
        <div class="w-6 h-6 rounded-md bg-green-500"></div>
        EcoMotion
      </div>
      <button id="btnCloseSidebar" type="button" class="p-2 rounded-md hover:bg-slate-800" aria-label="Cerrar menú">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M5.47 5.47a.75.75 0 011.06 0L12 10.94l5.47-5.47a.75.75 0 111.06 1.06L13.06 12l5.47 5.47a.75.75 0 11-1.06 1.06L12 13.06l-5.47 5.47a.75.75 0 11-1.06-1.06L10.94 12 5.47 6.53a.75.75 0 010-1.06z" clip-rule="evenodd"/></svg>
      </button>
    </div>
    <nav class="flex flex-col p-3">
      <a class="px-3 py-2 rounded-md hover:bg-slate-800" href="/manager">Dashboard</a>
      <a class="px-3 py-2 rounded-md hover:bg-slate-800" href="#">Vehicles</a>
      <a class="px-3 py-2 rounded-md bg-slate-800 text-white" href="/manager/users">Users</a>
      <a class="px-3 py-2 rounded-md hover:bg-slate-800" href="#">Reservations</a>
      <a class="px-3 py-2 rounded-md hover:bg-slate-800" href="#">Payments</a>
      <a class="px-3 py-2 rounded-md hover:bg-slate-800" href="#">Reports</a>
      <a class="px-3 py-2 rounded-md hover:bg-slate-800" href="#">Support</a>
      <a class="px-3 py-2 rounded-md hover:bg-slate-800" href="#">Partners</a>
      <a class="px-3 py-2 rounded-md hover:bg-slate-800" href="#">Settings</a>
    </nav>
  </div>
  <div id="mobileSidebarOverlay" class="fixed inset-0 bg-black/40 opacity-0 pointer-events-none transition-opacity duration-200 z-40 md:hidden"></div>

  <div class="min-h-screen grid grid-cols-1 md:grid-cols-[240px_1fr]">
    <!-- Sidebar -->
    <aside class="hidden md:flex flex-col gap-2 bg-slate-900 text-slate-200 p-4">
      <div class="flex items-center gap-2 font-bold text-lg mb-2">
        <div class="w-7 h-7 rounded-md bg-green-500"></div>
        EcoMotion Manager
      </div>
      <nav class="flex flex-col">
        <a class="px-3 py-2 rounded-md hover:bg-slate-800" href="/manager">Dashboard</a>
        <a class="px-3 py-2 rounded-md hover:bg-slate-800" href="#">Vehicles</a>
        <a class="px-3 py-2 rounded-md bg-slate-800 text-white" href="/manager/users">Users</a>
        <a class="px-3 py-2 rounded-md hover:bg-slate-800" href="#">Reservations</a>
        <a class="px-3 py-2 rounded-md hover:bg-slate-800" href="#">Payments</a>
        <a class="px-3 py-2 rounded-md hover:bg-slate-800" href="#">Reports</a>
        <a class="px-3 py-2 rounded-md hover:bg-slate-800" href="#">Support</a>
        <a class="px-3 py-2 rounded-md hover:bg-slate-800" href="#">Partners</a>
        <a class="px-3 py-2 rounded-md hover:bg-slate-800" href="#">Settings</a>
      </nav>
    </aside>

    <!-- Main -->
    <main class="flex flex-col">
      <header class="bg-white border-b border-slate-200 px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <button id="btnOpenSidebar" type="button" class="md:hidden -ml-1 p-2 rounded-md hover:bg-slate-100" aria-label="Abrir menú">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M3.75 5.25A.75.75 0 014.5 4.5h15a.75.75 0 010 1.5h-15a.75.75 0 01-.75-.75zm0 7A.75.75 0 014.5 11.5h15a.75.75 0 010 1.5h-15a.75.75 0 01-.75-.75zm0 7a.75.75 0 01.75-.75h15a.75.75 0 010 1.5h-15a.75.75 0 01-.75-.75z" clip-rule="evenodd"/></svg>
          </button>
          <div class="font-bold">EcoMotion Manager</div>
        </div>
        <div class="flex items-center gap-3">
          <input class="hidden sm:block border border-slate-200 rounded-md px-3 py-2 text-sm" placeholder="Search..." />
          <span class="hidden sm:inline text-sm text-slate-600">Welcome, Manager</span>
          <form method="post" action="/manager/logout">
            <button type="submit" class="bg-slate-900 text-white rounded-md px-3 py-2 text-sm">Logout</button>
          </form>
        </div>
      </header>

      <div class="p-4 space-y-4">
        <h2 class="text-base font-semibold">Gestión de Usuarios</h2>

        <?php if (!empty($_SESSION['flash_errors'])): $errs = $_SESSION['flash_errors']; unset($_SESSION['flash_errors']); ?>
          <div class="mb-3 rounded-md border border-red-200 bg-red-50 text-red-700 px-3 py-2 text-sm">
            <?php foreach ($errs as $er) echo '<div>'.e($er).'</div>'; ?>
          </div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['flash_success'])): unset($_SESSION['flash_success']); ?>
          <div class="mb-3 rounded-md border border-green-200 bg-green-50 text-green-700 px-3 py-2 text-sm">
            Operación realizada correctamente.
          </div>
        <?php endif; ?>

        <form method="post" action="/manager/users" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 mb-3 items-start">
          <input required name="name" placeholder="Name" class="w-full border border-slate-200 rounded-md px-3 py-2 text-sm">
          <input required type="email" name="email" placeholder="Email" class="w-full border border-slate-200 rounded-md px-3 py-2 text-sm">
          <input required minlength="6" type="password" name="password" placeholder="Password" class="w-full border border-slate-200 rounded-md px-3 py-2 text-sm">
          <input name="phone" placeholder="Phone" class="w-full border border-slate-200 rounded-md px-3 py-2 text-sm">
          <input name="accessibility_flags" placeholder="Accessibility" class="w-full border border-slate-200 rounded-md px-3 py-2 text-sm">
          <select name="role" class="w-full border border-slate-200 rounded-md px-3 py-2 text-sm">
            <option value="client">Cliente</option>
            <option value="manager">Manager</option>
          </select>
          <div class="sm:col-span-2 lg:col-span-1">
            <button type="submit" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white rounded-md px-3 py-2 text-sm">Crear Usuario</button>
          </div>
        </form>

        <?php if (empty($users)): ?>
          <div class="text-slate-500 text-sm">No users found.</div>
        <?php else: ?>
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead>
                <tr class="text-left border-b border-slate-200 text-slate-600">
                  <th class="py-2 pr-2 hidden md:table-cell">ID</th>
                  <th class="py-2 pr-2">Name</th>
                  <th class="py-2 pr-2 hidden md:table-cell">Email</th>
                  <th class="py-2 pr-2">Role</th>
                  <th class="py-2 pr-2 hidden md:table-cell">Phone</th>
                  <th class="py-2 pr-2 hidden md:table-cell">Acces.</th>
                  <th class="py-2 pr-2">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($users as $u): ?>
                  <?php $uid = e($u['id'] ?? ''); ?>
                  <tr class="border-b border-slate-100" data-row-id="<?= $uid ?>">
                    <td class="py-2 pr-2 align-top hidden md:table-cell"><?= $uid ?></td>
                    <td class="py-2 pr-2 align-top">
                      <input form="upd-<?= $uid ?>" name="name" value="<?= e($u['name'] ?? '') ?>" class="border border-slate-200 rounded-md px-2 py-1 min-w-[140px]">
                    </td>
                    <td class="py-2 pr-2 align-top hidden md:table-cell">
                      <input form="upd-<?= $uid ?>" type="email" name="email" value="<?= e($u['email'] ?? '') ?>" class="border border-slate-200 rounded-md px-2 py-1 min-w-[200px]">
                    </td>
                    <td class="py-2 pr-2 align-top">
                      <?php if (($u['role'] ?? '') !== 'tenant_admin'): ?>
                        <select form="upd-<?= $uid ?>" name="role" class="border border-slate-200 rounded-md px-2 py-1 min-w-[130px]">
                          <option value="client" <?= (($u['role'] ?? '')==='client')?'selected':'' ?>>client</option>
                          <option value="manager" <?= (($u['role'] ?? '')==='manager')?'selected':'' ?>>manager</option>
                        </select>
                      <?php else: ?>
                        <span class="text-slate-400">tenant_admin</span>
                      <?php endif; ?>
                    </td>
                    <td class="py-2 pr-2 align-top hidden md:table-cell">
                      <input form="upd-<?= $uid ?>" name="phone" value="<?= e($u['phone'] ?? '') ?>" class="border border-slate-200 rounded-md px-2 py-1 min-w-[120px]">
                    </td>
                    <td class="py-2 pr-2 align-top hidden md:table-cell">
                      <input form="upd-<?= $uid ?>" name="accessibility_flags" value="<?= e(is_string($u['accessibility_flags'] ?? '') ? ($u['accessibility_flags'] ?? '') : json_encode($u['accessibility_flags'] ?? '')) ?>" class="border border-slate-200 rounded-md px-2 py-1 min-w-[140px]">
                    </td>
                    <td class="py-2 pr-2 align-top whitespace-nowrap">
                      <?php if (($u['role'] ?? '') !== 'tenant_admin'): ?>
                        <form id="upd-<?= $uid ?>" method="post" action="/manager/users/<?= $uid ?>/update" class="inline"></form>
                        <button form="upd-<?= $uid ?>" type="submit" class="bg-amber-500 hover:bg-amber-600 text-white rounded-md px-3 py-1">Guardar</button>
                        <?php if (($u['role'] ?? '') === 'client'): ?>
                          <form class="js-delete-user inline" method="post" action="/manager/users/<?= $uid ?>/delete" data-id="<?= $uid ?>">
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white rounded-md px-3 py-1">Eliminar</button>
                          </form>
                        <?php endif; ?>
                      <?php else: ?>
                        <span class="text-slate-400">—</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <!-- Compact details for mobile -->
                  <tr class="md:hidden border-b border-slate-100">
                    <td class="py-2 pr-2 align-top" colspan="7">
                      <div class="text-xs text-slate-600 space-y-1">
                        <div><span class="font-medium text-slate-700">Email:</span> <?= e($u['email'] ?? '') ?></div>
                        <div><span class="font-medium text-slate-700">Phone:</span> <?= e($u['phone'] ?? '') ?></div>
                        <div><span class="font-medium text-slate-700">Acces.:</span> <?= e(is_string($u['accessibility_flags'] ?? '') ? ($u['accessibility_flags'] ?? '') : json_encode($u['accessibility_flags'] ?? '')) ?></div>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>

      </div>
      <footer class="mt-auto w-full text-center text-slate-500 text-xs py-4 border-t border-slate-100 bg-white">EcoMotion © <?= date('Y') ?> | Version 1.0</footer>
      </div>
    </main>
  </div>

  <script>
    (function(){
      // Mobile sidebar toggle
      var openBtn = document.getElementById('btnOpenSidebar');
      var closeBtn = document.getElementById('btnCloseSidebar');
      var drawer = document.getElementById('mobileSidebar');
      var overlay = document.getElementById('mobileSidebarOverlay');
      function openDrawer(){
        if (drawer) drawer.classList.remove('-translate-x-full');
        if (overlay) { overlay.classList.remove('pointer-events-none'); overlay.classList.add('opacity-100'); }
      }
      function closeDrawer(){
        if (drawer) drawer.classList.add('-translate-x-full');
        if (overlay) { overlay.classList.add('pointer-events-none'); overlay.classList.remove('opacity-100'); }
      }
      if (openBtn) openBtn.addEventListener('click', openDrawer);
      if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
      if (overlay) overlay.addEventListener('click', closeDrawer);

      function closestRow(el){ while (el && el.tagName !== 'TR') el = el.parentElement; return el; }
      function bindDelete(){
        var forms = document.querySelectorAll('.js-delete-user');
        forms.forEach(function(f){
          if (f.__bound) return; f.__bound = true;
          f.addEventListener('submit', function(ev){
            ev.preventDefault();
            if (!confirm('¿Eliminar este usuario?')) return;
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
                  alert('No se pudo eliminar el usuario.');
                }
              })
              .catch(function(){ alert('Error de red al eliminar.'); });
          });
        });
      }
      bindDelete();
    })();
  </script>
</body>
</html>
