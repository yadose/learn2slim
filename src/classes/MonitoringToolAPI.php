<?php
namespace Classes;
use \Mysqli;

class MonitoringToolAPI {
  //database credential variables
  private $database;
  private $databasehost;
  private $databaseuser;
  private $databasepassword;
  private $mysqli;
  private $allowedSession;
  private $allowedTables = array(
    "clients",
    "client",
    "devices",
    "device",
    "errors",
    "error",
    "jobs",
    "job"
  );
  /*
   * Creates new object of this class and connects to database
   * $sDbname (String) - Name of the Database
   * $sDbhost (String) - Name of the Database Host
   * $sDbuser (String) - Name of the Database User
   * $sDbpasswd (String) - Full String of the Database Users Password
   * returns NULL
   */
  public function __construct($sDbname,$sDbhost,$sDbuser,$sDbpasswd){
    $this->database = $sDbname;
    $this->databasehost = $sDbhost;
    $this->databaseuser = $sDbuser;
    $this->databasepassword = $sDbpasswd;
    $this->connect();
  }
  /*
   * Checks if tablenames are allowed depending from $this->allowedTables
   * $sCheck (String) - String which needs to be checked
   * returns (Bool)
   */
  public function isAllowed($sCheck,$aParams=array()){
    $sqlquery = "select pid,domain from devices where devices.key = '".$aParams['key']."'";
    if($result = $this->mysqli->query($sqlquery)){
        if($row = $result->fetch_row()){
          if(!password_verify($aParams['key'].$row[0].$row[1],$aParams['password'])){
            return false;
          }
        }
        else{
          return false;
        }
    }
    else{
      return false;
    }
    return in_array($sCheck,$this->allowedTables);
  }
  /*
   * Connects to database using credential parameters from constructor
   * returns NULL
   */
  public function connect(){
    //phpinfo();
    $this->mysqli = new Mysqli($this->databasehost,$this->databaseuser,$this->databasepassword,$this->database);
    if($this->mysqli->connect_errno){
      echo "Errno: " . $this->mysqli->connect_errno . "\n";
      echo "Error: " . $this->mysqli->connect_error . "\n";
    }
    else{
      //echo "successfully connected\n";

    }
  }
  /*
   * Selects all or a single record/s
   * $sTablename (String) - Name of the Table which gets the new Record
   * $sIdnumber (String) - ID of the Record which needs to be selected
   * returns $aResults (Array)
   */
  public function show($sTablename,$sIdnumber=''){
    if($sIdnumber!=''){
      $sqlquery = "select * from ".$sTablename."s where pid =".$sIdnumber;
    }
    else{
      $sqlquery = "select * from ".$sTablename;
    }

    if($result = $this->mysqli->query($sqlquery)){
        while($row = $result->fetch_assoc()){
          $aResults[] = $row;
        }
    }

    if(!isset($aResults)){
      $aResults = array(
        'detail'=>'No data found',
        'status'=> 404,
        'title'=> 'error'
        );
    }
    return $aResults;
  }
  /*
   * Inserts a new Record
   * $sTablename (String) - Name of the Table which gets the new Record
   * $aParams (Array) - Indexname is Columnname and referring Values
   * returns $aResults (Array)
   */
  public function create($sTablename,$aParams){
    //column names
    $sColnames = "";
    //column values
    $sColvalues = "";
    foreach($aParams as $sSingleParameterIndex => $sSingleParameterValue){
      if($sSingleParameterIndex != 'key' && $sSingleParameterIndex!='password'){
        $sColnames .= $sSingleParameterIndex.',';
        $sColvalues .= "'".$sSingleParameterValue."',";
      }
    }
    $sColnames = substr($sColnames,0,-1);
    $sColvalues = substr($sColvalues,0,-1);
    if($sTablename=='jobs'){
        if($aParams['end']=='0'){
          $sqlquery = "insert into ".$sTablename. "(".$sColnames.") VALUES (".$sColvalues.")";
        }
        else{
          $sqlquery = "update ".$sTablename. " SET end = '".$aParams['end']."' WHERE START='".$aParams['start']."' AND DEVICE='".$aParams['device']."' AND TYPE='".$aParams['type']."'";
        }

    }
    else{
        $sqlquery = "insert into ".$sTablename. "(".$sColnames.") VALUES (".$sColvalues.")";
    }

    if($result = $this->mysqli->query($sqlquery)){

      $aResults = array(
        'detail'=>'successfully inserted',
        'status'=> 200,
        'title'=> 'message'
        );

    }
    else{
      $aResults = array(
        'detail'=>'Not possible',
        'status'=> 403,
        'title'=> 'error'
        );
    }
    return $aResults;
  }
}
