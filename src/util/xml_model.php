<?php
Api::_include_once('./util/file.php');
Api::_include_once('./util/model.php');
Api::_include_once('./util/genera_xml.php');
class Xml_model{
    public static function set_missing_values(&$model){
        assignNVL2($model->Emisor->Identificacion,'Tipo','1',2);
        assignNVL2($model->Emisor->Identificacion,'Numero','0',12);
        $model->Sender = $model->Emisor;
        $model->Agencia = substr($model->NumeroConsecutivo,0,3);
        $model->Terminal = substr($model->NumeroConsecutivo,3,5);
        $model->Documento = new stdClass(); $model->Documento->Tipo = substr($model->NumeroConsecutivo,8,2);
        assignNVL2($model,'PlazoCredito','');
        assignNVL2($model->ResumenFactura,'CodigoMoneda','CRC');
        assignNVL2($model->ResumenFactura,'TipoCambio','1.00');
        assignNVL2($model->ResumenFactura,'TotalServGravados','0.00');
        assignNVL2($model->ResumenFactura,'TotalServExentos','0.00');
        assignNVL2($model->ResumenFactura,'TotalMercanciasGravadas','0.00');
        assignNVL2($model->ResumenFactura,'TotalMercanciasExentas','0.00');
        assignNVL2($model->ResumenFactura,'TotalGravado','0.00');
        assignNVL2($model->ResumenFactura,'TotalExento','0.00');
        assignNVL2($model->ResumenFactura,'TotalVenta','0.00');
        assignNVL2($model->ResumenFactura,'TotalDescuentos','0.00');
        assignNVL2($model->ResumenFactura,'TotalVentaNeta','0.00');
        assignNVL2($model->ResumenFactura,'TotalImpuesto','0.00');
        assignNVL2($model->ResumenFactura,'TotalComprobante','0.00');
        switch (Model::get_documento_tipo($model->Clave)){
            case '01':
            case '02':
            case '04':
                $model->sign = 1;
                break;
            default:
                $model->sign = -1;        
        }
        return $model;    
    }
    public static function file_get_model($clave){    
        $model=json_decode(json_encode(File::document_get_content($clave)));
        return Xml_model::set_missing_values($model);
    }
    public static function xml_get_model($pxml){
        return json_decode(json_encode(new SimpleXMLElement($pxml)));
    }
    
}
class Xml_wrap{
    public static function documento($sender,&$data){
        switch ($data->Documento->Tipo) {
        case '01':
        case '02':
        case '03':
        case '04':
            return Xml_wrap::documento_emisor($sender,$data);
            break;
        default:
            return Xml_wrap::mensaje_receptor($sender,$data);
        }

    }
    public static function documento_emisor($sender,&$data){
        Model::set_emisor($data);           
        Model::set_fecha_emision($data);    
        Model::set_clave($sender,$data);
        $data = Model::calcula($data);
        $data = Front_end::formatea($data,Front_end::getEnums());
        return Genera_xml::documento_emisor($sender,$data);;
    }
    public static function mensaje_receptor($sender,&$data){
        return Genera_xml::mensaje_receptor($sender,$data);
    }
}
