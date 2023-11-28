<?php

require_once './models/Pedido.php';
require_once './models/EstadoPedido.php';
require_once './models/Mesa.php';
require_once './utils/utils.php';
require_once './utils/Session.php';
require_once './interfaces/IController.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpException;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

class PedidoController implements IController {

    private $_carpetaClientes = './Imagenes/ImagenesDePedidos/2023/';


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
        echo $_ENV['CLAVE'];
        echo 'hola';
        $idTipoBebida = 1;
        $idEstadoPendiente = 1;
        $pedidos = Pedido::GetPedidosPorTipoProducto($idTipoBebida, $idEstadoPendiente);
        $payload = json_encode(["pedidos" => $pedidos]);
        $res->getBody()->write($payload);

        return $res; 
    }

    public function GetComidasPendientes(Request $req, Response $res, array $args = []) {
        
        $idTipoComida = 2;
        $idEstadoPendiente = 1;
        $pedidos = Pedido::GetPedidosPorTipoProducto($idTipoComida, $idEstadoPendiente);
        $payload = json_encode(["pedidos" => $pedidos]);
        $res->getBody()->write($payload);

        return $res; 
    }

    public function GetCervezasPendientes(Request $req, Response $res, array $args = []) {
        
        $idTipoCerveza = 3;
        $idEstadoPendiente = 1;
        $pedidos = Pedido::GetPedidosPorTipoProducto($idTipoCerveza, $idEstadoPendiente);
        $payload = json_encode(["pedidos" => $pedidos]);
        $res->getBody()->write($payload);

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
        $uploadedFiles = $req->getUploadedFiles();
        $uploadedFile = $uploadedFiles['imagen'];
        $codigo = generarCodigo(5);
        //parsear el array a un array de ItemPedido
        $itemsParseados = ItemPedido::ConvertirAArrayItems($parametros['items']);

        //validar estado del pedido
        $estadoPendiente = 1;// estado inicial pendiente
        $estado = EstadoPedido::GetEstadoPorId($estadoPendiente);
        if (!isset($estado)) {
            throw new HttpBadRequestException($req); 
        }

        $estadoMesaClienteEsperando = 1;
        $mesa = Mesa::GetMesaPorCodigo($parametros['codigoMesa']);
        
        $pedido = new Pedido($codigo, $parametros['nombreCliente'],
        $parametros['codigoMesa'], $estado->id, $estado->estado);
        //echo 'tienmpo estimado';
        //echo json_encode(['tiempo_estimado' => $pedido->tiempoEstimado]);
        $pedido->items = $itemsParseados;
        $pedido->CalcularTotal();
        $id = $pedido->CrearPedido();

        ItemPedido::CrearItems($id, $pedido->items);//hacer if para comprobar si falla
        
        $filename = FormatearNumero($pedido->id) . "$codigo-$pedido->codigoMesa.jpg";
        moveUploadedFile($this->_carpetaPedido, $filename, $uploadedFile);
        $pedido->SetArchivoImagen("$filename.jpg");

        //cambiar mesa a cliente esperando
        $mesa->idEstado = $estadoMesaClienteEsperando;
        $mesa->ModificarMesa();
        $res->getBody()->write(json_encode(['mensaje' => "Pedido creado", 'id' => $id, 'codigo' => $codigo]));

        return $res;
    }

    public function Delete(Request $req, Response $res, array $args = []) {
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
        $pedido->cancelado = true;
        $pedido->ModificarPedido();
        $res->getBody()->write(json_encode(['mensaje' => "Pedido cancelado"]));
        return $res; 
    }
    //ver
    //que el cocinero ingrese el tiempo de espera cuando se encargar del pedido
    //hay que comparar los viejos items y los nuevos, eliminar los que no estan y update los otros
    public function Update(Request $req, Response $res, array $args = []) {
        $parametros = $req->getParsedBody();
        //$codigo = generarCodigo(5);
        //var_dump($parametros);
        if ( !isset($parametros['id']) || !isset($parametros['nombreCliente']) || !isset($parametros['codigoMesa']) ||
        !isset($parametros['idEstado']) || !isset($parametros['items'])) {
            throw new HttpBadRequestException($req, 'Debe enviar id, nombre del cliente, codigo mesa y los items');
        }
        //parsear el array a un array de ItemPedido
        $itemsParseados = ItemPedido::ConvertirAArrayItems($parametros['items']);

        //validar estado del pedido
        $estado = EstadoPedido::GetEstadoPorId($parametros['idEstado']);
        if (!isset($estado)) {
            throw new HttpBadRequestException($req, 'Estado pedido invalido'); 
        }

        //validar que mesa este libre
        $estadoMesaLibre = 5;
        $mesaClienteEsperando = 1;
        $mesa = Mesa::GetMesaPorCodigo($parametros['codigoMesa']);
        if (!isset($mesa)) {
            throw new HttpBadRequestException($req, 'Mesa codigo '.$parametros['codigoMesa'].' no existe'); 
        }
        /*
        if ( $mesa->idEstado !== $estadoMesaLibre ) {
            throw new HttpBadRequestException($req, 'La mesa esta ocupada'); 
        }*/

        $pedido = Pedido::GetPedido(intval($parametros['id']));
        if (isset($pedido)) {
            throw new Exception("Error Processing Request", 1);
            
        }
        $pedido->nombreCliente = $parametros['nombreCliente'];
        $pedido->codigoMesa = $parametros['codigoMesa'];
        $pedido->idEstado = $estado->id;
        $pedido->estado = $estado->estado;
        $pedido->items = $itemsParseados;
        $pedido->CalcularTotal();

        $pedido->ModificarPedido();
        ItemPedido::EliminarItems($pedido->id);
        ItemPedido::CrearItems($pedido->id, $pedido->items);

        $pedido = new Pedido($codigo, $parametros['nombreCliente'],
        $parametros['codigoMesa'], $estado->id, $estado->estado);

        $pedido->items = $itemsParseados;
        $pedido->CalcularTotal();
        $id = $pedido->CrearPedido();
        //cambiar mesa a cliente esperando
        $mesa->idEstado = $mesaClienteEsperando;
        $mesa->ModificarMesa();
        $res->getBody()->write(json_encode(['mensaje' => "Pedido creado", 'id' => $id, 'codigo' => $codigo]));

        return $res;
    }

    public function AtenderPedidoBebidas(Request $req, Response $res, array $args = []) {
        $parametros = $req->getParsedBody();
        //validar tiempo estimado
        $minutos = $parametros['minutosEstimado'];
        $horaEstimada = new DateTime();
        $horaEstimada->add(new DateInterval('PT'.$minutos.'M')); 
        $pedido = Pedido::GetPedido(intval($parametros['id']));

        if (!isset($pedido->tiempoEstimado) || $horaEstimada > $pedido->tiempoEstimado) {
            $pedido->tiempoEstimado = $horaEstimada;
        }

        $idEstadoEnPreparacion = 2;
        $estado = EstadoPedido::GetEstadoPorId($idEstadoEnPreparacion);
        if (!isset($estado)) {
            throw new HttpBadRequestException($req, 'Estado pedido invalido'); 
        }

        $pedido->idEstado = $estado->id;
        $pedido->estado = $estado->estado;
        $idTipoBebida = 1;
        ItemPedido::SetEstadoItemsPorTipoProducto($pedido->id, $idEstadoEnPreparacion, $idTipoBebida);
    
        $res->getBody()->write(json_encode(['mensaje' => "Se estan preparando las bebidas"]));

        return $res;
    }
    public function AtenderPedidoComidas(Request $req, Response $res, array $args = []) {
        $parametros = $req->getParsedBody();
        //validar tiempo estimado
        $minutos = $parametros['minutosEstimado'];
        $horaEstimada = new DateTime();
        $horaEstimada->add(new DateInterval('PT'.$minutos.'M')); 
        $pedido = Pedido::GetPedido(intval($parametros['id']));

        if (!isset($pedido->tiempoEstimado) || $horaEstimada > $pedido->tiempoEstimado) {
            $pedido->tiempoEstimado = $horaEstimada;
        }

        $idEstadoEnPreparacion = 2;
        $estado = EstadoPedido::GetEstadoPorId($idEstadoEnPreparacion);
        if (!isset($estado)) {
            throw new HttpBadRequestException($req, 'Estado pedido invalido'); 
        }

        $pedido->idEstado = $estado->id;
        $pedido->estado = $estado->estado;
        $idTipoComida = 2;
        ItemPedido::SetEstadoItemsPorTipoProducto($pedido->id, $idEstadoEnPreparacion, $idTipoComida);
    
        $res->getBody()->write(json_encode(['mensaje' => "Se estan preparando las bebidas"]));

        return $res;
    }
    public function AtenderPedidoCervezas(Request $req, Response $res, array $args = []) {
        $parametros = $req->getParsedBody();
        //validar tiempo estimado
        $minutos = $parametros['minutosEstimado'];
        $horaEstimada = new DateTime();
        $horaEstimada->add(new DateInterval('PT'.$minutos.'M')); 
        $pedido = Pedido::GetPedido(intval($parametros['id']));

        if (!isset($pedido->tiempoEstimado) || $horaEstimada > $pedido->tiempoEstimado) {
            $pedido->tiempoEstimado = $horaEstimada;
        }

        $idEstadoEnPreparacion = 2;
        $estado = EstadoPedido::GetEstadoPorId($idEstadoEnPreparacion);
        if (!isset($estado)) {
            throw new HttpBadRequestException($req, 'Estado pedido invalido'); 
        }

        $pedido->idEstado = $estado->id;
        $pedido->estado = $estado->estado;
        $pedido->ModificarPedido();
        $idTipoCerveza = 3;
        ItemPedido::SetEstadoItemsPorTipoProducto($pedido->id, $idEstadoEnPreparacion, $idTipoCerveza);
        
        $res->getBody()->write(json_encode(['mensaje' => "Se estan preparando las bebidas"]));

        return $res;
    }

    private static function TerminarPedido($idPedido) {
        $pedido = Pedido::GetPedido(intval($idPedido));
        $estaListoPedido = true;
        $idEstadoListoParaServir = 3;
        $estado = EstadoPedido::GetEstadoPorId($idEstadoListoParaServir);

        foreach ($pedido->items as $item) {
            if ($item->idEstado != $idEstadoListoParaServir) {
                $estaListoPedido = false;
                break;
            }
        }
        if ($estaListoPedido) {
            $pedido->idEstado = $estado->id;
            $pedido->estado = $estado->estado;
            $pedido->ModificarPedido();
        }
    }

    public function TerminarPedidoCervezas(Request $req, Response $res, array $args = []) {
        $parametros = $req->getParsedBody();
        $pedido = Pedido::GetPedido(intval($parametros['id']));

        if (!isset($pedido)) {
            throw new HttpBadRequestException($req, 'Pedido no existe'); 
        }
        $idEstadoListoParaServir = 3;
        $estado = EstadoPedido::GetEstadoPorId($idEstadoListoParaServir);
        if (!isset($estado)) {
            throw new HttpBadRequestException($req, 'Estado pedido invalido'); 
        }
        
        
        $idTipoCerveza = 3;
        ItemPedido::SetEstadoItemsPorTipoProducto($pedido->id, $idEstadoListoParaServir, $idTipoCerveza);

        self::TerminarPedido($pedido->id);
        
        $res->getBody()->write(json_encode(['mensaje' => "Se estan preparando las bebidas"]));

        return $res;
    }

    public function TerminarPedidoBebidas(Request $req, Response $res, array $args = []) {
        $parametros = $req->getParsedBody(); 
        $pedido = Pedido::GetPedido(intval($parametros['id']));

        if (!isset($pedido)) {
            throw new HttpBadRequestException($req, 'Pedido no existe'); 
        }
        $idEstadoListoParaServir = 3;
        $estado = EstadoPedido::GetEstadoPorId($idEstadoListoParaServir);
        if (!isset($estado)) {
            throw new HttpBadRequestException($req, 'Estado pedido invalido'); 
        }
        
        
        $idTipoBebida = 1;
        ItemPedido::SetEstadoItemsPorTipoProducto($pedido->id, $idEstadoListoParaServir, $idTipoBebida);

        self::TerminarPedido($pedido->id);
        
        $res->getBody()->write(json_encode(['mensaje' => "Se estan preparando las bebidas"]));

        return $res;
    }

    public function TerminarPedidoComidas(Request $req, Response $res, array $args = []) {
        $parametros = $req->getParsedBody();
        $pedido = Pedido::GetPedido(intval($parametros['id']));

        if (!isset($pedido)) {
            throw new HttpBadRequestException($req, 'Pedido no existe'); 
        }
        $idEstadoListoParaServir = 3;
        $estado = EstadoPedido::GetEstadoPorId($idEstadoListoParaServir);
        if (!isset($estado)) {
            throw new HttpBadRequestException($req, 'Estado pedido invalido'); 
        }
        
        
        $idTipoComida = 2;
        ItemPedido::SetEstadoItemsPorTipoProducto($pedido->id, $idEstadoListoParaServir, $idTipoComida);

        self::TerminarPedido($pedido->id);
        
        $res->getBody()->write(json_encode(['mensaje' => "Se estan preparando las bebidas"]));

        return $res;
    }

    
}

?>