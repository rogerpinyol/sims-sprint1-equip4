<?php

require_once __DIR__ . '/../models/User.php';

class AuthController
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

        $this->render(__DIR__ . '/../views/auth/login.php', [
            'errors' => $errors,
            'old' => $old,
        ]);
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start();
            $token = $_POST['csrf_token'] ?? '';
            if (!hash_equals($_SESSION['csrf_token'] ?? '', (string)$token)) {
                $_SESSION['flash_errors'] = ['Token CSRF inválido'];
                header('Location: /login');
                exit;
            }
        }

        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $tenantId = (int)($_SESSION['tenant_id'] ?? 0);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            $_SESSION['flash_errors'] = ['Credenciales inválidas'];
            $_SESSION['flash_old'] = ['email' => $email];
            header('Location: /login');
            exit;
        }

        if ($tenantId <= 0) {
            $_SESSION['flash_errors'] = ['Selecciona un tenant primero (usa ?tenant=acme o ?tenant_id=1 en dev)'];
            $_SESSION['flash_old'] = ['email' => $email];
            header('Location: /login');
            exit;
        }

        // Query user by (tenant_id, email)
        require_once __DIR__ . '/../../config/database.php';
        /** @var PDO $pdo */
        $pdo = $pdo ?? (class_exists('Database') ? Database::getInstance()->getConnection() : null);
        if (!$pdo) {
            $_SESSION['flash_errors'] = ['No hay conexión a base de datos'];
            header('Location: /login');
            exit;
        }

        $stmt = $pdo->prepare('SELECT id, password_hash, role FROM users WHERE email = :email AND tenant_id = :tid LIMIT 1');
        $stmt->execute(['email' => $email, 'tid' => $tenantId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || !password_verify($password, (string)$row['password_hash'])) {
            $_SESSION['flash_errors'] = ['Email o contraseña incorrectos'];
            $_SESSION['flash_old'] = ['email' => $email];
            header('Location: /login');
            exit;
        }

        // Auth ok
        $_SESSION['user_id'] = (int)$row['id'];
        $_SESSION['role'] = (string)($row['role'] ?? 'client');
        header('Location: /users');
        exit;
    }

    public function logout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        // keep tenant_id to avoid losing tenant context
        $tenantId = (int)($_SESSION['tenant_id'] ?? 0);
        session_unset();
        session_destroy();
        session_start();
        if ($tenantId > 0) $_SESSION['tenant_id'] = $tenantId;
        header('Location: /');
        exit;
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
