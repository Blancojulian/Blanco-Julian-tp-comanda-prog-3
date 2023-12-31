<?php

require_once './utils/AutentificadorJWT.php';
require_once './utils/BaseRespuestaError.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class AuthMiddleware extends BaseRespuestaError
{
    private $_rolesValidos = [
        'socio' => 1,
        'bartender' => 2,
        'cervecero' => 3,
        'cocinero' => 4,
        'mozo' => 5,
        'cliente' => 6,
    ];
    private $_roles = [];
    private $_verificarRol;

    function __construct(...$roles) {
        $this->_roles = $roles;
        $this->_verificarRol = count($this->_roles) > 0;
    }
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
        $response = null;
        try {
            $estaAutorizado = false;
            $header = $request->getHeaderLine('Authorization');
            if (empty($header)) {
                return self::RespuestaError(401, 'El token esta vacio.');
            }
            $token = trim(explode("Bearer", $header)[1]);
            AutentificadorJWT::VerificarToken($token);
            if ($this->_verificarRol) {
                $data = AutentificadorJWT::ObtenerData($token);
                $rolValido = Puesto::GetPuestoPorNombre($data->rol);
                if (!isset($rolValido)) {
                    return self::RespuestaError(401, 'Rol invalido: '.$data->rol);
                }
                
                foreach ($this->_roles as $rol) {
                    $rol = strtolower($rol);
                    if ($data->rol === $rol) {
                        $estaAutorizado = true;
                        break;
                    }
                }
                if (!$estaAutorizado) {
                    return self::RespuestaError(401, 'Usuario no autorizado: ' . $data->rol);
                }
            }
            
        } catch (Exception $e) {
            return self::RespuestaError(401, 'Hubo un error con el token');
        }
        
        $response = $handler->handle($request);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function PasarDatos(Request $request, RequestHandler $handler): Response {
        $data = null;
        
        try {
            $header = $request->getHeaderLine('Authorization');
            if (empty($header)) {
                throw new Exception("Error en header");
            }
            $token = trim(explode("Bearer", $header)[1]);
            AutentificadorJWT::VerificarToken($token);
            $data = AutentificadorJWT::ObtenerData($token);
            $request = $request->withAttribute('datos', $data);
        } catch (\Exception $e) {
            return self::RespuestaError(401, 'Hubo un error con el token');
        }
        $response = $handler->handle($request);

        return $response;
    }

}

?>