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

    private static function EjecutarQueryInsertar($consulta, $producto) {
        $consulta->bindValue(':nombre', $producto->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':precio', $producto->precio, PDO::PARAM_STR);
        $consulta->bindValue(':stock', $producto->stock, PDO::PARAM_INT);
        $consulta->bindValue(':idTipoProducto', $producto->idTipoProducto, PDO::PARAM_INT);

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

        return $productos;
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
        $query = 'SELECT p.*, t.nombre AS tipo FROM productos p LEFT JOIN tiposDeProducto t ON p.idTipoProducto = t.id WHERE p.fechaBaja IS NULL';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->execute();

        return self::FetchQueryGetAll($consulta);

    }

    public static function GetProducto($id)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT p.*, t.nombre AS tipo FROM productos p LEFT JOIN tiposDeProducto t ON p.idTipoProducto = t.id WHERE p.id = :id AND p.fechaBaja IS NULL';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
        
        return self::FetchQueryGet($consulta);
    }

    public static function GetProductoPorNombre($strNombre)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT p.*, t.nombre AS tipo FROM productos p LEFT JOIN tiposDeProducto t ON p.idTipoProducto = t.id WHERE p.nombre = :nombre AND p.fechaBaja IS NULL';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':nombre', $strNombre, PDO::PARAM_STR);
        $consulta->execute();
        
        return self::FetchQueryGet($consulta);
    }

    public static function GetProductosPorIdTipo($idTipo)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT p.*, t.nombre AS tipo FROM productos p LEFT JOIN tiposDeProducto t ON p.idTipoProducto = t.id WHERE t.id = :tipo AND p.fechaBaja IS NULL';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':tipo', $idTipo, PDO::PARAM_INT);
        $consulta->execute();

        return self::FetchQueryGetAll($consulta);
    }

    public static function GetProductosPorStrTipo($strTipo)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT p.*, t.nombre AS tipo FROM productos p LEFT JOIN tiposDeProducto t ON p.idTipoProducto = t.id WHERE t.nombre = :tipo AND p.fechaBaja IS NULL';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':tipo', $strTipo, PDO::PARAM_STR);
        $consulta->execute();

        return self::FetchQueryGetAll($consulta);
    }

    public static function BajaProducto($id) {
        $fechaBaja = date('Y/m/d H:i:s',strtotime("now"));
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'UPDATE productos SET fechaBaja = :fechaBaja WHERE id = :id';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->bindValue(':fechaBaja', $fechaBaja, PDO::PARAM_STR);
        $consulta->execute();

        return $fechaBaja;
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

    public function GetFechaAlta() {
        return isset($this->fechaAlta) ? $this->fechaAlta->format('Y/m/d H:i:s') : null;
    }
    public function GetFechaModificacion() {
        return isset($this->fechaModificacion) ? $this->fechaModificacion->format('Y/m/d H:i:s') : null;
    }
    public function GetFechaBaja() {
        return isset($this->fechaBaja) ? $this->fechaBaja->format('Y/m/d H:i:s') : null;
    }
    public function ToCsvLine() {
        $fechaAlta = $this->GetFechaAlta();
        $fechaModificacion = $this->GetFechaModificacion();
        $fechaBaja = $this->GetFechaBaja();
        return "$this->nombre,$this->precio,$this->stock,$this->idTipoProducto,$this->tipoProducto,$fechaAlta,$fechaModificacion,$fechaBaja,$this->id";
    }

    //ya le estaria pasando el stream con getStream()
    public static function ParsearCsv($ruta, $tieneTitulos = false) {

        $productos = [];
        $producto = null;
        $p = null;
        $file = fopen($ruta,"r");
        $flagTitulos = false;
        
        while(!feof($file))
        {
            
            $p = fgetcsv($file);
            if ($tieneTitulos && !$flagTitulos) {
                $flagTitulos = true;
                continue;
            }
            //var_dump($p);
            if ($p !== false && $p !== null) {
                $fechaAlta = (isset($p[5]) || empty($p[5])) ? null : $p[5];
                $fechaModificacion = (isset($p[6]) || empty($p[6])) ? null : $p[6];
                $fechaBaja = (isset($p[7]) || empty($p[7])) ? null : $p[7];
                $id = (isset($p[8]) || empty($p[8])) ? null : $p[8];
                $producto = new Producto($p[0], $p[1], $p[2], $p[3], $p[4], $fechaAlta, $fechaModificacion, $fechaBaja, $id);
                array_push($productos, $producto);
            }
        }
        fclose($file);
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