<?php

$routes = [
    array('method' => 'POST', 'route' => 'api/user/login', 'regex' => '/^api\/user\/login$/', 'controller' => 'User', 'action' => 'Login'),
    array('method' => 'POST', 'route' => 'api/user/register', 'regex' => '/^api\/user\/register$/', 'controller' => 'User', 'action' => 'Register'),
    array('method' => 'GET', 'route' => 'api/user/:id', 'regex' => '/^api\/user\/[0-9a-fA-F]{24}$/', 'controller' => 'User', 'action' => 'GetUserById')
];
