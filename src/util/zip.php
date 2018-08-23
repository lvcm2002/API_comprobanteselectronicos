<?php
class Zipp {
    private $_zip,$_sender,$_clave;
    public static function file_name(){
        return 'example.zip';
    }
    function __construct($psender){
        $this->_sender = $psender;
        $this->_zip = new ZipArchive();
    }
    function zipFolder($pbase_dir,$pfolder){
        $options = array('add_path' => "{$pfolder}/", 'remove_all_path' => TRUE);
        $this->_zip->addGlob("{$pbase_dir}/".$this->_sender.'/'."{$pfolder}/*{$this->_clave}*", GLOB_BRACE, $options);
    }
    function clave($pclave){
        $this->_clave = $pclave;

        $this->_zip->open(Zipp::file_name(), ZipArchive::CREATE);

        $compra = File_name::$config->compra;
        $this->zipFolder($compra->base_dir,$compra->dir);
        $base_dir = File_name::$config->documento->base_dir;
        $documento = File_name::$config->documento->enum;
        $doc = $documento->{'01'};
        $this->zipFolder($base_dir,$doc->dir);
        $doc = $documento->{'02'};
        $this->zipFolder($base_dir,$doc->dir);
        $doc = $documento->{'03'};
        $this->zipFolder($base_dir,$doc->dir);
        $doc = $documento->{'04'};
        $this->zipFolder($base_dir,$doc->dir);
        $doc = $documento->{'05'};
        $this->zipFolder($base_dir,$doc->dir);
        $doc = $documento->{'06'};
        $this->zipFolder($base_dir,$doc->dir);
        $doc = $documento->{'07'};
        $this->zipFolder($base_dir,$doc->dir);
        $base_dir = File_name::$config->xml_response->base_dir;
        $documento = File_name::$config->xml_response->enum;
        $doc = $documento->{'01'};
        $this->zipFolder($base_dir,$doc->dir);
        $doc = $documento->{'02'};
        $this->zipFolder($base_dir,$doc->dir);
        $doc = $documento->{'03'};
        $this->zipFolder($base_dir,$doc->dir);
        $doc = $documento->{'04'};
        $this->zipFolder($base_dir,$doc->dir);
        $doc = $documento->{'05'};
        $this->zipFolder($base_dir,$doc->dir);
        $doc = $documento->{'06'};
        $this->zipFolder($base_dir,$doc->dir);
        $doc = $documento->{'07'};
        $this->zipFolder($base_dir,$doc->dir);
        $this->_zip->close();
    }

}