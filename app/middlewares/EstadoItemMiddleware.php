<?php

require_once './enums/EEstadosPedido.php';
require_once './utils/utils.php';
require_once './utils/BaseRespuestaError.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;
use Slim\Exception\HttpException;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

class EstadoItemMiddleware extends BaseRespuestaError
{
    private $_rolEmpleado;
    private $_idEstado;

    function __construct($idEstado) {
        //$this->_rolEmpleado = $rolEmpleado;
        $this->_idEstado = $idEstado;
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
        try {
            $this->ControlarCambioEstadoItem($request, $this->_idEstado);
        } catch (\Exception $e) {
            return self::RespuestaError(400, $e->getMessage());
        }
        $response = $handler->handle($request);

        return $response;
    }
    
    private function ControlarEncargadoDelItem(int $idTipoProducto, string $rol) {
        //$idTipoBebida = 1;
        //$idTipoComida = 2;
        //$idTipoCerveza = 3;
        $arr = [
            'bartender' => 1,
            'cocinero' => 2,
            'cervecero' => 3
        ];

        if ($arr[$rol] !== $idTipoProducto) {
            throw new Exception('El empleado no puede encargarse del item');
        }
    }

    //va a recibir el id pedido y un array con los ids de los productos y los minutos estimados que se van a atender
    //ver si hay un array de obj con el id item y los minutos estimados
    /*
    [
        {
            "idItem": 1,
            "minutosEstimados": 30
        },
        {
            "idItem": 2,
            "minutosEstimados": 20
        }
    ]
    */
    //validar que cuando se cambie a listo para servir sea el mismo empleado que acepto encargarse del item
    private function ControlarCambioEstadoItem(Request $request, int $idEstado) {

        if ($idEstado !== EstadosPedido::EnPreparacion->value && $idEstado !== EstadosPedido::ListoParaServir->value) {
            throw new Exception('Solamente se puede cambiar el estado a en preparacion o listo para servir');
        }
        $parametros = $request->getParsedBody();
        
        $item = null;
        $listaIdsPendiente = [];
        $listaIdsEnPreparacion = [];
        $listaIdsListoParaServir = [];
        $listaIdsEmpleadoNoEncargado = [];
        $listaIdsItemYaEncargado = [];
        $listaIdsNoExisten = [];
        $mensajeError = null;
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);
        AutentificadorJWT::VerificarToken($token);
        $data = AutentificadorJWT::ObtenerData($token);
        $rolEmpleado = $data->rol;
        $idEmpleado = $data->id;

        if (!isset($parametros['items']) || !is_array($parametros['items']) || count($parametros['items']) <= 0) {
            throw new Exception('Debe enviar los items y minutos estimados');
        }
        
        //ahora el item tiene el id del pedido, ya no se va a enviar el idPedido

        $esEnPreparacion = $idEstado == EstadosPedido::EnPreparacion->value;//si se esta por terminar el item, no va tener minutosEstimados

        foreach($parametros['items'] as $i) {
            if (!isset($i['idItem']) || ($esEnPreparacion && !isset($i['minutosEstimados']))) {
                throw new Exception('En los items debe enviar el id item y minutos estimados');
            }
            if ($esEnPreparacion && !EsNumeroEntero($i['minutosEstimados']) ) {
                throw new Exception('Los minutos estimados debe ser un numero entero, id item: ' . $i['idItem']);
            }
            $item = ItemPedido::GetItem(intval($i['idItem']));
            if (!isset($item)) {
                array_push($listaIdsNoExisten, $i['idItem']);
                continue;
            }
            $this->ControlarEncargadoDelItem($item->idTipoProducto, $rolEmpleado);

            if ($idEstado == EstadosPedido::ListoParaServir->value && $item->idEstado == EstadosPedido::Pendiente->value) {//si se quiere pasar de estado pendiente a listo
                array_push($listaIdsPendiente, $item->id);
            } else if ($idEstado == EstadosPedido::EnPreparacion->value && $item->idEstado == EstadosPedido::EnPreparacion->value) {//si ya se encuentra en preparacion
                array_push($listaIdsEnPreparacion, $item->id);
            } else if ($item->idEstado >= EstadosPedido::ListoParaServir->value) {//si el item ya esta listo
                array_push($listaIdsListoParaServir, $item->id);
            } else if (($idEstado === EstadosPedido::ListoParaServir->value && isset($item->idEmpleado) && $idEmpleado != $item->idEmpleado) ||
            ($idEstado === EstadosPedido::EnPreparacion->value && isset($item->idEmpleado)) ) {//si otro empleado se encarga del item o si se quiere servir pero otro se encarga 
                array_push($listaIdsItemYaEncargado, $item->id);
            }
        }

        if (count($listaIdsNoExisten) > 0) {
            $mensajeError = 'El item no existe, id items: ' . implode(", ", $listaIdsNoExisten) . "\n";
        }
        if (count($listaIdsItemYaEncargado) > 0) {
            $mensajeError = 'Otro empleado esta encargado del item, id items: ' . implode(", ", $listaIdsItemYaEncargado) . "\n";
        }
        if (count($listaIdsPendiente) > 0) {
            $mensajeError = 'El item se encuentra pendiente, primero debe encargar, id items: ' . implode(", ", $listaIdsEnPreparacion) . "\n";
        }

        if (count($listaIdsEnPreparacion) > 0) {
            $mensajeError = 'El item ya se encuentra en preparación, id items: ' . implode(", ", $listaIdsEnPreparacion) . "\n";
        }

        if (count($listaIdsListoParaServir) > 0) {
            $mensajeError = 'El item ya se encuentra listo para servir, id items: ' . implode(", ", $listaIdsListoParaServir) . "\n";
        }

        if (isset($mensajeError)) {
            throw new Exception($mensajeError);
        }
    }
}