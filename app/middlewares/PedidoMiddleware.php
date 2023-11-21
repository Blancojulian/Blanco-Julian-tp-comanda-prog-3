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
        $parametros = $request->getParsedBody();
        if (!isset($parametros['nombreCliente']) || !isset($parametros['codigoMesa']) ||
        !isset($parametros['items']) || count($parametros['items']) <= 0) {
            return self::RespuestaError(400, 'Debe enviar nombre del cliente, codigo de la mesa y los items del pedido');
        }
        try {
            $itemsParseados = ItemPedido::ConvertirAArrayItems($parametros['items']);
        } catch (Exception $e) {
            return self::RespuestaError(400, $e->getMessage());
        }
        
        //validar que mesa este libre
        $estadoMesaLibre = 5;
        $mesa = Mesa::GetMesaPorCodigo($parametros['codigoMesa']);
        if (!isset($mesa)) {
            return self::RespuestaError(400, 'Mesa codigo '.$parametros['codigoMesa'].' no existe');
        }
        $response = $handler->handle($request);

        return $response;
    }

    public function ControlarMesaLibre(Request $request, RequestHandler $handler): Response {
        $parametros = $request->getParsedBody();
        
        //validar que mesa este libre
        $estadoMesaLibre = 5;
        $mesa = Mesa::GetMesaPorCodigo($parametros['codigoMesa']);
        if ( $mesa->idEstado !== $estadoMesaLibre ) {
            return self::RespuestaError(400, 'La mesa esta ocupada');
        }
        $response = $handler->handle($request);

        return $response;
    }

    public function ControlarAtenderPedido() {
        $parametros = $request->getParsedBody();
        if (!isset($parametros['minutosEstimado']) || !isset($parametros['id'])) {
            return self::RespuestaError(400, 'Debe enviar los minutos estimados y Id del pedido');
        }


        $response = $handler->handle($request);

        return $response;
    }
}