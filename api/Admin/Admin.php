<?php
    function Admin($api) {
        $GET = $api->getGET();
        switch ($api->getAction()) {
            case 'filesystem': $result = fileSystem($api, $GET); break;
            case 'recover': $result = recover($api); break;
            case 'backup': $result = dobackup($api); break;
            case 'query': $result = function_exists($GET['aq']) ? $GET['aq']($api) : $api->response("api_Anime_resp_error_msg", 404); break;
            default: $result = $api->response("api_Anime_resp_error_msg", 404); break;
        }
        return $result;
    };

    function fileSystem($api, $GET){
        $POST = $api->getPOST();
        $path = $POST["path"];
        $scanedFiles = $api->scanFolders($path, false, true);
        if (sizeof($scanedFiles) > 0) {
            foreach ($scanedFiles as $list_sub) {
                $file = "$path/$list_sub";
                if (is_dir($file)) {
                    $kindfile = "dir";
                    $filesize = '0';
                } else {
                    $_filesize = filesize($file);
                    $_filesize = $_filesize / 1024;
                    $_filesizetext = "Kb"; //kb
                    if ($_filesize > 1000) {
                        $_filesize = $_filesize / 1024;
                        $_filesizetext = "Mb";
                    } //Mb
                    $_filesize = round($_filesize, 1, PHP_ROUND_HALF_UP);
                    $filesize = "$_filesize $_filesizetext";
                    $file_exp = explode(".",$file);
                    $ext = $file_exp[1];
                    if ($api->isVideo($ext)) {
                        $kindfile = "video";
                    } else if ($api->isImage($ext)) {
                        $kindfile = "img";
                    } else {
                        $kindfile = "zip";
                    }
                }
                if (strstr($path,"%20")) {
                    $path = str_replace("%20", " ", $path);
                }
                array_push($array_data,(object) array(
                    'file_kind' => $kindfile, 'file' => $list_sub, 'path' => $file, 'size' => $filesize
                ));
            }
            return $api->response("api_recover_msg", 200, $array_data);
        } else {
            return $api->response('No se ha podido listar elementos contenidos en la carpeta media, ya que no existe.', 404);
        }
    }

    function gettables($api) {
        $db = $api->getDb();
        $array_data = array();
        // $sql = "SELECT tablename FROM pg_catalog.pg_tables
        // WHERE schemaname != 'pg_catalog' AND schemaname != 'information_schema'";
        // $data = $db->listar($sql);
        $scanedFiles = $api->scanFolders("media/animes", false, true);
        if (sizeof($scanedFiles) > 0) {
            foreach ($scanedFiles as $val) {
                $id = $db->obtener_una_columna("SELECT id FROM animes WHERE siglas = '$val'");
                if ($id) {
                    $params = array('siglas' => $val ,'class' => 'active');
                } elseif (file_exists("sql/animes/$val.php") 
                || file_exists("sql/animes/$val.sql")
                || file_exists("sql/animes/nuevos/$val.php")
                || file_exists("sql/animes/nuevos/$val.sql")) {
                    $params = array('siglas' => $val,'class' => 'pause');
                } else {
                    $params = array('siglas' => $val,'class' => 'disabled');
                }
                array_push($array_data, $params);
            }
        }
        return $api->response("api_recover_msg", 200, $array_data, array('code'=>'s','text'=>'todobien'));
    }
    
    function gettables($api){
        $db = $api->getDb();
        $array_data = array();
        $sql = "SELECT tablename FROM pg_catalog.pg_tables
        WHERE schemaname != 'pg_catalog' AND schemaname != 'information_schema'";
        $data = $db->listar($sql);

        foreach ($data as $key => $table) {
            array_push($array_data, (object) array(
                'name' => ucfirst($table->tablename),
                "tabla" => $table->tablename
            ));
        }
        return $api->response("api_recover_msg", 200, $array_data);
    }

    function getdata($api){
        $db = $api->getDb();
        $POST = $api->getPOST();
        $table = $POST['table'];
        $sql = "SELECT * FROM $table";
        $t = $db->listar($sql);  
        $delete = isset($t[0]->id) ? false : true;
        $array_data = (object) array(
            "tabla" => $table,
            "deleted" => $delete
        );
        $get_filesystem = $api->scanfolders("backup");
        if (isset($get_filesystem)) {
            if (is_dir("backup/{$table}") || file_exists("backup/{$table}.sql")) {
                foreach ($get_filesystem as $key => $array) {
                    $files = explode("/",$array);
                    $backup_file = explode(".",end($files))[0];
                    if (strstr($backup_file, trim($table))) {
                        if (!isset($array_data->attr)) {
                            $array_data->attr = array();
                        }
                        array_push($array_data->attr, (object) array( 
                            "src" => $array
                        ));
                    }
                }
                if (isset($array_data->attr) && sizeof($array_data->attr) > 0) {
                    //Ordenar los archivos mediante su ruta para su posterior utilización
                    usort($array_data->attr, $api->object_sorter("src"));
                }
            }
            return $api->response("api_recover_msg", 200, $array_data);
        } else {
            return $api->response("api_recover_error_msg", 404);
        }
    }

    function recover($api){
        $db = $api->getDb();
        $POST = $api->getPOST();
        $table = $POST['table'];
        if (isset($POST["src"]) && file_exists($POST["src"]) && strstr($POST["src"],".json")) {
            $inserts = json_decode(json_encode(file_get_contents($POST["src"])));
            foreach ($inserts as $k => $insert) {
                $sql = "SELECT id FROM $table WHERE id = {$insert['id']}";
                $t = $db->listar($sql);  
                $keys = "";
                $values = "";
                $ney = 0;
                foreach ($insert as $key => $value) {
                    if(isset($t[0]->id)){
                        $sql = "SELECT $value FROM $table WHERE id = {$t[0]->id}";
                        $db_value = $db->listar($sql);
                        if($db_value->$key !== $value){
                            $sql = "UPDATE $table SET = $value WHERE id = {$t[0]->id}";
                            $db->ejecutar($sql);
                        }
                    } else {
                        $obj = handle_keys_values($ney, $key, $value, $keys, $values, $inserts);
                        $keys = $obj['keys'];
                        $values = $obj['values'];
                        $ney = $ney + 1;
                    }
                }
                if (!empty($keys) && !empty($values)) {
                    $sql = "INSERT INTO $table ($keys) VALUES($values);";
                    $db->ejecutar($sql);
                }
            }
        } elseif (isset($POST["src"]) && file_exists($POST["src"]) && strstr($POST["src"],".sql"))  {
            if($POST['deleted'] == false){
                $sql = "DELETE FROM {$POST["tabla"]}";
                $db->ejecutar($sql);
                $POST['deleted'] = true;
            }
            $delimiter = ");";
            $inserts = explode($delimiter,file_get_contents($POST["src"]));
            array_pop($inserts);
            foreach ($inserts as $key => $insert) {
                $db->ejecutar($insert.$delimiter);
            }
            return $api->response("api_recover_msg", 200, "ok");
        } else {
            return $api->response("api_recover_error_msg", 404);
        }
    };

    function dobackup($api){
        $db = $api->getDb();
        $POST = $api->getPOST();
        $tabla = $POST['tabla'];
        $sql = "SELECT * FROM $tabla";
        $result[$tabla] = $db->listar($sql);
        if(isset($result[$tabla])){
            $base_folder = "backup2";
            $path = "$base_folder/$tabla.sql";
            // $path2 = "$base_folder/$tabla.json";
            $save_backup = "";
            // $save_json = array();
            $num = 0;
            $limit = 50;
            $max_num = $limit;
            foreach ($result[$tabla] as $k => $tables) {
                $keys = "";
                $values = "";
                $ney = 0;
                // $arr = array();
                foreach ($tables as $key => $value) {
                    $obj = handle_keys_values($ney, $key, $value, $keys, $values, $tables);
                    $keys = $obj['keys'];
                    $values = $obj['values'];
                    //$key = in_array($key,$special_keys) ? '"'.$key.'"' : $key;
                    //$arr[$key] = $value;
                    $ney = $ney + 1;
                }

                if (/*count($arr) > 0 &&*/ !empty($keys) && !empty($values)) {
                    $save_backup .= "INSERT INTO $tabla ($keys) VALUES($values);\n";
                    // array_push($save_json,$arr);
                    if ($k == $max_num) {      
                        if($num > 0 ){
                            $path = "$base_folder/$tabla/$tabla$num.sql";
                            // $path2 = "$base_folder/$tabla/$tabla$num.json";
                        } else {
                            $path = "$base_folder/$tabla/$tabla.sql";
                            // $path2 = "$base_folder/$tabla/$tabla.json";
                        }
                        if(!is_dir("$base_folder")) mkdir("$base_folder");
                        if(!is_dir("$base_folder/$tabla/")) mkdir("$base_folder/$tabla");
                        if(!file_exists($path)) file_put_contents($path, $save_backup);
                        // if(!file_exists($path2)) write_file($save_json,'',$path2);
                        $max_num = $max_num + $limit;
                        $num = $num + 1;
                        $save_backup = "";
                        // $save_json = array();
                    } else if($k == (count($result[$tabla]) - 1 ) && $max_num > $limit) {
                        $path = "$base_folder/$tabla/$tabla$num.sql";
                        $path2 = "$base_folder/$tabla/$tabla$num.json";
                        if(!file_exists($path)) file_put_contents($path, $save_backup);
                        // if(!file_exists($path2)) write_file($save_json,'',$path2);
                        $save_backup = "";
                        // $save_json = array();
                    } else if($k == (count($result[$tabla]) - 1 ) && $max_num == $limit){
                        if(!is_dir("$base_folder")) mkdir("$base_folder");
                        if(!file_exists($path)) file_put_contents($path, $save_backup);
                        // if(!file_exists($path2)) write_file($save_json, '',$path2);
                        $save_backup = "";
                        // $save_json = array();
                    }
                }
            }
            return $api->response("api_backup_msg", 200, $result);
        } else {
            return $api->response("api_backup_error_msg", 404);
        }
    }

    function handle_keys_values($ney, $key, $value, $keys, $values, $tables){
        if($ney < (count((array) $tables) - 1)) {
            $keys .= '"'.$key.'"'.', ';
            $values .= "'$value', ";
        } elseif($ney < (count((array) $tables) - 1)) {
            $keys .= "$key, ";
            $values .= "'$value', ";
        } else {
            $keys .= $key;
            $values .= "'$value' ";
        }
        $obj['keys'] = $keys;
        $obj['values'] = $values;
        return $obj;
    }

    function geterrors($api) {
        $db = $api->getDb();
        $result_array = array();
        $file = "../../files/error_media.txt";
        if (file_exists($file)) unlink($file);
        $sql = "SELECT a.id, a.siglas FROM animes AS a ORDER BY a.siglas";
        $res = $db->listar($sql);
        if (count($res) > 0) {
            foreach ($res as $anime) {
                $value = $anime->siglas;
                $id = $anime->id;
                $scanedFiles = $api->scanFolders("media/animes/$value");
                if (sizeof($scanedFiles) > 0) {
                    foreach ($scanedFiles as $key => $array) {
                        $sub_folder = explode("/",$array);
                        $kind = $sub_folder[3];
                        $sql = "SELECT m.name, m.extension, m.type FROM media AS m WHERE m.type = $kind AND m.anime = $id ";
                        $res = $db->listar($sql);
                        if (count($res) > 0) {
                            foreach ($res as $key => $sub) {
                                if (!file_exists($array)) {
                                    $string = "$array \n";
                                    array_push($result_array, $string);
                                    $api->writeFile($string,'error',$file,);
                                }
                            }
                        } else {
                            $string = "\t$array \n";
                            array_push($result_array, $string);
                            $api->writeFile($string,'error',$file);
                        }
                    };
                } else {
                    return $api->response("api_Metadata_error_msg", 404);
                    break;
                }
            }
            $res = json_encode($result_array, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
            return $api->response("api_Metadata_msg", 200, $res);
        } else {
            return $api->response("api_Metadata_error_msg", 404);
        }
    }
?>