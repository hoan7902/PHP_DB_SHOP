<?php

$routes = [
    // Login (user)
    array('method' => 'POST', 'route' => 'api/user/login', 'regex' => '/^api\/user\/login\/?$/', 'controller' => 'UserController', 'action' => 'login'),
    // Register (user)
    array('method' => 'POST', 'route' => 'api/user/register', 'regex' => '/^api\/user\/register\/?$/', 'controller' => 'UserController', 'action' => 'register'),
    // Get a user's information (admin/self)
    array('method' => 'GET', 'route' => 'api/user/{userId}', 'regex' => '/^api\/user\/[0-9]+\/?$/', 'controller' => 'UserController', 'action' => 'getUserById'),
    // Get a list users (admin)
    array('method' => 'GET', 'route' => 'api/users', 'regex' => '/^api\/users(\/|\/[0-9]+)?$/', 'controller' => "UserController", 'action' => "getUsers"),
    // Update user's profile (self)
    array('method' => 'PUT', 'route' => 'api/user/profile', 'regex' => '/^api\/user\/profile\/?$/', 'controller' => 'UserController', 'action' => 'updateProfile'),
    // Post a product (admin)
    array('method' => 'POST', 'route' => 'api/product/add', 'regex' => '/^api\/product\/add\/?$/', 'controller' => 'ProductController', 'action' => 'addProduct'),
    // Get a product (user)
    array('method' => 'GET', 'route' => 'api/product/{productId}', 'regex' => '/^api\/product\/[0-9]+\/?$/', 'controller' => 'ProductController', 'action' => 'getOneProduct')
];
