<?php

require_once './db/AccesoDatos.php';
require_once './models/ItemPedido.php';
require_once './utils/utils.php';

class Pedido implements JsonSerializable
{
    public $id;
    public $codigo;
    public $nombreCliente;
    public $codigoMesa;
    public $estado;
    public $idEstado;
    public $items;
    public $total;
    public $tiempoEstimado;
    public $fechaAlta;
    public $fechaModificacion;
    public $fechaCancelacion;
    public $imagen;
    
    public function __construct($codigo, $nombreCliente, $codigoMesa, $idEstado, $estado, $total = 0, $tiempoEstimado = null, 
    $fechaAlta = null, $fechaModificacion = null, $fechaCancelacion = null, $id = null, $imagen = null, $items = []) {
        $this->id = $id;
        $this->codigo = $codigo;
        $this->nombreCliente = $nombreCliente;
        $this->codigoMesa = $codigoMesa;
        $this->estado = $estado;
        $this->idEstado = $idEstado;
        $this->items = $items;
        $this->total = $total;
        $this->tiempoEstimado = isset($tiempoEstimado) ? new DateTime($tiempoEstimado) : $tiempoEstimado;
        $this->fechaAlta = isset($fechaAlta) ? new DateTime($fechaAlta) : null;//date("Y-m-d H:i:s", strtotime($date))
        $this->fechaModificacion = isset($fechaModificacion) ? new DateTime($fechaModificacion) : null;
        $this->fechaCancelacion = isset($fechaCancelacion) ? new DateTime($fechaCancelacion) : null;
        $this->imagen = $imagen;
    }

    public function CalcularTotal() {
        $total = array_reduce($this->items, fn($carry, $item) => $carry + ($item->precioUnitario * $item->cantidad), 0);
        $this->total = $total;
    }

    private static function EjecutarQueryInsertar($consulta, $pedido) {
        
        
        $consulta->bindValue(':codigo', $pedido->codigo, PDO::PARAM_STR);
        $consulta->bindValue(':nombreCliente', $pedido->nombreCliente, PDO::PARAM_STR);
        $consulta->bindValue(':codigoMesa', $pedido->codigoMesa, PDO::PARAM_STR);
        $consulta->bindValue(':idEstado', $pedido->idEstado, PDO::PARAM_INT);
        $consulta->bindValue(':total', $pedido->total, PDO::PARAM_STR);//float
        $consulta->bindValue(':cancelado', $pedido->cancelado, PDO::PARAM_BOOL);
        $consulta->bindValue(':tiempoEstimado', $tiempoEstimado, PDO::PARAM_STR);

        $consulta->execute();
    }

    public function CrearPedido()
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'INSERT INTO pedidos (codigo,nombreCliente,codigoMesa,idEstado,total,tiempoEstimado,fechaAlta)VALUES(:codigo,:nombreCliente,:codigoMesa,:idEstado,:total,:tiempoEstimado,:fechaAlta)';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);

        $fechaAlta = date('Y/m/d H:i:s', strtotime("now"));
        $this->fechaAlta = new DateTime($fechaAlta);
        $consulta->bindValue(':fechaAlta', $fechaAlta, PDO::PARAM_STR);
        self::EjecutarQueryInsertar($consulta, $this);

        $this->id = $objetoAccesoDatos->RetornarUltimoIdInsertado();;
        return $this->id;
    }

    public function ModificarPedido()
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'UPDATE pedidos SET codigo=:codigo,nombreCliente = :nombreCliente,codigoMesa = :codigoMesa,idEstado = :idEstado,total = :total,fechaModificacion = :fechaModificacion,tiempoEstimado = :tiempoEstimado WHERE id = :id';
        $consulta = $objetoAccesoDatos->RetornarConsulta($query);

        $fechaModificacion = date('Y/m/d H:i:s', strtotime("now"));
        $this->fechaModificacion = new DateTime($fechaModificacion);
        $consulta->bindValue(':fechaModificacion', $fechaModificacion, PDO::PARAM_STR);
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        self::EjecutarQueryInsertar($consulta, $this);
        
        return $objetoAccesoDatos->RetornarUltimoIdInsertado();
    }

    public function SetArchivoImagen($imagen) {
        $objetoAccesoDatos = AccesoDatos::getObjetoAcceso();
        $query = 'UPDATE pedidos SET imagen = :imagen WHERE id = :id';

        $consulta = $objetoAccesoDatos->RetornarConsulta($query);
        $consulta->bindValue(':imagen', $imagen, PDO::PARAM_STR);
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->execute();

        return $objetoAccesoDatos->RetornarUltimoIdInsertado();
    }

    private static function BindColumns($consulta) {
        $auxPedido = new stdClass();
        $consulta->bindColumn('id', $auxPedido->id, PDO::PARAM_INT);
        $consulta->bindColumn('codigo', $auxPedido->codigo, PDO::PARAM_STR);
        $consulta->bindColumn('nombreCliente', $auxPedido->nombreCliente, PDO::PARAM_STR);
        $consulta->bindColumn('codigoMesa', $auxPedido->codigoMesa, PDO::PARAM_STR);
        $consulta->bindColumn('idEstado', $auxPedido->idEstado, PDO::PARAM_INT);
        $consulta->bindColumn('estado', $auxPedido->estado, PDO::PARAM_STR);
        $consulta->bindColumn('total', $auxPedido->total, PDO::PARAM_STR);//float
        $consulta->bindColumn('tiempoEstimado', $auxPedido->tiempoEstimado, PDO::PARAM_STR);
        $consulta->bindColumn('fechaAlta', $auxPedido->fechaAlta, PDO::PARAM_STR);
        $consulta->bindColumn('fechaModificacion', $auxPedido->fechaModificacion, PDO::PARAM_STR);
        $consulta->bindColumn('fechaBaja', $auxPedido->fechaBaja, PDO::PARAM_STR);
        $consulta->bindColumn('imagen', $auxPedido->imagen, PDO::PARAM_STR);
        return $auxPedido;
    }

    private static function FetchQueryGetAll($consulta) {
        
        $p = self::BindColumns($consulta);

        $pedido = null;
        $pedidos = [];
        while ($fila = $consulta->fetch(PDO::FETCH_BOUND)) {
            $pedido = new Pedido($p->codigo, $p->nombreCliente, $p->codigoMesa, $p->idEstado, $p->estado, floatval($p->total), 
            $p->tiempoEstimado, $imagen, $p->fechaAlta, $p->fechaModificacion, $p->fechaCancelacion, $p->id);
            $pedido->items = ItemPedido::GetItems($pedido->id);
            array_push($pedidos, $pedido);
        }

        return $pedidos;
    }

    private static function FetchQueryGet($consulta) {

        $p = self::BindColumns($consulta);

        $pedido = null;
        if ($consulta->fetch(PDO::FETCH_BOUND)) {
            $pedido = new Pedido($p->codigo, $p->nombreCliente, $p->codigoMesa, $p->idEstado, $p->estado, floatval($p->total), 
            $p->tiempoEstimado, $imagen, $p->fechaAlta, $p->fechaModificacion, $p->fechaCancelacion, $p->id);
            $pedido->items = ItemPedido::GetItems($pedido->id);
        }

        return $pedido;
    }

    public static function GetPedidos()
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT p.*, e.estado FROM pedidos p LEFT JOIN estadosPedido e ON p.idEstado = e.id WHERE p.cancelado = 0';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->execute();

        return self::FetchQueryGetAll($consulta);
    }

    public static function GetPedido($idPedido)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT p.*, e.estado FROM pedidos p LEFT JOIN estadosPedido e ON p.idEstado = e.id WHERE p.id = :id AND p.cancelado = 0';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':id', $idPedido, PDO::PARAM_INT);
        $consulta->execute();
        
        return self::FetchQueryGet($consulta);
    }

    public static function GetPedidosPorIdEstado($idEstado)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT p.*, e.estado FROM pedidos p LEFT JOIN estadosPedido e ON p.idEstado = e.id WHERE p.idEstado = :idEstado AND p.cancelado = 0';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':idEstado', $idEstado, PDO::PARAM_INT);
        $consulta->execute();
        
        return self::FetchQueryGetAll($consulta);
    }

    public static function GetPedidosPorTipoProducto($idTipoProducto, $idEstadoPedido = null)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT p.*, e.estado FROM pedidos p LEFT JOIN estadosPedido e ON p.idEstado = e.id 
        LEFT JOIN itemsPedido i ON i.idPedido = p.id LEFT JOIN productos pro ON i.idProducto = pro.id 
        WHERE p.cancelado = 0 AND pro.idTipoProducto = :idTipoProducto';
        if (isset($idEstadoPedido)) {
            $query .= ' AND p.idEstado = :idEstadoPedido';
        }
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':idTipoProducto', $idTipoProducto, PDO::PARAM_INT);
        $consulta->bindValue(':idEstadoPedido', $idEstadoPedido, PDO::PARAM_INT);
        $consulta->execute();

        return self::FetchQueryGetAll($consulta);
    }

    public function jsonSerialize(){
        $tiempoEstimado = isset($this->tiempoEstimado) ? $this->tiempoEstimado->format('Y-m-d H:i:s') : null;
        
        $json = [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'nombreCliente' => $this->nombreCliente,
            'codigoMesa' => $this->codigoMesa,
            'estado' => $this->estado,
            'total' => $this->total,
            'tiempoEstimado' => $tiempoEstimado,
            'items' => $this->items
        ];
        
        if (isset($this->fechaCancelacion)) {
            $json['fechaCancelacion'] = $this->fechaCancelacion->format('Y-m-d H:i:s');
        }
        return $json;
    }
}

?>