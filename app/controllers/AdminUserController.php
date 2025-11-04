<?php

require_once __DIR__ . '/../models/User.php';


class AdminUserController {

    private User $users;
    private int $tenantId;

    public function __construct() {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $this->tenantId = (int)($_SESSION['tenant_id'] ?? ($_GET['tenant_id'] ?? 0));
        if ($this->tenantId <= 0) {
            $this->json(['error' => 'Missing tenant_id'], 400);
            exit;
        }

        if (($_SESSION['role'] ?? '') !== 'tenant_admin') {
            $this->json(['error' => 'Forbidden'], 403);
            exit;
        }
        $this->users = new User($this->tenantId);
    }

    
    // LIST USERS
    public function index(): void
    {
        try {
            $list = $this->users->getAll();
            $this->json($list ?: []);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Failed to list users'], 500);
        }
    }


    // SHOW "SUPERUSER" USER CREATE FORM
    public function createForm(): void
    {
        $this->render(__DIR__ . '/../views/user/create.php', ['show_role' => true]);
    }


    //SAVE NEW USERS
    public function store(): void
    {
        $input = $this->readJsonBody() ?? $_POST;
        $name = trim((string)($input['name'] ?? ''));
        $email = trim((string)($input['email'] ?? ''));
        $password = (string)($input['password'] ?? '');
        $role = $input['role'] ?? null;


        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
            if (!empty($_POST)) {
                $this->render(__DIR__ . '/../views/user/create.php', ['errors' => ['Invalid input'], 'show_role' => true]);
                return;
            }
            $this->json(['error' => 'Invalid input'], 422);
            return;
        }

        try {
            if ($role !== null && $role !== '') {
                $id = $this->users->createUserWithRole($name, $email, $password, (string)$role);
            } else {
                $id = $this->users->register($name, $email, $password);
            }

            if ($id === false) {
                if (!empty($_POST)) {
                    $this->render(__DIR__ . '/../views/user/create.php', ['errors' => ['User not created'], 'show_role' => true]);
                    return;
                }
                $this->json(['error' => 'user not created'], 500);
                return;
            }

            if (!empty($_POST)) {
                $rows = $this->users->find(['id' => $id]);
                $created = !empty($rows) ? $rows[0] : null;
                $this->render(__DIR__ . '/../views/user/create.php', ['created' => $created, 'show_role' => true]);
                return;
            }

            $this->json(['id' => $id], 201);
        } catch (\Throwable $e) {
            if (!empty($_POST)) {
                $this->render(__DIR__ . '/../views/user/create.php', ['errors' => [$e->getMessage()], 'show_role' => true]);
                return;
            }
            $this->json(['error' => $e->getMessage()], 400);
        }
    }


    // UPDATE USERS PROFILE
    public function update(int $id): void
    {
        $id = (int)$id;
        if ($id <= 0) {
            $this->json(['error' => 'Invalid user id'], 400);
            return;
        }
        $input = $this->readJsonBody() ?? $_POST;
        $data = [];
        if (array_key_exists('name', $input))  { $data['name'] = trim((string)$input['name']); }
        if (array_key_exists('email', $input)) { $data['email'] = trim((string)$input['email']); }
        if (array_key_exists('phone', $input)) { $data['phone'] = trim((string)$input['phone']); }
        if (array_key_exists('accessibility_flags', $input)) {
            $data['accessibility_flags'] = $input['accessibility_flags'];
        }

        try {
            $ok = $this->users->updateDetails($id, $data);
            $this->json(['updated' => (bool)$ok]);
        } catch (\Throwable $e) {
            $this->json(['error' => $e->getMessage()], 400);
        }
    }

    //CHANGE USER PASSWORD
    public function changePassword(int $id): void
    {
        $id = (int)$id;
        if ($id <= 0) {
            $this->json(['error' => 'Invalid user id'], 400);
            return;
        }
        $input = $this->readJsonBody() ?? $_POST;
        $password = (string)($input['password'] ?? '');
        if (strlen($password) < 6) {
            $this->json(['error' => 'Password too short'], 422);
            return;
        }
        try {
            $ok = $this->users->changePassword($id, $password);
            $this->json(['updated' => (bool)$ok]);
        } catch (\Throwable $e) {
            $this->json(['error' => $e->getMessage()], 400);
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
