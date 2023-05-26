<?php

namespace App\Heart;
use \stdClass;
use \mysqli;
use \PDO;
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
        if(empty($this->query->where)){
            $this->where($column, $operator, $value);
        }
        $this->query->where .= " AND $column $operator ?";
        array_push($this->query->bindings, $value);
        return $this;
    }

    // public function whereIn($column, $subquery)
    // {
    //     $sql = $subquery->getSQL();
    //     $this->query->bindings = array_merge($this->query->bindings, $subquery->query->bindings);
    //     $this->query->whereIn = "$column IN ($sql)";
    //     return $this;
    // }

    public function whereIn($column, $callback)
    {
        $subQueryBuilder = new CamelDatabase();
        $callback($subQueryBuilder);

        $subQuery = '(' . $subQueryBuilder->getSQL() . ')';
        echo $subQuery . "\n";
        $this->query->whereIn = "$column IN $subQuery";
        $this->query->bindings = array_merge($this->query->bindings, $subQueryBuilder->query->bindings);
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
        
        $mainTableCount = count($mainTableItems);
        $belongedTableCount = count($belongedTableItems);

        //Create lookup table
        $mainTableItemsMap = array();
        foreach ($mainTableItems as $mainTableItem) {
            $mainTableItemsMap[$mainTableItem['id']] = $mainTableItem;
        }

        for($y = 0; $y < $mainTableCount; $y++){
            $mainTableItems[$y][$belongedTable] = array();
        }
        
        for($x = 0; $x < $belongedTableCount; $x++){
            $foreignUserID = $belongedTableItems[$x][$foreignKey];
            if(isset($mainTableItemsMap[$foreignUserID])){
                array_push($mainTableItems[$foreignUserID - 1][$belongedTable], $belongedTableItems[$x]);
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

            if (!empty($this->query->whereIn)){
                if(!empty($this->query->where)) $outputQuery .= " AND {$this->query->whereIn}";
                else $outputQuery .= " WHERE {$this->query->whereIn}";
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
          $response['success'] = false;
          $response['error'] = $conn->error;
          return $response;
        }
        
        if($sqlFunction === "SELECT"){
            $response['success'] = true;
            $result = $stmt->get_result();
            $response['items'] = array();
            while($a = $result->fetch_assoc()){
                array_push($response['items'], $a);
            }
        }
        else if($sqlFunction === "INSERT"){
            $response['success'] = true;
            $response['message'] = "Successfully inserted data.";
        }
        
        return $response;
    }

    /* Functions to help CRUD Operations */
    public function insert($item, $tableName, $autoIncrement = true){
        $tableFields = $this->returnTableFields($tableName);
        if($autoIncrement){
            $this->deleteKey($item, "id");
            $this->deleteKey($tableFields, "id");
        }
        $keys = implode(", ", array_keys($item));
        $values = "";
        for($x = 0; $x < count(array_keys($item)); $x++){
            if($x < count(array_keys($item)) - 1) $values .= "?" . ", ";
            else $values .= "?";
        }
        $outputQuery = "INSERT INTO $tableName ($keys) VALUES ($values)";
        $this->query->bindings = array_values($item);
        return $this->executeQuery($outputQuery, "INSERT");
    }

    public function filterSearch($filterObject, $tableName){
        $keys = array_keys($filterObject);
        $values = array_values($filterObject);
        $dbObject = $this->select("*")->from($tableName);
        for($x = 0; $x < count($keys); $x++){
            $dbObject->andWhere($keys[$x], "=", $values[$x]);
        }
        return $this->execute();
    }

    /* Custom functions that may be helpful */
    public function returnTableFields(string $table){
        $conn = $this->connection();
        $q = $conn->prepare("DESCRIBE ". $table);
        $q->execute();
        $result = $q->get_result();
        $fieldNames = [];
        while($a = $result->fetch_assoc()){
            // $pair = array($a['Field'] => "");
            // array_push($fieldNames, $pair);
            $fieldNames[$a['Field']] = "";
        }
        return $fieldNames;
    }

    function sameKeys($array1, $array2) {
        $keys1 = array_keys($array1);
        $keys2 = array_keys($array2);
        sort($keys1);
        sort($keys2);
        return $keys1 == $keys2;
    }

    function deleteKey(&$array, $key) {
        if (array_key_exists($key, $array)) {
            unset($array[$key]);
            return true;
        } else {
            return false;
        }
    }
}