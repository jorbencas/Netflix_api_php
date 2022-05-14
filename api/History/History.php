<?php 
  function History($api) {
    $GET = $api->getGET();
    switch ($api->getAction()) {
      case 'deletetelementbyepisode': $result = $api->isValidAdminToken() ? deletetelementbyepisode($api) : $api->response("api_history_resp_error_msg", 404); break;
      case 'deletehistory': $result = $api->isValidAccesToken() ? deletehistory($api) : $api->response("api_history_resp_error_msg", 404); break;
      case 'sethistory': $result = $api->isValidAccesToken() ? sethistory($api) : $api->response("api_history_resp_error_msg", 404); break;
      case 'deletelement': $result = $api->isValidAccesToken() ? deletelement($api) : $api->response("api_history_resp_error_msg", 404); break;
      case 'gethistory': $result = $api->isValidAccesToken() ? gethistory($api) : $api->response("api_history_resp_error_msg", 404); break;
      case 'query': $result = function_exists($GET['aq']) ? $GET['aq']($api) : $api->response("api_history_resp_error_msg", 404); break;
      default: $result = $api->response("api_history_resp_error_msg", 404);  break;
    }
    return $result;
  }

  function gethistory($api) {
    $db = $api->getDb();
    $POST = $api->getPOST();
    $sql = "SELECT h.*, m.type, m.name, m.extension, a.siglas, a.titulo_es as anime_titulo_es, 
    a.titulo_en as anime_titulo_en, a.titulo_va as anime_titulo_va, a.titulo_ca as anime_titulo_ca,
    e.titulo_es, e.titulo_en, e.titulo_va, e.titulo_ca
    FROM history AS h inner join episodes AS e ON(h.episode_id = e.id)
    INNER JOIN animes AS a ON(e.anime = a.id)
    INNER JOIN media AS m ON m.anime = a.id
    WHERE m.type = 'portada' AND h.profile = '{$POST['profile']}'";
    $user = $db->listar($sql);
    if (isset($user[0]->username)) {
      foreach ($user as $value) {
        $media = $value->name;
        $ext = $value->extension;
        $value->src = $api->handleMedia($value->type,$media,$ext,$value->siglas);
        unset($value->type);
        unset($value->name);
        unset($value->extension);
        $user[$key] = $value;
      }
      return $api->response("api_history_get_msg", 200, $user);
    } else {
      return $api->response("api_history_get_error_msg", 404);
    }
  } 

  function sethistory($api) {
    $db = $api->getDb();
    $POST = $api->getPOST();
    $sql = "INSERT INTO history(episode_id, profile) VALUES('{$POST['episode_id']}', '{$POST['profile']}')";
    $user = $db->listar($sql);
    if (isset($user[0]->username)) {
      return $api->response("api_history_set_msg", 200, $user);
    } else {
      return $api->response("api_history_set_error_msg", 404);
    }
  }

  function deletehistory($api) {
    $db = $api->getDb();
    $POST = $api->getPOST();
    $sql = "DELETE FROM history WHERE profile = '{$POST['profile']}'";
    $deleted = $db->ejecutar($sql);
    if (isset($deleted)) {
      return $api->response("api_history_delete_msg", 200, $deleted);
    } else {
      return $api->response("api_history_delete_error_msg", 404);
    }
  }

  function deletelement($api) {
    $db = $api->getDb();
    $POST = $api->getPOST();
    $sql = "DELETE FROM history WHERE id = '{$POST['id']}'";
    $deleted = $db->ejecutar($sql);
    if (isset($deleted)) {
      return $api->response("api_history_delete_msg", 200, $deleted);
    } else {
      return $api->response("api_history_delete_error_msg", 404);
    }
  }

  function deletetelementbyepisode($api) {
    $db = $api->getDb();
    $POST = $api->getPOST();
    $sql = "DELETE FROM history WHERE episode_id = '{$POST['id']}'";
    $deleted = $db->ejecutar($sql);
    if (isset($deleted)) {
      return $api->response("api_history_delete_msg", 200, $deleted);
    } else {
      return $api->response("api_history_delete_error_msg", 404);
    }
  }
?>