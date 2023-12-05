<?php

require_once './db/AccesoDatos.php';

class Empleado implements JsonSerializable
{
    public $id;
    public $nombre;
    public $apellido;
    public $dni;
    public $email;
    public $puesto;
    public $idPuesto;
    public $baja;
    public $fechaAlta;
    public $fechaModificacion;
    public $fechaBaja;
    public $fechaSuspension;
    private $contrasenia;
    
    public function __construct($nombre, $apellido, $dni, $email, $idPuesto, $puesto, $contrasenia, $fechaSuspension, $fechaAlta = null, $fechaModificacion = null, $fechaBaja = null, $id = null) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->dni = $dni;
        $this->email = $email;
        $this->idPuesto = $idPuesto;
        $this->puesto = $puesto;
        $this->contrasenia = $contrasenia;
        $this->fechaSuspension = isset($fechaSuspension) ? new DateTime($fechaSuspension) : null;
        $this->fechaAlta = isset($fechaAlta) ? new DateTime($fechaAlta) : null;//date("Y-m-d H:i:s", strtotime($date))
        $this->fechaModificacion = isset($fechaModificacion) ? new DateTime($fechaModificacion) : null;
        $this->fechaBaja = isset($fechaBaja) ? new DateTime($fechaBaja) : null;
    }

    public function GetFechaSuspension() {
        return isset($this->fechaSuspension) ? $this->fechaSuspension->format('Y/m/d H:i:s') : null;
    }

    private static function EjecutarQueryInsertar($consulta, $empleado) {
        //var_dump($empleado);
        $consulta->bindValue(':nombre', $empleado->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':apellido', $empleado->apellido, PDO::PARAM_STR);
        $consulta->bindValue(':dni', $empleado->dni, PDO::PARAM_STR);
        $consulta->bindValue(':email', $empleado->email, PDO::PARAM_STR);
        $consulta->bindValue(':idPuesto', $empleado->idPuesto, PDO::PARAM_INT);
        $consulta->bindValue(':contrasenia', $empleado->contrasenia, PDO::PARAM_STR);
        $consulta->bindValue(':fechaSuspension', $empleado->GetFechaSuspension(), PDO::PARAM_STR);
        $consulta->execute();
    }


    public function CrearEmpleado()
    {
        $objetoAccesoDatos = AccesoDatos::getObjetoAcceso();
        $query = 'INSERT INTO empleados (nombre, apellido, dni, email, idPuesto, contrasenia, fechaSuspension, fechaAlta) 
        VALUES (:nombre, :apellido, :dni, :email, :idPuesto, :contrasenia, :fechaSuspension, :fechaAlta)';
        $consulta = $objetoAccesoDatos->RetornarConsulta($query);
        
        $this->contrasenia = password_hash($this->contrasenia, PASSWORD_DEFAULT);
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
        $query = 'UPDATE empleados SET nombre = :nombre, apellido = :apellido, dni = :dni, email = :email, idPuesto = :idPuesto, 
        contrasenia = :contrasenia, fechaSuspension = :fechaSuspension fechaModificacion = :fechaModificacion WHERE id = :id';
        $consulta = $objetoAccesoDatos->RetornarConsulta($query);

        $fechaModificacion = date('Y/m/d H:i:s', strtotime("now"));
        $this->fechaModificacion = new DateTime($fechaModificacion);
        $consulta->bindValue(':fechaModificacion', $fechaModificacion, PDO::PARAM_STR);
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);

        self::EjecutarQueryInsertar($consulta, $this);
        return $objetoAccesoDatos->RetornarUltimoIdInsertado();
    }

    private static function BindColumns($consulta) {
        $aux = new stdClass();

        $consulta->bindColumn('id', $aux->id, PDO::PARAM_INT);
        $consulta->bindColumn('nombre', $aux->nombre, PDO::PARAM_STR);
        $consulta->bindColumn('apellido', $aux->apellido, PDO::PARAM_STR);
        $consulta->bindColumn('dni', $aux->dni, PDO::PARAM_STR);
        $consulta->bindColumn('email', $aux->email, PDO::PARAM_STR);
        $consulta->bindColumn('idPuesto', $aux->idPuesto, PDO::PARAM_INT);
        $consulta->bindColumn('puesto', $aux->puesto, PDO::PARAM_STR);
        $consulta->bindColumn('contrasenia', $aux->contrasenia, PDO::PARAM_STR);
        $consulta->bindColumn('fechaSuspension', $aux->fechaSuspension, PDO::PARAM_STR);
        $consulta->bindColumn('fechaAlta', $aux->fechaAlta, PDO::PARAM_STR);
        $consulta->bindColumn('fechaModificacion', $aux->fechaModificacion, PDO::PARAM_STR);
        $consulta->bindColumn('fechaBaja', $aux->fechaBaja, PDO::PARAM_STR);

        return $aux;
    }

    private static function FetchQueryGetAll($consulta) {
        $aux = self::BindColumns($consulta);

        $empleado = null;
        $empleados = [];
        while ($fila = $consulta->fetch(PDO::FETCH_BOUND)) {
            $empleado = new Empleado($aux->nombre, $aux->apellido, $aux->dni, $aux->email, $aux->idPuesto, $aux->puesto, $aux->contrasenia, $aux->fechaSuspension, $aux->fechaAlta, $aux->fechaModificacion, $aux->fechaBaja, $aux->id);
            array_push($empleados, $empleado);
        }
        return $empleados;
    }

    private static function FetchQueryGet($consulta) {
        $aux = self::BindColumns($consulta);

        $empleado = null;

        if ($consulta->fetch(PDO::FETCH_BOUND)) {
            $empleado = new Empleado($aux->nombre, $aux->apellido, $aux->dni, $aux->email, $aux->idPuesto, $aux->puesto, $aux->contrasenia, $aux->fechaSuspension, $aux->fechaAlta, $aux->fechaModificacion, $aux->fechaBaja, $aux->id);
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

    public static function GetEmpleadoPorEmail($email)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT e.*, p.nombre AS puesto FROM empleados e LEFT JOIN puestos p ON e.idPuesto = p.id WHERE e.email = :email AND e.fechaBaja IS NULL';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':email', $email, PDO::PARAM_STR);
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

    public static function BajaEmpleado($id) {
        $fechaBaja = date('Y/m/d H:i:s',strtotime("now"));
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'UPDATE empleados SET fechaBaja = :fechaBaja WHERE id = :id';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->bindValue(':fechaBaja', $fechaBaja, PDO::PARAM_STR);
        $consulta->execute();

        return $fechaBaja;
    }

    public static function SuspenderEmpleado($id) {
        $fechaSuspension = date('Y/m/d H:i:s',strtotime("now"));
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'UPDATE empleados SET fechaSuspension = :fechaSuspension WHERE id = :id';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->bindValue(':fechaSuspension', $fechaSuspension, PDO::PARAM_STR);
        $consulta->execute();

        return $fechaSuspension;
    }

    public static function ComprobarLogin($email, $contrasenia) {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        //$query = 'SELECT * FROM empleados WHERE email = :email AND fechaBaja IS NULL';
        $query = 'SELECT e.*, p.nombre AS puesto FROM empleados e LEFT JOIN puestos p ON e.idPuesto = p.id WHERE email = :email AND fechaBaja IS NULL';
        $retorno = null;
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':email', $email, PDO::PARAM_STR);
        $consulta->execute();

        $empleado = self::FetchQueryGet($consulta);

        if (isset($empleado) && password_verify($contrasenia, $empleado->contrasenia)) {
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