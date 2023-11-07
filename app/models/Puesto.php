<?php

require_once './db/AccesoDatos.php';

class Puesto {

    public int $id;
    public string $nombre;
    public bool $permisos;

    public function __construct($nombre, $permisos, $id = null) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->permisos = $permisos;
    }

    public function CrearPuesto() {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'INSERT INTO puestos (nombre, permisos) VALUES (:nombre, :permisos) ';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':permisos', $this->permisos, PDO::PARAM_BOOL);
        $consulta->execute();
        return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }
 
    public static function GetPuestoPorNombre($strNombre) {
        
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT id, nombre, permisos FROM puestos WHERE nombre = :nombre';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':nombre', $strNombre, PDO::PARAM_STR);
        $consulta->execute();
        
        $consulta->bindColumn('id', $id, PDO::PARAM_INT);
        $consulta->bindColumn('nombre', $nombre, PDO::PARAM_STR);
        $consulta->bindColumn('permisos', $permisos, PDO::PARAM_BOOL);
        $puesto = null;

        if ($data = $consulta->fetch(PDO::FETCH_BOUND)) {
            $puesto = new Puesto($nombre, $permisos, $id);
        }
        
        return $puesto;
    }

    public static function GetPuestoPorId($idPuesto) {
        
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT id, nombre, permisos FROM puestos WHERE id = :id';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':id', $idPuesto, PDO::PARAM_INT);//antes era $nombre, ver si se cargo algo mal
        $consulta->execute();
        
        $consulta->bindColumn('id', $id, PDO::PARAM_INT);
        $consulta->bindColumn('nombre', $nombre, PDO::PARAM_STR);
        $consulta->bindColumn('permisos', $permisos, PDO::PARAM_BOOL);
        $puesto = null;

        if ($consulta->fetch(PDO::FETCH_BOUND)) {
            $puesto = new Puesto($nombre, $permisos, $id);
        }
        
        return $puesto;
    }
}

?>