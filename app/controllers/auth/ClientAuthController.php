<?php
// Clients register and login

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../models/User.php';

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
        $input = $this->parseJsonBody() ?: $_POST;
        $name = trim((string)($input['name'] ?? ''));
        $email = trim((string)($input['email'] ?? ''));
        $password = (string)($input['password'] ?? '');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!hash_equals($_SESSION['csrf_token'] ?? '', (string)$token)) {
                $_SESSION['flash_errors'] = ['Token CSRF inválido'];
                header('Location: /register');
                exit;
            }
        }

        $errors = [];
        if ($name === '' || strlen($name) < 2) {
            $errors[] = 'Name must be at least 2 characters.';
        } else if (!preg_match("/^[A-Za-zÀ-ÿ ]+$/u", $name)) {
            $errors[] = 'Name must contain only letters and spaces.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email is not valid.';
        }
        if (strlen($password) < 8
            || !preg_match('/[A-Z]/', $password)
            || !preg_match('/[a-z]/', $password)
            || !preg_match('/\d/', $password)
            || !preg_match('/[^A-Za-z\d]/', $password)) {
            $errors[] = 'Password must be at least 8 characters and include uppercase, lowercase, number, and symbol.';
        }

        if ($errors) {
            if (!empty($_POST)) {
                $_SESSION['flash_errors'] = $errors;
                $_SESSION['flash_old'] = ['name' => $name, 'email' => $email];
                header('Location: /register');
                exit;
            }
            $this->json(['errors' => $errors], 422);
            return;
        }

        try {
            $users = new User((int)($_SESSION['tenant_id'] ?? 0));
            $result = $users->registerWithTenant($name, $email, $password);
            // Keep tenant resolved so login works, but DO NOT auto log user in.
            $_SESSION['tenant_id'] = (int)$result['tenant_id'];
            // Ensure no auto-login remnants
            unset($_SESSION['user_id']);
            unset($_SESSION['role']);
            if (!empty($_POST)) {
                $_SESSION['flash_success'] = true; // Show success message in login
                $_SESSION['flash_old'] = ['email' => $email];
                header('Location: /auth/login');
                exit;
            }
            $this->json(['tenant_id' => $result['tenant_id'], 'registered' => true], 201);
        } catch (Throwable $e) {
            if (!empty($_POST)) {
                $_SESSION['flash_errors'] = [$e->getMessage()];
                header('Location: /register');
                exit;
            }
            $status = ($e->getMessage() === 'Email already used') ? 409 : 500;
            $this->json(['error' => $e->getMessage()], $status);
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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start();
            $token = $_POST['csrf_token'] ?? '';
            if (!hash_equals($_SESSION['csrf_token'] ?? '', (string)$token)) {
                $_SESSION['flash_errors'] = ['Token CSRF inválido'];
                header('Location: /auth/login');
                exit;
            }
        }

        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $tenantId = (int)($_SESSION['tenant_id'] ?? 0);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            $_SESSION['flash_errors'] = ['Credenciales inválidas'];
            $_SESSION['flash_old'] = ['email' => $email];
            header('Location: /auth/login');
            exit;
        }

        if ($tenantId <= 0) {
            // Try to resolve tenant by authenticating across tenants via User model
            try {
                    $lookup = (new User(0))->authenticateAnyTenant($email, $password);
                    if ($lookup) {
                        $_SESSION['tenant_id'] = (int)$lookup['tenant_id'];
                        $_SESSION['user_id'] = (int)$lookup['id'];
                        $_SESSION['role'] = (string)($lookup['role'] ?? 'client');
                        header('Location: /client');
                        exit;
                    }
            } catch (\Throwable $e) {
                // logging disabled
            }
            $_SESSION['flash_errors'] = ['No se pudo resolver tu empresa automáticamente. Añade ?tenant o inicia sesión tras registrarte.'];
            $_SESSION['flash_old'] = ['email' => $email];
            header('Location: /auth/login');
            exit;
        }

        $userModel = new User($tenantId);
        $row = $userModel->authenticate($email, $password);
        if (!$row) {
            $_SESSION['flash_errors'] = ['Email o contraseña incorrectos'];
            $_SESSION['flash_old'] = ['email' => $email];
            header('Location: /auth/login');
            exit;
        }

    // Auth ok
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
}
