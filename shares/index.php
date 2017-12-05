<?php


if (!file_exists("config.php")){
    header("Location: install/");
    die;
}

include("load.php");

$view = "search.phtml";
include("view/layout.phtml");


