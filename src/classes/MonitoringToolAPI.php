<?php
namespace Classes;
use \PDO;

class MonitoringToolAPI {
  //database credential variables
  private $databasetype;
  private $databasename;
  private $databasehost;
  private $databaseuser;
  private $databasepassword;
  private $allowedSession;
  private $dbObject;
  private $dbStatement;
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
  public function __construct($sDbtype,$sDbname,$sDbhost,$sDbuser,$sDbpasswd){
    $this->databasetype = $sDbtype;
    $this->databasename = $sDbname;
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
    $sqlquery = "select pid,domain from devices where devices.key = ?";
    $dbStatement = $this->dbObject->prepare($sqlquery);
    if($dbStatement->execute(array($aParams['key']))){
        if($row = $dbStatement->fetch()){
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
    try{
        $this->dbObject = new PDO($this->databasetype.':host='.$this->databasehost.';dbname='.$this->databasename,$this->databaseuser,$this->databasepassword);
    }
    catch (PDOExeption $e){
      echo "Connection failed: ".$e->getMessage();
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
      $sqlquery = "select * from ".$sTablename."s where pid = ? ";
    }
    else{
      $sqlquery = "select * from ".$sTablename;
    }
    $dbStatement = $this->dbObject->prepare($sqlquery);

    if($dbStatement->execute(array($sIdnumber))){
        while($row = $dbStatement->fetch()){
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
    $sColbinds = "";
    //execute Content
    $mExecuteValues = [];
    //unset unneccesairy values
    unset($aParams['key']);
    unset($aParams['password']);
    foreach($aParams as $sSingleParameterIndex => $sSingleParameterValue){
      $sColnames .= $sSingleParameterIndex.',';
      $sColbinds .= '?,';
    }
    $sColnames = substr($sColnames,0,-1);
    $sColbinds = substr($sColbinds,0,-1);
    if($sTablename=='jobs'){
        if($aParams['end']=='0'){
          $sqlquery = "insert into ".$sTablename. "(".$sColnames.") VALUES (".$sColbinds.")";
          $mExecuteValues = array_values($aParams);
        }
        else{
          $sqlquery = "update ".$sTablename. " SET end = ? WHERE TYPE = ? AND START = ? AND DEVICE = ?";
          $mExecuteValues = array('end' => $aParams['end']);
          unset($aParams['end']);
          $mExecuteValues = array_values(array_merge($mExecuteValues,$aParams));
          #print_r($mExecuteValues);
          #die();
        }

    }
    else{
        $sqlquery = "insert into ".$sTablename. "(".$sColnames.") VALUES (".$sColbinds.")";
        $mExecuteValues = array_values($aParams);
    }

  $dbStatement = $this->dbObject->prepare($sqlquery);
  if($dbStatement->execute($mExecuteValues)){

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
