# User Model

This document explains how the User model works, how to use it in controllers, and the security mechanisms that protect user data. It also highlights reusable patterns you can apply to other components (e.g., Vehicles, Bookings, etc.).

- Source: `app/models/User.php`
- Base class: `app/core/Model.php` (generic CRUD + logging)
- Table: `users` (tenant-scoped table for multi-tenancy)

## Why the User Model is Important

The User model is a core component of the application, responsible for managing user data and ensuring secure interactions with the database. It provides methods for creating, updating, retrieving, and deleting user records while enforcing validation and security best practices.

## Schema (from `config/init.sql`)

```
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('client', 'manager', 'admin') DEFAULT 'client',
  is_active BOOLEAN DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);
```

## Public API

- `__construct(int $tenantId)`
  - Initializes the model with the tenant ID for scoping queries.

- `findById(int $id): array|null`
  - Fetches a user by their ID. Returns `null` if not found.

- `findByEmail(string $email): array|null`
  - Fetches a user by their email. Returns `null` if not found.

- `listUsers(array $filters = [], int $limit = 50, int $offset = 0): array`
  - Optional filters: `is_active` (bool), `role` (`client|manager|admin`).
  - Paginates with `limit` (1..200) and `offset`.

- `createUser(array $data): array|false`
  - Input: `name`, `email`, `password`, `role` (optional, defaults to `client`).
  - Hashes the password before storing it.
  - Returns the created user data or `false` on failure.

- `updateUser(int $id, array $data): bool`
  - Updates user fields like `name`, `email`, `role`, and `is_active`.
  - Validates email uniqueness and other constraints.

- `deleteUser(int $id): bool`
  - Performs a logical delete by setting `is_active` to `0`.

## Security Mechanisms

1. **Password Hashing**
   - Passwords are hashed using `password_hash()` before being stored in the database.
   - Verification uses `password_verify()` to ensure secure authentication.

2. **Email Validation**
   - Ensures that email addresses are unique and properly formatted.

3. **Role-Based Access Control (RBAC)**
   - User roles (`client`, `manager`, `admin`) are enforced at the controller level to restrict access to sensitive operations.

4. **Tenant Isolation**
   - All queries are scoped by `tenant_id` to ensure that users cannot access data from other tenants.

5. **Prepared Statements**
   - All database interactions use prepared statements to prevent SQL injection.

## Usage Examples

### Create a New User (Controller Snippet)

```php
$userModel = new User($tenantId);
$result = $userModel->createUser([
  'name' => 'John Doe',
  'email' => 'john.doe@example.com',
  'password' => 'securepassword',
  'role' => 'client',
]);
if ($result === false) {
  // Handle validation failure or duplicate email
} else {
  // User created successfully
}
```

### Authenticate a User

```php
$userModel = new User($tenantId);
$user = $userModel->findByEmail($email);
if ($user && password_verify($password, $user['password_hash'])) {
  // Authentication successful
} else {
  // Invalid credentials
}
```

### Update a User

```php
$userModel = new User($tenantId);
$success = $userModel->updateUser($userId, [
  'name' => 'Jane Doe',
  'role' => 'manager',
]);
if (!$success) {
  // Handle update failure
}
```

### Soft Delete a User

```php
$userModel = new User($tenantId);
$success = $userModel->deleteUser($userId);
if (!$success) {
  // Handle delete failure
}
```

## Reusable Patterns for Other Components

- **Validation and Logging**
  - Reuse the base model’s logging (`storage/logs/model_errors.log`) for consistent error tracking.
  - Validate input data before passing it to the model.

- **Tenant Scoping**
  - Always pass the `tenant_id` to model constructors to ensure data isolation.

- **Role Enforcement**
  - Enforce RBAC at the controller level to prevent unauthorized access to sensitive operations.

## Testing Checklist

- Creating a user with an invalid email fails.
- Duplicate email is rejected by the database and handled gracefully.
- Password verification succeeds for the correct password and fails for an incorrect one.
- Role-based access control prevents unauthorized actions.
- Soft-deleted users are excluded from queries by default.

By following these patterns, you can ensure secure and reliable user management while maintaining strong tenant isolation and scalability.