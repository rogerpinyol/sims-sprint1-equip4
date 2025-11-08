<?php

return [

    // Public flows
    ['GET',  '/',                 'PageController', 'home'],
    ['GET',  '/terms',            'PageController', 'terms'],
    ['GET',  '/privacy',          'PageController', 'privacy'],
    ['GET',  '/login',            'AuthController', 'loginForm'],
    ['POST', '/login',            'AuthController', 'login'],
    ['POST', '/logout',           'AuthController', 'logout'],
    ['GET',  '/register',         'PublicUserController', 'registerForm'],
    ['POST', '/register',         'PublicUserController', 'register'],

    // User flows
    ['GET', '/profile',             'PublicUserController', 'profile'],
    ['POST', '/profile',            'PublicUserController', 'updateProfile'],

    // Admin flows
    ['GET',  '/users',            'AdminUserController', 'index'],
    ['GET',  '/users/create',     'AdminUserController', 'createForm'],
    ['POST', '/users',            'AdminUserController', 'store'],
    ['GET',  '/users/(\d+)',     'AdminUserController', 'show'],
    ];