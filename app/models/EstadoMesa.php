<?php

require_once './db/AccesoDatos.php';

class EstadoMesa {

    public int $id;
    public string $estado;

    public function __construct($estado, $id = null) {
        $this->id = $id;
        $this->estado = $estado;
    }

    public function CrearEstado() {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'INSERT INTO estadosMesa (estado) VALUES (:estado) ';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->execute();
        return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }
 
    public static function GetEstadoPorNombreEstado($strEstado) {
        
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT id, estado FROM estadosMesa WHERE estado = :estado';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':estado', $strEstado, PDO::PARAM_STR);
        $consulta->execute();
        
        $consulta->bindColumn('id', $id, PDO::PARAM_INT);
        $consulta->bindColumn('estado', $estado, PDO::PARAM_STR);
        $estadoMesa = null;

        if ($data = $consulta->fetch(PDO::FETCH_BOUND)) {
            $estadoMesa = new EstadoMesa($estado, $id);
        }
        
        return $estadoMesa;
    }

    public static function GetEstadoPorId($idEstado) {
        
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT id, estado FROM estadosMesa WHERE id = :id';        
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':id', $idEstado, PDO::PARAM_INT);
        $consulta->execute();
        
        $consulta->bindColumn('id', $id, PDO::PARAM_INT);
        $consulta->bindColumn('estado', $estado, PDO::PARAM_STR);
        $estadoMesa = null;

        if ($consulta->fetch(PDO::FETCH_BOUND)) {
            $estadoMesa = new EstadoMesa($estado, $id);
        }
        
        return $estadoMesa;
    }
}

?>