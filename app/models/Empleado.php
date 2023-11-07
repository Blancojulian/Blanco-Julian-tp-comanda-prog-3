<?php

require_once './db/AccesoDatos.php';

class Empleado implements JsonSerializable
{
    public $id;
    public $nombre;
    public $apellido;
    public $dni;
    public $puesto;
    public $idPuesto;
    public $baja;

    
    public function __construct($nombre, $apellido, $dni, $idPuesto, $puesto, $baja = false, $id = null) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->dni = $dni;
        $this->idPuesto = $idPuesto;
        $this->puesto = $puesto;
        $this->baja = $baja;

    }


    public function CrearEmpleado()
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'INSERT INTO empleados (nombre, apellido, dni, idPuesto, baja) VALUES (:nombre, :apellido, :dni, :idPuesto, :baja)';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':apellido', $this->apellido, PDO::PARAM_STR);
        $consulta->bindValue(':dni', $this->dni, PDO::PARAM_STR);
        $consulta->bindValue(':idPuesto', $this->idPuesto, PDO::PARAM_INT);
        $consulta->bindValue(':baja', $this->baja, PDO::PARAM_BOOL);
        $consulta->execute();
        
        return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }
    public function ModificarEmpleado()
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'UPDATE empleados SET nombre = :nombre, apellido = :apellido, dni = :dni, idPuesto = :idPuesto, baja = :baja WHERE id = :id';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':apellido', $this->apellido, PDO::PARAM_STR);
        $consulta->bindValue(':dni', $this->dni, PDO::PARAM_STR);
        $consulta->bindValue(':idPuesto', $this->idPuesto, PDO::PARAM_INT);
        $consulta->bindValue(':baja', $this->baja, PDO::PARAM_BOOL);
        $consulta->execute();
        return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }

    public static function GetEmpleados()
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT e.*, p.nombre AS puesto FROM empleados e LEFT JOIN puestos p ON e.idPuesto = p.id WHERE e.baja = 0';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->execute();
        
        $consulta->bindColumn('id', $id, PDO::PARAM_INT);
        $consulta->bindColumn('nombre', $nombre, PDO::PARAM_STR);
        $consulta->bindColumn('apellido', $apellido, PDO::PARAM_STR);
        $consulta->bindColumn('dni', $dni, PDO::PARAM_STR);
        $consulta->bindColumn('idPuesto', $idPuesto, PDO::PARAM_INT);
        $consulta->bindColumn('puesto', $puesto, PDO::PARAM_STR);
        $consulta->bindColumn('baja', $baja, PDO::PARAM_BOOL);

        $empleado = null;
        $empleados = [];
        while ($fila = $consulta->fetch(PDO::FETCH_BOUND)) {
            $empleado = new Empleado($nombre, $apellido, $dni, $idPuesto, $puesto, $baja, $id);
            array_push($empleados, $empleado);
        }
        return $empleados;
    }

    public static function GetEmpleado($id)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT e.*, p.nombre AS puesto FROM empleados e LEFT JOIN puestos p ON e.idPuesto = p.id WHERE e.id = :id AND e.baja = 0';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
        
        $consulta->bindColumn('id', $id, PDO::PARAM_INT);
        $consulta->bindColumn('nombre', $nombre, PDO::PARAM_STR);
        $consulta->bindColumn('apellido', $apellido, PDO::PARAM_STR);
        $consulta->bindColumn('dni', $dni, PDO::PARAM_STR);
        $consulta->bindColumn('idPuesto', $idPuesto, PDO::PARAM_INT);
        $consulta->bindColumn('puesto', $puesto, PDO::PARAM_STR);
        $consulta->bindColumn('baja', $baja, PDO::PARAM_BOOL);

        $empleado = null;

        if ($consulta->fetch(PDO::FETCH_BOUND)) {
            $empleado = new Empleado($nombre, $apellido, $dni, $idPuesto, $puesto, $baja, $id);
        }
        
        return $empleado;
    }

    public static function GetEmpleadoPorDni($dniEmpleado)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT e.*, p.nombre AS puesto FROM empleados e LEFT JOIN puestos p ON e.idPuesto = p.id WHERE e.dni = :dniEmpleado AND e.baja = 0';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':dniEmpleado', $dniEmpleado, PDO::PARAM_STR);
        $consulta->execute();
        
        $consulta->bindColumn('id', $id, PDO::PARAM_INT);
        $consulta->bindColumn('nombre', $nombre, PDO::PARAM_STR);
        $consulta->bindColumn('apellido', $apellido, PDO::PARAM_STR);
        $consulta->bindColumn('dni', $dni, PDO::PARAM_STR);
        $consulta->bindColumn('idPuesto', $idPuesto, PDO::PARAM_INT);
        $consulta->bindColumn('puesto', $puesto, PDO::PARAM_STR);
        $consulta->bindColumn('baja', $baja, PDO::PARAM_BOOL);

        $empleado = null;

        if ($consulta->fetch(PDO::FETCH_BOUND)) {
            $empleado = new Empleado($nombre, $apellido, $dni, $idPuesto, $puesto, $baja, $id);
        }
        
        return $empleado;
    }

    public static function GetEmpleadosPorPuesto($idPuesto)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT e.*, p.nombre AS puesto FROM empleados e LEFT JOIN puestos p ON e.idPuesto = p.id WHERE e.idPuesto = :idPuesto AND e.baja = 0';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':idPuesto', $idPuesto, PDO::PARAM_INT);
        $consulta->execute();
        
        $consulta->bindColumn('id', $id, PDO::PARAM_INT);
        $consulta->bindColumn('nombre', $nombre, PDO::PARAM_STR);
        $consulta->bindColumn('apellido', $apellido, PDO::PARAM_STR);
        $consulta->bindColumn('dni', $dni, PDO::PARAM_STR);
        $consulta->bindColumn('idPuesto', $idPuesto, PDO::PARAM_INT);
        $consulta->bindColumn('puesto', $puesto, PDO::PARAM_STR);
        $consulta->bindColumn('baja', $baja, PDO::PARAM_BOOL);

        $empleado = null;
        $empleados = [];
        while ($fila = $consulta->fetch(PDO::FETCH_BOUND)) {
            $empleado = new Empleado($nombre, $apellido, $dni, $idPuesto, $puesto, $baja, $id);
            array_push($empleados, $empleado);
        }
        return $empleados;
    }

    public function jsonSerialize(){
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'dni' => $this->dni,
            'puesto' => $this->puesto
        ];
    }
}

?>