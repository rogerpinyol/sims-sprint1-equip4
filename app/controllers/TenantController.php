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
        // For API callers return JSON; for internal use, Router ignores return values, so echo JSON directly
        $this->json([
            'data' => $data,
            'pagination' => [
                'limit' => $limit,
                'offset' => $offset,
            ],
        ]);
        return [];
    }

    public function show(int $id): array
    {
        $this->requireSuperAdmin();

        $tenant = $this->tenants->findById($id);
        if ($tenant === null) {
            throw new HttpException(404, 'Tenant not found');
        }

        $this->json(['data' => $tenant], 200);
        return [];
    }

    public function store(): void
    {
        $this->requireSuperAdmin();
        $input = !empty($_POST) ? $_POST : $this->parseJsonBody();
        $clean = $this->filterCreatePayload($input);
        if ($clean === null) {
            if (!empty($_POST)) {
                $_SESSION['tenant_create_feedback'] = [
                    'success' => false,
                    'message' => 'Invalid payload',
                ];
                header('Location: /admin/tenants');
                exit;
            }
            $this->json(['error' => 'Invalid payload'], 422);
            return;
        }

        $result = $this->tenants->createTenant($clean);
        if ($result === false) {
            if (!empty($_POST)) {
                $_SESSION['tenant_create_feedback'] = [
                    'success' => false,
                    'message' => 'Unable to create tenant',
                ];
                header('Location: /admin/tenants');
                exit;
            }
            $this->json(['error' => 'Unable to create tenant'], 400);
            return;
        }

        $tenant = $this->tenants->findById($result['id']);
        if (!empty($_POST)) {
            $_SESSION['tenant_create_feedback'] = [
                'success' => true,
                'message' => 'Tenant created successfully',
                'api_key' => $result['api_key'] ?? null,
            ];
            header('Location: /admin/tenants');
            exit;
        }
        $this->json([
            'message' => 'Tenant created',
            'data' => $tenant,
            'api_key' => $result['api_key'],
        ], 201);
        return;
    }

    public function update(int $id): void
    {
        $this->requireSuperAdmin();
        if ($id <= 0) {
            if (!empty($_POST)) {
                $_SESSION['tenant_create_feedback'] = [
                    'success' => false,
                    'message' => 'Invalid tenant id',
                ];
                header('Location: /admin/tenants');
                exit;
            }
            $this->json(['error' => 'Invalid tenant id'], 400);
            return;
        }

        $input = !empty($_POST) ? $_POST : $this->parseJsonBody();
        $data = $this->filterUpdatePayload($input);
        if (empty($data)) {
            if (!empty($_POST)) {
                $_SESSION['tenant_create_feedback'] = [
                    'success' => false,
                    'message' => 'No valid fields to update',
                ];
                header('Location: /admin/tenants/' . urlencode((string)$id) . '/edit');
                exit;
            }
            $this->json(['error' => 'No valid fields to update'], 422);
            return;
        }

        if (!$this->tenants->updateTenant($id, $data)) {
            if (!empty($_POST)) {
                $_SESSION['tenant_create_feedback'] = [
                    'success' => false,
                    'message' => 'Update failed',
                ];
                header('Location: /admin/tenants/' . urlencode((string)$id) . '/edit');
                exit;
            }
            $this->json(['error' => 'Update failed'], 400);
            return;
        }

        $tenant = $this->tenants->findById($id);
        if ($tenant === null) {
            if (!empty($_POST)) {
                $_SESSION['tenant_create_feedback'] = [
                    'success' => false,
                    'message' => 'Tenant not found after update',
                ];
                header('Location: /admin/tenants');
                exit;
            }
            $this->json(['error' => 'Tenant not found after update'], 404);
            return;
        }
        if (!empty($_POST)) {
            $_SESSION['tenant_create_feedback'] = [
                'success' => true,
                'message' => 'Tenant updated',
            ];
            header('Location: /admin/tenants/' . urlencode((string)$id) . '/view');
            exit;
        }
        $this->json([
            'message' => 'Tenant updated',
            'data' => $tenant,
        ], 200);
        return;
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
        if (!empty($_POST)) {
            $_SESSION['tenant_create_feedback'] = [
                'success' => true,
                'message' => 'Tenant deactivated',
            ];
            header('Location: /admin/tenants');
            exit;
        }
        $this->json(['message' => 'Tenant deactivated'], 200);
        return [];
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
        if (!empty($_POST)) {
            $_SESSION['tenant_create_feedback'] = [
                'success' => true,
                'message' => 'API key rotated',
                'api_key' => $result['api_key'] ?? null,
            ];
            header('Location: /admin/tenants');
            exit;
        }
        $this->json([
            'message' => 'API key rotated',
            'api_key' => $result['api_key'],
        ], 200);
        return [];
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
        if (!empty($_POST)) {
            $_SESSION['tenant_create_feedback'] = [
                'success' => true,
                'message' => 'Tenant activated',
            ];
            header('Location: /admin/tenants');
            exit;
        }
        $this->json(['message' => 'Tenant activated'], 200);
        return [];
    }

    public function verifyApiKey(string $subdomain, string $apiKey): array
    {
        $subdomain = strtolower(trim($subdomain));
        $apiKey = trim($apiKey);

        if ($subdomain === '' || $apiKey === '') {
            throw new HttpException(422, 'Subdomain and API key are required');
        }

        $isValid = $this->tenants->verifyApiKey($subdomain, $apiKey);

        $this->json([
            'data' => [
                'subdomain' => $subdomain,
                'valid' => $isValid,
            ],
        ], $isValid ? 200 : 401);
        return [];
    }

    // Public endpoint-friendly variant that reads POST/JSON body instead of URL params
    // POST /api/verify-key { subdomain: string, api_key: string }
    public function verify(): void
    {
        $input = !empty($_POST) ? $_POST : $this->parseJsonBody();
        $subdomain = strtolower(trim((string)($input['subdomain'] ?? '')));
        $apiKey = (string)($input['api_key'] ?? '');
        if ($subdomain === '' || $apiKey === '') {
            $this->json(['error' => 'subdomain and api_key are required'], 422);
            return;
        }
        $isValid = $this->tenants->verifyApiKey($subdomain, $apiKey);
        $this->json([
            'data' => [
                'subdomain' => $subdomain,
                'valid' => $isValid,
            ],
        ], $isValid ? 200 : 401);
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
