<?php

require_once './db/AccesoDatos.php';
require_once './models/ItemPedido.php';
require_once './utils/utils.php';
require_once './enums/EEstadosPedido.php';

class Encuesta implements JsonSerializable
{
    
    public $id;
    public $idPedido;
    public $resenia;
    public $puntajeRestaurante;
    public $puntajeMozo;
    public $puntajeMesa;
    public $items;
    public $fechaAlta;
    public $fechaModificacion;
    public $fechaBaja;
    
    public function __construct($idPedido, $resenia, $puntajeRestaurante, $puntajeMozo, $puntajeMesa, $fechaAlta = null, $fechaModificacion = null, $fechaBaja = null, $id = null, $items = []) {
        $this->id = $id;
        $this->idPedido = intval($idPedido);
        $this->resenia = $resenia;
        $this->puntajeRestaurante = intval($puntajeRestaurante);
        $this->puntajeMozo = intval($puntajeMozo);
        $this->puntajeMesa = intval($puntajeMesa);
        $this->items = $items;

        $this->fechaAlta = isset($fechaAlta) ? new DateTime($fechaAlta) : null;//date("Y-m-d H:i:s", strtotime($date))
        $this->fechaModificacion = isset($fechaModificacion) ? new DateTime($fechaModificacion) : null;
        $this->fechaBaja = isset($fechaBaja) ? new DateTime($fechaBaja) : null;
    }

    public function GetFechaAlta() {
        return isset($this->fechaAlta) ? $this->fechaAlta->format('Y/m/d H:i:s') : null;
    }
    public function GetFechaModificacion() {
        return isset($this->fechaModificacion) ? $this->fechaModificacion->format('Y/m/d H:i:s') : null;
    }
    public function GetFechaBaja() {
        return isset($this->fechaBaja) ? $this->fechaBaja->format('Y/m/d H:i:s') : null;
    }

    private static function EjecutarQueryInsertar($consulta, $pedido) {
        
        $consulta->bindValue(':idPedido', $pedido->idPedido, PDO::PARAM_INT);
        $consulta->bindValue(':resenia', $pedido->resenia, PDO::PARAM_STR);
        $consulta->bindValue(':puntajeRestaurante', $pedido->puntajeRestaurante, PDO::PARAM_INT);
        $consulta->bindValue(':puntajeMozo', $pedido->puntajeMozo, PDO::PARAM_INT);
        $consulta->bindValue(':puntajeMesa', $pedido->puntajeMesa, PDO::PARAM_INT);

        $consulta->execute();
    }

    public function CrearEncuesta()
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'INSERT INTO encuestas (idPedido,resenia,puntajeRestaurante,puntajeMozo,puntajeMesa,fechaAlta)
        VALUES(:idPedido,:resenia,:puntajeRestaurante,:puntajeMozo,:puntajeMesa,:fechaAlta)';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);

        $fechaAlta = date('Y/m/d H:i:s', strtotime("now"));
        $this->fechaAlta = new DateTime($fechaAlta);
        $consulta->bindValue(':fechaAlta', $fechaAlta, PDO::PARAM_STR);
        self::EjecutarQueryInsertar($consulta, $this);

        $this->id = $objetoAccesoDato->RetornarUltimoIdInsertado();;
        return $this->id;
    }

    public function ModificarPedido()
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'UPDATE encuestas SET idPedido = :idPedido, resenia = :resenia, puntajeRestaurante = :puntajeRestaurante, 
        puntajeMozo = :puntajeMozo, puntajeMesa = :puntajeMesa, fechaModificacion = :fechaModificacion WHERE id = :id';
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
        $consulta->bindColumn('idPedido', $aux->idPedido, PDO::PARAM_INT);
        $consulta->bindColumn('resenia', $aux->resenia, PDO::PARAM_STR);
        $consulta->bindColumn('puntajeMozo', $aux->puntajeMozo, PDO::PARAM_INT);
        $consulta->bindColumn('puntajeMesa', $aux->puntajeMesa, PDO::PARAM_INT);
        $consulta->bindColumn('puntajeRestaurante', $aux->puntajeRestaurante, PDO::PARAM_INT);
        $consulta->bindColumn('fechaAlta', $aux->fechaAlta, PDO::PARAM_STR);
        $consulta->bindColumn('fechaModificacion', $aux->fechaModificacion, PDO::PARAM_STR);
        $consulta->bindColumn('fechaBaja', $aux->fechaBaja, PDO::PARAM_STR);
        return $aux;
    }
    
    private static function FetchQueryGetAll($consulta) {
        
        $p = self::BindColumns($consulta);

        $encuesta = null;
        $item = null;
        $encuestas = [];
        while ($fila = $consulta->fetch(PDO::FETCH_BOUND)) {
            $encuesta = new Encuesta($p->idPedido, $p->resenia, $p->puntajeRestaurante, $p->puntajeMozo, $p->puntajeMesa, 
            $p->fechaAlta, $p->fechaModificacion, $p->fechaBaja, $p->id);
            $items = ItemPedido::GetItems($p->idPedido);
            $encuesta->items = array_map(fn($i) => $i->ToResenia(), $items);
            array_push($encuestas, $encuesta);
        }

        return $encuestas;
    }

    private static function FetchQueryGet($consulta) {

        $p = self::BindColumns($consulta);

        $encuesta = null;
        if ($consulta->fetch(PDO::FETCH_BOUND)) {
            $encuesta = new Encuesta($p->idPedido, $p->resenia, $p->puntajeRestaurante, $p->puntajeMozo, $p->puntajeMesa, 
            $p->fechaAlta, $p->fechaModificacion, $p->fechaBaja, $p->id);
            $items = ItemPedido::GetItems($p->idPedido);
            $encuesta->items = array_map(fn($i) => $i->ToResenia(), $items);
        }

        return $encuesta;
    }

    public static function GetEncuestas()
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT * FROM encuestas WHERE fechaBaja IS NULL';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->execute();

        return self::FetchQueryGetAll($consulta);
    }

    public static function GetEncuesta($id)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT * FROM encuestas WHERE id = :id AND fechaBaja IS NULL';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
        
        return self::FetchQueryGet($consulta);
    }

    public static function GetEncuestaPorIdPedido($idPedido)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT * FROM encuestas WHERE idPedido = :idPedido AND fechaBaja IS NULL';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':idPedido', $idPedido, PDO::PARAM_INT);
        $consulta->execute();
        
        return self::FetchQueryGet($consulta);
    }

    public static function GetEncuestasPorPuntaje($puntaje, $esMayor = true)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT * FROM encuestas WHERE fechaBaja IS NULL';
        
        $query .= $esMayor ? ' AND puntajeRestaurante > :puntaje' : ' AND puntajeRestaurante < :puntaje';
        $query .= ' ORDER BY puntajeRestaurante DESC LIMIT 20'; 
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':puntaje', $puntaje, PDO::PARAM_INT);
        $consulta->execute();
        
        return self::FetchQueryGetAll($consulta);
    }
    
    public static function BajaEncuesta($id) {
        $fechaBaja = date('Y/m/d H:i:s',strtotime("now"));
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'UPDATE encuestas SET fechaBaja = :fechaBaja WHERE id = :id';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->bindValue(':fechaBaja', $fechaBaja, PDO::PARAM_STR);
        $consulta->execute();

        return $fechaBaja;
    }
    
    public function jsonSerialize(){
        return [
            'id' => $this->id,
            'idPedido' => $this->idPedido,
            'resenia' => $this->resenia,
            'puntajeRestaurante' => $this->puntajeRestaurante,
            'puntajeMozo' => $this->puntajeMozo,
            'puntajeMesa' => $this->puntajeMesa,
            'items' => $this->items
        ];
    }
}

?>