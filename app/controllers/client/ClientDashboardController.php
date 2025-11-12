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
        // Require authenticated end user
        if (empty($_SESSION['user_id'])) {
            header('Location: /auth/login');
            exit;
        }
        // Optional: restrict to non-admin roles
        $role = $_SESSION['role'] ?? 'client';
        if (in_array($role, ['tenant_admin','super_admin'], true)) {
            header('Location: /manager');
            exit;
        }
        $tenant = (int)($_SESSION['tenant_id'] ?? 0);
        $userId = (int)($_SESSION['user_id']);
        $userModel = new User($tenant);
        $user = $userModel->getById($userId) ?: ['name' => 'Cliente', 'email' => ''];
        $this->render(__DIR__ . '/../../views/client/dashboard.php', [
            'user' => $user,
            'tenant_id' => $tenant,
        ]);
    }
}
