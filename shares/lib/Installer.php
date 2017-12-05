<?php

namespace Shares\lib;

use Shares\model\Platform;

class Installer {

    private $connection = FALSE;

    public function __construct() {
        $this->connection = DBConnector::getConnection();
    }

    public function install() {
        if (!$this->connection) {
            throw new \Exception("No se puede crear la BDD");
        }
        $sql_drop_data = 'DROP TABLE IF EXISTS `data`;';
        $this->connection->query($sql_drop_data);
        
        $sql_data = ' 
            CREATE TABLE `data` (
                `url` VARCHAR(255) NOT NULL,
                `platform_id` INT(11) NOT NULL,
                `created_at` DATETIME NOT NULL,                
                `shares` INT(11) DEFAULT \'0\',
                PRIMARY KEY (`url`, `platform_id`)
            );';
        
        $this->connection->query($sql_data);
        
        $sql_drop_data = 'DROP TABLE IF EXISTS `platforms`;';
        $this->connection->query($sql_drop_data);
        
        $sql_platforms = '
            CREATE TABLE `platforms` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(50) NOT NULL,
                `connection_type` ENUM("URL") NOT NULL DEFAULT "URL",
                `connection_options` TEXT,
                `image_url` VARCHAR(255),
                PRIMARY KEY (`id`)
            );';
        
        $this->connection->query($sql_platforms);
        
        //Create default platforms
        $facebook = new Platform("facebook");
        $facebook->image_url = "https://www.facebook.com/images/fb_icon_325x325.png";
        $facebook->connection_type = Platform::CONNECTION_TYPE_URL;
        $facebook_options = array(
            "endpoint"          => "https://graph.facebook.com/?fields=share&id={{url}}",
            "response_format"   => "json",
            "response_value"   => "share.share_count"
        );
        $facebook->connection_options = json_encode((object)$facebook_options);
        $facebook->save();
        
        $linkedin = new Platform("linkedin");
        $linkedin->image_url = "https://media.licdn.com/mpr/mpr/AAEAAQAAAAAAAAgTAAAAJDAxM2I0ZGE4LWQ3YzUtNDAyYi1hZTU1LTAwNGIzMTAyNjQ3MA.png";
        $linkedin->connection_type = Platform::CONNECTION_TYPE_URL;
        $linkedin_options = array(
            "endpoint"                  => "https://www.linkedin.com/countserv/count/share?url={{url}}",
            "response_format"           => "custom",
            "response_regex_pattern"    => "/^.*count.*?(\d+).*$/",
            "response_regex_replace"    => "$1"
        );
        $linkedin->connection_options = json_encode((object)$linkedin_options);
        $linkedin->save();
        
        $pinterest = new Platform("pinterest");
        $pinterest->image_url = "https://cdn4.iconfinder.com/data/icons/social-messaging-ui-color-shapes-2-free/128/social-pinterest-square2-128.png";
        $pinterest->connection_type = Platform::CONNECTION_TYPE_URL;
        $pinterest_options = array(
            "endpoint"                  => "https://api.pinterest.com/v1/urls/count.json?url={{url}}",
            "response_format"           => "custom",
            "response_regex_pattern"    => "/^.*count.*?(\d+).*$/",
            "response_regex_replace"    => "$1"
        );
        $pinterest->connection_options = json_encode((object)$pinterest_options);
        $pinterest->save();
        
        return TRUE;
    }

}
