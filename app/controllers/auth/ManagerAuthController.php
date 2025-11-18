<?php

// Managers login, logout

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../services/ManagerAuthService.php';

class ManagerAuthController extends Controller
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
        $this->render(__DIR__ . '/../../views/auth/ManagerLogin.php', [
            'errors' => $errors,
            'old' => $old,
            'success' => $success,
            'layout' => __DIR__ . '/../../views/layouts/app.php',
            'title' => 'Manager Login — EcoMotion',
        ]);
    }

    public function login(): void
    {
        $this->verifyCsrfForPost(manager_base() . '/login');
        [$email, $password] = $this->getLoginInput();
        $svc = new ManagerAuthService();
        $valErrors = $svc->validateLoginInput($email, $password);
        if ($valErrors) {
            $this->failLogin($valErrors, $email);
            return;
        }
        // Always authenticate across all tenants for managers
        $row = $svc->authenticate(0, $email, $password);
        if (!$row) {
            $this->failLogin(['Email o contraseña incorrectos'], $email);
            return;
        }
        if (!$svc->ensureManagerRole($row)) {
            $this->failLogin(['Only users with Manager role can log in here.'], $email);
            return;
        }
        // Get tenant from authenticated user
        $tenantId = isset($row['tenant_id']) ? (int)$row['tenant_id'] : 0;
        // Preserve actual role (could be manager or tenant_admin) and harden session
        $this->resetSessionSecurity();
        if ($tenantId > 0) {
            $_SESSION['tenant_id'] = $tenantId;
        }
        $_SESSION['user_id'] = (int)$row['id'];
        $_SESSION['role'] = (string)($row['role'] ?? 'manager');
        header('Location: ' . manager_base());
        exit;
    }

    public function logout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $tenantId = (int)($_SESSION['tenant_id'] ?? 0);
        session_unset();
        session_destroy();
        session_start();
        if ($tenantId > 0) $_SESSION['tenant_id'] = $tenantId;
        header('Location: /');
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
        header('Location: ' . manager_base() . '/login');
        exit;
    }

    private function resetSessionSecurity(): void
    {
        // Regenerate ID and rotate CSRF token
        if (session_status() !== PHP_SESSION_ACTIVE) return;
        $oldToken = $_SESSION['csrf_token'] ?? null;
        session_regenerate_id(true);
        // Clear all except old token (optional reuse) then issue new
        $keep = $oldToken; foreach (array_keys($_SESSION) as $k) { unset($_SESSION[$k]); }
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        // Could store previous token if needed for parallel tabs
    }
}
