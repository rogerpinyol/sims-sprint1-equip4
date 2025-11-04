<?php

require_once __DIR__ . '/../models/User.php';


class PublicUserController
{
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

    // Render Register form (reads flash messages)
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
                // PRG: flash error then redirect
                $_SESSION['flash_errors'] = ['Token CSRF inválido'];
                header('Location: /register');
                exit;
            }
        }

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
            if (!empty($_POST)) {
                $_SESSION['flash_errors'] = ['Invalid input'];
                $_SESSION['flash_old'] = ['name' => $name, 'email' => $email];
                header('Location: /register');
                exit;
            }
            $this->json(['error' => 'Invalid input'], 422);
            return;
        }

        $pdo = $this->getPdo();
        try {
            $pdo->beginTransaction();

            if ($this->tenantId <= 0) {
                // create a lightweight tenant using email domain
                $domain = substr(strrchr($email, '@'), 1) ?: null;
                $tenantName = $domain ? explode('.', $domain)[0] : 'public';
                $sub = $tenantName . '-' . time();

                // ensure subdomain unique (simple attempt)
                $chk = $pdo->prepare('SELECT id FROM tenants WHERE subdomain = :sub');
                $chk->execute(['sub' => $sub]);
                if ($chk->fetchColumn()) {
                    // fallback to timestamped
                    $sub = $sub . '-' . rand(1000, 9999);
                }

                // attempt insert with retry on duplicate-subdomain race
                $attempts = 0;
                do {
                    try {
                        // match DB schema: column is plan_type and allowed values are 'standard','premium','enterprise'
                        $stmt = $pdo->prepare('INSERT INTO tenants (name, subdomain, plan_type, created_at) VALUES (?, ?, ?, NOW())');
                        $stmt->execute([$name, $sub, 'standard']);
                        $this->tenantId = (int)$pdo->lastInsertId();
                        break;
                    } catch (\PDOException $ex) {
                        // SQLSTATE 23000 usually signals unique constraint violation
                        if ($ex->getCode() === '23000' && $attempts < 3) {
                            $sub = $sub . '-' . rand(1000, 9999);
                            $attempts++;
                            continue;
                        }
                        throw $ex;
                    }
                } while ($attempts < 4);
            }
            $users = new User($this->tenantId);

            // check duplicate email within tenant
            $chkUser = $pdo->prepare('SELECT id FROM users WHERE email = :email AND tenant_id = :tid LIMIT 1');
            $chkUser->execute(['email' => $email, 'tid' => $this->tenantId]);
            if ($chkUser->fetchColumn()) {
                $pdo->rollBack();
                if (!empty($_POST)) {
                    $_SESSION['flash_errors'] = ['Email ya en uso'];
                    $_SESSION['flash_old'] = ['name' => $name];
                    header('Location: /register');
                    exit;
                }
                $this->json(['error' => 'Email already used'], 409);
                return;
            }

            $userId = $users->register($name, $email, $password);
            if ($userId === false) {
                $pdo->rollBack();
                throw new \RuntimeException('User creation failed');
            }

            $pdo->commit();
            // Persist tenant and user context in session for subsequent requests
            if (session_status() !== PHP_SESSION_ACTIVE) session_start();
            $_SESSION['tenant_id'] = $this->tenantId;
            if (!isset($_SESSION['user_id'])) $_SESSION['user_id'] = $userId;
            if (!isset($_SESSION['role'])) $_SESSION['role'] = 'client';
    } catch (\Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            // log full error for debugging (do not expose to users)
            error_log('PublicUserController::register error: ' . $e->__toString());
            // also write to storage log to make it easy to read during local debugging
            try {
                $logDir = __DIR__ . '/../../storage/logs';
                if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
                $msg = sprintf("[%s] %s in %s:%s\n", date('Y-m-d H:i:s'), $e->getMessage(), $e->getFile(), $e->getLine());
                $msg .= $e->getTraceAsString() . "\n----\n";
                @file_put_contents($logDir . '/public_register.log', $msg, FILE_APPEND);
            } catch (\Throwable $__) {
                // ignore logging failures
            }
            if (!empty($_POST)) {
                $_SESSION['flash_errors'] = ['Error interno, inténtalo más tarde.'];
                header('Location: /register');
                exit;
            }
            $this->json(['error' => 'Internal server error'], 500);
            return;
        }

        if (!empty($_POST)) {
            // PRG success redirect
            $_SESSION['flash_success'] = true;
            header('Location: /register?success=1');
            exit;
        }

        $this->json(['tenant_id' => $this->tenantId, 'user_id' => $userId], 201);
    }

    // ???
    public function getPdo(): \PDO
    {
        if (class_exists('Database')) {
            try {
                return Database::getInstance()->getConnection();
            } catch (\Throwable $e) {
                // fall through
            }
        }

        if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof \PDO) return $GLOBALS['pdo'];

        $dbFile = __DIR__ . '/../../config/database.php';
        if (is_file($dbFile)) {
            require_once $dbFile;
            if (isset($pdo) && $pdo instanceof \PDO) return $pdo;
            if (class_exists('Database')) return Database::getInstance()->getConnection();
        }

        throw new \RuntimeException('PDO not available');
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
