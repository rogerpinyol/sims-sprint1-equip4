<?php

// Load the User model
require_once __DIR__ . '/../models/User.php';

// Basic user controller (no framework)
class UserController {
    
    // Model instance (scoped to tenant)
    private User $users;
    // Current tenant id
    private int $tenantId;

    public function __construct() {
        // Ensure session is started
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Get tenant from session or query
        $this->tenantId = (int)($_SESSION['tenant_id'] ?? ($_GET['tenant_id'] ?? 0));

        // Guard: tenant required
        if ($this->tenantId <= 0) {
            $this->json(['error' => 'Missing tenant_id'], 400);
            exit;
        }

        // Init model
        $this->users = new User($this->tenantId);
    }

    // GET /users -> list users
    public function index(): void {
        try {
            $list = $this->users->getAll();
            $this->json($list ?: []);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Failed to list users'], 500);
        }
    }

    // POST /users -> create user (role requires admin)
    public function store(): void {
        // Accept JSON or form data
        $input = $this->readJsonBody() ?? $_POST;

        // Basic input
        $name = trim((string)($input['name'] ?? ''));
        $email = trim((string)($input['email'] ?? ''));
        $password = (string)($input['password'] ?? '');
        $role = $input['role'] ?? null;

        // Validate
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
            $this->json(['error' => 'Invalid input'], 422);
            return;
        }

        try {
            // Create with role (admin only) or regular register
            if ($role !== null && $role !== '') {
                $this->requireTenantAdmin();
                $id = $this->users->createUserWithRole($name, $email, $password, (string)$role);
            } else {
                $id = $this->users->register($name, $email, $password);
            }

            if ($id === false) {
                $this->json(['error' => 'user not created'], 500);
                return;
            }

            $this->json(['id' => $id], 201);
        } catch (\Throwable $e) {
            $this->json(['error' => $e->getMessage()], 400);
        }
    }

    // PUT/PATCH /users/{id} -> update fields
    public function update(int $id): void {
        $id = (int)$id;
        if ($id <= 0) {
            $this->json(['error' => 'Invalid user id'], 400);
            return;
        }

        // Accept JSON or form data
        $input = $this->readJsonBody() ?? $_POST;

        // Only allowed fields
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

    // PATCH /users/{id}/password -> change password
    public function changePassword(int $id): void {
        $id = (int)$id;
        if ($id <= 0) {
            $this->json(['error' => 'Invalid user id'], 400);
            return;
        }
        
        // Accept JSON or form data
        $input = $this->readJsonBody() ?? $_POST;
        $password = (string)($input['password'] ?? '');

        // Validate password
        if (strlen($password) < 6) {
            $this->json(['error' => 'Password too short'], 422);
            return;
        }

        // Auth: same user or tenant admin
        $sessionUserId = (int)($_SESSION['user_id'] ?? 0);
        if (!$this->isTenantAdmin() && $sessionUserId !== $id) {
            $this->json(['error' => 'Forbidden'], 403);
            return;
        }

        try {
            $ok = $this->users->changePassword($id, $password);
            $this->json(['updated' => (bool)$ok]);
        } catch (\Throwable $e) {
            $this->json(['error' => $e->getMessage()], 400);
        }
    }

    // ---- Auth helpers ----

    // Check admin role
    private function isTenantAdmin(): bool {
        return ($_SESSION['role'] ?? null) === 'tenant_admin';
    }

    // Enforce admin role
    private function requireTenantAdmin(): void {
        if (!$this->isTenantAdmin()) {
            $this->json(['error' => 'Forbidden'], 403);
            exit;
        }
    }

    // ---- IO helpers ----

    // Read JSON body as array (or null)
    private function readJsonBody(): ?array {
        $raw = file_get_contents('php://input');
        if ($raw === false || $raw === '') {
            return null;
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : null;
    }

    // Send JSON response with status
    private function json($data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}