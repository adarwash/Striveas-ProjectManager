<?php 

    // Version: Alpha 1.0

    class Functions {
        // Function for loading CSS
        public static function loadCSS($folder){
            $files = glob($_SERVER['DOCUMENT_ROOT']. $folder . '/*.css');
            $output = '';
            foreach ($files as $file){
                $fileName = basename($file);
                $output .= "<link rel='stylesheet' href='$folder/$fileName'>\n";
            }
            return $output;
        }

        // Function for loading JS
        public static function loadJS($folder) {
            $files = glob($_SERVER['DOCUMENT_ROOT'] . $folder . '/*.js');
            $output = '';
            foreach ($files as $file) {
                $fileName = basename($file);
                $output .= "<script src='$folder/$fileName'></script>\n";
            }
            return $output;
        }
    }



?>