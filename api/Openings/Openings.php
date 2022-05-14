<?php 
    function Openings($api) {
        $GET = $api->getGET();
        switch ($api->getAction()) {
            case 'inserteditOneopening': $result = $api->isValidAdminToken() ? inserteditOneopening($api) : $api->response("api_Openings_resp_error_msg", 404); break;
            case 'deleteOneopening': $result = $api->isValidAdminToken() ? deleteOneopening($api) : $api->response("api_Openings_resp_error_msg", 404); break;
            case 'deleteOpeningsbyanime': $result = $api->isValidAdminToken() ? deleteOpeningsbyanime($api) : $api->response("api_Openings_resp_error_msg", 404); break;
            case 'apione': $result = getOneopening($api); break;
            case 'apiby': $result = getOpeningsbyAnimes($api); break;
            case 'query': $result = function_exists($GET['aq']) ? $GET['aq']($api) : $api->response("api_Openings_resp_error_msg", 404);  break;
            default: $result = $api->response("api_Openings_resp_error_msg", 404); break;
        }
        return $result;
    }
    
    function getOpeningsbyAnimes($api) {
        $db = $api->getDb();
        $GET = $api->getGET();
        $sql = "SELECT o.id, o.nombre, o.descripcion, o.anime, a.siglas, o.num, a.kind
        FROM animes AS a
        INNER JOIN openings as o ON a.id = o.anime 
        WHERE o.anime = '{$GET['aa']}'";
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
            return $api->response("api_Openings_slides_msg", 200, $res);
        } else {
            return $api->response("api_Openings_slides_error_msg", 404); 
        }
    }

    function getOneopening($api) {
        $db = $api->getDb();
        $GET = $api->getGET();
        $sql = "SELECT o.id, o.anime, o.nombre, o.descripcion, a.siglas, o.num,
        (SELECT id FROM openings WHERE anime = o.anime AND num = ( SELECT num FROM openings WHERE id = '{$GET['ap']}' + 1 ) ) AS next,
        (SELECT id FROM openings WHERE anime = o.anime AND num = ( SELECT num FROM openings WHERE id = '{$GET['ap']}' - 1 ) ) AS prev
        FROM openings AS o
        INNER JOIN animes AS a ON(a.id = o.anime)
        WHERE o.id = '{$GET['ap']}'";
        $opening = $db->obtener_uno($sql);
        if (isset($opening->id)) {
            $translations = $api->gettranslations([
                array("kind" => "titulo", "id_external" => $opening->anime),
            ]);
            if (count($translations) > 0 ) {
                $kind = $translations['kind'];
                $k = $kind == "titulo" ? 'anime_titulo' : $kind;
                $opening->$k = $translations['translation'];
            }

            $media = $api->getMedias([
                array( 'type' => 'openings', 'id_external' => $opening->id),
                array( 'type' => 'portada', 'id_external' => $opening->anime)
            ]); 
            if (count($media) > 0) {
                foreach ($media as $media) {
                    $k = $media['type'] == 'portada' ? 'img' : 'src';
                    $opening->$k = $api->handleMedia($media['type'], $media['name'], $media['extension'], $opening->siglas);
                }
            } else {
                $opening->img = $api->handleMedia("img","no","jpg");
                $opening->src = $api->handleMedia("img","no","jpg");
            }
            return $api->response("api_Openings_One_msg", 200,$opening);
        } else {
            return $api->response("api_Openings_One_error_msg", 404); 
        }
    }

    function inserteditOneopening($api) {
        $db = $api->getDb();
        $POST = $api->getPOST();
        $sql = "SELECT * FROM openings WHERE id = '{$POST['id']}'";
        $valor = $db->listar($sql);
        if (!isset($valor[0]->id)) {
            $sql = "INSERT INTO openings( id, nombre, 
            descripcion, anime, num) VALUES('{$POST['id']}', 
            '{$POST['nombre']}', '{$POST['descripcion']}', '{$POST['anime']}', 
            '{$POST['num']}')";
            $inserted = $db->ejecutar($sql);
            if ($inserted) {
                $api->writeFile(array("kind" => "Opening insertado", "message" => "Se ha insertado el opening {$POST['nombre']}"),"log");
                return $api->response("api_Openings_insert_msg", 200, $sql);
            } else {
                return $api->response("api_Openings_insert_error_msg", 404); 
            }
        } else {
            $sql = "UPDATE openings set  
            nombre = '{$POST['nombre']}',  descripcion = '{$POST['descripcion']}', 
            anime = '{$POST['anime']}', num = '{$POST['num']}'  WHERE id = '{$POST['id']}'";
            $updated = $db->ejecutar($sql);
            if ($updated) {
                $api->writeFile(array("kind" => "Opening actualizado", "message" => "Se ha actualizado el opening {$POST['nombre']}"),"log");
                return $api->response("api_Openings_update_msg", 200, $sql);
            } else {
                return $api->response("api_Openings_update_error_msg", 404); 
            }
        }
    };
    
    function deleteOneopening($api) {
        $db = $api->getDb();
        $POST = $api->getPOST();
        $data = $api->apiReq("Openings&ap={$POST['id']}");
        if ($data['status']['code'] == 200) {
            $openings = $data['data']; 
            $openings['action'] = 'deleteby';
            $openings['type'] = "openings"; 
            $openings['kind'] = 'opening';
            $data = $api->apiReq("Upload", $openings);            
            if ($data['status']['code'] == 200) {
                $sql = "DELETE FROM openings WHERE id = '{$POST['id']}'";
                $deleted = $db->ejecutar($sql);
                if ($deleted) {
                    $ok = $data['data'];
                    return $api->response("api_Openings_delete_msg", 200, $ok);
                } else {
                    return $api->response("api_Openings_delete_error_msg", 404); 
                }
            } else {
                return $api->response("api_Openings_delete_error_msg", 404); 
            }
        } else {
            return $api->response("api_Openings_delete_error_msg", 404); 
        }
    };

    function deleteOpeningsbyanime($api) {
        $db = $api->getDb();
        $POST = $api->getPOST();
        $data = $api->apiReq("Openings&aa={$POST['id']}");
        if ($data['status']['code'] == 200) {
            $sql = "DELETE FROM openings WHERE anime = '{$POST['id']}'";
            $deleted = $db->ejecutar($sql);
            if ($deleted) {
                foreach ($data['data'] as $value) {
                    $id = $value['id'];
                    $params['kind'] = "epititulo";
                    $params['action'] = "deletetranslation";
                    $params['id_external'] = $id;
                    $api->apiReq("Langs", $params);
                    $sql = "DELETE FROM atributtes WHERE episodes = '$id' AND anime = '{$POST['id']}'";
                    $db->ejecutar($sql);
                    $params['id'] = $id;
                    $params['action'] = "deletetelementbyepisode";
                    $api->apiReq("History", $params); 
                    $params['id'] = $id;
                    $params['kind'] = 'episodes';
                    $params['action'] = "deletesearch";
                    $api->apiReq("Filters", $params); 
                }
                return $api->response("api_Openings_delete_msg", 200, 'ok');
            } else {
                return $api->response("api_Episodes_delete_error_msg", 404); 
            }
        } else {
            return $api->response("api_Openings_delete_msg", 200);
        }
    }
?>
