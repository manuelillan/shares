<?php

namespace Shares\lib;

abstract class DBConnector{
    private static $CONNECTION = FALSE;
    
    /**
     * 
     * @return \mysqli | FALSE
     */
    public static function getConnection(){
        if (!self::$CONNECTION && _DB_SERVER_){
            self::$CONNECTION = new \mysqli(_DB_SERVER_, _DB_USER_, _DB_PASSWORD_, _DB_NAME_);
        }
        return self::$CONNECTION;
    }
    
}

