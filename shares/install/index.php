<?php

use Shares\lib\Installer;

$view = "install.phtml";
$install_error = FALSE;

if (!empty($_POST)){
    require "../load.php";

    $db_server      = $_POST["db_server"];
    $db_name        = $_POST["db_name"];
    $db_user        = $_POST["db_user"];
    $db_password    = $_POST["db_password"];
    
    try{
        $connection = mysqli_connect($db_server, $db_user, $db_password, $db_name);
        if(!$connection){
            throw new Exception(mysqli_connect_error());
        }
        $defines = "<?php\n"
                . "define('_DB_SERVER_', '$db_server');\n"
                . "define('_DB_NAME_', '$db_name');\n"
                . "define('_DB_USER_', '$db_user');\n"
                . "define('_DB_PASSWORD_', '$db_password');\n";
        
        $config_file = fopen("../config.php","w");
        
        if (!is_writable("../config.php")){
            throw new Exception ("No se puede crear/modificar archivo config.php.  Revise los permisos");
        }
        fwrite($config_file, $defines);
        
        include("../config.php");
        
        $installer = new Installer();
        $installer->install();
        
        $view = "install_OK.phtml";
                
    }catch (Exception $e){
        $install_error = "Se ha producido un error al instalar: <br>" . $e->getMessage();
    }
}



include ("../view/layout.phtml");
