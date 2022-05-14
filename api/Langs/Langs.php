<?php 
    function Langs($api) {
        switch ($api->getAction()) {
            case 'insertedittranslation': $result = insertedittranslation($api); break;
            case 'getTittleLangs': $result = getTittleLangs($api); break;
            case 'getcodelangs': $result = getcodelangs($api); break;
            case 'deletetranslation':$result = deletetranslation($api); break;
            default: $result = $api->response("api_history_resp_error_msg", 404);  break;
        }
        return $result;
    }

    function getcodelangs($api) {
        $db = $api->getDb();
        $sql = "SELECT id, code FROM langs";
        $res = $db->listar($sql);
        if (isset($res)) {
            $mensage = 'Se ha podido obtener los idiomas';
            $result = $api->response($mensage, 200, $res);
        } else {
            $mensage = 'No se han podido obtener los idiomas';
            $result = $api->response($mensage, 500);
        }
        return $result;
    }

    function getTittleLangs($api) {
        $POST = $api->getPOST();
        $translation = $api->gettranslations($POST['translations']);
        if ( count($translation) > 0 ) {
            foreach ($translation as $key => $trans) {
                $sql = "SELECT code FROM langs WHERE id = '{$trans['id_external']}'";
                $trans["code"] = $api->getDb()->obtener_una_columna($sql);
                $translation[$key] = $trans;
            }
            $mensage = "Se ha podido obtener la traducion del idioma {$api->getLang()}";
            $result = $api->response($mensage, 200, $translation);
        } else {
            $mensage = "No se han podido obtener la traducion del idioma {$api->getLang()}";
            $result = $api->response($mensage, 500);
        }
        return $result;
    }

    function insertedittranslation($api) {
        $db = $api->getDb();
        $POST = $api->getPOST();
        if (isset($POST['id_external'])) {
            $idexternal = "AND t.id_external = {$POST['id_external']}";
        } else {
            $idexternal = "";
        }
        $sql = "SELECT t.id
        FROM langs AS l INNER JOIN translations AS t 
        ON(t.lang = l.id AND l.code = '{$POST['code']}')
        WHERE t.kind = '{$POST['kind']}' $idexternal";
        $res = $db->listar($sql);
        if (isset($res[0]->id)) {
            $sql = "UPDATE translations SET translations = '{$POST['translation']}' WHERE lang = '{$POST['lang']}' $idexternal";
        } else {
            $sql = "INSERT INTO translations(translation, lang, kind, id_external) VALUES({$POST['translation']}, {$POST['lang']}, {$POST['kind']}, {$POST['id_external']};";
        }
        $updated = $db->ejecutar($sql);
        if ($updated) {
            $mensage = "Se ha podido obtener la traducion del idioma {$POST['code']}";
            $result = $api->response($mensage, 200, $updated);
        } else {
            $mensage = "No se han podido obtener la traducion del idioma {$POST['code']}";
            $result = $api->response($mensage, 500);
        }
        return $result;
    }

    function deletetranslation($api) {
        $db = $api->getDb();
        $POST = $api->getPOST();
        $sql = "SELECT t.id
        FROM langs AS l INNER JOIN translations AS t 
        ON(t.lang = l.id AND l.code = '{$POST['code']}')
        WHERE t.kind = '{$POST['kind']}' AND t.id_external = {$POST['id_external']}";
        $res = $db->listar($sql);
        if (isset($res[0]->id)) {
            $sql = "DELETE FROM translations WHERE lang = '{$POST['lang']}' AND t.id_external = {$POST['id_external']}";
            $updated = $db->ejecutar($sql);
            $mensage = "Se ha podido obtener la traducion del idioma {$POST['code']}";
            $result = $api->response($mensage, 200, $updated);
        } else {
            $mensage = "No se han podido obtener la traducion del idioma {$POST['code']}";
            $result = $api->response($mensage, 500);
        }
        return $result;
    }
