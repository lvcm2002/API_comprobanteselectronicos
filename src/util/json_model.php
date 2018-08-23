<?php
class Json_model{
    public static function init_values(&$model){
        if (property_exists($model,'Receptor') && property_exists($model->Receptor,'Identificacion')){
            $model->Documento->Tipo = '01';
        }else{
            $model->Documento->Tipo = '04';
        }
    }
    public static function format_values($model){
        $model->Sender->Identificacion->Tipo = str_pad($model->Sender->Identificacion->Tipo,2,'0',STR_PAD_LEFT);
        $model->Sender->Identificacion->Numero = str_pad($model->Sender->Identificacion->Numero,12,'0',STR_PAD_LEFT);
        $model->Agencia = str_pad($model->Agencia,3,'0',STR_PAD_LEFT);
        $model->Terminal = str_pad($model->Terminal,5,'0',STR_PAD_LEFT);
        $model->Documento->Tipo = str_pad($model->Documento->Tipo,2,'0',STR_PAD_LEFT);
    }
}