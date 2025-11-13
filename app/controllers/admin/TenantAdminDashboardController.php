<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../models/Tenant.php';

class TenantAdminDashboardController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireSuperAdmin(); // now mapped to tenant_admin role
    }

    public function index(): void
    {
        // Ensure CSRF token
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        }
        $query = $_GET ?? [];
        $filters = [];
        if (array_key_exists('is_active', $query) && $query['is_active'] !== '') {
            $filters['is_active'] = $this->toBoolean($query['is_active']);
        }
        if (array_key_exists('plan_type', $query) && trim((string)$query['plan_type']) !== '') {
            $filters['plan_type'] = strtolower(trim((string)$query['plan_type']));
        }
        if (array_key_exists('search', $query) && trim((string)$query['search']) !== '') {
            $filters['search'] = trim((string)$query['search']);
        }
        $limit = $this->clamp((int)($query['limit'] ?? 50), 1, 200);
        $offset = max(0, (int)($query['offset'] ?? 0));

        $tenantModel = new Tenant();
        $tenants = $tenantModel->listTenants($filters, $limit, $offset);

        // feedback flash
        $feedback = $_SESSION['tenant_create_feedback'] ?? null;
        if ($feedback) unset($_SESSION['tenant_create_feedback']);

        $this->render(__DIR__ . '/../../views/admin/tenants.php', [
            'tenants' => $tenants,
            'pagination' => ['limit' => $limit, 'offset' => $offset],
            'feedback' => $feedback,
        ]);
    }

    private function toBoolean(mixed $value): bool
    {
        if (is_bool($value)) return $value;
        if (is_numeric($value)) return ((int)$value) === 1;
        $value = strtolower((string)$value);
        return in_array($value, ['1','true','yes','on'], true);
    }

    private function clamp(int $value, int $min, int $max): int
    {
        return max($min, min($max, $value));
    }
}
