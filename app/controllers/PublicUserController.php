<?php

require_once __DIR__ . '/../models/User.php';


class PublicUserController
{
    // Show profile edit form for logged-in users
    public function profile(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        $userId = (int)$_SESSION['user_id'];
        $userModel = $this->users;
        $user = $userModel ? $userModel->getById($userId) : null;
        $errors = $_SESSION['flash_errors'] ?? [];
        unset($_SESSION['flash_errors']);
        $success = false;
        if (!empty($_SESSION['flash_success'])) {
            $success = true;
            unset($_SESSION['flash_success']);
        }
        $this->render(__DIR__ . '/../views/user/profile.php', [
            'user' => $user,
            'errors' => $errors,
            'success' => $success
        ]);
    }

    public function updateProfile(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        $userId = (int)$_SESSION['user_id'];
        $userModel = $this->users;
        if (!$userModel) {
            $_SESSION['flash_errors'] = ['User model not available.'];
            header('Location: /profile');
            exit;
        }
        $data = [
            'name' => trim((string)($_POST['name'] ?? '')),
            'email' => trim((string)($_POST['email'] ?? '')),
            'phone' => trim((string)($_POST['phone'] ?? '')),
            'accessibility_flags' => $_POST['accessibility_flags'] ?? null,
        ];
        try {
            $userModel->updateDetails($userId, $data);
            $_SESSION['flash_success'] = true;
        } catch (\Throwable $e) {
            $_SESSION['flash_errors'] = [$e->getMessage()];
        }
        header('Location: /profile');
        exit;
    }
    private ?User $users = null;
    private int $tenantId = 0;


    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $this->tenantId = (int)($_SESSION['tenant_id'] ?? 0);
        if ($this->tenantId > 0) {
            $this->users = new User($this->tenantId);
        }
    }

    // Register Form
    public function registerForm(): void
    {
        $errors = $_SESSION['flash_errors'] ?? [];
        unset($_SESSION['flash_errors']);
        $success = false;
        if (!empty($_GET['success'])) {
            $success = true;
        } elseif (!empty($_SESSION['flash_success'])) {
            $success = true;
            unset($_SESSION['flash_success']);
        }
        $old = $_SESSION['flash_old'] ?? [];
        unset($_SESSION['flash_old']);

        $this->render(__DIR__ . '/../views/auth/register.php', [
            'show_role' => false,
            'errors' => $errors,
            'success' => $success,
            'old' => $old,
        ]);
    }

    // Register Function
    public function register(): void {

        $input = $this->readJsonBody() ?? $_POST;
        $name = trim((string)($input['name'] ?? ''));
        $email = trim((string)($input['email'] ?? ''));
        $password = (string)($input['password'] ?? '');

        // CSRF basic check for form POSTs
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start();
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
        // Password: min 8, upper, lower, digit, symbol
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
            $this->json(['error' => $errors], 422);
            return;
        }
        try {
            $users = new User($this->tenantId);
            $result = $users->registerWithTenant($name, $email, $password);

            // Actualizar tenantId por si se creó
            $this->tenantId = (int)$result['tenant_id'];
            if (session_status() !== PHP_SESSION_ACTIVE) session_start();
            $_SESSION['tenant_id'] = $this->tenantId;
            if (!isset($_SESSION['user_id'])) $_SESSION['user_id'] = (int)$result['user_id'];
            if (!isset($_SESSION['role'])) $_SESSION['role'] = 'client';

            if (!empty($_POST)) {
                $_SESSION['flash_success'] = true;
                header('Location: /login');
                exit;
            }

            $this->json($result, 201);
        } catch (\Throwable $e) {
            // log y respuesta adecuada
            error_log('PublicUserController::register error: ' . $e->__toString());
            try {
                $logDir = __DIR__ . '/../../storage/logs';
                if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
                $msg = sprintf("[%s] %s in %s:%s\n", date('Y-m-d H:i:s'), $e->getMessage(), $e->getFile(), $e->getLine());
                $msg .= $e->getTraceAsString() . "\n----\n";
                @file_put_contents($logDir . '/public_register.log', $msg, FILE_APPEND);
            } catch (\Throwable $__) {}

            if (!empty($_POST)) {
                $_SESSION['flash_errors'] = [$e->getMessage()];
                header('Location: /register');
                exit;
            }
            $status = ($e->getMessage() === 'Email already used') ? 409 : 500;
            $this->json(['error' => $e->getMessage()], $status);
            return;
        }
    }

    // ----- Helpers -----
    private function readJsonBody(): ?array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || $raw === '') return null;
        $data = json_decode($raw, true);
        return is_array($data) ? $data : null;
    }

    private function json($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    private function render(string $viewPath, array $vars = []): void
    {
        extract($vars, EXTR_SKIP);
        if (!is_file($viewPath)) {
            http_response_code(500);
            echo "View not found: " . htmlspecialchars($viewPath, ENT_QUOTES, 'UTF-8');
            return;
        }
        include $viewPath;
    }
}
