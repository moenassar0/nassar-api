<?php

namespace App\Heart;
use App\Middlewares\Auth;

class Middleware{
    const MAP = [
        "auth" => Auth::class
    ];

    public static function resolve($key){
        $middleware = static::MAP[$key];
        (new $middleware)->handle();
    }
}