<?php

namespace App\Heart;
use \stdClass;

class CamelDatabase{

    private $operation;
    private $table;
    private $columns;
    private $query;

    public function __construct()
    {
        $this->query = new stdClass();
        $this->query->sqlFunction = '';
        $this->query->select = '*';
        $this->query->from = '';
        $this->query->where = '';
        $this->query->orderBy = '';
        $this->query->whereIn = '';
    }

    public function select(...$columns){
        $this->query->sqlFunction = "SELECT";
        $this->query->select = implode(', ', $columns);
        return $this;
    }

    public function from(string $tableName){
        $this->query->from = $tableName;
        return $this;
    }

    public function orderBy(string $column){
        $this->query->orderBy = $column;
        return $this;
    }

    public function where(string $column, string $operator, string $value){
        $this->query->where = "$column $operator '$value'";
        return $this;
    }

    public function execute(){
        $sqlFunction = $this->query->sqlFunction;
        $outputQuery = "";
        if($sqlFunction === "SELECT"){
            $outputQuery .= "SELECT {$this->query->select} FROM {$this->query->from}";

            if (!empty($this->query->where)) {
                $outputQuery .= " WHERE {$this->query->where}";
            }

            return $outputQuery;
        }
    }

    // public function select(array $columns){
    //     $this->sql .= "SELECT ";
    //     for($x = 0; $x < count($columns); $x++){
    //         if($x === (count($columns) - 1)){
    //             $this->sql .= $columns[$x] . " ";
    //         }
    //         else{
    //             $this->sql .= $columns[$x] . ", ";
    //         }
    //     } 
    //     return $this;
    // }

    // public function from($table){
    //     $this->sql .= " FROM " . $table; 
    //     return $this;
    // }

    // public function where(string $column, string $operator, string $value){
    //     $this->sql .= " WHERE " . $column . " " . $operator . " " . $value;
    //     return $this;
    // }

    // public function orderBy(string $column){
    //     $this->sql .= " ORDER BY " . $column;
    //     return $this;
    // }

    // public function join(string $joinedTable, string $mainTableColumn, string $operator, string $joinedTableColumn){
    //     $this->sql .= " JOIN " . $joinedTable . " ON " . $mainTableColumn . " " . $operator . " " . $joinedTableColumn;
    //     return $this;
    // }

    // public function whereIn($column, $subquery)
    // {
    //     $this->query->whereIn = "$column IN ($subquery)";
    //     return $this;
    // }

    // public function execute(){
    //     return $this->sql;
    // }
}