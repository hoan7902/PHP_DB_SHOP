<?php

$routes = [
    // Role: admin - self - customer - user
    // Login (user)
    array('method' => 'POST', 'route' => 'api/user/login', 'regex' => '/^api\/user\/login\/?$/', 'controller' => 'UserController', 'action' => 'login'),
    // Register (user)
    array('method' => 'POST', 'route' => 'api/user/register', 'regex' => '/^api\/user\/register\/?$/', 'controller' => 'UserController', 'action' => 'register'),
    // Get a user's information (admin/self)
    array('method' => 'GET', 'route' => 'api/user/{userId}', 'regex' => '/^api\/user\/[0-9]+\/?$/', 'controller' => 'UserController', 'action' => 'getUserById'),
    // Get a list users (admin)
    array('method' => 'GET', 'route' => 'api/users/{frame}', 'regex' => '/^api\/users(\/|\/[0-9]+)?$/', 'controller' => "UserController", 'action' => "getUsers"),
    // Update user's profile (self)
    array('method' => 'PUT', 'route' => 'api/user/profile', 'regex' => '/^api\/user\/profile\/?$/', 'controller' => 'UserController', 'action' => 'updateProfile'),
    // Post a product (admin)
    array('method' => 'POST', 'route' => 'api/product/add', 'regex' => '/^api\/product\/add\/?$/', 'controller' => 'ProductController', 'action' => 'addProduct'),
    // Get a product (user)
    array('method' => 'GET', 'route' => 'api/product/{productId}', 'regex' => '/^api\/product\/[0-9]+\/?$/', 'controller' => 'ProductController', 'action' => 'getOneProduct'),
    // Get a list product (user)
    array('method' => 'GET', 'route' => 'api/products/{frame}', 'regex' => '/^api\/products\/?$/', 'controller' => "ProductController", 'action' => "getProducts"),
    // Create a category (admin)
    array('method' => 'POST', 'route' => 'api/category', 'regex' => '/^api\/category\/?$/', 'controller' => "CategoryController", 'action' => "addCategory"),
    // Get a list categories (user)
    array('method' => 'GET', 'route' => 'api/categories', 'regex' => '/^api\/categories\/?$/', 'controller' => "CategoryController", 'action' => "getCategories"),
];
