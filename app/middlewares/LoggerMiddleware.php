<?php

require_once './models/Log.php';
require_once './models/Operacion.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;
use Slim\Exception\HttpException;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

class LoggerMiddleware
{
    private $_descripcion;

    function __construct($descripcion) {
        $this->_descripcion = $descripcion;
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
        //usar solamente en ruta donde se verifica el jwt
        $data = self::GetDataFromRequest($request);
        $horario = date('Y-m-d H:i:s');
        $response = $handler->handle($request);
        $codigo = $response->getStatusCode();
        if (isset($data) && $codigo >= 200 && $codigo < 300) {
            //($idUsuario, $rol, $horario, $id = null) {
            $operacion = new Operacion($data->id, $data->rol, $horario, $this->_descripcion);    
            $operacion->CrearOperacion();
        }
        
        return $response;
    }

    private function GetDataFromRequest(Request $request) {
        $data = null;
        $header = $request->getHeaderLine('Authorization');
        if (!empty($header)) {
            $token = trim(explode("Bearer", $header)[1]);
            AutentificadorJWT::VerificarToken($token);
            $data = AutentificadorJWT::ObtenerData($token);
        }
        return $data;
    }

    //logguear los ingresos de los usuarios
    public function LogIngreso(Request $request, RequestHandler $handler): Response {
        
        $response = $handler->handle($request);
        
        $codigo = $response->getStatusCode();
        if ($codigo >= 200 && $codigo < 300) {
            $json = json_decode($response->getBody()->__toString());
            $data = AutentificadorJWT::ObtenerData($json->jwt);
            $log = new Log($data->id, $data->rol);
            $log->CrearLog();
        }
        return $response;
    }
    
}