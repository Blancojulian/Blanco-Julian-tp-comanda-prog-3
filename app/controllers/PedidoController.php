<?php

require_once './models/Pedido.php';
require_once './models/EstadoPedido.php';
require_once './models/Mesa.php';
require_once './utils/utils.php';
require_once './interfaces/IController.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpException;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

class PedidoController implements IController {


    public function Get(Request $req, Response $res, array $args = []) {
        if (!isset($args['id'])) {
            throw new HttpBadRequestException($req);   
        }
        $id = $args['id'];
        if (!is_numeric($id)) {
            throw new HttpBadRequestException($req);   
        }
        $pedido = Pedido::GetPedido($id);
        if (!isset($pedido)) {
            throw new HttpNotFoundException($req, 'Pedido no existe');   
        }
        $res->getBody()->write(json_encode($pedido));
        return $res; 
    }

    public function GetAll(Request $req, Response $res, array $args = []) {
        $pedidos = Pedido::GetPedidos();
        $res->getBody()->write(json_encode($pedidos));
        return $res; 
    }

    public function GetAllPorCriterio(Request $req, Response $res, array $args = []) {
        if (!isset($args['idEstado'])) {
            throw new HttpBadRequestException($req);
            
        }
        $estado = EstadoPedido::GetEstadoPorId(intval($args['idEstado']));
        if (!isset($estado)) {
            throw new HttpBadRequestException($req);
            
        }
        $pedidos = Pedido::GetPedidosPorIdEstado($estado->id);
        $res->getBody()->write(json_encode($pedidos));
        return $res;
    }
    //ver
    //el estado inicial va ser pendiente
    public function Create(Request $req, Response $res, array $args = []) {//ver si no habria que pedir el estado, si es una mesa nueva no deberia estar cerrada
        $parametros = $req->getParsedBody();
        $codigo = generarCodigo(5);
        //var_dump($parametros);
        if (!isset($parametros['nombreCliente']) || !isset($parametros['codigoMesa']) ||
        !isset($parametros['items'])) {
            throw new HttpBadRequestException($req, 'Debe enviar nombre del cliente, codigo mesa y los items');
        }
        //parsear el array a un array de ItemPedido
        $itemsParseados = ItemPedido::ConvertirAArrayItems($parametros['items']);

        //validar estado del pedido
        $estadoPendiente = 1;// estado inicial pendiente
        $estado = EstadoPedido::GetEstadoPorId($estadoPendiente);
        if (!isset($estado)) {
            throw new HttpBadRequestException($req); 
        }
//echo 'hola';
        //validar que mesa este libre
        $estadoMesaLibre = 5;
        $estadoMesaClienteEsperando = 1;
        $mesa = Mesa::GetMesaPorCodigo($parametros['codigoMesa']);
        if (!isset($mesa)) {
            throw new HttpNotFoundException($req, 'Mesa codigo '.$parametros['codigoMesa'].' no existe'); 
        }
        if ( $mesa->idEstado !== $estadoMesaLibre ) {
            throw new HttpBadRequestException($req, 'La mesa esta ocupada'); 
        }
        $pedido = new Pedido($codigo, $parametros['nombreCliente'],
        $parametros['codigoMesa'], $estado->id, $estado->estado);

        $pedido->items = $itemsParseados;
        $pedido->CalcularTotal();
        $id = $pedido->CrearPedido();

        ItemPedido::CrearItems($id, $pedido->items);//hacer if para comprobar si falla
        
        //cambiar mesa a cliente esperando
        $mesa->idEstado = $estadoMesaClienteEsperando;
        $mesa->ModificarMesa();
        $res->getBody()->write(json_encode(['mensaje' => "Pedido creado", 'id' => $id, 'codigo' => $codigo]));

        return $res;
    }

    public function Delete(Request $req, Response $res, array $args = []) {
        if (!isset($args['id'])) {
            throw new HttpBadRequestException($req);   
        }
        $id = $args['id'];
        if (!is_numeric($id)) {
            throw new HttpBadRequestException($req);   
        }
        $pedido = Pedido::GetPedido($id);
        if (!isset($pedido)) {
            throw new HttpNotFoundException($req, 'Pedido no existe');   
        }
        $pedido->cancelado = true;
        $pedido->ModificarPedido();
        $res->getBody()->write(json_encode(['mensaje' => "Pedido cancelado"]));
        return $res; 
    }
    //ver
    public function Update(Request $req, Response $res, array $args = []) {
        $parametros = $req->getParsedBody();
        //$codigo = generarCodigo(5);
        //var_dump($parametros);
        if ( !!isset($parametros['id']) || !isset($parametros['nombreCliente']) || !isset($parametros['codigoMesa']) ||
        !!isset($parametros['idEstado']) || !isset($parametros['items'])) {
            throw new HttpBadRequestException($req, 'Debe enviar id, nombre del cliente, codigo mesa y los items');
        }
        //parsear el array a un array de ItemPedido
        $itemsParseados = ItemPedido::ConvertirAArrayItems($parametros['items']);

        //validar estado del pedido
        $estado = EstadoPedido::GetEstadoPorId($parametros['idEstado']);
        if (!isset($estado)) {
            throw new HttpBadRequestException($req, 'Estado pedido invalido'); 
        }

        //validar que mesa este libre
        $estadoMesaLibre = 5;
        $mesaClienteEsperando = 1;
        $mesa = Mesa::GetMesaPorCodigo($parametros['codigoMesa']);
        if (!isset($mesa)) {
            throw new HttpBadRequestException($req, 'Mesa codigo '.$parametros['codigoMesa'].' no existe'); 
        }
        /*
        if ( $mesa->idEstado !== $estadoMesaLibre ) {
            throw new HttpBadRequestException($req, 'La mesa esta ocupada'); 
        }*/

        $pedido = Pedido::GetPedido(intval($parametros['id']));
        if (isset($pedido)) {
            throw new Exception("Error Processing Request", 1);
            
        }
        $pedido->nombreCliente = $parametros['nombreCliente'];
        $pedido->codigoMesa = $parametros['codigoMesa'];
        $pedido->idEstado = $estado->id;
        $pedido->estado = $estado->estado;
        $pedido->items = $itemsParseados;
        $pedido->CalcularTotal();

        $pedido->ModificarPedido();
        ItemPedido::EliminarItems($pedido->id);
        ItemPedido::CrearItems($pedido->id, $pedido->items);

        $pedido = new Pedido($codigo, $parametros['nombreCliente'],
        $parametros['codigoMesa'], $estado->id, $estado->estado);

        $pedido->items = $itemsParseados;
        $pedido->CalcularTotal();
        $id = $pedido->CrearPedido();
        //cambiar mesa a cliente esperando
        $mesa->idEstado = $mesaClienteEsperando;
        $mesa->ModificarMesa();
        $res->getBody()->write(json_encode(['mensaje' => "Pedido creado", 'id' => $id, 'codigo' => $codigo]));

        return $res;
    }
}

?>