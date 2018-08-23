<?php
//var_dump(__DIR__);
// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require 'vendor/autoload.php';

//carga email settings
use Dompdf\Dompdf;

// trae elemento del arreglo
function get_elem(){
    if (func_num_args() < 2){
        return '';
    }
    $arg_list = func_get_args();
    $arr0 = $arg_list[0];
    $default = $arg_list[1];
    for ($i = 2; $i < func_num_args(); $i++) {
        if (isset($arr0[$arg_list[$i]])==false){
            return $default;
        }
        $arr0 = $arr0[$arg_list[$i]];
    }
    return $arr0;
}

function cantidad_format($number){
    $decimales = 0;
    if (round($number) != round($number,5)){
        $decimales = 2;
    }
    return number_format($number,$decimales,".",",");
}
function format_tel($number){
    return sprintf("%s-%s",
              substr($number, 0, 4),
              substr($number, 4));
}
function format_cedula($number){
    $number = trim($number);
    switch (strlen($number)){
        case  9:
            return sprintf("%s-%s-%s",
              substr($number, 0, 1),
              substr($number, 1, 4),
              substr($number, 5));
            break;
        case 10:
            return sprintf("%s-%s-%s",
              substr($number, 0, 1),
              substr($number, 1, 3),
              substr($number, 4));
            break;
        case 11:
            return sprintf("s-%s-%s",
              substr($number, 0, 1),
              substr($number, 1, 3),
              substr($number, 4));
            break;
        default:
            return $number;
    }
}

// enums
function provincia($estado){
$_estado = (double)$estado;
$key = $_estado.'';
return Api::$enums['Provincia'][$key];
}
function canton($estado,$ciudad) {
$_estado = (double)$estado;
$_ciudad = (double)$ciudad;
$key = $_estado . '.' . $_ciudad;
return Api::$enums['Canton'][$key];
}
function distrito($estado,$ciudad,$sector) {
$_estado = (double)$estado;
$_ciudad = (double)$ciudad;
$_sector = (double)$sector;
$key = $_estado . '.' . $_ciudad . '.' . $_sector;
return Api::$enums['Distrito'][$key];
}
function unidades_array(){
    return array("Sp"=>"Servicios Profesionales","m"=>"Metro","kg"=>"Kilogramo","s"=>"Segundo","A"=>"Ampere","K"=>"Kelvin","mol"=>"Mol","cd"=>"Candela","m²"=>"Metro Cuadrado","m³"=>"Metro Cúbico","m/s"=>"Metro por segundo","m/s²"=>"Metro por segundo cuadrado","1/m"=>"1 por metro","kg/m³"=>"kilogramo por metro cúbico","A/m²"=>"ampere por metro cuadrado","A/m"=>"ampere por metro","mol/m³"=>"mol por metro cúbico","cd/m²"=>"candela por metro cuadrado","1"=>"uno (indice de refracción)","rad"=>"Radián"
    ,"sr"=>"Estereorradián","Hz"=>"hertz","N"=>"Newton","Pa"=>"Pascal","J"=>"Joule","W"=>"Watt","C"=>"coulomb","V"=>"Volt","F"=>"Farad","?"=>"Ohm","S"=>"Siemens","Wb"=>"Weber","T"=>"Tesla","H"=>"Henry","°C"=>"Grado Celsius","lm"=>"Lumen","lx"=>"Lux","Bq"=>"Becquerel","Gy"=>"Gray","Sv"=>"Sievert"
    ,"kat"=>"Katal","Pa·s"=>"Pascal segundo","N·m"=>"Newton metro","N/m"=>"Newton por metro","rad/s"=>"Radián por segundo","rad/s²"=>"Radián por segundo cuadrado","W/m²"=>"Watt por metro cuadrado","J/K"=>"Joule por kelvin","J/(kg·K)"=>"Joule por kilogramo kelvin","J/kg"=>"Joule por kilogramo","W/(m·K)"=>"Watt por metro kevin","J/m³"=>"Joule por metro cúbico","V/m"=>"Volt por metro","C/m³"=>"Coulomb por metro cúbico","C/m²"=>"Coulomb por metro cuadrado","F/m"=>"Farad por metro","H/m"=>"Henry por metro","J/mol"=>"Joule por mol","J/(mol·K)"=>"Joule por mol kelvin","C/kg"=>"Coulomb por kilogramo"
    ,"Gy/s"=>"Gray por segundo","W/sr"=>"Watt por estereorradián","W/(m²·sr)"=>"Watt por metro cuadrado estereorradián","kat/m³"=>"Katal por metro cúbico","min"=>"Minuto","h"=>"Hora","d"=>"Día","º"=>"Grado","'"=>"Minuto","''"=>"Segundo","L"=>"Litro","t"=>"Tonelada","Np"=>"Neper","B"=>"Bel","eV"=>"Electronvolt","u"=>"Unidad de masa atómica unificada","ua"=>"Unidad astronómica","Unid"=>"Unidad","Gal"=>"Galón","g"=>"Gramo"
    ,"Km"=>"Kilometro","ln"=>"Pulgada","cm"=>"Centímetro","mL"=>"Mililitro","mm"=>"Milímetro","Oz"=>"Onzas");
}
function identificacion_nombre($type_str){
    $type_number = $type_str;
    return Api::$enums['factura']['Sender']['Identificacion']['Tipo']['enum'][$type_number];
}

//convierte xml en array
function xml_to_array($pxml,$arr=TRUE){
    $xmlstring = file_get_contents($pxml);
    $pxml = simplexml_load_string($xmlstring, "SimpleXMLElement", LIBXML_NOCDATA);
    $json = json_encode($pxml);
    $xml_array = json_decode($json,$arr);
    return $xml_array;
}

//convierte xml en pdf
function rendered_factura($xml_array){

//formatea resumen de la factura
$lines = $xml_array['DetalleServicio']['LineaDetalle'];
if (isset($lines['NumeroLinea']) == true){
    $lines = array($lines);
}
$fechaEmision = new DateTime($xml_array['FechaEmision']);
$fechaEmision = $fechaEmision->format('d/m/Y');
$emisorNombreComercial = get_elem($xml_array,get_elem($xml_array,'','Emisor','Nombre'),'Emisor','NombreComercial');
$emisorNombre = ($emisorNombreComercial == get_elem($xml_array,'','Emisor','Nombre')?"":get_elem($xml_array,'','Emisor','Nombre'));
$emisorIdentificacionTipo = get_elem($xml_array,'01','Emisor','Identificacion','Tipo');
$emisorIdentificacionTipo = identificacion_nombre($emisorIdentificacionTipo);
$emisorIdentificacionNumero = format_cedula(get_elem($xml_array,'','Emisor','Identificacion','Numero'));
$emisorUbicacionProvincia = ucwords(strtolower(provincia(get_elem($xml_array,'1','Emisor','Ubicacion','Provincia'))));
$emisorUbicacionCanton = ucwords(strtolower(canton(get_elem($xml_array,'1','Emisor','Ubicacion','Provincia'),get_elem($xml_array,'1','Emisor','Ubicacion','Canton'))));
$emisorUbicacionDistrito = ucwords(strtolower(distrito(get_elem($xml_array,'1','Emisor','Ubicacion','Provincia'),get_elem($xml_array,'1','Emisor','Ubicacion','Canton'),get_elem($xml_array,'1','Emisor','Ubicacion','Distrito'))));
$emisorTelefono = get_elem($xml_array,'','Emisor','Telefono','NumTelefono');
$emisorTelefono = format_tel($emisorTelefono);
$emisorUbicacionOtrasSenas = get_elem($xml_array,'.','Emisor','Ubicacion','OtrasSenas');
$receptorIdentificacionTipo = get_elem($xml_array,'01','Receptor','Identificacion','Tipo');
$receptorIdentificacionTipo = identificacion_nombre($receptorIdentificacionTipo);
$receptorIdentificacionNumero = format_cedula(get_elem($xml_array,'','Receptor','Identificacion','Numero'));
$receptorUbicacionProvincia = ucwords(strtolower(provincia(get_elem($xml_array,'','Receptor','Ubicacion','Provincia'))));
$receptorUbicacionCanton = ucwords(strtolower(canton(get_elem($xml_array,'','Receptor','Ubicacion','Provincia'),get_elem($xml_array,'','Receptor','Ubicacion','Canton'))));
$receptorUbicacionDistrito = ucwords(strtolower(distrito(get_elem($xml_array,'','Receptor','Ubicacion','Provincia'),get_elem($xml_array,'','Receptor','Ubicacion','Canton'),get_elem($xml_array,'','Receptor','Ubicacion','Distrito'))));
$receptorTelefono = get_elem($xml_array,'','Receptor','Telefono','NumTelefono');
$receptorTelefono = format_tel($receptorTelefono);
$receptorUbicacionOtrasSenas = get_elem($xml_array,'.','Receptor','Ubicacion','OtrasSenas');
$totalVentaNeta = number_format(get_elem($xml_array,'0','ResumenFactura','TotalVentaNeta'),2,".",",");
$totalImpuesto = number_format(get_elem($xml_array,'0','ResumenFactura','TotalImpuesto'),2,".",",");
$totalComprobante = number_format(get_elem($xml_array,'0','ResumenFactura','TotalComprobante'),2,".",",");

// use the template
ob_start();
require(File_name::resource('/template/header.php'));
echo '<thead>
<tr>';
$descuentos = 0;
$codigos = 0;
foreach ($lines as $line){
    $codigos += (isset($line['Codigo'])==true?1:0);
    $descuentos += get_elem($line,'0','MontoDescuento');
}
if ($codigos != 0){
echo '<td class="header" style="text-align: center;">C&oacute;digo</td>';
}
echo '<td class="header" style="text-align: right;">Cantidad</td>
<td class="header" style="text-align: center;">Unidad<br />Medida</td>
<td class="header" style="text-align: center;">Descripci&oacute;n del<br />Producto/Servicio</td>
<td class="header" style="text-align: center;">Precio<br />Unitario</td>';
if ($descuentos != 0){
    echo '<td class="header" style="text-align: center;">Descuento</td>
<td class="header" style="text-align: center;">Naturaleza del&nbsp;<br />Descuento.</td>';
}
echo '<td class="header" style="text-align: right;">Monto<br />Impuestos</td>
<td class="header" style="text-align: right;">SubTotal</td>
</tr>
</thead>
<tbody>';

$ivTally = 0;                           // tally de impuesto de ventas
$otherImpuestosTally = 0;               // tally de otros impuestos
foreach ($lines as $line){
    //formatea valores de cada linea de detalle en la factura
    $codigoCodigo = get_elem($line,'','Codigo','Codigo');
    $cantidad = cantidad_format(get_elem($line,'1','Cantidad'));
    $unidadMedida = get_elem($line,unidades_array()[get_elem($line,'Unid','UnidadMedida')],'UnidadMedidaComercial');
    $lineaDetalle = get_elem($line,'','Detalle');
    $precioUnitario = number_format(get_elem($line,'0','PrecioUnitario'),2,".",",");
    $montoDescuento = number_format(get_elem($line,'0','MontoDescuento'),2,".",",");
    $naturalezaDescuento = get_elem($line,'','NaturalezaDescuento');

    //lee impuestos
    $impuestos = get_elem($line,array(),'Impuesto');
    if (isset($impuestos['Monto'])){
        $impuestos = array($impuestos);
    }
    //acumula los impuestos por linea
    $ivLinea = 0;
    $otrosImpuestosLinea = 0;
    foreach($impuestos as $impuesto){
        if ($impuesto['Codigo'] == '01'){
           $ivLinea += (double)$impuesto['Monto']; 
        }else{
           $otrosImpuestosLinea += (double)$impuesto['Monto']; 
        }
    }
    //acumula los impuestos por factura
    $ivTally += $ivLinea;
    $otherImpuestosTally += $otrosImpuestosLinea;
    $impuestoLinea = number_format(($ivLinea+$otrosImpuestosLinea)."",2,".",",");

    $montoTotalLinea = number_format(get_elem($line,'0','MontoTotalLinea'),2,".",",");
    echo "<tr>";
    //si codigo entonces imprime columna
    if ($codigos != 0){
        echo "<td class='detail' style='text-align: center;'>{$codigoCodigo}</td>";
    }
    echo "<td class='detail' style='text-align: right;'>{$cantidad}</td>
<td class='detail' style='text-align: center;'>{$unidadMedida}</td>
<td class='detail' style='text-align: center;'>{$lineaDetalle}</td>
<td class='detail' style='text-align: right;'>{$precioUnitario}</td>";
    //si descuentos entonces imprime columnas
    if ($descuentos != 0){
        echo "<td class='detail' style='text-align: right;'>{$montoDescuento}</td>
<td class='detail' style='text-align: center;'>{$naturalezaDescuento}&nbsp;</td>";
    }
    echo "<td class='detail' style='text-align: right;'>{$impuestoLinea}</td>
<td class='detail' style='text-align: right;'>{$montoTotalLinea}</td>
</tr>";

}
//formatea tallys de la factura
$totalImpuestoVenta =  number_format($ivTally."",2,".",",");
$totalImpuestos = (double) get_elem($xml_array,'0','ResumenFactura','TotalImpuesto');
$totalOtrosImpuestos = round($totalImpuestos -$ivTally,2);
$totalOtrosImpuestos =  number_format($totalOtrosImpuestos,2,".",",");

require(File_name::resource('/template/footer.php'));
$html = ob_get_contents();
ob_end_clean();

// turn output into a PDF
$dompdf = new DOMPDF();
$dompdf->load_html($html);
$dompdf->set_paper("letter","portrait");
$dompdf->render();
$pdfoutput = $dompdf->output();
return $pdfoutput;
}

//envia los 3 documentos xml, xml-response y pdf al receptor
function email3Documents($email_addresss = '',$email = array(),$xml_array = array(),$subject='',$body='',$bodyHTML='',$xml_file_name='factura.xml',$xml_response_file_name='xml-response.xml',$pdf_string = ''){
    var_dump($email);
    $mail = new PHPMailer(true);                                // Passing `true` enables exceptions
    try {
        //Server settings
        $mail->SMTPDebug = 0;                                   // 2 Enable verbose debug output
        $mail->isSMTP();                                        // Set mailer to use SMTP
        $mail->Host = $email->host; //['host'];                           // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                                 // Enable SMTP authentication
        $mail->Username = $email->username; //['username'];     // SMTP username
        $mail->Password = $email->password; //['password'];     // SMTP password
        $mail->SMTPSecure = 'ssl';//tls';                       // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 465; //587;                               // TCP port to connect to

        //Recipients
        $mail->setFrom($email->from, $xml_array['Emisor']['NombreComercial']);
        $mail->addAddress($email_addresss);     // Add a recipient

        //Attachments
        $mail->addAttachment($xml_file_name,'factura.xml');                         // Add attachments
        $mail->addAttachment($xml_response_file_name,'xml-response.xml');           // Add attachments
        $mail->addStringAttachment($pdf_string, 'factura.pdf');

        //Content
        $mail->isHTML(true);                                    // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = $bodyHTML;

        $mail->send();
        echo 'Message has been sent';
    } catch (Exception $e) {
        echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
    }
}

function _email_factura($email,$xml_factura_name,$xml_response_name){
$xml_array = xml_to_array($xml_factura_name);
$pdf_string = rendered_factura($xml_array);

$numeroConsecutivo = $xml_array['NumeroConsecutivo'];
$emisorNombreComercial = get_elem($xml_array,get_elem($xml_array,'','Emisor','Nombre'),'Emisor','NombreComercial');
$emisorNombre = get_elem($xml_array,'','Emisor','Nombre');
$subject = "Factura Electrónica: N° $numeroConsecutivo del Emisor: $emisorNombreComercial";
$body = "Factura Electronica N° $numeroConsecutivo

Emitida por: $emisorNombre
Nombre Comercial: $emisorNombreComercial

Generada por medio de {$email->from_url}";
$bodyHTML = $body;
$xml_array['Emisor']['NombreComercial'] = $emisorNombreComercial; 

email3Documents($xml_array['Receptor']['CorreoElectronico'],$email,$xml_array,$subject,$body,$bodyHTML,$xml_factura_name,$xml_response_name,$pdf_string);    
}

// funcion principal inicio del email
function email_factura($sender,$clave){
    $xml_factura = File_name::documento($sender->id,$clave,'01');
    if (!file_exists($xml_factura)){
        return;
    }
    $data = xml_to_array($xml_factura,FALSE);
    if ((Model::get_documento_tipo($data) == '01' || Model::get_documento_tipo($data) == '04') && property_exists($data,'Receptor') && property_exists($data->Receptor,'CorreoElectronico') && property_exists($data->Receptor,'Identificacion')){
        $xml_response = File_name::xml_response($sender,$clave,'01');
        _email_factura($sender->email,$xml_factura,$xml_response);
    }
}