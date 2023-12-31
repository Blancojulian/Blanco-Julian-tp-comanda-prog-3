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
require_once './controllers/ConsultaController.php';
require_once './controllers/PdfController.php';
require_once './middlewares/LoggerMiddleware.php';
require_once './middlewares/ImagenMiddleware.php';
require_once './middlewares/EmpleadoMiddleware.php';
require_once './middlewares/PedidoMiddleware.php';
require_once './middlewares/EstadoItemMiddleware.php';
require_once './middlewares/MesaMiddleware.php';
require_once './middlewares/ProductoMiddleware.php';
require_once './middlewares/AuthMiddleware.php';
require_once './enums/EEstadosPedido.php';

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
    $response = $response->withHeader('Content-Type', 'application/json');
    if ($exception instanceof HttpException) {
        $response = $response->withStatus($exception->getCode());
    } elseif ($exception instanceof PDOException) {
        $response = $response->withStatus(500);
    }

    return $response;
};
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->setDefaultErrorHandler($customErrorHandler);

$app->get('[/]', function (Request $request, Response $response) {    
    $payload = json_encode(["mensaje" => "Hola mundo"]);
    $datos = $request->getAttribute('datos');
    var_dump($datos);
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->group('/empleado', function (RouteCollectorProxy $group) {
    
    $group->delete('/suspencion/{id}', \EmpleadoController::class . ':SuspencionEmpleado');
    $group->get('[/]', \EmpleadoController::class . ':GetAll');
    $group->get('/{id}', \EmpleadoController::class . ':Get')
    ->add(\EmpleadoMiddleware::class . ':ControlarId');
    $group->delete('/{id}', \EmpleadoController::class . ':Delete')
    ->add(\EmpleadoMiddleware::class . ':ControlarId');
    $group->post('[/]', \EmpleadoController::class . ':Create')
    ->add(new EmpleadoMiddleware());
    $group->put('[/]', \EmpleadoController::class . ':Update')
    ->add(new EmpleadoMiddleware())
    ->add(\EmpleadoMiddleware::class . ':ControlarId');

})
->add(new AuthMiddleware('socio'));

$app->group('/mesa', function (RouteCollectorProxy $group) {
    
    $group->get('/factura-mas-alta[/]', \MesaController::class . ':GetMesasSegunFacturaMasAlta')
    ->add(new AuthMiddleware('socio'));
    $group->get('/facturado-por-fechas[/]', \MesaController::class . ':GetMesaTotalFacturadoPorFechas')
    ->add(new AuthMiddleware('socio'));
    $group->get('/mas-usada[/]', \MesaController::class . ':GetMesaMasUsada')
    ->add(new AuthMiddleware('socio'));
    $group->post('/cobrar/{id}', \MesaController::class . ':CobrarMesa')
    ->add(new LoggerMiddleware('Cobrar mesa'))
    ->add(new AuthMiddleware('mozo'));
    $group->post('/cerrar/{id}', \MesaController::class . ':CerrarMesa')
    ->add(new LoggerMiddleware('Cerrar mesa'))
    ->add(new AuthMiddleware('socio'));
    $group->get('[/]', \MesaController::class . ':GetAll')
    ->add(new AuthMiddleware('socio'));
    $group->get('/{id}', \MesaController::class . ':Get')
    ->add(\MesaMiddleware::class . ':ControlarId')
    ->add(new AuthMiddleware('socio'));
    $group->delete('/{id}', \MesaController::class . ':Delete')
    ->add(\MesaMiddleware::class . ':ControlarId')
    ->add(new AuthMiddleware('socio'));
    $group->post('[/]', \MesaController::class . ':Create')
    ->add(new AuthMiddleware('socio'));
    $group->put('[/]', \MesaController::class . ':Update')
    ->add(new MesaMiddleware())
    ->add(\MesaMiddleware::class . ':ControlarId')
    ->add(new AuthMiddleware('socio'));
    

})
->add(new AuthMiddleware('socio', 'mozo'));

$app->group('/producto', function (RouteCollectorProxy $group) {
    
    $group->get('/descargar[/]', \ProductoController::class . ':DescargarCsv');
    $group->post('/subir[/]', \ProductoController::class . ':SubirCsv');
    $group->get('/productos-segun-ventas[/]', \ProductoController::class . ':GetProductosSegunMasVentas');
    $group->get('[/]', \ProductoController::class . ':GetAll');
    $group->get('/{id}', \ProductoController::class . ':Get')
    ->add(\ProductoMiddleware::class . ':ControlarId');
    $group->delete('/{id}', \ProductoController::class . ':Delete')
    ->add(\ProductoMiddleware::class . ':ControlarId');
    $group->post('[/]', \ProductoController::class . ':Create')
    ->add(new ProductoMiddleware());
    $group->put('[/]', \ProductoController::class . ':Update')
    ->add(new ProductoMiddleware())
    ->add(\ProductoMiddleware::class . ':ControlarId');

})
->add(new AuthMiddleware('socio'));

$app->group('/pedido', function (RouteCollectorProxy $group) {
    
    $group->post('/servir/{id}', \PedidoController::class . ':ServirPedido')
    ->add(new LoggerMiddleware('Servir mesa'))
    ->add(new AuthMiddleware('mozo'));
    $group->get('/consultar-pedido[/]', \PedidoController::class . ':ConsultarPedido');
    $group->post('/puntuar[/]', \PedidoController::class . ':PuntuarPedido')
    ->add(\PedidoMiddleware::class . ':ControlarPuntuarPedido');
    $group->get('/pedidos-por-entrega[/]', \PedidoController::class . ':GetPedidosPorEntrega')
    ->add(new AuthMiddleware('socio'));
    $group->get('/pedidos-listos[/]', \PedidoController::class . ':GetPedidosListos')
    ->add(new AuthMiddleware('mozo'));

    $group->get('/productos-encargados[/]', \PedidoController::class . ':GetProductosEncargados')
    ->add(new AuthMiddleware('bartender','cocinero','cervecero'));

    $group->group('/pendientes', function (RouteCollectorProxy $groupPendientes) {
        $groupPendientes->get('/bebidas[/]', \PedidoController::class . ':GetBebidasPendientes')->add(new AuthMiddleware('bartender'));
        $groupPendientes->get('/comidas[/]', \PedidoController::class . ':GetComidasPendientes')->add(new AuthMiddleware('cocinero'));
        $groupPendientes->get('/cervezas[/]', \PedidoController::class . ':GetCervezasPendientes')->add(new AuthMiddleware('cervecero'));
    });

    $group->post('/atender[/]', \PedidoController::class . ':AtenderItems')
    ->add(new EstadoItemMiddleware(EstadosPedido::EnPreparacion->value))
    ->add(new LoggerMiddleware('Atender productos'))
    ->add(new AuthMiddleware('bartender','cocinero','cervecero'));
    $group->post('/terminar[/]', \PedidoController::class . ':TerminarItems')
    ->add(new EstadoItemMiddleware(EstadosPedido::ListoParaServir->value))
    ->add(new LoggerMiddleware('Terminar productos'))
    ->add(new AuthMiddleware('bartender','cocinero','cervecero'));
    
    $group->post('/imagen[/]', \PedidoController::class . ':AgregarImagen')
    ->add(\PedidoMiddleware::class . ':ControlarId')
    ->add(new ImagenMiddleware())
    ->add(new LoggerMiddleware('Agregar foto al pedido'))
    ->add(new AuthMiddleware('mozo'));

    $group->get('[/]', \PedidoController::class . ':GetAll')
    ->add(new AuthMiddleware('socio'));
    $group->get('/{id}', \PedidoController::class . ':Get')
    ->add(\PedidoMiddleware::class . ':ControlarId')
    ->add(new AuthMiddleware('socio', 'mozo'));
    $group->get('/criterio/{idEstado}', \PedidoController::class . ':GetAllPorCriterio')
    ->add(new AuthMiddleware('socio', 'mozo'));
    $group->delete('/{id}', \PedidoController::class . ':Delete')
    ->add(\PedidoMiddleware::class . ':ControlarId')
    ->add(new LoggerMiddleware('Cancelar pedido'))
    ->add(new AuthMiddleware('mozo'));
    $group->post('[/]', \PedidoController::class . ':Create')
    ->add(new PedidoMiddleware())
    ->add(new LoggerMiddleware('Creacion pedido'))
    ->add(new AuthMiddleware('mozo'));
    $group->put('[/]', \PedidoController::class . ':Update')
    ->add(\PedidoMiddleware::class . ':ControlarParametrosUpdate')
    ->add(\PedidoMiddleware::class . ':ControlarId')
    ->add(new LoggerMiddleware('Modificacion pedido'))
    ->add(new AuthMiddleware('mozo'));

});//->add(new AuthMiddleware());

$app->group('/consulta', function (RouteCollectorProxy $group) {
    $group->get('/mejores-comentarios[/]', \ConsultaController::class . ':GetMejoresComentarios');
    $group->get('/operaciones-por-sector[/]', \ConsultaController::class . ':GetOperacionesPorSector');
    $group->get('/operaciones-por-empleado[/]', \ConsultaController::class . ':GetOperacionesPorEmpleado');
    $group->get('/logs-por-empleado/{id}', \ConsultaController::class . ':GetLogsPorEmpleado');
})->add(new AuthMiddleware('socio'));

$app->get('/descargar-logo[/]', new PdfController())
->add(new AuthMiddleware('socio'));

$app->group('/login', function (RouteCollectorProxy $group) {
    
    $group->post('[/]', new AuthController());
    //$group->get('[/]', \LoginController::class . ':GetRol');

})->add(\LoggerMiddleware::class . ':LogIngreso');

$app->run();



?>