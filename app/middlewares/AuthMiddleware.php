<?php

require_once './utils/AutentificadorJWT.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class AuthMiddleware
{
    private $_roles = [
        'socio' => 1,
        'bartender' => 2,
        'cervecero' => 3,
        'cocinero' => 4,
        'mozo' => 5,
        'cliente' => 6,
    ];

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
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);
        $response = null;
        try {
            AutentificadorJWT::VerificarToken($token);
            $response = $handler->handle($request);
        } catch (Exception $e) {
            $response = new Response();
            $payload = json_encode(['error' => 'Hubo un error con el TOKEN']);
            $response->getBody()->write($payload);
            $response = $response->withStatus(401);

        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    private static function GetRol(Request $request) {
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);
        //var_dump($header);
        $data = AutentificadorJWT::ObtenerData($token);
        
        return $data;
    }
    private static function RespuestaNoAutorizado() {
        $response = new Response();
        $payload = json_encode(['error' => 'Usuario no autorizado']);
        $response->getBody()->write($payload);
        $response = $response->withStatus(401);
        $response = $response->withHeader('Content-Type', 'application/json');
        return $response;
    }
    private static function ManejarRespuesta($noEstaAutorizado, Request $request, RequestHandler $handler) {
        $response = $noEstaAutorizado ? self::RespuestaNoAutorizado() : $handler->handle($request);
        return $response;
    }
    //probar
    public function RechazarCliente(Request $request, RequestHandler $handler): Response {

        $rol = self::GetRol($request);
        //var_dump($rol);
        return self::ManejarRespuesta($rol === $this->_roles['cliente'], $request, $handler);
    }

    public function RechazarSocio(Request $request, RequestHandler $handler): Response {

        $rol = self::GetRol();
        return self::ManejarRespuesta($rol === $this->_roles['socio'], $request, $handler);
    }

    public function RechazarBartender(Request $request, RequestHandler $handler): Response {

        $rol = self::GetRol();
        return self::ManejarRespuesta($rol === $this->_roles['bartender'], $request, $handler);
    }

    public function RechazarCervecero(Request $request, RequestHandler $handler): Response {

        $rol = self::GetRol();
        return self::ManejarRespuesta($rol === $this->_roles['cervecero'], $request, $handler);
    }

    public function RechazarCocinero(Request $request, RequestHandler $handler): Response {

        $rol = self::GetRol();
        return self::ManejarRespuesta($rol === $this->_roles['cocinero'], $request, $handler);
    }

    public function RechazarMozo(Request $request, RequestHandler $handler): Response {

        $rol = self::GetRol();
        return self::ManejarRespuesta($rol === $this->_roles['mozo'], $request, $handler);
    }

    public function AutorizarCliente(Request $request, RequestHandler $handler): Response {

        $rol = self::GetRol();
        return self::ManejarRespuesta($rol !== $this->_roles['cliente'] && $rol !== $this->_roles['socio'], $request, $handler);
    }

    public function AutorizarSocio(Request $request, RequestHandler $handler): Response {

        $rol = self::GetRol();
        return self::ManejarRespuesta($rol !== $this->_roles['socio'], $request, $handler);
    }

    public function AutorizarBartender(Request $request, RequestHandler $handler): Response {

        $rol = self::GetRol();
        return self::ManejarRespuesta($rol !== $this->_roles['bartender'] && $rol !== $this->_roles['socio'], $request, $handler);
    }

    public function AutorizarCervecero(Request $request, RequestHandler $handler): Response {

        $rol = self::GetRol();
        return self::ManejarRespuesta($rol !== $this->_roles['cervecero'] && $rol !== $this->_roles['socio'], $request, $handler);
    }

    public function AutorizarCocinero(Request $request, RequestHandler $handler): Response {

        $rol = self::GetRol();
        return self::ManejarRespuesta($rol !== $this->_roles['cocinero'] && $rol !== $this->_roles['socio'], $request, $handler);
    }

    public function AutorizarMozo(Request $request, RequestHandler $handler): Response {

        $rol = self::GetRol();
        return self::ManejarRespuesta($rol !== $this->_roles['mozo'] && $rol !== $this->_roles['socio'], $request, $handler);
    }
}

?>