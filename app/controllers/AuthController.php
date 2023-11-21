<?php

require_once './models/Puesto.php';
require_once './utils/utils.php';
require_once './interfaces/IController.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpException;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

class AuthController {

    private static function StartSession() {
        if(session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public function Login(Request $req, Response $res, array $args = []) {

        $parametros = $req->getParsedBody();
        if (!isset($parametros['rol'])) {
            throw new HttpBadRequestException($req, 'Debe enviar el rol');   
        }
        
        $puesto = Puesto::GetPuestoPorNombre($parametros['rol']);
        if (!isset($puesto)) {
            throw new HttpBadRequestException($req, 'Rol invalido');       
        }

        $datos = ['rol' => $puesto->id];

        $token = AutentificadorJWT::CrearToken($datos);
        $payload = json_encode(array('jwt' => $token));

        $res->getBody()->write($payload);
        return $res
        ->withHeader('Content-Type', 'application/json');
    }
/*
    public function Logout(Request $req, Response $res, array $args = []) {
        
        self::StartSession();
        $mensaje = 'No esta loagueado';
        if (isset($_SESSION['rol'])) {
            session_unset();
            session_destroy();
            $mensaje = 'logout';
        }
        $res->getBody()->write(json_encode(['mensaje' => $mensaje]));
        return $res; 
    }
    public function GetRol(Request $req, Response $res, array $args = []) {
        
        self::StartSession();
        $rol = null;
        if (isset($_SESSION['rol'])) {
            $rol = $_SESSION['rol'];
        }
        $res->getBody()->write(json_encode(['rol' => $rol]));
        return $res; 
    }*/
}

?>