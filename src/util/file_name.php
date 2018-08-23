<?php
class File_name{
    public static $config;

    public static function rtrim_dir(&$dir){
        $dir = rtrim($dir,'/').'/';
    }
    public static function trim_dir(&$dir){
        $dir = '/'.trim($dir,'/').'/';
    }
    public static function mk_dir($sender){
        
    }
    public function __construct($init='./files.ini'){
        File_name::$config = json_decode(json_decode(json_encode(file_get_contents($init))));
        File_name::rtrim_dir(File_name::$config->api->base_dir);
        File_name::rtrim_dir(File_name::$config->api->resource_dir);
        File_name::rtrim_dir(File_name::$config->api->config->base_dir);
        File_name::rtrim_dir(File_name::$config->api->config->sender_dir);
        File_name::rtrim_dir(File_name::$config->compra->base_dir);
        File_name::rtrim_dir(File_name::$config->compra->dir);
        File_name::rtrim_dir(File_name::$config->documento->base_dir);
        File_name::trim_dir(File_name::$config->documento->pool2->dir);
        File_name::trim_dir(File_name::$config->documento->pool3->dir);
        File_name::trim_dir(File_name::$config->documento->json_pool->dir);
        File_name::trim_dir(File_name::$config->documento->enum->{'01'}->dir);
        File_name::trim_dir(File_name::$config->documento->enum->{'02'}->dir);
        File_name::trim_dir(File_name::$config->documento->enum->{'03'}->dir);
        File_name::trim_dir(File_name::$config->documento->enum->{'04'}->dir);
        File_name::trim_dir(File_name::$config->documento->enum->{'05'}->dir);
        File_name::trim_dir(File_name::$config->documento->enum->{'06'}->dir);
        File_name::trim_dir(File_name::$config->documento->enum->{'07'}->dir);
        File_name::rtrim_dir(File_name::$config->xml_response->base_dir);
        File_name::trim_dir(File_name::$config->xml_response->enum->{'01'}->dir);
        File_name::trim_dir(File_name::$config->xml_response->enum->{'02'}->dir);
        File_name::trim_dir(File_name::$config->xml_response->enum->{'03'}->dir);
        File_name::trim_dir(File_name::$config->xml_response->enum->{'04'}->dir);
        File_name::trim_dir(File_name::$config->xml_response->enum->{'05'}->dir);
        File_name::trim_dir(File_name::$config->xml_response->enum->{'06'}->dir);
        File_name::trim_dir(File_name::$config->xml_response->enum->{'07'}->dir);
    }
    public static function code_base_dir(){
        return File_name::$config->api->base_dir;
    }
    public static function document_base_dir(){
        return File_name::$config->documento->base_dir;
    }
    public static function json_pool_dir(){
        return File_name::$config->documento->json_pool->dir;
    }
    public static function pool3_dir(){
        return File_name::$config->documento->pool3->dir;
    }
    public static function pool2_dir(){
        return File_name::$config->documento->pool2->dir;
    }
    public static function name($sender_id,$clave,$base_dir,$document_type){
        return sprintf('%s%s%s%s%s%s%s', $base_dir,$sender_id,$document_type->dir,$document_type->prefix,$clave,$document_type->sufix, $document_type->ext);
    }
    public static function compra($sender,$clave){
        $doc = File_name::$config->compra;
        return File_name::name($sender->id,$clave,$doc->base_dir, $doc);
    }
    public static function mensaje_receptor($sender,$clave,$consecutivo,$documento_type){
        $doc = File_name::$config->documento;
        return File_name::name($sender->id,$clave.'-'.$consecutivo,$doc->base_dir, $doc->enum->{$documento_type});
    }
    public static function json_pool_documento($sender_id,$clave){
        $doc = File_name::$config->documento;
        return File_name::name($sender_id,$clave,$doc->base_dir, $doc->json_pool);
    }
    public static function xml_pool3_documento($sender_id,$clave){
        $doc = File_name::$config->documento;
        return File_name::name($sender_id,$clave,$doc->base_dir, $doc->pool3);
    }
    public static function xml_pool2_documento($sender_id,$clave){
        $doc = File_name::$config->documento;
        return File_name::name($sender_id,$clave,$doc->base_dir, $doc->pool2);
    }
    public static function config($file_name){
        $config_dir = File_name::$config->api->config->base_dir;
        return sprintf('%s%s', $config_dir,$file_name);
    }
    public static function config_sender($file_name){
        $config_dir = File_name::$config->api->config->base_dir;
        $sender_dir = File_name::$config->api->config->sender_dir;
        return sprintf('%s%s%s', $config_dir,$sender_dir,$file_name);
    }
    public static function resource($file_name){
        $resource_dir = File_name::$config->api->resource_dir;
        return sprintf('%s%s', $resource_dir,$file_name);
    }
    public static function documento($sender_id,$clave,$documento_type){
        $doc = File_name::$config->documento;
        return File_name::name($sender_id,$clave,$doc->base_dir, $doc->enum->{$documento_type});
    }
    public static function xml_response($sender,$clave,$documento_type){
        $doc = File_name::$config->xml_response;
        return File_name::name($sender->id,$clave,$doc->base_dir,$doc->enum->{$documento_type});
    }
    public static function mk_dirs($sender_id){
        $dirs = Mk_dirs::make_sender_dir_list($sender_id);
        foreach($dirs as $dir){
            Mk_dirs::make_dir($dir);
        }
    }
}
class Mk_dirs{
    public static function sender_dir_list($sender_id){
        $compra = $this->config->compra;
        $documento = $this->config->documento;
        $xml_response = $this->config->xml_response;
        $dirs = array(
            $compra->base_dir,
            $compra->base_dir . $sender,
            $compra->base_dir . $sender . $compra->dir,
            $documento->base_dir,
            $documento->base_dir . $sender,
            $documento->base_dir . $sender . $documento->pool3->dir,
            $documento->base_dir . $sender . $documento->pool2->dir,
            $documento->base_dir . $sender . $documento->json_pool->dir,
            $documento->base_dir . $sender . $documento->{'01'}->dir,
            $documento->base_dir . $sender . $documento->{'02'}->dir,
            $documento->base_dir . $sender . $documento->{'03'}->dir,
            $documento->base_dir . $sender . $documento->{'04'}->dir,
            $documento->base_dir . $sender . $documento->{'05'}->dir,
            $documento->base_dir . $sender . $documento->{'06'}->dir,
            $documento->base_dir . $sender . $documento->{'07'}->dir,
            $xml_response->base_dir,
            $xml_response->base_dir . $sender,
            $xml_response->base_dir . $sender . $xml_response->{'01'}->dir,
            $xml_response->base_dir . $sender . $xml_response->{'02'}->dir,
            $xml_response->base_dir . $sender . $xml_response->{'03'}->dir,
            $xml_response->base_dir . $sender . $xml_response->{'04'}->dir,
            $xml_response->base_dir . $sender . $xml_response->{'05'}->dir,
            $xml_response->base_dir . $sender . $xml_response->{'06'}->dir,
            $xml_response->base_dir . $sender . $xml_response->{'07'}->dir
        );
    }
    public static function make_dir($path){
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }
    }
}
