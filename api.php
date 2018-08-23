<?php
class Api {
    public static $config;
    public static $enums;
    public static function _init($init='./files.ini',$file_name='./util/file_name.php'){
        $config = json_decode(json_decode(json_encode(file_get_contents($init))));
        include_once($config->api->base_dir.$file_name);
        new File_name();
        Api::$enums = json_decode(utf8_encode(Api::_file_get_resouce("datasets/factura_rules.json")),true);
    }
    public static function _include_once($file_name){
        include_once(File_name::code_base_dir().$file_name);
    }
    public static function _file_get_config($file_name){
        return file_get_contents(File_name::config($file_name).'.ini');
    }
    public static function _file_get_config_sender_p12($file_name){
        return file_get_contents(File_name::config_sender($file_name).'.p12');
    }
    public static function _file_get_config_sender($file_name){
        return file_get_contents(File_name::config_sender($file_name).'.json');
    }
    public static function _file_get_resouce($file_name){
        return file_get_contents(File_name::resource($file_name));
    }
    public static function _file_put_contents_into_json_pool($sender_id,$id,$json){
        return file_put_contents(File_name::json_pool_documento($sender_id,$id),$json);
    }   
    public static function _unlink_contents_in_json_pool($sender_id,$id){
        return unlink(File_name::json_pool_documento($sender_id,$id));
    }   
    public static function _file_put_contents_into_xml_pool3($sender_id,$id,$json){
        return file_put_contents(File_name::xml_pool3_documento($sender_id,$id),$json);
    }   
    public static function _file_put_contents_into_xml_pool2($sender_id,$id,$json){
        return file_put_contents(File_name::xml_pool2_documento($sender_id,$id),$json);
    }   
    public static function _unlink_contents_in_xml_pool3($sender_id,$id){
        return unlink(File_name::xml_pool3_documento($sender_id,$id));
    }   
    public static function _unlink_contents_in_xml_pool2($sender_id,$id){
        return unlink(File_name::xml_pool2_documento($sender_id,$id));
    }   
    public static function _file_get_into_xml_response($sender_id,$clave,$documento_type){
        return file_get_contents(File_name::xml_response($sender_id,$clave,$documento_type));
    }   
    public static function _file_get_into_xml_document($sender_id,$clave,$documento_type){
        return file_get_contents(File_name::documento($sender_id,$clave,$documento_type));
    }   
    public static function _file_put_into_xml_document($sender_id,$clave,$documento_type,$xml){
        return file_put_contents(File_name::documento($sender_id,$clave,$documento_type),$xml);
    }   
}
Api::_init();
Api::_include_once('./util/util.php');
Api::_include_once('./util/file.php');
Api::_include_once('./util/frontend_consulta.php');
