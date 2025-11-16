<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
  <meta charset="utf-8">
  <title>Gestió de Vehicles - EcoMotion</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="/images/logo.jpg" type="image/jpeg">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="/css/brand.css" />
  <style>
    .btn {
      display: inline-flex;
      align-items: center;
      padding: 0.5rem 1rem;
      border-radius: 0.375rem;
      font-weight: 500;
      transition: all 0.2s;
      cursor: pointer;
      border: none;
    }
    .btn-primary {
      background-color: #3b82f6;
      color: white;
    }
    .btn-primary:hover {
      background-color: #2563eb;
    }
    .btn-secondary {
      background-color: #64748b;
      color: white;
    }
    .btn-secondary:hover {
      background-color: #475569;
    }
    .card {
      background: white;
      border: 1px solid #e2e8f0;
      border-radius: 0.75rem;
      padding: 1rem;
    }
  </style>
</head>
<body class="bg-slate-100 h-full">
<?php 
// Include helper functions
require_once __DIR__ . '/../../core/Controller.php';
if (!function_exists('e')) {
    function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('manager_base')) {
    function manager_base(): string {
        $mb = getenv('MANAGER_BASE') ?: '/ecomotion-manager';
        if ($mb === '' || $mb === false) return '/ecomotion-manager';
        if ($mb[0] !== '/') $mb = '/' . $mb;
        if ($mb !== '/' && substr($mb, -1) === '/') $mb = rtrim($mb, '/');
        return $mb;
    }
}
$active = 'vehicles'; 
?>
<div class="bg-slate-50 text-slate-900">
  <?php include __DIR__ . '/../partials/manager/mobileSidebar.php'; ?>

  <div class="min-h-screen grid grid-cols-1 md:grid-cols-[240px_1fr]">
    <?php include __DIR__ . '/../partials/manager/sidebar.php'; ?>

    <!-- Main -->
    <main class="flex flex-col">
      <?php include __DIR__ . '/../partials/manager/header.php'; ?>

      <div class="p-4 space-y-4">
        <div class="flex justify-between items-center">
          <h2 class="text-2xl font-bold">Gestió de Vehicles</h2>
          <a href="/vehicle/create" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-1" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Afegir Vehicle
          </a>
        </div>

        <?php if (empty($vehicles)): ?>
          <div class="card">
            <p class="text-slate-600">No hi ha vehicles registrats. <a href="/vehicle/create" class="text-blue-600 hover:underline font-semibold">Afegeix el primer!</a></p>
          </div>
        <?php else: ?>
          <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
            <table class="table w-full">
                <thead class="bg-slate-100">
                    <tr>
                        <th class="py-3 px-4 text-left text-xs font-semibold text-slate-700 uppercase">VIN</th>
                        <th class="py-3 px-4 text-left text-xs font-semibold text-slate-700 uppercase">Model</th>
                        <th class="py-3 px-4 text-center text-xs font-semibold text-slate-700 uppercase">Bateria</th>
                        <th class="py-3 px-4 text-center text-xs font-semibold text-slate-700 uppercase">Estat</th>
                        <th class="py-3 px-4 text-center text-xs font-semibold text-slate-700 uppercase">Accions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <?php foreach ($vehicles as $v): ?>
                    <tr class="hover:bg-slate-50">
                        <td class="py-3 px-4 font-mono text-sm"><?= htmlspecialchars($v['vin'] ?? '') ?></td>
                        <td class="py-3 px-4 font-semibold"><?= htmlspecialchars($v['model'] ?? '') ?></td>
                        <td class="py-3 px-4 text-center">
                            <span class="inline-block px-2 py-1 bg-slate-100 text-slate-900 rounded text-sm font-medium">
                                <?= htmlspecialchars($v['battery_capacity'] ?? '0') ?> kWh
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <?php 
                            $status = $v['status'] ?? 'offline';
                            $statusColors = [
                                'available' => 'bg-green-100 text-green-800',
                                'booked' => 'bg-blue-100 text-blue-800',
                                'charging' => 'bg-yellow-100 text-yellow-800',
                                'maintenance' => 'bg-red-100 text-red-800',
                            ];
                            $colorClass = $statusColors[$status] ?? 'bg-slate-100 text-slate-800';
                            ?>
                            <span class="inline-block px-2 py-1 rounded text-xs font-semibold <?= $colorClass ?>"><?= ucfirst($status) ?></span>
                        </td>
                        <td class="py-3 px-4 text-center space-x-2">
                            <a href="/vehicle/edit?id=<?= htmlspecialchars($v['id']) ?>" class="inline-block px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs transition">Editar</a>
                            <button type="button" data-href="/vehicle/delete?id=<?= htmlspecialchars($v['id']) ?>" data-name="<?= htmlspecialchars($v['model'] ?? $v['vin'] ?? 'vehicle') ?>" class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded text-xs transition js-delete">Eliminar</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </main>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Mobile sidebar toggle
const btnOpen = document.getElementById('btnOpenSidebar');
const btnClose = document.getElementById('btnCloseSidebar');
const mobileSidebar = document.getElementById('mobileSidebar');
const overlay = document.getElementById('mobileSidebarOverlay');
if (btnOpen) btnOpen.addEventListener('click', () => {
  if (mobileSidebar) mobileSidebar.classList.remove('-translate-x-full');
  if (overlay) { overlay.classList.remove('pointer-events-none', 'opacity-0'); }
});
if (btnClose) btnClose.addEventListener('click', () => {
  if (mobileSidebar) mobileSidebar.classList.add('-translate-x-full');
  if (overlay) { overlay.classList.add('pointer-events-none', 'opacity-0'); }
});
if (overlay) overlay.addEventListener('click', () => {
  if (mobileSidebar) mobileSidebar.classList.add('-translate-x-full');
  overlay.classList.add('pointer-events-none', 'opacity-0');
});

// Delete confirmation
document.querySelectorAll('.js-delete').forEach(btn => {
  btn.addEventListener('click', function() {
    const href = this.getAttribute('data-href');
    const name = this.getAttribute('data-name');
    Swal.fire({
      title: '¿Estás seguro?',
      text: `Vas a eliminar el vehículo: ${name}`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = href;
      }
    });
  });
});
</script>
</body>
</html>