<?php
Api::_include_once('./util/util.php');
Api::_include_once('./util/file.php');
class Genera_xml{
    public static function mensaje_receptor($sender,&$data){
        $xmlDoc = new DOMDocument('1.0' , 'UTF-8');
        libxml_use_internal_errors(true);

        $facturacion = $xmlDoc->appendChild($xmlDoc->createElement("MensajeReceptor"));
        $facturacion->appendChild($xmlDoc->createAttribute("xmlns"))->appendChild(
        $xmlDoc->createTextNode('https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/mensajeReceptor'));

        //header
        $facturacion->appendChild($xmlDoc->createAttribute("xmlns:xsd"))->appendChild(
                $xmlDoc->createTextNode('http://www.w3.org/2001/XMLSchema'));
        $facturacion->appendChild($xmlDoc->createAttribute("xmlns:xsi"))->appendChild(
                $xmlDoc->createTextNode('http://www.w3.org/2001/XMLSchema-instance'));


        //data
         
        $facturacion->appendChild($xmlDoc->createElement("Clave", $data->Clave));
        $facturacion->appendChild($xmlDoc->createElement("NumeroCedulaEmisor", $data->NumeroCedulaEmisor));
        $facturacion->appendChild($xmlDoc->createElement("FechaEmisionDoc", $data->FechaEmisionDoc));
        $facturacion->appendChild($xmlDoc->createElement("Mensaje", $data->Mensaje));
        $facturacion->appendChild($xmlDoc->createElement("DetalleMensaje", $data->DetalleMensaje));
        $facturacion->appendChild($xmlDoc->createElement("MontoTotalImpuesto", $data->MontoTotalImpuesto));
        $facturacion->appendChild($xmlDoc->createElement("TotalFactura", $data->TotalFactura));
        $facturacion->appendChild($xmlDoc->createElement("NumeroCedulaReceptor", $data->NumeroCedulaReceptor));
        $facturacion->appendChild($xmlDoc->createElement("NumeroConsecutivoReceptor", $data->NumeroConsecutivoReceptor));
            

        $xmlDoc->formatOutput = true;
        $xml_sin_firma = $xmlDoc->saveXML();

        //genera firma
        $firma = $sender->firma_xml($xml_sin_firma,$data->FechaFirma);
 
        //firma documento 
        $dom = new DOMDocument();
        $dom_2 = new DOMDocument();
        //cargo el documento sin firma
        $doc_sin_firma = $dom->LoadXML($xml_sin_firma);
        // cargo la firma del documento canonicalizado probado hasta el 19-01-2018
        $dom_firma = $dom_2->LoadXML($firma);
        //canonicalizo la firm
        $valor_2 = $dom_2->documentElement;
        $dom_2->formatOutput = true;
        $dom->documentElement->appendChild($dom->importNode($valor_2, true));
        $documento_con_firma = $dom->C14N($doc_sin_firma);
        $xml_save = '<?xml version="1.0" encoding="utf-8"?>'.$documento_con_firma;

        //log de xml MensajeReceptor
        mensaje_receptor_put_contents($sender, $data, $xml_save);
        return $xml_save;
    }    
    
    public static function documento_emisor($sender,&$data){
        $xmlDoc = new DOMDocument('1.0' , 'UTF-8');
        libxml_use_internal_errors(true);
        switch ($data->Documento->Tipo) {
            case '01':
                    $facturacion = $xmlDoc->appendChild($xmlDoc->createElement("FacturaElectronica"));
                    $facturacion->appendChild($xmlDoc->createAttribute("xmlns"))->appendChild(
            $xmlDoc->createTextNode('https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/facturaElectronica'));
            break;
            case '02':
                    $facturacion = $xmlDoc->appendChild($xmlDoc->createElement("NotaDebitoElectronica"));
                    $facturacion->appendChild($xmlDoc->createAttribute("xmlns"))->appendChild(
            $xmlDoc->createTextNode('https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/notaDebitoElectronica'));
            break;
            case '03':
                    $facturacion = $xmlDoc->appendChild($xmlDoc->createElement("NotaCreditoElectronica"));
                    $facturacion->appendChild($xmlDoc->createAttribute("xmlns"))->appendChild(
            $xmlDoc->createTextNode('https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/notaCreditoElectronica'));
            break;
            case '04':
                    $facturacion = $xmlDoc->appendChild($xmlDoc->createElement("TiqueteElectronico"));
                    $facturacion->appendChild($xmlDoc->createAttribute("xmlns"))->appendChild(
            $xmlDoc->createTextNode('https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/tiqueteElectronico'));
            break;
            case '05':
            case '06':
            case '07':
                    $facturacion = $xmlDoc->appendChild($xmlDoc->createElement("MensajeReceptor"));
                    $facturacion->appendChild($xmlDoc->createAttribute("xmlns"))->appendChild(
            $xmlDoc->createTextNode('https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/mensajeReceptor'));
            break;

        }
        $facturacion->appendChild($xmlDoc->createAttribute("xmlns:xsd"))->appendChild(
                $xmlDoc->createTextNode('http://www.w3.org/2001/XMLSchema'));
        $facturacion->appendChild($xmlDoc->createAttribute("xmlns:xsi"))->appendChild(
                $xmlDoc->createTextNode('http://www.w3.org/2001/XMLSchema-instance'));
        if ($data->Documento->Tipo == '05'){
            $datosMensaje= $this->ConsultaMensajeReceptor($_GET['mr']);
            $facturacion->appendChild($xmlDoc->createElement("Clave", $datosMensaje['clave_referencia']));
            $facturacion->appendChild($xmlDoc->createElement("NumeroCedulaEmisor", $datosMensaje['cedula_emisor']));
            $facturacion->appendChild($xmlDoc->createElement("FechaEmisionDoc", date('c')));
            $facturacion->appendChild($xmlDoc->createElement("Mensaje", $datosMensaje['mensaje']));
            $facturacion->appendChild($xmlDoc->createElement("DetalleMensaje", $datosMensaje['detalle_mensaje']));
            $facturacion->appendChild($xmlDoc->createElement("MontoTotalImpuesto", "0.00000"));
            $facturacion->appendChild($xmlDoc->createElement("TotalFactura", $datosMensaje['total_factura']));
            $facturacion->appendChild($xmlDoc->createElement("NumeroCedulaReceptor", $datosMensaje['cedula_receptor']));
            $facturacion->appendChild($xmlDoc->createElement("NumeroConsecutivoReceptor", $datosMensaje['numero_consecutivo']));
        }else{
        //Clave
        $clave = $facturacion->appendChild($xmlDoc->createElement("Clave", $data->Clave));
        //Consecutivo
        $nconsecutivo = $facturacion->appendChild($xmlDoc->createElement("NumeroConsecutivo", $data->Agencia.$data->Terminal.$data->Documento->Tipo."".$data->Documento->Numero.""));
        //Fecha de Emision dinamica
        appendNVL($xmlDoc,$facturacion,$data,"FechaEmision");
	//Emisor viene del json Emisor
        $emisor = $facturacion->appendChild($xmlDoc->createElement("Emisor"));
        appendNVL($xmlDoc,$emisor,$data->Emisor,"Nombre");
        $ident_emisor = $emisor->appendChild($xmlDoc->createElement("Identificacion"));
            appendNVL($xmlDoc,$ident_emisor,$data->Emisor->Identificacion,"Tipo");
            $data->Emisor->Identificacion->Numero = ltrim($data->Emisor->Identificacion->Numero,'0');
            //var_dump($data->Emisor->Identificacion->Numero);
            appendNVL($xmlDoc,$ident_emisor,$data->Emisor->Identificacion,"Numero");
        appendNVL($xmlDoc,$emisor,$data->Emisor,"IdentificacionExtranjero");
        appendNVL($xmlDoc,$emisor,$data->Emisor,"NombreComercial");
        $ubicacion_emisor = $emisor->appendChild($xmlDoc->createElement("Ubicacion"));
            appendNVL($xmlDoc,$ubicacion_emisor,$data->Emisor->Ubicacion,"Provincia");
            appendNVL($xmlDoc,$ubicacion_emisor,$data->Emisor->Ubicacion,"Canton");
            appendNVL($xmlDoc,$ubicacion_emisor,$data->Emisor->Ubicacion,"Distrito");
            appendNVL($xmlDoc,$ubicacion_emisor,$data->Emisor->Ubicacion,"OtrasSenas");
        if (isset($data->Emisor->Telefono)){
            $telf_emisor = $emisor->appendChild($xmlDoc->createElement("Telefono"));
                appendNVL($xmlDoc,$telf_emisor,$data->Emisor->Telefono,"CodigoPais");
                appendNVL($xmlDoc,$telf_emisor,$data->Emisor->Telefono,"NumTelefono");
        }
        if (isset($data->Emisor->Fax)){
            $fax_emisor = $emisor->appendChild($xmlDoc->createElement("Fax"));
                appendNVL($xmlDoc,$fax_emisor,$data->Emisor->Fax,"CodigoPais");
                appendNVL($xmlDoc,$fax_emisor,$data->Emisor->Fax,"NumTelefono");
        }

        $emisor->appendChild($xmlDoc->createElement("CorreoElectronico", $data->Emisor->CorreoElectronico));
        //Cliente
        if (isset($data->Receptor)){
            $receptor = $facturacion->appendChild($xmlDoc->createElement("Receptor"));
                appendNVL($xmlDoc,$receptor,$data->Receptor,"Nombre");
                if (isset($data->Receptor->Identificacion)){
                    $ident_receptor = $receptor->appendChild($xmlDoc->createElement("Identificacion"));
                        appendNVL($xmlDoc,$ident_receptor,$data->Receptor->Identificacion,"Tipo");
                        $data->Receptor->Identificacion->Numero = ltrim($data->Receptor->Identificacion->Numero,'0');
                        appendNVL($xmlDoc,$ident_receptor,$data->Receptor->Identificacion,"Numero");
                }
                appendNVL($xmlDoc,$receptor,$data->Receptor,"IdentificacionExtranjero");
                appendNVL($xmlDoc,$receptor,$data->Receptor,"NombreComercial");
                if (isset($data->Receptor->Ubicacion)){
                    $ubicacion_receptor = $receptor->appendChild($xmlDoc->createElement("Ubicacion"));
                            appendNVL($xmlDoc,$ubicacion_receptor,$data->Receptor->Ubicacion,"Provincia");
                            appendNVL($xmlDoc,$ubicacion_receptor,$data->Receptor->Ubicacion,"Canton");
                            appendNVL($xmlDoc,$ubicacion_receptor,$data->Receptor->Ubicacion,"Distrito");
                            appendNVL($xmlDoc,$ubicacion_receptor,$data->Receptor->Ubicacion,"OtrasSenas");
                }
                if (isset($data->Receptor->Telefono)){
                    $telf_receptor = $receptor->appendChild($xmlDoc->createElement("Telefono"));
                            appendNVL($xmlDoc,$telf_receptor,$data->Receptor->Telefono,"CodigoPais");
                            appendNVL($xmlDoc,$telf_receptor,$data->Receptor->Telefono,"NumTelefono");
                }
                if (isset($data->Receptor->Fax)){
                    $telf_receptor = $receptor->appendChild($xmlDoc->createElement("Telefono"));
                            appendNVL($xmlDoc,$telf_receptor,$data->Receptor->Fax,"CodigoPais");
                            appendNVL($xmlDoc,$telf_receptor,$data->Receptor->Fax,"NumTelefono");
                }
                appendNVL($xmlDoc,$receptor,$data->Receptor,"CorreoElectronico");
        }
        $facturacion->appendChild($xmlDoc->createElement("CondicionVenta", $data->CondicionVenta));
        if (isset($data->PlazoCredito)){
            $facturacion->appendChild($xmlDoc->createElement("PlazoCredito", $dias_credito));
        }
        if ($data->Documento->Tipo == "01") {
            $facturacion->appendChild($xmlDoc->createElement("MedioPago", nvl($data->MedioPago)));
        }else
        {
            $facturacion->appendChild($xmlDoc->createElement("MedioPago", "01"));
        }

        $detalle_servicio = $facturacion->appendChild($xmlDoc->createElement("DetalleServicio"));
        foreach ($data->DetalleServicio->LineaDetalle as $res) {
            $linea_detalle = $detalle_servicio->appendChild($xmlDoc->createElement("LineaDetalle"));
            appendNVL($xmlDoc,$linea_detalle,$res,"NumeroLinea");
            if (isset($res->Codigo)){
                        $codigo_linea =	$linea_detalle->appendChild($xmlDoc->createElement("Codigo"));
                        //var_dump($res->Codigo);
                        $codigo_linea->appendChild($xmlDoc->createElement("Tipo", "04"));
                        appendNVL($xmlDoc,$codigo_linea,$res->Codigo,"Codigo");
            }
            appendNVL($xmlDoc,$linea_detalle,$res,"Cantidad");
            appendNVL($xmlDoc,$linea_detalle,$res,"UnidadMedida");
            appendNVL($xmlDoc,$linea_detalle,$res,"UnidadMedidaComercial");
            appendNVL($xmlDoc,$linea_detalle,$res,"Detalle");
            appendNVL($xmlDoc,$linea_detalle,$res,"PrecioUnitario");
            appendNVL($xmlDoc,$linea_detalle,$res,"MontoTotal");
            appendNVL($xmlDoc,$linea_detalle,$res,"MontoDescuento");
            appendNVL($xmlDoc,$linea_detalle,$res,"NaturalezaDescuento");
            appendNVL($xmlDoc,$linea_detalle,$res,"SubTotal");

            //Impuesto 
            foreach (nvl($res->Impuesto,array()) as $imp) {
                    $impuesto = $linea_detalle->appendChild($xmlDoc->createElement("Impuesto"));
                    appendNVL($xmlDoc,$impuesto,$imp,"Codigo");
                    appendNVL($xmlDoc,$impuesto,$imp,"Tarifa");
                    appendNVL($xmlDoc,$impuesto,$imp,"Monto");
                    foreach (nvl($imp->Exoneracion,array()) as $exo){
                        $exoneracion = $impuesto->appendChild($xmlDoc->createElement("Exoneracion"));
                        appendNVL($xmlDoc,$exoneracion,$exo,"TipoDocumento");
                        appendNVL($xmlDoc,$exoneracion,$exo,"NumeroDocumento");
                        appendNVL($xmlDoc,$exoneracion,$exo,"NombreInstitucion");
                        appendNVL($xmlDoc,$exoneracion,$exo,"FechaEmision");
                        //$exoneracion->appendChild($xmlDoc->createElement("FechaEmision", date('Y-m-d')."T".date('H:i:s')."Z"));
                        appendNVL($xmlDoc,$exoneracion,$exo,"MontoImpuesto");
                        appendNVL($xmlDoc,$exoneracion,$exo,"PorcentajeCompra");
                    }
            }
            appendNVL($xmlDoc,$linea_detalle,$res,"MontoTotalLinea");

        }
                                
                                
        $resumen_factura = $facturacion->appendChild($xmlDoc->createElement("ResumenFactura"));
        //var_dump($data->ResumenFactura);
        appendNVL($xmlDoc,$resumen_factura,$data->ResumenFactura,"CodigoMoneda");
        appendNVL($xmlDoc,$resumen_factura,$data->ResumenFactura,"TipoCambio");
        appendNVL($xmlDoc,$resumen_factura,$data->ResumenFactura,"TotalServGravados");
        appendNVL($xmlDoc,$resumen_factura,$data->ResumenFactura,"TotalServExentos");
        appendNVL($xmlDoc,$resumen_factura,$data->ResumenFactura,"TotalMercanciasGravadas");
        appendNVL($xmlDoc,$resumen_factura,$data->ResumenFactura,"TotalMercanciasExentas");
        appendNVL($xmlDoc,$resumen_factura,$data->ResumenFactura,"TotalGravado");
        appendNVL($xmlDoc,$resumen_factura,$data->ResumenFactura,"TotalExento");
        appendNVL($xmlDoc,$resumen_factura,$data->ResumenFactura,"TotalVenta");
        appendNVL($xmlDoc,$resumen_factura,$data->ResumenFactura,"TotalDescuentos");
        appendNVL($xmlDoc,$resumen_factura,$data->ResumenFactura,"TotalVentaNeta");
        appendNVL($xmlDoc,$resumen_factura,$data->ResumenFactura,"TotalImpuesto");
        appendNVL($xmlDoc,$resumen_factura,$data->ResumenFactura,"TotalComprobante");
        
        if (isset($data->InformacionReferencia)){
            foreach($data->InformacionReferencia as $referencia){
                $informacion_referencia = $facturacion->appendChild($xmlDoc->createElement("InformacionReferencia"));
                appendNVL($xmlDoc,$informacion_referencia,$referencia,"TipoDoc");
                appendNVL($xmlDoc,$informacion_referencia,$referencia,"Numero");
                appendNVL($xmlDoc,$informacion_referencia,$referencia,"FechaEmision");
                appendNVL($xmlDoc,$informacion_referencia,$referencia,"Codigo");
                appendNVL($xmlDoc,$informacion_referencia,$referencia,"Razon");                
            }
        }
			
        // Normativa 
        $normativa = $facturacion->appendChild($xmlDoc->createElement("Normativa"));
        $normativa->appendChild($xmlDoc->createElement("NumeroResolucion", "DGT-R-48-2016"));
        $normativa->appendChild($xmlDoc->createElement("FechaResolucion", "20-02-2017 13:22:22"));
//        $normativa->appendChild($xmlDoc->createElement("FechaResolucion", "07-10-2016 08:00:00"));
        }
        
        $xmlDoc->formatOutput = true;
        $xml_sin_firma = $xmlDoc->saveXML();

        //firmar
        $firma = $sender->firma_xml($xml_sin_firma,$data->FechaFirma);

        $dom = new DOMDocument();
        $dom_2 = new DOMDocument();
        //documento sin firma
        $doc_sin_firma = $dom->LoadXML($xml_sin_firma);
        //firma del documento canonicalizado
        $dom_firma = $dom_2->LoadXML($firma);

        $valor_2 = $dom_2->documentElement;
        $dom_2->formatOutput = true;
        $dom->documentElement->appendChild($dom->importNode($valor_2, true));
        $documento_con_firma = $dom->C14N($doc_sin_firma);
        $xml_save = '<?xml version="1.0" encoding="utf-8"?>'.$documento_con_firma;

        File::document_put_contents($sender->id,$data, $xml_save);
        
        return $xml_save;
    }
}