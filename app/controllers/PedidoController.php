<?php

require_once './utils/AutentificadorJWT.php';
require_once './models/Pedido.php';
require_once './models/Encuesta.php';
require_once './models/EstadoPedido.php';
require_once './models/ItemPedido.php';
require_once './models/Mesa.php';
require_once './utils/utils.php';
require_once './interfaces/IController.php';
require_once './enums/EEstadosMesa.php';
require_once './enums/EEstadosPedido.php';
require_once './utils/BaseRespuestaError.php';


use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpException;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

class PedidoController extends BaseRespuestaError implements IController {

    private $_carpetaPedidos = './Imagenes/ImagenesDePedidos/2023/';


    public function Get(Request $req, Response $res, array $args = []) {
        if (!isset($args['id'])) {
            throw new HttpBadRequestException($req);   
        }
        $id = $args['id'];
        if (!is_numeric($id)) {
            throw new HttpBadRequestException($req);   
        }
        $pedido = Pedido::GetPedido($id);
        if (!isset($pedido)) {
            throw new HttpNotFoundException($req, 'Pedido no existe');   
        }
        $res->getBody()->write(json_encode($pedido));
        return $res; 
    }

    public function GetAll(Request $req, Response $res, array $args = []) {
        
        $pedidos = Pedido::GetPedidos();
        $payload = json_encode(array("pedidos" => $pedidos));
        //var_dump($req->getParsedBody());
        //$res->getBody()->write(json_encode($pedidos));
        $res->getBody()->write($payload);

        return $res; 
    }

    public function GetBebidasPendientes(Request $req, Response $res, array $args = []) {
        
        $idTipoBebida = 1;
        $idEstadoPendiente = 1;
        $pedidos = ItemPedido::GetItemsPorTipoProducto($idTipoBebida, $idEstadoPendiente);
        $payload = json_encode(["items pedidos" => $pedidos]);
        $res->getBody()->write($payload);

        return $res; 
    }

    public function GetComidasPendientes(Request $req, Response $res, array $args = []) {
        
        $idTipoComida = 2;
        $idEstadoPendiente = 1;
        $pedidos = ItemPedido::GetItemsPorTipoProducto($idTipoComida, $idEstadoPendiente);
        $payload = json_encode(["items pedidos" => $pedidos]);
        $res->getBody()->write($payload);

        return $res; 
    }

    public function GetCervezasPendientes(Request $req, Response $res, array $args = []) {
        
        $idTipoCerveza = 3;
        $idEstadoPendiente = 1;
        $pedidos = ItemPedido::GetItemsPorTipoProducto($idTipoCerveza, $idEstadoPendiente);
        $payload = json_encode(["items pedidos" => $pedidos]);
        $res->getBody()->write($payload);

        return $res; 
    }

    public function GetProductosEncargados(Request $req, Response $res, array $args = []) {
        $data = $this->GetDataFromRequest($req);
        $items = ItemPedido::GetItemsPorIdEmpleado($data->id);
        $payload = json_encode(["productos del empleado" => $items]);
        $res->getBody()->write($payload);
        
        return $res; 
    }

    public function GetPedidosListos(Request $req, Response $res, array $args = []) {
        
        $pedidos = Pedido::GetPedidosListosParaServir();
        $res->getBody()->write(json_encode($pedidos));
        return $res;
    }

    public function GetAllPorCriterio(Request $req, Response $res, array $args = []) {
        if (!isset($args['idEstado'])) {
            throw new HttpBadRequestException($req);
            
        }
        $estado = EstadoPedido::GetEstadoPorId(intval($args['idEstado']));
        if (!isset($estado)) {
            throw new HttpBadRequestException($req);
            
        }
        $pedidos = Pedido::GetPedidosPorIdEstado($estado->id);
        $res->getBody()->write(json_encode($pedidos));
        return $res;
    }
    //ver
    //el estado inicial va ser pendiente
    //agregar tiempo estimado a cada item, y cada empleado que se encarga
    //sacar cantidad, que se repitan los item del pedido
    //fecha de cheacion por cada item
    public function Create(Request $req, Response $res, array $args = []) {//ver si no habria que pedir el estado, si es una mesa nueva no deberia estar cerrada
        $parametros = $req->getParsedBody();
        $data = $this->GetDataFromRequest($req);//ver si esta bien hacer esto, o si tendria que mandar el idEmpleado por parametro
        $idMozo = $data->id;
        $codigo = generarCodigo(5);
        $itemsParseados = ItemPedido::ConvertirAArrayItems($parametros['items']);

        //validar estado del pedido
        // estado inicial pendiente
        $estado = EstadoPedido::GetEstadoPorId(EstadosPedido::EnPreparacion->value);
        if (!isset($estado)) {
            throw new HttpBadRequestException($req, 'No se encontro el estado pendiente'); 
        }

        $mesa = Mesa::GetMesaPorCodigo($parametros['codigoMesa']);
        
        $pedido = new Pedido($codigo, $parametros['nombreCliente'], $mesa->id, 
        $mesa->codigo, $estado->id, $estado->estado, $idMozo);
        $pedido->items = $itemsParseados;
        $pedido->CalcularTotal();
        $id = $pedido->CrearPedido();

        ItemPedido::CrearItems($id, $pedido->items);//hacer if para comprobar si falla
        
        //cambiar mesa a cliente esperando
        $mesa->idEstado = EstadosMesa::ClienteEsperando->value;
        $mesa->ModificarMesa();

        $payload = json_encode(['mensaje' => "Pedido creado", 'id' => $id, 'codigo' => $codigo]);
        $res->getBody()->write($payload);
        
        return $res;
    }

    public function Delete(Request $req, Response $res, array $args = []) {
        
        $id = $args['id'];
        $pedido = Pedido::GetPedido($id);
        if (!isset($pedido)) {
            throw new HttpNotFoundException($req, 'Pedido no existe');   
        }
        Pedido::CancelarPedido($pedido->id);
        $payload = json_encode(['mensaje' => "Pedido cancelado"]);
        $res->getBody()->write($payload);
        return $res; 
    }
    //ver
    //que el cocinero ingrese el tiempo de espera cuando se encargar del pedido
    //hay que comparar los viejos items y los nuevos, eliminar los que no estan y update los otros
    //deberia pone el estado inicial en pendiente, en mas no se tendria que cambiar por update
    public function Update(Request $req, Response $res, array $args = []) {
        $parametros = $req->getParsedBody();
    
        //validar que mesa este libre
        $estadoMesaLibre = 5;
        $mesaClienteEsperando = 1;
        $mesa = Mesa::GetMesaPorCodigo($parametros['codigoMesa']);
        if (!isset($mesa)) {
            throw new HttpBadRequestException($req, 'Mesa codigo '.$parametros['codigoMesa'].' no existe'); 
        }

        $pedido = Pedido::GetPedido(intval($parametros['id']));
        
        $pedido->nombreCliente = $parametros['nombreCliente'];
        $pedido->codigoMesa = $parametros['codigoMesa'];
        $pedido->idEstado = $estado->id;
        $pedido->estado = $estado->estado;
        if (isset($parametros['items'])) {
            $itemsParseados = ItemPedido::ConvertirAArrayItems($parametros['items'], $pedido->id);
            $pedido->items = $itemsParseados;
            $pedido->CalcularTotal();
            ItemPedido::EliminarItems($pedido->id);
            ItemPedido::CrearItems($pedido->id, $pedido->items);
        }

        $pedido->ModificarPedido();
        
        $payload = json_encode(['mensaje' => "Pedido modificado", 'id' => $id, 'codigo' => $codigo]);
        $res->getBody()->write($payload);

        return $res;
    }

    public function AgregarImagen(Request $req, Response $res, array $args = []) {
        $parametros = $req->getParsedBody();

        $pedido = Pedido::GetPedido(intval($parametros['id']));
        if (!isset($pedido)) {
            return self::RespuestaError(404, 'Pedido no existe');
        }
        $uploadedFiles = $req->getUploadedFiles();
        $uploadedFile = $uploadedFiles['imagen'];
        $filename = FormatearNumero($pedido->id) . "-$pedido->codigo-$pedido->codigoMesa.jpg";
        moveUploadedFile($this->_carpetaPedidos, $filename, $uploadedFile);
        $pedido->imagen = "$filename.jpg";
        $pedido->ModificarPedido();
        $payload = json_encode(['mensaje' => "Se agreggo imagen al pedido $pedido->codigo"]);
        $res->getBody()->write($payload);

        return $res;
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
    //este hay que pasar a un controller EstadoItem o hacer AtenderItems y TerminarItems
    //ahora conculto el el pedido desde idPedido del item
    //asi se puede preparar items de distintos pedidos
    //ItemPedido::MofidicarItem(intval($parametros['idPedido']), $item);
    //cambiar esta linea: ItemPedido::MofidicarItem(intval($parametros['idPedido']), $item);
    public function AtenderItems(Request $req, Response $res, array $args = []) {
        $parametros = $req->getParsedBody();
        $data = $this->GetDataFromRequest($req);//ver si esta bien hacer esto, o si tendria que mandar el idEmpleado por parametro
        $items = [];
        $item = null;
        $minutos = null;
        $horaEstimada = null;
        $idEstadoEnPreparacion = 2;

        foreach ($parametros["items"] as $i) {

            $item = ItemPedido::GetItem(intval($i['idItem']));
            $pedido = Pedido::GetPedido($item->idPedido);

            $minutos = $i['minutosEstimados'];
            $horaEstimada = new DateTime();
            $horaEstimada->add(new DateInterval('PT'.$minutos.'M'));
            $item->tiempoEstimado = $horaEstimada;
            $item->idEstado = $idEstadoEnPreparacion;
            $item->idEmpleado = $data->id;

            //ItemPedido::MofidicarItem(intval($parametros['idPedido']), $item);
            $item->MofidicarItem();
            $pedido->idEstado = $idEstadoEnPreparacion;

            if (!isset($pedido->tiempoEstimado) || $horaEstimada > $pedido->tiempoEstimado) {
                $pedido->tiempoEstimado = $horaEstimada;
            }

            $pedido->ModificarPedido();
        }
        
        $payload = json_encode(['mensaje' => "Se estan preparando los items"]);
        $res->getBody()->write($payload);

        return $res;
    }

    public function TerminarItems(Request $req, Response $res, array $args = []) {
        $parametros = $req->getParsedBody();
        $items = [];
        $item = null;
        $minutos = null;
        $horaEstimada = null;
        $horaMaxima = null;
        $idEstadoListoParaServir = 3;
        $horaFinalizado = new DateTime();//date('Y/m/d H:i:s', strtotime("now"));


        foreach ($parametros["items"] as $i) {

            $item = ItemPedido::GetItem(intval($i['idItem']));
            //$pedido = Pedido::GetPedido($item->idPedido);

            $item->horaFinalizado = $horaFinalizado;
            $item->idEstado = $idEstadoListoParaServir;
            $item->MofidicarItem();

            //ItemPedido::MofidicarItem(intval($parametros['idPedido']), $item);
            $estaListoPedido = self::TerminarPedido($item->idPedido, $horaFinalizado);
        }
        
        //$pedido = Pedido::GetPedido(intval($parametros['idPedido']));
        
        //$estaListoPedido = self::TerminarPedido($pedido->id, $horaFinalizado);
        
        $payload = json_encode(['mensaje' => "Se terminaron los item"]);
        $res->getBody()->write($payload);

        return $res;
    }

    
    private static function TerminarPedido($idPedido, $horaFinalizado) {
        $pedido = Pedido::GetPedido(intval($idPedido));
        $estaListoPedido = true;
        $idEstadoListoParaServir = 3;
        //$estado = EstadoPedido::GetEstadoPorId($idEstadoListoParaServir);

        foreach ($pedido->items as $item) {
            if ($item->idEstado != $idEstadoListoParaServir) {
                $estaListoPedido = false;
                break;
            }
        }
        if ($estaListoPedido) {
            $pedido->idEstado = $idEstadoListoParaServir;
            $pedido->horaFinalizado = $horaFinalizado;
            $pedido->ModificarPedido();
        }
        return $estaListoPedido;
    }

    public function PuntuarPedido(Request $req, Response $res, array $args = []) {
        $parametros = $req->getParsedBody();
        $arrayPuntajeItems = $parametros['puntajeItems'];
        $pedido = Pedido::GetPedidoPorCodigo($parametros['codigoPedido']);
        //if (!isset($parametros['codigoPedido']) || !isset($parametros['codigoMesa']) || !isset($parametros['resenia']) || !isset($parametros['puntajeMesa']) || !isset($parametros['puntajeMozo']) || 
        //!isset($parametros['puntajeItems']) || !is_array($parametros['puntajeItems']) || count($parametros['puntajeItems']) <= 0) {

        //($idPedido, $resenia, $puntajeRestaurante, $puntajeMozo, $puntajeMesa, $fechaAlta = null, $fechaModificacion = null, $fechaCancelacion = null, $id = null, $items = []) {
        $encuesta = new Encuesta($pedido->id, $parametros['resenia'], intval($parametros['puntajeRestaurante']), 
        intval($parametros['puntajeMozo']), intval($parametros['puntajeMesa']));
        $encuesta->CrearEncuesta();

        $index = null;
        foreach ($pedido->items as $item) {
            $index = array_find_index(fn($i) => $i['idItem'] == $item->id, $arrayPuntajeItems);
            if ($index != -1) {
                $item->puntaje = intval($arrayPuntajeItems[$index]['puntaje']);
                $item->MofidicarItem();
            }
        }

        $payload = json_encode(['mensaje' => "Encuesta recibida"]);
        $res->getBody()->write($payload);

        return $res;
    }

    public function ConsultarPedido(Request $req, Response $res, array $args = []) {
        $parametros = $req->getQueryParams();

        if (!isset($parametros['codigoPedido']) || !isset($parametros['codigoMesa']) ||
        EsVacioONuloOEnBlanco($parametros['codigoPedido']) || EsVacioONuloOEnBlanco($parametros['codigoMesa'])) {
            return self::RespuestaError(400, 'Debe enviar codigo de pedido y codigo de mesa');
        }

        $pedido = Pedido::GetPedidoPorCodigo($parametros['codigoPedido']);
        if (!isset($pedido) || $pedido->codigoMesa != $parametros['codigoMesa']) {
            return self::RespuestaError(404, 'Pedido no encontrado');
        }
        $ahora = new DateTime();
        $payload = null;
        if (isset($pedido->horaEntrega)) {
            $payload = json_encode(['mensaje' => 'Ya se entrego el pedido']);
        } else if ($pedido->tiempoEstimado instanceof DateTime) {
            $estaAtrasado = $ahora > $pedido->tiempoEstimado;
            $estado = $estaAtrasado ? 'atrasado' : 'En preparación';
            $signo = $estaAtrasado ? '- ' : '';
            $payload = json_encode([
                'horaEstimada' => $pedido->GetTiempoEstimado(),
                'tiempoRestante'=> $signo . CalcularTiempoRestante($pedido->tiempoEstimado),
                'estado' => $estado
            ]);
        } else {
            $payload = json_encode(['mensaje' => 'No se esta preparando el pedido']);
        }
        $res->getBody()->write($payload);

        return $res;
    }
    /*
    public function GetMejoresComentarios(Request $req, Response $res, array $args = []) {
        $resenias = Pedido::GetPedidosPorPuntaje(7);
        $payload = json_encode($resenias);
        $res->getBody()->write($payload);

        return $res;
    }*/
    

    public function GetPedidosPorEntrega(Request $req, Response $res, array $args = []) {
        $parametros = $req->getQueryParams();

        if (!isset($parametros['filtro']) || ($parametros['filtro'] != 'fueraDeTiempo' && $parametros['filtro'] != 'enTermino')) {
            return self::RespuestaError(400, 'Debe enviar filtro, fueraDeTiempo o enTermino');
        }

        $fueraDeTiempo = $parametros['filtro'] == 'fueraDeTiempo';
        $pedidos = Pedido::GetPedidosPorEntrega($fueraDeTiempo);
        $payload = json_encode($pedidos);
        $res->getBody()->write($payload);

        return $res;
    }

    public function ServirPedido(Request $req, Response $res, array $args = []) {
        $mesaClienteComiendo = 2;
        $idEstadoListoParaServir = 3;
        $mesaClienteComiendo = 2;
        //$idMesa = intval($args['id']);
        $idPedido = intval($args['id']);

        $pedido = Pedido::GetPedido($idPedido);
        if (!isset($pedido)) {
            return self::RespuestaError(404, 'Pedido no existe');
        }
        if ($pedido->idEstado !== $idEstadoListoParaServir) {
            return self::RespuestaError(400, 'Pedido no terminado');
        }
        if (isset($pedido->horaEntrega)) {
            return self::RespuestaError(400, 'Pedido ya entregado');
        }
        $mesa = Mesa::GetMesa($pedido->idMesa);
        if (!isset($mesa)) {
            return self::RespuestaError(404, 'Mesa no existe');
        }
        if ($mesa->idEstado == $mesaClienteComiendo) {
            return self::RespuestaError(400, 'Mesa ya servida');
        }
        $pedido->horaEntrega = new DateTime();
        $pedido->ModificarPedido();
        $mesa->idEstado = $mesaClienteComiendo;
        $mesa->ModificarMesa();
        $payload = json_encode(['mensaje' => "Pedido servido"]);
        $res->getBody()->write($payload);

        return $res;
    }
    
}

?>