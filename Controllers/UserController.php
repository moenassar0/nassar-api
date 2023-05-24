<?php

namespace App\Controllers;
use App\Heart\CamelDatabase;

class UserController{
    public function getUsers(){
        $db = new CamelDatabase();
        $whereINQuery = $db->select("user_id")->from("mock_data")->where("user_id", ">", "980")->andWhere("id", ">", "900")->andWhere("id", "<", "950");
        
        $db = new CamelDatabase();
        $data = $db->select("*")->from("users")->whereIn("id", $whereINQuery)->execute();

        echo json_encode($data);
    }

    public function postUser(){

    }
}