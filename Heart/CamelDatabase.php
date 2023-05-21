<?php

namespace App\Heart;

class CamelDatabase{

    private $operation;
    private $table;
    private $columns;
    private $sql;

    public function __construct()
    {
        $this->operation = "";
        $this->table = "";
        $this->columns = "*";
    }

    public function select(array $columns){
        $this->sql .= "SELECT ";
        for($x = 0; $x < count($columns); $x++){
            if($x === (count($columns) - 1)){
                $this->sql .= $columns[$x] . " ";
            }
            else{
                $this->sql .= $columns[$x] . ", ";
            }
        } 
        return $this;
    }

    public function from($table){
        $this->sql .= " FROM " . $table; 
        return $this;
    }

    public function execute(){
        return $this->sql;
    }
}