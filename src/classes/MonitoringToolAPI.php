<?php
namespace Classes;
use \Mysqli;
//use \PDO;
class MonitoringToolAPI {
  //DATABASE
  private $database;
  private $databasehost;
  private $databaseuser;
  private $databasepassword;
  public function __construct($dbname,$dbhost,$dbuser,$dbpasswd){
    $this->database = $dbname;
    $this->databasehost = $dbhost;
    $this->databaseuser = $dbuser;
    $this->databasepassword = $dbpasswd;
  }
  public function connect(){
    //phpinfo();
    $mysqli = new Mysqli($this->databasehost,$this->databaseuser,$this->databasepassword,$this->database);
    //$pdo = new PDO("mysql:host=" . $this->databasehost . ";dbname=" . $this->database, $this->databaseuser, $this->databasepassword);
    //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    if($mysqli->connect_errno){
      echo "Errno: " . $mysqli->connect_errno . "\n";
      echo "Error: " . $mysqli->connect_error . "\n";
    }
    $sqlquery = "select * from users";
    if($result = $mysqli->query($sqlquery)){
        while($row = $result->fetch_assoc()){
          echo $row['name'];
        }
        //die("ok");
    }
    else{
      echo $mysqli->error;
      //die('nicht ok');
    }
    //die('');
  }
  public function finishedJob(){

  }
  public function reportError(){

  }
}
