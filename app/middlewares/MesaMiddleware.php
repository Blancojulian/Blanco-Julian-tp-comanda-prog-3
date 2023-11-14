<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

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
        // Fecha antes
        $before = date('Y-m-d H:i:s');
        
        // Continua al controller
        $response = $handler->handle($request);
        $existingContent = json_decode($response->getBody());
    
        // Despues
        $response = new Response();
        $existingContent->fechaAntes = $before;
        $existingContent->fechaDespues = date('Y-m-d H:i:s');
        
        $payload = json_encode($existingContent);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
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
}