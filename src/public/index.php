<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';
//db configs
$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$config['db']['host'] = "82.165.160.231";
$config['db']['user'] = "joseuser";
$config['db']['pass'] = "Mld7_c28%";
$config['db']['dbname'] = "josetest";

$app = new \Slim\App(["settings" => $config]);
//$db = new \Classes\MonitoringToolAPI($config['db']['dbname'],$config['db']['host'],$config['db']['user'],$config['db']['pass']);

$container = $app->getContainer();
//add logger
$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler("../logs/app.log");
    $logger->pushHandler($file_handler);
    return $logger;
};

//add database connection
/*$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'], $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};*/
$container['db'] = function($c){
  $db = $c['settings']['db'];
  $api = new \Classes\MonitoringToolAPI($db['dbname'],$db['host'],$db['user'],$db['pass']);
  $api->connect();
};

$app->get('/hello/{name}', function (Request $request, Response $response) {

    $name = $request->getAttribute('name');
    $response->getBody()->write("Hello, $name");
    print_r($this->db);
    $this->logger->addInfo('Some info');
    return $response;
});

$app->run();
