<?php
    function Blog($api) {
        $GET = $api->getGET();
        switch ($api->getAction()) {
            // case 'deleteAll': $result = $api->isValidAdminToken() ? deleteAll($api, $POST) : $api->response("api_Blog_lastnews_error_msg", 404); break;
            // case 'insertOne': $result = $api->isValidAdminToken() ? insertOne($api : $api->response("api_Blog_lastnews_error_msg", 404); break;
            // case 'editOne': $result = $api->isValidAdminToken() ? editOne($api) : $api->response("api_Blog_lastnews_error_msg", 404); break;
            // case 'deleteOne': $result = $api->isValidAdminToken() ? deleteOne($api) : $api->response("api_Blog_lastnews_error_msg", 404); break;
            // case 'removefav': $result = removeFavorite($api); break;
            case 'query': $result = function_exists($GET['aq']) ?  $GET['aq']($api):$api->response("api_Blog_lastnews_error_msg", 404); break;
            default: $result = $api->response("api_Blog_lastnews_error_msg", 404);  break;
        }
        return $result;
    }

    function lastposts($api) {
        //$db = $api->getDb();
        $img = $api->handleMedia('img','no','jpg');
        $prueba = array();
        for ($i= 0; $i < 5; $i++) {
            $id = $i + 1;
            array_push($prueba, array(
                'id' => $i,
                'option' => $i == 0 ? 'first':'articles', 
                'src' => /*$i < $num_elements ? $animes[$i]['caratula'] :*/ $img, 
                'titulo' => /*$i < $num_elements ? $animes[$i]["titulo_{$lang}"] :*/ "Entradas $id", 
                'fecha' => '12-12-2015', 
                'descripcion' => 'Prueba de Entradas del blog de Cosas de Anime que en un foturo sera desarrollado de forma automatica, para asi poder crear entrada de los animes y noticias destacados'
            ));
        }

        if (count($prueba) == 5) {
            return $api->response("api_Blog_lastnews_msg", 200, $prueba);
        } else {
            return $api->response("api_Blog_lastnews_error_msg", 404);
        }
    }

    function entradas($api){
        $src = $api->handleMedia('img','no','jpg');
        $prueba = [
            array('id' => 1, 'src' => $src, 'titulo' => 'Entradas ', 'fecha' => '12-12-2015', 'descripcion' => 'Prueba de Entradas del blog de Cosas de Anime que en un foturo sera desarrollado de forma automatica, para asi poder crear entrada de los animes y noticias destacados'),
            array('id' => 2, 'src' => $src, 'titulo' => 'Entradas ', 'fecha' => '12-12-2015', 'descripcion' => 'Prueba de Entradas del blog de Cosas de Anime que en un foturo sera desarrollado de forma automatica, para asi poder crear entrada de los animes y noticias destacados'),
            array('id' => 3, 'src' => $src, 'titulo' => 'Entradas ', 'fecha' => '12-12-2015', 'descripcion' => 'Prueba de Entradas del blog de Cosas de Anime que en un foturo sera desarrollado de forma automatica, para asi poder crear entrada de los animes y noticias destacados'),
            array('id' => 4, 'src' => $src, 'titulo' => 'Entradas ', 'fecha' => '12-12-2015', 'descripcion' => 'Prueba de Entradas del blog de Cosas de Anime que en un foturo sera desarrollado de forma automatica, para asi poder crear entrada de los animes y noticias destacados'),
            array('id' => 5, 'src' => $src, 'titulo' => 'Entradas ', 'fecha' => '12-12-2015', 'descripcion' => 'Prueba de Entradas del blog de Cosas de Anime que en un foturo sera desarrollado de forma automatica, para asi poder crear entrada de los animes y noticias destacados'),
            array('id' => 6, 'src' => $src, 'titulo' => 'Entradas ', 'fecha' => '12-12-2015', 'descripcion' => 'Prueba de Entradas del blog de Cosas de Anime que en un foturo sera desarrollado de forma automatica, para asi poder crear entrada de los animes y noticias destacados'),
            array('id' => 7, 'src' => $src, 'titulo' => 'Entradas ', 'fecha' => '12-12-2015', 'descripcion' => 'Prueba de Entradas del blog de Cosas de Anime que en un foturo sera desarrollado de forma automatica, para asi poder crear entrada de los animes y noticias destacados'),
            array('id' => 8, 'src' => $src, 'titulo' => 'Entradas ', 'fecha' => '12-12-2015', 'descripcion' => 'Prueba de Entradas del blog de Cosas de Anime que en un foturo sera desarrollado de forma automatica, para asi poder crear entrada de los animes y noticias destacados'),
            array('id' => 9, 'src' => $src, 'titulo' => 'Entradas ', 'fecha' => '12-12-2015', 'descripcion' => 'Prueba de Entradas del blog de Cosas de Anime que en un foturo sera desarrollado de forma automatica, para asi poder crear entrada de los animes y noticias destacados')
        ];
        return $api->response("api_Blog_lastnews_msg", 200, $prueba);
    }
?>