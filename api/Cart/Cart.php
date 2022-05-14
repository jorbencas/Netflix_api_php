<?php
    function Cart($api) {
        $GET = $api->getGET();
        switch ($api->getAction()) {
            case 'get_cart': $result = get_cart($api); break;
            case 'insert_line': $result = insert_line($api); break;
            case 'remove_line': $result = remove_line($api); break;
            case 'query': $result = function_exists($GET['aq']) ? $GET['aq']($api) : $api->response("api_Anime_resp_error_msg", 400); break;
            default: $result = $api->response("api_Anime_resp_error_msg", 404); break;
        }
        return $result;
    }

    function getnumproducts($api) {
        $db = $api->getDb();
        $POST = $api->getPOST();
        $sql = "SELECT COUNT(cl.*) AS num
        FROM cralines AS cl INNER JOIN cart AS c ON(c.id = cl.cart) 
        WHERE c.username = '{$POST['username']}";
        $valor = $db->listar($sql);
        if (isset($valor[0]->num)) {
            $valor = get_object_vars($valor[0]); 
            $res = $api->response("api_Anime_last_msg", 200, $valor['num']);
        } else {
            $res = $api->response("api_Anime_last_error_msg", 500);
        }
        return $res;
    }

    function get_cart($api) {
        $db = $api->getDb();
        $sql = "SELECT * FROM ";
        $sql = "SELECT * FROM ";
    }

    function insert_line($api) {
        $db = $api->getDb();
        $sql = "SELECT * FROM ";
        $peticion['cabecera']['coddir_envio'] = "";
        $peticion['cabecera']['codagente'] = "";
        $peticion['cabecera']['codcliente'] = "";
        $peticion['cabecera']['codpedcli'] = "";
        $peticion['cabecera']['codpago'] = "";
        $peticion['cabecera']['observaciones'] = ""; //String

        //Portes
        $peticion['cabecera']['netoportes'] = ""; //double
        $peticion['cabecera']['portesmanual'] = true; //boolean

        //Lineas
        // $peticion['lineas'][$carro[$i]['barcode']]['referencia'] = "";
        // $peticion['lineas'][$carro[$i]['barcode']]['barcode'] = "";
        // $peticion['lineas'][$carro[$i]['barcode']]['cantidad'] = ""; //int
        // $peticion['lineas'][$carro[$i]['barcode']]['observaciones'] = ""; //int
        // if (isset($peticion)) {
        //      $res = $api->response("api_Buscador_msg", 200, $result);
        // } else {
        //     $res = $api->response("api_Buscador_error_msg", 404);
        // }
    }

    function remove_line($api) {
        $db = $api->getDb();
        $sql = "SELECT * FROM ";
    }
?>