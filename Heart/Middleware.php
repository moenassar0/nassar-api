<?php

namespace App\Heart;
use App\Middlewares\Auth;
use Exception;

class Middleware{
    const MAP = [
        "auth" => Auth::class
    ];

    public static function resolve($key){
        if(isset(static::MAP[$key])){
            $middleware = static::MAP[$key];
            (new $middleware)->handle();
        }
        else throw new Exception("Middleware " . $key . " is failing! Not found class or middleware key.");
    }
}