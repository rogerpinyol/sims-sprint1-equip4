<?php $active = $active ?? ''; ?>
<aside class="hidden md:flex flex-col gap-2 bg-slate-900 text-slate-200 p-4">
  <div class="flex items-center gap-2">
		<a href="#" class="flex items-center gap-2">
			<img src="/images/logo.jpg" alt="EcoMotion logo" class="w-8 h-8 rounded-full shadow" />
			<span class="text-lg font-bold">EcoMotion</span>
		</a>
	</div>
  <nav class="flex flex-col">
    <a class="px-3 py-2 rounded-md <?= $active==='dashboard' ? 'bg-slate-800 text-white' : 'hover:bg-slate-800' ?>" href="<?= e(manager_base()) ?>">Dashboard</a>
    <a class="px-3 py-2 rounded-md <?= $active==='vehicles' ? 'bg-slate-800 text-white' : 'hover:bg-slate-800' ?>" href="<?= e(manager_base()) ?>/vehicles">Vehicles</a>
    <a class="px-3 py-2 rounded-md <?= $active==='users' ? 'bg-slate-800 text-white' : 'hover:bg-slate-800' ?>" href="<?= e(manager_base()) ?>/users">Users</a>
    <a class="px-3 py-2 rounded-md hover:bg-slate-800" href="#">Reservations</a>
    <a class="px-3 py-2 rounded-md hover:bg-slate-800" href="#">Payments</a>
    <a class="px-3 py-2 rounded-md hover:bg-slate-800" href="#">Reports</a>
    <a class="px-3 py-2 rounded-md hover:bg-slate-800" href="#">Support</a>
    <a class="px-3 py-2 rounded-md hover:bg-slate-800" href="#">Partners</a>
    <a class="px-3 py-2 rounded-md hover:bg-slate-800" href="#">Settings</a>
  </nav>
</aside>
