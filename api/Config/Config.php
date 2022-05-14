<?php 
  function Config($api) {
    switch ($api->getAction()) {
      case 'setconfig_user': $result = $api->isValidAccesToken() ? setconfig_user($api) : $api->response("api_config_resp_error_msg", 500); break;
      case 'getconfig_user': $result = $api->isValidAccesToken() ? getconfig_user($api) : $api->response("api_config_resp_error_msg", 500); break;
      case 'setconfig_profile': $result = $api->isValidAccesToken() ? setconfig_profile($api) : $api->response("api_config_resp_error_msg", 500); break;
      case 'getconfig_profile': $result = $api->isValidAccesToken() ? getconfig_profile($api) : $api->response("api_config_resp_error_msg", 500); break;
      default: $result = $api->response("api_config_resp_error_msg", 500);  break;
    }
    return $result;
  }

  function getconfig_user($api) {
    $db = $api->getDb();
    $POST = $api->getPOST();
    $sql = "SELECT * FROM config_user WHERE username = '{$POST['user']}'";
    $user = $db->listar($sql);
    if (isset($user[0]->username)) {
      return $api->response("api_config_get_msg", 200, $user[0]);
    } else {
      return $api->response("api_config_get_error_msg", 500);
    }
  }

  function getconfig_profile($api) {
    $db = $api->getDb();
    $POST = $api->getPOST();
    $sql = "SELECT n.*, cp.* 
    FROM notification AS n 
    INNER JOIN config_profile AS cp ON cp.id_notification = n.id
    WHERE cp.profile = '{$POST['profile']}'";
    $user = $db->listar($sql);
    if (isset($user[0]->username)) {
      return $api->response("api_config_get_msg", 200, $user[0]);
    } else {
      return $api->response("api_config_get_error_msg", 500);
    }
  }

  function setconfig_user($api) {
    $db = $api->getDb();
    $POST = $api->getPOST();
    $sql = "SELECT * FROM config_user WHERE username = '{$POST['user']}'";
    $user = $db->listar($sql);
    if (isset($user[0]->username)) {
      $sql = "UPDATE config_user SET theme = '{$POST['theme']}', autoplay = '{$POST['autoplay']}', 
      columns = '{$POST['columns']}', orden = '{$POST['orden']}', ordenepi = '{$POST['ordenepi']}', 
      lang = '{$POST['lang']}', vol = '{$POST['vol']}', default_view = '{$POST['default_view']}', 
      avable_chat = '{$POST['avable_chat']}', avable_history = '{$POST['avable_history']}', 
      limit_elem_collection = '{$POST['limit_elem_collection']}', option_paginator = '{$POST['option_paginator']}' WHERE username = '{$POST['user']}'";
    } else {
      $sql = "INSERT INTO config_user( username, theme, autoplay, columns, orden, ordenepi, lang, vol, default_view, avable_chat, avable_history, limit_elem_collection, option_paginator) 
      VALUES('{$POST['user']}', '{$POST['theme']}', '{$POST['autoplay']}', '{$POST['columns']}', '{$POST['orden']}', '{$POST['ordenepi']}', '{$POST['lang']}', '{$POST['vol']}', '{$POST['default_view']}', '{$POST['avable_chat']}', '{$POST['avable_history']}', '{$POST['limit_elem_collection']}', '{$POST['option_paginator']}' )";
    }
    $inserted = $db->ejecutar($sql);
    if (isset($inserted)) {
      return $api->response("api_config_set_msg", 200, $user);
    } else {
      return $api->response("api_config_set_error_msg", 500);
    }
  }


  function setconfig_profile($api) {
    $db = $api->getDb();
    $POST = $api->getPOST();
    $sql = "SELECT * FROM config_profile WHERE profile = '{$POST['user']}'";
    $user = $db->listar($sql);
    if (isset($user[0]->username)) {
      $sql = "UPDATE config_profile SET theme = '{$POST['theme']}', autoplay = '{$POST['autoplay']}', 
      columns = '{$POST['columns']}', orden = '{$POST['orden']}', ordenepi = '{$POST['ordenepi']}', 
      lang = '{$POST['lang']}', vol = '{$POST['vol']}', default_view = '{$POST['default_view']}', 
      avable_chat = '{$POST['avable_chat']}', avable_history = '{$POST['avable_history']}', 
      limit_elem_collection = '{$POST['limit_elem_collection']}' WHERE username = '{$POST['user']}'";
    } else {
      $sql = "INSERT INTO config_profile( username, theme, autoplay, columns, orden, ordenepi, lang, vol, default_view, avable_chat, avable_history, limit_elem_collection) 
      VALUES('{$POST['user']}', '{$POST['theme']}', '{$POST['autoplay']}', '{$POST['columns']}', '{$POST['orden']}', '{$POST['ordenepi']}', '{$POST['lang']}', '{$POST['vol']}', '{$POST['default_view']}', '{$POST['avable_chat']}', '{$POST['avable_history']}', '{$POST['limit_elem_collection']}' )";
    }

    $inserted = $db->ejecutar($sql);
    if (isset($inserted)) {
      return $api->response("api_config_set_msg", 200, $user);
    } else {
      return $api->response("api_config_set_error_msg", 500);
    }
  }
?>