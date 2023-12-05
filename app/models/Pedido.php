<?php

require_once './db/AccesoDatos.php';
require_once './models/ItemPedido.php';
require_once './utils/utils.php';
require_once './enums/EEstadosPedido.php';

class Pedido implements JsonSerializable
{

    public $id;
    public $codigo;
    public $nombreCliente;
    public $codigoMesa;
    public $estado;
    public $idEstado;
    public $items;
    public $idMozo;
    public $total;
    public $tiempoEstimado;
    public $horaFinalizado;
    public $resenia;
    public $puntajeMozo;
    public $puntajeMesa;
    public $puntajeRestaurante;
    public $fechaAlta;
    public $fechaModificacion;
    public $fechaCancelacion;
    public $imagen;
    //public $horaPago;//ver, si tendria que poner a la hora que se cobra al cliente
    
    
    public function __construct($codigo, $nombreCliente, $codigoMesa, $idEstado, $estado, $idMozo, $total = 0, $tiempoEstimado = null, $horaFinalizado = null,
    $resenia = null, $puntajeMozo = null, $puntajeMesa = null, $puntajeRestaurante = null, $fechaAlta = null, $fechaModificacion = null, $fechaCancelacion = null, 
    $id = null, $imagen = null, $items = []) {
        $this->id = $id;
        $this->codigo = $codigo;
        $this->nombreCliente = $nombreCliente;
        $this->codigoMesa = $codigoMesa;
        $this->estado = $estado;
        $this->idEstado = intval($idEstado);
        $this->items = $items;
        $this->total = floatval($total);
        $this->tiempoEstimado = isset($tiempoEstimado) ? new DateTime($tiempoEstimado) : $tiempoEstimado;
        $this->horaFinalizado = isset($horaFinalizado) ? new DateTime($horaFinalizado) : $horaFinalizado;
        
        $this->resenia = $resenia;
        $this->puntajeMozo = $puntajeMozo;
        $this->puntajeMesa = $puntajeMesa;
        $this->puntajeRestaurante = $puntajeRestaurante;

        $this->fechaAlta = isset($fechaAlta) ? new DateTime($fechaAlta) : null;//date("Y-m-d H:i:s", strtotime($date))
        $this->fechaModificacion = isset($fechaModificacion) ? new DateTime($fechaModificacion) : null;
        $this->fechaCancelacion = isset($fechaCancelacion) ? new DateTime($fechaCancelacion) : null;
        $this->idMozo = intval($idMozo);
        $this->imagen = $imagen;
    }

    public function CalcularTotal() {
        $total = array_reduce($this->items, fn($carry, $item) => $carry + ($item->precioUnitario), 0);
        $this->total = $total;
    }

    public function GetFechaAlta() {
        return isset($this->fechaAlta) ? $this->fechaAlta->format('Y/m/d H:i:s') : null;
    }
    public function GetFechaModificacion() {
        return isset($this->fechaModificacion) ? $this->fechaModificacion->format('Y/m/d H:i:s') : null;
    }
    public function GetFechaCancelacion() {
        return isset($this->fechaCancelacion) ? $this->fechaCancelacion->format('Y/m/d H:i:s') : null;
    }
    public function GetTiempoEstimado() {
        return isset($this->tiempoEstimado) ? $this->tiempoEstimado->format('Y/m/d H:i:s') : null;
    }
    public function GetHoraFinalizado() {
        return isset($this->horaFinalizado) ? $this->horaFinalizado->format('Y/m/d H:i:s') : null;
    }

    private static function EjecutarQueryInsertar($consulta, $pedido) {
        
        
        $consulta->bindValue(':codigo', $pedido->codigo, PDO::PARAM_STR);
        $consulta->bindValue(':nombreCliente', $pedido->nombreCliente, PDO::PARAM_STR);
        $consulta->bindValue(':codigoMesa', $pedido->codigoMesa, PDO::PARAM_STR);
        $consulta->bindValue(':idEstado', $pedido->idEstado, PDO::PARAM_INT);
        $consulta->bindValue(':total', $pedido->total, PDO::PARAM_STR);//float
        $consulta->bindValue(':tiempoEstimado', $pedido->GetTiempoEstimado(), PDO::PARAM_STR);
        $consulta->bindValue(':horaFinalizado', $pedido->GetHoraFinalizado(), PDO::PARAM_STR);
        $consulta->bindValue(':idMozo', $pedido->idMozo, PDO::PARAM_INT);
        $consulta->bindValue(':resenia', $pedido->resenia, PDO::PARAM_STR);
        $consulta->bindValue(':puntajeMozo', $pedido->puntajeMozo, PDO::PARAM_INT);
        $consulta->bindValue(':puntajeMesa', $pedido->puntajeMesa, PDO::PARAM_INT);
        $consulta->bindValue(':puntajeRestaurante', $pedido->puntajeRestaurante, PDO::PARAM_INT);
        $consulta->bindValue(':imagen', $pedido->imagen, PDO::PARAM_STR);

        $consulta->execute();
    }

    public function CrearPedido()
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'INSERT INTO pedidos (codigo,nombreCliente,codigoMesa,idEstado,idMozo,total,tiempoEstimado,
        horaFinalizado,resenia, puntajeMozo, puntajeMesa,puntajeRestaurante,imagen,fechaAlta)
        VALUES(:codigo,:nombreCliente,:codigoMesa,:idEstado,:idMozo,:total,:tiempoEstimado,:horaFinalizado,
        :resenia, :puntajeMozo, :puntajeMesa,:puntajeRestaurante,:imagen,:fechaAlta)';
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
        $query = 'UPDATE pedidos SET codigo = :codigo,nombreCliente = :nombreCliente,codigoMesa = :codigoMesa,
        idEstado = :idEstado,idMozo = :idMozo,total = :total,fechaModificacion = :fechaModificacion,
        tiempoEstimado = :tiempoEstimado, horaFinalizado = :horaFinalizado, resenia = :resenia, puntajeMozo = :puntajeMozo, 
        puntajeMesa = :puntajeMesa, puntajeRestaurante = :puntajeRestaurante, imagen = :imagen WHERE id = :id';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);

        $fechaModificacion = date('Y/m/d H:i:s', strtotime("now"));
        $this->fechaModificacion = new DateTime($fechaModificacion);
        $consulta->bindValue(':fechaModificacion', $fechaModificacion, PDO::PARAM_STR);
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        self::EjecutarQueryInsertar($consulta, $this);
        
        return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }

    public function SetArchivoImagen($imagen) {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'UPDATE pedidos SET imagen = :imagen WHERE id = :id';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':imagen', $imagen, PDO::PARAM_STR);
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->execute();

        return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }

    private static function BindColumns($consulta) {
        $auxPedido = new stdClass();
        $consulta->bindColumn('id', $auxPedido->id, PDO::PARAM_INT);
        $consulta->bindColumn('codigo', $auxPedido->codigo, PDO::PARAM_STR);
        $consulta->bindColumn('nombreCliente', $auxPedido->nombreCliente, PDO::PARAM_STR);
        $consulta->bindColumn('codigoMesa', $auxPedido->codigoMesa, PDO::PARAM_STR);
        $consulta->bindColumn('idEstado', $auxPedido->idEstado, PDO::PARAM_INT);
        $consulta->bindColumn('estado', $auxPedido->estado, PDO::PARAM_STR);
        $consulta->bindColumn('idMozo', $auxPedido->idMozo, PDO::PARAM_INT);
        $consulta->bindColumn('total', $auxPedido->total, PDO::PARAM_STR);//float
        $consulta->bindColumn('tiempoEstimado', $auxPedido->tiempoEstimado, PDO::PARAM_STR);
        $consulta->bindColumn('horaFinalizado', $auxPedido->horaFinalizado, PDO::PARAM_STR);
        $consulta->bindColumn('resenia', $auxPedido->resenia, PDO::PARAM_STR);
        $consulta->bindColumn('puntajeMozo', $auxPedido->puntajeMozo, PDO::PARAM_INT);
        $consulta->bindColumn('puntajeMesa', $auxPedido->puntajeMesa, PDO::PARAM_INT);
        $consulta->bindColumn('puntajeRestaurante', $auxPedido->puntajeRestaurante, PDO::PARAM_INT);
        $consulta->bindColumn('fechaAlta', $auxPedido->fechaAlta, PDO::PARAM_STR);
        $consulta->bindColumn('fechaModificacion', $auxPedido->fechaModificacion, PDO::PARAM_STR);
        $consulta->bindColumn('fechaCancelacion', $auxPedido->fechaCancelacion, PDO::PARAM_STR);
        $consulta->bindColumn('imagen', $auxPedido->imagen, PDO::PARAM_STR);
        return $auxPedido;
    }

    private static function FetchQueryGetAll($consulta) {
        
        $p = self::BindColumns($consulta);

        $pedido = null;
        $pedidos = [];
        while ($fila = $consulta->fetch(PDO::FETCH_BOUND)) {
            $pedido = new Pedido($p->codigo, $p->nombreCliente, $p->codigoMesa, $p->idEstado, $p->estado, $p->idMozo,floatval($p->total), 
            $p->tiempoEstimado, $p->horaFinalizado, $p->resenia, $p->puntajeMozo, $p->puntajeMesa, $p->puntajeRestaurante, $p->fechaAlta, 
            $p->fechaModificacion, $p->fechaCancelacion, $p->id, $p->imagen);
            $pedido->items = ItemPedido::GetItems($pedido->id);
            array_push($pedidos, $pedido);
        }

        return $pedidos;
    }

    private static function FetchQueryGet($consulta) {

        $p = self::BindColumns($consulta);

        $pedido = null;
        if ($consulta->fetch(PDO::FETCH_BOUND)) {
            $pedido = new Pedido($p->codigo, $p->nombreCliente, $p->codigoMesa, $p->idEstado, $p->estado, $p->idMozo,floatval($p->total), 
            $p->tiempoEstimado, $p->horaFinalizado, $p->resenia, $p->puntajeMozo, $p->puntajeMesa, $p->puntajeRestaurante, $p->fechaAlta, 
            $p->fechaModificacion, $p->fechaCancelacion, $p->id, $p->imagen);
            $pedido->items = ItemPedido::GetItems($pedido->id);
        }

        return $pedido;
    }

    public static function GetPedidos()
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT p.*, e.estado FROM pedidos p LEFT JOIN estadosPedido e ON p.idEstado = e.id WHERE p.fechaCancelacion IS NULL';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->execute();

        return self::FetchQueryGetAll($consulta);
    }

    public static function GetPedido($idPedido)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT p.*, e.estado FROM pedidos p LEFT JOIN estadosPedido e ON p.idEstado = e.id WHERE p.id = :id AND p.fechaCancelacion IS NULL';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':id', $idPedido, PDO::PARAM_INT);
        $consulta->execute();
        
        return self::FetchQueryGet($consulta);
    }
    public static function GetPedidoPorCodigo($codigoPedido)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT p.*, e.estado FROM pedidos p LEFT JOIN estadosPedido e ON p.idEstado = e.id WHERE p.codigo = :codigoPedido AND p.fechaCancelacion IS NULL';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':codigoPedido', $codigoPedido, PDO::PARAM_STR);
        $consulta->execute();
        
        return self::FetchQueryGet($consulta);
    }
    //no usar, al final se envia el idPedido
    public static function GetPedidoPorMesaConClienteComiendo($idMesa)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT p.*, e.estado FROM pedidos p LEFT JOIN estadosPedido e ON p.idEstado = e.id LEFT JOIN mesas m ON p.codigoMesa = m.codigo 
        WHERE m.id = :id AND p.idEstado = 3 AND p.fechaCancelacion IS NULL AND p.horaFinalizado  = ( SELECT MAX(p.horaFinalizado) FROM pedidos WHERE p.fechaCancelacion IS NULL)';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':id', $idMesa, PDO::PARAM_INT);
        $consulta->execute();
        
        return self::FetchQueryGet($consulta);
    }

    public static function GetPedidosPorIdEstado($idEstado)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT p.*, e.estado FROM pedidos p LEFT JOIN estadosPedido e ON p.idEstado = e.id WHERE p.idEstado = :idEstado AND p.fechaCancelacion IS NULL';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':idEstado', $idEstado, PDO::PARAM_INT);
        $consulta->execute();
        
        return self::FetchQueryGetAll($consulta);
    }
    public static function GetPedidosPorPuntaje($puntaje, $esMayor = true)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT p.*, e.estado FROM pedidos p LEFT JOIN estadosPedido e ON p.idEstado = e.id WHERE p.fechaCancelacion IS NULL';
        
        $query .= $esMayor ? ' AND p.puntajeRestaurante > :puntaje' : ' AND p.puntajeRestaurante < :puntaje';
        $query .= ' ORDER BY p.puntajeRestaurante DESC LIMIT 20'; 
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':puntaje', $puntaje, PDO::PARAM_INT);
        $consulta->execute();
        
        $pedidos = self::FetchQueryGetAll($consulta);
        $resenias = array_map(fn($p) => $p->ToResenia(), $pedidos);
        return $resenias;
    }

    public static function GetPedidosPorTipoProducto($idTipoProducto, $idEstadoPedido = null)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT p.*, e.estado FROM pedidos p LEFT JOIN estadosPedido e ON p.idEstado = e.id 
        LEFT JOIN itemsPedido i ON i.idPedido = p.id LEFT JOIN productos pro ON i.idProducto = pro.id 
        WHERE p.fechaCancelacion IS NULL AND pro.idTipoProducto = :idTipoProducto';
        if (isset($idEstadoPedido)) {
            $query .= ' AND p.idEstado = :idEstadoPedido';
        }
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':idTipoProducto', $idTipoProducto, PDO::PARAM_INT);
        $consulta->bindValue(':idEstadoPedido', $idEstadoPedido, PDO::PARAM_INT);
        $consulta->execute();

        return self::FetchQueryGetAll($consulta);
    }

    public static function GetCodigoMesaConMasPedidos() {
        $retorno = null;
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT codigoMesa, COUNT(*) AS cantidad
        FROM pedidos
        WHERE fechaCancelacion IS NOT NULL
        GROUP BY codigoMesa
        ORDER BY cantidad DESC
        LIMIT 1';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->execute();
        $consulta->bindColumn('codigoMesa', $codigoMesa, PDO::PARAM_STR);
        $consulta->bindColumn('cantidad', $cantidad, PDO::PARAM_INT);

        if ($consulta->fetch(PDO::FETCH_BOUND)) {
            $retorno = [
                'codigoMesa' => $codigoMesa,
                'cantidad' => $cantidad
            ];
        }

        return $retorno;
    }
    
    public static function GetPedidosPorEntrega($fueraDeTiempo = false)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT p.*, e.estado FROM pedidos p LEFT JOIN estadosPedido e ON p.idEstado = e.id WHERE p.fechaCancelacion IS NULL';
        $query .= $fueraDeTiempo ? ' AND p.horaFinalizado > p.tiempoEstimado' : ' AND p.horaFinalizado < p.tiempoEstimado';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->execute();
        
        return self::FetchQueryGetAll($consulta);
    }


    //ver si tendria que tener fecha de baja tambien
    /*
    public static function BajaPedido($id) {
        $fechaBaja = date('Y/m/d H:i:s',strtotime("now"));
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'UPDATE pedidos SET fechaBaja = :fechaBaja WHERE id = :id';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->bindValue(':fechaBaja', $fechaBaja, PDO::PARAM_STR);
        $consulta->execute();

        return $fechaBaja;
    }*/
    
    public static function CancelarPedido($id) {
        $fechaCancelacion = date('Y/m/d H:i:s',strtotime("now"));
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'UPDATE pedidos SET fechaCancelacion = :fechaCancelacion WHERE id = :id';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->bindValue(':fechaCancelacion', $fechaCancelacion, PDO::PARAM_STR);
        $consulta->execute();

        return $fechaCancelacion;
    }

    public function ToResenia() {
        $arrPuntajes = [];
        foreach ($this->items as $item) {
            array_push($arrPuntajes, $item->ToResenia());
        }
        return [
            'resenia' => $this->resenia,
            'puntajeMozo' => $this->puntajeMozo,
            'puntajeMesa' => $this->puntajeMesa,
            'puntajeRestaurante' => $this->puntajeRestaurante,
            'puntajesItems' => $arrPuntajes

        ];
    }
    
    public function jsonSerialize(){
        $tiempoRestante = $this->tiempoEstimado instanceof DateTime ? CalcularTiempoRestante($this->tiempoEstimado) : null;
        $json = [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'nombreCliente' => $this->nombreCliente,
            'codigoMesa' => $this->codigoMesa,
            'estado' => $this->estado,
            'total' => $this->total,
            'tiempoEstimado' => $this->GetTiempoEstimado(),
            'horaFinalizado' => $this->GetHoraFinalizado(),
            'items' => $this->items
        ];
        if ($this->idEstado != EstadosPedido::ListoParaServir->value) {
            $tiempoRestante = $this->tiempoEstimado instanceof DateTime ? CalcularTiempoRestante($this->tiempoEstimado) : null;
            $json['tiempoRestante'] = $tiempoRestante;
        }
        
        if (isset($this->fechaCancelacion)) {
            $json['fechaCancelacion'] = $this->GetFechaCancelacion();
        }
        return $json;
    }
}

?>