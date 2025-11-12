<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../models/User.php';

class ManagerUserController extends Controller
{
    private User $users;
    private int $tenantId = 0;

    public function __construct()
    {
        parent::__construct();
        $this->tenantId = (int)($_SESSION['tenant_id'] ?? 0);
        $this->requireRole(['manager']);
        $this->users = new User($this->tenantId ?: 0);
    }

    // GET /manager/users -> list all users but show role; managers primarily manage clients
    public function index(): void
    {
        $tenant = $this->requireTenant();
        $list = $this->users->getAll() ?: [];
        $this->render(__DIR__ . '/../../views/manager/ManagerUsers.php', [
            'users' => $list,
            'tenant_id' => $tenant,
            'layout' => __DIR__ . '/../../views/layouts/app.php',
            'title' => 'EcoMotion Manager — Users',
            'scripts' => ['/js/manager-users.js'],
        ]);
    }

    // Show form (reuses index)
    public function createForm(): void
    {
        $this->index();
    }

    // POST /manager/users -> create a user with role client or manager
    public function store(): void
    {
        $tenant = $this->requireTenant();
        $input = !empty($_POST) ? $_POST : $this->parseJsonBody();
        $name = trim((string)($input['name'] ?? ''));
        $email = trim((string)($input['email'] ?? ''));
        $password = (string)($input['password'] ?? '');
        $phone = trim((string)($input['phone'] ?? ''));
        $access = $input['accessibility_flags'] ?? null;
        $role = strtolower(trim((string)($input['role'] ?? 'client')));

        $errors = [];
        if ($name === '' || strlen($name) < 2) $errors[] = 'Name must be at least 2 chars';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email';
        if (strlen($password) < 6) $errors[] = 'Password min length 6';
        if (!in_array($role, ['client', 'manager'], true)) $errors[] = 'Invalid role (allowed: client, manager)';

        if ($errors) {
            if (!empty($_POST)) {
                $_SESSION['flash_errors'] = $errors;
                header('Location: /manager/users');
                exit;
            }
            $this->json(['errors' => $errors], 422);
            return;
        }

        try {
            $id = $this->users->createUserWithRole($name, $email, $password, $role);
            if ($id === false) throw new RuntimeException('Insert failed');
            // Persist optional details if provided
            $extra = [];
            if ($phone !== '') $extra['phone'] = $phone;
            if ($access !== null && $access !== '') $extra['accessibility_flags'] = $access;
            if ($extra) {
                $this->users->updateDetails((int)$id, $extra);
            }
        } catch (Throwable $e) {
            if (!empty($_POST)) {
                $_SESSION['flash_errors'] = [$e->getMessage()];
                header('Location: /manager/users');
                exit;
            }
            $this->json(['error' => $e->getMessage()], ($e instanceof InvalidArgumentException ? 400 : 500));
            return;
        }

        if (!empty($_POST)) {
            header('Location: /manager/users');
            exit;
        }
        $this->json(['created_id' => $id, 'tenant_id' => $tenant]);
    }

    // GET /manager/users/{id}
    public function show(int $id): void
    {
        $this->requireTenant();
        $row = $this->users->getById($id);
        if (!$row) {
            http_response_code(404);
            echo 'User not found';
            return;
        }
        $this->json($row);
    }

    // POST /manager/users/{id}/update -> managers can update basic fields and role (client/manager) for users in their tenant
    public function update(int $id): void
    {
        $this->requireTenant();
        $target = $this->users->getById($id);
        if (!$target) {
            $_SESSION['flash_errors'] = ['User not found'];
            header('Location: /manager/users');
            exit;
        }
        if (($target['role'] ?? '') === 'tenant_admin') {
            $_SESSION['flash_errors'] = ['Managers cannot modify tenant_admin users'];
            header('Location: /manager/users');
            exit;
        }

        $input = !empty($_POST) ? $_POST : $this->parseJsonBody();
        $data = [
            'name' => trim((string)($input['name'] ?? '')),
            'email' => trim((string)($input['email'] ?? '')),
            'phone' => trim((string)($input['phone'] ?? '')),
            'accessibility_flags' => $input['accessibility_flags'] ?? null,
        ];
        $role = isset($input['role']) ? strtolower(trim((string)$input['role'])) : null;

        try {
            // Update basic details
            $this->users->updateDetails($id, $data);
            // Update role if provided and allowed (only client/manager)
            if ($role !== null && $role !== '' && in_array($role, ['client', 'manager'], true)) {
                // Avoid no-op
                if (($target['role'] ?? null) !== $role) {
                    $this->users->updateRole($id, $role);
                }
            } elseif ($role !== null && $role !== '') {
                throw new InvalidArgumentException('Invalid role (allowed: client, manager)');
            }
            if (!empty($_POST)) {
                $_SESSION['flash_success'] = true;
                header('Location: /manager/users');
                exit;
            }
            $this->json(['updated' => true]);
        } catch (Throwable $e) {
            if (!empty($_POST)) {
                $_SESSION['flash_errors'] = [$e->getMessage()];
                header('Location: /manager/users');
                exit;
            }
            $this->json(['error' => $e->getMessage()], ($e instanceof InvalidArgumentException ? 422 : 500));
        }
    }

    // POST /manager/users/{id}/delete -> managers can only delete client users
    public function delete(int $id): void
    {
        $this->requireTenant();
        $target = $this->users->getById($id);
        if (!$target) {
            $_SESSION['flash_errors'] = ['User not found'];
            header('Location: /manager/users');
            exit;
        }
        if (($target['role'] ?? '') !== 'client') {
            $_SESSION['flash_errors'] = ['Managers can only delete client users'];
            header('Location: /manager/users');
            exit;
        }
        $ok = $this->users->delete($id);
        if (!empty($_POST)) {
            $_SESSION['flash_success'] = $ok ? true : false;
            if (!$ok) $_SESSION['flash_errors'] = ['Delete failed'];
            header('Location: /manager/users');
            exit;
        }
        $this->json(['deleted' => (bool)$ok]);
    }
}
