<?php
    trait Validators {
        public function isImage($extension) {
            $valid = false;
            $extension = strtolower($extension);
            switch ($extension) {
                case 'jpg': case 'gif': case 'png': case 'jpeg': 
                    $valid = true; 
                    break;
                default: $valid = false; break;
            }
            return $valid;
        }
    
        public function isDocument($extension) {
            $extension = strtolower($extension);
            switch ($extension) {
                case 'pdf': $valid = true; break;
                default: $valid = false; break;
            }
            return $valid;
        }
    
        public function isVideo($extension) {
            $extension = strtolower($extension);
            switch ($extension) {
                case 'mp4': case 'webm': $valid = true; break;
                default: $valid = false; break;
            }
            return $valid;
        }
    
        public function isMusic($extension) {
            $valid = false;
            $extension = strtolower($extension);
            if ($extension == 'mp3'){
                $valid = true;
            }
            return $valid;
        }

        public function isAjax() {
            $valid = false;
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                $valid = true;
            }
            return $valid;
        }
    
        public function isLocalHost() {
            $valid = false;
            if (isset($_SERVER['REMOTE_ADDR']) 
            && ( $_SERVER['REMOTE_ADDR'] == $_SERVER['SERVER_ADDR'] )) {
                $valid = true;
            }
            return $valid;
        }
    }
?>