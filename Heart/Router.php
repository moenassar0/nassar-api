<?php

namespace App\Heart;
class Router{
    public $routes = [];

    public function get(string $path, string $controller, string $functionName){
        $this->addRoute("GET", $path, $controller, $functionName);
    }

    public function post(string $path, string $controller, string $functionName){
        $this->addRoute("POST", $path, $controller, $functionName);
    }

    public function addRoute(string $method, string $path, string $controller, string $functionName){
        array_push($this->routes, array(
            "path" => $path,
            "method" => $method,
            "controller" => $controller,
            "function" => $functionName
        ));
    }
}