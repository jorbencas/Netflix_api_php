<?php
    function Footer($api) {
        $GET = $api->getGET();
        switch ($api->getAction()) {
            case 'query': $result = function_exists($GET['aq']) ? $GET['aq']($api) : $api->response("api_Anime_resp_error_msg", 404); break;
            default: $result = $api->response("api_Anime_resp_error_msg", 404); break;
        }
        return $result;
    };

    function getviews($api) {
        $db = $api->getDb();
        $sql = "SELECT visiteds FROM metadata WHERE id = 1";
        $res = $db->obtener_una_columna($sql);
        if (isset($res) && is_numeric($res)) {
            $result = $api->response("api_Login_msg", 200, $res);
        } else {
            $result = $api->response("api_Login_msg", 404);
        }
        return $result;
    }

    function setviews($api) {
        $db = $api->getDb();
        $sql = "SELECT visiteds FROM metadata WHERE id = 1";
        $res = $db->obtener_una_columna($sql);
        if (isset($res) && is_numeric($res)) {
            $sql = "UPDATE metadata SET visiteds = ($res  + 1) WHERE id = 1";
        } else {
            $sql = "INSERT INTO metadata(visiteds, num_users) VALUES(0,0);";
        }
        $db->ejecutar($sql);
        $sql = "SELECT visiteds FROM metadata WHERE id = 1";
        $res = $db->obtener_una_columna($sql);
        if (isset($res) && is_numeric($res)) {
            $result = $api->response("api_Login_msg", 200, $res);
        } else {
            $result = $api->response("api_Login_error_msg", 404);
        }
        return $result;
    }
?>