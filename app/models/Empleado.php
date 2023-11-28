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
    public $fechaAlta;
    public $fechaModificacion;
    public $fechaBaja;
    private $contrasenia;
    
    public function __construct($nombre, $apellido, $dni, $idPuesto, $puesto, $contrasenia, $fechaAlta = null, $fechaModificacion = null, $fechaBaja = null, $id = null) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->dni = $dni;
        $this->idPuesto = $idPuesto;
        $this->puesto = $puesto;
        $this->contrasenia = $contrasenia;
        $this->fechaAlta = isset($fechaAlta) ? new DateTime($fechaAlta) : null;//date("Y-m-d H:i:s", strtotime($date))
        $this->fechaModificacion = isset($fechaModificacion) ? new DateTime($fechaModificacion) : null;
        $this->fechaBaja = isset($fechaBaja) ? new DateTime($fechaBaja) : null;
    }

    private static function EjecutarQueryInsertar($consulta, $empleado) {
        $consulta->bindValue(':nombre', $empleado->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':apellido', $empleado->apellido, PDO::PARAM_STR);
        $consulta->bindValue(':dni', $empleado->dni, PDO::PARAM_STR);
        $consulta->bindValue(':idPuesto', $empleado->idPuesto, PDO::PARAM_INT);
        $consulta->bindValue(':contrasenia', $empleado->contrasenia, PDO::PARAM_STR);
        $consulta->execute();
    }
    private static function HashearContrasenia($contrasenia) {
        return password_hash($contrasenia, PASSWORD_DEFAULT);
    }


    public function CrearEmpleado()
    {
        $objetoAccesoDatos = AccesoDatos::getObjetoAcceso();
        $query = 'INSERT INTO empleados (nombre, apellido, dni, idPuesto, contrasenia, fechaAlta) VALUES (:nombre, :apellido, :dni, :idPuesto, :contrasenia, :fechaAlta)';
        $consulta = $objetoAccesoDatos->RetornarConsulta($query);
        
        $this->contrasenia = self::HashearContrasenia($this->contrasenia);
        $consulta = $objetoAccesoDatos->RetornarConsulta($query);
        $fechaAlta = date('Y/m/d H:i:s', strtotime("now"));
        $this->fechaAlta = new DateTime($fechaAlta);
        $consulta->bindValue(':fechaAlta', $fechaAlta, PDO::PARAM_STR);

        self::EjecutarQueryInsertar($consulta, $this);
        $this->id = $objetoAccesoDatos->RetornarUltimoIdInsertado();
        return $this->id;
    }
    public function ModificarEmpleado()
    {
        $objetoAccesoDatos = AccesoDatos::getObjetoAcceso();
        $query = 'UPDATE empleados SET nombre = :nombre, apellido = :apellido, dni = :dni, idPuesto = :idPuesto, 
        contrasenia = :contrasenia, fechaModificacion = :fechaModificacion WHERE id = :id';
        $consulta = $objetoAccesoDatos->RetornarConsulta($query);

        $fechaModificacion = date('Y/m/d H:i:s', strtotime("now"));
        $this->fechaModificacion = new DateTime($fechaModificacion);
        $consulta->bindValue(':fechaModificacion', $fechaModificacion, PDO::PARAM_STR);
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);

        self::EjecutarQueryInsertar($consulta, $this);
        return $objetoAccesoDatos->RetornarUltimoIdInsertado();
    }

    private static function FetchQueryGetAll($consulta) {
        $consulta->bindColumn('id', $id, PDO::PARAM_INT);
        $consulta->bindColumn('nombre', $nombre, PDO::PARAM_STR);
        $consulta->bindColumn('apellido', $apellido, PDO::PARAM_STR);
        $consulta->bindColumn('dni', $dni, PDO::PARAM_STR);
        $consulta->bindColumn('idPuesto', $idPuesto, PDO::PARAM_INT);
        $consulta->bindColumn('puesto', $puesto, PDO::PARAM_STR);
        $consulta->bindColumn('fechaAlta', $fechaAlta, PDO::PARAM_STR);
        $consulta->bindColumn('fechaModificacion', $fechaModificacion, PDO::PARAM_STR);
        $consulta->bindColumn('fechaBaja', $fechaBaja, PDO::PARAM_STR);

        $empleado = null;
        $empleados = [];
        while ($fila = $consulta->fetch(PDO::FETCH_BOUND)) {
            $empleado = new Empleado($nombre, $apellido, $dni, $idPuesto, $puesto, $fechaAlta, $fechaModificacion, $fechaBaja, $id);
            array_push($empleados, $empleado);
        }
        return $empleados;
    }

    private static function FetchQueryGet($consulta) {
        $consulta->bindColumn('id', $id, PDO::PARAM_INT);
        $consulta->bindColumn('nombre', $nombre, PDO::PARAM_STR);
        $consulta->bindColumn('apellido', $apellido, PDO::PARAM_STR);
        $consulta->bindColumn('dni', $dni, PDO::PARAM_STR);
        $consulta->bindColumn('idPuesto', $idPuesto, PDO::PARAM_INT);
        $consulta->bindColumn('puesto', $puesto, PDO::PARAM_STR);
        $consulta->bindColumn('baja', $baja, PDO::PARAM_BOOL);
        $consulta->bindColumn('fechaAlta', $fechaAlta, PDO::PARAM_STR);
        $consulta->bindColumn('fechaModificacion', $fechaModificacion, PDO::PARAM_STR);
        $consulta->bindColumn('fechaBaja', $fechaBaja, PDO::PARAM_STR);

        $empleado = null;

        if ($consulta->fetch(PDO::FETCH_BOUND)) {
            $empleado = new Empleado($nombre, $apellido, $dni, $idPuesto, $puesto, $fechaAlta, $fechaModificacion, $fechaBaja, $id);
        }
        
        return $empleado;
    }

    public static function GetEmpleados()
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT e.*, p.nombre AS puesto FROM empleados e LEFT JOIN puestos p ON e.idPuesto = p.id WHERE e.fechaBaja IS NULL';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->execute();
        
        return self::FetchQueryGetAll($consulta);
    }

    public static function GetEmpleado($id)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT e.*, p.nombre AS puesto FROM empleados e LEFT JOIN puestos p ON e.idPuesto = p.id WHERE e.id = :id AND e.fechaBaja IS NULL';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
        
        return self::FetchQueryGet($consulta);
    }

    public static function GetEmpleadoPorDni($dniEmpleado)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT e.*, p.nombre AS puesto FROM empleados e LEFT JOIN puestos p ON e.idPuesto = p.id WHERE e.dni = :dniEmpleado AND e.fechaBaja IS NULL';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':dniEmpleado', $dniEmpleado, PDO::PARAM_STR);
        $consulta->execute();
        
        return self::FetchQueryGet($consulta);
    }

    public static function GetEmpleadosPorPuesto($idPuesto)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT e.*, p.nombre AS puesto FROM empleados e LEFT JOIN puestos p ON e.idPuesto = p.id WHERE e.idPuesto = :idPuesto AND e.fechaBaja IS NULL';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':idPuesto', $idPuesto, PDO::PARAM_INT);
        $consulta->execute();
        
        return self::FetchQueryGetAll($consulta);
    }

    public static function ComprobarLogin($dni, $contrasenia) {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT * FROM empleados WHERE dni = :dni AND fechaBaja IS NULL';
        $retorno = null;
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':dni', $dni, PDO::PARAM_STR);
        $consulta->execute();

        $empleado = self::FetchQueryGet($consulta);

        if (isset($cliente) && password_verify($contrasenia, $empleado->contrasenia)) {
            $retorno = $empleado;
        }
        return $retorno;
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