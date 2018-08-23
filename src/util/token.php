<?php
class Token{
    public function get($sender){
            $string = "username=".$sender->username_ws."&password=".$sender->password_ws."&grant_type=password&client_id=".$sender->client_id_ws."";

            $curl = curl_init();
            curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://idp.comprobanteselectronicos.go.cr/auth/realms/rut-stag/protocol/openid-connect/token",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => $string,
                    CURLOPT_HTTPHEADER => array(
                    "cache-control: no-cache",
                    "content-type: application/x-www-form-urlencoded",
                    "postman-token: c1016240-cf6f-fe54-67d6-587ad9b11c39"
                    ),
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                    $respuesta = "cURL Error #:" . $err;
                    return  $respuesta;
            } else {
                    $response = json_decode($response);
                    $respuesta = 'bearer '.$response->{'access_token'};
                    return $respuesta;
            }
    }
}