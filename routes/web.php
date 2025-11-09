<?php

return [

    // Public flows
    ['GET',  '/',                 'PageController', 'home'],
    ['GET',  '/terms',            'PageController', 'terms'],
    ['GET',  '/privacy',          'PageController', 'privacy'],
    ['GET',  '/login',            'AuthController', 'loginForm'],
    ['POST', '/login',            'AuthController', 'login'],
    ['POST', '/logout',           'AuthController', 'logout'],
    ['GET',  '/register',         'RegistrationController', 'form'],
    ['POST', '/register',         'RegistrationController', 'register'],

    // User flows
    ['GET', '/profile',             'PublicUserController', 'profile'],
    ['POST', '/profile',            'PublicUserController', 'updateProfile'],

    // Client app dashboard
    ['GET', '/client',            'ClientAppController', 'index'],

    // Admin flows
    ['GET',  '/admin',            'AdminUserController', 'index'],
    ['GET',  '/admin/users/create',     'AdminUserController', 'createForm'],
    ['POST', '/admin/users',            'AdminUserController', 'store'],
    ['GET',  '/admin/users/(\d+)',     'AdminUserController', 'show'],
    ];