<?php

require_once './db/AccesoDatos.php';

class Producto implements JsonSerializable
{
    public $id;
    public $nombre;
    public $precio;
    public $stock;
    public $tipoProducto;
    public $idTipoProducto;
    public $baja;
    
    public function __construct($nombre, $precio, $stock, $idTipoProducto, $tipoProducto, $baja = false, $id = null) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->precio = $precio;
        $this->stock = $stock;
        $this->idTipoProducto = $idTipoProducto;
        $this->tipoProducto = $tipoProducto;
        $this->baja = $baja;
        

    }

    

    public function CrearProducto()
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'INSERT INTO productos (nombre,precio,stock,idTipoProducto,baja)values(:nombre,:precio,:stock,:idTipoProducto,:baja)';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':precio', $this->precio, PDO::PARAM_STR);
        $consulta->bindValue(':stock', $this->stock, PDO::PARAM_INT);
        $consulta->bindValue(':idTipoProducto', $this->idTipoProducto, PDO::PARAM_INT);
        $consulta->bindValue(':baja', $this->baja, PDO::PARAM_BOOL);

        $consulta->execute();
        return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }
    public function ModificarProducto()
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'UPDATE productos SET nombre = :nombre, precio = :precio, stock = :stock, idTipoProducto =:idTipoProducto, baja = :baja WHERE id = :id';
        
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':precio', $this->precio, PDO::PARAM_STR);
        $consulta->bindValue(':stock', $this->stock, PDO::PARAM_INT);
        $consulta->bindValue(':idTipoProducto', $this->idTipoProducto, PDO::PARAM_INT);
        $consulta->bindValue(':baja', $this->baja, PDO::PARAM_BOOL);
        $consulta->execute();
        return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }

    public static function GetProductos()
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT p.*, t.nombre AS tipo FROM productos p LEFT JOIN tiposDeProducto t ON p.idTipoProducto = t.id WHERE p.baja = 0';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->execute();

        $consulta->bindColumn('id', $id, PDO::PARAM_INT);
        $consulta->bindColumn('nombre', $nombre, PDO::PARAM_STR);
        $consulta->bindColumn('precio', $precio, PDO::PARAM_STR);//parsear a float
        $consulta->bindColumn('stock', $stock, PDO::PARAM_INT);
        $consulta->bindColumn('idTipoProducto', $idTipoProducto, PDO::PARAM_INT);
        $consulta->bindColumn('tipo', $tipo, PDO::PARAM_STR);
        $consulta->bindColumn('baja', $baja, PDO::PARAM_BOOL);

        $producto = null;
        $productos = [];
        while ($fila = $consulta->fetch(PDO::FETCH_BOUND)) {
            $producto = new Producto($nombre, floatval($precio), $stock, $idTipoProducto, $tipo, $baja, $id);
            array_push($productos, $producto);
        }
        return $productos;
    }

    public static function GetProducto($id)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT p.*, t.nombre AS tipo FROM productos p LEFT JOIN tiposDeProducto t ON p.idTipoProducto = t.id WHERE p.id = :id AND p.baja = 0';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
        
        $consulta->bindColumn('id', $id, PDO::PARAM_INT);
        $consulta->bindColumn('nombre', $nombre, PDO::PARAM_STR);
        $consulta->bindColumn('precio', $precio, PDO::PARAM_STR);//parsear a float
        $consulta->bindColumn('stock', $stock, PDO::PARAM_INT);
        $consulta->bindColumn('idTipoProducto', $idTipoProducto, PDO::PARAM_INT);
        $consulta->bindColumn('tipo', $tipo, PDO::PARAM_STR);
        $consulta->bindColumn('baja', $baja, PDO::PARAM_BOOL);

        $producto = null;

        if ($consulta->fetch(PDO::FETCH_BOUND)) {
            $producto = new Producto($nombre, floatval($precio), $stock, $idTipoProducto, $tipo, $baja, $id);
        }
        
        return $producto;
    }

    public static function GetProductoPorNombre($strNombre)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT p.*, t.nombre AS tipo FROM productos p LEFT JOIN tiposDeProducto t ON p.idTipoProducto = t.id WHERE p.nombre = :nombre AND p.baja = 0';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':nombre', $strNombre, PDO::PARAM_STR);
        $consulta->execute();
        
        $consulta->bindColumn('id', $id, PDO::PARAM_INT);
        $consulta->bindColumn('nombre', $nombre, PDO::PARAM_STR);
        $consulta->bindColumn('precio', $precio, PDO::PARAM_STR);//parsear a float
        $consulta->bindColumn('stock', $stock, PDO::PARAM_INT);
        $consulta->bindColumn('idTipoProducto', $idTipoProducto, PDO::PARAM_INT);
        $consulta->bindColumn('tipo', $tipo, PDO::PARAM_STR);
        $consulta->bindColumn('baja', $baja, PDO::PARAM_BOOL);

        $producto = null;

        if ($consulta->fetch(PDO::FETCH_BOUND)) {
            $producto = new Producto($nombre, floatval($precio), $stock, $idTipoProducto, $tipo, $baja, $id);
        }
        
        return $producto;
    }

    public static function GetProductosPorIdTipo($idTipo)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT p.*, t.nombre AS tipo FROM productos p LEFT JOIN tiposDeProducto t ON p.idTipoProducto = t.id WHERE t.id = :tipo AND p.baja = 0';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':tipo', $idTipo, PDO::PARAM_INT);
        $consulta->execute();

        $consulta->bindColumn('id', $id, PDO::PARAM_INT);
        $consulta->bindColumn('nombre', $nombre, PDO::PARAM_STR);
        $consulta->bindColumn('precio', $precio, PDO::PARAM_STR);//parsear a float
        $consulta->bindColumn('stock', $stock, PDO::PARAM_INT);
        $consulta->bindColumn('idTipoProducto', $idTipoProducto, PDO::PARAM_INT);
        $consulta->bindColumn('tipo', $tipo, PDO::PARAM_STR);
        $consulta->bindColumn('baja', $baja, PDO::PARAM_BOOL);

        $producto = null;
        $productos = [];
        while ($fila = $consulta->fetch(PDO::FETCH_BOUND)) {
            $producto = new Producto($nombre, floatval($precio), $stock, $idTipoProducto, $tipo, $baja, $id);
            array_push($productos, $producto);
        }
        return $productos;
    }

    public static function GetProductosPorStrTipo($strTipo)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT p.*, t.nombre AS tipo FROM productos p LEFT JOIN tiposDeProducto t ON p.idTipoProducto = t.id WHERE t.nombre = :tipo AND p.baja = 0';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':tipo', $strTipo, PDO::PARAM_STR);
        $consulta->execute();

        $consulta->bindColumn('id', $id, PDO::PARAM_INT);
        $consulta->bindColumn('nombre', $nombre, PDO::PARAM_STR);
        $consulta->bindColumn('precio', $precio, PDO::PARAM_STR);//parsear a float
        $consulta->bindColumn('stock', $stock, PDO::PARAM_INT);
        $consulta->bindColumn('idTipoProducto', $idTipoProducto, PDO::PARAM_INT);
        $consulta->bindColumn('tipo', $tipo, PDO::PARAM_STR);
        $consulta->bindColumn('baja', $baja, PDO::PARAM_BOOL);

        $producto = null;
        $productos = [];
        while ($fila = $consulta->fetch(PDO::FETCH_BOUND)) {
            $producto = new Producto($nombre, floatval($precio), $stock, $idTipoProducto, $tipo, $baja, $id);
            array_push($productos, $producto);
        }
        return $productos;
    }

    public function jsonSerialize(){
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'precio' => $this->precio,
            'stock' => $this->stock,
            'tipoProducto' => $this->tipoProducto
        ];
    }
}

?>