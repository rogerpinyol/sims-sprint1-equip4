<?php

// CRUD users by admin

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/User.php';

class AdminUserController extends Controller
{
    private User $users;
    private int $tenantId = 0;

    public function __construct()
    {
        parent::__construct();
        $this->tenantId = (int)($_SESSION['tenant_id'] ?? 0);
        $this->requireRole(['admin', 'tenant_admin', 'super_admin']);
        $this->users = new User($this->tenantId ?: 0);
    }

    // GET /admin -> listado usuarios
    public function index(): void
    {
        $tenant = $this->requireTenant();
        $list = $this->users->getAll() ?: [];
        $this->render(__DIR__ . '/../views/admin/manageUsers.php', [
            'users' => $list,
            'tenant_id' => $tenant,
        ]);
    }

    // Show form
    public function createForm(): void
    {
        $this->index(); 
    }

    // Create user
    public function store(): void
    {
        $tenant = $this->requireTenant();
        $input = !empty($_POST) ? $_POST : $this->parseJsonBody();
        $name = trim((string)($input['name'] ?? ''));
        $email = trim((string)($input['email'] ?? ''));
        $password = (string)($input['password'] ?? '');
        $role = (string)($input['role'] ?? 'client');
        $phone = trim((string)($input['phone'] ?? ''));
        $access = $input['accessibility_flags'] ?? null;

        $errors = [];
        if ($name === '' || strlen($name) < 2) $errors[] = 'Name must be at least 2 chars';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email';
        if (strlen($password) < 6) $errors[] = 'Password min length 6';
        if ($role && !in_array($role, ['client','manager','tenant_admin','admin','super_admin'], true)) $errors[] = 'Invalid role';

        if ($errors) {
            if (!empty($_POST)) {
                $_SESSION['flash_errors'] = $errors;
                header('Location: /admin');
                exit;
            }
            $this->json(['errors' => $errors], 422);
            return;
        }

        try {
            if ($role === 'admin') $role = 'tenant_admin';
            $id = $this->users->createUserWithRole($name, $email, $password, $role);
            if ($id === false) throw new RuntimeException('Insert failed');
        } catch (Throwable $e) {
            if (!empty($_POST)) {
                $_SESSION['flash_errors'] = [$e->getMessage()];
                header('Location: /admin');
                exit;
            }
            $this->json(['error' => $e->getMessage()], ($e instanceof InvalidArgumentException ? 400 : 500));
            return;
        }

        if (!empty($_POST)) {
            header('Location: /admin');
            exit;
        }
        $this->json(['created_id' => $id, 'tenant_id' => $tenant]);
    }

    // GET /admin/users/{id}
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
}
