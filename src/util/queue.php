<?php
class Db_pending_queue extends Queue{
    private $base_dir;
    public function __construct(){
        $this->base_dir = File_name::document_base_dir();
    }
    private function put_db_pending_claves_in_queue(){
        $model = new stdClass();
        $db_model = new Db_model($model);
        $sender_dirs = array_slice(scandir($this->base_dir), 2);
        foreach($sender_dirs as $sender_id){
            list($tipo, $numero) = explode("-", $sender_id);
            $jsons = json_decode(Db_model::consulta_detalle_de_documentos_pendientes($tipo,$numero));
            //var_dump($jsons);
            $this->enqueue_array($sender_id,$jsons);
        }        
    }    
    public function dequeue(){
        $key_value_pair = parent::dequeue();
        $obj = new stdClass();
        $obj->Clave = $key_value_pair->value->clave;
        $obj->Emisor = new stdClass();
        $obj->Emisor->Identificacion = new stdClass();
        $obj->Emisor->Identificacion->Tipo = $key_value_pair->value->tipo;
        $obj->Emisor->Identificacion->Numero = $key_value_pair->value->numero;
        $obj->Receptor = $obj->Emisor;
        $obj->NumeroConsecutivoReceptor = $key_value_pair->value->consecutivo;
        $key_value_pair->value = $obj;
        return $key_value_pair;
    }
    public function run($process){
        $this->put_db_pending_claves_in_queue();
        while (!$this->isEmpty()){
            $sender_id_file_pair = $this->dequeue();
            $status = $process($sender_id_file_pair->key,$sender_id_file_pair->value);
            if (isset($status)){
                $this->setEmpty();
            }
        }
    }
}
class Pool_queue extends Queue{
    private $base_dir,$dir;
    public function __construct($pbase_dir,$pdir){
        $this->base_dir = $pbase_dir;
        $this->dir = $pdir;
    }
    private function put_file_structure_in_queue(){
        $sender_dirs = array_slice(scandir($this->base_dir), 2);
        foreach($sender_dirs as $sender_id){
            $jsons = array_slice(scandir($this->base_dir.$sender_id.$this->dir), 2);
            $this->enqueue_array($sender_id,$jsons);
        }        
    }
    public function dequeue(){
        $key_value_pair = parent::dequeue();
        $key_value_pair->value = file_get_contents($this->base_dir.$key_value_pair->key.$this->dir.$key_value_pair->value);
        return $key_value_pair;
    }
    public function run($process){
        $this->put_file_structure_in_queue();
        while (!$this->isEmpty()){
            $sender_id_file_pair = $this->dequeue();
            $status = $process($sender_id_file_pair->key,$sender_id_file_pair->value);
            if (isset($status)){
                $this->setEmpty();
            }
        }
    }

}
class Xml_pool3_queue extends Pool_queue{
    public function __construct(){
        parent::__construct(File_name::document_base_dir(),File_name::pool3_dir());
    }
}
class Xml_pool2_queue extends Pool_queue{
    public function __construct(){
        parent::__construct(File_name::document_base_dir(),File_name::pool2_dir());
    }
}
class Json_pool_queue extends Pool_queue{
    public function __construct(){
        parent::__construct(File_name::document_base_dir(),File_name::json_pool_dir());
    }
}
class Key_value_pair{
    public $key;
    public $value;
    public function __construct($pkey,$pvalue){
        $this->key = $pkey;
        $this->value = $pvalue;
    }
}
class Queue_element{
    public $key_value_pair;
    public $next;
}
class Queue{
    private $font = null;
    private $back = null; 

    public function isEmpty(){
        return $this->font == null;
    }
    public function setEmpty(){
        while (!$this->isEmpty()){
            $this->dequeue();
        }
    }
    public function enqueue_array($key,$values){
        foreach($values as $value){
            $this->enqueue($key,$value);
        }
    }
    public function enqueue($key,$value){
        $oldBack = $this->back;
        $this->back = new Queue_element(); 
        $this->back->key_value_pair = new Key_value_pair($key,$value);
        if($this->isEmpty()){
            $this->font = $this->back; 
        }else{
            $oldBack->next = $this->back;
        }
    }    
    public function dequeue(){
        if($this->isEmpty()){
          return null; 
        }
        $removedValue = $this->font->key_value_pair;
        $this->font = $this->font->next;
        return $removedValue;
    }
}