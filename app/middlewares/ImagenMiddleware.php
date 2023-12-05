<?php

require_once './utils/BaseRespuestaError.php';
require_once './utils/utils.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Slim\Exception\HttpException;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

class ImagenMiddleware extends BaseRespuestaError
{
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
        $uploadedFiles = $request->getUploadedFiles();
        $mb = 1024 * 1024;
        
        if (!isset($uploadedFiles['imagen'])) {
            return self::RespuestaError(400, 'No se envio la imagen');
        }

        if ($uploadedFiles['imagen']->getError() !== UPLOAD_ERR_OK) {
            return self::RespuestaError(400, 'Error al subir la imagen');
        }
        
        $ext = pathinfo($uploadedFiles['imagen']->getClientFilename(), PATHINFO_EXTENSION);
        if ($ext !== 'jpg') {
            return self::RespuestaError(400, 'La extensión incorrecta, se permiten archivos .jpg');
        }

        if ($uploadedFiles['imagen']->getSize() > (10 * $mb)) {
            return self::RespuestaError(400, 'El tamaño del archivo supera el limite, se permiten archivos de 10 mb máximo.');
        }
        
        
        
        $response = $handler->handle($request);

        /*
        //le puse atributo a la req en el controller pero no sirve, vuelven en NULL
        if ($response->getStatusCode() === 200) {
            $arr = $request->getAttribute('archivo');
            $arr2 = $request->getAttribute('archivo2');
            $arr3 = $request->getAttribute('archivo3');
            var_dump($arr);
            var_dump($arr2);
            var_dump($arr3);
        }*/

        return $response;
    }

}

?>