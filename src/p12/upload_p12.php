<?php
Api::_include_once('./util/file_name.php');
$compania = new stdClass();
$compania = $_REQUEST['config'];
$identificacion = $compania['Emisor']['Identificacion'];
$tipo = str_pad($identificacion['Tipo'],2,'0',STR_PAD_LEFT);
$numero = str_pad($identificacion['Numero'],12,'0',STR_PAD_LEFT);

if (!empty($tipo) && isset($numero)){
    $sender_id = $tipo.'-'.$numero;
    file_put_contents('uploads/'.$sender_id.'.json',json_encode($compania));
    File_name::mk_dirs($sender_id);
}

function rename_old_file($pfile_name){
    
    if(file_exists($pfile_name)){
        $file_name = $pfile_name;
        $increment = 0;
        list($name, $ext) = explode('.', $file_name);
        while(file_exists($file_name)) {
            $increment++;
            $file_name = $name.'-'. $increment . '.' . $ext;
        }
        rename($pfile_name,$file_name);
    }
}
$target_dir = "emisor/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);

$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
if ($_FILES["fileToUpload"]["size"] > 500000) {
    echo "Sorry, your file is too large.";
    $uploadOk = 0;
}
// Allow certain file formats
if($imageFileType != "p12") {
    echo "Sorry, only p12 files are allowed.";
    $uploadOk = 0;
}
// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
// if everything is ok, try to upload file
} else {
    rename_old_file($target_file);
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}
?>