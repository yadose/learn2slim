<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;
//db configs
$config['db']['type'] = "mysql";
$config['db']['host'] = "localhost";
$config['db']['user'] = "root";
$config['db']['pass'] = "jose8819";
$config['db']['dbname'] = "josetest";

$sApiVersion = 'v1';

$container = new \Slim\Container;
$app = new \Slim\App(["settings" => $config]);

$container = $app->getContainer();
//add logger
$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler("../logs/app.log");
    $logger->pushHandler($file_handler);
    return $logger;
};
//db container
$container['db'] = function($c) {
    $aDb = $c['settings']['db'];
    $oApi = new \Classes\MonitoringToolAPI($aDb['type'],$aDb['dbname'],$aDb['host'],$aDb['user'],$aDb['pass']);
    return $oApi;
};

//get all
$app->get('/api/'.$sApiVersion.'/{object}', function (Request $request, Response $response) {
    $sTablename = $request->getAttribute('object');
    $oApi = $this->db;
    $iStatus = 200;

    if($oApi->isAllowed($sTablename)){
      $aData = $oApi->show($sTablename);
      if(isset($aData['status'])){
          $iStatus = $aData['status'];
      }
    }
    else{
      $aData = array(
        'detail'=> 'Authorization failed',
        'status'=> 403,
        'title'=> 'error'
      );
      $iStatus = $aData['status'];
    }
    return $response->withStatus($iStatus)
    ->withHeader('Content-Type', 'application/json')
    ->write(json_encode($aData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
});

//get single
$app->get('/api/'.$sApiVersion.'/{object}/{idnumber}', function (Request $request, Response $response) {
    $sTablename = $request->getAttribute('object');
    $sIdnumber = $request->getAttribute('idnumber');
    $oApi = $this->db;
    $iStatus = 200;

    if($oApi->isAllowed($sTablename)){
      $aData = $oApi->show($sTablename,$sIdnumber);
      if(isset($aData['status'])){
          $iStatus = $aData['status'];
      }
    }
    else{
      $aData = array(
        'detail'=> 'Authorization failed',
        'status'=> 403,
        'title'=> 'error'
      );
      $iStatus = $aData['status'];
    }
    return $response->withStatus($iStatus)
    ->withHeader('Content-Type', 'application/json')
    ->write(json_encode($aData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
});

//insert
$app->post('/api/'.$sApiVersion.'/{object}', function (Request $request, Response $response) {
    //get encrypted code
    $sTablename = $request->getAttribute('object');
    $aParameter = $request->getParsedBody();


    //decrypt code

    //get params

    //$response->getBody()->write("Hello has value, $code['hello']");
    //send them to db
    $oApi = $this->db;
    $iStatus = 200;

    if($oApi->isAllowed($sTablename,$aParameter)){
      $aData = $oApi->create($sTablename,$aParameter);
      if(isset($aData['status'])){
          $iStatus = $aData['status'];
      }
    }
    else{
      $aData = array(
        'detail'=> 'Authorization failed',
        'status'=> 403,
        'title'=> 'error'
      );
      $iStatus = $aData['status'];
    }
    return $response->withStatus($iStatus)
    ->withHeader('Content-Type', 'application/json')
    ->write(json_encode($aData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
});

//testarea
$app->post('/test/', function (Request $request, Response $response) {
    #$response->getBody()->write("Hello don't mind me.");
    $aParameter = $request->getParsedBody();
    $oApi = $this->db;
    //print_r($oApi->show('error',11));
    print_r($oApi->create('jobs',$aParameter));
});

$app->run();
