<?php
Api::_include_once('./util/envia.php');
Api::_include_once('./util/model.php');
class Timbra{
    public static function documento($sender,$data,$xml_ws){

        switch (Model::get_documento_tipo($data)) {
        case '01':
        case '02':
        case '03':
        case '04':
            return Timbra::documentos_emisor($sender,$data,$xml_ws);
        default:
            return Timbra::mensaje_receptor($sender,$data,$xml_ws);
        }
    }
    
    public static function mensaje_receptor($sender,$data,$xml_ws){

        $token = $sender->get_token();exit;

        //envia mensaje
        $recepcion = json_decode(Envia::mensaje_receptor($sender,$token,$data,$xml_ws));

        switch ($recepcion->{'ind-estado'}) {

            case 'aceptado':
                    File::xml_response_MensajeAceptacion_put_contents($sender,$data, $recepcion);
                    $message = ["clave"=>$recepcion->{'clave'},"estado"=>$recepcion->{'ind-estado'},"fecha"=>$recepcion->{'fecha'},"respuesta"=>$recepcion->{'respuesta-xml'}];
                    return $recepcion->{'ind-estado'};
            break;

            case 'recibido':
                    $consulta = $sender->consulta_recepcion($token, $data->Clave,$data->NumeroConsecutivoReceptor);
                    File::xml_response_MensajeAceptacion_put_contents($sender,$data, $consulta);
                    $message = "Su documento fue Recibido de Manera Correcta Timbrado bajo el numero de documento #".$consulta->{'clave'};
                    return $message;
            break;

            case 'procesando':
                    $consulta = $sender->consulta_recepcion($token, $data->Clave,$data->NumeroConsecutivoReceptor);
                    if ($consulta->{'ind-estado'} != 'procesando') {
                            File::xml_response_MensajeAceptacion_put_contents($sender,$data, $consulta);
                            return $consulta->{'ind-estado'};
                    }else
                    {
                            $consulta2 = $sender->consulta_recepcion($token, $data->Clave,$data->NumeroConsecutivoReceptor);
                            return $consulta2->{'ind-estado'};
                    }

            break;

            case 'rechazado':
                    $consulta = $sender->consulta_recepcion($token, $data->Clave,$data->NumeroConsecutivoReceptor);
                    File::xml_response_MensajeAceptacion_put_contents($sender,$data, $consulta);
                    return $consulta->{'ind-estado'};
            break;

            case 'error':
                    $consulta = $sender->consulta_recepcion($token, $data->Clave,$data->NumeroConsecutivoReceptor);
                    File::xml_response_MensajeAceptacion_put_contents($sender,$data, $consulta);
                    return $consulta->{'ind-estado'};
            break;
            default:
                    return $recepcion->{'ind-estado'};
            break;

        }

    }
    public static function consulta_documentos_emisor($sender,$pdata,$token=null){
        if (is_null($token)){
            $token = $sender->get_token();
        }
        $clave = $pdata->Clave;
        $consecutivo = (is_null($pdata->NumeroConsecutivoReceptor)?'':$pdata->NumeroConsecutivoReceptor);
        for ($i=0; $i < 1; $i++) { 
            $consulta = $sender->consulta_recepcion($token, $clave);
            if (isset($consulta->{'ind-estado'}) == false){
                continue;
            }
            if (isset($consulta->{'ind-estado'}) == false){
                continue;
            }
            if ($consulta->{'ind-estado'} == 'aceptado') {
                Db_model::updt_resumen_documento($sender->Tipo,$sender->Numero,$clave,$consulta->{'ind-estado'});
                File::xml_response_put_contents($sender,$consulta);
                $sender->email_factura($clave);
                break;
            }
            if($consulta->{'ind-estado'} == 'recibido') {
                break;
            }
            if($consulta->{'ind-estado'} == 'rechazado'){
                Db_model::updt_resumen_documento($sender->Tipo,$sender->Numero,$clave,$consulta->{'ind-estado'});
                File::xml_response_put_contents($sender,$consulta);
                break;
            }
        }
        return $consulta;
    }
    
    public static function documentos_emisor($sender,$data,$xml_ws){
        $token = $sender->get_token();

        //envia documento
        $recepcion = json_decode(Envia::documento_emisor($sender,$token,$data,$xml_ws));
        if (is_numeric($recepcion)){
            return $recepcion;
        }
        if ($recepcion->{'ind-estado'} == 'aceptado'){
            File::xml_response_put_contents($sender,$recepcion);
            $message = "Su documento fue Recibido y aceptado de Manera Correcta Timbrado bajo el numero de documento #".$recepcion->{'clave'};
            return $message;
        }
        $consulta = $sender->consulta_recepcion($token, $recepcion->{'clave'});
        if (!is_object($consulta)){
            return "No hay respuesta";
        }
        switch ($recepcion->{'ind-estado'}) {
                case 'recibido':
                        File::xml_response_put_contents($sender,$consulta);
                        $message = "Su documento fue Recibido de Manera Correcta Timbrado bajo el numero de documento #".$consulta->{'clave'};
                        return $message;
                break;
                case 'procesando':
                        $consulta = $sender->consulta_recepcion($token, $recepcion->{'clave'});
                        if ($consulta->{'ind-estado'} != 'procesando') {
                                File::xml_response_put_contents($sender,$consulta);
                                return $consulta->{'ind-estado'};
                        }else
                        {
                                $consulta2 = $sender->consulta_recepcion($token, $consulta->{'clave'});
                                return $consulta2->{'ind-estado'};
                        }

                break;
                case 'rechazado':
                        $consulta = $sender->consulta_recepcion($token, $recepcion->{'clave'});
                       File::xml_response_put_contents($sender,$consulta);
                return $consulta->{'ind-estado'};
                break;
                case 'error':
                        $consulta = $sender->consulta_recepcion($token, $recepcion->{'clave'});
                        File::xml_response_put_contents($sender,$consulta);
                return $consulta->{'ind-estado'};
                break;
                default:
                        return $recepcion->{'ind-estado'};
                break;
        }
    }
}