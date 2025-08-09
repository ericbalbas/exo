<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;

$router = new Router;

$router->get('/', function () {
    require_once __DIR__ . '/../src/Views/';
});

$router->dispatch();
