<?php
namespace Shares\model;

class Platform extends GenericModel{
    
    public $id;
    public $name;
    public $connection_options;
    public $connection_type;
    public $image_url;
    
    const CONNECTION_TYPE_URL = "URL";
    
    const TABLE_NAME = "platforms";
    
    public function __construct($name=""){
        $this->name = $name;
        parent::__construct(self::TABLE_NAME);
    }
    
    public static function getAll(){
        return parent::getAll("Shares\model\Platform");
    }
    
    public static function get(array $params){
        return parent::get($params, "Shares\model\Platform");
    }
    
}

