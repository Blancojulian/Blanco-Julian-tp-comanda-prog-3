<?php

require_once './models/Pedido.php';
require_once './models/ItemPedido.php';
require_once './models/Mesa.php';
require_once './utils/utils.php';
require_once './enums/EEstadosMesa.php';
require_once './utils/BaseRespuestaError.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;
use Slim\Exception\HttpException;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

class PedidoMiddleware extends BaseRespuestaError
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
        return $this->ControlarParametrosCreate($request, $handler);
    }

    //$routeArguments = \Slim\Routing\RouteContext::fromRequest($request)->getRoute()->getArguments();
    //https://www.slimframework.com/docs/v4/objects/request.html#route-object
    //recuperar args
    //cambiar, ahora los items van a ser opcinales para update pedido
    public function ControlarParametrosCreate(Request $request, RequestHandler $handler): Response {
        $parametros = $request->getParsedBody();
        if (!isset($parametros['nombreCliente']) || !isset($parametros['codigoMesa']) ||
        !isset($parametros['items'])) {
            return self::RespuestaError(400, 'Debe enviar nombre del cliente, codigo de la mesa y los items del pedido');
        }
        
        try {
            $this->ControlarItems($parametros['items']);
        } catch (Exception $e) {
            return self::RespuestaError(400, $e->getMessage());
        }
        //validar que mesa este cerrada
        $mesa = Mesa::GetMesaPorCodigo($parametros['codigoMesa']);
        if (!isset($mesa)) {
            return self::RespuestaError(400, 'Mesa codigo '.$parametros['codigoMesa'].' no existe');
        }
        
        if ($mesa->idEstado !== EstadosMesa::Cerrada->value) {
            return self::RespuestaError(400, 'Mesa ocupada');
        }
        $response = $handler->handle($request);

        return $response;
    }

    public function ControlarParametrosUpdate(Request $request, RequestHandler $handler): Response {
        $parametros = $request->getParsedBody();
        $idEstadoPendiente = 1;
        if (!isset($parametros['nombreCliente']) || !isset($parametros['codigoMesa'])) {
            return self::RespuestaError(400, 'Debe enviar nombre del cliente y codigo de la mesa, opcinalmente los items del pedido');
        }

        $pedido = Pedido::GetPedido(intval($parametros['id']));
        if (!isset($pedido)) {
            return self::RespuestaError(404, 'Pedido no existe');            
        }
        
        if (isset($parametros['items'])) {
            try {
                if ($pedido->idEstado !== $idEstadoPendiente) {
                    throw new Exception('No se puede cambiar los items si el pedido no se encuentra pendiente');
                }
                $this->ControlarItems($parametros['items']);
            } catch (Exception $e) {
                return self::RespuestaError(400, $e->getMessage());
            }
        }
        
        //validar que mesa este cerrada
        $mesa = Mesa::GetMesaPorCodigo($parametros['codigoMesa']);
        if (!isset($mesa)) {
            return self::RespuestaError(404, 'Mesa codigo '.$parametros['codigoMesa'].' no encontrada');
        }
        
        if ($mesa->codigo !== $pedido->codigoMesa && $mesa->idEstado != EstadosMesa::Cerrada->value) {
            return self::RespuestaError(400, 'Mesa ocupada');
        }
        $response = $handler->handle($request);

        return $response;
    }
    private function ControlarItems($items) {
        if (!is_array($items) || count($items) <= 0 ) {
            throw new Exception('Items con formato incorrecto o lista vacia');
        }
        $itemsParseados = ItemPedido::ConvertirAArrayItems($items);
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
    //va a recibir los minutos estimados, el id pedido y un array con los ids de los productos que se van a atender
    //ver si hay un array de obj con el id item y los minutos estimados
    

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

    public function ControlarPuntuarPedido(Request $request, RequestHandler $handler): Response {
        $parametros = $request->getParsedBody();
        $idEstadoListoParaServir = 3;
        $listaItemsNoPuntuados = [];
        $mensajeError = null;
        if (!isset($parametros['codigoPedido']) || !isset($parametros['codigoMesa']) || !isset($parametros['resenia']) || 
        !isset($parametros['puntajeMesa']) || !isset($parametros['puntajeMozo']) || !isset($parametros['puntajeRestaurante']) || 
        !isset($parametros['puntajeItems']) || !is_array($parametros['puntajeItems']) || count($parametros['puntajeItems']) <= 0) {
            return self::RespuestaError(400, 'Debe enviar codigo pedido, codigo mesa, una reseña, y puntajes de mesa, mozo, restaurante e items');
        }

        if (!EsNumeroEntero($parametros['puntajeMesa']) || $parametros['puntajeMesa'] < 1 || $parametros['puntajeMesa'] > 10 ||
        !EsNumeroEntero($parametros['puntajeMozo']) || $parametros['puntajeMozo'] < 1 || $parametros['puntajeMozo'] > 10  ||
        !EsNumeroEntero($parametros['puntajeRestaurante']) || $parametros['puntajeRestaurante'] < 1 || $parametros['puntajeRestaurante'] > 10 ) {
            return self::RespuestaError(400, 'La puntuación debe ser un numero entero entre 1 y 10');
            
        }

        if (strlen($parametros['resenia']) > 66) {
            return self::RespuestaError(400, 'La reseña debe ser de hasta 66 caracteres');
        }

        $pedido = Pedido::GetPedidoPorCodigo($parametros['codigoPedido']);

        if (!isset($pedido) || $pedido->codigoMesa != $parametros['codigoMesa']) {
            return self::RespuestaError(404, 'Pedido no encontrado');
        }

        if ($pedido->idEstado != $idEstadoListoParaServir) {
            return self::RespuestaError(400, 'El pedido no se sirvio');
        }
        try {
            foreach ($parametros['puntajeItems'] as $itemPuntaje) {
                if (!isset($itemPuntaje['idItem']) ||  !isset($itemPuntaje['puntaje'])) {
                    throw new Exception('En los items debe enviar el id item y minutos estimados');
                }
                if (!EsNumeroEntero($itemPuntaje['puntaje']) || $itemPuntaje['puntaje'] < 1 || $itemPuntaje['puntaje'] > 10 ) {
                    throw new Exception('La puntuación debe ser un numero entero entre 1 y 10');
                }
            }
            
        } catch (\Exception $e) {
            return self::RespuestaError(400, $e->getMessage());
        }

        $index = null;
        foreach ($pedido->items as $item) {
            $index = array_find_index(fn($i) => $i['idItem'] == $item->id, $parametros['puntajeItems']);
            if ($index == -1) {
                array_push($listaItemsNoPuntuados, "Id: $item->id Producto: $item->producto");
            }
        }
        if (count($listaItemsNoPuntuados) > 0) {
            $mensajeError = 'Los items no fueron puntuados: ' . implode(", ", $listaItemsNoPuntuados);
            return self::RespuestaError(400, $mensajeError);
        }

        $response = $handler->handle($request);

        return $response;

    }
}