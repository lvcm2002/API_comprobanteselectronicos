<?php
Api::_include_once('./util/frontend_lib.php');
$func = $_GET['r'];


switch ($func){
    case "enums":
        echo json_encode(Front_end::getEnums());
        break;
    case "struct":
        echo Api::_file_get_resouce("json/FacturaElectronica.json");
        break;
    case "example":
        echo Api::_file_get_resouce("example/FacturaElectronica.json");
        break;
    case "upload_sender":
        Api::_include_once('./p12/upload_p12.php');
        break;
    case "email":
        Api::_include_once('./util/sender.php');
        $clave = '50618081800011405023900100001010000000054100000054';
        $xml_factura = File_name::documento('01-000114050239',$clave,'01');
        $data = xml_to_array($xml_factura,FALSE);
        $sender = new Sender($data);
        $sender->email_factura($clave);
        break;
    case "download":
        Api::_include_once('./util/zip.php');
        Api::_include_once('./util/download.php');
        Api::_include_once('./util/model.php');
        $json = utf8_encode(nvl($_GET['data'],'{}'));

        // get source
        $data = Front_end::withJson($json);
        $sender_id = Model::get_sender_id($data);
        // get clave
        $clave = Model::get_clave($data);

        // redirect to $sender folder
        $zip = new Zipp($sender_id);
        // read $clave from $sender
        $zip->clave($clave);
        // download zip file
        Download::zip_file();
        break;
    case "save":
        //file_put_contents("example1.json",$json);
        echo "{'status':'ok'}";
        break;
    case "calcula":
        Api::_include_once('./util/util.php');
        Api::_include_once('./util/model.php');
        $json = utf8_encode(nvl($_GET['data'],'{}'));
        $data = Front_end::withJson($json);
        $data = Model::calcula($data);
        $data = Front_end::formatea($data,Front_end::getEnums());
        echo json_encode($data);
        break;
    case "genXML":
        Api::_include_once('./util/util.php');
        Api::_include_once('./util/model.php');
        $json = utf8_encode(nvl($_GET['data'],'{}'));
        $data = Front_end::withJson($json);
        $data = Model::calcula($data);
        $data = Front_end::formatea($data,Front_end::getEnums());
        $data = XMLSerializer::generateValidXmlFromObj($data,'FacturaElectronica');
        echo $data;
        break;
    case "timbraPendingQueue":
        Api::_include_once('./util/timbra.php');
        Api::_include_once('./util/bk_consulta.php');
        Api::_include_once('./util/sender.php');
        Api::_include_once('./util/json_model.php');
        Api::_include_once('./util/xml_model.php');
        Api::_include_once('./consultas/db_model.php');
        Api::_include_once('./util/queue.php');
        $db_pending_queue = new Db_pending_queue();
        $db_pending_queue->run(function($psender_id,$pdata){
            if (!Bk_consulta::is_connected()){
                return false;
            }
            $sender = new Sender($pdata);

            Timbra::consulta_documentos_emisor($sender,$pdata);
        });
        break;
    case "timbraXML3":
        Api::_include_once('./util/timbra.php');
        Api::_include_once('./util/bk_consulta.php');
        Api::_include_once('./util/sender.php');
        Api::_include_once('./util/json_model.php');
        Api::_include_once('./util/xml_model.php');
        Api::_include_once('./consultas/db_model.php');
        Api::_include_once('./util/queue.php');
        $json_pool_queue = new Xml_pool3_queue();
        $json_pool_queue->run(function($psender_id,$pxml){
            if (!Bk_consulta::is_connected()){
                return false;
            }
            $data = Xml_model::xml_get_model($pxml);
            $sender = new Sender($data);

            Timbra::documento($sender,$data,$pxml);

            Api::_file_put_into_xml_document($psender_id,Model::get_clave($data),Model::get_documento_tipo($data),$pxml);
            Api::_unlink_contents_in_xml_pool3($psender_id,Model::get_clave($data));
        });
        break;
    case "timbraXML2":
        Api::_include_once('./util/timbra.php');
        Api::_include_once('./util/bk_consulta.php');
        Api::_include_once('./util/sender.php');
        Api::_include_once('./util/json_model.php');
        Api::_include_once('./util/xml_model.php');
        Api::_include_once('./consultas/db_model.php');
        Api::_include_once('./util/queue.php');
        $json_pool_queue = new Xml_pool2_queue();
        $json_pool_queue->run(function($psender_id,$pxml){
            if (!Bk_consulta::is_connected()){
                return false;
            }
            $data = Xml_model::xml_get_model($pxml);
            $sender = new Sender($data);

            Timbra::documento($sender,$data,$pxml);

            Api::_file_put_into_xml_document($psender_id,Model::get_clave($data),Model::get_documento_tipo($data),$pxml);
            Api::_unlink_contents_in_xml_pool2($psender_id,Model::get_clave($data));
        });
        break;
    case "saveXML":
        Api::_include_once('./util/timbra.php');
        Api::_include_once('./util/sender.php');
        Api::_include_once('./util/json_model.php');
        Api::_include_once('./util/xml_model.php');
        Api::_include_once('./consultas/db_model.php');
        Api::_include_once('./util/queue.php');
        $json_pool_queue = new Json_pool_queue();
        $json_pool_queue->run(function($psender_id,$pjson){

            // read json
            $json = utf8_encode(nvl($pjson,'{}'));
            $data = Front_end::withJson($json);

            //xml wrap data
            $sender = new Sender($data);
            $xml = Xml_wrap::documento($sender,$data);

            //insert in db
            if (in_array($data->Documento->Tipo, array('01','02','03','04'))){
                Xml_model::set_missing_values($data);
                $db_model = new Db_model($data);
                $db_model->insert_resumen_documento($data);
            }

            if ($sender->situacion == 3){
                //queue calls to pool3
                Api::_file_put_contents_into_xml_pool3($psender_id,Model::get_clave($data),$xml);
                Api::_unlink_contents_in_json_pool($psender_id,Model::get_numero_consecutivo($data));
                return;
            }
            if (!Bk_consulta::is_connected()){
                //queue calls to pool2
                Api::_file_put_contents_into_xml_pool2($psender_id,Model::get_clave($data),$xml);
                Api::_unlink_contents_in_json_pool($psender_id,Model::get_numero_consecutivo($data));
                return;
            }
            Timbra::documento($sender,$data,$xml);

            Api::_file_put_into_xml_document($psender_id,Model::get_clave($data),Model::get_documento_tipo($data),$xml);
            Api::_unlink_contents_in_json_pool($psender_id,Model::get_numero_consecutivo($data));
        });
        break;
    case "saveJSON":
        Api::_include_once('./util/sender.php');
        Api::_include_once('./util/json_model.php');
        Api::_include_once('./consultas/db_model.php');
        $json = utf8_encode(nvl($_GET['data'],'{}'));
        
        $data = Front_end::withJson($json);

        $data = Front_end::formatea($data,Front_end::getEnums());
        $data = XMLSerializer::generateValidXmlFromObj($data,'FacturaElectronica');
        $status = Front_end::isValid($data,'xsd/FacturaElectronica0.xsd');
        $loopback_json = $json;
        if ($status == 'ok'){
            $data0 = Front_end::withJson($json);
            
            //set document type
            Json_model::init_values($data0);
            
            //set document sender
            Model::set_sender($data0);
            $sender = new Sender($data0);

            //set document number
            $db_model = new Db_model($data0);
            Model::set_documento_numero($data0,$db_model->inc_consecutivo());

            //set consecutivo
            Model::set_numero_consecutivo($data0);
            
            
            $numero_consecutivo = Model::get_numero_consecutivo($data0);
            
            $json0 = json_encode($data0);

            Api::_file_put_contents_into_json_pool($sender->id,$numero_consecutivo,$json0);
            $loopback_json = $json0;
        }
        echo "{'status':'{$status}','json':'{$loopback_json}'}";
        break;
    case "validate":
        $json = utf8_encode(nvl($_GET['data'],'{}'));
        $data = Front_end::withJson($json);
//        $data = Front_end::calcula($data);
//        $data = Front_end::formatea($data,Front_end::getEnums());

        $data = XMLSerializer::generateValidXmlFromObj($data,'FacturaElectronica');
        //file_put_contents("example1.xml",$data);
        $status = Front_end::isValid($data,File_name::resource('xsd/FacturaElectronica0.xsd'));
        echo "{'status':'{$status}'}";
        break;
    case "generaXML":
        $json = utf8_encode(nvl($_GET['data'],'{}'));
        $data = Front_end::withJson($json);
        $data = Front_end::calcula($data);
        $data->Clave = "foobar";
        $data = Front_end::formatea($data,Front_end::getEnums());
        echo Front_end::generaXML($data);
        break;
    case "generaToken":
        
        
    return;
        $json = utf8_encode(nvl($_GET['data'],'{}'));
        $data = XMLSerializer::generateValidXmlFromObj($data,'FacturaElectronica');
        //file_put_contents("example1.xml",$data);
        //$status = Front_end::isValid($data,'xsd/FacturaElectronica0.xsd');
    default:
        echo "{}";
        break;
}