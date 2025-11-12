<?php $active = $active ?? ''; ?>
<!-- Mobile sidebar (drawer) -->
<div id="mobileSidebar" class="fixed inset-y-0 left-0 w-64 bg-slate-900 text-slate-200 transform -translate-x-full transition-transform duration-200 z-50 md:hidden">
  <div class="p-4 flex items-center justify-between border-b border-slate-800">
    <div class="flex items-center gap-2 font-bold">
      <div class="w-6 h-6 rounded-md" style="background: var(--brand-green);"></div>
      EcoMotion
    </div>
    <button id="btnCloseSidebar" type="button" class="p-2 rounded-md hover:bg-slate-800" aria-label="Cerrar menú">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M5.47 5.47a.75.75 0 011.06 0L12 10.94l5.47-5.47a.75.75 0 111.06 1.06L13.06 12l5.47 5.47a.75.75 0 11-1.06 1.06L12 13.06l-5.47 5.47a.75.75 0 11-1.06-1.06L10.94 12 5.47 6.53a.75.75 0 010-1.06z" clip-rule="evenodd"/></svg>
    </button>
  </div>
  <nav class="flex flex-col p-3">
    <a class="px-3 py-2 rounded-md <?= $active==='dashboard' ? 'bg-slate-800 text-white' : 'hover:bg-slate-800' ?>" href="<?= e(manager_base()) ?>">Dashboard</a>
    <a class="px-3 py-2 rounded-md hover:bg-slate-800" href="#">Vehicles</a>
    <a class="px-3 py-2 rounded-md <?= $active==='users' ? 'bg-slate-800 text-white' : 'hover:bg-slate-800' ?>" href="<?= e(manager_base()) ?>/users">Users</a>
    <a class="px-3 py-2 rounded-md hover:bg-slate-800" href="#">Reservations</a>
    <a class="px-3 py-2 rounded-md hover:bg-slate-800" href="#">Payments</a>
    <a class="px-3 py-2 rounded-md hover:bg-slate-800" href="#">Reports</a>
    <a class="px-3 py-2 rounded-md hover:bg-slate-800" href="#">Support</a>
    <a class="px-3 py-2 rounded-md hover:bg-slate-800" href="#">Partners</a>
    <a class="px-3 py-2 rounded-md hover:bg-slate-800" href="#">Settings</a>
  </nav>
</div>
<div id="mobileSidebarOverlay" class="fixed inset-0 bg-black/40 opacity-0 pointer-events-none transition-opacity duration-200 z-40 md:hidden"></div>
