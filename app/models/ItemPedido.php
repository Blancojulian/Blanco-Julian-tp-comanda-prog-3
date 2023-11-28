<?php

require_once './db/AccesoDatos.php';
require_once './utils/utils.php';

class ItemPedido implements JsonSerializable
{
    public $idProducto;
    public $producto;
    public $precioUnitario;
    public $idEstado;
    public $estado;
    public $idEmpleado;
    
    public function __construct($idProducto, $producto, $precioUnitario, $idEstado, $estado, $idEmpleado = null, $id = null) {
        
        $this->idProducto = $idProducto;
        $this->producto = $producto;
        $this->precioUnitario = $precioUnitario;
        $this->idEstado = $idEstado;
        $this->estado = $estado;
        $this->idEmpleado = $idEmpleado;
        $this->id = $id;

    }

    
    //no se va a usar siempre se va insertar un array de pedidos y para modificar se eliminan los anteriores y agregan los nuevos
    //por ahora
    public static function CrearItem($idPedido, ItemPedido $item)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'INSERT INTO itemsPedido (idPedido,idProducto,precioUnitario,idEstado)VALUES(:idPedido,:idProducto,:precioUnitario,:idEstado)';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':idPedido', $idPedido, PDO::PARAM_INT);
        $consulta->bindValue(':idProducto', $item->idProducto, PDO::PARAM_INT);
        $consulta->bindValue(':precioUnitario', $item->precioUnitario, PDO::PARAM_STR);//ver sino tirar error pq es float
        $consulta->bindValue(':idEstado', $item->idEstado, PDO::PARAM_INT);
        return $consulta->execute();
    }

    public static function CrearItems($idPedido, array $items)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'INSERT INTO itemsPedido (idPedido,idProducto,precioUnitario,idEstado) VALUES (:idPedido,:idProducto,:precioUnitario,:idEstado)';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        
        foreach ($items as $item) {
            $consulta->bindValue(':idPedido', $idPedido, PDO::PARAM_INT);
            $consulta->bindValue(':idProducto', $item->idProducto, PDO::PARAM_INT);
            $consulta->bindValue(':precioUnitario', $item->precioUnitario, PDO::PARAM_STR);//ver sino tirar error pq es float
            $consulta->bindValue(':idEstado', $item->idEstado, PDO::PARAM_INT);
            $consulta->execute();
        }
    }

    public static function GetItems($idPedidoSolicitado)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT i.*, p.nombre AS producto, e.estado AS nombreEstado FROM itemsPedido i LEFT JOIN productos p ON i.idProducto = p.id LEFT JOIN estadosPedido e ON i.idEstado = e.id WHERE i.idPedido = :idPedido';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':idPedido', $idPedidoSolicitado, PDO::PARAM_INT);
        $consulta->execute();

        //$consulta->bindColumn('idPedido', $idPedido, PDO::PARAM_INT);
        $consulta->bindColumn('id', $id, PDO::PARAM_INT);
        $consulta->bindColumn('idProducto', $idProducto, PDO::PARAM_INT);
        $consulta->bindColumn('producto', $producto, PDO::PARAM_STR);
        $consulta->bindColumn('precioUnitario', $precioUnitario, PDO::PARAM_STR);//parsear a float
        $consulta->bindColumn('idEstado', $idEstado, PDO::PARAM_INT);
        $consulta->bindColumn('nombreEstado', $estado, PDO::PARAM_STR);
        $consulta->bindColumn('idEmpleado', $idEmpleado, PDO::PARAM_INT);
        $item = null;
        $items = [];
        while ($fila = $consulta->fetch(PDO::FETCH_BOUND)) {
            $item = new ItemPedido($idProducto, $producto, floatval($precioUnitario), $idEstado, $estado, $idEmpleado, $id);
            array_push($items, $item);
        }
        return $items;
    }

    public static function GetItem($idItem)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT i.*, p.nombre AS producto, e.estado AS nombreEstado FROM itemsPedido i LEFT JOIN productos p ON i.idProducto = p.id LEFT JOIN estadosPedido e ON i.idEstado = e.id WHERE i.id = :id';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':id', $idItem, PDO::PARAM_INT);
        $consulta->execute();

        $consulta->bindColumn('id', $id, PDO::PARAM_INT);
        $consulta->bindColumn('idProducto', $idProducto, PDO::PARAM_INT);
        $consulta->bindColumn('producto', $producto, PDO::PARAM_STR);
        $consulta->bindColumn('precioUnitario', $precioUnitario, PDO::PARAM_STR);//parsear a float
        $consulta->bindColumn('idEstado', $idEstado, PDO::PARAM_INT);
        $consulta->bindColumn('nombreEstado', $estado, PDO::PARAM_STR);
        $consulta->bindColumn('idEmpleado', $idEmpleado, PDO::PARAM_INT);
        $item = null;
        
        if ($consulta->fetch(PDO::FETCH_BOUND)) {
            $item = new ItemPedido($idProducto, $producto, floatval($precioUnitario), $idEstado, $estado, $idEmpleado, $id);
        }
        return $item;
    }

    public static function GetItemPorTipoProducto($idTipoProducto, $idEstado = 1)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT TOP 1 i.*, p.nombre AS producto, e.estado AS nombreEstado FROM itemsPedido i LEFT JOIN productos p ON i.idProducto = p.id LEFT JOIN estadosPedido e ON i.idEstado = e.id WHERE p.idTipoProducto = :idTipoProducto';
        
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':idTipoProducto', $idTipoProducto, PDO::PARAM_INT);
        $consulta->bindValue(':idEstado', $idEstado, PDO::PARAM_INT);
        $consulta->execute();

        $consulta->bindColumn('id', $id, PDO::PARAM_INT);
        $consulta->bindColumn('idProducto', $idProducto, PDO::PARAM_INT);
        $consulta->bindColumn('producto', $producto, PDO::PARAM_STR);
        $consulta->bindColumn('precioUnitario', $precioUnitario, PDO::PARAM_STR);//parsear a float
        $consulta->bindColumn('idEstado', $idEstado, PDO::PARAM_INT);
        $consulta->bindColumn('nombreEstado', $estado, PDO::PARAM_STR);
        $consulta->bindColumn('idEmpleado', $idEmpleado, PDO::PARAM_INT);
        $item = null;
        
        if ($consulta->fetch(PDO::FETCH_BOUND)) {
            $item = new ItemPedido($idProducto, $producto, floatval($precioUnitario), $idEstado, $estado, $idEmpleado, $id);
        }
        return $item;
    }

    //ver si usar o enviar directamente el idProducto desde el postman
    public static function ConvertirAArrayItems(array $data = []) {
        $item = null;
        $items = [];
        $producto = null;
        $idEstado = 1;
        $estado = 'pendiente';

        foreach ($data as $i) {
            $producto = Producto::GetProducto($i['idProducto']);
            if (!isset($producto)) {
                throw new Exception("No existe producto id ". $i['idProducto']);
                
            }
            $idEmpleado = isset($i['idEmpleado']) ? $i['idEmpleado'] : null;
            $id = isset($i['id']) ? $i['id'] : null;

            $item = new ItemPedido($producto->id, $producto->nombre, $producto->precio, $idEstado, $estado, $idEmpleado, $id);
            array_push($items, $item);
        }
        return $items;
    }

    public static function EliminarItems($idPedido)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'DELETE FROM itemsPedido WHERE idPedido = :idPedido';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':idPedido', $idPedido, PDO::PARAM_INT);
        return $consulta->execute();
    }
    public static function EliminarItem($idItem)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'DELETE FROM itemsPedido WHERE id = :id';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':id', $idItem, PDO::PARAM_INT);
        return $consulta->execute();
    }
    
    public static function SetEstadoItemsPorTipoProducto($idPedido, $idEstado, $idTipoProducto)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'DELETE FROM itemsPedido WHERE idPedido = :idPedido';
        $query = 'UPDATE itemsPedido i LEFT JOIN productos p ON i.idProducto = p.id SET i.idEstado=:idEstado WHERE i.idPedido = :idPedido AND p.idTipoProducto=:idTipoProducto';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':idPedido', $idPedido, PDO::PARAM_INT);
        $consulta->bindValue(':idEstado', $idEstado, PDO::PARAM_INT);
        $consulta->bindValue(':idTipoProducto', $idTipoProducto, PDO::PARAM_INT);

        return $consulta->execute();
    }
    public function SetEstadoItem($id, $idEstado)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'UPDATE itemsPedido SET idEstado = :idEstado WHERE id = :id';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->bindValue(':idEstado', $idEstado, PDO::PARAM_INT);

        return $consulta->execute();
    }
    public function jsonSerialize(){
        return [
            'id' => $this->id,
            'idProducto' => $this->idProducto,
            'producto' => $this->producto,
            'cantidad' => $this->cantidad,
            'precioUnitario' => $this->precioUnitario,
            'estado' => $this->estado,
            'idEmpleado' => $this->idEmpleado


        ];
    }
}

?>