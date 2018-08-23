<?php
Api::_include_once('./util/bk_consulta.php');
class Envia{
    public static function mensaje_receptor($sender,$token,$data,$xml_ws){
        $url=$sender->url.'recepcion/';  

        $datos = array(
        'clave' =>$data->Clave,
        'fecha' =>$data->FechaEmisionDoc,
        'emisor' => array(
            'tipoIdentificacion' => $data->Emisor->Identificacion->Tipo,
            'numeroIdentificacion' =>$data->Emisor->Identificacion->Numero
        ),
        'receptor' => array(
            'tipoIdentificacion' => $data->Emisor->Identificacion->Tipo,
            'numeroIdentificacion' => $data->Emisor->Identificacion->Numero
        ),
        'consecutivoReceptor' => $data->NumeroConsecutivoReceptor,
        'comprobanteXml' =>$xml_ws
         );


        $mensaje = json_encode($datos);


        $header = array(
            'Authorization: '.$token,
            'Content-Type: application/json'
        );

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $mensaje);

        $respuesta = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        $arrayResp = array(
            "Status" => $status,
            "text" => explode("\n", $respuesta)
        );
        curl_close($curl);

        if ($status==400) {
            return '{"ind-estado":"error"}';
        } else {
            for($i=0;$i<10;$i++){
                    $consulta_recepcion = Bk_consulta::recepcion($sender->url,$token,$data->Clave,'-'.$data->NumeroConsecutivoReceptor);//$this->Consulta_recepcion($token,$data->Clave,'-'.$data->NumeroConsecutivoReceptor);
                            $consult = json_decode($consulta_recepcion);
                            if ($consult->{'ind-estado'} == 'aceptado') {
                                    //TODO
                                    //ACTUALIZO DATOS EN BD CON RESPUESTA DE HACIENDA
                                    //$this->updateStates($consult->{'ind-estado'},$clave,$pidoperti);
                                    break;
                            }
            }
            return $consulta_recepcion;
        }
    }
    
    public static function documento_emisor($sender,$token,$doc_data,$xml_ws){ 
            $url = $sender->url."recepcion";
            $fecha = $doc_data->FechaEmision;
            
            $array = new \stdClass();
            $array->clave = "".$doc_data->Clave;
            $array->fecha = "".$doc_data->FechaEmision;
            $array->emisor = array( 
                    "tipoIdentificacion" => ''.$doc_data->Emisor->Identificacion->Tipo, 
                    "numeroIdentificacion" => ''.$doc_data->Emisor->Identificacion->Numero
            );
            if (isset($doc_data->Receptor) && isset($doc_data->Receptor->Identificacion)){
                $array->receptor = array(
                        "tipoIdentificacion" => ''.$doc_data->Receptor->Identificacion->Tipo, 
                        "numeroIdentificacion" => ''.$doc_data->Receptor->Identificacion->Numero
                );
            }

            $array->comprobanteXml = base64_encode($xml_ws);
            $json = json_encode($array, true);
            $curl = curl_init();
            //$curl->verbose($on = true, $output = STDERR);
            curl_setopt_array($curl, array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => $json,
                    CURLOPT_HTTPHEADER => array(
                    "authorization: ".$token,
                    "cache-control: no-cache",
                    "content-type: application/json",
                    "postman-token: 689c8b8b-789b-94a3-ba89-607cb3338a5d"
                    ),
            ));
            var_dump(array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => $json,
                    CURLOPT_HTTPHEADER => array(
                    "authorization: ".$token,
                    "cache-control: no-cache",
                    "content-type: application/json",
                    "postman-token: 689c8b8b-789b-94a3-ba89-607cb3338a5d"
                    ),
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            new Db_model($doc_data);
            Db_model::updt_resumen_documento($sender->Tipo,$sender->Numero,$doc_data->Clave,'sent');
            
            if ($err) {
                    $respuesta = "cURL Error #:".$err;
                    return  $respuesta;
            } else {
                for ($i=0; $i < 10; $i++) { 
                    $consulta_recepcion = Bk_consulta::recepcion($sender->url,$token, $doc_data->Clave);//$this->Consulta_recepcion($token, $doc_data->Clave);
                    $consult = json_decode($consulta_recepcion);
                    if (isset($consult->{'ind-estado'}) == false){
                        continue;
                    }
                    if (isset($consult->{'ind-estado'}) == false){
                        continue;
                    }
                    if ($consult->{'ind-estado'} == 'aceptado') {
                        $db_model = new Db_model($doc_data);
                        $db_model->updt_resumen_documento($sender->Tipo,$sender->Numero,$doc_data->Clave,$consult->{'ind-estado'});
                        $sender->email_factura($doc_data->Clave);
                        break;
                    }
                    if($consult->{'ind-estado'} == 'recibido') {
                        $db_model = new Db_model($doc_data);
                        $db_model->updt_resumen_documento($sender->Tipo,$sender->Numero,$doc_data->Clave,$consult->{'ind-estado'});
                        break;
                    }
                    if($consult->{'ind-estado'} == 'rechazado'){
                        $db_model = new Db_model($doc_data);
                        $db_model->updt_resumen_documento($sender->Tipo,$sender->Numero,$doc_data->Clave,$consult->{'ind-estado'});
                        break;
                    }
                }
                return $consulta_recepcion;
            }
    }    
        
    public static function getCausaError($respuesta) {
        $info = explode("\n", $respuesta);
        $info_array = array();
        foreach($info as $line){
            if (strpos($line,":")){
                list($k, $v) = explode(':', $line);
                $info_array[ $k ] = ltrim($v);
            }
        }
        if ($info_array['X-Http-Status']==400){
            return $info_array['X-Error-Cause'];
        }

    }

}