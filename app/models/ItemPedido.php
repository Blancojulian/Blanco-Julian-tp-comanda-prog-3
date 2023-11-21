<?php

require_once './db/AccesoDatos.php';
require_once './utils/utils.php';

class ItemPedido implements JsonSerializable
{
    public $idProducto;
    public $producto;
    public $cantidad;
    public $precioUnitario;
    public $idEstado;
    public $estado;
    
    public function __construct($idProducto, $producto, $cantidad, $precioUnitario, $idEstado, $estado) {
        
        $this->idProducto = $idProducto;
        $this->producto = $producto;
        $this->cantidad = $cantidad;
        $this->precioUnitario = $precioUnitario;
        $this->idEstado = $idEstado;
        $this->estado = $estado;

    }

    
    //no se va a usar siempre se va insertar un array de pedidos y para modificar se eliminan los anteriores y agregan los nuevos
    //por ahora
    public static function CrearItem($idPedido, ItemPedido $item)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'INSERT INTO itemsPedido (idPedido,idProducto,cantidad,precioUnitario,idEstado)VALUES(:idPedido,:idProducto,:cantidad,:precioUnitario,:idEstado)';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':idPedido', $idPedido, PDO::PARAM_INT);
        $consulta->bindValue(':idProducto', $item->idProducto, PDO::PARAM_INT);
        $consulta->bindValue(':cantidad', $item->cantidad, PDO::PARAM_INT);
        $consulta->bindValue(':precioUnitario', $item->precioUnitario, PDO::PARAM_STR);//ver sino tirar error pq es float
        $consulta->bindValue(':idEstado', $item->idEstado, PDO::PARAM_INT);
        return $consulta->execute();
    }

    public static function CrearItems($idPedido, array $items)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'INSERT INTO itemsPedido (idPedido,idProducto,cantidad,precioUnitario,idEstado) VALUES (:idPedido,:idProducto,:cantidad,:precioUnitario,:idEstado)';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        //echo 'hola';
        //var_dump($items);
        //$objetoAccesoDato->beginTransaction();
        foreach ($items as $item) {
            $consulta->bindValue(':idPedido', $idPedido, PDO::PARAM_INT);
            $consulta->bindValue(':idProducto', $item->idProducto, PDO::PARAM_INT);
            $consulta->bindValue(':cantidad', $item->cantidad, PDO::PARAM_INT);
            $consulta->bindValue(':precioUnitario', $item->precioUnitario, PDO::PARAM_STR);//ver sino tirar error pq es float
            $consulta->bindValue(':idEstado', $item->idEstado, PDO::PARAM_INT);
            $consulta->execute();
        }
        //return $objetoAccesoDato->commit();
        //return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }

    public static function GetItems($idPedidoSolicitado)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT i.*, p.nombre AS producto, e.estado AS nombreEstado FROM itemsPedido i LEFT JOIN productos p ON i.idProducto = p.id LEFT JOIN estadosPedido e ON i.idEstado = e.id WHERE i.idPedido = :idPedido';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':idPedido', $idPedidoSolicitado, PDO::PARAM_INT);
        $consulta->execute();

        $consulta->bindColumn('idPedido', $idPedido, PDO::PARAM_INT);
        $consulta->bindColumn('idProducto', $idProducto, PDO::PARAM_INT);
        $consulta->bindColumn('producto', $producto, PDO::PARAM_STR);
        $consulta->bindColumn('cantidad', $cantidad, PDO::PARAM_INT);
        $consulta->bindColumn('precioUnitario', $precioUnitario, PDO::PARAM_STR);//parsear a float
        $consulta->bindColumn('idEstado', $idEstado, PDO::PARAM_INT);
        $consulta->bindColumn('nombreEstado', $estado, PDO::PARAM_STR);
        $item = null;
        $items = [];
        while ($fila = $consulta->fetch(PDO::FETCH_BOUND)) {
            $item = new ItemPedido($idProducto, $producto, $cantidad, floatval($precioUnitario), $idEstado, $estado);
            array_push($items, $item);
        }
        return $items;
    }
    //ver si usar o enviar directamente el idProducto desde el postman
    public static function ConvertirAArrayItems(array $data = []) {
        $item = null;
        $items = [];
        $producto = null;
        $idEstado = 1;
        $estado = 'pendiente';

        foreach ($data as $i) {
            $producto = Producto::GetProducto($i['id']);
            if (!isset($producto)) {
                throw new Exception("No existe producto id ". $i['producto']);
                
            }
            $item = new ItemPedido($producto->id, $producto->nombre, $i['cantidad'], $producto->precio, $idEstado, $estado);
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
    
    public function jsonSerialize(){
        return [
            'idProducto' => $this->idProducto,
            'producto' => $this->producto,
            'cantidad' => $this->cantidad,
            'precioUnitario' => $this->precioUnitario,
            'estado' => $this->estado

        ];
    }
}

?>