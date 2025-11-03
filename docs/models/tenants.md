# Tenant Model

This document explains how the Tenant model works, how to use it in controllers, and the security mechanisms that protect multi‑tenancy. It also highlights reusable patterns you can apply to other components (Users, Vehicles, Incidences, etc.).

- Source: `app/models/Tenant.php`
- Base class: `app/core/Model.php` (generic CRUD + logging)
- Table: `tenants` (root table in multi‑tenant design; no `tenant_id` column)

## Why tenants are special

Every other table is scoped by `tenant_id`. The `tenants` table is the root that defines each tenant and therefore is not scoped by `tenant_id`. For this reason, the Tenant model overrides the base model’s tenant‑filtered methods and provides safe, purpose‑built operations.

Controllers MUST enforce RBAC (e.g., super‑admin only) before calling any method that modifies tenants.

## Schema (from `config/init.sql`)

```
CREATE TABLE tenants (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  subdomain VARCHAR(100) UNIQUE,
  plan_type ENUM('standard', 'premium', 'enterprise') DEFAULT 'standard',
  api_key VARCHAR(255) UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  is_active BOOLEAN DEFAULT 1
);
```

## Public API

- `__construct()`
  - Wires DB connection through the base model and sets the table to `tenants`.

- `findById(int $id): array|null`
  - Fetch a single tenant by id. Returns `null` if not found.

- `findBySubdomain(string $subdomain): array|null`
  - Fetch by subdomain after validating it. Returns `null` if invalid or not found.

- `listTenants(array $filters = [], int $limit = 50, int $offset = 0): array`
  - Optional filters: `is_active` (bool), `plan_type` (`standard|premium|enterprise`).
  - Paginates with `limit` (1..200) and `offset`.

- `createTenant(array $data): array|false`
  - Input: `name` (required), `subdomain` (required), `plan_type` (optional, defaults to `standard`).
  - Generates a strong API key, stores only a secure hash in DB, and returns:
    - `{ id: int, api_key: string }` (plaintext key is shown only once).

- `updateTenant(int $id, array $data): bool`
  - Allowed fields: `name`, `subdomain`, `plan_type`, `is_active`.

- `deactivateTenant(int $id): bool`
  - Convenience to set `is_active = 0` (preferred over hard delete).

- `rotateApiKey(int $id): array|false`
  - Generates a new API key, stores its hash, and returns `{ id, api_key }` (plaintext key only once).

- `verifyApiKey(string $subdomain, string $presentedKey): bool`
  - Looks up an active tenant by subdomain and verifies the key using `password_verify()`.

> Important: For Tenants, don’t use the base `insert/update/delete` methods. They are overridden to warn and no‑op in this model to avoid accidental misuse.

## Security mechanisms

1. Root entity, not tenant‑scoped
   - The base model auto‑applies `tenant_id` to all queries, but tenants table has no such column. To avoid misuse, the Tenant model overrides the base CRUD to provide dedicated, safe methods.

2. API key hashing
   - API keys are generated with `random_bytes(32)` (64 hex chars) and stored as a hash using `password_hash()`.
   - The plaintext key is never stored; it’s returned only once at creation/rotation.
   - Verification uses `password_verify()` and constant‑time comparison under the hood.

3. Subdomain validation and reserved names
   - Subdomain pattern: `/^(?!-)[a-z0-9-]{1,63}(?<!-)$/`.
   - Reserved words are rejected (e.g., `www`, `admin`, `api`, `static`, `assets`).
   - Combined with a UNIQUE constraint on `subdomain` in the DB.

4. Prepared statements and whitelisting
   - All SQL uses prepared statements.
   - Any dynamic column names are whitelisted via regex (`^[a-zA-Z0-9_]+$`).

5. Soft‑delete over hard delete
   - Prefer `is_active = 0` to preserve referential integrity and auditability.

6. RBAC enforced by controllers
   - Controllers must check that the current user is a super‑admin before calling `createTenant`, `updateTenant`, `deactivateTenant`, or `rotateApiKey`.
   - Typical flow:
     - Resolve tenant context from subdomain/host for normal users.
     - Allow tenant mutations only to system admins (no tenant context) or dedicated management endpoints.

## Usage examples

Create a new tenant (controller snippet):

```php
$tenantModel = new Tenant();
$this->requireRole(['super_admin']); // controller-level RBAC
$result = $tenantModel->createTenant([
  'name' => 'City A',
  'subdomain' => 'city-a',
  'plan_type' => 'premium',
]);
if ($result === false) {
  // handle duplicate subdomain or validation failure
} else {
  // $result['api_key'] -> show once to the admin; store securely if needed on client side
}
```

Rotate API key:

```php
$tenantModel = new Tenant();
$this->requireRole(['super_admin']);
$new = $tenantModel->rotateApiKey($tenantId);
// present $new['api_key'] once to the operator
```

Verify API key (e.g., in an API middleware):

```php
$tenantModel = new Tenant();
if (!$tenantModel->verifyApiKey($subdomain, $incomingKey)) {
  http_response_code(401);
  exit('Invalid or inactive tenant');
}
```

List tenants with filters:

```php
$tenantModel = new Tenant();
$tenants = $tenantModel->listTenants(['is_active' => true, 'plan_type' => 'standard'], 50, 0);
```

## Reusable patterns for other components

- Most other models (Users, Vehicles, Incidences, Bookings, etc.) should extend the base `Model` and rely on its built‑in `tenant_id` scoping. Example outline:

```php
require_once __DIR__ . '/../core/Model.php';

class Vehicle extends Model {
  public function __construct(int $tenantId) {
    parent::__construct('vehicles', $tenantId);
  }

  public function findAvailable(): array|false {
    return $this->find(['status' => 'available']);
  }
}
```

- Controller rule of thumb:
  - Resolve `tenant_id` from session or subdomain early.
  - Pass that `tenant_id` to model constructors for all tenant‑scoped entities.
  - Never trust client‑provided `tenant_id` in request bodies.

- Validation & logging:
  - Reuse the base model’s logging (`storage/logs/model_errors.log`).
  - Whitelist columns when building dynamic filters.

## Testing checklist

- Creating a tenant with an invalid subdomain fails.
- Duplicate subdomain is rejected by DB (unique index) and handled gracefully.
- API key verification succeeds for the correct key and fails for a wrong key.
- Deactivating a tenant stops API key verification.
- RBAC prevents non‑super‑admins from mutating tenants.

By following this pattern, you get strong tenant isolation, safe API key handling, and reusable foundations for the rest of your MVC components.
