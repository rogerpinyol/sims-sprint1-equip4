<?php

$user = $user ?? ['name' => 'Client', 'email' => '']; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoMotion</title>
    <link rel="stylesheet" href="/path/to/your/styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<div class="h-full min-h-screen flex flex-col" id="appBody">
  <header class="flex items-center justify-between px-4 py-3 bg-white border-b border-[color:var(--brand-beige)] shadow-sm relative z-50">
      <div class="flex items-center gap-3">
  <button id="btnOpenSidebar" class="p-2 rounded-md bg-slate-200 hover:bg-slate-300 md:hidden" aria-label="Menú">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5m-16.5 6h16.5m-16.5 6h16.5" />
          </svg>
        </button>
  <h1 class="text-lg font-semibold tracking-tight">EcoMotion</h1>
      </div>
      <div class="flex items-center gap-2 text-sm flex-wrap justify-end">
        <span class="hidden sm:inline">Hola, <?= e($user['name']) ?></span>
        <a href="/profile" class="btn btn-primary">Perfil</a>
        <form method="post" action="/auth/logout" class="inline">
          <button type="submit" class="btn btn-primary">Salir</button>
        </form>
      </div>
    </header>
  <div class="flex-1 relative flex min-h-0">

    <!-- Sidebar -->
    <aside id="sidebar" class="w-72 bg-white border-r border-[color:var(--brand-green-light)] z-40 transform -translate-x-full transition-transform duration-200 absolute inset-y-0 left-0 md:translate-x-0 md:relative md:flex md:flex-col shadow-lg md:shadow-none flex-shrink-0">
      <div class="flex flex-col h-full">
        <div class="flex items-center justify-between px-4 py-3 border-b border-[color:var(--brand-green-light)] bg-slate-50 shrink-0">
          <div class="font-medium text-slate-700">Vehículos cercanos</div>
          <button id="btnCloseSidebar" class="p-1 rounded-md hover:bg-slate-200 md:hidden" aria-label="Cerrar">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M5.47 5.47a.75.75 0 011.06 0L12 10.94l5.47-5.47a.75.75 0 111.06 1.06L13.06 12l5.47 5.47a.75.75 0 11-1.06 1.06L12 13.06l-5.47 5.47a.75.75 0 11-1.06-1.06L10.94 12 5.47 6.53a.75.75 0 010-1.06z" clip-rule="evenodd"/></svg>
          </button>
        </div>
        <div class="flex-1 min-h-0 p-3 overflow-y-auto">
          <!-- Mobile cards -->
          <div id="vehiclesList" class="space-y-3 text-sm md:hidden">
            <div class="text-slate-500">Cargando vehículos...</div>
          </div>
          <!-- Desktop table -->
          <div id="vehiclesTableWrap" class="hidden md:block overflow-x-auto">
            <div class="text-slate-500 text-sm">Cargando vehículos...</div>
          </div>
        </div>
        <div class="p-3 border-t border-[color:var(--brand-green-light)] bg-slate-50 shrink-0">
          <div class="text-xs mb-2 text-slate-500">Filtrar por estado</div>
          <form id="statusFilterForm" class="grid grid-cols-2 gap-2 text-xs">
            <label class="flex items-center gap-1"><input type="checkbox" name="status" value="available" class="accent-blue-600" checked> <span>Disponible</span></label>
            <label class="flex items-center gap-1"><input type="checkbox" name="status" value="booked" class="accent-yellow-500" checked> <span>Reservado</span></label>
            <label class="flex items-center gap-1"><input type="checkbox" name="status" value="maintenance" class="accent-orange-500" checked> <span>Mantenimiento</span></label>
            <label class="flex items-center gap-1"><input type="checkbox" name="status" value="charging" class="accent-blue-500" checked> <span>Cargando</span></label>
            <button type="submit" class="btn btn-primary col-span-2 mt-1 w-full">Apply</button>
          </form>
        </div>
      </div>
    </aside>
    <div id="sidebarOverlay" class="fixed inset-0 bg-black/40 opacity-0 pointer-events-none transition-opacity duration-200 z-30 md:hidden"></div>
    <!-- Map container -->
  <div id="map" class="flex-1 min-h-[60vh] sm:min-h-0 h-full"></div>
    <button id="btnRecenter" class="btn btn-primary absolute z-40 fab-bottom-right" aria-label="Locate Me">
      <i class="fas fa-location-arrow text-lg"></i>
    </button>
  </div>
  </div>
</body>
</html>