<?php

use App\Heart\Router;
use App\Controllers\UserController;

require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . './');
$dotenv->load();

$router = new Router();

$router->middleware('auth', function($router) {
    $router->get('/users', UserController::class, 'getUsers');
    $router->post("/user", UserController::class, "postUser");
});

$router->route();