<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Tenant.php';

class TenantController extends Controller
{
    private Tenant $tenants;

    public function __construct(?PDO $pdo = null)
    {
        parent::__construct();
        $this->tenants = new Tenant($pdo);
    }

    public function index(array $queryParams = []): array
    {
        $this->requireSuperAdmin();

        $filters = [];
        // Only apply is_active filter when provided and not empty string
        if (array_key_exists('is_active', $queryParams) && $queryParams['is_active'] !== '') {
            $filters['is_active'] = $this->toBoolean($queryParams['is_active']);
        }
        // Only apply plan_type when provided and not empty
        if (array_key_exists('plan_type', $queryParams) && trim((string)$queryParams['plan_type']) !== '') {
            $filters['plan_type'] = strtolower(trim((string)$queryParams['plan_type']));
        }
        // Apply search filter (name or subdomain) when provided and not empty
        if (array_key_exists('search', $queryParams) && trim((string)$queryParams['search']) !== '') {
            $filters['search'] = trim((string)$queryParams['search']);
        }

        $limit = $this->clamp((int)($queryParams['limit'] ?? 50), 1, 200);
        $offset = max(0, (int)($queryParams['offset'] ?? 0));

        $data = $this->tenants->listTenants($filters, $limit, $offset);
        return $this->jsonResponse([
            'data' => $data,
            'pagination' => [
                'limit' => $limit,
                'offset' => $offset,
            ],
        ], 200, $this->jsonHeaders());
    }

    public function show(int $id): array
    {
        $this->requireSuperAdmin();

        $tenant = $this->tenants->findById($id);
        if ($tenant === null) {
            throw new HttpException(404, 'Tenant not found');
        }

        return $this->jsonResponse(['data' => $tenant], 200, $this->jsonHeaders());
    }

    public function store(array $payload): array
    {
        $this->requireSuperAdmin();
        $clean = $this->filterCreatePayload($payload);
        if ($clean === null) {
            throw new HttpException(422, 'Invalid payload');
        }

        $result = $this->tenants->createTenant($clean);
        if ($result === false) {
            throw new HttpException(400, 'Unable to create tenant');
        }

        $tenant = $this->tenants->findById($result['id']);

        return $this->jsonResponse([
            'message' => 'Tenant created',
            'data' => $tenant,
            'api_key' => $result['api_key'],
        ], 201, $this->jsonHeaders());
    }

    public function update(int $id, array $payload): array
    {
        $this->requireSuperAdmin();
        if ($id <= 0) {
            throw new HttpException(400, 'Invalid tenant id');
        }

        $data = $this->filterUpdatePayload($payload);
        if (empty($data)) {
            throw new HttpException(422, 'No valid fields to update');
        }

        if (!$this->tenants->updateTenant($id, $data)) {
            throw new HttpException(400, 'Update failed');
        }

        $tenant = $this->tenants->findById($id);
        if ($tenant === null) {
            throw new HttpException(404, 'Tenant not found after update');
        }

        return $this->jsonResponse([
            'message' => 'Tenant updated',
            'data' => $tenant,
        ], 200, $this->jsonHeaders());
    }

    public function deactivate(int $id): array
    {
        $this->requireSuperAdmin();
        if ($id <= 0) {
            throw new HttpException(400, 'Invalid tenant id');
        }

        if (!$this->tenants->deactivateTenant($id)) {
            throw new HttpException(400, 'Unable to deactivate tenant');
        }

        return $this->jsonResponse([
            'message' => 'Tenant deactivated',
        ], 200, $this->jsonHeaders());
    }

    public function rotateApiKey(int $id): array
    {
        $this->requireSuperAdmin();
        if ($id <= 0) {
            throw new HttpException(400, 'Invalid tenant id');
        }

        $result = $this->tenants->rotateApiKey($id);
        if ($result === false) {
            throw new HttpException(400, 'Unable to rotate API key');
        }

        return $this->jsonResponse([
            'message' => 'API key rotated',
            'api_key' => $result['api_key'],
        ], 200, $this->jsonHeaders());
    }

    public function activate(int $id): array
    {
        $this->requireSuperAdmin();
        if ($id <= 0) {
            throw new HttpException(400, 'Invalid tenant id');
        }

        if (!$this->tenants->updateTenant($id, ['is_active' => 1])) {
            throw new HttpException(400, 'Unable to activate tenant');
        }

        return $this->jsonResponse([
            'message' => 'Tenant activated',
        ], 200, $this->jsonHeaders());
    }

    public function verifyApiKey(string $subdomain, string $apiKey): array
    {
        $subdomain = strtolower(trim($subdomain));
        $apiKey = trim($apiKey);

        if ($subdomain === '' || $apiKey === '') {
            throw new HttpException(422, 'Subdomain and API key are required');
        }

        $isValid = $this->tenants->verifyApiKey($subdomain, $apiKey);

        return $this->jsonResponse([
            'data' => [
                'subdomain' => $subdomain,
                'valid' => $isValid,
            ],
        ], $isValid ? 200 : 401, $this->jsonHeaders());
    }

    private function filterCreatePayload(array $payload): ?array
    {
        $name = trim((string)($payload['name'] ?? ''));
        $subdomain = strtolower(trim((string)($payload['subdomain'] ?? '')));
        $plan = isset($payload['plan_type']) ? strtolower(trim((string)$payload['plan_type'])) : 'standard';

        if ($name === '' || $subdomain === '') {
            return null;
        }

        $data = [
            'name' => $name,
            'subdomain' => $subdomain,
            'plan_type' => $plan,
        ];

        return $data;
    }

    private function filterUpdatePayload(array $payload): array
    {
        $allowed = ['name', 'subdomain', 'plan_type', 'is_active'];
        $data = [];
        foreach ($allowed as $field) {
            if (!array_key_exists($field, $payload)) {
                continue;
            }

            $value = $payload[$field];
            switch ($field) {
                case 'name':
                    $value = trim((string)$value);
                    if ($value === '') continue 2;
                    $data['name'] = $value;
                    break;
                case 'subdomain':
                    $value = strtolower(trim((string)$value));
                    if ($value === '') continue 2;
                    $data['subdomain'] = $value;
                    break;
                case 'plan_type':
                    $value = strtolower(trim((string)$value));
                    if ($value === '') continue 2;
                    $data['plan_type'] = $value;
                    break;
                case 'is_active':
                    $data['is_active'] = $this->toBoolean($value);
                    break;
            }
        }

        return $data;
    }

    private function toBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_numeric($value)) {
            return ((int)$value) === 1;
        }
        $value = strtolower((string)$value);
        return in_array($value, ['1', 'true', 'yes', 'on'], true);
    }

    private function clamp(int $value, int $min, int $max): int
    {
        return max($min, min($max, $value));
    }

    private function jsonHeaders(): array
    {
        return ['Content-Type' => 'application/json'];
    }
}
