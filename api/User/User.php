<?php
function User($api)
{
  switch ($api->getAction()) {
    case 'Login':
      $result = Login($api);
      break;
    case 'Register':
      $result = Register($api);
      break;
    case 'logout':
      $result = $api->isValidAccesToken() ? logout($api) : $api->response("api_Auth_resp_error_msg", 404);
      break;
    case 'updateUser':
      $result = $api->isValidAccesToken() ? updateUser($api) : $api->response("api_User_resp_error_msg", 404);
      break;
    case 'apione':
      $result = /*$api->isValidAccesToken() ?*/ getOneUser($api) /*: $api->response("api_User_resp_error_msg", 404)*/;
      break;
    case 'apiby':
      $result = $api->isValidAccesToken() ? getalluser($api) : $api->response("api_User_resp_error_msg", 404);
      break;
    default:
      $result = $api->response("api_User_resp_error_msg", 404);
      break;
  }
  return $result;
}

function getOneUser($api)
{
  $db = $api->getDb();
  $GET = $api->getGET();
  $sql = "SELECT u.* FROM users AS u WHERE u.username = '{$GET['ap']}'";
  $user = $db->obtener_uno($sql);
  if (isset($user->username)) {
    return $api->response("api_Anime_ordAsc_msg", 200, $user);
  } else {
    return $api->response("api_User_error_msg", 404);
  }
};

function getalluser($api)
{
  $db = $api->getDb();
  $GET = $api->getGET();
  $sql = "SELECT u.* FROM users AS u WHERE u.username NOT LIKE '{$GET['aa']}'";
  $user = $db->listar($sql);
  if (isset($user[0]->username)) {
    return $api->response("api_Anime_ordAsc_msg", 200, $user);
  } else {
    return $api->response("api_User_error_msg", 404);
  }
};

function updateUser($api)
{
  $db = $api->getDb();
  $POST = $api->getPOST();
  $sql = "UPDATE users SET nombre = '{$POST['nombre']}', apellidos = '{$POST['apellidos']}', email = '{$POST['email']}',
    date_birthday = '{$POST['date_birthday']}', dni = '{$POST['dni']}', genere = '{$POST['genere']}'
    WHERE username = '{$POST['username']}'";
  $db->ejecutar($sql);

  $sql = "SELECT u.* FROM users AS u WHERE u.username = '{$POST['username']}'";
  $user = $db->obtener_uno($sql);
  if (isset($user->username)) {
    return $api->response("api_Anime_ordAsc_msg", 200, $user);
  } else {
    return $api->response("api_User_error_msg", 404);
  }
};

function Login($api)
{
  $POST = $api->getPOST();
  if (!isset($POST['username']) || !isset($POST['passwd'])) {
    return $api->response("api_Login_error_msg", 404);
  } else {
    $db = $api->getDb();
    $sql = "SELECT username, tipo FROM users WHERE username = '{$POST['username']}' AND password = '{$POST['passwd']}'";
    $user = $db->listar($sql);
    if (isset($user[0]->username)) {
      $user_login = get_object_vars($user[0]);
      $mensage = "El usuario: {$user_login['username']} ha iniciado sessión.";
      $kind = "Inicio de sessión";
      $api->writeFile(array("kind" => $kind, "message" => $mensage), "log");
      return $api->response("api_Login_msg", 200, $user_login);
    } else {
      return $api->response("api_Login_error_msg", 404);
    }
  }
}

function Register($api)
{
  $POST = $api->getPOST();
  if (!isset($POST['username']) || !isset($POST['passwd'])) {
    return $api->response("api_signup_error_msg", 404);
  } else {
    $db = $api->getDb();
    $usuario = isset($POST['username']) ? $POST['username'] : "jorge";
    $nombre = isset($POST['nombre']) ? $POST['nombre'] : "jorge";
    $email = isset($POST['email']) ? $POST['email'] : "pepito@gmail.com";
    $password = isset($POST['passwd']) ? $POST['passwd'] : "676576576765";
    $genere = isset($POST['sexo']) ? $POST['sexo'] : "H";
    $activado = isset($POST['activado']) ? $POST['activado'] : 1;
    $sql = "SELECT username FROM users WHERE username = '$usuario' AND password = '$password' ";
    $user = $db->listar($sql);
    if (!$user) {
      $sql = "INSERT INTO users (username, nombre, apellidos, email, password"
        . " , date_birthday, tipo, dni, token, activado, genere"
        . " ) VALUES ('$usuario', '$nombre', '', '$email','$password',"
        . " '', 'admin','','', '$activado', '$genere')";
      $inserted = $db->ejecutar($sql);
      if (isset($inserted)) {
        $sql = "SELECT username, tipo FROM users WHERE username = '{$POST['username']}' AND password = '{$POST['passwd']}' ";
        $user = $db->listar($sql);
        if (isset($user[0]->username)) {
          $user_login = get_object_vars($user[0]);
          $acces_token = md5($user[0]->username . $api->getApiToken());
          $at = "";
          if ($user_login['tipo'] == "admin") {
            $admin_token = md5($user_login['acces_token'] . "administrator" . date("H:i:s"));
            $at .= ", admin_token = $admin_token";
          }
          $sql = "UPDATE users SET acces_token = '{$acces_token}' $at WHERE username = '{$POST['username']}'";
          $inserted = $db->ejecutar($sql);
          $mensage = "El usuario: {$user_login['username']} ha iniciado sessión.";
          $kind = "Inicio de sessión";
          $api->writeFile(array("kind" => $kind, "message" => $mensage), "log");
          return $api->response("api_Login_msg", 200, $user_login);
        } else {
          return $api->response("api_signup_error_msg", 404);
        }
      } else {
        return $api->response("api_signup_error_msg", 404);
      }
    } else {
      return $api->response("api_auth_exist_msg", 200);
    }
  }
}

function logout($api)
{
  $POST = $api->getPOST();
  if (!isset($POST['username'])) {
    return $api->response("api_logout_error_msg", 404);
  } else {
    $db = $api->getDb();
    $sql = "SELECT username FROM users WHERE username = '{$POST['username']}'";
    $user = $db->listar($sql);
    if (isset($user[0]->username)) {
      $mensage = "El usuario: {$user[0]->username} se a desconectado.";
      $kind = "Cierra de sessión";
      $api->writeFile(array("kind" => $kind, "message" => $mensage), "log");
      return $api->response("api_logout_msg", 200);
    } else {
      return $api->response("api_logout_error_msg", 404);
    }
  }
}
