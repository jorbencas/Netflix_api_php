<?php 
    class Cache {
        public function getcache($module) {
            $file = "cache/$module.html";
            if (file_exists($file)) {
                return file_get_contents($file);
            } else {
                return null;
            }
                //return apc_fetch($key);
        }

        public function savecache($module, $obj) {
            if (!is_dir("cache")) mkdir("cache");
            $file = "cache/$module.html";
            if (file_exists($file)) {
                $file_time = filemtime($file)*(60*60*24);
            }
            if (!file_exists($file) || (isset($file_time) && $file_time < time())) {
                file_put_contents($file, $obj);
            }

               // apc_add($key, $value, $expiration);
        }
    }
?>