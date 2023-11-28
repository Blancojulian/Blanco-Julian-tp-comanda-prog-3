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
    public $fechaAlta;
    public $fechaModificacion;
    public $fechaBaja;
    
    public function __construct($nombre, $precio, $stock, $idTipoProducto, $tipoProducto, $fechaAlta = null, $fechaModificacion = null, $fechaBaja = null, $id = null) {
        $this->id = intval($id);
        $this->nombre = $nombre;
        $this->precio = floatval($precio);
        $this->stock = intval($stock);
        $this->idTipoProducto = intval($idTipoProducto);
        $this->tipoProducto = $tipoProducto;
        $this->fechaAlta = isset($fechaAlta) ? new DateTime($fechaAlta) : null;//date("Y-m-d H:i:s", strtotime($date))
        $this->fechaModificacion = isset($fechaModificacion) ? new DateTime($fechaModificacion) : null;
        $this->fechaBaja = isset($fechaBaja) ? new DateTime($fechaBaja) : null;
        

    }

    private static function EjecutarQueryInsertar($consulta, $cliente) {
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':precio', $this->precio, PDO::PARAM_STR);
        $consulta->bindValue(':stock', $this->stock, PDO::PARAM_INT);
        $consulta->bindValue(':idTipoProducto', $this->idTipoProducto, PDO::PARAM_INT);

        $consulta->execute();
    }
    public function CrearProducto()
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'INSERT INTO productos (nombre,precio,stock,idTipoProducto,fechaAlta)values(:nombre,:precio,:stock,:idTipoProducto,:fechaAlta)';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        
        $fechaAlta = date('Y/m/d H:i:s', strtotime("now"));
        $this->fechaAlta = new DateTime($fechaAlta);
        $consulta->bindValue(':fechaAlta', $fechaAlta, PDO::PARAM_STR);

        self::EjecutarQueryInsertar($consulta, $this);
        $this->id = $objetoAccesoDato->RetornarUltimoIdInsertado();
        return $this->id;
    }
    public function ModificarProducto()
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'UPDATE productos SET nombre = :nombre, precio = :precio, stock = :stock, 
        idTipoProducto =:idTipoProducto, fechaModificacion = :fechaModificacion WHERE id = :id';
        
        $fechaModificacion = date('Y/m/d H:i:s', strtotime("now"));
        $this->fechaModificacion = new DateTime($fechaModificacion);
        $consulta->bindValue(':fechaModificacion', $fechaModificacion, PDO::PARAM_STR);
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);

        self::EjecutarQueryInsertar($consulta, $this);
        return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }

    private static function FetchQueryGetAll($consulta) {
        $consulta->bindColumn('id', $id, PDO::PARAM_INT);
        $consulta->bindColumn('nombre', $nombre, PDO::PARAM_STR);
        $consulta->bindColumn('precio', $precio, PDO::PARAM_STR);//parsear a float
        $consulta->bindColumn('stock', $stock, PDO::PARAM_INT);
        $consulta->bindColumn('idTipoProducto', $idTipoProducto, PDO::PARAM_INT);
        $consulta->bindColumn('tipo', $tipo, PDO::PARAM_STR);
        $consulta->bindColumn('fechaAlta', $fechaAlta, PDO::PARAM_STR);
        $consulta->bindColumn('fechaModificacion', $fechaModificacion, PDO::PARAM_STR);
        $consulta->bindColumn('fechaBaja', $fechaBaja, PDO::PARAM_STR);

        $producto = null;
        $productos = [];
        while ($fila = $consulta->fetch(PDO::FETCH_BOUND)) {
            $producto = new Producto($nombre, floatval($precio), $stock, $idTipoProducto, $tipo, $fechaAlta, $fechaModificacion, $fechaBaja, $id);
            array_push($productos, $producto);
        }
    }

    private static function FetchQueryGet($consulta) {
        $consulta->bindColumn('id', $id, PDO::PARAM_INT);
        $consulta->bindColumn('nombre', $nombre, PDO::PARAM_STR);
        $consulta->bindColumn('precio', $precio, PDO::PARAM_STR);//parsear a float
        $consulta->bindColumn('stock', $stock, PDO::PARAM_INT);
        $consulta->bindColumn('idTipoProducto', $idTipoProducto, PDO::PARAM_INT);
        $consulta->bindColumn('tipo', $tipo, PDO::PARAM_STR);
        $consulta->bindColumn('fechaAlta', $fechaAlta, PDO::PARAM_STR);
        $consulta->bindColumn('fechaModificacion', $fechaModificacion, PDO::PARAM_STR);
        $consulta->bindColumn('fechaBaja', $fechaBaja, PDO::PARAM_STR);

        $producto = null;

        if ($consulta->fetch(PDO::FETCH_BOUND)) {
            $producto = new Producto($nombre, floatval($precio), $stock, $idTipoProducto, $tipo, $fechaAlta, $fechaModificacion, $fechaBaja, $id);
        }
        
        return $producto;
    }

    public static function GetProductos()
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT p.*, t.nombre AS tipo FROM productos p LEFT JOIN tiposDeProducto t ON p.idTipoProducto = t.id WHERE p.baja = 0';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->execute();

        return self::FetchQueryGetAll($consulta);

    }

    public static function GetProducto($id)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT p.*, t.nombre AS tipo FROM productos p LEFT JOIN tiposDeProducto t ON p.idTipoProducto = t.id WHERE p.id = :id AND p.baja = 0';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
        
        return self::FetchQueryGet($consulta);
    }

    public static function GetProductoPorNombre($strNombre)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT p.*, t.nombre AS tipo FROM productos p LEFT JOIN tiposDeProducto t ON p.idTipoProducto = t.id WHERE p.nombre = :nombre AND p.baja = 0';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':nombre', $strNombre, PDO::PARAM_STR);
        $consulta->execute();
        
        return self::FetchQueryGet($consulta);
    }

    public static function GetProductosPorIdTipo($idTipo)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT p.*, t.nombre AS tipo FROM productos p LEFT JOIN tiposDeProducto t ON p.idTipoProducto = t.id WHERE t.id = :tipo AND p.baja = 0';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':tipo', $idTipo, PDO::PARAM_INT);
        $consulta->execute();

        return self::FetchQueryGetAll($consulta);
    }

    public static function GetProductosPorStrTipo($strTipo)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT p.*, t.nombre AS tipo FROM productos p LEFT JOIN tiposDeProducto t ON p.idTipoProducto = t.id WHERE t.nombre = :tipo AND p.baja = 0';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':tipo', $strTipo, PDO::PARAM_STR);
        $consulta->execute();

        return self::FetchQueryGetAll($consulta);
    }

    public static function ToCsvArray($productos) {
        //$nombre, $precio, $stock, $idTipoProducto, $tipoProducto, $fechaAlta = null, $fechaModificacion = null, $fechaBaja = null, $id = null
        
        $arrayCsv = [];
        foreach ($productos as $producto) {
            array_push($arrayCsv, [
                $this->id,
                $this->nombre,
                $this->precio,
                $this->stock,
                $this->idTipoProducto,
                $this->tipoProducto,
                $this->fechaAlta,
                $this->fechaModificacion,
                $this->fechaBaja,
            ]);
        }
        return $arrayCsv;
        
    }

    public static function ToProducto($csvProducto) {
        $p = fgetcsv($csvProducto);
        $producto = new Producto($p[1], $p[2], $p[3], $p[4], $p[5], $p[6], $p[7], $p[8], $p[0]);
        
        return $producto;
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