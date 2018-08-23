<?php
date_default_timezone_set('America/Costa_Rica');

function assignNVL2(&$targetBranch,$fieldName,$default,$pad=0){
    if (property_exists($targetBranch,$fieldName)==false){
        $targetBranch->$fieldName = $default;
    }
    if ($pad > 0){
        $targetBranch->$fieldName = str_pad($targetBranch->$fieldName,$pad,'0',STR_PAD_LEFT);
    }
}

function assignNVL(&$targetBranch,$fieldName,$value,$format = 'dinero'){
    if (isset($value) && $value > 0){
        if ($format == "dinero"){
            //echo "$format<br>";
            //$value = number_format($value,5,".","");
        }
        $targetBranch->$fieldName = $value;
        
    }else{
        $targetBranch->$fieldName = "0";//.00000";//            echo "out<br>";
    }
}
function nvl2(&$originBranch,$nodeName,$default=0){
    if (!is_null($originBranch) && property_exists($originBranch,$nodeName)){
        return $originBranch->$nodeName;
    }else{
        return $default;
    }
}
function nvl(&$var, $default = 0, $pad = 0)
{
    $value = isset($var)==true ? $var
                       : $default;
    if ($pad > 0){
        $value = str_pad($value,'0',$pad,STR_PAD_LEFT);
    }
    return $value;
}
function appendNVL($xmlDoc,$targetBranch,$originBranch,$nodeName){

    if (property_exists($originBranch,$nodeName)){
        return $targetBranch->appendChild($xmlDoc->createElement($nodeName, $originBranch->{$nodeName}));
    }
    return null;
}
function now_c(){
    return date_format(date_create(null, timezone_open('America/Costa_Rica')),'c');
}

function now_f(){
    return date('Y-m-d')."T".date('H:i:s')."Z"; //date('Y-m-d')."T".date('H:i:s')."Z"
}

function set_db_connection(&$conn){
    if ($conn instanceof Connection && is_resource($conn)){
        return;
    }
    $conn = new Connection();
}