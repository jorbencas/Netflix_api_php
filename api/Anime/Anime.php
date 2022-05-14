<?php 
    function Anime($api) {
        $GET = $api->getGET();
        switch ($api->getAction()) {  
            case 'inserteditOneanime': $result = $api->isValidAdminToken() ? inserteditOneanime($api) : $api->response("api_Anime_resp_error_msg", 404); break;
            case 'deleteOneanime': $result = $api->isValidAdminToken() ? deleteOneanime($api) : $api->response("api_Anime_resp_error_msg", 404); break;
            // case 'addfav': $result = $api->isValidAccesToken() ? addFavorite($api) : $api->response("api_Anime_resp_error_msg", 404); break;
            // case 'removefav': $result = $api->isValidAccesToken() ? removeFavorite($api): $api->response("api_Anime_resp_error_msg", 404); break;
            case 'apione': $result = getone($api, $GET); break;
            case 'apislide': $result = getslides($api, $GET); break;
            case 'query': $result = function_exists($GET['aq']) ? $GET['aq']($api, $GET) : $api->response("api_Anime_resp_error_msg", 404); break;
            default: $result = $api->response("api_Anime_resp_error_msg", 404); break;
        }
        return $result;
    };

    function getlistanime($api, $GET) {
        $db = $api->getDb();
        $where = handlefilters($api, $GET);
        $sql = "SELECT a.id, a.valorations, a.siglas, a.state,
        a.date_publication, a.date_finalization, at.favorite
        FROM animes AS a 
        LEFT JOIN atributtes AS at ON(at.anime = a.id AND at.profile = '{$GET['profile']}' ) 
        WHERE a.created IS NOT NULL $where";
        $res = $db->listar($sql);
        if (isset($res[0]->id)) {
            $translations = $api->gettranslations([
                array("kind" => "titulo", "id_external" => $res[0]->id)
            ]);
            if (count($translations) > 0) {
                foreach ($translations as $lang) {
                    $kind = $lang['kind'];
                    $res[0]->$kind = $lang['translation'];
                }
            }
            $res[0]->generes = getFiltersByCode($api, $res[0]->generes);
            $res = $api->response("api_Anime_msg", 200, $res);
        } else {
            $res = $api->response("api_Anime_error_msg", 404);
        }
        return $res;
    }

    function getslides($api, $GET) {
        $db = $api->getDb();
        $where = handlefilters($api, $GET);
        $sql = "SELECT a.id, a.kind, a.generes, a.temporada, a.siglas, a.created
        FROM animes AS a WHERE a.created IS NOT NULL $where";
        $anime = $db->listar($sql);
        if (isset($anime[0]->id)) {
            foreach ($anime as $value) {
                $translations = $api->gettranslations([
                    array("kind" => "titulo", "id_external" => $value->id),
                    array("kind" => "sinopsis", "id_external" => $value->id)
                ]);
                if (count($translations) > 0 ) {
                    foreach ($translations as $lang) {
                        $kind = $lang['kind'];
                        $value->$kind = $lang['translation'];
                    }
                }
                
                $value->generes = getFiltersByCode($api, $value->generes);
                $media = $api->apiReqNode("media", array(
                    'type' => 'portada',
                    'id_external' => $value->id
                ));
                if (count($media) > 0) {
                    $value->src = $api->handleMedia($media['type'], $media['name'], $media['extension'], $value->siglas);
                } else {
                    $value->src = $api->handleMedia("img","no","jpg");
                }
            }
            $res = $api->response("api_Anime_slides_msg", 200, $anime);
        } else {
            $res = $api->response("api_Anime_slides_error_msg", 404);
        }
        return $res;
    };

    function getone($api, $GET) {
        $db = $api->getDb();
        if (isset($GET['kind']) && $GET['kind'] == 'serie') {
            if (isset($GET['seasion'])) {
                $whereSubSelect = "s.id = {$GET['seasion']}";
            } else {
                $whereSubSelect = "s.epistart = (SELECT id FROM episodes WHERE anime = {$GET['ap']} ORDER BY num ASC LIMIT 1)";
            }
            $select = "(SELECT count(epi.*) 
            FROM episodes AS epi INNER JOIN seasons AS s 
            ON (s.anime = epi.anime AND epi.id BETWEEN s.epistart AND s.epiend) 
            WHERE epi.anime = '{$GET['ap']}' AND $whereSubSelect) AS num_epis";
        } else {
            $select = "(SELECT count(*) FROM episodes WHERE anime = '{$GET['ap']}') AS num_epis";
        }
        $sql = "SELECT a.id, a.kind, a.valorations, a.generes, a.temporada, a.siglas,
        a.state, a.idiomas, a.date_publication, a.date_finalization, a.created,
        (SELECT count(*) FROM openings WHERE anime = '{$GET['ap']}') AS num_opes,
        (SELECT count(*) FROM endings WHERE anime = '{$GET['ap']}') AS num_ends,
        $select
        FROM animes AS a WHERE a.id = '{$GET['ap']}'";
        $res = $db->obtener_uno($sql);
        if (isset($res->id)) {
            $translations = $api->gettranslations([
                array("kind" => "titulo", "id_external" => $res->id),
                array("kind" => "sinopsis", "id_external" => $res->id),
            ]);
            if ( count($translations) > 0 ) {
                foreach ($translations as $lang) {
                    $kind = $lang['kind'];
                    $res->$kind = $lang['translation'];
                }
            }
            $res->generes = getFiltersByCode($api, $res->generes);
            $media = $api->getMedias([
                array( 'type' => 'banner', 'id_external' => $res->id),
                array( 'type' => 'portada', 'id_external' => $res->id)
            ]); 
            if (count($media) > 0) {
                foreach ($media as $media) {
                    $k = $media['type'] == 'portada' ? 'src' : $media['type'];
                    $res->$k = $api->handleMedia($media['type'], $media['name'], $media['extension'], $res->siglas);
                }
            } else {
                $res->banner = $api->handleMedia("img","no","jpg");
                $res->src = $api->handleMedia("img","no","jpg");
            }

            if (isset($GET['kind']) && $GET['kind'] == 'serie') {
                $sql = "SELECT s.id
                FROM seasons AS s
                WHERE s.anime = '{$GET['ap']}'";
                $res->seasions = $db->listar($sql);
                foreach ($res->seasions as $value) {
                    $translations = $api->gettranslations([
                        array("kind" => "seasions", "id_external" => $value->id),
                    ]);
                    if ( count($translations) > 0 ) {
                        // $kind = $translations['kind'];
                        $value->title = $translations['translation'];
                    }
                }
            }
            $res = $api->response("api_Anime_One_msg", 200, $res);
        } else {
            $res = $api->response("api_Anime_One_error_msg", 404);
        }
        return $res;
    };

    function getFiltersByCode($api, $g){
        if (isset($g)) {
            $generes = explode(",",$g);
            if (sizeof($generes) > 0) {
                $gen = array();
                foreach ($generes as $code) {
                    $sql = "SELECT id, code FROM filters WHERE code = '{$code}'";
                    $filter = $api->getDb()->obtener_uno($sql);
                    $trans = $api->gettranslations([
                        array("kind" => "filters", "id_external" => $filter->id)
                    ]);
                    if (count($trans) > 0) {
                        array_push($gen,array(
                            'filter' => $filter->code, 
                            "title" => $trans['translation']
                        ));
                    }
                }
                if (sizeof($gen) > 0 ) {
                    $g = $gen;
                }
                unset($gen);
            }
        }
        return $g;
    }

    function handlefilters($api, $GET) {
        $where = "";
        if (isset($GET['f'])) {
            $f = explode("_", $GET['f']); 
            $filter = $f[1];
            switch ($f[0]) {
                case 'letters':
                    $id_externals = array();
                    $translations = $api->gettranslations([
                        array("kind" => "titulo"),
                    ]);
                    if ( count($translations) > 0 ) {
                        foreach ($translations as $lang) {
                            $titulo = $lang['translation'];
                            if ($filter == '0-9') {
                                for ($i=0; $i <= 9; $i++) { 
                                    if (strchr($titulo,$i)) {
                                        array_push($id_externals,$lang['id_external']);
                                    }
                                }
                            } else {
                                if (strchr($titulo,$filter)) {
                                    array_push($id_externals,$lang['id_external']);
                                }
                            }
                        }
                    }
                    $ids = implode(",",$id_externals);
                    $where .= "AND a.id IN($ids)";
                    break;    
                case 'generes':
                    $where .= "AND a.generes LIKE '%$filter%'";
                    break;    
                case 'years':
                    $where .= "AND a.date_publication LIKE '$filter%'";
                    break;    
                case 'languajes':
                    $where .= "AND a.idiomas LIKE '$filter'";
                    break;    
                case 'kinds':
                    if ($filter !== 'all')
                        $where .= "AND a.kind LIKE '$filter'";
                    break;
                case 'temporadas':
                    $where .= "AND a.temporada LIKE '%$filter%'";
                    break;
            }
        }

        if (isset($GET['od'])) {
            $where .= " ORDER BY a.{$GET['od']} DESC";
        } else if (isset($GET['oa'])) {
            $where .= " ORDER BY a.{$GET['oa']} ASC";
        }

        if (isset($GET['as'])) {
            $limit = explode("_",$GET['as']);
            $where .= " OFFSET $limit[0] LIMIT $limit[1]";
        }
        return $where;
    }

    function getnumanimes($api, $GET) { 
        $db = $api->getDb();
        $where = handlefilters($api, $GET);
        $sql = "SELECT count(a.id) FROM animes AS a WHERE a.created IS NOT NULL $where";
        $valor = $db->obtener_una_columna($sql);
        if (isset($valor)) {
            $res = $api->response("api_Anime_last_msg", 200, $valor);
        } else {
            $res = $api->response("api_Anime_last_error_msg", 404);
        }
        return $res;
    };

    function lastanimes($api, $GET) {
        $db = $api->getDb();
        $where = handlefilters($api, $GET);
        $sql = "SELECT a.id, a.kind, a.generes 
        FROM animes AS a 
        WHERE a.created IS NOT NULL $where";
        $animes = $db->listar($sql);
        if (count($animes) > 0) {
            foreach ($animes as $value) {
                $translations = $api->gettranslations([
                    array("kind" => "titulo", "id_external" => $value->id)
                ]);
                if ( count($translations) > 0 ) {
                    $kind = $translations['kind'];
                    $value->$kind = $translations['translation'];
                }
                $value->generes = getFiltersByCode($api, $value->generes);
            }
            $res = $api->response("api_Anime_lastanime_msg", 200, $animes);
        } else {
            $res = $api->response("api_Anime_lastanime_error_msg", 404);
        }
        return $res;
    };
    
    function inserteditOneanime($api) {
        $db = $api->getDb();
        $anime = $api->getPOST();
        $sql = "SELECT id FROM animes WHERE siglas = '{$anime['siglas']}'";
        $valor = $db->obtener_una_columna($sql);
        if (isset($valor)) {
            $date = date("Y-m-d H:i:s");
            $sql = "UPDATE animes set  
            siglas = '{$anime['siglas']}', generes = '{$anime['generes']}',  
            idiomas = '{$anime['idiomas']}', date_publication = '{$anime['date_publication']}', 
            date_finalization = '{$anime['date_finalization']}', state = '{$anime['state']}', 
            kind = '{$anime['kind']}', temporada = '{$anime['temporada']}', views = '{$anime['views']}', 
            valorations = '{$anime['valorations']}', 
            downloads ='{$anime['downloads']}', updated = '$date' WHERE id = '{$anime['id']}'";
            $updated = $db->ejecutar($sql);
            if (isset($updated)) {
                $sql = "UPDATE atributtes SET favorite = '', collection = '', 
                WHERE anime = '{$anime['id']}' AND profile = '{$anime['profile']}'";
                $api->writeFile(array("kind" => "Anime actualizado", "message" => "Se ha actualizado el anime {$anime['siglas']}"), "log");
                $message = "api_Anime_update_msg";
                $checked = true;
            } else {
                $message = "api_Anime_update_error_msg";
                $checked = false;
            }
        } else {
            $sql = "INSERT INTO animes(id, siglas, generes, idiomas, date_publication, 
            date_finalization, state, kind, temporada, valorations, 
            views, downloads) VALUES('{$anime['id']}', '{$anime['siglas']}',  
            '{$anime['generes']}',  '{$anime['idiomas']}', '{$anime['date_publication']}', 
            '{$anime['date_finalization']}', '{$anime['state']}', '{$anime['kind']}', 
            '{$anime['temporada']}', '{$anime['valorations']}', '{$anime['views']}', 
            '{$anime['downloads']}' )";
            $inserted = $db->ejecutar($sql);
            if (isset($inserted)) {
                $api->writeFile(array("kind" => "Anime insertado", "message" => "Se ha insertado el anime {$anime['siglas']}"),"log");
                $message = "api_Anime_insert_msg";
                $checked = true;
            } else {
                $message = "api_Anime_insert_error_msg";
                $checked = false;
            }
        }

        if ($checked) {
            $sql = "SELECT id FROM animes WHERE id = '{$anime['id']}' AND siglas = '{$anime['siglas']}'";
            $id = $db->obtener_una_columna($sql);

            $params = array("action" => "gettranslations", "translations" => []);
            if (!empty($anime['titulo_es'])) $field = "titulo_es";
            if (!empty($anime['titulo_en'])) $field = "titulo_en";
            if (!empty($anime['titulo_va'])) $field = "titulo_va";
            if (!empty($anime['titulo_ca'])) $field = "titulo_ca";
            $elements = explode("_",$field);
            $params["translations"][0][0]['kind'] = $elements[0];
            $params["code"] = $elements[1];
            $params["translations"][0][1]['id_external'] = $id;

            if (!empty($anime['sinopsis_es'])) $field = "sinopsis_es";
            if (!empty($anime['sinopsis_en'])) $field = "sinopsis_en";
            if (!empty($anime['sinopsis_va'])) $field = "sinopsis_va";
            if (!empty($anime['sinopsis_ca'])) $field = "sinopsis_ca";
            $elements = explode("_",$field);
            $params["translations"][1][0]['kind'] = $elements[0];
            $params["translations"][1][1]['id_external'] = $id;
            $data = $api->apiReq("Langs",$params);
            if ($data['status']['code'] == 200) {
                $checked = true;
            } else {
                $checked = false;
            }
        }
        if ($checked) {
            $res = $api->response($message, 200, $sql);
        } else {
            $res = $api->response($message, 404); 
        }
        return $res;
    };

    function deleteOneanime($api) {
        $db = $api->getDb();
        $POST = $api->getPOST();
        $data = $api->apiReq("Anime&ap={$POST['id']}");
        $anime = $data['data'];
        $anime['action'] = 'deleteby';
        $anime['type'] = 'anime';
        $anime['kind'] = 'anime';
        $data = $api->apiReq("Upload" ,$anime);
        $params["action"] = "deleteEpisodesbyanime";
        $data = $api->apiReq("Episodes", $params);
        $params["action"] = "deleteOpeningsbyanime";
        $data = $api->apiReq("Openings", $params);
        $params["action"] = "deleteEndingsbyanime";
        $data = $api->apiReq("Endings", $params);
        $params['action'] = "removecollectionbyanime";
        $data = $api->apiReq("Collections", $params);
        $sql = "DELETE FROM atributtes WHERE anime = '{$POST['id']}' AND profile = '{$POST['profile']}'";
        $db->ejecutar($sql);
        $sql = "DELETE FROM seasons WHERE anime = '{$POST['id']}'";
        $db->ejecutar($sql);
        $sql = "DELETE FROM animes WHERE id = '{$POST['id']}'";
        $db->ejecutar($sql);
        //selecionar elemento para despues borrar
        $params['action'] = "deletetranslation";
        $params['id_external'] = $POST['id'];
        $api->apiReq("Langs", $params);
        $res = $api->response("api_Anime_delete_msg", 200, "ok");
        return $res;
    };
    
    function getfav($api, $GET) {
        $db = $api->getDb();
        $POST = $api->getPOST();
        $idprofile = $POST['profile'];
        $sql = "SELECT a.id, a.kind, a.valorations, a.generes, a.temporada,
        a.siglas, a.state, a.idiomas, a.date_publication, 
        a.date_finalization, a.created, m.name, m.extension, m.type, at.favorite
        FROM animes AS a INNER JOIN media AS m on m.anime = a.id
        INNER JOIN atributtes AS at ON(a.id = at.anime)
        WHERE m.type = 'portada' AND at.profile = '$idprofile' AND at.favorite = true ";
        $res = $db->listar($sql);
        if (isset($res[0]->id)) {
            foreach ($res as $value) {
                $translations = $api->gettranslations([
                    array("kind" => "titulo", "id_external" => $value->id)
                ]);
                if ( count($translations) > 0 ) {
                    foreach ($translations as $lang) {
                        $kind = $lang['kind'];
                        $value->$kind = $lang['translation'];
                    }
                }
                $value->src = $api->handleMedia($value->type, $value->name, $value->extension, $value->siglas);
            }
            $res = $api->response("api_Anime_favorite_msg", 200, $res);
        } else {
            $res = $api->response("api_Anime_favorite_error_msg", 404);
        }
        return $res;
    };

    // function addFavorite($api) {
    //     $db = $api->getDb();
    //     $POST = $api->getPOST();
    //     $idanime = $POST['id'];
    //     $idprofile = $POST['profile'];

    //     $sql = "SELECT * FROM atributtes WHERE profile = '$idprofile' AND anime = '$idanime'";
    //     $res = $db->listar($sql);
    //     if (isset($res[0]->id)) {
    //         $sql = "UPDATE atributtes set favorite = true WHERE profile = '$idprofile' AND anime = '$idanime' ";
    //         $updated = $db->ejecutar($sql);
    //         if (isset($updated)) {
    //             $res = $api->response("api_fav_insert_msg",'fas fa-heart');
    //         } else {
    //             $res = $api->response("api_fav_insert_error_msg", 404);
    //         }
    //     } else {
    //         $sql = "INSERT INTO atributtes(profile, anime, favorite) VALUES('$idprofile','$idanime',true)";
    //         $inserted = $db->ejecutar($sql);
    //         if (isset($inserted)) {
    //             $res = $api->response("api_fav_insert_msg", 200,'fas fa-heart');
    //         } else {
    //             $res = $api->response("api_fav_insert_error_msg", 500);
    //         }
    //     }
    //     return $res;
    // };

    // function removeFavorite($api) {
    //     $db = $api->getDb();
    //     $POST = $api->getPOST();
    //     $idanime = $POST['id'];
    //     $idprofile = $POST['profile'];
    //     $sql = "SELECT id FROM atributtes WHERE profile = '$idprofile' AND anime = '$idanime' AND favorite = true";
    //     $valor = $db->obtener_una_columna($sql);
    //     if (isset($valor)) {
    //         $sql = "UPDATE atributtes set favorite = false WHERE profile = '$idprofile' AND anime = '$idanime'";
    //         $updated = $db->ejecutar($sql);
    //         if (isset($updated)) {
    //             $res = $api->response("api_fav_remove_msg", 200, 'far fa-heart');
    //         } else {
    //             $res = $api->response("api_fav_remove_error_msg", 404);
    //         }
    //     } else {
    //         $res = $api->response("api_fav_remove_error_msg", 404);
    //     }
    //     return $res;
    // };
?>