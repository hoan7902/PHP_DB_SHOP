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
    array('method' => 'GET', 'route' => 'api/products', 'regex' => '/^api\/products\/?$/', 'controller' => "ProductsController", 'action' => "getProducts"),
    // Update a product (admin)
    array('method' => 'POST', 'route' => 'api/product/update', 'regex' => '/^api\/product\/update\/?$/', 'controller' => 'ProductsController', 'action' => 'updateProduct'),
    // Remove a product out of category
    array('method' => 'DELETE', 'route' => 'api/product/removepoc', 'regex' => '/^api\/product\/removepoc\/?$/', 'controller' => "ProductsInCategoriesController", 'action' => "removeProductOutOfCat"),

    // Create a category (admin)
    array('method' => 'POST', 'route' => 'api/category', 'regex' => '/^api\/category\/?$/', 'controller' => "CategoriesController", 'action' => "addCategory"),
    // Get a list categories (user)
    array('method' => 'GET', 'route' => 'api/categories', 'regex' => '/^api\/categories\/?$/', 'controller' => "CategoriesController", 'action' => "getCategories"),
    // Delete a category (admin)
    array('method' => 'DELETE', 'route' => 'api/category/{categoryId}', 'regex' => '/^api\/category\/[0-9]+\/?$/', 'controller' => 'CategoriesController', 'action' => 'deleteCategory'),
    // Modify a category (admin)
    array('method' => 'PUT', 'route' => 'api/category/{categoryId}', 'regex' => '/^api\/category\/[0-9]+\/?$/', 'controller' => 'CategoriesController', 'action' => 'updateCategory'),

    // Create An Order (customer)
    array('method' => 'POST', 'route' => 'api/order/create', 'regex' => '/^api\/order\/create\/?$/', 'controller' => 'OrdersController', 'action' => 'createAnOrder'),
    // Update status of order (admin)
    array('method' => 'PUT', 'route' => 'api/order/update-status', 'regex' => '/^api\/order\/update-status\/?$/', 'controller' => 'OrdersController', 'action' => 'updateStatusOrder'),
    // My order (self)
    array('method' => 'GET', 'route' => 'api/order/my-orders', 'regex' => '/^api\/order\/my-orders\/?$/', 'controller' => 'OrdersController', 'action' => 'myOrders'),
    // Query orders (admin)
    array('method' => 'GET', 'route' => 'api/order/orders', 'regex' => '/^api\/order\/orders\/?$/', 'controller' => 'OrdersController', 'action' => 'getOrders'),
    // Cancel an order (self)
    array('method' => 'PUT', 'route' => 'api/order/cancel/{orderId}', 'regex' => '/^api\/order\/cancel\/[0-9]+\/?$/', 'controller' => 'OrdersController', 'action' => 'cancelAnOrder'),
    // Detail order (admin, self)
    array('method' => 'GET', 'route' => 'api/order/{orderId}', 'regex' => '/^api\/order\/[0-9]+\/?$/', 'controller' => 'OrdersController', 'action' => 'orderDetail'),

    // Add product to cart
    array('method' => 'POST', 'route' => 'api/cart/{productId}', 'regex' => '/^api\/cart\/[0-9]+\/?$/', 'controller' => 'CartsController', 'action' => 'addToCart'),
    // Remove product from cart
    array('method' => 'DELETE', 'route' => 'api/cart/{productId}', 'regex' => '/^api\/cart\/[0-9]+\/?$/', 'controller' => 'CartsController', 'action' => 'removeFromCart'),
    // Query products in my cart (self)
    array('method' => 'GET', 'route' => 'api/cart/my-cart', 'regex' => '/^api\/cart\/my-cart\/?$/', 'controller' => 'CartsController', 'action' => 'queryMyCart'),
];
