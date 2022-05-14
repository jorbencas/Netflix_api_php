<?php 
    function Profiles($api) {
        switch ($api->getAction()) {
            case 'get_profile': $result = get_profile($api); break;
            case 'getprofilesbyuser': $result = getprofilesbyuser($api); break;
            case 'inserteditprofile': $result = inserteditprofile($api); break;
            case 'setprofile': $result = setprofile($api); break;
            default: $result = $api->response("api_Profiles_resp_error_msg", 404); break;
        }
        return $result;
    }

    function setprofile($api) {
        $db = $api->getDb();
        $POST = $api->getPOST();
        if (isset($POST['id_profile'])) {
            $sql = "SELECT id FROM profiles WHERE id = '{$POST['id_profile']}'";
            $profile = $db->obtener_uno($sql);
            return $api->response("api_Profiles_profile_msg", 200, $profile);
        } else {
            return $api->response("api_Profiles_profile_error_msg", 404); 
        }
    }

    function get_profile($api) {
        $db = $api->getDb();
        $POST = $api->getPOST();
        $sql = "SELECT p.id, p.nombre, p.username, m.type, m.name, m.extension   
        FROM profiles AS p LEFT JOIN media AS m ON(m.profile = p.id)
        WHERE p.username LIKE '{$POST['username']}' AND p.id = '{$POST['profile']}'
        AND m.type = 'profiles'";
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
            return $api->response("api_Profiles_profile_msg", 200, $user);
        } else {
            return $api->response("api_Profiles_profile_error_msg", 404); 
        }
    }

    function getprofilesbyuser($api) {
        $db = $api->getDb();
        $POST = $api->getPOST();
        $sql = "SELECT p.id, p.nombre, p.username  
        FROM profiles AS p 
        WHERE p.username LIKE '{$POST['username']}'";
        $user = $db->listar($sql);
        if (isset($user[0]->username)) {
            foreach ($user as $value) {
                // $media = $value->name;
                // $ext = $value->extension;
                // $value->src = handleMedia($value->type,$media,$ext,$value->siglas);
                $value->capital_letter = substr(ucfirst($value->nombre),0,1);
                // unset($value->type);
                // unset($value->name);
                // unset($value->extension);
            }
            return $api->response("api_Profiles_profile_user_msg", 200, $user);
        } else {
            return $api->response("api_Profiles_profile_user_error_msg", 404); 
        }
    }

    function inserteditprofile($api) {
        $db = $api->getDb();
        $POST = $api->getPOST();
        $sql = "SELECT id FROM profiles WHERE profile LIKE '{$POST['profile']}' AND username LIKE '{$POST['username']}'";
        $profile = $db->obtener_uno($sql);
        if (isset($profile->id)) {
            $id = $profile->id;
            $sql = "UPDATE profiles SET nombre = '{$POST['nombre']}' WHERE id = '$id'";
            $db->ejecutar($sql);
            return $api->response("api_Profiles_update_msg", 200, $profile);
        } else {
            $sql = "INSERT INTO profiles(nombre, username) VALUES('{$POST['profile']}','{$POST['username']}')";
            $db->ejecutar($sql);
            //$avatar = get_gravatar($email, $s = 80, $d = 'wavatar', $r = 'g', $img = false, $atts = array()) ;
            // if (isset($POST['avatar'])) {
            //     $avatar = $POST['avatar'];
            // } else {
            //     $avatar = get_gravatar($email, $s = 80, $d = 'wavatar', $r = 'g', $img = false, $atts = array()) ;
            // }
            
            $mensage = "El usuario con el perfil: {$POST['profile']} ha iniciado sessión.";
            $kind = "Inicio de sessión";
            $api->writeFile(array("kind" => $kind, "message" => $mensage),"log");
            $sql = "SELECT id FROM profiles WHERE profile LIKE '{$POST['profile']}' AND username LIKE '{$POST['username']}'";
            $profile = $db->obtener_uno($sql);
            return $api->response("api_Profiles_insert_msg", 200, $profile);
        }
    }

    function get_gravatar($email, $s = 80, $d = 'wavatar', $r = 'g', $img = false, $atts = array()) {
        $email = trim($email);
        $email = strtolower($email);
        $email_hash = md5($email);

        $url = "http://www.gravatar.com/avatar/" . $email_hash;
        $url .= md5(strtolower(trim($email)));
        $url .= "?s=$s&d=$d&r=$r";
        if ($img) {
            $url = '<img  src="' . $url . '"';
            foreach ($atts as $key => $val)
                $url .= ' ' . $key . '="' . $val . '"';
            $url .= ' />';
        }
        return $url;
    }
?>