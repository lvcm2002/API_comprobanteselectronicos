<?php
class Bk_consulta{
    public static function recepcion($purl,$ptoken, $pclave, $pconsecutivo=''){
        if ($pconsecutivo != ''){
            $pconsecutivo = '-'.$pconsecutivo;
        }
        $url = $purl.'recepcion/'.$pclave.$pconsecutivo;
        //var_dump($url);
        $curl = curl_init();
        curl_setopt_array($curl, array(
                CURLOPT_URL => "".$url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "authorization: ".$ptoken,
                    "cache-control: no-cache",
                    "content-type: application/x-www-form-urlencoded",
                    "postman-token: 65becdcc-f3f2-8598-f38a-9f6d9adb803a"
                ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        $info = curl_getinfo($curl, CURLINFO_HTTP_CODE);//CURLOPT_HTTPHEADER);
        curl_close($curl);

        
        $arrayResp = array(
		        "Status" => $info,
		        "text" => explode("\n", $response)
		    );
        $respObj = json_decode($response);        
        
        if ($err || $info == 400) {
                $respuesta = "cURL Error #:" . $err . " Info: " . $info;
                return  $respuesta;
        } else {
                return $response;

        }
    }    
    
    function comprobante_by_clave($token,$pclave){
        $url = $this->url."comprobantes?clave=".$pclave."";
        $curl = curl_init();
        curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                "authorization: ".$token,
                        "cache-control: no-cache",
                "postman-token: 689c8b8b-789b-94a3-ba89-607cb3338a5d"
                ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            $respuesta = "cURL Error #:" . $err;
            return  $respuesta;
        } else {
            return $response;
        }
    }
    
    function comprobantes_all($ptoken,$pdata){
        $url = $this->url."comprobantes?offset=1&limit=10&emisor=".$pdata->Emisor->Identificacion->Tipo.$pdata->Emisor->Identificacion->Numero."&receptor=".$pdata->Receptor->Identificacion->Tipo.$pdata->Receptor->Identificacion->Numero."";
        $token = $ptoken;
        $curl = curl_init();
        curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                "authorization: ".$token,
                "cache-control: no-cache",
                "postman-token: 689c8b8b-789b-94a3-ba89-607cb3338a5d"
                ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
                $respuesta = "cURL Error #:" . $err;
                return  $respuesta;
        } else {
                return $response;
        }
    }    
    public static function is_connected()
    {   
        $connected = @fsockopen("www.hacienda.go.cr", 80);

        if ($connected){
            $is_conn = true; //action when connected
            fclose($connected);
        }else{
            $is_conn = false; //action in connection failure
        }
        return $is_conn;
    }
}