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
->add(\AuthMiddleware::class . ':AutorizarSocio')
->add(new AuthMiddleware());

$app->group('/mesa', function (RouteCollectorProxy $group) {
    
    $group->get('[/]', \MesaController::class . ':GetAll');
    $group->get('/{id}', \MesaController::class . ':Get');
    $group->delete('/{id}', \MesaController::class . ':Delete');
    $group->post('[/]', \MesaController::class . ':Create');
    $group->put('[/]', \MesaController::class . ':Update');

})
->add(\AuthMiddleware::class . ':AutorizarMozo')
->add(new AuthMiddleware());

$app->group('/producto', function (RouteCollectorProxy $group) {
    
    $group->get('[/]', \ProductoController::class . ':GetAll');
    $group->get('/{id}', \ProductoController::class . ':Get');
    $group->delete('/{id}', \ProductoController::class . ':Delete');
    $group->post('[/]', \ProductoController::class . ':Create');//agregar tiempo estimado
    $group->put('[/]', \ProductoController::class . ':Update');

})
->add(\AuthMiddleware::class . ':AutorizarSocio')
->add(new AuthMiddleware());

$app->group('/pedido', function (RouteCollectorProxy $group) {//sacar campo precio unitario
    //GetCervezasPendientes
    $group->group('/pendientes', function (RouteCollectorProxy $groupPendientes) {
        $groupPendientes->get('/bebidas[/]', \PedidoController::class . ':GetBebidasPendientes')->add(\AuthMiddleware::class . ':AutorizarBartender');
        $groupPendientes->get('/comidas[/]', \PedidoController::class . ':GetComidasPendientes')->add(\AuthMiddleware::class . ':AutorizarCocinero');
        $groupPendientes->get('/cervezas[/]', \PedidoController::class . ':GetCervezasPendientes')->add(\AuthMiddleware::class . ':AutorizarCervecero');
        
    })
    ->add(\AuthMiddleware::class . ':RechazarMozo')
    ->add(\AuthMiddleware::class . ':RechazarCliente');

    $group->group('/atender', function (RouteCollectorProxy $groupAtender) {
        $groupAtender->get('/bebidas[/]', \PedidoController::class . ':AtenderPedidoBebidas')->add(\AuthMiddleware::class . ':AutorizarBartender');
        $groupAtender->get('/comidas[/]', \PedidoController::class . ':AtenderPedidoComidas')->add(\AuthMiddleware::class . ':AutorizarCocinero');
        $groupAtender->get('/cervezas[/]', \PedidoController::class . ':AtenderPedidoCervezas')->add(\AuthMiddleware::class . ':AutorizarCervecero');
    })
    ->add(\PedidoMiddleware::class . ':ControlarAtenderPedido')
    ->add(\AuthMiddleware::class . ':RechazarMozo')
    ->add(\AuthMiddleware::class . ':RechazarCliente');

    $group->group('/terminar', function (RouteCollectorProxy $groupTerminar) {
        $groupTerminar->get('/bebidas[/]', \PedidoController::class . ':TerminarPedidoBebidas')->add(\AuthMiddleware::class . ':AutorizarBartender');
        $groupTerminar->get('/comidas[/]', \PedidoController::class . ':TerminarPedidoComidas')->add(\AuthMiddleware::class . ':AutorizarCocinero');
        $groupTerminar->get('/cervezas[/]', \PedidoController::class . ':TerminarPedidoCervezas')->add(\AuthMiddleware::class . ':AutorizarCervecero');
    })
    ->add(\PedidoMiddleware::class . ':ControlarAtenderPedido')
    ->add(\AuthMiddleware::class . ':RechazarMozo')
    ->add(\AuthMiddleware::class . ':RechazarCliente');

    $group->get('[/]', \PedidoController::class . ':GetAll')->add(\AuthMiddleware::class . ':RechazarCliente');
    $group->get('/{id}', \PedidoController::class . ':Get');//si se pone primero tapa las otras rutas

    $group->get('/criterio/{idEstado}', \PedidoController::class . ':GetAllPorCriterio')->add(\AuthMiddleware::class . ':AutorizarSocio');
    $group->delete('/{id}', \PedidoController::class . ':Delete')->add(\AuthMiddleware::class . ':AutorizarMozo');
    $group->post('[/]', \PedidoController::class . ':Create')->add(\PedidoMiddleware::class . ':ControlarParametros')->add(\AuthMiddleware::class . ':AutorizarMozo');
    $group->put('[/]', \PedidoController::class . ':Update')->add(\PedidoMiddleware::class . ':ControlarParametros')->add(\AuthMiddleware::class . ':AutorizarMozo');

})->add(new AuthMiddleware());

$app->group('/login', function (RouteCollectorProxy $group) {
    
    $group->post('[/]', \AuthController::class . ':login');
    //$group->get('[/]', \LoginController::class . ':GetRol');

});

$app->run();



?>