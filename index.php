<?php

require_once __DIR__ . '/vendor/autoload.php';
use App\Heart\Router;

//Add routes
$router = new Router();
$router->get("/users", UserController::class, "getUsers");
$router->post("/user", UserController::class, "addUser");
