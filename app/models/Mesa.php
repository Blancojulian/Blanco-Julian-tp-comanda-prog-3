<?php

require_once './db/AccesoDatos.php';
require_once './utils/utils.php';

class Mesa implements JsonSerializable
{
    public $id;
    public $codigo;
    public $estado;
    public $idEstado;
    public $baja;
    
    public function __construct($codigo, $idEstado, $estado, $fechaAlta = null, $fechaModificacion = null, $fechaBaja = null, $id = null) {
        $this->id = $id;
        $this->codigo = $codigo;
        $this->idEstado = $idEstado;
        $this->estado = $estado;
        $this->baja = $baja;

    }

    private static function EjecutarQueryInsertar($consulta, $cliente) {
        $consulta->bindValue(':codigo', $this->codigo, PDO::PARAM_STR);
        $consulta->bindValue(':idEstado', $this->idEstado, PDO::PARAM_INT);

        $consulta->execute();
    }

    public function CrearMesa()
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'INSERT INTO mesas (codigo,idEstado,fechaAlta) VALUES (:codigo,:idEstado,:fechaAlta)';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        
        $fechaAlta = date('Y/m/d H:i:s', strtotime("now"));
        $this->fechaAlta = new DateTime($fechaAlta);
        $consulta->bindValue(':fechaAlta', $fechaAlta, PDO::PARAM_STR);

        self::EjecutarQueryInsertar($consulta, $this);
        $this->id = $objetoAccesoDato->RetornarUltimoIdInsertado();
        return $this->id;
    }

    public function ModificarMesa()
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'UPDATE mesas SET codigo = :codigo, idEstado = :idEstado, fechaModificacion = :fechaModificacion WHERE id = :id';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);

        $fechaModificacion = date('Y/m/d H:i:s', strtotime("now"));
        $this->fechaModificacion = new DateTime($fechaModificacion);
        $consulta->bindValue(':fechaModificacion', $fechaModificacion, PDO::PARAM_STR);
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);

        self::EjecutarQueryInsertar($consulta, $this);
        return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }

    private static function FetchQueryGetAll($consulta) {
        
        $consulta->bindColumn('id', $id, PDO::PARAM_INT);
        $consulta->bindColumn('codigo', $codigo, PDO::PARAM_STR);
        $consulta->bindColumn('idEstado', $idEstado, PDO::PARAM_INT);
        $consulta->bindColumn('estado', $estado, PDO::PARAM_STR);
        $consulta->bindColumn('fechaAlta', $fechaAlta, PDO::PARAM_STR);
        $consulta->bindColumn('fechaModificacion', $fechaModificacion, PDO::PARAM_STR);
        $consulta->bindColumn('fechaBaja', $fechaBaja, PDO::PARAM_STR);

        $mesa = null;
        $mesas = [];
        while ($fila = $consulta->fetch(PDO::FETCH_BOUND)) {
            $mesa = new Mesa($codigo, $idEstado, $estado, $fechaAlta, $fechaModificacion, $fechaBaja, $id);
            array_push($mesas, $mesa);
        }
        return $mesas;
    }

    private static function FetchQueryGet($consulta) {
        $consulta->bindColumn('id', $id, PDO::PARAM_INT);
        $consulta->bindColumn('codigo', $codigo, PDO::PARAM_STR);
        $consulta->bindColumn('idEstado', $idEstado, PDO::PARAM_INT);
        $consulta->bindColumn('estado', $estado, PDO::PARAM_STR);
        $consulta->bindColumn('fechaAlta', $fechaAlta, PDO::PARAM_STR);
        $consulta->bindColumn('fechaModificacion', $fechaModificacion, PDO::PARAM_STR);
        $consulta->bindColumn('fechaBaja', $fechaBaja, PDO::PARAM_STR);

        $mesa = null;

        if ($consulta->fetch(PDO::FETCH_BOUND)) {
            $mesa = new Mesa($codigo, $idEstado, $estado, $fechaAlta, $fechaModificacion, $fechaBaja, $id);
        }
        
        return $mesa;
    }

    public static function GetMesas()
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT m.*, e.estado FROM mesas m LEFT JOIN estadosMesa e ON m.idEstado = e.id WHERE m.baja = 0';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->execute();

        return self::FetchQueryGetAll($consulta);
    }
    
    public static function GetMesa($id)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT m.*, e.estado FROM mesas m LEFT JOIN estadosMesa e ON m.idEstado = e.id WHERE m.id = :id AND m.baja = 0';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
        
        return self::FetchQueryGet($consulta);
    }

    public static function GetMesaPorCodigo($codigo)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT m.*, e.estado FROM mesas m LEFT JOIN estadosMesa e ON m.idEstado = e.id WHERE m.codigo = :codigo AND m.baja = 0';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':codigo', $codigo, PDO::PARAM_STR);
        $consulta->execute();
        
        return self::FetchQueryGet($consulta);
    }
    public static function GetMesasPorEstado($idEstado)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT m.*, e.estado AS estado FROM mesas m LEFT JOIN estadosMesa e ON m.idEstado = e.id WHERE m.idEstado = :idEstado AND m.baja = 0';
        
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':idEstado', $idEstado, PDO::PARAM_INT);
        $consulta->execute();

        return self::FetchQueryGetAll($consulta);
    }
    
    public function jsonSerialize(){
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'estado' => $this->estado
        ];
    }
}

?>