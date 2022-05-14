<?php
  function Collections($api) {
    $GET = $api->getGET();
    switch ($api->getAction()) {
      case 'removecollection': $result = $api->isValidAccesToken() ? removecollection($api) : $api->response("api_Collections_resp_error", 404); break;
      case 'addelementcollection': $result = $api->isValidAccesToken() ? addelementcollection($api) : $api->response("api_Collections_resp_error", 404); break;
      case 'removeonecollection': $result = $api->isValidAccesToken() ? removeonecollection($api) : $api->response("api_Collections_resp_error", 404); break;
      case 'removecollectionbyanime': $result = $api->isValidAccesToken() ? removecollectionbyanime($api) : $api->response("api_Collections_resp_error", 404); break;
      case 'removecollectionbyepisode': $result = $api->isValidAccesToken() ? removecollectionbyepisode($api) : $api->response("api_Collections_resp_error", 404); break;
      case 'apione': $result = getcollection($api); break;
      case 'apislide': $result = getmycollection($api); break;
      case 'apiby': $result = getallcolections($api); break;
      case 'query': $result = function_exists($GET['aq']) ? $GET['aq']($api) : $api->response("api_Collections_resp_error", 404); break;
      default: $result = $api->response("api_Collections_resp_error",  404);  break;
    }
    return $result;
  }
    
  function removecollection($api) {
    $db = $api->getDb();
    $POST = $api->getPOST();
    if (isset($POST['profile']) && isset($POST['id'])) {
      $sql = "SELECT c.id FROM collections c 
      WHERE c.profile = '{$POST['profile']}' AND c.id = '{$POST['id']}'";
      $collections = $db->listar($sql);
      if (isset($collections[0]->id)) {
        $sql = "DELETE FROM collections WHERE id = '{$POST['id']}'";
        $deleted = $db->ejecutar($sql);
        if (isset($deleted)) {
          $res = $api->response("api_Collections_removecollection_msg", 200, $collections);
        } else {
          $res = $api->response("api_Collections_removecollection_error_msg", 404);
        }
      } else {
        $res = $api->response("api_Collections_removecollection_error_msg", 404);
      }
    } else {
      $res = $api->response("api_Collections_removecollection_error_msg", 404);
    }
    return $res;
  }
    
  function addelementcollection($api) {
    $db = $api->getDb();
    $POST = $api->getPOST();
    if (isset($POST['profile']) && isset($POST['episode'])) {
      $sql = "SELECT c.id 
      FROM collections c INNER JOIN atributtes AS atr ON(atr.collection = c.id) 
      WHERE c.profile = '{$POST['profile']}' AND atr.episode = '{$POST['episode']}' ";
      $collections = $db->listar($sql);
      if (!isset($collections[0]->id)) {
        $sql = "SELECT c.id FROM collections c WHERE c.name = '{$POST['name']}'";
        $collections = $db->listar($sql);
        if (!isset($collections[0]->id)) {
          $sql = "INSERT INTO collections(name,profile) VALUES('{$POST['name']}', '{$POST['profile']}')";
          $inserted = $db->ejecutar($sql);
        
          $sql = "SELECT MAX(id) AS id FROM collections WHERE profile = '{$POST['profile']}'";
          $collections = $db->listar($sql);
          $id = $collections[0]->id;
        } else {
          $id = $collections[0]->id;
        }
        
        $sql = "INSERT INTO atributtes(profile, episode, collection) VALUE('{$POST['profile']}','{$POST['episode']}', '$id')";
        $inserted = $db->ejecutar($sql);
        if (isset($inserted)) {
          $sql = "SELECT c.id, c.name as titulo, c.profile, m.type, m.name, m.extension, a.siglas
          FROM collections c INNER JOIN atributtes AS atr ON(atr.collection = c.id) 
          INNER JOIN episodes as e ON e.id = atr.episode
          INNER JOIN animes AS a ON(a.id = e.anime)
          INNER JOIN media AS m ON m.anime = a.id
          WHERE c.id = '$id' AND c.profile = '{$POST['profile']}' AND m.type = 'portada'";
          $collections = $db->listar($sql);
          if (isset($collections[0]->id)) {
            foreach ($collections as $key => $value) {
              $media = $value->name;
              $ext = $value->extension;
              $value->src = $api->handleMedia($value->type,$media,$ext,$value->siglas);
              unset($value->name);
              unset($value->extension);
              $res[$key] = $value;
            }
            $res = $api->response("api_Collections_addelement_msg", 200, $res);
          } else {
            $res = $api->response("api_Collections_addelement_error_msg", 404);
          }
        } else {
          $res = $api->response("api_Collections_addelement_error_msg", 404);
        }
      } else {
        $res = $api->response("api_Collections_addelement_error_msg", 404);
      }
    } else {
      $res = $api->response("api_Collections_addelement_error_msg", 404);
    }
    return $res;
  };

  function removeonecollection($api) {
    $db = $api->getDb();
    $POST = $api->getPOST();
    if (isset($POST['profile']) && isset($POST['episode'])) {
      $sql = "SELECT atr.episode AS episode_id
      FROM collections c INNER JOIN atributtes AS atr ON(atr.collection = c.id) 
      WHERE c.profile = '{$POST['profile']}' AND atr.episode = '{$POST['episode']}' ";
      $collections = $db->listar($sql);
      if (isset($collections[0]->episode_id)) {
        $sql = "DELETE FROM atributtes WHERE episode = '{$POST['episode']}' AND profile = '{$POST['profile']}'";
        $deleted = $db->ejecutar($sql);
        if (isset($deleted)) {
          $res = $api->response("api_Collections_removeone_msg", 200, $collections);
        } else {
          $res = $api->response("api_Collections_removeone_error_msg", 404);
        }
      } else {
        $res = $api->response("api_Collections_removeone_error_msg", 404);
      }
    } else {
      $res = $api->response("api_Collections_removeone_error_msg", 404);
    }
  return $res;
  };

  function getcollection($api) {
    $db = $api->getDb();
    $GET = $api->getGET();
    $sql = "SELECT c.id, c.name AS titulo, m.type, m.name, m.extension, a.siglas, a.titulo_es as anime_titulo_es, 
    a.titulo_en as anime_titulo_en, a.titulo_va as anime_titulo_va, a.titulo_ca as anime_titulo_ca, 
    e.id as episode_id, e.titulo_es, e.titulo_en, e.titulo_va, e.titulo_ca
    FROM collections c INNER JOIN atributtes AS atr ON(atr.collection = c.id) 
    INNER JOIN episodes as e ON e.id = atr.episode
    INNER JOIN animes AS a ON(a.id = e.anime)
    INNER JOIN media AS m ON m.anime = a.id
    WHERE m.type = 'portada' AND c.id = '{$GET['ap']}'";
    $collections = $db->listar($sql);
    if (isset($collections[0]->id)) {
      foreach ($collections as $key => $value) {
        $media = $value->name;
        $ext = $value->extension;
        $value->src = $api->handleMedia($value->type,$media,$ext,$value->siglas);
        unset($value->name);
        unset($value->extension);
        $collections[$key] = $value;
      }
      $res = $api->response("api_Collections_one_msg", 200, $collections);
    } else {
      $res = $api->response("api_Collections_one_error_msg", 404);
    }
    return $res;
  };

  function getmycollection($api) {
    $db = $api->getDb();
    $GET = $api->getGET();
    $sql = "SELECT DISTINCT ON(c.id) c.id, c.name AS titulo, m.type, m.name, m.extension, a.siglas, e.titulo_es, e.titulo_en, e.titulo_va,
    e.titulo_ca
    FROM collections c INNER JOIN atributtes AS atr ON(atr.collection = c.id) 
    INNER JOIN episodes as e ON e.id = atr.episode
    INNER JOIN animes AS a ON(a.id = e.anime)
    INNER JOIN media AS m ON m.anime = a.id
    WHERE c.profile = '{$GET['as']}' AND m.type = 'portada'";
    $collections = $db->listar($sql);
    if (isset($collections[0]->id)) {
      foreach ($collections as $key => $value) {
        $media = $value->name;
        $ext = $value->extension;
        $value->src = $api->handleMedia($value->type,$media,$ext,$value->siglas);
        unset($value->name);
        unset($value->extension);
        $sql = "SELECT count(c.id) AS cant 
        FROM collections c WHERE c.id = $value->id";
        $cant = $db->listar($sql);
        $value->num = $cant[0]->cant;
        $collections[$key] = $value;
      }
      $res = $api->response("api_Collections_my_msg", 200, $collections);
    } else {
      $res = $api->response("api_Collections_my_error_msg", 404);
    }
    return $res;
  };

    function getallcolections($api) {
      $db = $api->getDb();
      $sql = "SELECT c.id, c.name AS titulo
      FROM collections c";
      $collections = $db->listar($sql);
      if (isset($collections[0]->id)) {
        $res = $api->response("api_Collections_msg", 200, $collections);
      } else {
        $res = $api->response("api_Collections_error_msg", 404);
      }
      return $res;
    }

   /*  function getcollectionsby($api) {
      $db = $api->getDb();
      $sql = "SELECT c.id, c.name AS titulo, m.type, m.name, m.extension, a.siglas, e.titulo_es, e.titulo_en, e.titulo_va,
      e.titulo_ca, e.num
      FROM collections c INNER JOIN atributtes AS atr ON(atr.collection = c.id) 
      INNER JOIN episodes as e ON e.id = atr.episode
      INNER JOIN animes AS a ON(a.id = e.anime)
      INNER JOIN media AS m ON m.anime = a.id
      WHERE c.profile = '{$POST['profile']}' AND atr.episode = '{$POST['id']}' AND m.type = 'portada'";
      $collections = $db->listar($sql);
      if (isset($collections[0]->id)) {
        foreach ($collections as $key => $value) {
          $media = $value->name;
          $ext = $value->extension;
          $value->src = handleMedia($value->type,$media,$ext,$value->siglas);
          unset($value->name);
          unset($value->extension);
          $collections[$key] = $value;
        }
        $res = $api->response("api_Collections_my_msg", 200, $collections);
      } else {
        $res = $api->response("api_Collections_my_error_msg", 404);
      }
      return $res;
    }
 */
  function removecollectionbyanime($api) {
    $db = $api->getDb();
    $POST = $api->getPOST();
    $sql = "SELECT c.id FROM animes a 
    inner join attributes AS atr ON(atr.episode = e.id) 
    inner join episode e ON(e.anime = a.id) 
    inner join collections AS c ON(c.id = atr.collection)
    WHERE a.id = '{$POST['id']}'";
    $collections = $db->listar($sql);
    if (isset($collections[0]->id)) {
      foreach ($collections as $key => $value) {
      $sql = "DELETE FROM collections WHERE id = $value";
      $db->ejecutar($sql);
      }
      $res = $api->response("api_Collections_my_msg", 200, $collections);
    } else {
      $res = $api->response("api_Collections_my_error_msg", 404);
    }
    return $res;
  }

  function removecollectionbyepisode($api) {
    $db = $api->getDb();
    $POST = $api->getPOST();
    $sql = "SELECT c.id FROM episode e
    inner join attributes AS atr ON(atr.episode = e.id) 
    inner join collections AS c ON(c.id = atr.collection)
    WHERE e.id = '{$POST['id']}'";
    $collections = $db->listar($sql);
    if (isset($collections[0]->id)) {
      foreach ($collections as $key => $value) {
        $sql = "DELETE FROM collections WHERE id = $value";
        $db->ejecutar($sql);
      }
      $res = $api->response("api_Collections_my_msg", 200, $collections);
    } else {
      $res = $api->response("api_Collections_my_error_msg", 404);
    }
    return $res;
  }
?>