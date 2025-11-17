<header class="bg-white border-b border-slate-200 px-4 py-3 flex items-center justify-between">
  <div class="flex items-center gap-2">
    <button id="btnOpenSidebar" type="button" class="md:hidden -ml-1 p-2 rounded-md hover:bg-slate-100" aria-label="Abrir menú">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M3.75 5.25A.75.75 0 014.5 4.5h15a.75.75 0 010 1.5h-15a.75.75 0 01-.75-.75zm0 7A.75.75 0 014.5 11.5h15a.75.75 0 010 1.5h-15a.75.75 0 01-.75-.75zm0 7a.75.75 0 01.75-.75h15a.75.75 0 010 1.5h-15a.75.75 0 01-.75-.75z" clip-rule="evenodd"/></svg>
    </button>
  </div>
  <div class="flex items-center gap-3">
    <input class="hidden sm:block input w-48" placeholder="Search..." />
    <span class="hidden sm:inline text-sm text-slate-600">Welcome, Manager</span>
    <form method="post" action="<?= e(manager_base()) ?>/logout">
      <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-medium px-4 py-2 rounded-lg text-sm transition">Logout</button>
    </form>
  </div>
</header>
