<?php
// admin.routes.php
// Expects $__routeContext to be defined (router, csrfCheck, ensureCsrfToken, renderError, requireSuperAdmin)
/** @var array $__routeContext */
$router = $__routeContext['router'];
$csrfCheck = $__routeContext['csrfCheck'] ?? null;
$ensureCsrfToken = $__routeContext['ensureCsrfToken'] ?? null;
$renderError = $__routeContext['renderError'] ?? null;
$requireSuperAdmin = $__routeContext['requireSuperAdmin'] ?? null;

// Ensure CSRF token exists for forms
if (is_callable($ensureCsrfToken)) $ensureCsrfToken();

// Require controllers used by these routes (no autoload guaranteed in web.php)
require_once __DIR__ . '/../app/controllers/VehicleController.php';

// Vehicles
$router->add('GET', '/vehicles', [\VehicleController::class, 'index']);
$router->add('GET', '/vehicle/create', [\VehicleController::class, 'create']);
$router->add('POST', '/vehicle/store', [\VehicleController::class, 'store'], is_callable($csrfCheck) ? [$csrfCheck] : []);
$router->add('GET', '/vehicle/edit', [\VehicleController::class, 'edit']);
$router->add('POST', '/vehicle/update', [\VehicleController::class, 'update'], is_callable($csrfCheck) ? [$csrfCheck] : []);
// Support both GET (legacy) and POST (safer) for delete; controller reads id from GET/POST
$router->add('GET', '/vehicle/delete', [\VehicleController::class, 'delete']);
$router->add('POST', '/vehicle/delete', [\VehicleController::class, 'delete'], is_callable($csrfCheck) ? [$csrfCheck] : []);

// Admin tenants route removed (TenantController not present)

// Additional admin routes could be added here
