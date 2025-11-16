<?php

require_once __DIR__ . '/Router.php';
require_once __DIR__ . '/../app/middleware/TenantContext.php';

$router = new Router();

// Normalize manager base path
$MB = getenv('MANAGER_BASE') ?: '/ecomotion-manager';
if ($MB === '' || $MB === false) { $MB = '/ecomotion-manager'; }
if ($MB[0] !== '/') { $MB = '/' . $MB; }
if ($MB !== '/' && substr($MB, -1) === '/') { $MB = rtrim($MB, '/'); }

// Manager auth (legacy + pretty base)
$router->add('GET', '/manager/login', ['auth/ManagerAuthController','loginForm']);
$router->add('POST','/manager/login', ['auth/ManagerAuthController','login']);
$router->add('POST','/manager/logout',['auth/ManagerAuthController','logout']);
$router->add('GET', $MB . '/login',   ['auth/ManagerAuthController','loginForm']);
$router->add('POST',$MB . '/login',   ['auth/ManagerAuthController','login']);
$router->add('POST',$MB . '/logout',  ['auth/ManagerAuthController','logout']);

// Client auth
$router->add('GET',  '/auth/login',    ['auth/ClientAuthController','loginForm']);
$router->add('POST', '/auth/login',    ['auth/ClientAuthController','login']);
$router->add('POST', '/auth/logout',   ['auth/ClientAuthController','logout']);
$router->add('GET',  '/register',      ['auth/ClientAuthController','form']);
$router->add('POST', '/register',      ['auth/ClientAuthController','register']);

// Client profile & dashboard (tenant scoped middleware)
$tenantMw = [TenantContext::detect()];
$router->add('GET',  '/profile',             ['client/ClientController','profile'], $tenantMw);
$router->add('POST', '/profile',             ['client/ClientController','updateProfile'], $tenantMw);
$router->add('POST', '/profile/delete',      ['client/ClientController','deleteAccount'], $tenantMw);
$router->add('GET',  '/client',              ['client/ClientDashboardController','index'], $tenantMw);
$router->add('GET',  '/client/dashboard',    ['client/ClientDashboardController','index'], $tenantMw);
$router->add('GET',  '/client/api/vehicles', ['client/VehiclesApiController','list'], $tenantMw);

// Manager dashboard & users (tenant scoped)
$router->add('GET',  '/manager',                     ['manager/ManagerDashboardController','index'], $tenantMw);
$router->add('GET',  '/manager/users',               ['manager/ManagerUserController','index'], $tenantMw);
$router->add('GET',  '/manager/users/create',        ['manager/ManagerUserController','createForm'], $tenantMw);
$router->add('POST', '/manager/users',               ['manager/ManagerUserController','store'], $tenantMw);
$router->add('GET',  '/manager/users/{id}',          ['manager/ManagerUserController','show'], $tenantMw);
$router->add('POST', '/manager/users/{id}/update',   ['manager/ManagerUserController','update'], $tenantMw);
$router->add('POST', '/manager/users/{id}/delete',   ['manager/ManagerUserController','delete'], $tenantMw);
$router->add('GET',  $MB,                            ['manager/ManagerDashboardController','index'], $tenantMw);
$router->add('GET',  $MB . '/users',                 ['manager/ManagerUserController','index'], $tenantMw);
$router->add('GET',  $MB . '/users/create',          ['manager/ManagerUserController','createForm'], $tenantMw);
$router->add('POST', $MB . '/users',                 ['manager/ManagerUserController','store'], $tenantMw);
$router->add('GET',  $MB . '/users/{id}',            ['manager/ManagerUserController','show'], $tenantMw);
$router->add('POST', $MB . '/users/{id}/update',     ['manager/ManagerUserController','update'], $tenantMw);
$router->add('POST', $MB . '/users/{id}/delete',     ['manager/ManagerUserController','delete'], $tenantMw);

$router->add('GET', $MB . '/vehicles', ['VehicleController','index'], $tenantMw);
$router->add('GET', $MB . '/vehicle/create', ['VehicleController','create'], $tenantMw);
$router->add('POST', $MB . '/vehicle/store', ['VehicleController','store'], $tenantMw);
$router->add('GET', $MB . '/vehicle/edit', ['VehicleController','edit'], $tenantMw);
$router->add('POST', $MB . '/vehicle/update', ['VehicleController','update'], $tenantMw);
$router->add('POST', $MB . '/vehicle/delete', ['VehicleController','delete'], $tenantMw);

// Super admin tenant management (no tenant middleware - root entity)
$router->add('GET',  '/admin/login',   ['auth/TenantAdminAuthController','loginForm']);
$router->add('POST', '/admin/login',   ['auth/TenantAdminAuthController','login']);
$router->add('POST', '/admin/logout',  ['auth/TenantAdminAuthController','logout']);
$router->add('GET',  '/admin/tenants',                   ['admin/TenantAdminDashboardController','index']);
$router->add('GET',  '/admin/tenants/{id}/view',         ['admin/TenantAdminDashboardController','show']);
$router->add('GET',  '/admin/tenants/{id}/edit',         ['admin/TenantAdminDashboardController','editForm']);
$router->add('GET',  '/admin/tenants/{id}',              ['TenantController','show']);
$router->add('POST', '/admin/tenants',                   ['TenantController','store']);
$router->add('POST', '/admin/tenants/{id}/update',       ['TenantController','update']);
$router->add('POST', '/admin/tenants/{id}/deactivate',   ['TenantController','deactivate']);
$router->add('POST', '/admin/tenants/{id}/activate',     ['TenantController','activate']);
$router->add('POST', '/admin/tenants/{id}/rotate-api-key',['TenantController','rotateApiKey']);

// Public API utility: verify tenant API key (body: { subdomain, api_key })
$router->add('POST', '/api/verify-key', ['TenantController','verify']);

// Vehicle routes (merged from CRUD)
$router->add('GET', '/vehicles', ['VehicleController','index'], $tenantMw);
$router->add('GET', '/vehicle/create', ['VehicleController','create'], $tenantMw);
$router->add('POST', '/vehicle/store', ['VehicleController','store'], $tenantMw);
$router->add('GET', '/vehicle/edit', ['VehicleController','edit'], $tenantMw);
$router->add('POST', '/vehicle/update', ['VehicleController','update'], $tenantMw);
$router->add('POST', '/vehicle/delete', ['VehicleController','delete'], $tenantMw);

// Manager vehicle routes (pretty base)

return $router;
