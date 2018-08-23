<?php
Api::_include_once('./consultas/conexion.php');
Api::_include_once('./util/util.php');

class Db_model {

public static $db;
public $model;

public function __construct($pmodel) {
  set_db_connection(Db_model::$db);
  $this->model = $pmodel;
}


public function init_consecutivo(){
    $qry = "INSERT INTO numeracion (id, cedula_tipo, cedula_numero, agencia_codigo, terminal_codigo, documento_tipo, numeracion) VALUES (NULL, '{$this->model->Sender->Identificacion->Tipo}', '{$this->model->Sender->Identificacion->Numero}', '{$this->model->Agencia}', '{$this->model->Terminal}', '{$this->model->Documento->Tipo}', 1)";
    $sql = Db_model::$db->query($qry);
    return $qry;
}

public function get_consecutivo(){
    $qry = "SELECT numeracion FROM numeracion WHERE cedula_tipo = '{$this->model->Sender->Identificacion->Tipo}' AND cedula_numero = '{$this->model->Sender->Identificacion->Numero}' AND agencia_codigo = '{$this->model->Agencia}' AND terminal_codigo = '{$this->model->Terminal}' AND documento_tipo = '{$this->model->Documento->Tipo}' ";
    $results = Db_model::$db->query($qry);
    $numeracion = (Db_model::$db->num_rows($results) ?$results->fetch_all(MYSQLI_ASSOC)[0]['numeracion'] :0);
    $sql = str_pad($numeracion,10,'0',STR_PAD_LEFT);
    return $sql;
}
public function updt_consecutivo(){
    $qry = "UPDATE numeracion SET numeracion=numeracion+1 WHERE cedula_tipo = '{$this->model->Sender->Identificacion->Tipo}' AND cedula_numero = '{$this->model->Sender->Identificacion->Numero}' AND agencia_codigo = '{$this->model->Agencia}' AND terminal_codigo = '{$this->model->Terminal}' AND documento_tipo = '{$this->model->Documento->Tipo}'";
    $sql = Db_model::$db->query($qry);
}
public function inc_consecutivo(){
    if ($this->get_consecutivo() == '0000000000'){
        $this->init_consecutivo();
    }else{
        $this->updt_consecutivo();
    }
    return $this->get_consecutivo();
}
function insert_resumen_documento($model){
    $qry = <<<EOT
INSERT INTO resumen_documento (
    sign,
    clave, 
    sender_cedula_tipo, 
    sender_cedula_numero, 
    ind_estado,
    cedula_tipo, 
    cedula_numero, 
    agencia_codigo, 
    terminal_codigo, 
    documento_tipo, 
    fecha_emision, 
    fecha_emision_v, 
    numero_consecutivo, 
    condicion_venta,
    plazo_credito,
    medio_pago,
    moneda_codigo, 
    tipo_cambio, 
    serv_gravados, 
    serv_exentos, 
    mercancias_gravadas, 
    mercancias_exentas, 
    gravado, 
    exento, 
    venta, 
    descuentos, 
    venta_neta, 
    impuesto, 
    total_comprobante) 
VALUES (
    {$model->sign},
    '{$model->Clave}', 
    '{$model->Sender->Identificacion->Tipo}', 
    '{$model->Sender->Identificacion->Numero}', 
    'queue',
    '{$model->Emisor->Identificacion->Tipo}', 
    '{$model->Emisor->Identificacion->Numero}', 
    '{$model->Agencia}', 
    '{$model->Terminal}', 
    '{$model->Documento->Tipo}', 
    '{$model->FechaEmision}', 
    '{$model->FechaEmision}', 
    '{$model->NumeroConsecutivo}', 
    '{$model->CondicionVenta}', 
    '{$model->PlazoCredito}', 
    '{$model->MedioPago}', 
    '{$model->ResumenFactura->CodigoMoneda}', 
    '{$model->ResumenFactura->TipoCambio}', 
    '{$model->ResumenFactura->TotalServGravados}', 
    '{$model->ResumenFactura->TotalServExentos}', 
    '{$model->ResumenFactura->TotalMercanciasGravadas}', 
    '{$model->ResumenFactura->TotalMercanciasExentas}', 
    '{$model->ResumenFactura->TotalGravado}', 
    '{$model->ResumenFactura->TotalExento}', 
    '{$model->ResumenFactura->TotalVenta}', 
    '{$model->ResumenFactura->TotalDescuentos}', 
    '{$model->ResumenFactura->TotalVentaNeta}', 
    '{$model->ResumenFactura->TotalImpuesto}', 
    '{$model->ResumenFactura->TotalComprobante}');
EOT;
    $sql = Db_model::$db->query($qry);
}

public static function updt_resumen_documento($sender_tipo,$sender_numero,$clave,$ind_estado){
    $qry = "UPDATE resumen_documento SET ind_estado='{$ind_estado}' WHERE cedula_tipo = '{$sender_tipo}' AND cedula_numero = '{$sender_numero}' AND clave = '{$clave}'";
    $sql = Db_model::$db->query($qry);
}

public static function consulta_detalle_de_documentos_pendientes($tipo,$numero){
    $qry = <<<EOT
SELECT
    sender_cedula_tipo as tipo,sender_cedula_numero as numero,clave,numero_consecutivo_receptor consecutivo
FROM resumen_documento r
WHERE
    sender_cedula_tipo = '{$tipo}' AND 
    sender_cedula_numero = '{$numero}' AND 
    ind_estado != 'rechazado' AND ind_estado != 'aceptado'
ORDER BY 
    tipo,numero,clave
EOT;
    //var_dump($qry);
    return Db_model::$db->fetch_json($qry);
}

function consulta_detalle_de_documentos_rechazados_sin_remplazo($model){
    $qry = <<<EOT
SELECT
    ind_estado,
    sender_cedula_tipo, 
    sender_cedula_numero, 
    agencia_codigo,
    terminal_codigo,
    numero_consecutivo, 
    DATE(fecha_emision) as fecha,
    clave,
    documento_tipo,
    moneda_codigo,
    tipo_cambio,
    (serv_gravados*sign) as serv_gravados, 
    (serv_exentos*sign) as serv_exentos, 
    (mercancias_gravadas*sign) as mercancias_gravadas, 
    (mercancias_exentas*sign) as mercancias_exentas, 
    (gravado*sign) as gravado, 
    (exento*sign) as exento, 
    (venta*sign) as venta, 
    (descuentos*sign) as descuentos, 
    (venta_neta*sign) as venta_neta, 
    (impuesto*sign) as impuesto, 
    (total_comprobante*sign) as total_comprobante
FROM resumen_documento r
WHERE
    sender_cedula_tipo = '{$model->Sender->Identificacion->Tipo}' AND 
    sender_cedula_numero = '{$model->Sender->Identificacion->Numero}' AND 
    ind_estado = 'rechazado' AND
    r.clave not in (select documento_numero from informacion_referencia i where r.documento_tipo in ('01','04')) 
ORDER BY 
    fecha_emision
EOT;
    return Db_model::$db->fetch_json($qry);
}

function consulta_detalle_de_rechazos_sin_FE_TE_reimpreso($model){
    $qry = <<<EOT
SELECT *,if (r.clave not in (select documento_numero from informacion_referencia),'0','1' ) as referentes
FROM resumen_documento r
WHERE
    sender_cedula_tipo = '01' AND 
    sender_cedula_numero = '000114050239' AND 
    ind_estado = 'rechazado' AND
    r.clave not in (select documento_numero from informacion_referencia i where clave_documento_tipo in ('01','04')) 
ORDER BY 
    fecha_emision
EOT;
    return Db_model::$db->fetch_json($qry);
}

function consulta_detalle($model){
    $qry = <<<EOT
SELECT 
    clave, 
    sender_cedula_tipo, 
    sender_cedula_numero, 
    ind_estado,
    cedula_tipo, 
    cedula_numero, 
    agencia_codigo, 
    terminal_codigo, 
    documento_tipo, 
    fecha_emision, 
    fecha_emision_v, 
    numero_consecutivo, 
    moneda_codigo, 
    tipo_cambio, 
    serv_gravados*sign, 
    serv_exentos*sign, 
    mercancias_gravadas*sign, 
    mercancias_exentas*sign, 
    gravado*sign, 
    exento*sign, 
    venta*sign, 
    descuentos*sign, 
    venta_neta*sign, 
    impuesto*sign, 
    total_comprobante*sign 
FROM resumen_documento 
WHERE 
    cedula_tipo = '{$model->Sender->Identificacion->Tipo}' AND 
    cedula_numero = '{$model->Sender->Identificacion->Numero}' AND 
    agencia_codigo like '%{$model->Agencia}%' AND 
    terminal_codigo like '%{$model->Terminal}%' AND 
    documento_tipo like '%{$model->Documento->Tipo}%'
EOT;
//        AND 
//    fecha_emision >= '{$model->FechaEmision0}' AND
//    fecha_emision < '{$model->FechaEmision1}';
    return Db_model::$db->fetch_json($qry);
}

public function qry_resumen_diario_por_tipo_de_documento($data){
    $qry = <<<EOT
SELECT
    sender_cedula_tipo, 
    sender_cedula_numero, 
    DATE(fecha_emision) as fecha,
    documento_tipo,
    sum(serv_gravados*sign) as serv_gravados, 
    sum(serv_exentos*sign) as serv_exentos, 
    sum(mercancias_gravadas*sign) as mercancias_gravadas, 
    sum(mercancias_exentas*sign) as mercancias_exentas, 
    sum(gravado*sign) as gravado, 
    sum(exento*sign) as exento, 
    sum(venta*sign) as venta, 
    sum(descuentos*sign) as descuentos, 
    sum(venta_neta*sign) as venta_neta, 
    sum(impuesto*sign) as impuesto, 
    sum(total_comprobante*sign) as total_comprobante
FROM resumen_documento r
WHERE
    sender_cedula_tipo = '{$data->Sender->Identificacion->Tipo}' AND 
    sender_cedula_numero = '{$data->Sender->Identificacion->Numero}'
GROUP BY 
    DATE(fecha_emision),
    documento_tipo
ORDER BY 
    DATE(fecha_emision),
    documento_tipo
EOT;
    return Db_model::$db->fetch_json($qry);
}

public function qry_resumen_diario($data){
    $qry = <<<EOT
SELECT
    sender_cedula_tipo, 
    sender_cedula_numero, 
    DATE(fecha_emision) as fecha,
    sum(serv_gravados*sign) as serv_gravados, 
    sum(serv_exentos*sign) as serv_exentos, 
    sum(mercancias_gravadas*sign) as mercancias_gravadas, 
    sum(mercancias_exentas*sign) as mercancias_exentas, 
    sum(gravado*sign) as gravado, 
    sum(exento*sign) as exento, 
    sum(venta*sign) as venta, 
    sum(descuentos*sign) as descuentos, 
    sum(venta_neta*sign) as venta_neta, 
    sum(impuesto*sign) as impuesto, 
    sum(total_comprobante*sign) as total_comprobante
FROM resumen_documento r
WHERE
    sender_cedula_tipo = '{$data->Sender->Identificacion->Tipo}' AND 
    sender_cedula_numero = '{$data->Sender->Identificacion->Numero}' AND 
    ind_estado = 'aceptado'
GROUP BY 
    DATE(fecha_emision)
ORDER BY 
    DATE(fecha_emision)
EOT;
    return Db_model::$db->fetch_json($qry);
}

public function qry_resumen_diario_de_impuestos_de_venta($data){
    $qry = <<<EOT
SELECT
    sender_cedula_tipo, 
    sender_cedula_numero, 
    DATE(fecha_emision) as fecha,
    sum(serv_gravados*sign) as serv_gravados, 
    sum(serv_exentos*sign) as serv_exentos, 
    sum(mercancias_gravadas*sign) as mercancias_gravadas, 
    sum(mercancias_exentas*sign) as mercancias_exentas, 
    sum(gravado*sign) as gravado, 
    sum(exento*sign) as exento, 
    sum(venta*sign) as venta, 
    sum(descuentos*sign) as descuentos, 
    sum(venta_neta*sign) as venta_neta, 
    sum(impuesto*sign) as impuesto,
    sum(monto_original_13*sign) as iv_original_13,
    sum(monto_original_10*sign) as iv_original_10,
    sum(monto_original_5*sign) as iv_original_5,
    sum(monto_original*sign) as iv_original,
    sum(monto_13*sign) as iv_13,
    sum(monto_10*sign) as iv_10,
    sum(monto_5*sign) as iv_5,  
    sum(monto*sign) as iv,  
    sum(total_comprobante*sign) as total_comprobante
FROM resumen_documento r,
 (
select 
 resumen_documento_id,
 sum(if(tarifa_original = 13,monto_original,0)) as monto_original_13 ,
 sum(if(tarifa_original = 10,monto_original,0)) as monto_original_10 ,
 sum(if(tarifa_original = 5,monto_original,0)) as monto_original_5, 
 sum(monto_original) as monto_original,
 sum(if(tarifa_original = 13,monto,0)) as monto_13 ,
 sum(if(tarifa_original = 10,monto,0)) as monto_10 ,
 sum(if(tarifa_original = 5,monto,0)) as monto_5, 
 sum(monto) as monto
from detalle_impuestos group by resumen_documento_id
  ) as t
WHERE
    t.resumen_documento_id = r.id AND
    sender_cedula_tipo = '{$data->Sender->Identificacion->Tipo}' AND 
    sender_cedula_numero = '{$data->Sender->Identificacion->Numero}'
GROUP BY 
    DATE(fecha_emision),
    documento_tipo
ORDER BY 
    DATE(fecha_emision),
    documento_tipo
EOT;
    return Db_model::$db->fetch_json($qry);
}

public function qry_resumen_mes_de_impuestos_de_venta($data){
    $qry = <<<EOT
SELECT
    sender_cedula_tipo, 
    sender_cedula_numero, 
    YEAR(fecha_emision) as year,
    MONTH(fecha_emision) as month,
    sum(serv_gravados*sign) as serv_gravados, 
    sum(serv_exentos*sign) as serv_exentos, 
    sum(mercancias_gravadas*sign) as mercancias_gravadas, 
    sum(mercancias_exentas*sign) as mercancias_exentas, 
    sum(gravado*sign) as gravado, 
    sum(exento*sign) as exento, 
    sum(venta*sign) as venta, 
    sum(descuentos*sign) as descuentos, 
    sum(venta_neta*sign) as venta_neta, 
    sum(impuesto*sign) as impuesto,
    sum(monto_original_13*sign) as iv_original_13,
    sum(monto_original_10*sign) as iv_original_10,
    sum(monto_original_5*sign) as iv_original_5,
    sum(monto_original*sign) as iv_original,
    sum(monto_13*sign) as iv_13,
    sum(monto_10*sign) as iv_10,
    sum(monto_5*sign) as iv_5,  
    sum(monto*sign) as iv,  
    sum(total_comprobante*sign) as total_comprobante
FROM resumen_documento r,
 (
select 
 resumen_documento_id,
 sum(if(tarifa_original = 13,monto_original,0)) as monto_original_13 ,
 sum(if(tarifa_original = 10,monto_original,0)) as monto_original_10 ,
 sum(if(tarifa_original = 5,monto_original,0)) as monto_original_5, 
 sum(monto_original) as monto_original,
 sum(if(tarifa_original = 13,monto,0)) as monto_13 ,
 sum(if(tarifa_original = 10,monto,0)) as monto_10 ,
 sum(if(tarifa_original = 5,monto,0)) as monto_5, 
 sum(monto) as monto
from detalle_impuestos group by resumen_documento_id
  ) as t
WHERE
    t.resumen_documento_id = r.id AND
    sender_cedula_tipo = '{$data->Sender->Identificacion->Tipo}' AND 
    sender_cedula_numero = '{$data->Sender->Identificacion->Numero}'
GROUP BY 
    YEAR(fecha_emision),
    MONTH(fecha_emision),
    documento_tipo
ORDER BY 
    YEAR(fecha_emision),
    MONTH(fecha_emision),
    documento_tipo
EOT;
    return Db_model::$db->fetch_json($qry);
}

public function qry_resumen_mes($data){
    $qry = <<<EOT
SELECT
    sender_cedula_tipo, 
    sender_cedula_numero,
    YEAR(fecha_emision) as ano, 
    MONTHNAME(fecha_emision) as fecha,
    sum(serv_gravados*sign) as serv_gravados, 
    sum(serv_exentos*sign) as serv_exentos, 
    sum(mercancias_gravadas*sign) as mercancias_gravadas, 
    sum(mercancias_exentas*sign) as mercancias_exentas, 
    sum(gravado*sign) as gravado, 
    sum(exento*sign) as exento, 
    sum(venta*sign) as venta, 
    sum(descuentos*sign) as descuentos, 
    sum(venta_neta*sign) as venta_neta, 
    sum(impuesto*sign) as impuesto, 
    sum(total_comprobante*sign) as total_comprobante
FROM resumen_documento r
WHERE
    sender_cedula_tipo = '{$data->Sender->Identificacion->Tipo}' AND 
    sender_cedula_numero = '{$data->Sender->Identificacion->Numero}' AND 
    ind_estado = 'aceptado'
GROUP BY 
    YEAR(fecha_emision), MONTH(fecha_emision)
ORDER BY 
    YEAR(fecha_emision), MONTH(fecha_emision)
EOT;
    return Db_model::$db->fetch_json($qry);
}

}

// unitest consulta db y envia json
//$clave = "50606081800011405023900100001010000000070100000070";
//$example = Xml_model::file_get_model($clave);
//$db_model = new Db_model($example);
//var_dump($db_model->consulta_detalle($example));
//var_dump($db_model->consulta_detalle_de_documentos_rechazados_sin_remplazo($example));
//var_dump($db_model->consulta_detalle_de_rechazos_sin_FE_TE_reimpreso($example));
//var_dump($db_model->qry_resumen_diario_por_tipo_de_documento($example));
//var_dump($db_model->qry_resumen_diario($example));
//var_dump($db_model->qry_resumen_diario_de_impuestos_de_venta($example));
//var_dump($db_model->qry_resumen_mes_de_impuestos_de_venta($example));
//var_dump($db_model->qry_resumen_mes($example));

// unitest insert_resumen_documento
//$clave = "50606081800011405023900100001010000000070100000070";
//$example = Xml_model::file_get_model($clave);
//$db_model = new Db_model($example);
//$db_model->insert_resumen_documento($example);

// unitest inc_consecutivo
//$example = json_decode(file_get_contents("../example/FacturaElectronica.json"))->NotaCredito;
//$example->Sender = $example->Emisor;
//$modelObj = new Db_model();//$example);
//var_dump($modelObj->inc_consecutivo());

