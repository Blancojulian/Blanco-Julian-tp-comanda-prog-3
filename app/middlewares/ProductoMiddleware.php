<?php

require_once './utils/BaseRespuestaError.php';
require_once './utils/utils.php';
require_once './models/Empleado.php';
require_once './models/Puesto.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;
use Slim\Exception\HttpException;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

class ProductoMiddleware extends BaseRespuestaError
{
    /**
     * Example middleware invokable class
     *
     * @param  ServerRequest  $request PSR-7 request
     * @param  RequestHandler $handler PSR-15 request handler
     *
     * @return Response
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        return $this->ControlarParametros($request, $handler);
    }

    //$routeArguments = \Slim\Routing\RouteContext::fromRequest($request)->getRoute()->getArguments();
    //https://www.slimframework.com/docs/v4/objects/request.html#route-object
    //recuperar args
    public function ControlarParametros(Request $request, RequestHandler $handler): Response {
        $parametros = $request->getParsedBody();

        $response = null;
        
        $puesto = Puesto::GetPuestoPorNombre($parametros['puesto']);
        if (!isset($puesto)) {
            return self::RespuestaError(400, "Debe enviar un puesto valido");
            
        }

        $empleado = Empleado::GetEmpleadoPorDni($parametros['dni']);

        if (isset($empleado)) {
            return self::RespuestaError(400, "Empleado con dni $empleado->dni ya existe");
        }
        $email = strtolower($parametros['email']);
        $empleado = Empleado::GetEmpleadoPorEmail($email);
        if (isset($empleado)) {
            return self::RespuestaError(400, "El email $empleado->email ya se encuentra en uso");
        }
        //nuevo
        
        //($nombre, $precio, $stock, $idTipoProducto, $tipoProducto, $fechaAlta = null, $fechaModificacion = null, $fechaBaja = null, $id = null) {

        if (!isset($parametros['nombre']) || !isset($parametros['precio']) ||
        !isset($parametros['stock']) || !isset($parametros['tipoProducto'])) {
            throw new HttpBadRequestException($req, 'Debe enviar nombre, precio, stock y tipo de producto');
        }
        $tipoProducto = TipoProducto::GetTipoPorNombre($parametros['tipoProducto']);
        if (!isset($tipoProducto)) {
            throw new HttpBadRequestException($req, 'Tipo de producto invalido');
            
        }

        if (!is_numeric($parametros['precio']) || !EsNumeroEntero($parametros['stock'])) {
            throw new HttpBadRequestException($req, 'Precio debe ser un numero y stock numero entero');
        }
        
        $response = $handler->handle($request);

        return $response;
    }

    public function ControlarId(Request $request, RequestHandler $handler): Response {
        $parametros = $request->getParsedBody();
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $idArg = $route->getArgument('id');
        $response = null;
        $id = null;
        if (isset($parametros['id'])) {
            $id = $parametros['id'];
        } else if (isset($idArg)) {
            $id = $idArg;
        } else {
            return self::RespuestaError(400, 'Debe enviar el id del producto');
        }

        if (!is_numeric($id) || str_contains($id, '.')) { 
            return self::RespuestaError(400, 'El ID debe ser un numero');
        }

        $response = $handler->handle($request);

        return $response;
    }

}