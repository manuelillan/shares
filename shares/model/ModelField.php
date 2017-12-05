<?php

namespace Shares\model;

class ModelField{
    private $name;
    private $type;
    private $is_key;
    
    public function __construct($name, $type, $is_key = FALSE){
        $this->name     = $name;
        $this->type     = $type;
        $this->is_key   = $is_key;
    }
    
    public function isKey(){
        return $this->is_key;
    }
    
    public function getType(){
        return $this->type;
    }
    
    public function getName(){
        return $this->name;
    }
    
}
