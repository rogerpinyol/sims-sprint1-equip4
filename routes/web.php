<?php
// Bootstrap Router instance and register all routes using Router::add
require_once __DIR__ . '/Router.php';

$router = new Router();

// Normalize manager base path
$MB = getenv('MANAGER_BASE') ?: '/ecomotion-manager';
if ($MB === '' || $MB === false) { $MB = '/ecomotion-manager'; }
if ($MB[0] !== '/') { $MB = '/' . $MB; }
if ($MB !== '/' && substr($MB, -1) === '/') { $MB = rtrim($MB, '/'); }

// Manager auth (legacy + pretty base)
$router->add('GET', '/manager/login', ['ManagerAuthController','loginForm']);
$router->add('POST','/manager/login', ['ManagerAuthController','login']);
$router->add('POST','/manager/logout',['ManagerAuthController','logout']);
$router->add('GET', $MB . '/login',   ['ManagerAuthController','loginForm']);
$router->add('POST',$MB . '/login',   ['ManagerAuthController','login']);
$router->add('POST',$MB . '/logout',  ['ManagerAuthController','logout']);

// Client auth
$router->add('GET',  '/auth/login',    ['ClientAuthController','loginForm']);
$router->add('POST', '/auth/login',    ['ClientAuthController','login']);
$router->add('POST', '/auth/logout',   ['ClientAuthController','logout']);
$router->add('GET',  '/register',      ['ClientAuthController','form']);
$router->add('POST', '/register',      ['ClientAuthController','register']);

// Client profile & dashboard
$router->add('GET',  '/profile',             ['ClientController','profile']);
$router->add('POST', '/profile',             ['ClientController','updateProfile']);
$router->add('POST', '/profile/delete',      ['ClientController','deleteAccount']);
$router->add('GET',  '/client',              ['ClientDashboardController','index']);
$router->add('GET',  '/client/dashboard',    ['ClientDashboardController','index']);
$router->add('GET',  '/client/api/vehicles', ['VehiclesApiController','list']);

// Manager dashboard & users (legacy + pretty base) using {id} placeholder
$router->add('GET',  '/manager',                     ['ManagerDashboardController','index']);
$router->add('GET',  '/manager/users',               ['ManagerUserController','index']);
$router->add('GET',  '/manager/users/create',        ['ManagerUserController','createForm']);
$router->add('POST', '/manager/users',               ['ManagerUserController','store']);
$router->add('GET',  '/manager/users/{id}',          ['ManagerUserController','show']);
$router->add('POST', '/manager/users/{id}/update',   ['ManagerUserController','update']);
$router->add('POST', '/manager/users/{id}/delete',   ['ManagerUserController','delete']);
$router->add('GET',  $MB,                            ['ManagerDashboardController','index']);
$router->add('GET',  $MB . '/users',                 ['ManagerUserController','index']);
$router->add('GET',  $MB . '/users/create',          ['ManagerUserController','createForm']);
$router->add('POST', $MB . '/users',                 ['ManagerUserController','store']);
$router->add('GET',  $MB . '/users/{id}',            ['ManagerUserController','show']);
$router->add('POST', $MB . '/users/{id}/update',     ['ManagerUserController','update']);
$router->add('POST', $MB . '/users/{id}/delete',     ['ManagerUserController','delete']);

// Super admin tenant management (REST style)
$router->add('GET',  '/admin/tenants',                   ['TenantController','index']);
$router->add('GET',  '/admin/tenants/{id}',              ['TenantController','show']);
$router->add('POST', '/admin/tenants',                   ['TenantController','store']);
$router->add('POST', '/admin/tenants/{id}/update',       ['TenantController','update']);
$router->add('POST', '/admin/tenants/{id}/deactivate',   ['TenantController','deactivate']);
$router->add('POST', '/admin/tenants/{id}/activate',     ['TenantController','activate']);
$router->add('POST', '/admin/tenants/{id}/rotate-api-key',['TenantController','rotateApiKey']);

return $router;
