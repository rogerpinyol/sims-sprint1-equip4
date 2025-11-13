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
        $tenantId = (int)($_SESSION['tenant_id'] ?? 0);
        $row = $svc->authenticate($tenantId, $email, $password);
        if (!$row) {
            $msg = ($tenantId <= 0) ? 'No se pudo resolver tu empresa automáticamente. Añade ?tenant o ?tenant_id.' : 'Email o contraseña incorrectos';
            $this->failLogin([$msg], $email);
            return;
        }
        if (!$svc->ensureManagerRole($row)) {
            $this->failLogin(['Solo los usuarios con rol Manager pueden iniciar sesión aquí.'], $email);
            return;
        }
        // Resolve tenant if not set
        if ($tenantId <= 0 && isset($row['tenant_id'])) {
            $_SESSION['tenant_id'] = (int)$row['tenant_id'];
        }
        $_SESSION['user_id'] = (int)$row['id'];
        $_SESSION['role'] = 'manager';
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
}
