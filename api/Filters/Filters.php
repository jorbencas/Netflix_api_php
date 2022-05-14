<?php 
    function Filters($api) {
        $GET = $api->getGET();
        switch ($api->getAction()) {
            case 'handlesearch': $result = handlesearch($api); break;
            case 'updatesearchuser': $result = $api->isValidAccesToken() ? updatesearchuser($api) : $api->response("api_Buscador_resp_error_msg", 404); break;
            case 'mysearches': $result = $api->isValidAccesToken() ? mysearches($api) : $api->response("api_Buscador_resp_error_msg", 404); break;
            case 'deletesearch': $result = $api->isValidAccesToken() ? deletesearch($api) : $api->response("api_Buscador_resp_error_msg", 404); break;
            case 'getFiltersAvaible': $result = getFiltersAvaible($api); break;
            case 'getFilters': $result = getFilters($api); break;
            case 'query': $result = function_exists($GET['aq']) ? $GET['aq']($api): $api->response("api_Filters_resp_error_msg", 400); break;
            default: $result = $api->response("api_Filters_resp_error_msg", 404); break;
        }
        return $result;
    }

    function getFiltersAvaible($api){
        $sql = "SELECT DISTINCT ON(f.code) f.id, f.code 
        FROM filters AS f INNER JOIN animes AS a 
        ON a.generes LIKE ('%' || f.code::text || '%') 
        WHERE f.kind = 'generes';";
        $filters = $api->getDb()->listar($sql);
        $result = array();
        
        foreach ($filters as $filter) {
            $translations = $api->gettranslations([
                array("kind" => "filters", "id_external" => $filter->id)
            ]);
            if ( count($translations) > 0 ) {
                array_push($result,array('filter' => $filter->code,'avable' => false, "title" => $translations['translation']));
            }
        }
        if (isset($result)) {
            $res = $api->response("api_Filters_msg", 200, $result);
        } else {
            $res = $api->response("api_Filters_error_msg", 404); 
        }
        return $res;
    }

    function getFilters($api) {
        $db = $api->getDb();
        $POST = $api->getPOST();
        $result = array();
        if($api->isAjax() && isset($POST['kind'])){
            if ($POST['kind'] == 'years' || $POST['kind'] == 'letters'){
                if ($POST['kind'] == 'years') {
                    $r = ['1998', '1999', '2000', '2001', '2002', '2003', '2004', '2005', '2006', '2007', '2008', '2009', '2010', '2011', '2012', '2013', '2014', '2015', '2016', '2017', '2018', '2019', '2020','2021'];
                } else {
                    $r = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'Ñ', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '0-9'];
                }
                foreach ($r as $value) {
                    array_push($result,array('filter' => $value, "title" => $value));
                }
            } else {
                $sql = "SELECT id, code, kind FROM filters WHERE kind = '{$POST['kind']}'";
                $filters = $db->ejecutar($sql);
                foreach ($filters as $filter) {
                    $translations = $api->gettranslations([
                        array("kind" => "filters", 'id_external' => $filter['id'])
                    ]);

                    if (count($translations) > 0) {
                        array_push($result,array('filter' => $filter['code'], "title" => $translations['translation']));
                    }
                }
            }
        } else {
            $sql = "SELECT id, code, kind FROM filters";
            $filters = $db->ejecutar($sql);
            $translations = $api->gettranslations([
                array("kind" => "filters")
            ]);
            foreach ($filters as $filter) {
                if (count($translations) > 0) {
                    if (!isset($result[$filter['kind']])) {
                        $result[$filter['kind']] = array();
                    }
                    foreach ($translations as $lang) {
                        if ($filter['id'] == $lang['id_external']) {
                            array_push($result[$filter['kind']],array('filter' => $filter['code'], "title" => $lang['translation']));
                        }
                    }
                }
            }
            $r['letters'] = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'Ñ', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '0-9'];
            $r['years'] = ['1998', '1999', '2000', '2001', '2002', '2003', '2004', '2005', '2006', '2007', '2008', '2009', '2010', '2011', '2012', '2013', '2014', '2015', '2016', '2017', '2018', '2019', '2020','2021'];
            foreach (array('letters','years') as $l) {
                foreach ($r[$l] as $value) {
                    if (!isset($result[$l])) {
                        $result[$l] = array();
                    }
                    array_push($result[$l],array('filter' => $value, "title" => $value));
                }
            }
        }
        if (isset($result)) {
            $res = $api->response("api_Filters_msg", 200, $result);
        } else {
            $res = $api->response("api_Filters_error_msg", 404); 
        }
        return $res;
    }

    function handlesearch($api) {
        $db = $api->getDb();
        $POST = $api->getPOST();
        if (!isset($POST['search']) || empty($POST['search'])) {
            return $api->response("api_Buscador_error_msg", 404);
        } else {
            $kind = $POST['kind'];
            if (isset($POST['limit'])) {
                $lim = explode("_",$POST['limit']);
                $limit = "OFFSET $lim[0] LIMIT $lim[1]";
            }

            $filter = ucfirst(strtolower($POST['search']));
            if (strstr($filter," ")) {
                $delimiter = " ";
                $words = explode($delimiter,$filter);
            } else if (strstr($filter,"-")) {
                $delimiter = "-";
                $words = explode($delimiter,$filter);
            } else if (strstr($filter,";")) {
                $delimiter = ";";
                $words = explode($delimiter,$filter);
            }

            if (isset($delimiter)) {
                $filter = "";
                foreach ($words as $key => $value) {
                    if ($key < (count($words) - 1)) {
                        $filter .= ucfirst(strtolower($value)) . $delimiter;
                    } else {
                        $filter .= ucfirst(strtolower($value));
                    }
                }
            }

            switch ($kind) {
                case 'letters':
                    $where = "";
                    $tittles = [];
                    $translations = $api->gettranslations([
                        array("kind" => "titulo", "keyword" => $filter),
                    ]);
                    if (count($translations) > 0 ) {
                        $id_externals = array();
                        foreach ($translations as $lang) {
                            $titulo = $lang['translation'];
                            if ($filter == '0-9') {
                                for ($i=0; $i <= 9; $i++) { 
                                    if (strchr($titulo,$i)) {
                                        array_push($id_externals,$lang['id_external']);
                                    }
                                }
                            } else {
                                $tittles[$lang['id_external']] = $titulo;
                                array_push($id_externals,$lang['id_external']);
                            }
                        }
                        $ids = implode(",",$id_externals);
                        $where = "AND a.id IN($ids)";
                    }
                    $sql = "SELECT a.id, a.generes, a.siglas, a.created, a.kind
                    FROM animes AS a 
                    WHERE a.created IS NOT NULL $where ORDER BY a.date_publication DESC $limit";
                    $result = $db->listar($sql);
                    foreach ($result as $key => $value) {
                        $value = get_object_vars($value);
                        $value['titulo'] = $tittles[$value['id']];
                        $translations = $api->gettranslations([
                            array("kind" => "sinopsis", "id_external" => $value['id'])
                        ]);
                        if ( count($translations) > 0 ) {
                            $kind = $translations['kind'];
                            $value[$kind] = $translations['translation'];
                        }

                        $media = $api->apiReqNode("media", array(
                            'type' => 'portada',
                            'id_external' => $value['id']
                        ));
                        if (count($media) > 0) {
                            $value['src'] = $api->handleMedia($media['type'], $media['name'], $media['extension'], $value['siglas']);
                        } else {
                            $value['src'] = $api->handleMedia("img","no","jpg");
                        }
                        $result[$key] = $value;
                    }
                    break;
                case 'opening':
                    $sql = "SELECT o.id, o.nombre, o.descripcion, o.anime, m.name, 
                    m.extension, a.siglas, o.num, a.idiomas, m.type
                    FROM animes AS a
                    INNER JOIN openings as o ON a.id = o.anime 
                    INNER JOIN media AS m ON m.anime = a.id
                    WHERE o.nombre LIKE $filter AND m.type = 'portada'";
                    $result = $db->listar($sql);
                    foreach ($result as $key => $value) {
                        $media = $api->apiReqNode("media", array(
                            'type' => 'portada',
                            'id_external' => $value->anime
                        ));
                        if (count($media) > 0) {
                            $value->src = $api->handleMedia($media['type'], $media['name'], $media['extension'], $value->siglas);
                        } else {
                            $value->src = $api->handleMedia("img","no","jpg");
                        }
                        $result[$key] = $value;
                    }
                    break;
                case 'ending':
                    $sql = "SELECT e.id, e.anime, e.nombre, e.descripcion, m.name, 
                    m.type, m.extension, a.siglas, e.num, a.idiomas
                    FROM endings as e
                    INNER JOIN animes AS a ON a.id = e.anime 
                    INNER JOIN media AS m ON m.anime = a.id
                    WHERE e.nombre LIKE $filter AND m.type = 'portada'";
                    $result = $db->listar($sql);
                    foreach ($result as $key => $value) {
                        $media = $api->apiReqNode("media", array(
                            'type' => 'portada',
                            'id_external' => $value->anime
                        ));
                        if (count($media) > 0) {
                            $value->src = $api->handleMedia($media['type'], $media['name'], $media['extension'], $value->siglas);
                        } else {
                            $value->src = $api->handleMedia("img","no","jpg");
                        }
                        $result[$key] = $value;
                    }
                    break;
                case 'episode':
                    $sql = "SELECT e.id, a.siglas, e.anime, m.name, m.extension, 
                    m.type, e.num FROM animes AS a 
                    INNER JOIN episodes as e ON a.id = e.anime
                    INNER JOIN media AS m ON m.anime = a.id
                    WHERE e.nombre LIKE $filter AND m.type = 'portada'";
                    $result = $db->listar($sql);
                    foreach ($result as $key => $value) {
                        $media = $api->apiReqNode("media", array(
                            'type' => 'portada',
                            'id_external' => $value->anime
                        ));
                        if (count($media) > 0) {
                            $value->src = $api->handleMedia($media['type'], $media['name'], $media['extension'], $value->siglas);
                        } else {
                            $value->src = $api->handleMedia("img","no","jpg");
                        }
                        $result[$key] = $value;
                    }
                    break;
                case 'personage':
                    # code...
                    break;
                case 'username_searched':
                    # code...
                    break;
                case 'new_post':
                    # code...
                    break;
                case 'chat':
                    # code...
                    break;
                case 'notifications':
                    # code...
                    break;
                default:
                    $result = null;
                    break;
            }
        
            if (isset($result)) {
                // if ($api->isValidAccesToken()) {
                //     $result['data'] = $api->apiReq("Filters", array(
                //         'user' => $POST['user'],
                //         'action' => 'updatesearchuser',
                //         $kind => $result[0]->id,
                //         'kind'  => $kind,
                //         'profile' => $POST['profile'],
                //         'search' => $POST['search']
                //     ));
                // }
                return $api->response("api_Buscador_msg", 200, $result);
            } else {
                return $api->response("api_Buscador_error_msg", 404);
            }
        }
    }

    function mysearches($api) {
        $db = $api->getDb();
        $POST = $api->getPOST();
        $sql = "SELECT DISTINCT ON(s.anime) a.id, a.sinopsis_es,
        a.sinopsis_en, a.sinopsis_va, a.sinopsis_ca, a.titulo_es, a.titulo_en, 
        a.titulo_va, a.titulo_ca, a.siglas, a.kind,
        m.name, m.extension, m.type
        FROM searches s INNER JOIN animes a ON(s.anime = a.id) 
        INNER JOIN media AS m ON(m.anime = a.id) 
        WHERE s.profile = '{$POST['profile']}' AND m.type = 'portada' 
        ORDER BY s.anime, s.created DESC";  
        $result = $db->listar($sql);
        if (isset($result[0]->id)) {
            foreach ($result as $value) {
                $value->src = $api->handleMedia($value->type, $value->name, $value->extension, $value->siglas);
                unset($value->name);
                unset($value->extension);
                unset($value->type);
            }
            return $api->response("api_UpdateBuscador_msg", 200, $result);
        } else {
            return $api->response("api_UpdateBuscador_error_msg", 404);
        }
    }

    function updatesearchuser($api) {
        $db = $api->getDb();
        $POST = $api->getPOST();
        $date = getdate();
        $sql = "SELECT id FROM searches WHERE profile = '{$POST['profile']}' AND {$POST['kind']} = '{$POST[$POST['kind']]}' AND updated = '$date' ";
        $id = $db->obtener_una_columna($sql);
        if (!isset($id)) {
            $sql = "INSERT INTO searches(search, kind, profile, {$POST['kind']}) VALUES('{$POST['search']}', '{$POST['kind']}', '{$POST['profile']}', '{$POST[$POST['kind']]}') RETURNING *;";
            $inserted = $db->obtener_uno($sql);
            return $api->response("api_UpdateBuscador_msg", 200, $inserted);
        } else {
            return $api->response("api_UpdateBuscador_error_msg", 404);
        }
    }

    function deletesearch($api) {
        $db = $api->getDb();
        $POST = $api->getPOST();
        $sql = "SELECT id FROM searches WHERE kind = '{$POST['kind']}' AND id_external = '{$POST['id']}'";
        $id = $db->obtener_una_columna($sql);
        if (!isset($id)) {
            $sql = "DELETE FROM searches WHERE id = $id";
            $deleted = $db->ejecutar($sql);
            if ($deleted) {
                return $api->response("api_Episodes_delete_msg", 200, "ok");
            } else {
                return $api->response("api_UpdateBuscador_error_msg", 404);
            }
        } else {
            return $api->response("api_UpdateBuscador_error_msg", 404);
        }
    }
?>