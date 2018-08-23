<?php
Api::_include_once('./util/firma.php');
Api::_include_once('./util/token.php');
Api::_include_once('./util/model.php');
Api::_include_once('./email/email_factura.php');
class Sender{
    public $pass, $url, $username_ws, $password_ws, $client_id_ws, $serial;
    public $config; 
    public $certs; 
    public $name_space; 
    public $id;
    public $situacion;
    public $Tipo,$Numero;
    public $email;

    public function __construct($doc_data, array $config = []) {
        $this->id = Model::get_sender_id($doc_data);
        if (!$config) {
                        $config = [];
        }
        switch (Model::get_documento_tipo($doc_data)) {
            case '01':
                    $this->name_space = 'https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/facturaElectronica';
            break;
            case '02':
                    $this->name_space = 'https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/notaDebitoElectronica';
            break;
            case '03':
                    $this->name_space = 'https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/notaCreditoElectronica';
            break;
            case '04':
                    $this->name_space = 'https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/tiqueteElectronico';
            break;
            default:
                    $this->name_space = 'https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/mensajeReceptor';
        }
        Sender::set_user_pass_ws($this,$doc_data);

        $data = Api::_file_get_config_sender_p12(Model::get_sender_id($doc_data));
        $this->config = array_merge([
            'file' => '',
            'pass' => $this->pass,
            'data' => ''.$data,
            ], $config);

        if (!$this->config['data'] and $this->config['file']) {
            if (is_readable($this->config['file'])) {
                    $this->config['data'] = file_get_contents($this->config['file']);
            } else {
                    return $this->error('Archivo de la firma electrónica '.basename($this->config['file']).' no puede ser leído');
            }
        }

        if ($this->config['data'] and openssl_pkcs12_read($this->config['data'], $this->certs, $this->config['pass'])===false) {
            return $this->error('No fue posible leer los datos de la firma electrónica (verificar la contraseña)');
        }
        $this->data = openssl_x509_parse($this->certs['cert']);
        $this->serial = $this->data['serialNumber'];
        
        unset($this->config['data']);
    }

    public function email_factura($clave){
        email_factura($this,$clave);
    }
    public function consulta_recepcion($ptoken, $pclave, $pconsecutivo=''){
        return json_decode(Bk_consulta::recepcion($this->url,$ptoken, $pclave, $pconsecutivo));
    }
    
    public function firma_xml(&$xml,$fechaFirma){
        $firma = new Firma($this);
        return $firma->xml($xml,$fechaFirma);        
    }
    
    public function get_token(){
        $token = new Token();
        return $token->get($this);
    }
    
    public static function set_user_pass_ws(&$obj,$doc_data){
        $emisor_json = Api::_file_get_config_sender(Model::get_sender_id($doc_data));//file_get_contents(File_name::config_sender(Model::get_sender_id($doc_data).'.json'));
        $emisor_data = Front_end::withJson($emisor_json);
        $obj->username_ws = $emisor_data->id->username_ws;
        $obj->password_ws = urlencode($emisor_data->id->password_ws);
        $obj->client_id_ws = $emisor_data->id->client_id_ws;//'api-stag';//api-prod
        $obj->url = $emisor_data->id->url;
        $obj->pass = $emisor_data->id->pass;
        $obj->situacion = $emisor_data->id->situacion;
        $obj->Tipo = $emisor_data->Emisor->Identificacion->Tipo;
        $obj->Numero = $emisor_data->Emisor->Identificacion->Numero;
        $obj->email = $emisor_data->id->email;
    }    
}
