<?php

namespace Shares\model;

class Data extends GenericModel{
    
    public $url;
    public $platform_id;
    public $created_at;
    public $shares;
    
    const TABLE_NAME = "data";
    
    public function __construct(){
        parent::__construct(self::TABLE_NAME);
    }
    
    public static function getAll(){
        return parent::getAll("\Shares\model\Data");
    }
    
    public static function get($url, $platform_id){
        $params = array(
            "url"           => $url,
            "platform_id"   => $platform_id
        );
        $result = parent::get($params, "\Shares\model\Data");
        if(count($result)){
            return $result[0];
        }else{
            return FALSE;
        }
    }
    
}