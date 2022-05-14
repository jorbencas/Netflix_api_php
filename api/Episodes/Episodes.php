<?php 
    function Episodes($api) {
        $GET = $api->getGET();
        switch ($api->getAction()) {
            case 'inserteditOneepisode': $result = $api->isValidAdminToken() ? inserteditOneepisode($api) : $api->response("api_Episodes_resp_error_msg", 404); break;
            case 'deleteOneepisode': $result = $api->isValidAdminToken() ? deleteOneepisode($api) : $api->response("api_Episodes_resp_error_msg", 404); break;
            case 'deleteEpisodesbyanime':  $result = $api->isValidAdminToken() ? deleteEpisodesbyanime($api) : $api->response("api_Episodes_resp_error_msg", 404); break;
            case 'apione': $result = getOne($api); break;
            case 'apislide': $result = getLastepisodes($api); break;
            case 'apiby': $result = getbyAnime($api); break;
            case 'query': $result = function_exists($GET['aq']) ? $GET['aq']($api) : $api->response("api_Episodes_resp_error_msg", 404); break;
            default: $result = $api->response("api_Episodes_resp_error_msg", 404);  break;
        }
        return $result;
    }

    function getOne($api) {
        $GET = $api->getGET();
        $db = $api->getDb();
        if (isset($GET['kind'])) {
            $select = "s.id AS id_external, (SELECT ep.id FROM episodes AS ep INNER JOIN seasons AS sa ON(ep.id BETWEEN sa.epistart AND sa.epiend AND sa.id = s.id) WHERE ep.id = '{$GET['ap']}' + 1 ) AS next,
            (SELECT ep.id FROM episodes AS ep INNER JOIN seasons AS sa ON(ep.id BETWEEN sa.epistart AND sa.epiend AND sa.id = s.id) WHERE ep.id = '{$GET['ap']}' - 1 ) AS prev";
            $from = "INNER JOIN seasons AS s ON (s.anime = e.anime AND e.id BETWEEN s.epistart AND s.epiend)";
        } else {
            $select = "(SELECT id FROM episodes WHERE anime = a.id AND num = ( SELECT num FROM episodes WHERE id = '{$GET['ap']}' + 1 ) ) AS next,
            (SELECT id FROM episodes WHERE anime = a.id AND num = ( SELECT num FROM episodes WHERE id = '{$GET['ap']}' - 1 ) ) AS prev";
            $from = "";
        }
        $sql = "SELECT e.id, e.anime, e.num, a.siglas, $select
        FROM animes AS a INNER JOIN episodes as e ON a.id = e.anime
        $from WHERE e.id = '{$GET['ap']}' ";
        $episode = $db->obtener_uno($sql);
        if (isset($episode->id)) {
            $trans = [
                array("kind" => "epititulo", "id_external" => $episode->id),
                array("kind" => "titulo", "id_external" => $episode->anime)  
            ];
            if (isset($GET['kind'])) {
                array_push($trans, array("kind" => "seasions", "id_external" => $episode->id_external));
            }

            $translations = $api->gettranslations($trans);
            if ( count($translations) > 0 ) {
                foreach ($translations as $lang) {
                    $kind = $lang['kind'];
                    $k = $kind == "titulo" ? 'anime_titulo' : $kind;
                    $episode->$k = $lang['translation'];
                }
            }
            $media = $api->getMedias([
                array( 'type' => 'episodes', 'id_external' => $episode->id),
                array( 'type' => 'portada', 'id_external' => $episode->anime)
            ]); 
            if (count($media) > 0) {
                foreach ($media as $media) {
                    $k = $media['type'] == 'portada' ? 'img' : 'src';
                    $episode->$k = $api->handleMedia($media['type'], $media['name'], $media['extension'], $episode->siglas);
                }
            } else {
                $episode->img = $api->handleMedia("img","no","jpg");
                $episode->src = $api->handleMedia("img","no","jpg");
            }
            return $api->response("api_Episodes_One_msg", 200, $episode);
        } else {
            return $api->response("api_Episodes_One_error_msg", 404); 
        }
    };

    function getbyAnime($api) {
        $GET = $api->getGET();
        $db = $api->getDb();
        if (isset($GET['kind'])) {
            if (isset($GET['seasion'])) {
                $where = "AND s.id = {$GET['seasion']}";
            } else {
                $where = "AND s.epistart = (SELECT id FROM episodes WHERE anime = {$GET['aa']} ORDER BY num ASC LIMIT 1)";
            }
            $from = "INNER JOIN seasons AS s ON (s.anime = e.anime AND e.id BETWEEN s.epistart AND s.epiend)";
        } else {
            $where = "";
            $from = "";
        }
        $sql = "SELECT e.id, a.siglas, e.anime, e.num, a.kind
        FROM animes AS a 
        INNER JOIN episodes as e ON a.id = e.anime
        $from
        WHERE e.anime = '{$GET['aa']}' $where";
        $res = $db->listar($sql);
        if (isset($res[0]->id)) {
            $media = $api->apiReqNode("media", array(
                'type' => 'portada',
                'id_external' => $GET['aa']
            ));
            foreach ($res as $value) {
                $translations = $api->gettranslations([
                    array("kind" => "epititulo", "id_external" => $value->id)
                ]);
                if (count($translations) > 0 ) {
                    $kind = $translations['kind'];
                    $value->$kind = $translations['translation'];
                }
                if (count($media) > 0) {
                    $value->src = $api->handleMedia($media['type'], $media['name'], $media['extension'], $value->siglas);
                } else {
                    $value->src = $api->handleMedia("img","no","jpg");
                }
            }
            return $api->response("api_Episodes_slides_msg", 200, $res);
        } else {
            return $api->response("api_Episodes_slides_error_msg", 404); 
        }
    }

    function getLastepisodes($api) {
        $GET = $api->getGET();
        $db = $api->getDb();
        $limit = explode("_",$GET['as']);
        $sql = "SELECT DISTINCT e.id, a.kind, e.created, a.siglas, e.anime
        FROM animes AS a 
        INNER JOIN episodes as e ON a.id = e.anime
        ORDER BY e.created DESC OFFSET $limit[0] LIMIT $limit[1]";
        $res = $db->listar($sql);
        if (isset($res[0]->id)) {
            foreach ($res as $value) {
                $translations = $api->gettranslations([
                    array("kind" => "epititulo", "id_external" => $value->id),
                    array("kind" => "titulo", "id_external" => $value->anime)
                ]);
                if (count($translations) > 0) {
                    foreach ($translations as $lang) {
                        $kind = $lang['kind'];
                        $k = $kind == "titulo" ? 'anime_titulo' : $kind;
                        $value->$k = $lang['translation'];
                    }
                }

                $media = $api->apiReqNode("media",  array(
                    'type' => 'banner',
                    'id_external' => $value->anime
                ));
                if (count($media) > 0) {
                    $value->src = $api->handleMedia($media['type'], $media['name'], $media['extension'], $value->siglas);
                } else {
                    $value->src = $api->handleMedia("img","no","jpg");
                }

            }
            return $api->response("api_Episodes_slides_msg", 200, $res);
        } else {
            return $api->response("api_Episodes_slides_error_msg", 404); 
        }
    }

    function getidrand($api) {
        $db = $api->getDb();
        $sql = "SELECT e.id, a.kind FROM episodes AS e INNER JOIN animes AS a ON(e.anime = a.id) ORDER BY random() LIMIT 1;";
        $valor = $db->obtener_uno($sql);
        if (isset($valor)) {
            return $api->response("api_Episodes_last_msg", 200, $valor);
        } else {
            return $api->response("api_Episodes_last_error_msg", 404); 
        }

    }
    
    function inserteditOneepisode($api) {
        $POST = $api->getPOST();
        $db = $api->getDb();
        $sql = "SELECT * FROM episodes WHERE id = '{$POST['id']}'";
        $valor = $db->listar($sql);
        if (!isset($valor[0]->id)) {
            $sql = "INSERT INTO episodes(id,   
            idiomas,  anime,  views, downloads, num) VALUE('{$POST['id']}',  
            '{$POST['idiomas']}', '{$POST['anime']}',  
            '{$POST['views']}', '{$POST['downloads']}', '{$POST['num']}')";
            $inserted = $db->ejecutar($sql);
            if ($inserted) {
                $api->writeFile(array("kind" => "Episodio insertado", "message" => "Se ha insertado el episodio {$POST['titulo_es']}"),"log");
                $message = "api_Episodes_insert_msg";
                $checked = true;
            } else {
                $message = "api_Episodes_insert_error_msg"; 
                $checked = false;
            }
        } else {
            $sql = "UPDATE episodes SET idiomas = '{$POST['idiomas']}', 
            anime = '{$POST['anime']}', 
            views = '{$POST['views']}', downloads = '{$POST['downloads']}', 
            num = '{$POST['num']}' WHERE id = '{$POST['id']}'";
            $updated = $db->ejecutar($sql);
            if ($updated) {
                $api->writeFile(array("kind" => "Episodio actualizado", "message" => "Se ha actualizado el episodio {$POST['titulo_es']}"),"log");
                $message = "api_Episodes_update_msg";
                $checked = true;
            } else {
                $message = "api_Episodes_update_error_msg"; 
                $checked = false;
            }
        }

        if ($checked) {
            $sql = "SELECT id FROM animes WHERE id = '{$POST['id']}'";
            $valor = $db->listar($sql);

            $params = array("action" => "gettranslations", "translations" => []);
            if (!empty($POST['titulo_es'])) $field = "titulo_es";
            if (!empty($POST['titulo_en'])) $field = "titulo_en";
            if (!empty($POST['titulo_va'])) $field = "titulo_va";
            if (!empty($POST['titulo_ca'])) $field = "titulo_ca";
            $elements = explode("_",$field);
            $params["translations"][0][0]['kind'] = "epititulo";
            $params["code"] = end($elements);
            $params["translations"][0][1]['id_external'] = $valor[0]->id;

            $data = $api->apiReq("Langs",$params);
            if ($data['status']['code'] == 200) {
                $checked = true;
            } else {
                $checked = false;
            }
        }
        if ($checked) {
            $res = $api->response($message, 200, "OK");
        } else {
            $res = $api->response($message, 404); 
        }
        return $res;
    };
    
    function deleteOneepisode($api) {
        $POST = $api->getPOST();
        $db = $api->getDb();
        $data = $api->apiReq("Episodes&ap={$POST['id']}");
        if ($data['status']['code'] == 200) {
            $episodes = $data['data']; 
            $episodes['action'] = 'deleteby';
            $episodes['type'] = "episodes"; 
            $episodes['kind'] = 'episode';
            $data = $api->apiReq("Upload", $episodes);
            if ($data['status']['code'] == 200) {
                $sql = "DELETE FROM episodes WHERE id = '{$POST['id']}'";
                $deleted = $db->ejecutar($sql);
                if ($deleted) {
                    $params['action'] = "removecollectionbyepisode";
                    $api->apiReq("Collections", $params);
                    //selecionar elemento para despues borrar
                    $params['kind'] = "epititulo";
                    $params['action'] = "deletetranslation";
                    $params['id_external'] = $POST['id'];
                    $api->apiReq("Langs", $params);
                    return $api->response("api_Episodes_delete_msg", 200, $data['data']);
                } else {
                    return $api->response("api_Episodes_delete_error_msg", 404); 
                }
            } else {
                return $api->response("api_Episodes_delete_error_msg", 404); 
            }
        } else {
            return $api->response("api_Episodes_update_error_msg", 404); 
        }
    };

    function deleteEpisodesbyanime($api) {
        $POST = $api->getPOST();
        $db = $api->getDb();
        $data = $api->apiReq("Episodes&aa={$POST['id']}");
        if ($data['status']['code'] == 200) {
            $sql = "DELETE FROM episodes WHERE anime = '{$POST['id']}'";
            $deleted = $db->ejecutar($sql);
            if ($deleted) {
                foreach ($data['data'] as $key => $value) {
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
                return $api->response("api_Episodes_delete_msg", 200, "ok");
            } else {
                return $api->response("api_Episodes_delete_error_msg", 404); 
            }
        } else {
            return $api->response("api_Episodes_delete_msg", 200, $POST);
        }
    }
?>
