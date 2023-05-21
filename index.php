<?php

require_once __DIR__ . '/vendor/autoload.php';
use App\Heart\Router;
use App\Controllers\UserController;

//Add routes
$router = new Router();
$router->get("/users", UserController::class, "getUsers");

//Find if route requested matches one of our routes
$url = (parse_url($_SERVER['REQUEST_URI']));
$url = $url['path'];
$method = $_SERVER['REQUEST_METHOD'];
for($x = 0; $x < count($router->routes); $x++){
    if($router->routes[$x]['path'] === $url && $router->routes[$x]['method'] === $method){
        $className = $router->routes[$x]['controller'];
        $class = new $className();
        $functionName = $router->routes[$x]['function'];
        $result = $class->$functionName();
        echo $result;
    }
}