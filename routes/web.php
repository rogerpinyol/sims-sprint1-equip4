<?php

return [
    // Manager Login
    ['GET',  '/manager/login',      'auth/ManagerAuthController', 'loginForm'],
    ['POST', '/manager/login',      'auth/ManagerAuthController', 'login'],
    ['POST', '/manager/logout',     'auth/ManagerAuthController', 'logout'],

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

    // Manager area (CRUD users limited to client creation)
    ['GET',  '/manager',          'manager/ManagerUserController', 'index'],
    ['GET',  '/manager/users/create', 'manager/ManagerUserController', 'createForm'],
    ['POST', '/manager/users',    'manager/ManagerUserController', 'store'],
    ['GET',  '/manager/users/(\d+)', 'manager/ManagerUserController', 'show'],
    ['POST', '/manager/users/(\d+)/update', 'manager/ManagerUserController', 'update'],
    ['POST', '/manager/users/(\d+)/delete', 'manager/ManagerUserController', 'delete'],
];