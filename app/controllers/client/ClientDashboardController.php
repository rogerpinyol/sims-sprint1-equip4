<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/User.php';

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
            header('Location: /login');
            exit;
        }
        // Optional: restrict to non-admin roles
        $role = $_SESSION['role'] ?? 'client';
        if (in_array($role, ['tenant_admin','super_admin'], true)) {
            header('Location: /admin');
            exit;
        }
        $tenant = (int)($_SESSION['tenant_id'] ?? 0);
        $this->render(__DIR__ . '/../views/user/index.php', [
            'users' => [], // placeholder content for now
            'tenant_id' => $tenant,
        ]);
    }
}
