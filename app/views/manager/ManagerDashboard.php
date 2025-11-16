<?php
$totalVehicles = $totalVehicles ?? 45;
$activeReservations = $activeReservations ?? 12;
$dailyRevenue = $dailyRevenue ?? 1230;
?>
<div class="bg-slate-50 text-slate-900">
  <?php $active='dashboard'; include __DIR__ . '/../partials/manager/mobileSidebar.php'; ?>

  <div class="min-h-screen grid grid-cols-1 md:grid-cols-[240px_1fr]">
    <?php $active='dashboard'; include __DIR__ . '/../partials/manager/sidebar.php'; ?>

    <!-- Main -->
    <main class="flex flex-col">
      <?php include __DIR__ . '/../partials/manager/header.php'; ?>

      <div class="p-4 space-y-4">
        <h2 class="text-base font-semibold">Overview</h2>
        <!-- Stat cards -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <div class="card">
            <div class="text-slate-500 text-xs">Vehículos Disponibles</div>
            <div class="text-3xl font-extrabold mt-1"><?= e(number_format($totalVehicles)) ?></div>
          </div>
          <div class="card">
            <div class="text-slate-500 text-xs">Reservas Activas</div>
            <div class="text-3xl font-extrabold mt-1"><?= e(number_format($activeReservations)) ?></div>
          </div>
          <div class="card">
            <div class="text-slate-500 text-xs">Ingresos Diarios</div>
            <div class="text-3xl font-extrabold mt-1">€<?= e(number_format($dailyRevenue, 0, ',', '.')) ?></div>
          </div>
        </section>

        <!-- Charts placeholder -->
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-4">
          <div class="bg-white border border-slate-200 rounded-xl p-4 min-h-[260px]">
            <h3 class="text-sm font-semibold mb-2">Vehicles</h3>
            <?php
            // Load vehicles from database if not set
            if (!isset($vehicles)) {
              $vehicles = [];
              try {
                $tenantId = method_exists('TenantContext', 'tenantId') ? (int)(TenantContext::tenantId() ?? 0) : 0;
                if ($tenantId > 0) {
                  require_once __DIR__ . '/../../models/Vehicle.php';
                  $vehicleModel = new Vehicle($tenantId);
                  $vehicles = $vehicleModel->listAll() ?: [];
                }
              } catch (Exception $e) {
                $vehicles = [];
              }
            }
            ?>
            <div class="h-96 overflow-y-auto">
              <table class="table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>VIN</th>
                    <th>Modelo</th>
                    <th>Estado</th>
                    <th>Batería</th>
                    <th>Lat</th>
                    <th>Lng</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($vehicles as $v): ?>
                  <tr>
                    <td><?= e($v['id']) ?></td>
                    <td><?= e($v['vin']) ?></td>
                    <td><?= e($v['model']) ?></td>
                    <td>
                      <?php
                        $status = strtolower($v['status']);
                        $color = match($status) {
                          'available' => 'text-green-600',
                          'booked' => 'text-blue-600',
                          'charging' => 'text-yellow-600',
                          'maintenance' => 'text-red-600',
                          default => 'text-slate-500',
                        };
                      ?>
                      <span class="font-semibold <?= e($color) ?>"><?= e(ucfirst($v['status'])) ?></span>
                    </td>
                    <td><?= e($v['battery_level']) ?>%</td>
                    <td><?= e($v['lat']) ?></td>
                    <td><?= e($v['lng']) ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
          <div class="bg-white border border-slate-200 rounded-xl p-4 min-h-[260px]">
            <h3 class="text-sm font-semibold mb-2">Mapa de Vehículos</h3>
            <div id="vehiclesMap" data-vehicles='<?= e(json_encode($vehicles ?? [])) ?>' class="h-96 rounded-md bg-slate-200"></div>
          </div>
        </section>

      </div>
      <footer class="mt-auto w-full text-center text-slate-500 text-xs py-4 border-t border-slate-100 bg-white">EcoMotion © <?= date('Y') ?> | Version 1.0</footer>
    </main>
  </div>

  
</div>
