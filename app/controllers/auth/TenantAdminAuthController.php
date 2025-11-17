<?php

// Tenant Admin login/logout (platform-level)

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../services/ManagerAuthService.php'; // reuse validate/authenticate

class TenantAdminAuthController extends Controller
{
    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    }

    public function loginForm(): void
    {
        $errors = $_SESSION['flash_errors'] ?? [];
        unset($_SESSION['flash_errors']);
        $old = $_SESSION['flash_old'] ?? [];
        unset($_SESSION['flash_old']);
        $success = false;
        if (!empty($_SESSION['flash_success'])) {
            $success = true;
            unset($_SESSION['flash_success']);
        }
        $this->render(__DIR__ . '/../../views/auth/TenantAdminLogin.php', [
            'errors' => $errors,
            'old' => $old,
            'success' => $success,
            'layout' => __DIR__ . '/../../views/layouts/app.php',
            'title' => 'Tenant Admin Login — EcoMotion',
        ]);
    }

    public function login(): void
    {
        $this->verifyCsrfForPost('/admin/login');
        [$email, $password] = $this->getLoginInput();
        $svc = new ManagerAuthService();
        $valErrors = $svc->validateLoginInput($email, $password);
        if ($valErrors) {
            $this->failLogin($valErrors, $email);
            return;
        }
        $row = $svc->authenticate(0, $email, $password);
        if (!$row) {
            $this->failLogin(['Incorrect credentials'], $email);
            return;
        }
        if (strtolower((string)($row['role'] ?? '')) !== 'tenant_admin') {
            $this->failLogin(['Access restricted to Tenant Admin'], $email);
            return;
        }
        $this->resetSessionPreserving(['csrf_token']);
        $_SESSION['user_id'] = (int)$row['id'];
        $_SESSION['role'] = $row['role'];
        header('Location: /admin/tenants');
        exit;
    }

    public function logout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        session_unset();
        session_destroy();
        session_start();
        header('Location: /admin/login');
        exit;
    }

    // ---- Helpers ----
    private function verifyCsrfForPost(string $redirectOnFail): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', (string)$token)) {
            $_SESSION['flash_errors'] = ['Token CSRF inválido'];
            header('Location: ' . $redirectOnFail);
            exit;
        }
    }

    private function getLoginInput(): array
    {
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        return [$email, $password];
    }

    private function failLogin(array $errors, string $email): void
    {
        $_SESSION['flash_errors'] = $errors;
        $_SESSION['flash_old'] = ['email' => $email];
        header('Location: /admin/login');
        exit;
    }

    private function resetSessionPreserving(array $keysToKeep = []): void
    {
        $preserved = [];
        foreach ($keysToKeep as $k) {
            if (isset($_SESSION[$k])) $preserved[$k] = $_SESSION[$k];
        }
        session_regenerate_id(true);
        foreach (array_keys($_SESSION) as $k) { unset($_SESSION[$k]); }
        foreach ($preserved as $k => $v) { $_SESSION[$k] = $v; }
        if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    }
}
