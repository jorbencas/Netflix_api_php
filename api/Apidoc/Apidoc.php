<?php
    function Apidoc($api) {
        $POST = $api->getPOST();
        switch ($api->getAction()) {
            case 'scan': $result = scan($api); break;
            case 'Admin': $result = function_exists($POST['func']) ? $POST['func']($api) : $api->response("api_getcalls_error_msg", 404); break;
            case 'Apidoc': $result = function_exists($POST['func']) ? $POST['func']($api) : $api->response("api_getcalls_error_msg", 404); break;
            case 'Backup': $result = function_exists($POST['func']) ? $POST['func']($api) : $api->response("api_getcalls_error_msg", 404); break;
            case 'Showerrors': $result = function_exists($POST['func']) ? $POST['func']($api) : $api->response("api_getcalls_error_msg", 404); break;
            case 'Filesystem': $result = function_exists($POST['func']) ? $POST['func']($api) : $api->response("api_getcalls_error_msg", 404); break;
            case 'Anime': $result = function_exists($POST['func']) ? $POST['func']($api) : $api->response("api_getcalls_error_msg", 404); break;
            case 'Footer': $result = function_exists($POST['func']) ? $POST['func']($api) : $api->response("api_getcalls_error_msg", 404); break;
            case 'Auth': $result = function_exists($POST['func']) ? $POST['func']($api) : $api->response("api_getcalls_error_msg", 404); break;
            case 'Cart': $result = function_exists($POST['func']) ? $POST['func']($api) : $api->response("api_getcalls_error_msg", 404); break;
            case 'Chat': $result = function_exists($POST['func']) ? $POST['func']($api) : $api->response("api_getcalls_error_msg", 404); break;
            case 'Collections': $result = function_exists($POST['func']) ? $POST['func']($api) : $api->response("api_getcalls_error_msg", 404); break;
            case 'Comments': $result = function_exists($POST['func']) ? $POST['func']($api) : $api->response("api_getcalls_error_msg", 404); break;
            case 'Config': $result = function_exists($POST['func']) ? $POST['func']($api) : $api->response("api_getcalls_error_msg", 404); break;
            case 'Endings': $result = function_exists($POST['func']) ? $POST['func']($api) : $api->response("api_getcalls_error_msg", 404); break;
            case 'Episodes': $result = function_exists($POST['func']) ? $POST['func']($api) : $api->response("api_getcalls_error_msg", 404); break;
            case 'Events': $result = function_exists($POST['func']) ? $POST['func']($api) : $api->response("api_getcalls_error_msg", 404); break;
            case 'Filters': $result = function_exists($POST['func']) ? $POST['func']($api) : $api->response("api_getcalls_error_msg", 404); break;
            case 'History': $result = function_exists($POST['func']) ? $POST['func']($api) : $api->response("api_getcalls_error_msg", 404); break; 
            case 'Openings': $result = function_exists($POST['func']) ? $POST['func']($api) : $api->response("api_getcalls_error_msg", 404); break; 
            case 'Personage': $result = function_exists($POST['func']) ? $POST['func']($api) : $api->response("api_getcalls_error_msg", 404); break; 
            case 'Profiles': $result = function_exists($POST['func']) ? $POST['func']($api) : $api->response("api_getcalls_error_msg", 404); break; 
            case 'Upload': $result = function_exists($POST['func']) ? $POST['func']($api) : $api->response("api_getcalls_error_msg", 404); break; 
            case 'User': $result = function_exists($POST['func']) ? $POST['func']($api) : $api->response("api_getcalls_error_msg", 404); break; 
            /* case 'value': $result = function_exists($POST['func']) ? $POST['func']($api) : $api->response("api_getcalls_error_msg", 404); break; 
            case 'value': $result = function_exists($POST['func']) ? $POST['func']($api) : $api->response("api_getcalls_error_msg", 404); break; 
            case 'value': $result = function_exists($POST['func']) ? $POST['func']($api) : $api->response("api_getcalls_error_msg", 404); break; 
            case 'value': $result = function_exists($POST['func']) ? $POST['func']($api) : $api->response("api_getcalls_error_msg", 404); break;         */
            default: $result = $api->response("api_getcalls_error_msg", 404); break;
        }
        return $result;
    };

    function scan($api){
        $apis = array();
        $scanedFiles = $api->scanFolders("api",true,true);
        if (sizeof($scanedFiles) > 0) {
            foreach ($scanedFiles as $key => $value) {
                if (strstr($value,".php")) {
                    $value = str_replace("_api.php","",$value);
                    array_push($apis,$value);
                }
            }
            $result = $api->response("ok", 200, $apis);
        } else {
            $result = $api->response("apidoc_getapis_errror_msg", 500);
        }
        return $result;
    }

    function Footerapi($api) {
        $db = $api->getDb();
        $result = array();
        array_push($result,(Object)array('method' => "GET", 
            'url' => "Footer&aq=getviews",
            'text' => "Se obtien las visitas de la pagina"
        ));

        array_push($result,(Object)array('method' => "GET",
            'url' => "Footer&aq=setviews",
            'text' => "Incrementa el numero de visitas de la pagina en 1"
        ));
        return $api->response("api_getcalls_error_msg", 200, $result);
    }
?>