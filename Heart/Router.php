<?php

namespace App\Heart;
class Router{
    public $routes = [];
    private $currentMiddleware = null;

    public function middleware($middleware, $callback)
    {
        $this->currentMiddleware = $middleware;
        $callback($this);
        $this->currentMiddleware = null;
    }

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
            "function" => $functionName,
            "middleware" => $this->currentMiddleware
        ));
    }

    public function route(){
        $url = (parse_url($_SERVER['REQUEST_URI']));
        $url = $url['path'];
        $method = $_SERVER['REQUEST_METHOD'];
        for($x = 0; $x < count($this->routes); $x++){
            if($this->routes[$x]['path'] === $url && $this->routes[$x]['method'] === $method){
                if($this->routes[$x]['middleware']){
                    Middleware::resolve($this->routes[$x]['middleware']);
                }
                $className = $this->routes[$x]['controller'];
                $class = new $className();
                $functionName = $this->routes[$x]['function'];
                $result = $class->$functionName();
                die;
            }
        }
        echo json_encode(array("success" => false, "error" => "Route not found!"));
        die;
    }
}