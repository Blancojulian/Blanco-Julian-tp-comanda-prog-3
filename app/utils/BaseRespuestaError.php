<?php

use Slim\Psr7\Response;

class BaseRespuestaError {
    protected static function RespuestaError($codigo = 400, $error = 'Faltan parametros') {
        $response = new Response();
        $payload = json_encode(['error' => $error]);
        $response->getBody()->write($payload);
        $response = $response->withStatus($codigo);
        $response = $response->withHeader('Content-Type', 'application/json');
        return $response;
    }
}

?>