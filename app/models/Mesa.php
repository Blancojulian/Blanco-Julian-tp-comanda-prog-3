<?php

require_once './db/AccesoDatos.php';
require_once './utils/utils.php';

class Mesa implements JsonSerializable
{
    public $id;
    public $codigo;
    public $estado;
    public $idEstado;
    public $fechaAlta;
    public $fechaModificacion;
    public $fechaBaja;

    public const CANT_MAX_MESAS = 10;
    
    public function __construct($codigo, $idEstado, $estado, $fechaAlta = null, $fechaModificacion = null, $fechaBaja = null, $id = null) {
        $this->id = $id;
        $this->codigo = $codigo;
        $this->idEstado = $idEstado;
        $this->estado = $estado;
        $this->fechaAlta = isset($fechaAlta) ? new DateTime($fechaAlta) : null;//date("Y-m-d H:i:s", strtotime($date))
        $this->fechaModificacion = isset($fechaModificacion) ? new DateTime($fechaModificacion) : null;
        $this->fechaBaja = isset($fechaBaja) ? new DateTime($fechaBaja) : null;
        
    }

    private static function EjecutarQueryInsertar($consulta, $mesa) {
        $consulta->bindValue(':codigo', $mesa->codigo, PDO::PARAM_STR);
        $consulta->bindValue(':idEstado', $mesa->idEstado, PDO::PARAM_INT);

        $consulta->execute();
    }

    public function CrearMesa()
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'INSERT INTO mesas (codigo,idEstado,fechaAlta) VALUES (:codigo,:idEstado,:fechaAlta)';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        
        $fechaAlta = date('Y/m/d H:i:s', strtotime("now"));
        $this->fechaAlta = new DateTime($fechaAlta);
        $consulta->bindValue(':fechaAlta', $fechaAlta, PDO::PARAM_STR);

        self::EjecutarQueryInsertar($consulta, $this);
        $this->id = $objetoAccesoDato->RetornarUltimoIdInsertado();
        return $this->id;
    }

    public function ModificarMesa()
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'UPDATE mesas SET codigo = :codigo, idEstado = :idEstado, fechaModificacion = :fechaModificacion WHERE id = :id';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);

        $fechaModificacion = date('Y/m/d H:i:s', strtotime("now"));
        $this->fechaModificacion = new DateTime($fechaModificacion);
        $consulta->bindValue(':fechaModificacion', $fechaModificacion, PDO::PARAM_STR);
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);

        self::EjecutarQueryInsertar($consulta, $this);
        return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }

    private static function BindColumns($consulta) {
        $aux = new stdClass();

        $consulta->bindColumn('id', $aux->id, PDO::PARAM_INT);
        $consulta->bindColumn('codigo', $aux->codigo, PDO::PARAM_STR);
        $consulta->bindColumn('idEstado', $aux->idEstado, PDO::PARAM_INT);
        $consulta->bindColumn('estado', $aux->estado, PDO::PARAM_STR);
        $consulta->bindColumn('fechaAlta', $aux->fechaAlta, PDO::PARAM_STR);
        $consulta->bindColumn('fechaModificacion', $aux->fechaModificacion, PDO::PARAM_STR);
        $consulta->bindColumn('fechaBaja', $aux->fechaBaja, PDO::PARAM_STR);

        return $aux;
    }

    private static function FetchQueryGetAll($consulta) {
        $aux = self::BindColumns($consulta);

        $mesa = null;
        $mesas = [];
        while ($fila = $consulta->fetch(PDO::FETCH_BOUND)) {
            $mesa = new Mesa($aux->codigo, $aux->idEstado, $aux->estado, $aux->fechaAlta, $aux->fechaModificacion, $aux->fechaBaja, $aux->id);
            array_push($mesas, $mesa);
        }
        return $mesas;
    }

    private static function FetchQueryGet($consulta) {
        $aux = self::BindColumns($consulta);

        $mesa = null;

        if ($consulta->fetch(PDO::FETCH_BOUND)) {
            $mesa = new Mesa($aux->codigo, $aux->idEstado, $aux->estado, $aux->fechaAlta, $aux->fechaModificacion, $aux->fechaBaja, $aux->id);
        }
        
        return $mesa;
    }

    public static function GetMesas()
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT m.*, e.estado FROM mesas m LEFT JOIN estadosMesa e ON m.idEstado = e.id WHERE m.fechaBaja IS NULL';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->execute();

        return self::FetchQueryGetAll($consulta);
    }
    
    public static function GetMesa($id)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT m.*, e.estado FROM mesas m LEFT JOIN estadosMesa e ON m.idEstado = e.id WHERE m.id = :id AND m.fechaBaja IS NULL';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
        
        return self::FetchQueryGet($consulta);
    }

    public static function GetMesaPorCodigo($codigo)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT m.*, e.estado FROM mesas m LEFT JOIN estadosMesa e ON m.idEstado = e.id WHERE m.codigo = :codigo AND m.fechaBaja IS NULL';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':codigo', $codigo, PDO::PARAM_STR);
        $consulta->execute();
        
        return self::FetchQueryGet($consulta);
    }
    public static function GetMesasPorEstado($idEstado)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT m.*, e.estado AS estado FROM mesas m LEFT JOIN estadosMesa e ON m.idEstado = e.id WHERE m.idEstado = :idEstado AND m.fechaBaja IS NULL';
        
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':idEstado', $idEstado, PDO::PARAM_INT);
        $consulta->execute();

        return self::FetchQueryGetAll($consulta);
    }

    public static function GetMesaTotalFacturadoPorFechas($codigoMesa, $fechaInicio, $fechaFinal)
    {
        $retorno = 0;
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT SUM(total) AS total_mesa FROM pedidos WHERE (DATE(fechaAlta) BETWEEN DATE(:fechaInicio) AND DATE(:fechaFinal)) AND idMesa = :idMesa AND fechaCancelacion IS NULL';
        
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':idMesa', $codigoMesa, PDO::PARAM_STR);
        $consulta->bindValue(':fechaInicio', $fechaInicio, PDO::PARAM_STR);
        $consulta->bindValue(':fechaFinal', $fechaFinal, PDO::PARAM_STR);
        $consulta->execute();
        $consulta->bindColumn('total_mesa', $total, PDO::PARAM_STR);

        if ($consulta->fetch(PDO::FETCH_BOUND)) {
            $retorno = floatval($total);
        }

        return $retorno;
    }
//punto 21, creo que listo, ver si se tendria que hacer la consulta con un inner join a pedidos
    public static function GetMesasSegunFacturacion() {
        $retorno = null;
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        
        $query = 'SELECT m.*, e.estado, MAX(p.total) AS totalMesa
        FROM mesas m LEFT JOIN estadosMesa e ON m.idEstado = e.id LEFT JOIN pedidos p ON p.idMesa = m.id
        WHERE p.fechaCancelacion IS NULL
        GROUP BY m.id HAVING MAX(p.total) IS NOT NULL
        ORDER BY totalMesa ASC';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->execute();
        $consulta->bindColumn('totalMesa', $totalMesa, PDO::PARAM_STR);

        $aux = self::BindColumns($consulta);

        $mesa = null;
        $mesas = [];
        while ($fila = $consulta->fetch(PDO::FETCH_BOUND)) {
            $mesa = new Mesa($aux->codigo, $aux->idEstado, $aux->estado, $aux->fechaAlta, $aux->fechaModificacion, $aux->fechaBaja, $aux->id);
            array_push($mesas, ['facturado' => floatval($totalMesa), 'mesa' => $mesa]);
        }
        return $mesas;
    }


    public static function BajaMesa($id) {
        $fechaBaja = date('Y/m/d H:i:s',strtotime("now"));
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'UPDATE mesas SET fechaBaja = :fechaBaja WHERE id = :id';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->bindValue(':fechaBaja', $fechaBaja, PDO::PARAM_STR);
        $consulta->execute();

        return $fechaBaja;
    }
    
    public function jsonSerialize(){
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'estado' => $this->estado
        ];
    }
}

?>