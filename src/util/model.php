<?php
Api::_include_once('./util/util.php');
Api::_include_once('./util/bk_consulta.php');;
class Model{
    public static function get_documento_tipo_from_clave($pclave){
        $consecutivo = substr($pclave,21,20);
        return substr($consecutivo,8,2);            
    }
    public static function get_documento_tipo_from_data($pdata){
        return $data->Documento->Tipo; 
    }
    public static function get_documento_tipo($pobj){
        if (is_string($pobj)){
            return Model::get_documento_tipo_from_clave($pobj);
        }
        if (isset($pobj->Mensaje)){
            return str_pad($pobj->Mensaje+4,2,'0',STR_PAD_LEFT);
        }
        if (isset($pobj->Documento)){
            return nvl(nvl($pobj->Documento,new stdClass())->Tipo,'1',2);
        }
        return Model::get_documento_tipo_from_clave(Model::get_clave($pobj));
    }
    
    public static function get_sender($data){
        switch (Model::get_documento_tipo($data)){
        case '01':
        case '02':
        case '03':
        case '04': 
            return $data->Emisor;
        default:
            return $data->Receptor;
        }        
    }
    public static function get_sender_id($pdata){
        if (is_string($pdata)){
            return Model::get_sender_id_by_clave($pdata);
        }
        return Model::get_sender_id_by_data($pdata);
    }
    
    public static function get_sender_id_by_data($data){
        $sender = Model::get_sender($data);
        return $sender->Identificacion->Tipo.'-'.str_pad($sender->Identificacion->Numero,12,'0',STR_PAD_LEFT);
    }
    public static function get_tipo_by_number($number){
        $tipo = '03';
        if (substr($number, 1,1) == '3' && strlen($number) == 10){
            $tipo = '02';
        }
        if (strlen($number) == 9){
            $tipo = '01';
        }
        return $tipo;
    }
    public static function get_sender_id_by_clave($clave){
        $number = ltrim(substr($clave,9,12),'0');
        $tipo = Model::get_tipo_by_number($number);
        
        return $tipo.'-'.substr($clave,9,12); //TODO
    }

    public static function get_clave($pobj){
        if (is_string($pobj)){
            return $pobj;
        }
        return $pobj->Clave;
    }
    public static function get_consecutivo($pobj){
        return nvl2($pobj,'NumeroConsecutivoReceptor','');
    }

    public static function set_numero_consecutivo(&$data){
        $newClass = new stdClass();
        $consecutivo = sprintf('%s%s%s%s',nvl($data->Agencia,'1',3),nvl($data->Terminal,'1',5),nvl(nvl($data->Documento,$newClass)->Tipo,'1',2),nvl(nvl($data->Documento,$newClass)->Numero,'1',10));
        switch ($data->Documento->Tipo){
            case '01':
            case '02':
            case '03':
            case '04':
                $data->NumeroConsecutivo = $consecutivo;
                break;
            default:
                $data->NumeroConsecutivoReceptor = $consecutivo;
        }
    }
    public static function get_numero_consecutivo($data){
        if (isset($data->NumeroConsecutivo)){
            return $data->NumeroConsecutivo;
        }
        return $data->NumeroConsecutivoReceptor;
        //return 
    }
    
    public static function set_clave($sender,&$data){
        $newClass = new stdClass();
        $tipoDocumento = nvl(nvl($data->Documento,$newClass)->Tipo,'1',2);
        $numeroDocumento = nvl(nvl($data->Documento,$newClass)->Numero,'1',10);
        $agencia = nvl($data->Agencia,'1',3);
        $terminal = nvl($data->Terminal,'1',5);   
        $fechaEmision = date_format(date_create($data->FechaEmision),'y-m-d');
        list($year,$mes,$dia)=explode("-",$fechaEmision);
        $emisorTipo = nvl(nvl(nvl($data->Emisor,$newClass)->Identificacion,$newClass)->Tipo,'1',2);
        $emisorIdentificacion = nvl(nvl(nvl($data->Emisor,$newClass)->Identificacion,$newClass)->Numero,'0',12); 
        //TODO situacion default segun configuracion de sender
        $situacion = $sender->situacion; 

        if (!Bk_consulta::is_connected()){
            $situacion = 2;            
        }
        $data->Clave = '506'.$dia.''.$mes.''.$year.''.$emisorIdentificacion.$agencia.$terminal.$tipoDocumento.''.$numeroDocumento.$situacion.substr($numeroDocumento,2,10);
    }

    public static function set_sender(&$data){
        $data->Sender = Model::get_sender($data);
    }
    
    public static function set_documento_numero(&$data,$number){
        $data->Documento->Numero = $number;
    }

    public static function set_fecha_emision(&$data){
        switch ($data->Documento->Tipo){
        case '01':
        case '02':
        case '03':
        case '04':
            $data->FechaEmision = now_c();
            break;
        default:
            $data->FechaEmisionDoc = now_c();
        }
        $data->FechaFirma = now_f();
        return $data;
    }
    
    public static function set_emisor(&$data){
        $newClass = new stdClass();
        
        $emisor_json = Api::_file_get_config_sender(Model::get_sender_id($data));//File_name::config_sender(Model::get_sender_id($data));//file_get_contents("../sender/".Model::get_sender_id($data).".json");
        $emisor_data = Front_end::withJson($emisor_json);
        $data->Emisor = $emisor_data->Emisor;
    }
    
    public static function calcula(&$data){
        //set_emisor($data);
        //set_clave($data);
        $detalle = $data->DetalleServicio->LineaDetalle;
        $TotalServExentosTally = 0;
        $TotalServGravadosTally = 0;
        $TotalMercanciasExentasTally = 0;
        $TotalMercanciasGravadasTally = 0;
        $DescuentosTally = 0;
        $ImpuestosTally = 0;
        foreach($detalle as $l){
            $l->MontoTotal = number_format($l->Cantidad*$l->PrecioUnitario,5,".","");
            $l->SubTotal = number_format($l->MontoTotal-nvl2($l,'MontoDescuento',0),5,".","");
            $tMontoTally = 0;
            /*
            $subtotalAcumaldoDeImpuestos = $l->SubTotal;
            foreach(nvl($l->Impuesto,array()) as $t){
                $t->Tarifa = number_format($t->Tarifa,2);
                $t->Monto = $subtotalAcumaldoDeImpuestos*($t->Tarifa/100);
                $tMontoTally += $t->Monto;
                foreach(nvl($t->Exoneracion,array()) as $x){
                    $tMontoTally -= $t->MontoImpuesto;
                }
                $subtotalAcumaldoDeImpuestos += $t->Monto;
            }
             
            */
            $exento = true;
            unset($ImpuestoVenta);
            foreach(nvl($l->Impuesto,array()) as $t){
                $exento = ($t->Tarifa > 0?false:$exento);
                if ($t->Codigo == '01'){
                    //echo "Impuesto:{$t->Codigo}<br>";
                    $ImpuestoVenta = $t;
                    continue;
                }
                $t->Tarifa = number_format($t->Tarifa,2);
                $t->Monto = number_format($l->SubTotal*($t->Tarifa/100),5,".","");
                //echo "Impuesto:{$t->Codigo},Monto:{$t->Monto}<br>";
                $tMontoTally += $t->Monto;
                foreach(nvl($t->Exoneracion,array()) as $x){
                    $x->MontoImpuesto = number_format(($t->Monto*($x->PorcentajeCompra/100)),5,".","");
                    $tMontoTally -= $x->MontoImpuesto;
                    $remanente = ((100-$x->PorcentajeCompra)/100);
                    $t->Tarifa = number_format($t->Tarifa*$remanente,2);
                    $t->Monto = number_format($t->Monto*$remanente,5,".","");
                }
                
            }
            if (isset($ImpuestoVenta)){
                $t = $ImpuestoVenta;
                //echo "ImpuestoVenta tMontoTally:{$tMontoTally},";
                $t->Tarifa = number_format($t->Tarifa,2);
                $t->Monto = number_format(($l->SubTotal+$tMontoTally)*($t->Tarifa/100),5,".","");
                //echo "l->SubTotal+tMontoTally:{($l->SubTotal+$tMontoTally)}, t->Tarifa:{($t->Tarifa/100)}, t->Monto:{$t->Monto}<br>";
                $tMontoTally += $t->Monto;
                foreach(nvl($t->Exoneracion,array()) as $x){
                    $x->MontoImpuesto = number_format(($t->Monto*($x->PorcentajeCompra/100)),5,".","");
                    $tMontoTally -= $x->MontoImpuesto;
                    $remanente = ((100-$x->PorcentajeCompra)/100);
                    $t->Tarifa = number_format($t->Tarifa*$remanente,2);
                    $t->Monto = number_format($t->Monto*$remanente,5,".","");  //TODO en vez de recibir Tarifa de tax en campo Tarifa usar otro nombre para que recalculos no afecten el resultado
                }
            }
            $l->MontoTotalLinea = number_format($l->SubTotal+$tMontoTally,5,".","");
            
            if (isset($l->Mercancia)==false){
                if (!$exento){
                    $TotalServGravadosTally += $l->MontoTotal;
                } else {
                    $TotalServExentosTally += $l->MontoTotal;    
                }
            }
            else{
                if (!$exento){
                    $TotalMercanciasGravadasTally += $l->MontoTotal;
                } else {
                    $TotalMercanciasExentasTally += $l->MontoTotal;    
                }
            }
            $DescuentosTally += nvl2($l,'MontoDescuento',0);
            $ImpuestosTally += $tMontoTally;
        }
        
        if (isset($data->ResumenFactura)==false || is_array($data->ResumenFactura)){
            $data->ResumenFactura = new stdClass();
        }
        $r = $data->ResumenFactura;
        //test
        assignNVL($r,'TotalServGravados',$TotalServGravadosTally,'dinero');
        assignNVL($r,'TotalServExentos',$TotalServExentosTally);
        assignNVL($r,'TotalMercanciasGravadas',$TotalMercanciasGravadasTally);
        assignNVL($r,'TotalMercanciasExentas',$TotalMercanciasExentasTally);
        assignNVL($r,'TotalGravado',$TotalServGravadosTally+$TotalMercanciasGravadasTally);
        assignNVL($r,'TotalExento',$TotalServExentosTally+$TotalMercanciasExentasTally);
        $r->TotalVenta = number_format($TotalServGravadosTally+$TotalMercanciasGravadasTally+$TotalServExentosTally+$TotalMercanciasExentasTally,5,".","");
        assignNVL($r,'TotalDescuentos',$DescuentosTally);
        $r->TotalVentaNeta = number_format($r->TotalVenta-$DescuentosTally,5,".","");
        assignNVL($r,'TotalImpuesto',$ImpuestosTally);
        $r->TotalComprobante = number_format($r->TotalVentaNeta+$ImpuestosTally,5,".","");
        $data->Normativa = (object) [
            'NumeroResolucion' => 'DGT-R-48-2016',
            'FechaResolucion' => '20-02-2017 13:22:22',
        ];
        return $data;
    }
}
