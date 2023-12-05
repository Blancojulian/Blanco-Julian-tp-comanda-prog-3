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

class EmpleadoMiddleware extends BaseRespuestaError
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
        if (EsVacioONuloOEnBlanco($parametros['nombre']) || EsVacioONuloOEnBlanco($parametros['apellido']) || EsVacioONuloOEnBlanco($parametros['dni']) ||
            EsVacioONuloOEnBlanco($parametros['email']) || !isset($parametros['puesto'])) {
            
            return self::RespuestaError(400, 'Debe ingresar nombre, apellido, numero de documento, email, contraseña y puesto');
        
        }
        if (!is_numeric($parametros['dni']) || str_contains($parametros['dni'], '.')) { 
            return self::RespuestaError(400, 'Numero de documento debe ser un numero');

        }

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
        //este lo debe tomar
        $request = $request->withAttribute('archivo3', 'mundo');
        
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
            return self::RespuestaError(400, 'Debe enviar el id del cliente');
        }

        if (!is_numeric($id) || str_contains($id, '.')) { 
            return self::RespuestaError(400, 'El ID debe ser un numero');
        }

        $response = $handler->handle($request);

        return $response;
    }

}