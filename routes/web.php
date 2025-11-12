<?php

// Revisar com funciona aquesta variable d'entorn
$MB = getenv('MANAGER_BASE') ?: '/ecomotion-manager';
if ($MB === '' || $MB === false) { $MB = '/ecomotion-manager'; }
if ($MB[0] !== '/') { $MB = '/' . $MB; }
if ($MB !== '/' && substr($MB, -1) === '/') { $MB = rtrim($MB, '/'); }

return [
    // Manager Login
    ['GET',  '/manager/login',      'auth/ManagerAuthController', 'loginForm'],
    ['POST', '/manager/login',      'auth/ManagerAuthController', 'login'],
    ['POST', '/manager/logout',     'auth/ManagerAuthController', 'logout'],

    // Manager Login (pretty base)
    ['GET',  $MB . '/login',      'auth/ManagerAuthController', 'loginForm'],
    ['POST', $MB . '/login',      'auth/ManagerAuthController', 'login'],
    ['POST', $MB . '/logout',     'auth/ManagerAuthController', 'logout'],

    // Client Login and Register
    ['GET',  '/auth/login',     'auth/ClientAuthController', 'loginForm'],
    ['POST', '/auth/login',     'auth/ClientAuthController', 'login'],
    ['POST', '/auth/logout',    'auth/ClientAuthController', 'logout'],

    // Optional GET convenience redirectors (non-RESTful but handy)
    ['GET',  '/register',         'auth/ClientAuthController', 'form'],
    ['POST', '/register',         'auth/ClientAuthController', 'register'],

    // Client CRUD
    ['GET', '/profile',           'client/ClientController', 'profile'],
    ['POST', '/profile',          'client/ClientController', 'updateProfile'],
    ['POST', '/profile/delete',   'client/ClientController', 'deleteAccount'],
    ['GET', '/client',            'client/ClientDashboardController', 'index'],
    ['GET', '/client/dashboard',  'client/ClientDashboardController', 'index'],
    // Live vehicles API for client dashboard map (Leaflet)
    ['GET', '/client/api/vehicles', 'client/VehiclesApiController', 'list'],

    // Manager overview (no CRUD here)
    ['GET',  '/manager',          'manager/ManagerDashboardController', 'index'],
    // Manager users section (CRUD)
    ['GET',  '/manager/users',    'manager/ManagerUserController', 'index'],
    ['GET',  '/manager/users/create', 'manager/ManagerUserController', 'createForm'],
    ['POST', '/manager/users',    'manager/ManagerUserController', 'store'],
    ['GET',  '/manager/users/(\d+)', 'manager/ManagerUserController', 'show'],
    ['POST', '/manager/users/(\d+)/update', 'manager/ManagerUserController', 'update'],
    ['POST', '/manager/users/(\d+)/delete', 'manager/ManagerUserController', 'delete'],

    // Pretty base routes (alias)
    ['GET',  $MB,                       'manager/ManagerDashboardController', 'index'],
    ['GET',  $MB . '/users',            'manager/ManagerUserController', 'index'],
    ['GET',  $MB . '/users/create',     'manager/ManagerUserController', 'createForm'],
    ['POST', $MB . '/users',            'manager/ManagerUserController', 'store'],
    ['GET',  $MB . '/users/(\d+)',      'manager/ManagerUserController', 'show'],
    ['POST', $MB . '/users/(\d+)/update','manager/ManagerUserController', 'update'],
    ['POST', $MB . '/users/(\d+)/delete','manager/ManagerUserController', 'delete'],
];