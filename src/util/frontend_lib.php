<?php
class XMLSerializer {

    public static function generateValidXmlFromObj(stdClass $obj, $node_block='nodes', $node_name='node') {
        $arr = get_object_vars($obj);
        return self::generateValidXmlFromArray($arr, $node_block, $node_name);
    }

    public static function generateValidXmlFromArray($array, $node_block='nodes', $node_name='node') {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>';

        $xml .= '<' . $node_block . '>';
        $xml .= self::generateXmlFromArray($array, $node_name);
        $xml .= '</' . $node_block . '>';

        return $xml;
    }

    private static function generateXmlFromArray($array, $node_name) {
        $xml = '';

        if (is_array($array) || is_object($array)) {
            foreach ($array as $key=>$value) {
                if (is_numeric($key)) {
                    //$key = $node_name;
                    $xml .= self::generateXmlFromArray($value, $node_name);
                    if ($key < (count($array)-1)){
                        $xml .= '</' .$node_name . '><' . $node_name . '>';
                    }
                }else{
                    if ($key == "Mercancia"){
                        self::generateXmlFromArray($value, $node_name);
                    }else{
                        $xml .= '<' . $key . '>' . self::generateXmlFromArray($value, $key) . '</' . $key . '>';                          
                    }
                }
            }
        } else {
            $xml = htmlspecialchars($array, ENT_QUOTES);
        }
        return $xml;
    }
}

class Front_end{
    public static function loopArray(&$data,$confObj,$lvl=0){
        foreach($data as $value){
            Front_end::loopObject($value,$confObj,$lvl+1);
        }
        unset($value);
    }
    public static function loopObject(&$data,$confObj,$lvl=0){
        if (is_array($data)){
////            echo "<br>";
            Front_end::loopArray($data,$confObj,$lvl);
            return;
        }
        if (!is_object($data)){
            if (isset($confObj['dinero'])){
                $data = number_format($data,5,".","");
            }
            if (isset($confObj['decimal'])){
                $data = number_format($data,$confObj['decimal'],".","");
            }
            if (isset($confObj['pad'])){
////                echo "{$data}/pad:{$confObj['pad']}<br>";
                $data = str_pad($data, $confObj['pad'], "0", STR_PAD_LEFT);
            }
            if (isset($confObj['max'])){
                $data = substr($data,0,$confObj['max']);
            }
////            echo "$data<br>";
            return;
        }
////        echo "<br>";
        foreach ($data as $key => &$value) {
////                    echo str_repeat("&nbsp;",$lvl*5)."$key =>";
                    //echo "k:["; var_dump($key); echo "] v:["; var_dump($value); echo "]<br>";
                    Front_end::loopObject($value,$confObj[$key],$lvl+1);
        }
        unset($value);
    }
    public static function isValid($xml_name,$xsd){
        $xml = new DOMDocument(); 
        $xml->loadXML($xml_name);
        if (!$xml->schemaValidate(File_name::resource($xsd))) {
            return 'DOMDocument::schemaValidate() Generated Errors!';
            //libxml_display_errors();
        }else{
            return 'ok';
        }
    }
    public static function formatea(&$data,$confObj){
        Front_end::loopObject($data,Front_end::getEnums());
        return $data;
    }
    public static function withJson($json){
        $data = json_decode(utf8_encode($json),false);
        return $data;
    }
    public static function getJsonEnums(){
        return json_encode(Front_end::getEnums());
    }
    public static function getEnums(){
        $factura_rules = json_decode(utf8_encode(Api::_file_get_resouce("datasets/factura_rules.json")),true);
        $factura = $factura_rules['factura'];
        $factura['Emisor']['Ubicacion']['Provincia']['enum']    = $factura_rules['Provincia'];
        $factura['Emisor']['Ubicacion']['Canton']['enum']       = $factura_rules['Canton'];
        $factura['Emisor']['Ubicacion']['Distrito']['enum']     = $factura_rules['Distrito'];
        $factura['Receptor']['Ubicacion']['Provincia']['enum']  = $factura_rules['Provincia'];
        $factura['Receptor']['Ubicacion']['Canton']['enum']     = $factura_rules['Canton'];
        $factura['Receptor']['Ubicacion']['Distrito']['enum']   = $factura_rules['Distrito'];
        return $factura;
    }
}