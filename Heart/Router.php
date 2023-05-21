<?php

namespace App\Heart;
class Router{
    public $routes = [];

    public function get(string $path, string $controller, string $functionName){
        array_push($this->routes, array(
            "path" => $path,
            "method" => "GET",
            "controller" => $controller,
            "function" => $functionName
        ));
    }

    public function post(){
        return 0;
    }
}