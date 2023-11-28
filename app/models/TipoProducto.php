<?php

require_once './db/AccesoDatos.php';

class TipoProducto {

    public int $id;
    public string $nombre;

    public function __construct($nombre, $id = null) {
        $this->id = $id;
        $this->nombre = $nombre;
    }

    public function CrearTipoProducto() {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'INSERT INTO tiposDeProducto (nombre) VALUES (:nombre)';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->execute();
        return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }
 
    public static function GetTipoPorNombre($nombre) {
        
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT id, nombre FROM tiposDeProducto WHERE nombre = :nombre';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':nombre', $nombre, PDO::PARAM_STR);
        $consulta->execute();
        
        $consulta->bindColumn('id', $id, PDO::PARAM_INT);
        $consulta->bindColumn('nombre', $nombre, PDO::PARAM_STR);
        $tipo = null;

        if ($data = $consulta->fetch(PDO::FETCH_BOUND)) {
            $tipo = new TipoProducto($nombre, $id);
        }
        
        return $tipo;
    }

    public static function GetTipoPorId($id) {
        
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT id, nombre FROM tiposDeProducto WHERE id = :id';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
        
        $consulta->bindColumn('id', $id, PDO::PARAM_INT);
        $consulta->bindColumn('nombre', $nombre, PDO::PARAM_STR);
        $tipo = null;

        if ($consulta->fetch(PDO::FETCH_BOUND)) {
            $tipo = new Puesto($nombre, $id);
        }
        
        return $tipo;
    }
}

?>