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
require_once './controllers/LoginController.php';
require_once './controllers/AuthController.php';
require_once './middlewares/LoggerMiddleware.php';
require_once './middlewares/ImagenMiddleware.php';
require_once './middlewares/AuthMiddleware.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

date_default_timezone_set('America/Argentina/Buenos_Aires');

$app = AppFactory::create();
$app->setBasePath('/Blanco-Julian-tp-comanda-prog-3/app');
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
//$app->set('upload_directory', __DIR__ . '/uploads');
//hash m5
//https://www.php.net/manual/en/function.password-hash.php
//agregar fecha alta modificacion, baja
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


/*
$app->get('/', function ($req, $res, $args) {
    $res->getBody()->write('hola mundo');
    return $res;    
});*/

$app->get('[/]', function (Request $request, Response $response) {    
    $payload = json_encode(array("mensaje" => "Slim Framework 4 PHP"));
    
    // Pausa para probar el middleware (5 segundos)
    //sleep(5);
    
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
})->add(new LoggerMiddleware());

$app->group('/empleado', function (RouteCollectorProxy $group) {
    
    $group->get('[/]', \EmpleadoController::class . ':GetAll');
    $group->get('/{id}', \EmpleadoController::class . ':Get');
    $group->delete('/{id}', \EmpleadoController::class . ':Delete');
    $group->post('[/]', \EmpleadoController::class . ':Create');
    $group->put('[/]', \EmpleadoController::class . ':Update');

})
->add(new AuthMiddleware('socio'));

$app->group('/mesa', function (RouteCollectorProxy $group) {
    
    $group->get('[/]', \MesaController::class . ':GetAll');
    $group->get('/{id}', \MesaController::class . ':Get');
    $group->delete('/{id}', \MesaController::class . ':Delete');
    $group->post('[/]', \MesaController::class . ':Create');
    $group->put('[/]', \MesaController::class . ':Update');

})
->add(new AuthMiddleware('mozo'));

$app->group('/producto', function (RouteCollectorProxy $group) {
    
    $group->get('[/]', \ProductoController::class . ':GetAll');
    $group->get('/{id}', \ProductoController::class . ':Get');
    $group->delete('/{id}', \ProductoController::class . ':Delete');
    $group->post('[/]', \ProductoController::class . ':Create');//agregar tiempo estimado
    $group->put('[/]', \ProductoController::class . ':Update');

})
->add(new AuthMiddleware('socio'));

$app->group('/pedido', function (RouteCollectorProxy $group) {//sacar campo precio unitario
    //GetCervezasPendientes
    $group->group('/pendientes', function (RouteCollectorProxy $groupPendientes) {
        $groupPendientes->get('/bebidas[/]', \PedidoController::class . ':GetBebidasPendientes')->add(new AuthMiddleware('bartender'));
        $groupPendientes->get('/comidas[/]', \PedidoController::class . ':GetComidasPendientes')->add(new AuthMiddleware('cocinero'));
        $groupPendientes->get('/cervezas[/]', \PedidoController::class . ':GetCervezasPendientes')->add(new AuthMiddleware('cervecero'));
        
    });

    $group->group('/atender', function (RouteCollectorProxy $groupAtender) {
        $groupAtender->post('/bebidas[/]', \PedidoController::class . ':AtenderPedidoBebidas')->add(new AuthMiddleware('bartender'));
        $groupAtender->post('/comidas[/]', \PedidoController::class . ':AtenderPedidoComidas')->add(new AuthMiddleware('cocinero'));
        $groupAtender->post('/cervezas[/]', \PedidoController::class . ':AtenderPedidoCervezas')->add(new AuthMiddleware('cervecero'));
    })
    ->add(\PedidoMiddleware::class . ':ControlarAtenderPedido');

    $group->group('/terminar', function (RouteCollectorProxy $groupTerminar) {
        $groupTerminar->post('/bebidas[/]', \PedidoController::class . ':TerminarPedidoBebidas')->add(new AuthMiddleware('bartender'));
        $groupTerminar->post('/comidas[/]', \PedidoController::class . ':TerminarPedidoComidas')->add(new AuthMiddleware('cocinero'));
        $groupTerminar->post('/cervezas[/]', \PedidoController::class . ':TerminarPedidoCervezas')->add(new AuthMiddleware('cervecero'));
    })
    ->add(\PedidoMiddleware::class . ':ControlarAtenderPedido');

    $group->get('[/]', \PedidoController::class . ':GetAll')->add(new AuthMiddleware('socio'));
    $group->get('/{id}', \PedidoController::class . ':Get');//si se pone primero tapa las otras rutas

    $group->get('/criterio/{idEstado}', \PedidoController::class . ':GetAllPorCriterio')->add(new AuthMiddleware('socio'));
    $group->delete('/{id}', \PedidoController::class . ':Delete')->add(\AuthMiddleware::class . ':AutorizarMozo');
    $group->post('[/]', \PedidoController::class . ':Create')->add(\PedidoMiddleware::class . ':ControlarParametros')->add(new AuthMiddleware('mozo'));
    $group->put('[/]', \PedidoController::class . ':Update')->add(\PedidoMiddleware::class . ':ControlarParametros')->add(new AuthMiddleware('mozo'));

})->add(new AuthMiddleware());

$app->group('/login', function (RouteCollectorProxy $group) {
    
    $group->post('[/]', \AuthController::class . ':login');
    //$group->get('[/]', \LoginController::class . ':GetRol');

});

$app->run();



?>