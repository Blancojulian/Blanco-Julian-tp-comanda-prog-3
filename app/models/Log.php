<?php

require_once './db/AccesoDatos.php';

class Log implements JsonSerializable
{
    public $id; 
    public $idUsuario; 
    public $rol; 
    public $horario;
    
    public function __construct($idUsuario, $rol, $horario = null, $id = null) {
        $this->id = is_numeric($id) ? intval($id) : null;
        $this->idUsuario = intval($idUsuario);
        $this->rol = $rol;
        $this->horario = isset($horario) ? new DateTime($horario) : new DateTime();

    }

    public function GetHorario() {
        return ($this->horario instanceof DateTime) ? $this->horario->format('Y/m/d H:i:s') : null;
    }

    private static function EjecutarQueryInsertar($consulta, $log) {
        
        $consulta->bindValue(':idUsuario', $log->idUsuario, PDO::PARAM_INT);
        $consulta->bindValue(':rol', $log->rol, PDO::PARAM_STR);
        $consulta->bindValue(':horario', $log->GetHorario(), PDO::PARAM_STR);
        $consulta->execute();
    }

    public function CrearLog() {
        $objetoAccesoDatos = AccesoDatos::getObjetoAcceso();
        $query = 'INSERT INTO logsUsuarios (idUsuario, rol, horario) VALUES (:idUsuario, :rol, :horario)';
        
        $consulta = $objetoAccesoDatos->RetornarConsulta($query);
        
        self::EjecutarQueryInsertar($consulta, $this);
        $this->id = $objetoAccesoDatos->RetornarUltimoIdInsertado();
        return $this->id;
    }

    private static function BindColumns($consulta) {
        $aux = new stdClass();
        
        $consulta->bindColumn('id', $aux->id, PDO::PARAM_INT);
        $consulta->bindColumn('idUsuario', $aux->idUsuario, PDO::PARAM_INT);
        $consulta->bindColumn('rol', $aux->rol, PDO::PARAM_STR);
        $consulta->bindColumn('horario', $aux->horario, PDO::PARAM_STR);

        return $aux;
    }
    
    private static function FetchQueryGetAll($consulta) {
        $aux = self::BindColumns($consulta);
        $log = null;
        $logs = [];
        while ($fila = $consulta->fetch(PDO::FETCH_BOUND)) {
            $log = new Log($aux->idUsuario, $aux->rol, $aux->horario, $aux->id);
            array_push($logs, $log);
        }
        return $logs;
    }

    public static function GetLogs()
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT * FROM logsUsuarios';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->execute();

        return self::FetchQueryGetAll($consulta);
    }

    public static function GetLogsPorUsuario($idUsuario)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT * FROM logsUsuarios WHERE idUsuario = :idUsuario ORDER BY idUsuario ASC, horario ASC';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':idUsuario', $idUsuario, PDO::PARAM_INT);
        $consulta->execute();

        return self::FetchQueryGetAll($consulta);
    }

    public function jsonSerialize(){
        return [
            'id' => $this->id,
            'idUsuario' => $this->idUsuario,
            'rol' => $this->rol,
            'horario' => $this->GetHorario()
        ];
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