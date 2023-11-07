<?php

require_once './models/Mesa.php';
require_once './models/EstadoMesa.php';
require_once './utils/utils.php';
require_once './interfaces/IController.php';

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

        if (!isset($parametros['idEstado'])) {
            throw new HttpBadRequestException($req, 'Debe enviar el id del estado');
        }
        $estado = EstadoMesa::GetEstadoPorId($parametros['idEstado']);
        if (!isset($estado)) {
            throw new HttpBadRequestException($req, 'Debe enviar un estado de mesa valido'); 
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
        $mesa->baja = true;
        $mesa->ModificarMesa();
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
}

?>