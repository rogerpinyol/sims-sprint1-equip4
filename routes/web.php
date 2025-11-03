<?php

return [
    ['GET',  '/',                 'PageController', 'home'],
    ['GET',  '/terms',            'PageController', 'terms'],
    ['GET',  '/privacy',          'PageController', 'privacy'],

    // Public flows
    ['GET',  '/register',         'PublicUserController', 'registerForm'],
    ['POST', '/register',         'PublicUserController', 'register'],

    // Admin flows
    ['GET',  '/users',            'AdminUserController', 'index'],
    ['GET',  '/users/create',     'AdminUserController', 'createForm'],
    ['POST', '/users',            'AdminUserController', 'store'],
    ['GET',  '/users/(\d+)',     'AdminUserController', 'show'],
];