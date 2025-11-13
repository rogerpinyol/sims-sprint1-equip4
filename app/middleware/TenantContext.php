<?php
// Tenant context middleware: detects tenant from subdomain or header and injects into request
// Usage: apply to all tenant-scoped routes to ensure data isolation

require_once __DIR__ . '/../models/Tenant.php';

class TenantContext
{
    private static ?array $tenant = null;

    /**
     * Middleware callable that detects and sets tenant context.
     * Returns false (aborts) if tenant not found or inactive.
     * 
     * Detection order:
     * 1. X-Tenant-Subdomain header (for API/testing)
     * 2. Subdomain from HTTP_HOST (e.g., city-alpha.domain.com -> city-alpha)
     * 3. Query param ?tenant=subdomain (dev/testing only, disable in production)
     */
    public static function detect(): callable
    {
        return function() {
            // Check if already set (avoid re-detection)
            if (self::$tenant !== null) {
                return true;
            }

            // Ensure JSON content-type for any early error responses
            $ensureJsonHeader = function () {
                if (!headers_sent()) {
                    header('Content-Type: application/json; charset=utf-8');
                }
            };

            $tenantModel = new Tenant();

            // 0) Session-based tenant (commonly set after login)
            $sessionTid = isset($_SESSION['tenant_id']) ? (int)$_SESSION['tenant_id'] : 0;
            if ($sessionTid > 0) {
                $tenant = $tenantModel->findById($sessionTid);
                if ($tenant && (int)$tenant['is_active'] === 1) {
                    self::$tenant = $tenant;
                    return true;
                }
                // If session points to invalid tenant, drop it and continue detection
                unset($_SESSION['tenant_id']);
            }

            // 1) Dev/API override by explicit tenant_id
            $paramTid = isset($_GET['tenant_id']) ? (int)$_GET['tenant_id'] : 0;
            if ($paramTid > 0) {
                $tenant = $tenantModel->findById($paramTid);
                if ($tenant === null) {
                    $ensureJsonHeader();
                    http_response_code(404);
                    echo json_encode(['error' => 'Tenant not found']);
                    return false;
                }
                if (!(int)$tenant['is_active']) {
                    $ensureJsonHeader();
                    http_response_code(403);
                    echo json_encode(['error' => 'Tenant is inactive']);
                    return false;
                }
                $_SESSION['tenant_id'] = (int)$tenant['id'];
                self::$tenant = $tenant;
                return true;
            }

            // 2) Subdomain/header based resolution
            $subdomain = self::extractSubdomain();
            if ($subdomain !== null && $subdomain !== '') {
                $tenant = $tenantModel->findBySubdomain($subdomain);
                if ($tenant === null) {
                    $ensureJsonHeader();
                    http_response_code(404);
                    echo json_encode(['error' => 'Tenant not found']);
                    return false;
                }
                if (!(int)$tenant['is_active']) {
                    $ensureJsonHeader();
                    http_response_code(403);
                    echo json_encode(['error' => 'Tenant is inactive']);
                    return false;
                }
                $_SESSION['tenant_id'] = (int)$tenant['id'];
                self::$tenant = $tenant;
                return true;
            }

            // 3) Optional dev default (e.g., in local without subdomain)
            $defaultTid = (int)(getenv('DEFAULT_TENANT_ID') ?: 0);
            if ($defaultTid > 0) {
                $tenant = $tenantModel->findById($defaultTid);
                if ($tenant && (int)$tenant['is_active'] === 1) {
                    $_SESSION['tenant_id'] = (int)$tenant['id'];
                    self::$tenant = $tenant;
                    return true;
                }
            }

            // Nothing worked
            $ensureJsonHeader();
            http_response_code(400);
            echo json_encode(['error' => 'Tenant not specified']);
            return false;
        };
    }

    /**
     * Get current tenant context (must call detect() first).
     */
    public static function current(): ?array
    {
        return self::$tenant;
    }

    /**
     * Get current tenant ID (shorthand).
     */
    public static function tenantId(): ?int
    {
        return self::$tenant['id'] ?? null;
    }

    /**
     * Extract subdomain from request.
     * Priority: header > subdomain > query param (dev only).
     */
    private static function extractSubdomain(): ?string
    {
        // 1. Check X-Tenant-Subdomain header
        if (isset($_SERVER['HTTP_X_TENANT_SUBDOMAIN'])) {
            return strtolower(trim($_SERVER['HTTP_X_TENANT_SUBDOMAIN']));
        }

        // 2. Parse subdomain from host
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $parts = explode('.', $host);
        
        // If host is like city-alpha.localhost:8081 or city-alpha.domain.com
        if (count($parts) >= 2) {
            $subdomain = strtolower(trim($parts[0]));
            // Exclude common non-tenant prefixes
            if (!in_array($subdomain, ['www', 'api', 'admin', 'localhost', '127'], true)) {
                return $subdomain;
            }
        }

        // 3. Dev fallback: query param (remove in production)
        if (isset($_GET['tenant'])) {
            return strtolower(trim($_GET['tenant']));
        }

        return null;
    }

    /**
     * Reset tenant context (useful for testing).
     */
    public static function reset(): void
    {
        self::$tenant = null;
    }
}
