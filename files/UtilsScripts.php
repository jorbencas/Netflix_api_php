<?php 
    class UtilsScripts {
        
        public function removeFolders($dir) {
            if (file_exists($dir)) {
                $folders = array_diff(scandir($dir),array("..","."));
                if (sizeof($folders) > 0) {
                    foreach ($folders as $file) {
                        if(is_dir("$dir/$file")){
                            $this->removeFolders("$dir/$file");
                        } else{
                            unlink("$dir/$file");
                        } 
                    }
                    return rmdir($dir);
                } else{
                    //return rmdir($dir);
                }
            }
        }
        
        public function object_sorter($clave, $orden=null) {
            return function ($a, $b) use ($clave,$orden) {
                $ac = is_object($a) ? $a->$clave : $a[$clave];
                $bc = is_object($b) ? $b->$clave : $b[$clave];  
                return ($orden=="DESC") ? strnatcmp($bc, $ac) : strnatcmp($ac, $bc);
            };
        }

        public function array_clone($array) {
            return array_map(function($element) {
                return ((is_array($element))
                    ? $this->array_clone($element)
                    : ((is_object($element))
                        ? clone $element
                        : $element
                    )
                );
            }, $array);
        }

        public function getNumDaysMonth($date) {
            $timeEsp = trim($date) != "" ? str_replace("/", "-", $date) : "now";
            return (int)date("t",strtotime($timeEsp));
        }

        public function extractZIP($path, $name) {
            $resource = $path . $name . '.zip';
            try {
                if (file_exists($resource)) {
                    exec("unzip $resource ");
                    return true;
                } else {
                    return false;
                }
            }catch (\Throwable $th) {
                echo $th;
                return false;
            }
        }
    
        public function comprimZIP($path, $name) {
            $resource =  $path . $name . ".zip";
            try {
                exec ("zip $resource $path . $name");
                return true;
            }catch (\Throwable $th) {
                echo $th;
                return false;
            }
        }

        /*Genera codigo hexadecimal de 32 cartacteres para clients_id*/
        public function generateHex32Dec(){
            return bin2hex(random_bytes(32));
        }

        public function getNombreFichero(){
            return pathinfo(__FILE__, PATHINFO_FILENAME);
        }

        public function getTableColumns($db,$tabla){
            $sql = "select column_name 
            from information_schema.columns 
            where table_name = '$tabla';";
            //echo $sql . "\n";
            return $db->listar($sql);
        }
    }
?>