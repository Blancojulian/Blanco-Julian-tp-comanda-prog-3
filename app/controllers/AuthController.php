<?php

require_once './models/Puesto.php';
require_once './models/Empleado.php';
require_once './utils/utils.php';
require_once './interfaces/IController.php';
require_once './utils/BaseRespuestaError.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpException;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

class AuthController extends BaseRespuestaError {

    public function __invoke(Request $req, Response $res, array $args = []) {
        $parametros = $req->getParsedBody();
        if (!isset($parametros['email']) || !isset($parametros['contrasenia'])) {
            return self::RespuestaError(400, 'Debe enviar el email y contraseña');  
        }
        
        $empleado = Empleado::ComprobarLogin($parametros['email'], $parametros['contrasenia']);

        if (!isset($empleado)) {
            return self::RespuestaError(401, 'Email o contraseña incorrecto'); 
        }

        $datos = [
            'rol' => $empleado->puesto,
            'id' => $empleado->id
        ];

        $token = AutentificadorJWT::CrearToken($datos);
        $payload = json_encode(['jwt' => $token]);

        $res->getBody()->write($payload);
        return $res
        ->withHeader('Content-Type', 'application/json');
    }

/*
    public function Login(Request $req, Response $res, array $args = []) {

        $parametros = $req->getParsedBody();
        if (!isset($parametros['rol'])) {
            throw new HttpBadRequestException($req, 'Debe enviar el rol');   
        }
        //agregar id
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
    }*/

}

?>