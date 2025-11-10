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
            'show_role' => false,
            'errors' => $errors,
            'success' => $success,
            'old' => $old,
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
            $_SESSION['tenant_id'] = (int)$result['tenant_id'];
            $_SESSION['user_id'] = (int)($result['user_id'] ?? 0);
            $_SESSION['role'] = $_SESSION['role'] ?? 'client';

            if (!empty($_POST)) {
                $_SESSION['flash_success'] = true;
                header('Location: /login');
                exit;
            }
            $this->json($result, 201);
        } catch (Throwable $e) {
            $this->logError($e, 'public_register');
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
        $this->render(__DIR__ . '/../../views/Client/login.php', [
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
                header('Location: /client/login');
                exit;
            }
        }

        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $tenantId = (int)($_SESSION['tenant_id'] ?? 0);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            $_SESSION['flash_errors'] = ['Credenciales inválidas'];
            $_SESSION['flash_old'] = ['email' => $email];
            header('Location: /client/login');
            exit;
        }

        if ($tenantId <= 0) {
            $_SESSION['flash_errors'] = ['Selecciona un tenant primero (usa ?tenant=acme o ?tenant_id=1 en dev)'];
            $_SESSION['flash_old'] = ['email' => $email];
            header('Location: /client/login');
            exit;
        }

        $userModel = new User($tenantId);
        $row = $userModel->authenticate($email, $password);
        if (!$row) {
            $_SESSION['flash_errors'] = ['Email o contraseña incorrectos'];
            $_SESSION['flash_old'] = ['email' => $email];
            header('Location: /client/login');
            exit;
        }

        // Auth ok
        $_SESSION['user_id'] = (int)$row['id'];
        $_SESSION['role'] = (string)($row['role'] ?? 'client');
        header('Location: /client');
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
