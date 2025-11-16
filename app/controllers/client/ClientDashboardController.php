<?php
// Correct relative paths (this file is in app/controllers/client)
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../models/User.php';

class ClientDashboardController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(): void
    {
        $this->ensureClientSession();
        $this->redirectIfAdminRole();
        $tenant = $this->resolveTenant();
        $user = $this->loadDashboardUser($tenant);
        $this->renderDashboard($user, $tenant);
    }

    // ---- Helpers (single responsibility) ----
    private function ensureClientSession(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: /auth/login');
            exit;
        }
    }

    private function redirectIfAdminRole(): void
    {
        $role = $_SESSION['role'] ?? 'client';
        if (in_array($role, ['tenant_admin'], true)) {
            header('Location: /manager');
            exit;
        }
    }

    private function resolveTenant(): int
    {
        $tenant = method_exists('TenantContext', 'tenantId') ? (int)(TenantContext::tenantId() ?? 0) : 0;
        return $tenant > 0 ? $tenant : $this->requireTenant();
    }

    private function loadDashboardUser(int $tenant): array
    {
        $userId = (int)($_SESSION['user_id']);
        $userModel = new User($tenant);
        return $userModel->getById($userId) ?: ['name' => 'Client', 'email' => ''];
    }

    private function renderDashboard(array $user, int $tenant): void
    {
        $this->render(__DIR__ . '/../../views/client/dashboard.php', [
            'user' => $user,
            'tenant_id' => $tenant,
            'layout' => __DIR__ . '/../../views/layouts/app.php',
            'title' => 'EcoMotion - Mapa de Vehículos',
            'scripts' => ['/js/dashboard.js'],
        ]);
    }
}
