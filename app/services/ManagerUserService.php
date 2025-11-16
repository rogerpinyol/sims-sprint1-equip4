<?php

require_once __DIR__ . '/../models/User.php';

class ManagerUserService
{
    private User $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    // Validate creation input
    public function validateCreate(array $input): array
    {
        $errors = [];
        if ($input['name'] === '' || strlen($input['name']) < 2) $errors[] = 'Name must be at least 2 chars';
        if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email';
        if (strlen($input['password']) < 6) $errors[] = 'Password min length 6';
        if (!in_array($input['role'], ['client', 'manager'], true)) $errors[] = 'Invalid role (allowed: client, manager)';
        return $errors;
    }

    // Create user and optional details
    public function create(array $input): int|false
    {
        $id = $this->model->createUserWithRole($input['name'], $input['email'], $input['password'], $input['role']);
        if ($id === false) throw new RuntimeException('Insert failed');
        $extra = [];
        if ($input['phone'] !== '') $extra['phone'] = $input['phone'];
        if ($input['accessibility_flags'] !== null && $input['accessibility_flags'] !== '') {
            $extra['accessibility_flags'] = $input['accessibility_flags'];
        }
        if ($extra) {
            $this->model->updateDetails((int)$id, $extra);
        }
        return (int)$id;
    }

    // Validate update input relative to target
    public function validateUpdate(array $input, array $target): array
    {
        $errors = [];
        if ($input['email'] !== '' && !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email';
        }
        if ($input['role'] !== '' && !in_array($input['role'], ['client', 'manager'], true)) {
            $errors[] = 'Invalid role (allowed: client, manager)';
        }
        return $errors;
    }

    // Perform update
    public function update(int $id, array $input, array $target): void
    {
        $details = [
            'name' => $input['name'],
            'email' => $input['email'],
            'phone' => $input['phone'],
            'accessibility_flags' => $input['accessibility_flags'],
        ];
        $this->model->updateDetails($id, $details);
        $newRole = $input['role'];
        if ($newRole !== '' && $newRole !== ($target['role'] ?? null)) {
            $this->model->updateRole($id, $newRole);
        }
    }

    // Validate delete permissions
    public function validateDelete(array $target): array
    {
        $errors = [];
        if (($target['role'] ?? '') === 'tenant_admin') {
            $errors[] = 'Managers cannot delete tenant_admin users';
        }
        return $errors;
    }

    public function delete(int $id): bool
    {
        return $this->model->delete($id);
    }
}
