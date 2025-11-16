<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../services/ManagerUserService.php';

class ManagerUserController extends Controller
{
    private ?User $users = null;

    public function __construct()
    {
        parent::__construct();
        $this->requireRole(['manager','tenant_admin']);
    }

    // GET /manager/users -> list all users (single responsibility: fetch & render list)
    public function index(): void
    {
        $tenant = $this->resolveTenant();
        $model = $this->getUserModel($tenant);
        $list = $model->getAll() ?: [];
        $this->render(__DIR__ . '/../../views/manager/ManagerUsers.php', [
            'users' => $list,
            'tenant_id' => $tenant,
            'layout' => __DIR__ . '/../../views/layouts/app.php',
            'title' => 'EcoMotion Manager — Users',
            'scripts' => ['/js/manager-users.js'],
        ]);
    }

    // GET /manager/users/create -> show creation form only (single responsibility)
    public function createForm(): void
    {
        $tenant = $this->resolveTenant();
        $this->render(__DIR__ . '/../../views/manager/ManagerUserCreate.php', [
            'tenant_id' => $tenant,
            'layout' => __DIR__ . '/../../views/layouts/app.php',
            'title' => 'Create User — EcoMotion Manager',
        ]);
    }

    // POST /manager/users -> create user (single responsibility: orchestrate create)
    public function store(): void
    {
        $tenant = $this->resolveTenant();
        $model = $this->getUserModel($tenant);
        $input = $this->collectInput();
        $svc = new ManagerUserService($model);
        $errors = $svc->validateCreate($input);
        if ($errors) {
            $this->handleFormErrors($errors, '/manager/users');
            return;
        }
        try {
            $id = $svc->create($input);
        } catch (Throwable $e) {
            $this->handleFormErrors([$e->getMessage()], '/manager/users', ($e instanceof InvalidArgumentException ? 400 : 500));
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
        $tenant = $this->resolveTenant();
        $model = $this->getUserModel($tenant);
        $row = $model->getById($id);
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
        $tenant = $this->resolveTenant();
        $model = $this->getUserModel($tenant);
        $svc = new ManagerUserService($model);
        $target = $model->getById($id);
        if (!$target) {
            $this->handleFormErrors(['User not found'], '/manager/users', 404);
            return;
        }
        if (($target['role'] ?? '') === 'tenant_admin') {
            $this->handleFormErrors(['Managers cannot modify tenant_admin users'], '/manager/users', 403);
            return;
        }
        $input = $this->collectInput();
        $input['phone'] = $input['phone'] ?? null;
        $errors = $svc->validateUpdate($input, $target);
        if ($errors) {
            $this->handleFormErrors($errors, '/manager/users');
            return;
        }
        try {
            $svc->update($id, $input, $target);
        } catch (Throwable $e) {
            $this->handleFormErrors([$e->getMessage()], '/manager/users', ($e instanceof InvalidArgumentException ? 422 : 500));
            return;
        }
        if (!empty($_POST)) {
            $_SESSION['flash_success'] = true;
            header('Location: /manager/users');
            exit;
        }
        $this->json(['updated' => true]);
    }

    // POST /manager/users/{id}/delete -> managers can only delete client users
    public function delete(int $id): void
    {
        $tenant = $this->resolveTenant();
        $model = $this->getUserModel($tenant);
        $svc = new ManagerUserService($model);
        $target = $model->getById($id);
        if (!$target) {
            $this->handleFormErrors(['User not found'], '/manager/users', 404);
            return;
        }
        $errors = $svc->validateDelete($target);
        if ($errors) {
            $this->handleFormErrors($errors, '/manager/users', 403);
            return;
        }
        $ok = $svc->delete($id);
        if (!empty($_POST)) {
            $_SESSION['flash_success'] = $ok ? true : false;
            if (!$ok) $_SESSION['flash_errors'] = ['Delete failed'];
            header('Location: /manager/users');
            exit;
        }
        $this->json(['deleted' => (bool)$ok]);
    }

    // ---------------- Private helpers (single-purpose) ----------------

    private function resolveTenant(): int
    {
        $tenant = method_exists('TenantContext', 'tenantId') ? (int)(TenantContext::tenantId() ?? 0) : 0;
        return $tenant > 0 ? $tenant : $this->requireTenant();
    }

    private function getUserModel(int $tenant): User
    {
        if ($this->users instanceof User) return $this->users;
        $this->users = new User($tenant);
        return $this->users;
    }

    private function collectInput(): array
    {
        $input = !empty($_POST) ? $_POST : $this->parseJsonBody();
        return [
            'name' => trim((string)($input['name'] ?? '')),
            'email' => trim((string)($input['email'] ?? '')),
            'password' => (string)($input['password'] ?? ''),
            'phone' => trim((string)($input['phone'] ?? '')),
            'accessibility_flags' => $input['accessibility_flags'] ?? null,
            'role' => strtolower(trim((string)($input['role'] ?? 'client'))),
        ];
    }

    private function handleFormErrors(array $errors, string $redirect, int $jsonStatus = 422): void
    {
        if (!empty($_POST)) {
            $_SESSION['flash_errors'] = $errors;
            header('Location: ' . $redirect);
            exit;
        }
        $this->json(['errors' => $errors], $jsonStatus);
    }
}
