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
    
    public function __construct($codigo, $idEstado, $estado, $baja = false, $id = null) {
        $this->id = $id;
        $this->codigo = $codigo;
        $this->idEstado = $idEstado;
        $this->estado = $estado;
        $this->baja = $baja;

    }

    

    public function CrearMesa()
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'INSERT INTO mesas (codigo,idEstado,baja) VALUES (:codigo,:idEstado,:baja)';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':codigo', $this->codigo, PDO::PARAM_STR);
        $consulta->bindValue(':idEstado', $this->idEstado, PDO::PARAM_INT);
        $consulta->bindValue(':baja', $this->baja, PDO::PARAM_BOOL);
        $consulta->execute();
        return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }

    public function ModificarMesa()
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'UPDATE mesas SET codigo = :codigo, idEstado = :idEstado, baja = :baja WHERE id = :id';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->bindValue(':codigo', $this->codigo, PDO::PARAM_STR);
        $consulta->bindValue(':idEstado', $this->idEstado, PDO::PARAM_INT);
        $consulta->bindValue(':baja', $this->baja, PDO::PARAM_BOOL);
        $consulta->execute();
        return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }

    public static function GetMesas()
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT m.*, e.estado FROM mesas m LEFT JOIN estadosMesa e ON m.idEstado = e.id WHERE m.baja = 0';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->execute();

        $consulta->bindColumn('id', $id, PDO::PARAM_INT);
        $consulta->bindColumn('codigo', $codigo, PDO::PARAM_STR);
        $consulta->bindColumn('idEstado', $idEstado, PDO::PARAM_INT);
        $consulta->bindColumn('estado', $estado, PDO::PARAM_STR);
        $consulta->bindColumn('baja', $baja, PDO::PARAM_BOOL);

        $mesa = null;
        $mesas = [];
        while ($fila = $consulta->fetch(PDO::FETCH_BOUND)) {
            $mesa = new Mesa($codigo, $idEstado, $estado, $baja, $id);
            array_push($mesas, $mesa);
        }
        return $mesas;
    }
    
    public static function GetMesa($id)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT m.*, e.estado FROM mesas m LEFT JOIN estadosMesa e ON m.idEstado = e.id WHERE m.id = :id AND m.baja = 0';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
        
        $consulta->bindColumn('id', $id, PDO::PARAM_INT);
        $consulta->bindColumn('codigo', $codigo, PDO::PARAM_STR);
        $consulta->bindColumn('idEstado', $idEstado, PDO::PARAM_INT);
        $consulta->bindColumn('estado', $estado, PDO::PARAM_STR);
        $consulta->bindColumn('baja', $baja, PDO::PARAM_BOOL);

        $mesa = null;

        if ($consulta->fetch(PDO::FETCH_BOUND)) {
            $mesa = new Mesa($codigo, $idEstado, $estado, $baja, $id);
        }
        
        return $mesa;
    }

    public static function GetMesaPorCodigo($codigo)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT m.*, e.estado FROM mesas m LEFT JOIN estadosMesa e ON m.idEstado = e.id WHERE m.codigo = :codigo AND m.baja = 0';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':codigo', $codigo, PDO::PARAM_STR);
        $consulta->execute();
        
        $consulta->bindColumn('id', $id, PDO::PARAM_INT);
        $consulta->bindColumn('codigo', $codigo, PDO::PARAM_STR);
        $consulta->bindColumn('idEstado', $idEstado, PDO::PARAM_INT);
        $consulta->bindColumn('estado', $estado, PDO::PARAM_STR);
        $consulta->bindColumn('baja', $baja, PDO::PARAM_BOOL);

        $mesa = null;

        if ($consulta->fetch(PDO::FETCH_BOUND)) {
            $mesa = new Mesa($codigo, $idEstado, $estado, $baja, $id);
        }
        
        return $mesa;
    }
    public static function GetMesasPorEstado($idEstado)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT m.*, e.estado AS estado FROM mesas m LEFT JOIN estadosMesa e ON m.idEstado = e.id WHERE m.idEstado = :idEstado AND m.baja = 0';
        
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':idEstado', $idEstado, PDO::PARAM_INT);
        $consulta->execute();

        $consulta->bindColumn('id', $id, PDO::PARAM_INT);
        $consulta->bindColumn('codigo', $codigo, PDO::PARAM_STR);
        $consulta->bindColumn('idEstado', $idEstado, PDO::PARAM_INT);
        $consulta->bindColumn('estado', $estado, PDO::PARAM_STR);
        $consulta->bindColumn('baja', $baja, PDO::PARAM_BOOL);

        $mesa = null;
        $mesas = [];
        while ($fila = $consulta->fetch(PDO::FETCH_BOUND)) {
            $mesa = new Mesa($codigo, $idEstado, $estado, $baja, $id);
            array_push($mesas, $mesa);
        }
        return $mesas;
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