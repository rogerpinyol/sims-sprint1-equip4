<?php

// Tenant model: manages SaaS tenants (root entity, not tenant-scoped)
// Security notes:
// - This model intentionally bypasses the base Model's tenant_id scoping since the
//   tenants table has no tenant_id column (it is the root of multi-tenancy).
// - Controllers MUST enforce RBAC (e.g., super-admin only) before calling mutating methods.

require_once __DIR__ . '/../core/Model.php';

class Tenant extends Model
{
    // Allowed values and validation rules
    private const PLAN_TYPES = ['standard', 'premium', 'enterprise'];
    private const SUBDOMAIN_REGEX = '/^(?!-)[a-z0-9-]{1,63}(?<!-)$/';
    private const RESERVED_SUBDOMAINS = ['www', 'admin', 'api', 'static', 'assets'];

    public function __construct(?PDO $pdo = null)
    {
        // Pass table name and a neutral tenantId (0) to parent; allow PDO injection for tests.
        parent::__construct('tenants', 0, $pdo);
    }

    // -----------------------------
    // Read operations
    // -----------------------------

    public function findById(int $id): array|null
    {
        $sql = 'SELECT id, name, subdomain, plan_type, created_at, is_active FROM tenants WHERE id = :id';
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row !== false ? $row : null;
        } catch (Throwable $e) {
            $this->logError($e);
            return null;
        }
    }

    public function findBySubdomain(string $subdomain): array|null
    {
        if (!$this->isValidSubdomain($subdomain)) return null;
        $sql = 'SELECT id, name, subdomain, plan_type, created_at, is_active FROM tenants WHERE subdomain = :subdomain';
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['subdomain' => $subdomain]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row !== false ? $row : null;
        } catch (Throwable $e) {
            $this->logError($e);
            return null;
        }
    }

    public function listTenants(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $limit = max(1, min(200, $limit));
        $offset = max(0, $offset);

        $where = [];
        $params = [];
        if (isset($filters['is_active'])) {
            $where[] = 'is_active = :is_active';
            $params['is_active'] = (int)!!$filters['is_active'];
        }
        if (isset($filters['plan_type']) && in_array($filters['plan_type'], self::PLAN_TYPES, true)) {
            $where[] = 'plan_type = :plan_type';
            $params['plan_type'] = $filters['plan_type'];
        }

        $sql = 'SELECT id, name, subdomain, plan_type, created_at, is_active FROM tenants';
        if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
        $sql .= ' ORDER BY created_at DESC LIMIT :limit OFFSET :offset';

        try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($params as $k => $v) {
                $stmt->bindValue(':' . $k, $v);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            $this->logError($e);
            return [];
        }
    }

    // -----------------------------
    // Create / Update operations
    // -----------------------------

    // Creates tenant and returns [id, api_key_plaintext]. Stores only a secure hash in DB.
    public function createTenant(array $data): array|false
    {
        $name = trim((string)($data['name'] ?? ''));
        $subdomain = strtolower(trim((string)($data['subdomain'] ?? '')));
        $plan = strtolower(trim((string)($data['plan_type'] ?? 'standard')));

        if ($name === '' || !$this->isValidSubdomain($subdomain) || !in_array($plan, self::PLAN_TYPES, true)) {
            return false;
        }

        // Generate API key (present to caller once) and store a hash only
        $apiKeyPlain = $this->generateApiKey();
        $apiKeyHash = password_hash($apiKeyPlain, PASSWORD_DEFAULT);

        $sql = 'INSERT INTO tenants (name, subdomain, plan_type, api_key, is_active) VALUES (:name, :subdomain, :plan_type, :api_key, 1)';

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'name' => $name,
                'subdomain' => $subdomain,
                'plan_type' => $plan,
                'api_key' => $apiKeyHash,
            ]);
            $id = (int)$this->pdo->lastInsertId();
            return ['id' => $id, 'api_key' => $apiKeyPlain];
        } catch (PDOException $e) {
            // Unique violations (subdomain/api_key) are possible
            $this->logError($e);
            return false;
        } catch (Throwable $e) {
            $this->logError($e);
            return false;
        }
    }

    public function updateTenant(int $id, array $data): bool
    {
        $allowed = ['name', 'subdomain', 'plan_type', 'is_active'];
        $sets = [];
        $params = ['id' => $id];

        if (isset($data['name'])) {
            $name = trim((string)$data['name']);
            if ($name === '') return false;
            $sets[] = 'name = :name';
            $params['name'] = $name;
        }
        if (isset($data['subdomain'])) {
            $sd = strtolower(trim((string)$data['subdomain']));
            if (!$this->isValidSubdomain($sd)) return false;
            $sets[] = 'subdomain = :subdomain';
            $params['subdomain'] = $sd;
        }
        if (isset($data['plan_type'])) {
            $plan = strtolower(trim((string)$data['plan_type']));
            if (!in_array($plan, self::PLAN_TYPES, true)) return false;
            $sets[] = 'plan_type = :plan_type';
            $params['plan_type'] = $plan;
        }
        if (array_key_exists('is_active', $data)) {
            $sets[] = 'is_active = :is_active';
            $params['is_active'] = (int)!!$data['is_active'];
        }

        if (empty($sets)) return false;

        $sql = 'UPDATE tenants SET ' . implode(', ', $sets) . ' WHERE id = :id';
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (Throwable $e) {
            $this->logError($e);
            return false;
        }
    }

    public function deactivateTenant(int $id): bool
    {
        return $this->updateTenant($id, ['is_active' => 0]);
    }

    // Rotates and returns a new plaintext API key while storing only the hash
    public function rotateApiKey(int $id): array|false
    {
        $newKey = $this->generateApiKey();
        $hash = password_hash($newKey, PASSWORD_DEFAULT);
        $sql = 'UPDATE tenants SET api_key = :api_key WHERE id = :id';
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['api_key' => $hash, 'id' => $id]);
            return ['id' => $id, 'api_key' => $newKey];
        } catch (Throwable $e) {
            $this->logError($e);
            return false;
        }
    }

    // Verifies an API key for a tenant identified by subdomain
    public function verifyApiKey(string $subdomain, string $presentedKey): bool
    {
        if (!$this->isValidSubdomain($subdomain) || $presentedKey === '') return false;
        try {
            $stmt = $this->pdo->prepare('SELECT api_key, is_active FROM tenants WHERE subdomain = :s LIMIT 1');
            $stmt->execute(['s' => $subdomain]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row || !(int)$row['is_active']) return false;
            return password_verify($presentedKey, $row['api_key']);
        } catch (Throwable $e) {
            $this->logError($e);
            return false;
        }
    }

    // -----------------------------
    // Overrides to avoid tenant_id scoping for tenants table
    // -----------------------------

    public function find(array $conditions = []): array|false
    {
        // Build safe WHERE
        $sql = 'SELECT id, name, subdomain, plan_type, created_at, is_active FROM tenants';
        $params = [];
        $clauses = [];
        $i = 0;
        foreach ($conditions as $col => $val) {
            if (!preg_match('/^[a-zA-Z0-9_]+$/', (string)$col)) continue;
            $i++;
            $ph = 'p' . $i;
            $clauses[] = "`$col` = :$ph";
            $params[$ph] = $val;
        }
        if ($clauses) $sql .= ' WHERE ' . implode(' AND ', $clauses);

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            $this->logError($e);
            return false;
        }
    }

    // Prevent accidental use of tenant-scoped base methods
    public function insert(array $data): int|false { trigger_error('Use createTenant() for Tenants', E_USER_WARNING); return false; }
    public function update(int $id, array $data): bool { trigger_error('Use updateTenant() for Tenants', E_USER_WARNING); return false; }
    public function delete(int $id): bool { trigger_error('Deactivating tenants is recommended; use deactivateTenant()', E_USER_WARNING); return false; }

    // -----------------------------
    // Helpers
    // -----------------------------

    private function isValidSubdomain(string $subdomain): bool
    {
        if ($subdomain === '' || !preg_match(self::SUBDOMAIN_REGEX, $subdomain)) return false;
        if (in_array($subdomain, self::RESERVED_SUBDOMAINS, true)) return false;
        return true;
    }

    private function generateApiKey(): string
    {
        // 32 bytes -> 64 hex chars; fits well and is URL-safe
        return bin2hex(random_bytes(32));
    }
}
