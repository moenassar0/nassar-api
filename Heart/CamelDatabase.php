<?php

namespace App\Heart;
use \stdClass;
use \mysqli;
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
        $this->query->join = '';
        $this->query->bindings = [];
    }

    public function reset(){
        $this->query = new stdClass();
        $this->query->sqlFunction = '';
        $this->query->select = '*';
        $this->query->from = '';
        $this->query->where = '';
        $this->query->orderBy = '';
        $this->query->whereIn = '';
        $this->query->join = '';
        $this->query->bindings = [];
    }

    public function connection(){
        $servername = $_ENV['DB_SERVERNAME'];
        $username = $_ENV['DB_USERNAME'];
        $password = $_ENV['DB_PASSWORD'];
        $dbname = $_ENV['DB_NAME'];
    
        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        return $conn;
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

    public function orderBy(string $column, string $orderType = "ASC"){
        if($orderType !== "ASC" && $orderType !== "DESC"){
            die("Bad order type");
        }
        $this->query->orderBy = "$column $orderType";
        return $this;
    }

    public function where(string $column, string $operator, string $value){
        $this->query->where = "$column $operator ?";
        array_push($this->query->bindings, $value);
        return $this;
    }

    public function orWhere(string $column, string $operator, string $value){
        $this->query->where .= " OR $column $operator ?";
        array_push($this->query->bindings, $value);
        return $this;
    }

    public function andWhere(string $column, string $operator, string $value){
        $this->query->where .= " AND $column $operator ?";
        array_push($this->query->bindings, $value);
        return $this;
    }

    public function whereIn($column, $subquery)
    {
        $subquery = $subquery->getSQL();
        $this->query->whereIn = "$column IN ($subquery)";
        return $this;
    }

    public function join(string $joinedTable, string $mainTableColumn, string $operator, string $joinedTableColumn){
        $this->query->join = "$joinedTable ON $mainTableColumn $operator $joinedTableColumn";
        return $this;
    }

    public function belongsTo($mainTable, $belongedTable, $foreignKey, $specificID = false){
        if(!$specificID){
            $mainTableItems = $this->select("*")->from($mainTable)->execute();
        }
        else{
            $mainTableItems = $this->select("*")->from($mainTable)->where("id", "=", $specificID)->execute();
        }
        $this->reset();
        $mainTableItems = $mainTableItems["items"];
        $belongedTableItems = $this->select("*")->from($belongedTable)->execute();
        $belongedTableItems = $belongedTableItems["items"];
        
        for($x = 0; $x < count($mainTableItems); $x++){
            if(!isset($mainTableItems[$x][$belongedTable])) $mainTableItems[$x][$belongedTable] = array();
            for($y = 0; $y < count($belongedTableItems); $y++){
                if($belongedTableItems[$y][$foreignKey] === $mainTableItems[$x]['id']){
                    array_push($mainTableItems[$x][$belongedTable], $belongedTableItems[$y]);
                }
            }
        }
        return $mainTableItems;
    }

    public function getSQL(){
        return $this->execute(false);
    }

    public function execute($executeSQL = true){
        $sqlFunction = $this->query->sqlFunction;
        $outputQuery = "";
        if($sqlFunction === "SELECT"){
            $outputQuery .= "SELECT {$this->query->select} FROM {$this->query->from}";

            if(!empty($this->query->join)){
                $outputQuery .= " JOIN {$this->query->join}";
            }

            if(!empty($this->query->where)){
                $outputQuery .= " WHERE {$this->query->where}";
            }

            if (!empty($this->query->whereIn)) {
                $outputQuery .= " WHERE {$this->query->whereIn}";
            }

            if(!empty($this->query->orderBy)){
                $outputQuery .= " ORDER BY {$this->query->orderBy}";
            }
            
            if($executeSQL) return $this->executeQuery($outputQuery, $sqlFunction);
            else return $outputQuery;
        }
    }

    public function executeQuery($query, $sqlFunction){
        $conn = $this->connection();
        $response = [];
        $stmt = $conn->prepare($query);
        
        //Error handling
        if(!$stmt || !$stmt->execute($this->query->bindings)){
          $response['success'] = "false";
          $response['error'] = $conn->error;
          return $response;
        }

        if($sqlFunction === "SELECT"){
            $result = $stmt->get_result();
            $response['items'] = array();
            while($a = $result->fetch_assoc()){
                array_push($response['items'], $a);
            }
        }

        return $response;
    }
}