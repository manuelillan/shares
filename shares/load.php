<?php

if(file_exists(__DIR__."/config.php")){
    require_once (__DIR__."/config.php");
}

require_once(__DIR__."/model/GenericModel.php");
foreach (glob(__DIR__ . "/model/*.php") as $filename)
{
    require_once $filename;
}
foreach (glob(__DIR__ . "/lib/*.php") as $filename)
{
    require $filename;
}
