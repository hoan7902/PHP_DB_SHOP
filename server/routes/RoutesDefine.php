<?php

$routes = [
    array('method' => 'POST', 'route' => 'api/user/login', 'regex' => '/^api\/user\/login\/?$/', 'controller' => 'UserController', 'action' => 'login'),
    array('method' => 'POST', 'route' => 'api/user/register', 'regex' => '/^api\/user\/register\/?$/', 'controller' => 'UserController', 'action' => 'register'),
    array('method' => 'GET', 'route' => 'api/user/:id', 'regex' => '/^api\/user\/[0-9]+\/?$/', 'controller' => 'UserController', 'action' => 'getUserById'),
    array('method' => 'GET', 'route' => 'api/users', 'regex' => '/^api\/users\/?$/', 'controller' => "UserController", 'action' => "getUsers"),
    array('method' => 'POST', 'route' => 'api/product/add', 'regex' => '/^api\/product\/add\/?$/', 'controller' => 'ProductController', 'action' => 'addProduct')
];
