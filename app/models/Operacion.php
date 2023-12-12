<?php

require_once './db/AccesoDatos.php';

class Operacion
{
    public $id; 
    public $idUsuario; 
    public $sector; 
    public $horario;
    public $descripcion;
    
    public function __construct($idUsuario, $sector, $horario, $descripcion = '',$id = null) {
        $this->id = is_numeric($id) ? intval($id) : null;
        $this->idUsuario = intval($idUsuario);
        $this->sector = $sector;
        $this->horario = new DateTime($horario);
        $this->descripcion = $descripcion;

    }

    private static function EjecutarQueryInsertar($consulta, $log) {
        
        $horario = isset($log->horario) ? $log->horario->format('Y-m-d H:i:s') : null;
        $consulta->bindValue(':idUsuario', $log->idUsuario, PDO::PARAM_INT);
        $consulta->bindValue(':sector', $log->sector, PDO::PARAM_STR);
        $consulta->bindValue(':horario', $horario, PDO::PARAM_STR);
        $consulta->bindValue(':descripcion', $log->descripcion, PDO::PARAM_STR);
        $consulta->execute();
    }

    public function CrearOperacion() {
        $objetoAccesoDatos = AccesoDatos::getObjetoAcceso();
        $query = 'INSERT INTO operaciones (idUsuario, sector, horario, descripcion) VALUES (:idUsuario, :sector, :horario, :descripcion)';
        
        $consulta = $objetoAccesoDatos->RetornarConsulta($query);
        
        self::EjecutarQueryInsertar($consulta, $this);
        $this->id = $objetoAccesoDatos->RetornarUltimoIdInsertado();
        return $this->id;
    }

    private static function BindColumns($consulta) {
        $aux = new stdClass();
        
        $consulta->bindColumn('id', $id, PDO::PARAM_INT);
        $consulta->bindColumn('idUsuario', $idUsuario, PDO::PARAM_INT);
        $consulta->bindColumn('sector', $sector, PDO::PARAM_STR);
        $consulta->bindColumn('horario', $horario, PDO::PARAM_STR);
        $consulta->bindColumn('descripcion', $descripcion, PDO::PARAM_STR);

        return $aux;
    }
    
    private static function FetchQueryGetAll($consulta) {
        $aux = self::BindColumns($consulta);
        $log = null;
        $logs = [];
        while ($fila = $consulta->fetch(PDO::FETCH_BOUND)) {
            $log = new Log($aux->idUsuario, $aux->sector, $aux->horario, $aux->descripcion, $aux->id);
            array_push($logs, $log);
        }
        return $logs;
    }

    public static function GetOperaciones()
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT * FROM operaciones';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->execute();

        return self::FetchQueryGetAll($consulta);
    }
    public static function GetCantidadOperacionesPorSector()
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT sector, COUNT(*) AS cantidadOperaciones FROM operaciones GROUP BY sector';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->execute();
        $consulta->bindColumn('sector', $sector, PDO::PARAM_STR);
        $consulta->bindColumn('cantidadOperaciones', $cantidadOperaciones, PDO::PARAM_INT);
        $arrCantidadOperacionesPorSector = [];

        while ($fila = $consulta->fetch(PDO::FETCH_BOUND)) {
            array_push($arrCantidadOperacionesPorSector, ['sector' => $sector, 'cantidad operaciones' => $cantidadOperaciones]);
        }
        return $arrCantidadOperacionesPorSector;
    }

    public static function GetCantidadOperacionesPorEmpleado()
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT idUsuario, sector, COUNT(*) AS cantidadOperaciones FROM operaciones GROUP BY idUsuario, sector ORDER BY sector';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->execute();
        $consulta->bindColumn('idUsuario', $idUsuario, PDO::PARAM_INT);
        $consulta->bindColumn('sector', $sector, PDO::PARAM_STR);
        $consulta->bindColumn('cantidadOperaciones', $cantidadOperaciones, PDO::PARAM_INT);
        $arrCantidadOperaciones = [];

        while ($fila = $consulta->fetch(PDO::FETCH_BOUND)) {
            array_push($arrCantidadOperaciones, ['idUsuario' => $idUsuario, 'sector' => $sector, 'cantidad operaciones' => $cantidadOperaciones]);
        }
        return $arrCantidadOperaciones;
    }
}

?>