<?php

require_once './db/AccesoDatos.php';

class Log
{
    public $id; 
    public $idUsuario; 
    public $rol; 
    public $horaEntrada;
    public $horaSalida;
    
    public function __construct($idUsuario, $rol, $horaEntrada, $horaSalida, $id = null) {
        $this->id = is_numeric($id) ? intval($id) : null;
        $this->idUsuario = intval($idUsuario);
        $this->rol = $rol;
        $this->horaEntrada = new DateTime($horaEntrada);
        $this->horaSalida = new DateTime($horaSalida);

    }

    private static function EjecutarQueryInsertar($consulta, $ajuste) {
        
        $horaEntrada = isset($ajuste->horaEntrada) ? $ajuste->horaEntrada->format('Y-m-d H:i:s') : null;
        $horaSalida = isset($ajuste->horaSalida) ? $ajuste->horaSalida->format('Y-m-d H:i:s') : null;
        $consulta->bindValue(':idUsuario', $ajuste->idUsuario, PDO::PARAM_INT);
        $consulta->bindValue(':rol', $ajuste->rol, PDO::PARAM_STR);
        $consulta->bindValue(':horaEntrada', $horaEntrada, PDO::PARAM_STR);
        $consulta->bindValue(':horaSalida', $horaSalida, PDO::PARAM_STR);
        $consulta->execute();
    }

    public function CrearLog() {
        $objetoAccesoDatos = AccesoDatos::getObjetoAcceso();
        $query = 'INSERT INTO logs (idUsuario, rol, horaEntrada, horaSalida) VALUES (:idUsuario, :rol, :horaEntrada, :horaSalida)';
        
        $consulta = $objetoAccesoDatos->RetornarConsulta($query);
        
        self::EjecutarQueryInsertar($consulta, $this);
        $this->id = $objetoAccesoDatos->RetornarUltimoIdInsertado();
        return $this->id;
    }
/*
    public function ModificarAjuste() {
        $objetoAccesoDatos = AccesoDatos::getObjetoAcceso();
        $query = 'UPDATE ajustes SET idReserva = :idReserva, motivo = :motivo, ajuste = :ajuste, fechaModificacion = :fechaModificacion WHERE id = :id';

        $consulta = $objetoAccesoDatos->RetornarConsulta($query);
        $fechaModificacion = date('Y/m/d H:i:s', strtotime("now"));
        $this->fechaModificacion = new DateTime($fechaModificacion);
        $consulta->bindValue(':fechaModificacion', $fechaModificacion, PDO::PARAM_STR);
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);

        self::EjecutarQueryInsertar($consulta, $this);
        return $objetoAccesoDatos->RetornarUltimoIdInsertado();
    }

    private static function BindColumns($consulta) {
        $consulta->bindColumn('id', $id, PDO::PARAM_INT);
        $consulta->bindColumn('idReserva', $idReserva, PDO::PARAM_INT);
        $consulta->bindColumn('motivo', $motivo, PDO::PARAM_STR);
        $consulta->bindColumn('ajuste', $ajuste, PDO::PARAM_STR);//parsear a float
        $consulta->bindColumn('fechaAlta', $fechaAlta, PDO::PARAM_STR);
        $consulta->bindColumn('fechaModificacion', $fechaModificacion, PDO::PARAM_STR);
        $consulta->bindColumn('fechaBaja', $fechaBaja, PDO::PARAM_STR);

    }
    
    private static function FetchQueryGetAll($consulta) {
        $consulta->bindColumn('id', $id, PDO::PARAM_INT);
        $consulta->bindColumn('idReserva', $idReserva, PDO::PARAM_INT);
        $consulta->bindColumn('motivo', $motivo, PDO::PARAM_STR);
        $consulta->bindColumn('ajuste', $ajuste, PDO::PARAM_STR);//parsear a float
        $consulta->bindColumn('fechaAlta', $fechaAlta, PDO::PARAM_STR);
        $consulta->bindColumn('fechaModificacion', $fechaModificacion, PDO::PARAM_STR);
        $consulta->bindColumn('fechaBaja', $fechaBaja, PDO::PARAM_STR);
        $ajuste = null;
        $ajustes = [];
        while ($fila = $consulta->fetch(PDO::FETCH_BOUND)) {
            $ajuste = new Ajuste($idReserva, $motivo, $ajuste, $fechaAlta, $fechaModificacion, $fechaBaja, $id);
            array_push($ajustes, $ajuste);
        }
        return $ajustes;
    }

    private static function FetchQueryGet($consulta) {
        $consulta->bindColumn('id', $id, PDO::PARAM_INT);
        $consulta->bindColumn('idReserva', $idReserva, PDO::PARAM_INT);
        $consulta->bindColumn('motivo', $motivo, PDO::PARAM_STR);
        $consulta->bindColumn('ajuste', $ajuste, PDO::PARAM_STR);//parsear a float
        $consulta->bindColumn('fechaAlta', $fechaAlta, PDO::PARAM_STR);
        $consulta->bindColumn('fechaModificacion', $fechaModificacion, PDO::PARAM_STR);
        $consulta->bindColumn('fechaBaja', $fechaBaja, PDO::PARAM_STR);
        $ajuste = null;
        if ($consulta->fetch(PDO::FETCH_BOUND)) {
            $ajuste = new Ajuste($idReserva, $motivo, $ajuste, $fechaAlta, $fechaModificacion, $fechaBaja, $id);
        }
        
        return $ajuste;
    }

    public static function GetAjustes()
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT * FROM ajustes WHERE fechaBaja IS NULL';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->execute();

        return self::FetchQueryGetAll($consulta);
    }

    public static function GetAjuste($id)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT * FROM ajustes WHERE id = :id';// AND fechaBaja IS NULL';
    
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return self::FetchQueryGet($consulta);
    }*/


}

?>