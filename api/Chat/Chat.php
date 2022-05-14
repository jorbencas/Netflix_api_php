<?php 
  function Chat($api) {
    switch ($api->getAction()) {
      case 'insertmessage': $result = $api->isValidAccesToken() ? insertmessage($api) : $api->response("api_User_resp_error_msg", 404); break;
      case 'listmessages': $result = $api->isValidAccesToken() ? listmessages($api) : $api->response("api_User_resp_error_msg", 404); break;
      default: $result = $api->response("api_User_resp_error_msg", 404);  break;
    }
    return $result;
  }

  function insertmessage($api) {
    $db = $api->getDb();
    $POST = $api->getPOST();
    $date = date("Y-m-d");
    $hour = date("H:i:s");
    $sql =  "INSERT INTO chat(msg_text,emiitter, receptor, date, hour) VALUES('{$POST['msg_text']}',  '{$POST['user']}', '{$POST['receptor']}', '$date', '$hour')";
    $inserted = $db->ejecutar($sql);
    if ($inserted) {
      $sql = "SELECT * FROM chat WHERE emiitter = '{$POST['user']}' AND hour = '$hour'";
      $user = $db->listar($sql);
      if (isset($user[0]->id)) {
        return $api->response("api_Anime_ordAsc_msg", 200, $user);
      } else {
        return $api->response("api_User_error_msg", 404); 
      }
    } else {
      return $api->response("api_User_error_msg", 404);
    }
  }

  function listmessages($api) {
    $db = $api->getDb();
    $POST = $api->getPOST();
    $sql = "SELECT * 
    FROM chat 
    WHERE emiitter = '{$POST['user']}' AND receptor = '{$POST['receptor']}' OR 
    receptor = '{$POST['user']}' AND emiitter = '{$POST['receptor']}'";
    $user = $db->listar($sql);
    if (isset($user[0]->id)) {
      return $api->response("api_Anime_ordAsc_msg", 200, $user);
    } else {
      return $api->response("api_User_error_msg", 404); 
    }
  }
?>