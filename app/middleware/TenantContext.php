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

            $subdomain = self::extractSubdomain();
            
            if ($subdomain === null || $subdomain === '') {
                http_response_code(400);
                echo json_encode(['error' => 'Tenant not specified']);
                return false;
            }

            // Look up tenant
            $tenantModel = new Tenant();
            $tenant = $tenantModel->findBySubdomain($subdomain);

            if ($tenant === null) {
                http_response_code(404);
                echo json_encode(['error' => 'Tenant not found']);
                return false;
            }

            if (!$tenant['is_active']) {
                http_response_code(403);
                echo json_encode(['error' => 'Tenant is inactive']);
                return false;
            }

            // Store tenant context
            self::$tenant = $tenant;
            return true;
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
