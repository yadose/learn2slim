<?php
namespace Classes;
use \PDO;

class MonitoringToolAPI {
  //database credential variables
  /*
   * string
   * Type of the Database (Mysql, Oracle, MariaDB)
   */
  private $databasetype;
  /*
   * string
   * Name of the Database
   */
  private $databasename;
  /*
   * string
   * Host of the Database
   */
  private $databasehost;
  /*
   * string
   * User of the Database
   */
  private $databaseuser;
  /*
   * string
   * Password of the Database
   */
  private $databasepassword;

  //intern
  /*
   * object
   * Object of the class PDO for intern use
   */
  private $dbObject;
  /*
   * object
   * Object of the statement which is returned by PDO->prepare()
   */
  private $dbStatement;
  /*
   * array(string)
   * Array of Strings containing allowed Tables to select from or insert/update to
   */
  private $allowedTables = array(
    "client",
    "device",
    "error",
    "job"
  );
  /*
   * Creates new object of this class and connects to database
   * $sDbtype (String) - Database Type (Mysql, Oracle, MariaDB)
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
   * and the key, verifies the password
   * $sCheck (String) - String which needs to be checked
   * returns (Bool)
   */
  public function isAllowed($sCheck,$aParams=array()){
    //Ausnahme Locale Angular Programm
    if(isset($aParams['key']) || (!isset($aParams['key']) && $_SERVER['REMOTE_ADDR'] != '127.0.0.1') ){/*192.168.4.21,localhost*/
      $sqlquery = "select pid,domain from device where device.key = ?";
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
    }
    return in_array($sCheck,$this->allowedTables);
  }
  /*
   * Connects to database using credential parameters from constructor
   * returns NULL
   */
  public function connect(){
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
      $sqlquery = "select * from ".$sTablename." where pid = ? ";
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
    if($sTablename=='job'){
        if($aParams['end']=='0'){
          $sqlquery = "insert into ".$sTablename. "(".$sColnames.") VALUES (".$sColbinds.")";
          $mExecuteValues = array_values($aParams);
        }
        else{
          $sqlquery = "update ".$sTablename. " SET end = ? WHERE type = ? AND start = ? AND deviceId = ?";
          //put array $mExecuteValues into the right order for later execution
          $mExecuteValues = array('end' => $aParams['end']);
          unset($aParams['end']);
          $mExecuteValues = array_values(array_merge($mExecuteValues,$aParams));
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
        'title'=> 'error',
        'query'=>$mExecuteValues.' '.$sqlquery
        );
    }
    return $aResults;
  }
}
