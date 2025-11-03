<?php

require_once __DIR__ . '/../models/User.php';

class PublicUserController {
    private ?User $users = null;
    private int $tenantId = 0;

    public function __construct() {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $this->tenantId = (int)($_SESSION['tenant_id'] ?? ($_GET['tenant_id'] ?? 0));
        if ($this->tenantId > 0) {
            $this->users = new User($this->tenantId);
        }
    }

    // GET /register
    public function registerForm(): void {
        $this->render(__DIR__ . '/../views/auth/register.php', ['show_role' => false]);
    }

    // POST /register
    public function register(): void {
        $input = $this->readJsonBody() ?? $_POST;
        $name = trim((string)($input['name'] ?? ''));
        $email = trim((string)($input['email'] ?? ''));
        $password = (string)($input['password'] ?? '');

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
            if (!empty($_POST)) {
                $this->render(__DIR__ . '/../views/auth/register.php', ['errors' => ['Invalid input'], 'show_role' => false]);
                return;
            }
            $this->json(['error' => 'Invalid input'], 422);
            return;
        }

        try {
            $pdo = $this->getPdo();

            // Begin transaction: create tenant (if needed) and create user atomically
            $pdo->beginTransaction();

            if ($this->tenantId <= 0) {
                // Create a lightweight tenant using email domain or default name
                $domain = substr(strrchr($email, '@'), 1) ?: null;
                $tenantName = $domain ? explode('.', $domain)[0] : 'public';
                $sub = $tenantName . '-' . time();

                // ensure subdomain unique (simple attempt)
                $chk = $pdo->prepare('SELECT id FROM tenants WHERE subdomain = :sub');
                $chk->execute(['sub' => $sub]);
                if ($chk->fetchColumn() !== false) {
                    // fallback make more unique
                    $sub .= '-' . bin2hex(random_bytes(4));
                }

                $stmt = $pdo->prepare('INSERT INTO tenants (name, subdomain, created_at) VALUES (:name, :subdomain, NOW())');
                $stmt->execute(['name' => $tenantName, 'subdomain' => $sub]);
                $this->tenantId = (int)$pdo->lastInsertId();
                // instantiate User model now that tenant exists
                $this->users = new User($this->tenantId);
                // store in session for subsequent requests
                $_SESSION['tenant_id'] = $this->tenantId;
            }

            // Check duplicate email for this tenant
            $dup = $pdo->prepare('SELECT id FROM users WHERE email = :email AND tenant_id = :t');
            $dup->execute(['email' => $email, 't' => $this->tenantId]);
            if ($dup->fetchColumn() !== false) {
                $pdo->rollBack();
                if (!empty($_POST)) {
                    $this->render(__DIR__ . '/../views/auth/register.php', ['errors' => ['Email already in use'], 'show_role' => false]);
                    return;
                }
                $this->json(['error' => 'Email already in use'], 409);
                return;
            }

            $id = $this->users->register($name, $email, $password);
            if ($id === false) {
                $pdo->rollBack();
                if (!empty($_POST)) {
                    $this->render(__DIR__ . '/../views/auth/register.php', ['errors' => ['User not created'], 'show_role' => false]);
                    return;
                }
                $this->json(['error' => 'user not created'], 500);
                return;
            }

            $pdo->commit();
            if ($id === false) {
                if (!empty($_POST)) {
                    $this->render(__DIR__ . '/../views/auth/register.php', ['errors' => ['User not created'], 'show_role' => false]);
                    return;
                }
                $this->json(['error' => 'user not created'], 500);
                return;
            }

            if (!empty($_POST)) {
                $rows = $this->users->find(['id' => $id]);
                $created = !empty($rows) ? $rows[0] : null;
                $this->render(__DIR__ . '/../views/auth/register.php', ['created' => $created, 'show_role' => false]);
                return;
            }

            $this->json(['id' => $id], 201);
        } catch (\Throwable $e) {
            if (!empty($_POST)) {
                $this->render(__DIR__ . '/../views/auth/register.php', ['errors' => [$e->getMessage()], 'show_role' => false]);
                return;
            }
            $this->json(['error' => $e->getMessage()], 400);
        }
    }

    // Helper to expose PDO for controller-level queries (kept minimal)
    private function getPdo(): PDO {
        // load DB config which exposes $pdo
        require_once __DIR__ . '/../../config/database.php';
        if (!isset($pdo) || !($pdo instanceof PDO)) throw new RuntimeException('PDO not available');
        return $pdo;
    }

    // ---- helpers ----
    private function readJsonBody(): ?array {
        $raw = file_get_contents('php://input');
        if ($raw === false || $raw === '') return null;
        $data = json_decode($raw, true);
        return is_array($data) ? $data : null;
    }

    private function json($data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    private function render(string $viewPath, array $vars = []): void {
        extract($vars, EXTR_SKIP);
        if (!is_file($viewPath)) {
            http_response_code(500);
            echo "View not found: " . htmlspecialchars($viewPath, ENT_QUOTES, 'UTF-8');
            return;
        }
        include $viewPath;
    }
}
