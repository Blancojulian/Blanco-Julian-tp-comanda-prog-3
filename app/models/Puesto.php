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
//probar
    public static function GetPuestos2(...$arrRoles) {
        $roles = join(",", array_map(fn($r) => "'$r'", $arrRoles));
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT id, nombre, permisos FROM puestos WHERE nombre IN (:roles)';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':roles', $roles, PDO::PARAM_STR);
        $consulta->execute();
        
        $consulta->bindColumn('id', $id, PDO::PARAM_INT);
        $consulta->bindColumn('nombre', $nombre, PDO::PARAM_STR);
        $consulta->bindColumn('permisos', $permisos, PDO::PARAM_BOOL);
        $puestos = [];
        $puesto = null;

        while ($data = $consulta->fetch(PDO::FETCH_BOUND)) {
            $puesto = new Puesto($nombre, $permisos, $id);
            array_push($puestos, $puesto);
        }
        
        return $puesto;
    }

    public static function GetPuestos(...$arrRoles) {
        
        $str = str_repeat("?,", count($arrRoles));
        $str = substr($str, 0, -1);
        
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = "SELECT id, nombre, permisos FROM puestos WHERE nombre IN ($str)";
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $i = 0;
        foreach ($arrRoles as $rol) {
            $i++;
            $consulta->bindValue($i, $rol, PDO::PARAM_STR);

        }
        $consulta->execute();
        
        $consulta->bindColumn('id', $id, PDO::PARAM_INT);
        $consulta->bindColumn('nombre', $nombre, PDO::PARAM_STR);
        $consulta->bindColumn('permisos', $permisos, PDO::PARAM_BOOL);
        $puestos = [];
        $puesto = null;

        while ($data = $consulta->fetch(PDO::FETCH_BOUND)) {
            $puesto = new Puesto($nombre, $permisos, $id);
            array_push($puestos, $puesto);
        }
        
        return $puesto;
    }
}

?>