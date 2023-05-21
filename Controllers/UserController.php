<?php

namespace App\Controllers;
use App\Heart\CamelDatabase;

class UserController{
    public function getUsers(){
        $db = new CamelDatabase();
        print_r(
            $db
                ->select(array("id", "firstName"))
                ->from("users")
                ->where("id", "=", "0")
                ->orderBy('id')
                ->execute()
        );
    }
}