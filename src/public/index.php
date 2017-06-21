<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';
//db configs
$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$config['db']['host'] = "localhost";
$config['db']['user'] = "root";
$config['db']['pass'] = "jose8819";
$config['db']['dbname'] = "josetest";



$app = new \Slim\App(["settings" => $config]);

$container = $app->getContainer();
//add logger
$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler("../logs/app.log");
    $logger->pushHandler($file_handler);
    return $logger;
};

$container['db'] = function($c){
  $db = $c['settings']['db'];
  $api = new \Classes\MonitoringToolAPI($db['dbname'],$db['host'],$db['user'],$db['pass']);
  $api->connect();
};

$app->get('/hello/{name}', function (Request $request, Response $response) {

    $name = $request->getAttribute('name');
    $response->getBody()->write("Hello, $name");
    $this->db;
    //$this->logger->addInfo('Some info');
    return $response;
});

$app->run();
