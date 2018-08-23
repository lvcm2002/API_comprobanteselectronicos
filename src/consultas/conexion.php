<?php
class Connection extends mysqli {

  public function __construct() {
        $db_config = json_decode(Api::_file_get_config('db_config'));
        parent::__construct($db_config->host, $db_config->user,$db_config->pass,$db_config->database);
        if ($this->connect_errno){
            die('Error en la conexión a la base de datos');
            echo '<br>error<br>'; 
        }else{
        }

        $this->set_charset("utf8");
  }
  public function num_rows($query) {
    return mysqli_num_rows($query);
  }

  public function free($query) {
    return mysqli_free_result($query);
  }

  public function fetch_array($query) {
    return mysqli_fetch_array($query);
  }

  public function fetch_all($qry){
    $results = $this->query($qry);
    return $results->fetch_all(MYSQLI_ASSOC);

  }
  public function fetch_json($qry) {
    return json_encode($this->fetch_all($qry));
  }
}
