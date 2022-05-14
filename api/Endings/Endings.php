<?php
    function Endings($api) {
        $GET = $api->getGET();
        switch ($api->getAction()) {
            case 'inserteditOneending': $result = $api->isValidAdminToken() ? inserteditOneending($api) : $api->response("api_Endings_resp_error_msg", 404); break;
            case 'deleteOneending': $result = $api->isValidAdminToken() ? deleteOneending($api) : $api->response("api_Endings_resp_error_msg", 404); break;
            case 'deleteEndingsbyanime': $result = $api->isValidAdminToken() ? deleteEndingsbyanime($api) : $api->response("api_Endings_resp_error_msg", 404); break;
            case 'apione': $result = getOnending($api); break;
            case 'apiby': $result = getEndingsbyAnime($api); break;
            case 'query': $result = function_exists($GET['aq']) ? $GET['aq']($api) : $api->response("api_Endings_resp_error_msg", 404); break;
            default: $result = $api->response("api_Endings_resp_error_msg", 404);  break;
        }
        return $result;
    }

    function getEndingsbyAnime($api) {
        $GET = $api->getGET();
        $db = $api->getDb();
        $sql = "SELECT e.id, e.anime, e.nombre, e.descripcion,  
        a.siglas, e.num, a.kind
        FROM endings as e
        INNER JOIN animes AS a ON a.id = e.anime 
        WHERE e.anime = '{$GET['aa']}'";
        $res = $db->listar($sql);
        if (isset($res[0]->id)) {
            $media = $api->apiReqNode("media", array(
                'type' => 'portada',
                'id_external' => $GET['aa']
            ));
            foreach ($res as $key => $value) {
                if (count($media) > 0) {
                    $value->src = $api->handleMedia($media['type'], $media['name'], $media['extension'], $value->siglas);
                } else {
                    $value->src = $api->handleMedia("img","no","jpg");
                }
                $res[$key] = $value;
            }
            return $api->response("api_Endings_slides_msg", 200, $res);
        } else {
            return $api->response("api_Endings_slides_error_msg", 404);
        }
    }

    function getOnending($api) {
        $db = $api->getDb();
        $GET = $api->getGET();
        $sql = "SELECT e.id, e.anime, e.nombre, e.descripcion, a.siglas, e.num,
        (SELECT id FROM endings WHERE anime = e.anime AND num = ( SELECT num FROM endings WHERE id = '{$GET['ap']}' - 1) ) AS prev,
        (SELECT id FROM endings WHERE anime = e.anime AND num = ( SELECT num FROM endings WHERE id = '{$GET['ap']}' + 1) ) AS next
        FROM endings AS e
        INNER JOIN animes AS a ON(a.id = e.anime)
        WHERE e.id = '{$GET['ap']}'";
        $ending = $db->obtener_uno($sql);
        if (isset($ending->id)) {
            $translations = $api->gettranslations([
                array("kind" => "titulo", "id_external" => $ending->anime),
            ]);
            if ( count($translations) > 0 ) {
                $kind = $translations['kind'];
                $k = $kind == "titulo" ? 'anime_titulo' : $kind;
                $ending->$k = $translations['translation'];
            }
            $media = $api->getMedias([
                array( 'type' => 'endings', 'id_external' => $$ending->id),
                array( 'type' => 'portada', 'id_external' => $ending->anime)
            ]); 
            if (count($media) > 0) {
                foreach ($media as $media) {
                    $k = $media['type'] == 'portada' ? 'img' : 'src';
                    $ending->$k = $api->handleMedia($media['type'], $media['name'], $media['extension'], $ending->siglas);
                }
            } else {
                $ending->img = $api->handleMedia("img","no","jpg");
                $ending->src = $api->handleMedia("img","no","jpg");
            }
            return $api->response("api_Endings_One_msg", 200, $ending);
        } else {
            return $api->response("api_Endings_One_error_msg", 404);
        }
    }

    function inserteditOneending($api) {
        $db = $api->getDb();
        $POST = $api->getPOST();
        $sql = "SELECT * FROM endings WHERE id = '{$POST['id']}'";
        $valor = $db->listar($sql);
        if (!isset($valor[0]->id)) {
            $sql = "INSERT INTO endings( id, nombre, 
            descripcion, anime, num) VALUES('{$POST['id']}', 
            '{$POST['nombre']}', '{$POST['descripcion']}', '{$POST['anime']}', 
            '{$POST['num']}' )";
            $inserted = $db->ejecutar($sql);
            if ($inserted) {
                $api->writeFile(array("kind" => "Ending insertado" , "message" => "Se ha insertado el ending {$POST['nombre']}"),"log");
                return $api->response("api_Endings_insert_msg", 200, $sql);
            } else {
                return $api->response("api_Endings_insert_error_msg", 404);
            }
        } else {
            $sql = "UPDATE endings set  
            nombre = '{$POST['nombre']}', descripcion = '{$POST['descripcion']}', 
            anime = '{$POST['anime']}', num = '{$POST['num']}'  WHERE id = '{$POST['id']}'";
            $updated = $db->ejecutar($sql);
            if ($updated) {
                $api->writeFile(array("kind" => "Ending actualizado" , "message" => "Se ha actualizado el ending {$POST['nombre']}"), "log");
                return $api->response("api_Endings_update_msg", 200, $sql);
            } else {
                return $api->response("api_Endings_update_error_msg", 404);
            }
        }       
    };

    function deleteOneending($api) {
        $db = $api->getDb();
        $POST = $api->getPOST();
        $data = $api->apiReq("Endings&ap={$POST['id']}");
        if ($data['status']['code'] == 200) {
            $endings = $data['data']; 
            $endings['action'] = 'deleteby';
            $endings['type'] = "endings"; 
            $endings['kind'] = 'ending';
            $data = $api->apiReq("Upload", $endings);
            if ($data['status']['code'] == 200) {
                $sql = "DELETE FROM endings WHERE id = '{$POST['id']}'";
                $deleted = $db->ejecutar($sql);
                if ($deleted) {
                    return $api->response("api_Endings_delete_msg", 200, $data['data']);
                } else {
                    return $api->response("api_Endings_delete_error_msg", 404); 
                }
            } else {
                return $api->response("api_Endings_delete_error_msg", 404);
            }
        } else {
            return $api->response("api_Endings_delete_error_msg", 404);
        }
    };

    function deleteEndingsbyanime($api) {
        $db = $api->getDb();
        $POST = $api->getPOST();
        $data = $api->apiReq("Endings&aa={$POST['id']}");
        if ($data['status']['code'] == 200) {
            $sql = "DELETE FROM endings WHERE anime = '{$POST['id']}'";
            $deleted = $db->ejecutar($sql);
            if ($deleted) {
                foreach ($data['data'] as $key => $value) {
                    $id = $value['id'];
                    $params['kind'] = "epititulo";
                    $params['action'] = "deletetranslation";
                    $params['id_external'] = $id;
                    $api->apiReq("Langs", $params);
                    $sql = "DELETE FROM atributtes WHERE endings = '$id' AND anime = '{$POST['id']}'";
                    $db->ejecutar($sql);
                    $params['id'] = $id;
                    $params['action'] = "deletetelementbyending";
                    $api->apiReq("History", $params); 
                    $params['id'] = $id;
                    $params['kind'] = 'episodes';
                    $params['action'] = "deletesearch";
                    $api->apiReq("Filters", $params); 
                }
                return $api->response("api_Endings_delete_msg", 200, "ok");
            } else {
                return $api->response("api_Endings_delete_error_msg", 404); 
            }
        } else {
            return $api->response("api_Endings_delete_msg", 200);
        }
    }
?>