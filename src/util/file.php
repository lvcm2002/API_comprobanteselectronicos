<?php
Api::_include_once('./util/model.php');
class File{
    public static function compra_name($sender,$pobj){
        return File_name::compra($sender,Model::get_clave($pobj));
    }
    
    public static function mensaje_receptor_name($sender,$pobj){
        return File_name::mensaje_receptor($sender,Model::get_clave($pobj),Model::get_consecutivo($pobj),Model::get_documento_tipo($pobj));
    }
    public static function document_name($sender_id,$pobj){
        return File_name::documento($sender_id,Model::get_clave($pobj),Model::get_documento_tipo($pobj));
    }

    public static function xml_response_name($sender,$pobj){
        return File_name::xml_response($sender,Model::get_clave($pobj),Model::get_documento_tipo($pobj));
    }

    public static function document_get_contents($sender_id,$clave){
        return simplexml_load_file(File::document_name($sender_id,$clave));
    }

    public static function mensaje_receptor_put_contents($sender,$data,$xml){
        file_put_contents(File::mensaje_receptor_name($sender,$data), $xml);
    }
    
    public static function document_put_contents($sender_id,$data,$xml){
        file_put_contents(File::document_name($sender_id,$data), $xml);
    }

    public static function xml_response_put_contents($sender,$consulta){//$clave,$data){
        file_put_contents(File::xml_response_name($sender,$consulta->{'clave'}), base64_decode($consulta->{'respuesta-xml'}));
    }

    public static function xml_response_MensajeAceptacion_put_contents($sender,$data,$consulta){
        file_put_contents(File::xml_response_name($sender,$data), base64_decode($consulta->{'respuesta-xml'}));
    }
    
}
