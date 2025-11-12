<?php

require_once __DIR__ . '/../../core/Controller.php';

class ManagerDashboardController extends Controller
{
    private int $tenantId = 0;

    public function __construct()
    {
        parent::__construct();
        $this->tenantId = (int)($_SESSION['tenant_id'] ?? 0);
        $this->requireRole(['manager']);
    }

    // GET /manager -> Overview only (no user CRUD here)
    public function index(): void
    {
        $this->render(__DIR__ . '/../../views/manager/ManagerDashboard.php', [
            'totalVehicles' => 45,
            'activeReservations' => 12,
            'dailyRevenue' => 1230,
        ]);
    }
}
