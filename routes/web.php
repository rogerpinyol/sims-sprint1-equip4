<?php

return [
    // Login Admin
    ['GET',  '/admin/login',      'auth/AdminAuthController', 'loginForm'],
    ['POST', '/admin/login',      'auth/AdminAuthController', 'login'],
    ['POST', '/admin/logout',     'auth/AdminAuthController', 'logout'],

    // Login y registro Cliente (unificado)
    ['GET',  '/client/login',     'auth/ClientAuthController', 'loginForm'],
    ['POST', '/client/login',     'auth/ClientAuthController', 'login'],
    ['POST', '/client/logout',    'auth/ClientAuthController', 'logout'],
    ['GET',  '/register',         'auth/ClientAuthController', 'form'],
    ['POST', '/register',         'auth/ClientAuthController', 'register'],

    // CRUD Cliente
    ['GET', '/profile',           'client/ClientController', 'profile'],
    ['POST', '/profile',          'client/ClientController', 'updateProfile'],
    ['GET', '/client',            'client/ClientDashboardController', 'index'],

    // CRUD Admin
    ['GET',  '/admin',            'admin/AdminUserController', 'index'],
    ['GET',  '/admin/users/create', 'admin/AdminUserController', 'createForm'],
    ['POST', '/admin/users',        'admin/AdminUserController', 'store'],
    ['GET',  '/admin/users/(\\d+)', 'admin/AdminUserController', 'show'],
];