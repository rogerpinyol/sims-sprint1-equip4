<?php

// Managers login, logout

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../models/User.php';

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
        ]);
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start();
            $token = $_POST['csrf_token'] ?? '';
            if (!hash_equals($_SESSION['csrf_token'] ?? '', (string)$token)) {
                $_SESSION['flash_errors'] = ['Token CSRF inválido'];
                header('Location: /manager/login');
                exit;
            }
        }

        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $tenantId = (int)($_SESSION['tenant_id'] ?? 0);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            $_SESSION['flash_errors'] = ['Credenciales inválidas'];
            $_SESSION['flash_old'] = ['email' => $email];
            header('Location: /manager/login');
            exit;
        }

        $row = null;
        $userModel = null;

        // If we already have a tenant, try tenant-scoped auth first
        if ($tenantId > 0) {
            $userModel = new User($tenantId);
            $row = $userModel->authenticate($email, $password);
        }

        // If tenant not set or auth failed, try to resolve by authenticating across tenants
        if (!$row) {
            try {
                $any = (new User(0))->authenticateAnyTenant($email, $password);
                if ($any) {
                    // Only allow manager role here
                    if (($any['role'] ?? '') !== 'manager') {
                        $_SESSION['flash_errors'] = ['Solo los usuarios con rol Manager pueden iniciar sesión aquí.'];
                        $_SESSION['flash_old'] = ['email' => $email];
                        header('Location: /manager/login');
                        exit;
                    }
                    // Set resolved tenant and proceed with that scope
                    $_SESSION['tenant_id'] = (int)$any['tenant_id'];
                    $tenantId = (int)$any['tenant_id'];
                    $userModel = new User($tenantId);
                    $row = $userModel->getById((int)$any['id']);
                }
            } catch (\Throwable $e) {
                // ignore lookup errors
            }
        }

        if (!$row) {
            // No auth match; if we still have no tenant set, give a clear hint for dev
            if ($tenantId <= 0) {
                $_SESSION['flash_errors'] = ['No se pudo resolver tu empresa automáticamente. Añade ?tenant o ?tenant_id, o verifica tus credenciales.'];
            } else {
                $_SESSION['flash_errors'] = ['Email o contraseña incorrectos'];
            }
            $_SESSION['flash_old'] = ['email' => $email];
            header('Location: /manager/login');
            exit;
        }

        // Only allow managers to login here
        $role = (string)($row['role'] ?? '');
        if ($role !== 'manager') {
            $_SESSION['flash_errors'] = ['Solo los usuarios con rol Manager pueden iniciar sesión aquí.'];
            $_SESSION['flash_old'] = ['email' => $email];
            header('Location: /manager/login');
            exit;
        }

        // Auth ok for manager
        $_SESSION['user_id'] = (int)$row['id'];
        $_SESSION['role'] = 'manager';
        header('Location: /manager');
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
}
