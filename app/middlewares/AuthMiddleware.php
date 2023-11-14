<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class AuthMiddleware
{
    private $_socio = 1;
    private $_bartender = 2;
    private $_cervecero = 3;
    private $_cocinero = 4;
    private $_mozo = 5;
    private $_cliente = 6;


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
        if(session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $response = null;
        if (isset($_SESSION['rol'])) {
            $response = $handler->handle($request);
        } else {
            $response = new Response();
            $payload = json_encode(['error' => 'usuario no logueado']);
            $response->getBody()->write($payload);
            $response = $response->withStatus(403);
            $response = $response->withHeader('Content-Type', 'application/json');
        }
        
        return $response;
    }

    private static function GetRol() {
        if(session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        return isset($_SESSION['rol']) ? $_SESSION['rol'] : null;
    }
    private static function RespuestaNoAutorizado() {
        $response = new Response();
        $payload = json_encode(['error' => 'usuario no autorizado']);
        $response->getBody()->write($payload);
        $response = $response->withStatus(401);
        $response = $response->withHeader('Content-Type', 'application/json');
        return $response;
    }
    private static function ManejarRespuesta($noEstaAutorizado, Request $request, RequestHandler $handler) {
        $response = $noEstaAutorizado ? self::RespuestaNoAutorizado() : $handler->handle($request);
        return $response;
    }
    public function RechazarCliente(Request $request, RequestHandler $handler): Response {

        $rol = self::GetRol();
        return self::ManejarRespuesta($rol === $this->_cliente, $request, $handler);
        
        //return $response->withHeader('Content-Type', 'application/json');
    }

    public function RechazarSocio(Request $request, RequestHandler $handler): Response {

        $rol = self::GetRol();
        return self::ManejarRespuesta($rol === $this->_socio, $request, $handler);
        
        //return $response->withHeader('Content-Type', 'application/json');
    }

    public function RechazarBartender(Request $request, RequestHandler $handler): Response {

        $rol = self::GetRol();
        return self::ManejarRespuesta($rol === $this->_bartender, $request, $handler);
        
        //return $response->withHeader('Content-Type', 'application/json');
    }

    public function RechazarCervecero(Request $request, RequestHandler $handler): Response {

        $rol = self::GetRol();
        return self::ManejarRespuesta($rol === $this->_cervecero, $request, $handler);
        
        //return $response->withHeader('Content-Type', 'application/json');
    }

    public function RechazarCocinero(Request $request, RequestHandler $handler): Response {

        $rol = self::GetRol();
        return self::ManejarRespuesta($rol === $this->_cocinero, $request, $handler);
        
        //return $response->withHeader('Content-Type', 'application/json');
    }

    public function RechazarMozo(Request $request, RequestHandler $handler): Response {

        $rol = self::GetRol();
        return self::ManejarRespuesta($rol === $this->_mozo, $request, $handler);
        
        //return $response->withHeader('Content-Type', 'application/json');
    }

    public function AutorizarCliente(Request $request, RequestHandler $handler): Response {

        $rol = self::GetRol();
        return self::ManejarRespuesta($rol !== $this->_cliente && $rol !== $this->_socio, $request, $handler);
        
        //return $response->withHeader('Content-Type', 'application/json');
    }

    public function AutorizarSocio(Request $request, RequestHandler $handler): Response {

        $rol = self::GetRol();
        return self::ManejarRespuesta($rol !== $this->_socio, $request, $handler);
        
        //return $response->withHeader('Content-Type', 'application/json');
    }

    public function AutorizarBartender(Request $request, RequestHandler $handler): Response {

        $rol = self::GetRol();
        return self::ManejarRespuesta($rol !== $this->_bartender && $rol !== $this->_socio, $request, $handler);
        
        //return $response->withHeader('Content-Type', 'application/json');
    }

    public function AutorizarCervecero(Request $request, RequestHandler $handler): Response {

        $rol = self::GetRol();
        return self::ManejarRespuesta($rol !== $this->_cervecero && $rol !== $this->_socio, $request, $handler);
        
        //return $response->withHeader('Content-Type', 'application/json');
    }

    public function AutorizarCocinero(Request $request, RequestHandler $handler): Response {

        $rol = self::GetRol();
        return self::ManejarRespuesta($rol !== $this->_cocinero && $rol !== $this->_socio, $request, $handler);
        
        //return $response->withHeader('Content-Type', 'application/json');
    }

    public function AutorizarMozo(Request $request, RequestHandler $handler): Response {

        $rol = self::GetRol();
        return self::ManejarRespuesta($rol !== $this->_mozo && $rol !== $this->_socio, $request, $handler);
        
        //return $response->withHeader('Content-Type', 'application/json');
    }
}

?>