# Unit and Integration Testing in SIMS SaaS

This document explains how to run and extend unit/integration tests for the PHP backend. It now includes examples for the User domain (models + services) in addition to the Tenant examples.

## Test Environment Setup

- **Test configuration is stored in `.env.test`** (not `.env`).
- The test database (e.g., `ecomotiondb_test`) is separate from your development and production databases.
- The test script loads `.env.test` for credentials and DB host.
- Tests are run against a real MariaDB instance (usually via Docker Compose).

### Example `.env.test`
```
MARIADB_USER=root
MARIADB_PASSWORD=password
MARIADB_DATABASE=ecomotiondb_test
MARIADB_HOST=127.0.0.1
MARIADB_PORT=3306
```
> Use a dedicated MariaDB account for automated tests. Root is acceptable in local/dev environments if the host is trusted.

## How Unit/Integration Tests Work

- Test scripts are in the `tests/` directory.
- Each test script loads `.env.test` for DB credentials.
- The script creates the test database if it doesn't exist, loads the schema from `config/init.sql`, and truncates all tables before running tests.
- Tests use dependency injection to pass a PDO connection to models, ensuring isolation from production code.
- Assertions are made with simple helper functions (e.g., `assert_true`, `assert_equals`).

## Example: Tenant Model Integration Test

File: `tests/TenantModelMySqlTest.php`

**What it does:**
- Connects to the MariaDB test database using credentials from `.env.test`.
- Loads the schema from `config/init.sql`.
- Truncates all tables for a clean slate.
- Runs a series of assertions to verify the Tenant model's behavior:
  - Creating a tenant and verifying the API key is hashed
  - Finding tenants by ID and subdomain
  - Verifying and rotating API keys
  - Deactivating tenants
  - Listing tenants with filters
  - Rejecting invalid subdomains

**How to run:**
```bash
# Make sure your MariaDB container is running and .env.test is configured
php tests/TenantModelMySqlTest.php
```

**Sample output:**
```
TenantModelMySqlTest: OK
```

## Example: Tenant Controller Integration Test

File: `tests/TenantControllerTest.php`

**What it validates:**
- Super-admin RBAC is required for create/update/deactivate/rotate operations.
- Tenant creation returns a one-time plaintext API key.
- Listing, showing, and updating tenants returns sanitized data.
- API key rotation invalidates the previous key.
- Deactivation prevents further API access.
- Unauthorized roles (e.g., `manager`) receive a 403 `HttpException`.
- Missing tenants return 404 errors.

**How to run:**
```bash
php tests/TenantControllerTest.php
```

**Sample output:**
```
TenantControllerTest: OK
```

## Best Practices
- **Never use your development or production database for tests.** Always use a dedicated test DB.
- **Keep `.env.test` out of version control** if it contains sensitive credentials.
- **Reset the test DB before each run** to ensure tests are repeatable and isolated.
- **Inject PDO connections** for testability and to avoid global state.
- **Add new tests** in the `tests/` directory, following the pattern in `TenantModelMySqlTest.php` and `TenantControllerTest.php`.

## User Domain Tests (PHPUnit)

File: `tests/UserDomainTest.php`

**Cubre:**
- Registro con creación automática de tenant (`registerWithTenant`).
- Autenticación correcta e incorrecta (`authenticate`).
- Validaciones de `updateDetails` (teléfono y email).
- Rechazo de roles inválidos en `updateRole`.
- Registro de `tenant_admin` vía `ClientAuthService` y verificación de rol.
- Verificación de rol para managers (`ManagerAuthService::ensureManagerRole`).
- Creación y actualización de usuario con `ManagerUserService` (incluye cambio de rol).

**Ejecutar:**
```powershell
vendor\bin\phpunit --filter UserDomainTest
```

## Comandos Útiles (PowerShell)

```powershell
# Instalar dependencias
composer install

# Ejecutar todos los tests
vendor\bin\phpunit

# Ejecutar un test específico
vendor\bin\phpunit --filter UserDomainTest

# Mostrar cobertura en texto
vendor\bin\phpunit --coverage-text
```

## CI/CD con GitHub Actions

Workflow: `.github/workflows/ci.yml`

**Resumen:**
- Arranca servicio MariaDB.
- Instala PHP 8.1 y dependencias Composer.
- Ejecuta PHPUnit con cobertura.

**Extender:**
- Agregar matriz de versiones PHP (`strategy.matrix.php: [8.1, 8.2]`).
- Subir reporte cobertura a Codecov (añadir paso con `codecov/codecov-action`).

---
Con esto la base de tests de usuarios queda integrada al pipeline y es fácil de extender.

## Extending
- To add tests for other models, create a new test script in `tests/`, inject a test PDO, and follow the same setup/teardown pattern.
- For more advanced testing, consider using PHPUnit and a test bootstrap that loads `.env.test` automatically.

---

This approach ensures your tests are safe, repeatable, and always run against the correct environment.
