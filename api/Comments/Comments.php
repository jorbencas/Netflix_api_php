<?php
    function Comments($api) {
        $GET = $api->getGET();
        switch ($api->getAction()) {
            case "deleteOnecomment": $result = $api->isValidAdminToken() ? deleteOnecomment($api, $headers) : $api->response("api_Comments_resp_error_msg", 404); break;
            case 'insertcomment': $result = $api->isValidAdminToken() ? insertcomment($api, $headers) : $api->response("api_Comments_resp_error_msg", 404); break;
            case 'apione': $result = $api->isValidAccesToken() ? getcommentbyuser($api) : $api->response("api_Comments_resp_error_msg", 404); break;
            case 'apiby': $result = $api->isValidAccesToken() ? getcommentbyepisode($api) : $api->response("api_Comments_resp_error_msg", 404); break;
            case 'query': $result = $api->isValidAccesToken() && function_exists($GET['aq']) ? $GET['aq']($api) : $api->response("api_Comments_resp_error_msg", 404); break;
            default: $result = $api->response("api_Comments_resp_error_msg", 404);  break;
        }
        return $result;
    }

    function insertcomment($api) {
        $db = $api->getDb();
        $POST = $api->getPOST();
        $date = date("Y-m-d");
        $hour = date("H:i:s");
        $episode = $POST['episode'] !== '' ? $POST['episode'] : null;
        $anime = $POST['anime'] !== '' ? $POST['anime'] : null;
        $manga = $POST['manga'] !== '' ? $POST['manga'] : null;
        $sql = "INSERT INTO comments(comment, fecha, hora, username, episode, anime, manga) VALUES('{$POST['comment']}', '$date', '$hour', '{$POST['username']}', '$episode', '$anime', '$manga')";
        $comments = $db->ejecutar($sql);
        if ($comments) {
            if ($POST['episode'] !== '') $column = 'episode';
            else if ($POST['anime'] !== '') $column = 'anime';
            else if ($POST['manga'] !== '') $column = 'manga';
            $sql = "SELECT * FROM comments WHERE $column = '{$POST["$column"]}'";
            $comments = $db->listar($sql);
            foreach ($comments as $key => $value) {
                $value = get_object_vars($value);
                $data = $api->apiReq("User&ap={$value['username']}"); 
                $value['avatar'] = $data['data'][0]['avatar'];
                $comments[$key] = $value;
            }
            return $api->response("api_Comments_insert_msg", 200, $comments);
        } else {
            return $api->response("api_Comments_insert_error_msg", 404);
        }
    };

    function deleteOnecomment($api) {
        $db = $api->getDb();
        $POST = $api->getPOST();
        $sql = "DELETE FROM comments WHERE id = '{$POST['id']}'";
        $comments = $db->ejecutar($sql);
        if ($comments) {
            $user = $POST['username'];
            $sql = "SELECT * FROM comments WHERE username = '$user'";
            $comments = $db->listar($sql);
            if (count($comments) > 0) {
                foreach ($comments as $key => $value) {
                    $value = get_object_vars($value);
                    $data = $api->apiReq("User&ap={$value['username']}"); 
                    $value['avatar'] = $data['data'][0]['avatar'];
                    $comments[$key] = $value;
                }
                return $api->response("api_Comments_insert_msg", 200, $comments);
            } else {
                return $api->response("api_Comments_user_error_msg", 404);
            }
        } else {
            return $api->response("api_Comments_insert_error_msg", 404);
        }
    };

    function getcommentbyepisode($api) {
        $db = $api->getDb();
        $GET = $api->getGET();
        $sql = "SELECT * FROM comments WHERE episode = '{$GET['aa']}'";
        $comments = $db->listar($sql);
        if (isset($comments[0]->id)) {
            return $api->response("api_Comments_episode_msg", 200, $comments);
        } else {
            return $api->response("api_Comments_episode_error_msg", 404);
        }
    };

    function getcommentbyuser($api) {
        $db = $api->getDb();
        $GET = $api->getGET();
        $sql = "SELECT * FROM comments WHERE username = '{$GET['ap']}'";
        $comments = $db->listar($sql);
        if (isset($comments[0]->id)) {
            return $api->response("api_Comments_user_msg", 200, $comments);
        } else {
            return $api->response("api_Comments_user_error_msg", 404);
        }
    };
?>