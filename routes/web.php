<?php

// Each route: ['METHOD', 'pattern', 'ControllerClass', 'methodName']
return [
    ['GET',  '/',                 'PageController', 'home'],
    ['GET',  '/users',            'UserController', 'index'],
    ['GET',  '/users/create',     'UserController', 'createForm'],
    ['POST', '/users',            'UserController', 'store'],
    ['GET',  '/users/(\d+)',      'UserController', 'show'],
    ['GET',  '/terms',            'PageController', 'terms'],
    ['GET',  '/privacy',          'PageController', 'privacy'],
];