<?php   
    function Upload($api) {
        switch ( $api->getAction()) {
            case 'subir': $result = subir($api); break;
            case 'importar': $result = importar($api); break;
            case 'delete': $result = delete($api); break;
            case 'deleteby': $result = deleteby($api); break;
            case 'selecionar': $result = selecionar($api); break;
            case 'getmediaby': $result = getmediabysomething($api); break;
            case 'getmedia': $result = getmedia($api);break;
            default: $result = null; break;
        }
        return $result;
    }


    function getmedia($api) {
        $POST = $api->getPOST();
        $media = $api->apiReqNode("media", $POST);
        if (count($media) > 0) {
            $value->src = $api->handleMedia($media['type'], $media['name'], $media['extension'], $value->siglas);
        } else {
            $value->src = $api->handleMedia("img","no","jpg");
        }

        return $api->response("api_Anime_slides_msg", 200, postinset($POST, $api));
        // } else {
        //     return $api->response("api_Anime_slides_error_msg", 404); 
        // }
    }

    function getmediabysomething($api) {
        $POST = $api->getPOST();
        if ($POST['type'] == 'anime') {
            $type = "(m.type = 'portada' OR m.type = 'banner')";
        }
        return $api->response("api_Anime_slides_msg", 200, postinset($POST, $api));
        // } else {
        //     return $api->response("api_Anime_slides_error_msg", 404); 
        // }
    }

    function subir($api) {
        $POST = $api->getPOST();
        $file = $_FILES['file']['name'];
        for ($i = 0; $i < count($file); $i++) {
            $data = getnameExtension($file[$i], $POST['type'], $POST['siglas']);
            $name = $data['name'];
            $extension = $data['ext'];
            if ( !nameExist($POST, $api) && ($api->isImage($extension) || $api->isVideo($extension)) && makefolder($api,$POST)) {
                $siglas = getsiglas($POST);
                $url = $api->handleMedia($POST['type'], $name, $extension, $siglas);
                if (!file_exists($url)) {
                    move_uploaded_file($_FILES['file']['tmp_name'][$i], $url);
                }

                if (!isset($res[0]["id"])) {
                    $element = insert($POST, $api);
                }
                $response[$i] = postinset($element, $api, $_FILES['file']['size'][$i]);
            }
        }
        
        if (isset($response)) {
            return $api->response("api_Anime_slides_msg", 200, $response);
        } else {
            return $api->response("api_Anime_slides_error_msg", 404, $_FILES); 
        }
    }

    function importar($api) {
        $POST = $api->getPOST();
        $data = getnameExtension($POST['file2'], $POST['type'], $POST['siglas']);
        $name = $data['name'];
        $extension = $data['ext'];
        if ( !nameExist($POST, $api) && ($api->isImage($extension) || $api->isVideo($extension)) && makefolder($api,$POST)) {
            $contenido_pdf = file_get_contents($POST['file2']);
            $siglas = getsiglas($POST);
            $path = $api->handleMedia($POST['type'], $name, $extension, $siglas);
            $api->writeFile($contenido_pdf,'backup',$path);
            insert($POST, $api);
            return $api->response("api_Anime_slides_msg", 200, postinset($POST['file2'], $api));
        } else {
            return $api->response("api_Anime_slides_error_msg", 404); 
        }
    }

    function postinset($data, $api, $size = 0) {
        $POST = $api->getPOST();
        foreach ($data as $d) {
            $ultimfoto = json_encode($api->apiReqNode("media", $d));
            $siglas = getsiglas($POST);
            $url = $api->handleMedia($ultimfoto["type"], $ultimfoto['name'], $ultimfoto['extension'], $siglas);
            $_filesize = $size == 0 ? $size : filesize($url);
            $_filesize = $_filesize / 1024;
            $_filesizetext = "Kb"; //kb
            if ($_filesize > 1000) {
                $_filesize = $_filesize / 1024;
                $_filesizetext = "Mb";
            } //Mb
            $_filesize = round($_filesize, 1, PHP_ROUND_HALF_UP);
            $response['filesize'] = "$_filesize $_filesizetext";
            
            switch ($ultimfoto['type']) {
                case "portada": $response['kind'] = "anime"; break;
                case "banner": $response['kind'] = "anime"; break;
                case "personages": $response['kind'] = "personage"; break;
                case "episodes": $response['kind'] = "episode"; break;
                case "openings": $response['kind'] = "opening"; break;
                case "endings": $response['kind'] = "ending"; break;
                case "profiles": $response['kind'] = "profiles"; break;
                case 'new_post': $response['kind'] = "posts"; break;
                case 'chat': $response['kind'] = "chat"; break;
            }

            $response["siglas"] = $siglas;
            $response["id"] = $ultimfoto['id_relative'];
            $response["type"] = $ultimfoto["type"];
            $response["name"] = $ultimfoto["name"];
            $response["extension"] = $ultimfoto["extension"];
            $response["main"] = $ultimfoto["main"];
            $response['urlarchivo'] = $url;
            $response['urldescarga'] = $url;
            return $response;
        }
    }

    function makefolder($api,$POST) {
        $c = false;
        $siglas = getsiglas($POST);
        if (isset($siglas)) {
            switch ($POST['type']) {
                case 'profile':
                case 'new_post':
                case 'chat':
                    $path = "/media/{$POST['type']}/{$POST[$POST['type']]}";
                    break;
                default:
                    $path = "/media/animes/{$POST['siglas']}/{$POST['type']}";
                    break;
            }
            $api->mkFolders($path);
            $c = is_dir($path) ? true : false;   
        }
        return $c;
    }

    function nameExist($POST, $api) {
        $valor = json_encode($api->apiReqNode("media/check", $POST));

        return isset($valor) ? true : false;
    }

    function getnameExtension($file, $type = null, $siglas = null) {
        if (strpos($file,"http")) {
            $name = explode("/", $file);
            $namearray = explode(".", end($name));
            $filename = $namearray[0];
            $extension = $namearray[1];
        } else {
            $name = pathinfo($file);
            $filename = $name['filename'];
            $extension = $name['extension'];
        }
        $data['name'] = isset($type) && ($type == "banner" || $type == "portada") ? $siglas : normaliza_upload($filename); //nombre completo MD5 -> ahora normal
        $data['ext'] = strtolower($extension); //extension nombre
        return $data;
    }

    function delete($api) {
        $POST = $api->getPOST();
        $idimg = 0;
        $POST["anime"] = $POST["id_relative"];
        $POST["siglas"] = $POST["siglas"];
        $POST["idioma"] = $POST["idioma"];
        $data = getnameExtension($POST['namedelete']);
        $ruta_real = $api->handleMedia($POST['type'], $data['name'], $data['ext'], $POST['siglas'], $elem);
        $sql = "SELECT * FROM media WHERE id='{$POST['iddelete']}'";
        $valor = $db->listar($sql);
        $actual = get_object_vars($valor[0]);
        $idimg = $actual['id'];
        $principal = $actual['main'];
        if (file_exists($ruta_real)) unlink($ruta_real);
        $sql = "DELETE FROM media WHERE id='{$POST['iddelete']}'";
        $db->ejecutar($sql);

        if ($actual['main'] == '1') {
            $sql = "SELECT MAX(updated) FROM media";
            $valor = $db->obtener_una_columna($sql);
            $ultimoorden = $valor['updated'];
            $sqlprueba = "UPDATE media SET main = true WHERE type = '{$POST['type']}' AND {$POST['kind']} = '{$POST['id_relative']}'  AND updated = $ultimoorden";
            $db->ejecutar($sqlprueba);
            $sql = "SELECT id, main FROM media WHERE type = '{$POST['type']}' AND {$POST['kind']} = '{$POST['id_relative']}'  AND main = '1'";
            $valor = $db->listar($sql);
            $valor = get_object_vars($valor[0]);
            $idimg = $valor['id'];
            $principal = $valor['main'];
        }

        $response['main'] = $principal;
        $response['idimg'] = $idimg;
        $response['id'] = $POST['iddelete'];

        if (isset($response)) {
            return $api->response("api_Anime_slides_msg", 200, $response);
        } else {
            return $api->response("api_Anime_slides_error_msg", 404); 
        }
    }

    function getsiglas($POST) {
        if (isset($POST['siglas'])) {
            $siglas = $POST['siglas'];
        } elseif (isset($POST['profile'])) {
            $siglas = $POST['profile'];
        } elseif (isset($POST['new_post'])) {
            $siglas = $POST['new_post'];
        } elseif (isset($POST['chat'])) {
            $siglas = $POST['chat'];
        }
        return $siglas;
    }

    function insert($POST, $api) {
        $main = isset($POST['main']) ? $POST['main'] : null;
        return json_encode($api->apiReqNode("media/new", $POST));
    }

    function deleteby($api) {
        $POST = $api->getPOST();
        $sql = "DELETE FROM media WHERE anime = '{$POST['id']}'";
        $delete = $db->ejecutar($sql);
        if (isset($delete)) {
            return $api->response("api_Anime_slides_msg", 200, $POST);
        } else {
            return $api->response("api_Anime_slides_error_msg", 404); 
        }
    }

    function normaliza_upload($cadena) {
        $originales = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞ&ßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ"';
        $modificadas = 'AAAAAAACEEEEIIIIDNOOOOOOUUUUYP8BaaaaaaaceeeeiiiidnoooooouuuyybyRr\'';
        $cadena = utf8_decode($cadena);
        $cadena = strtr($cadena, utf8_decode($originales), $modificadas);
        $cadena = str_replace(" ", "-", trim($cadena));
        return utf8_encode($cadena);
    }

    function selecionar($api) {
        $POST = $api->getPOST();
        $sql = "UPDATE media SET main = false WHERE type = '{$POST['type']}' AND {$POST['kind']} = '{$POST['id_relative']}' ";
        $db->ejecutar($sql);

        $sql = "UPDATE media SET main = true WHERE id = '{$POST['idimg']}'";
        $db->ejecutar($sql);
        $response['idimg'] = $POST['idimg'];
        if (isset($response)) {
            return $api->response("api_Anime_slides_msg", 200, $response);
        } else {
            return $api->response("api_Anime_slides_error_msg", 404); 
        }
    }
?>