<?php
Api::_include_once('./util/zip.php');
class Download{
    public static function zip_file(){
        $file_name = Zipp::file_name();
        if(file_exists($file_name)){
            //Set Headers:
            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($file_name)) . ' GMT');
            header('Content-Type: application/force-download');
            header('Content-Disposition: inline; filename="'.$file_name.'"');
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . filesize($file_name));
            header('Connection: close');
            readfile($file_name);
            if(file_exists($file_name)){
                unlink($file_name);
            }
            exit();
        }
    }
}
