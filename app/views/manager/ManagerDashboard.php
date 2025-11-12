<?php
function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
$totalVehicles = $totalVehicles ?? 45;
$activeReservations = $activeReservations ?? 12;
$dailyRevenue = $dailyRevenue ?? 1230;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>EcoMotion Manager</title>
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
      <a class="px-3 py-2 rounded-md bg-slate-800 text-white" href="/manager">Overview</a>
      <a class="px-3 py-2 rounded-md hover:bg-slate-800" href="#">Vehicles</a>
      <a class="px-3 py-2 rounded-md hover:bg-slate-800" href="/manager/users">Users</a>
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
        <a class="px-3 py-2 rounded-md bg-slate-800 text-white" href="/manager">Overview</a>
        <a class="px-3 py-2 rounded-md hover:bg-slate-800" href="#">Vehicles</a>
        <a class="px-3 py-2 rounded-md hover:bg-slate-800" href="/manager/users">Users</a>
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
        <h2 class="text-base font-semibold">Overview</h2>
        <!-- Stat cards -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <div class="bg-white border border-slate-200 rounded-xl p-4">
            <div class="text-slate-500 text-xs">Vehículos Disponibles</div>
            <div class="text-3xl font-extrabold mt-1"><?= e(number_format($totalVehicles)) ?></div>
          </div>
          <div class="bg-white border border-slate-200 rounded-xl p-4">
            <div class="text-slate-500 text-xs">Reservas Activas</div>
            <div class="text-3xl font-extrabold mt-1"><?= e(number_format($activeReservations)) ?></div>
          </div>
          <div class="bg-white border border-slate-200 rounded-xl p-4">
            <div class="text-slate-500 text-xs">Ingresos Diarios</div>
            <div class="text-3xl font-extrabold mt-1">€<?= e(number_format($dailyRevenue, 0, ',', '.')) ?></div>
          </div>
        </section>

        <!-- Charts placeholder -->
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-4">
          <div class="bg-white border border-slate-200 rounded-xl p-4 min-h-[260px]">
            <h3 class="text-sm font-semibold mb-2">Uso de Vehículos</h3>
            <div class="h-[220px] flex items-center justify-center text-slate-400">[Chart placeholder]</div>
          </div>
          <div class="bg-white border border-slate-200 rounded-xl p-4 min-h-[260px]">
            <h3 class="text-sm font-semibold mb-2">Mapa de Vehículos</h3>
            <div class="h-[220px] rounded-md bg-slate-200"></div>
          </div>
        </section>

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
    })();
  </script>
</body>
</html>
