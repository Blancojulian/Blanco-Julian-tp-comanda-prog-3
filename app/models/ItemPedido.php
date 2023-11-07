<?php

require_once './db/AccesoDatos.php';
require_once './utils/utils.php';

class ItemPedido implements JsonSerializable
{
    public $idPedido;
    public $idProducto;
    public $producto;
    public $cantidad;
    public $precioUnitario;
    
    public function __construct($idPedido, $idProducto, $producto, $cantidad, $precioUnitario) {
        $this->idPedido = $idPedido;
        $this->idProducto = $idProducto;
        $this->producto = $producto;
        $this->cantidad = $cantidad;
        $this->precioUnitario = $precioUnitario;

    }

    
    //no se va a usar siempre se va insertar un array de pedidos y para modificar se eliminan los anteriores y agregan los nuevos
    //por ahora
    public static function CrearItem(ItemPedido $item)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'INSERT INTO itemsPedido (idPedido,idProducto,cantidad,precioUnitario)VALUES(:idPedido,:idProducto,:cantidad,:precioUnitario)';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':idPedido', $item->idPedido, PDO::PARAM_INT);
        $consulta->bindValue(':idProducto', $item->idProducto, PDO::PARAM_INT);
        $consulta->bindValue(':cantidad', $item->cantidad, PDO::PARAM_INT);
        $consulta->bindValue(':precioUnitario', $item->precioUnitario, PDO::PARAM_STR);//ver sino tirar error pq es float
        return $consulta->execute();
    }

    public static function CrearItems(array $items)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'INSERT INTO itemsPedido (idPedido,idProducto,cantidad,precioUnitario) VALUES (:idPedido,:idProducto,:cantidad,:precioUnitario)';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $objetoAccesoDato->beginTransaction();
        foreach ($items as $item) {
            $consulta->bindValue(':idPedido', $item->idPedido, PDO::PARAM_INT);
            $consulta->bindValue(':idProducto', $item->idProducto, PDO::PARAM_INT);
            $consulta->bindValue(':cantidad', $item->cantidad, PDO::PARAM_INT);
            $consulta->bindValue(':precioUnitario', $item->precioUnitario, PDO::PARAM_STR);//ver sino tirar error pq es float
            $consulta->execute();
        }
        return $objetoAccesoDato->commit();
        //return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }

    public static function GetItems($idPedidoSolicitado)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT i.*, p.nombre AS producto FROM itemsPedido i LEFT JOIN productos p ON i.idProducto = p.id WHERE i.idPedido = :idPedido';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':idPedido', $idPedidoSolicitado, PDO::PARAM_INT);
        $consulta->execute();

        $consulta->bindColumn('idPedido', $idPedido, PDO::PARAM_INT);
        $consulta->bindColumn('idProducto', $idProducto, PDO::PARAM_INT);
        $consulta->bindColumn('producto', $producto, PDO::PARAM_STR);
        $consulta->bindColumn('cantidad', $cantidad, PDO::PARAM_INT);
        $consulta->bindColumn('precioUnitario', $precioUnitario, PDO::PARAM_STR);//parsear a float

        $item = null;
        $items = [];
        while ($fila = $consulta->fetch(PDO::FETCH_BOUND)) {
            $item = new ItemPedido($idPedido, $idProducto, $producto, $cantidad, floatval($precioUnitario));
            array_push($items, $item);
        }
        return $items;
    }
    //ver si usar o enviar directamente el idProducto desde el postman
    public static function ConvertirAArrayItems(array $data = []) {
        $item = null;
        $items = [];
        $producto = null;

        foreach ($data as $i) {
            $producto = Producto::GetProductoPorNombre($i['producto']);
            if (!isset($producto)) {
                throw new Exception("No existe producto ". $i['producto']);
                
            }
            $item = new ItemPedido($i['idPedido'], $producto->id, $producto->nombre, $i['cantidad'], floatval($i['precioUnitario']));
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
    
    
    public function jsonSerialize(){
        return [
            'producto' => $this->producto,
            'cantidad' => $this->cantidad,
            'precioUnitario' => $this->precioUnitario
        ];
    }
}

?>