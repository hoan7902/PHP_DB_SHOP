<?php

$routes = [
    // Role: admin - self - customer - user
    // Login (user)
    array('method' => 'POST', 'route' => 'api/user/login', 'regex' => '/^api\/user\/login\/?$/', 'controller' => 'UsersController', 'action' => 'login'),
    // Register (user)
    array('method' => 'POST', 'route' => 'api/user/register', 'regex' => '/^api\/user\/register\/?$/', 'controller' => 'UsersController', 'action' => 'register'),
    // Get a user's information (admin/self)
    array('method' => 'GET', 'route' => 'api/user/{userId}', 'regex' => '/^api\/user\/[0-9]+\/?$/', 'controller' => 'UsersController', 'action' => 'getUserById'),
    // Get a list users (admin)
    array('method' => 'GET', 'route' => 'api/users/{frame}', 'regex' => '/^api\/users(\/|\/[0-9]+)?$/', 'controller' => "UsersController", 'action' => "getUsers"),
    // Update user's profile (self)
    array('method' => 'PUT', 'route' => 'api/user/profile', 'regex' => '/^api\/user\/profile\/?$/', 'controller' => 'UsersController', 'action' => 'updateProfile'),
    // Update profile image (self)
    array('method' => 'POST', 'route' => 'api/user/avatar', 'regex' => '/^api\/user\/avatar\/?$/', 'controller' => 'UsersController', 'action' => 'updateAvatar'),
    // Post a product (admin)
    array('method' => 'POST', 'route' => 'api/product/add', 'regex' => '/^api\/product\/add\/?$/', 'controller' => 'ProductsController', 'action' => 'addProduct'),
    // Get a product (user)
    array('method' => 'GET', 'route' => 'api/product/{productId}', 'regex' => '/^api\/product\/[0-9]+\/?$/', 'controller' => 'ProductsController', 'action' => 'getOneProduct'),
    // Delete a product (admin)
    array('method' => 'DELETE', 'route' => 'api/product/{productId}', 'regex' => '/^api\/product\/[0-9]+\/?$/', 'controller' => 'ProductsController', 'action' => 'deleteOneProduct'),
    // Get a list product (user)
    array('method' => 'GET', 'route' => 'api/products/{frame}', 'regex' => '/^api\/products\/?$/', 'controller' => "ProductsController", 'action' => "getProducts"),
    // Create a category (admin)
    array('method' => 'POST', 'route' => 'api/category', 'regex' => '/^api\/category\/?$/', 'controller' => "CategoriesController", 'action' => "addCategory"),
    // Get a list categories (user)
    array('method' => 'GET', 'route' => 'api/categories', 'regex' => '/^api\/categories\/?$/', 'controller' => "CategoriesController", 'action' => "getCategories"),
    // Remove a product out of category
    array('method' => 'PUT', 'route' => 'api/product/removepoc', 'regex' => '/^api\/product\/removepoc\/?$/', 'controller' => "ProductsInCategoriesController", 'action' => "removeProductOutOfCat"),
];
