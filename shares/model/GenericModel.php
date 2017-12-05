<?php

namespace Shares\model;

use Shares\lib\DBConnector;

abstract class GenericModel{
    
    private $table_name = "";
    
    protected static $fields = array();
    
    const TABLE_NAME = "";
    
    public function __construct($table_name){
        $this->table_name   = $table_name;
        self::initFields($table_name);
    }
    
    protected function getTableName(){
        return $this->table_name;
    }
    
    protected static function getPrimaryKeyFields(){
        $keys = array();
        foreach(self::getFields() as $field){
            if ($field->isKey()){
                $keys[] = $field;
            }
        }
        return $keys;
    }
    
    protected static function getNotPrimaryKeyFields(){
        $keys = array();
        foreach(self::getFields() as $field){
            if (!$field->isKey()){
                $keys[] = $field;
            }
        }
        return $keys;
    }
    
    public static function getFields(){
        if(count(self::$fields) == 0){
            self::initFields();
        }
        return self::$fields;
    }
    
    public static function initFields($table_name=FALSE){
        if(!$table_name){
            $table_name = static::TABLE_NAME;
        }
        $conn = DBConnector::getConnection();
        $stmt = $conn->prepare(
            "SELECT "
                . "column_name, "
                . "column_type, "
                . "column_key "
            . "FROM information_schema.columns "
            . "WHERE table_name=? "
            . "AND table_schema=?;"
        );
        
        $schema = _DB_NAME_;
        $stmt->bind_param("ss", $table_name, $schema);
        $stmt->execute();
        $stmt->bind_result($col_name, $col_type, $col_key);
        
        self::$fields = array();
        while($stmt->fetch()){
            self::$fields[] = new ModelField($col_name, $col_type, ($col_key=="PRI"));
        }
        $stmt->close();
    }
    
    public static function get(array $params, $className){
        $conn = DBConnector::getConnection();
        $results = array();
        
        $sql = "select * from " . static::TABLE_NAME;
        if(count($params) > 0){
            $where = array();
            foreach($params as $column_name=>$value){
                $where[] = $column_name . "='" . $conn->escape_string($value) . "' ";
            }
            $sql .= " where " . implode(" and ", $where);
        }else{
            throw Exception ("Must filter by at least 1 param");
        }
        $sql_result = $conn->query($sql);
        while($row = $sql_result->fetch_assoc()){
            $model = new $className();
            foreach($row as $column_name=>$value){
                $model->{$column_name} = $value;
            }
            $results[] = $model;
        }
        
        $sql_result->close();
        return $results;
    }
    
    public static function getAll($class_name){
        $results = array();
        $table = constant($class_name . "::TABLE_NAME");
        $conn = DBConnector::getConnection();
        $sql = "SELECT * FROM " . $table;
        if ($sql_result = $conn->query($sql)){
            while($row = $sql_result->fetch_object()){
                $model = new $class_name();
                foreach((array)$row as $column=>$value){
                    $model->{$column} = $value;
                }
                $results[] = $model;
            }
            $sql_result->close();
        }
        
        return $results;
    }
    
    public function save(){
        $pks            = self::getPrimaryKeyFields();
        $npks           = self::getNotPrimaryKeyFields();
        $cols           = self::getFields();
        $col_names      = array_map(function($el){return $el->getName();},$cols);
        
        $sets = array();
        $npks_col_names = array();
        foreach($npks as $npk){
            $sets[] = $npk->getName() . "=? ";
            $npks_col_names[] = $npk->getName();
        }

        $sql = "INSERT INTO " . $this->table_name . " (" 
            . implode(",",$col_names)
            . ") VALUES ("
            . str_repeat("?,", count($col_names) - 1)
            . "?) "
            . "ON DUPLICATE KEY UPDATE " 
            . implode(",",$sets);
        
        $conn = DBConnector::getConnection();
        $stmt = $conn->prepare($sql);
        $params = array(str_repeat("s",count($col_names) + count($npks_col_names)));
        foreach($col_names as $col_name){
            $params[] = &$this->{$col_name};
        }
        
        foreach($npks_col_names as $npk_col_name){
            $params[] = &$this->{$npk_col_name};
        }
        
        call_user_func_array(array($stmt, "bind_param"), $params);
        $stmt->execute();
        $stmt->close();
        if($conn->error) {
            echo ($conn->error);
            throw new \Exception($conn->error);
        }
        return TRUE;
        
    }
}