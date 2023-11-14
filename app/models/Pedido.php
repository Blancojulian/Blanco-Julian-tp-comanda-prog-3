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
    public $tiempoEstimado;//DATE
    public $cancelado;
    
    public function __construct($codigo, $nombreCliente, $codigoMesa, $idEstado, $estado, $total = 0, $tiempoEstimado = null, $cancelado = false, $id = null, $items = []) {
        $this->id = $id;
        $this->codigo = $codigo;
        $this->nombreCliente = $nombreCliente;
        $this->codigoMesa = $codigoMesa;
        $this->estado = $estado;
        $this->idEstado = $idEstado;
        $this->items = $items;
        $this->total = $total;
        $this->tiempoEstimado = $tiempoEstimado;
        $this->cancelado = $cancelado;

    }

    public function CalcularTotal() {
        $total = array_reduce($this->items, fn($carry, $item) => $carry + ($item->precioUnitario * $item->cantidad), 0);
        $this->total = $total;
    }

    public function CrearPedido()
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'INSERT INTO pedidos (codigo,nombreCliente,codigoMesa,idEstado,total,cancelado,tiempoEstimado)VALUES(:codigo,:nombreCliente,:codigoMesa,:idEstado,:total,:cancelado,:tiempoEstimado)';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':codigo', $this->codigo, PDO::PARAM_STR);
        $consulta->bindValue(':nombreCliente', $this->nombreCliente, PDO::PARAM_STR);
        $consulta->bindValue(':codigoMesa', $this->codigoMesa, PDO::PARAM_STR);
        $consulta->bindValue(':idEstado', $this->idEstado, PDO::PARAM_INT);
        $consulta->bindValue(':total', $this->total, PDO::PARAM_STR);//float
        $consulta->bindValue(':cancelado', $this->cancelado, PDO::PARAM_BOOL);

        if (isset($this->tiempoEstimado)) {
            echo 'en crear pedio'. $this->tiempoEstimado->format("Y-m-d H:i:s");
            $consulta->bindValue(':tiempoEstimado', $this->tiempoEstimado->format("Y-m-d H:i:s"), PDO::PARAM_STR);
        } else {
            $consulta->bindValue(':tiempoEstimado', $this->tiempoEstimado, PDO::PARAM_STR);
        }
        $consulta->execute();
        return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }

    public function ModificarPedido()
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'UPDATE pedidos SET codigo=:codigo,nombreCliente = :nombreCliente,codigoMesa = :codigoMesa,idEstado = :idEstado,total = :total,cancelado = :cancelado,tiempoEstimado = :tiempoEstimado WHERE id = :id';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->bindValue(':codigo', $this->codigo, PDO::PARAM_STR);
        $consulta->bindValue(':nombreCliente', $this->nombreCliente, PDO::PARAM_STR);
        $consulta->bindValue(':codigoMesa', $this->codigoMesa, PDO::PARAM_STR);
        $consulta->bindValue(':idEstado', $this->idEstado, PDO::PARAM_INT);
        $consulta->bindValue(':total', $this->total, PDO::PARAM_STR);//float
        $consulta->bindValue(':cancelado', $this->cancelado, PDO::PARAM_BOOL);

        if (isset($this->tiempoEstimado) || $this->tiempoEstimado instanceof DateTime) {
            $consulta->bindValue(':tiempoEstimado', $this->tiempoEstimado->format("Y-m-d H:i:s"), PDO::PARAM_STR);
        } else {
            $consulta->bindValue(':tiempoEstimado', $this->tiempoEstimado, PDO::PARAM_STR);
        }
        $consulta->execute();
        return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }

    public static function GetPedidos()
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT p.*, e.estado FROM pedidos p LEFT JOIN estadosPedido e ON p.idEstado = e.id WHERE p.cancelado = 0';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->execute();

        $consulta->bindColumn('id', $id, PDO::PARAM_INT);
        $consulta->bindColumn('codigo', $codigo, PDO::PARAM_STR);
        $consulta->bindColumn('nombreCliente', $nombreCliente, PDO::PARAM_STR);
        $consulta->bindColumn('codigoMesa', $codigoMesa, PDO::PARAM_STR);
        $consulta->bindColumn('idEstado', $idEstado, PDO::PARAM_INT);
        $consulta->bindColumn('estado', $estado, PDO::PARAM_STR);
        $consulta->bindColumn('total', $total, PDO::PARAM_STR);//float
        $consulta->bindColumn('cancelado', $cancelado, PDO::PARAM_BOOL);
        $consulta->bindColumn('tiempoEstimado', $tiempoEstimado, PDO::PARAM_STR);

        $pedido = null;
        $pedidos = [];
        while ($fila = $consulta->fetch(PDO::FETCH_BOUND)) {
            $tiempoEstimado = isset($tiempoEstimado) ? new DateTime($tiempoEstimado) : $tiempoEstimado;
            $pedido = new Pedido($codigo, $nombreCliente, $codigoMesa, $idEstado, $estado, floatval($total), $tiempoEstimado, $cancelado, $id);
            array_push($pedidos, $pedido);
        }

        foreach ($pedidos as $p) {
            $p->items = ItemPedido::GetItems($p->id);
        }
        return $pedidos;
    }

    public static function GetPedido($idPedido)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT p.*, e.estado FROM pedidos p LEFT JOIN estadosPedido e ON p.idEstado = e.id WHERE p.id = :id AND p.cancelado = 0';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':id', $idPedido, PDO::PARAM_INT);
        $consulta->execute();
        
        $consulta->bindColumn('id', $id, PDO::PARAM_INT);
        $consulta->bindColumn('codigo', $codigo, PDO::PARAM_STR);
        $consulta->bindColumn('nombreCliente', $nombreCliente, PDO::PARAM_STR);
        $consulta->bindColumn('codigoMesa', $codigoMesa, PDO::PARAM_STR);
        $consulta->bindColumn('idEstado', $idEstado, PDO::PARAM_INT);
        $consulta->bindColumn('estado', $estado, PDO::PARAM_STR);
        $consulta->bindColumn('total', $total, PDO::PARAM_STR);//float
        $consulta->bindColumn('cancelado', $cancelado, PDO::PARAM_BOOL);
        $consulta->bindColumn('tiempoEstimado', $tiempoEstimado, PDO::PARAM_STR);

        $pedido = null;

        if ($consulta->fetch(PDO::FETCH_BOUND)) {
            $tiempoEstimado = isset($tiempoEstimado) ? new DateTime($tiempoEstimado) : $tiempoEstimado;
            $pedido = new Pedido($codigo, $nombreCliente, $codigoMesa, $idEstado, $estado, floatval($total), $tiempoEstimado, $cancelado, $id);
            $pedido->items = ItemPedido::GetItems($pedido->id);
        }
        
        return $pedido;
    }

    public static function GetPedidosPorIdEstado($idEstado)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT p.*, e.estado FROM pedidos p LEFT JOIN estadosPedido e ON p.idEstado = e.id WHERE p.idEstado = :idEstado AND p.cancelado = 0';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':idEstado', $idEstado, PDO::PARAM_INT);
        $consulta->execute();
        
        $consulta->bindColumn('id', $id, PDO::PARAM_INT);
        $consulta->bindColumn('codigo', $codigo, PDO::PARAM_STR);
        $consulta->bindColumn('nombreCliente', $nombreCliente, PDO::PARAM_STR);
        $consulta->bindColumn('codigoMesa', $codigoMesa, PDO::PARAM_STR);
        $consulta->bindColumn('idEstado', $idEstado, PDO::PARAM_INT);
        $consulta->bindColumn('estado', $estado, PDO::PARAM_STR);
        $consulta->bindColumn('total', $total, PDO::PARAM_STR);//float
        $consulta->bindColumn('cancelado', $cancelado, PDO::PARAM_BOOL);
        $consulta->bindColumn('tiempoEstimado', $tiempoEstimado, PDO::PARAM_STR);

        $pedido = null;
        $pedidos = [];
        while ($fila = $consulta->fetch(PDO::FETCH_BOUND)) {
            $tiempoEstimado = isset($tiempoEstimado) ? new DateTime($tiempoEstimado) : $tiempoEstimado;
            $pedido = new Pedido($codigo, $nombreCliente, $codigoMesa, $idEstado, $estado, floatval($total), $tiempoEstimado, $cancelado, $id);
            array_push($pedidos, $pedido);
        }

        foreach ($pedidos as $p) {
            $p->items = ItemPedido::GetItems($p->id);
        }
        return $pedidos;
    }
    private static function bindColumnPedidos($consulta) {
       
        $consulta->bindColumn('id', $id, PDO::PARAM_INT);
        $consulta->bindColumn('codigo', $codigo, PDO::PARAM_STR);
        $consulta->bindColumn('nombreCliente', $nombreCliente, PDO::PARAM_STR);
        $consulta->bindColumn('codigoMesa', $codigoMesa, PDO::PARAM_STR);
        $consulta->bindColumn('idEstado', $idEstado, PDO::PARAM_INT);
        $consulta->bindColumn('estado', $estado, PDO::PARAM_STR);
        $consulta->bindColumn('total', $total, PDO::PARAM_STR);//float
        $consulta->bindColumn('cancelado', $cancelado, PDO::PARAM_BOOL);
        $consulta->bindColumn('tiempoEstimado', $tiempoEstimado, PDO::PARAM_STR);

        $pedido = null;
        $pedidos = [];
        while ($fila = $consulta->fetch(PDO::FETCH_BOUND)) {
            $tiempoEstimado = isset($tiempoEstimado) ? new DateTime($tiempoEstimado) : $tiempoEstimado;
            $pedido = new Pedido($codigo, $nombreCliente, $codigoMesa, $idEstado, $estado, floatval($total), $tiempoEstimado, $cancelado, $id);
            array_push($pedidos, $pedido);
        }

        foreach ($pedidos as $p) {
            $p->items = ItemPedido::GetItems($p->id);
        }
        return $pedidos;
    }

    public static function GetPedidosPorTipoProducto($idTipoProducto, $idEstadoPedido)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT p.*, e.estado FROM pedidos p LEFT JOIN estadosPedido e ON p.idEstado = e.id 
        LEFT JOIN itemsPedido i ON i.idPedido = p.id LEFT JOIN productos pro ON i.idProducto = pro.id 
        WHERE p.cancelado = 0 AND pro.idTipoProducto = :idTipoProducto AND p.idEstado = :idEstadoPedido';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':idTipoProducto', $idTipoProducto, PDO::PARAM_INT);
        $consulta->bindValue(':idEstadoPedido', $idEstadoPedido, PDO::PARAM_INT);
        $consulta->execute();

        return self::bindColumnPedidos($consulta);
    }

    public function jsonSerialize(){
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'nombreCliente' => $this->nombreCliente,
            'codigoMesa' => $this->codigoMesa,
            'estado' => $this->estado,
            'total' => $this->total,
            'tiempoEstimado' => $this->tiempoEstimado,
            'items' => $this->items
        ];
    }
}

?>