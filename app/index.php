<?php


//use Psr\Http\Message\ResponseInterface as Response;
//use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use Selective\BasePath\BasePathMiddleware;
use Slim\Exception\HttpException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpBadRequestException;

use Selective\BasePath\BasePathDetector;
use Slim\Views\PhpRenderer;
use Slim\Routing\RouteCollectorProxy;
use Psr\Log\LoggerInterface;

require __DIR__ . '/../vendor/autoload.php';
require_once './controllers/EmpleadoController.php';
require_once './controllers/MesaController.php';
require_once './controllers/ProductoController.php';
require_once './controllers/PedidoController.php';


$app = AppFactory::create();
$app->setBasePath('/Blanco-Julian-tp-comanda-prog-3/app');
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

$customErrorHandler = function (
    Request $request,
    Throwable $exception,
    bool $displayErrorDetails,
    bool $logErrors,
    bool $logErrorDetails,
    ?LoggerInterface $logger = null
) use ($app) {
    if ($logger) {
        $logger->error($exception->getMessage());
    }
    
    $payload = ['error' => $exception->getMessage()];

    $response = $app->getResponseFactory()->createResponse();
    $response->getBody()->write(
        json_encode($payload, JSON_UNESCAPED_UNICODE)
    );
    if ($exception instanceof HttpException) {
        $response = $response->withStatus($exception->getCode());
    } elseif ($exception instanceof PDOException) {
        $response = $response->withStatus(500);
    }

    return $response;
};
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->setDefaultErrorHandler($customErrorHandler);

//$app->addErrorMiddleware(true, true, true);



$app->get('/', function ($req, $res, $args) {
    $res->getBody()->write('hola mundo');
    return $res;    
});

$app->group('/empleado', function (RouteCollectorProxy $group) {
    
    $group->get('/', \EmpleadoController::class . ':GetAll');
    $group->get('/{id}', \EmpleadoController::class . ':Get');
    $group->delete('/{id}', \EmpleadoController::class . ':Delete');
    $group->post('/', \EmpleadoController::class . ':Create');
    $group->put('/', \EmpleadoController::class . ':Update');

});

$app->group('/mesa', function (RouteCollectorProxy $group) {
    
    $group->get('/', \MesaController::class . ':GetAll');
    $group->get('/{id}', \MesaController::class . ':Get');
    $group->delete('/{id}', \MesaController::class . ':Delete');
    $group->post('/', \MesaController::class . ':Create');
    $group->put('/', \MesaController::class . ':Update');

});

$app->group('/producto', function (RouteCollectorProxy $group) {
    
    $group->get('/', \ProductoController::class . ':GetAll');
    $group->get('/{id}', \ProductoController::class . ':Get');
    $group->delete('/{id}', \ProductoController::class . ':Delete');
    $group->post('/', \ProductoController::class . ':Create');//agregar tiempo estimado
    $group->put('/', \ProductoController::class . ':Update');

});

$app->group('/pedido', function (RouteCollectorProxy $group) {//sacar campo precio unitario
    
    $group->get('/', \PedidoController::class . ':GetAll');
    $group->get('/{id}', \PedidoController::class . ':Get');
    $group->delete('/{id}', \PedidoController::class . ':Delete');
    $group->post('/', \PedidoController::class . ':Create');
    $group->put('/', \PedidoController::class . ':Update');

});

$app->run();



?>