<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../models/Vehicle.php';

class ManagerDashboardController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireRole(['manager']);
    }

    // GET /manager -> Overview only (no user CRUD here)
    public function index(): void
    {
        $tenantId = $this->resolveTenant();
        [$vehicles, $totalVehicles] = $this->loadVehiclesSummary($tenantId);
        $this->renderDashboard($vehicles, $totalVehicles);
    }

    // ---- Helpers ----
    private function resolveTenant(): int
    {
        $tenantId = method_exists('TenantContext', 'tenantId') ? (int)(TenantContext::tenantId() ?? 0) : 0;
        return $tenantId > 0 ? $tenantId : $this->requireTenant();
    }

    private function loadVehiclesSummary(int $tenantId): array
    {
        $vehicles = null;
        $total = 0;
        try {
            $vehModel = new Vehicle($tenantId);
            $list = $vehModel->listAll();
            if (!empty($list)) {
                $vehicles = $list;
                $total = count($list);
            }
        } catch (\Throwable $e) {
            // allow fallback
        }
        return [$vehicles, $total];
    }

    private function renderDashboard(?array $vehicles, int $totalVehicles): void
    {
        $this->render(__DIR__ . '/../../views/manager/ManagerDashboard.php', [
            'vehicles' => $vehicles,
            'totalVehicles' => $totalVehicles ?: 45,
            'activeReservations' => 12,
            'dailyRevenue' => 1230,
            'layout' => __DIR__ . '/../../views/layouts/app.php',
            'title' => 'EcoMotion Manager — Dashboard',
            'scripts' => ['/js/manager-dashboard.js'],
        ]);
    }
}
