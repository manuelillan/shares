<?php
include "../load.php";
$url = $_POST["url"];
$results = array();
$error_msg = FALSE;
if($url){
    $urlenc = urlencode($url);
    $platforms = \Shares\model\Platform::getAll();
    foreach($platforms as $platform){
        $data = Shares\model\Data::get($urlenc, $platform->id);
        if (!$data){
            if($platform->connection_options){
                $options = json_decode($platform->connection_options);
                if($options->endpoint){
                    $ch = curl_init();
                    $optArray = array(
                        CURLOPT_URL => str_replace("{{url}}", $urlenc, $options->endpoint),
                        CURLOPT_RETURNTRANSFER => true
                    );
                    curl_setopt_array($ch, $optArray);
                    $data_retrieved = curl_exec($ch);
                    curl_close($ch);
                    $data = new Shares\model\Data();
                    $data->url = $urlenc;
                    $data->platform_id = $platform->id;
                    $data->created_at = date("Y-m-d H:i:s");
                    $shares = 0;
                    switch ($options->response_format){
                        case "custom":
                            $shares = preg_replace($options->response_regex_pattern,$options->response_regex_replace, $data_retrieved);
                            break;
                        case "json":
                            try{
                                $data_json = json_decode($data_retrieved);
                                $indexes = explode(".",$options->response_value);
                                $value = $data_json;
                                foreach($indexes as $index){
                                    if(property_exists($value, $index)){
                                        $value = $value->{$index};
                                    }
                                }
                                $shares = $value;
                            }catch (\Exception $e){
                                $shares = 0;
                            }
                            break;
                    }
                    $data->shares = is_numeric($shares) ? $shares : 0;
                    $data->save();
                }
            }
        }
        $results[] = array(
            "platform"  => $platform,
            "data"      => $data
        );
    }
    
}else{
    $error_msg = "No se ha indicado una URL válida";
}

include("../view/search_results.phtml");

