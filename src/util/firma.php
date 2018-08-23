<?php
class Firma{
    private $pass, $url, $username_ws, $password_ws, $client_id_ws, $serial;
    private $config; 
    private $certs; 
    private $name_space; 

    public function __construct($sender){
        $this->pass = $sender->pass;
        $this->url = $sender->url;
        $this->username_ws = $sender->username_ws;
        $this->password_ws = $sender->password_ws;
        $this->client_id_ws = $sender->client_id_ws;
        $this->serial = $sender->serial;
        $this->config = $sender->config;
        $this->certs = $sender->certs;
        $this->name_space = $sender->name_space;
    }
    
    function xml($pxml,$fechaFirma=null){
        return "";
    }

}