<?php

require_once './models/Empleado.php';
require_once './models/Encuesta.php';
require_once './models/Operacion.php';
require_once './models/Log.php';
require_once './models/Puesto.php';
require_once './utils/utils.php';
require_once './interfaces/IController.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpException;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

class ConsultaController {
    public function GetMejoresComentarios(Request $req, Response $res, array $args = []) {
        $encuestas = Encuesta::GetEncuestasPorPuntaje(7);
        $payload = json_encode($encuestas);
        $res->getBody()->write($payload);

        return $res;
    }
    
    public function GetOperacionesPorSector(Request $req, Response $res, array $args = []) {
        $operaciones = Operacion::GetCantidadOperacionesPorSector();
        $payload = json_encode($operaciones);
        $res->getBody()->write($payload);

        return $res;
    }

    public function GetOperacionesPorEmpleado(Request $req, Response $res, array $args = []) {
        $operaciones = Operacion::GetCantidadOperacionesPorEmpleado();
        $payload = json_encode($operaciones);
        $res->getBody()->write($payload);

        return $res;
    }

    public function GetLogsPorEmpleado(Request $req, Response $res, array $args = []) {
        if (!isset($args['id'])) {
            throw new HttpBadRequestException($req, 'Debe enviar id');   
        }
        $id = $args['id'];
        if (!EsNumeroEntero($id)) {
            throw new HttpBadRequestException($req, 'Id debe ser un numero');   
        }
        $logs = Log::GetLogsPorUsuario($id);
        $payload = json_encode($logs);
        $res->getBody()->write($payload);

        return $res;
    }
    
}

?>