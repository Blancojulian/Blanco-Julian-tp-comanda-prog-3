<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;
use Slim\Exception\HttpException;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

class MesaMiddleware
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

    private static function RespuestaError($codigo = 400, $error = 'Faltan parametros') {
        $response = new Response();
        $payload = json_encode(['error' => $error]);
        $response->getBody()->write($payload);
        $response = $response->withStatus($codigo);
        $response = $response->withHeader('Content-Type', 'application/json');
        return $response;
    }
    //$routeArguments = \Slim\Routing\RouteContext::fromRequest($request)->getRoute()->getArguments();
    //https://www.slimframework.com/docs/v4/objects/request.html#route-object
    //recuperar args
    public  function ControlarParametros(Request $request, RequestHandler $handler): Response {
        $parametros = $req->getParsedBody();
        $response = null;
        if (!isset($parametros['idEstado'])) {
            $response = self::RespuestaError(400, 'Debe enviar el id del estado');
        } else {
            $response = $handler->handle($request);
        }

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
            return self::RespuestaError(400, 'Debe enviar el id de la mesa');
        }

        if (!is_numeric($id) || str_contains($id, '.')) { 
            return self::RespuestaError(400, 'El ID debe ser un numero');

        }

        $response = $handler->handle($request);

        return $response;
    }

    public function ControlarMesaLibre(Request $request, RequestHandler $handler): Response {
        $parametros = $request->getParsedBody();
        
        //validar que mesa este libre
        $estadoMesaLibre = 5;
        $mesa = Mesa::GetMesaPorCodigo($parametros['codigoMesa']);
        if (!isset($mesa)) {
            return self::RespuestaError(400, 'La mesa no existe');
        }
        if ( $mesa->idEstado !== $estadoMesaLibre ) {
            return self::RespuestaError(400, 'La mesa esta ocupada');
        }
        $response = $handler->handle($request);

        return $response;
    }
}