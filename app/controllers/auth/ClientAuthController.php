<?php
// Clients register and login

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../services/ClientAuthService.php';

class ClientAuthController extends Controller
{
    public function form(): void
    {
        $errors = $_SESSION['flash_errors'] ?? [];
        unset($_SESSION['flash_errors']);
        $success = !empty($_SESSION['flash_success']);
        unset($_SESSION['flash_success']);
        $old = $_SESSION['flash_old'] ?? [];
        unset($_SESSION['flash_old']);

        $this->render(__DIR__ . '/../../views/auth/register.php', [
              'show_role' => false, // mantiene mismo nombre de vista
            'errors' => $errors,
            'success' => $success,
            'old' => $old,
            'layout' => __DIR__ . '/../../views/layouts/app.php',
            'title' => 'Register — EcoMotion',
        ]);
    }

    public function register(): void
    {
        // 1) CSRF check (only for HTML form POST)
        $this->verifyCsrfForPost('/register');

        // 2) Gather inputs
        [$name, $email, $password] = $this->getRegisterInput();
        // Rol por defecto ya que el selector fue eliminado de la vista
        $role = 'client';

        // 3) Validate
        $svc = new ClientAuthService();
        $errors = $svc->validateRegistrationInput($name, $email, $password, $role);
        if (!empty($errors)) {
            $this->handleRegisterErrors($errors, $name, $email);
            return;
        }

        // 4) Perform registration (single responsibility delegated to service/model)
        try {
            $tenantId = (int)($_SESSION['tenant_id'] ?? 0);
                $result = $svc->registerClient($tenantId, $name, $email, $password, $role);
            $_SESSION['tenant_id'] = (int)$result['tenant_id'];
            unset($_SESSION['user_id'], $_SESSION['role']);
                // Auto-login if tenant_admin
                if ($role === 'tenant_admin') {
                    $_SESSION['user_id'] = (int)$result['user_id'];
                    $_SESSION['role'] = 'tenant_admin';
                    header('Location: /admin/tenants');
                    exit;
                }
            $this->handleRegisterSuccess($email, (int)$result['tenant_id']);
        } catch (Throwable $e) {
            $this->handleRegisterException($e, $name, $email);
        }
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
            // Render new client login view
            $this->render(__DIR__ . '/../../views/auth/Login.php', [
            'errors' => $errors,
            'old' => $old,
            'success' => $success,
            'layout' => __DIR__ . '/../../views/layouts/app.php',
            'title' => 'Login Cliente — EcoMotion',
        ]);
    }

    public function login(): void
    {
        // 1) CSRF for HTML form
        $this->verifyCsrfForPost('/auth/login');

        // 2) Input & validation
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $svc = new ClientAuthService();
        $valErrors = $svc->validateLoginInput($email, $password);
        if (!empty($valErrors)) {
            $this->loginFail($valErrors, $email);
            return;
        }

        // 3) Authenticate (with or without tenant pre-resolved)
        $tenantId = (int)($_SESSION['tenant_id'] ?? 0);
        $row = $svc->authenticate($email, $password, $tenantId);
        if (!$row) {
            $this->loginFail(['Email o contraseña incorrectos'], $email);
            return;
        }

        // If row didn't include tenant_id and none in session, block (shouldn't happen if svc works)
        if ($tenantId <= 0) {
            if (!isset($row['tenant_id'])) {
                $this->loginFail(['No se pudo resolver tu empresa automáticamente. Añade ?tenant o usa subdominio.'], $email);
                return;
            }
            $_SESSION['tenant_id'] = (int)$row['tenant_id'];
        }

        // 4) Session set and redirect
        $_SESSION['user_id'] = (int)$row['id'];
        $_SESSION['role'] = (string)($row['role'] ?? 'client');
        header('Location: /client/dashboard');
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

    // ------------ Private helpers (single-purpose) ------------

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

    private function getRegisterInput(): array
    {
        $input = $this->parseJsonBody() ?: $_POST;
        $name = trim((string)($input['name'] ?? ''));
        $email = trim((string)($input['email'] ?? ''));
        $password = (string)($input['password'] ?? '');
        return [$name, $email, $password];
    }

    private function handleRegisterErrors(array $errors, string $name, string $email): void
    {
        if (!empty($_POST)) {
            $_SESSION['flash_errors'] = $errors;
            $_SESSION['flash_old'] = ['name' => $name, 'email' => $email];
            header('Location: /register');
            exit;
        }
        $this->json(['errors' => $errors], 422);
    }

    private function handleRegisterSuccess(string $email, int $tenantId): void
    {
        if (!empty($_POST)) {
            $_SESSION['flash_success'] = true;
            $_SESSION['flash_old'] = ['email' => $email];
            header('Location: /auth/login');
            exit;
        }
        $this->json(['tenant_id' => $tenantId, 'registered' => true], 201);
    }

    private function handleRegisterException(\Throwable $e, string $name, string $email): void
    {
        if (!empty($_POST)) {
            $_SESSION['flash_errors'] = [$e->getMessage()];
            $_SESSION['flash_old'] = ['name' => $name, 'email' => $email];
            header('Location: /register');
            exit;
        }
        $status = ($e->getMessage() === 'Email already used') ? 409 : 500;
        $this->json(['error' => $e->getMessage()], $status);
    }

    private function loginFail(array $errors, string $email): void
    {
        $_SESSION['flash_errors'] = $errors;
        $_SESSION['flash_old'] = ['email' => $email];
        header('Location: /auth/login');
        exit;
    }
}
