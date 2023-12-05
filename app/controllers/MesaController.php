<?php

require_once './models/Mesa.php';
require_once './models/EstadoMesa.php';
require_once './models/Pedido.php';
require_once './utils/utils.php';
require_once './interfaces/IController.php';
require_once './enums/EEstadosMesa.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpException;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

class MesaController implements IController {


    public function Get(Request $req, Response $res, array $args = []) {
        if (!isset($args['id'])) {
            throw new HttpBadRequestException($req, 'Debe enviar el Id');   
        }
        $id = $args['id'];
        if (!is_numeric($id)) {
            throw new HttpBadRequestException($req, 'El Id debe ser un numero');   
        }
        $mesa = Mesa::GetMesa($id);
        if (!isset($mesa)) {
            throw new HttpNotFoundException($req, 'Mesa no existe');   
        }
        $res->getBody()->write(json_encode($mesa));
        return $res; 
    }

    public function GetAll(Request $req, Response $res, array $args = []) {
        $mesas = Mesa::GetMesas();
        $res->getBody()->write(json_encode($mesas));
        return $res; 
    }

    public function GetAllPorCriterio(Request $req, Response $res, array $args = []) {
        if (!isset($args['idEstado'])) {
            throw new HttpBadRequestException($req, 'Debe enviar el id del estado');
            
        }
        if (!is_numeric($args['idEstado'])) {
            throw new HttpBadRequestException($req, 'El Id estado debe ser un numero');   
        }
        $estado = EstadoMesa::GetEstadoPorId(intval($args['idEstado']));
        if (!isset($estado)) {
            throw new HttpBadRequestException($req, 'Debe enviar un estado de mesa valido');
            
        }
        $mesas = Mesa::GetMesasPorEstado($estado->id);
        $res->getBody()->write(json_encode($mesas));
        return $res;
    }

    public function Create(Request $req, Response $res, array $args = []) {//ver si no habria que pedir el estado, si es una mesa nueva no deberia estar cerrada
        $parametros = $req->getParsedBody();
        $codigo = generarCodigo(5);
        $idEstadoInicialMesa = EstadosMesa::Cerrada->value;

        $mesas = Mesa::GetMesas();
        if (count($mesas) >= Mesa::CANT_MAX_MESAS) {
            throw new HttpBadRequestException($req, 'Limite de mesas');
        }
        $estado = EstadoMesa::GetEstadoPorId($idEstadoInicialMesa);
        if (!isset($estado)) {
            throw new HttpBadRequestException($req, 'Estado de mesa invalido'); 
        }

        $mesa = new Mesa($codigo, $estado->id, $estado->estado);
        $id = $mesa->CrearMesa();
        $res->getBody()->write(json_encode(['mensaje' => "Mesa creada", 'id' => $id, 'codigo' => $codigo]));

        return $res;
    }

    public function Delete(Request $req, Response $res, array $args = []) {
        if (!isset($args['id'])) {
            throw new HttpBadRequestException($req, 'Debe enviar el Id de la mesa');   
        }
        $id = $args['id'];
        if (!is_numeric($id)) {
            throw new HttpBadRequestException($req, 'El Id debe ser un numero');   
        }
        $mesa = Mesa::GetMesa($id);
        if (!isset($mesa)) {
            throw new HttpNotFoundException($req, 'Mesa no existe');   
        }
        Mesa::BajaMesa($id);
        $res->getBody()->write(json_encode(['mensaje' => "Mesa eliminada"]));
        return $res; 
    }
    public function Update(Request $req, Response $res, array $args = []) {
        $parametros = $req->getParsedBody();
        //$codigo = generarCodigo(5);

        if (!isset($parametros['id']) || !isset($parametros['idEstado'])) {
            throw new HttpBadRequestException($req);
        }
        if (!is_numeric($parametros['id'])) {
            throw new HttpBadRequestException($req, 'El Id debe ser un numero');   
        }
        $estado = EstadoMesa::GetEstadoPorId($parametros['idEstado']);
        if (!isset($estado)) {
            throw new HttpBadRequestException($req, 'Id estado invalido'); 
        }

        $mesa = Mesa::GetMesa(intval($parametros['id']));
        
        if (!isset($mesa)) {
            throw new HttpNotFoundException($req, 'Mesa no existe');   
        }
        
        $mesa->idEstado = $estado->id;
        $mesa->estado = $estado->estado;
        
        $mesa->ModificarMesa();

        $res->getBody()->write(json_encode(['mensaje' => "Mesa modificada"]));

        return $res;
    }

    //el mozo ingresa el id del Pedido listo para servir y lo lleva a la mesa
    //cambia el estadode la mesa 
    public function ServirMesa(Request $req, Response $res, array $args = []) {
        $mesaClienteComiendo = 2;
        $idEstadoListoParaServir = 3;
        //$idMesa = intval($args['id']);
        $idPedido = intval($args['id']);

        $pedido = Pedido::GetPedido($idPedido);
        if (!isset($pedido)) {
            return self::RespuestaError(404, 'Pedido no existe');
        }
        if ($pedido->idEstado !== $idEstadoListoParaServir) {
            return self::RespuestaError(400, 'Pedido no terminado');
        }
        $mesa = Mesa::GetMesaPorCodigo($pedido->codigoMesa);
        if (!isset($mesa)) {
            return self::RespuestaError(404, 'Mesa no existe');
        }
        $mesa->idEstado = $mesaClienteComiendo;
        $mesa->ModificarMesa();
        $payload = json_encode(['mensaje' => "Mesa servida"]);
        $res->getBody()->write($payload);

        return $res;
    }
    //el mozo directamente va a cobrar la cuenta, validar que el estado de la mesa sea con cliente comiendo
    //no esta terminado
    public function CobrarMesa(Request $req, Response $res, array $args = []) {
        $mesaClienteComiendo = 2;
        $mesaClientePagando = 3;
        $idMesa = intval($args['id']);
        $mesa = Mesa::GetMesaPorCodigo($idMesa);
        if (!isset($mesa)) {
            return self::RespuestaError(404, 'Mesa no existe');
        }
        
        if ($mesa->idEstado < $mesaClienteComiendo) {
            return self::RespuestaError(400, 'A la mesa no se sirvio el pedido');
        }
        if ($mesa->idEstado == $mesaClientePagando) {
            return self::RespuestaError(400, 'Mesa ya cobrada');
        }
        
        $mesa->idEstado = $mesaClientePagando;
        $mesa->ModificarMesa();
        $payload = json_encode(['mensaje' => "Mesa cobrada"]);
        $res->getBody()->write($payload);

        return $res;
    }
    //el socio cierra la mesa
    public function CerrarMesa(Request $req, Response $res, array $args = []) {
        $mesaClienteComiendo = 2;
        $mesaClientePagando = 3;
        $mesaCerrada = 4;
        $idMesa = intval($args['id']);
        $mesa = Mesa::GetMesaPorCodigo($idMesa);
        if (!isset($mesa)) {
            return self::RespuestaError(404, 'Mesa no existe');
        }
        
        if ($mesa->idEstado < $mesaClientePagando) {
            return self::RespuestaError(400, 'Mesa con cliente');
        }
        
        $mesa->idEstado = $mesaCerrada;
        $mesa->ModificarMesa();
        $payload = json_encode(['mensaje' => "Mesa cerrada"]);
        $res->getBody()->write($payload);
        
        return $res;
    }
    
    public function GetMesaMasUsada(Request $req, Response $res, array $args = []) {
        $parametros = $req->getQueryParams();
        $datos = null;
        $mensaje = ['mensaje' => "No hay mesas usadas"];
        if (!isset($parametros['codigoMesa']) || EsVacioONuloOEnBlanco($parametros['codigoMesa'])) {
            return self::RespuestaError(400, 'Debe enviar codigo de mesa');
        }
        $consulta = Pedido::GetCodigoMesaConMasPedidos($parametros['codigoMesa']);

        if (isset($consulta)) {
            $mensaje = $consulta;
        }

        $payload = json_encode($mensaje);
        $res->getBody()->write($payload);

        return $res;
    }
}

?>