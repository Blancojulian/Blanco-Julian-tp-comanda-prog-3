<?php

require_once './db/AccesoDatos.php';
require_once './utils/utils.php';

class ItemPedido implements JsonSerializable
{
    public $idProducto;
    public $producto;
    public $precioUnitario;
    public $idEstado;
    public $estado;
    public $idEmpleado;
    public $tiempoEstimado;
    public $horaFinalizado;
    public $puntaje;
    public $idTipoProducto;//con este atributo controlar que corresponda de que se encarga cada empleado
    
    public function __construct($idProducto, $producto, $precioUnitario, $idTipoProducto, $idEstado, $estado, $idEmpleado = null, $tiempoEstimado = null, $horaFinalizado = null, $puntaje = null, $id = null) {
        
        $this->idProducto = $idProducto;
        $this->producto = $producto;
        $this->precioUnitario = floatval($precioUnitario);
        $this->idTipoProducto = $idTipoProducto;
        $this->idEstado = $idEstado;
        $this->estado = $estado;
        $this->idEmpleado = $idEmpleado;
        $this->tiempoEstimado = $tiempoEstimado;
        $this->horaFinalizado = $horaFinalizado;
        $this->puntaje = $puntaje;
        $this->id = $id;

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
    public function GetTiempoEstimado() {
        return isset($this->tiempoEstimado) ? $this->tiempoEstimado->format('Y/m/d H:i:s') : null;
    }
    public function GetHoraFinalizado() {
        return isset($this->horaFinalizado) ? $this->horaFinalizado->format('Y/m/d H:i:s') : null;
    }
    private static function EjecutarQueryInsertar($consulta, $idPedido, $item) {
        
        $consulta->bindValue(':idPedido', intval($idPedido), PDO::PARAM_INT);
        $consulta->bindValue(':idProducto', $item->idProducto, PDO::PARAM_INT);
        $consulta->bindValue(':precioUnitario', $item->precioUnitario, PDO::PARAM_STR);//ver sino tirar error pq es float
        $consulta->bindValue(':idEstado', $item->idEstado, PDO::PARAM_INT);
        $consulta->bindValue(':idEmpleado', $item->idEmpleado, PDO::PARAM_INT);
        $consulta->bindValue(':tiempoEstimado', $item->GetTiempoEstimado(), PDO::PARAM_STR);
        $consulta->bindValue(':horaFinalizado', $item->GetHoraFinalizado(), PDO::PARAM_STR);
        $consulta->bindValue(':puntaje', $item->puntaje, PDO::PARAM_INT);
        echo 'ola';
        
        $consulta->execute();
    }
    //no se va a usar siempre se va insertar un array de pedidos y para modificar se eliminan los anteriores y agregan los nuevos
    //por ahora
    //no hacer esto, ahora tienen id
    public static function CrearItem($idPedido, ItemPedido $item)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'INSERT INTO itemsPedido (idPedido,idProducto,precioUnitario,idEstado,idEmpleado,tiempoEstimado,horaFinalizado,puntaje)
        VALUES(:idPedido,:idProducto,:precioUnitario,:idEstado,:idEmpleado,:tiempoEstimado,:horaFinalizado,:puntaje)';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        self::EjecutarQueryInsertar($consulta, $idPedido, $item);

        $this->id = $objetoAccesoDato->RetornarUltimoIdInsertado();;
        return $this->id;
    }

    public static function MofidicarItem($idPedido, ItemPedido $item)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'UPDATE itemsPedido SET idPedido = :idPedido, idProducto = :idProducto, 
        precioUnitario = :precioUnitario, idEstado = :idEstado, idEmpleado = :idEmpleado, 
        tiempoEstimado = :tiempoEstimado, horaFinalizado = :horaFinalizado, 
        puntaje = :puntaje WHERE id = :id';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        self::EjecutarQueryInsertar($consulta, $idPedido, $item);
        
        return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }

    public static function CrearItems($idPedido, array $items)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'INSERT INTO itemsPedido (idPedido,idProducto,precioUnitario,idEstado,idEmpleado,tiempoEstimado,horaFinalizado,puntaje)
        VALUES(:idPedido,:idProducto,:precioUnitario,:idEstado,:idEmpleado,:tiempoEstimado,:horaFinalizado,:puntaje)';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        
        foreach ($items as $item) {
            self::EjecutarQueryInsertar($consulta, $idPedido, $item);
        }
    }

    private static function BindColumns($consulta) {
        $aux = new stdClass();
        $consulta->bindColumn('id', $aux->id, PDO::PARAM_INT);
        $consulta->bindColumn('idProducto', $aux->idProducto, PDO::PARAM_INT);
        $consulta->bindColumn('producto', $aux->producto, PDO::PARAM_STR);
        $consulta->bindColumn('precioUnitario', $aux->precioUnitario, PDO::PARAM_STR);//parsear a float
        $consulta->bindColumn('idTipoProducto', $aux->idTipoProducto, PDO::PARAM_INT);
        $consulta->bindColumn('idEstado', $aux->idEstado, PDO::PARAM_INT);
        $consulta->bindColumn('nombreEstado', $aux->estado, PDO::PARAM_STR);
        $consulta->bindColumn('idEmpleado', $aux->idEmpleado, PDO::PARAM_INT);
        $consulta->bindColumn('tiempoEstimado', $aux->tiempoEstimado, PDO::PARAM_STR);
        $consulta->bindColumn('horaFinalizado', $aux->horaFinalizado, PDO::PARAM_STR);
        $consulta->bindColumn('puntaje', $aux->puntaje, PDO::PARAM_INT);
        return $aux;
    }

    private static function FetchQueryGetAll($consulta) {
        
        $aux = self::BindColumns($consulta);
        $item = null;
        $items = [];
        while ($fila = $consulta->fetch(PDO::FETCH_BOUND)) {
            //var_dump($aux);
            $item = new ItemPedido($aux->idProducto, $aux->producto, $aux->precioUnitario, $aux->idTipoProducto, $aux->idEstado, $aux->estado, $aux->idEmpleado, $aux->tiempoEstimado, $aux->horaFinalizado, $aux->puntaje, $aux->id);
            array_push($items, $item);
        }

        return $items;
    }

    private static function FetchQueryGet($consulta) {
        
        $aux = self::BindColumns($consulta);

        $item = null;
        if ($consulta->fetch(PDO::FETCH_BOUND)) {
            $item = new ItemPedido($aux->idProducto, $aux->producto, $aux->precioUnitario, $aux->idTipoProducto, $aux->idEstado, $aux->estado, $aux->idEmpleado, $aux->tiempoEstimado, $aux->horaFinalizado, $aux->puntaje, $aux->id);
        }

        return $item;
    }

    public static function GetItems($idPedidoSolicitado)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT i.*, p.nombre AS producto, p.idTipoProducto AS idTipoProducto, e.estado AS nombreEstado FROM itemsPedido i LEFT JOIN productos p ON i.idProducto = p.id LEFT JOIN estadosPedido e ON i.idEstado = e.id WHERE i.idPedido = :idPedido';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':idPedido', $idPedidoSolicitado, PDO::PARAM_INT);
        $consulta->execute();

        //$consulta->bindColumn('idPedido', $idPedido, PDO::PARAM_INT);

        return self::FetchQueryGetAll($consulta);
    }

    public static function GetItem($idItem)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT i.*, p.nombre AS producto, p.idTipoProducto AS idTipoProducto, e.estado AS nombreEstado FROM itemsPedido i LEFT JOIN productos p ON i.idProducto = p.id LEFT JOIN estadosPedido e ON i.idEstado = e.id WHERE i.id = :id';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':id', $idItem, PDO::PARAM_INT);
        $consulta->execute();

        return self::FetchQueryGet($consulta);
    }
    //no sirve, falta idPedido
    public static function GetItemPorTipoProducto($idTipoProducto, $idEstado = 1)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT i.*, p.nombre AS producto, p.idTipoProducto AS idTipoProducto, e.estado AS nombreEstado FROM itemsPedido i LEFT JOIN productos p ON i.idProducto = p.id LEFT JOIN estadosPedido e ON i.idEstado = e.id WHERE p.idTipoProducto = :idTipoProducto AND i.idEstado = :idEstado LIMIT 1';
        
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':idTipoProducto', $idTipoProducto, PDO::PARAM_INT);
        $consulta->bindValue(':idEstado', $idEstado, PDO::PARAM_INT);
        $consulta->execute();

        return self::FetchQueryGet($consulta);
    }

    public static function GetItemsPorTipoProducto($idTipoProducto, $idEstado = 1)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT i.*, p.nombre AS producto, p.idTipoProducto AS idTipoProducto, e.estado AS nombreEstado FROM itemsPedido i LEFT JOIN productos p 
        ON i.idProducto = p.id LEFT JOIN estadosPedido e ON i.idEstado = e.id 
        WHERE p.idTipoProducto = :idTipoProducto AND i.idEstado = :idEstado';
        
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':idTipoProducto', $idTipoProducto, PDO::PARAM_INT);
        $consulta->bindValue(':idEstado', $idEstado, PDO::PARAM_INT);
        $consulta->execute();

        return self::FetchQueryGetAll($consulta);
    }

    public static function GetItemsPorIdEmpleado($idEmpleado)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT i.*, p.nombre AS producto, p.idTipoProducto AS idTipoProducto, e.estado AS nombreEstado FROM itemsPedido i LEFT JOIN productos p 
        ON i.idProducto = p.id LEFT JOIN estadosPedido e ON i.idEstado = e.id WHERE i.idEmpleado = :idEmpleado';
        
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':idEmpleado', $idEmpleado, PDO::PARAM_INT);
        $consulta->execute();

        return self::FetchQueryGetAll($consulta);
    }

    //ver si usar o enviar directamente el idProducto desde el postman
    public static function ConvertirAArrayItems(array $data = []) {
        $item = null;
        $items = [];
        $producto = null;
        $idEstado = 1;
        $estado = 'pendiente';

        foreach ($data as $i) {
            $producto = Producto::GetProducto($i['idProducto']);
            if (!isset($producto)) {
                throw new Exception("No existe producto id ". $i['idProducto']);
                
            }
            $idEmpleado = isset($i['idEmpleado']) ? $i['idEmpleado'] : null;
            $id = isset($i['id']) ? $i['id'] : null;

            if (isset($i['cantidad'])) {
                $cantidad = intval($i['cantidad']);
                for ($i=1; $i <= $cantidad; $i++) { 
                    $item = new ItemPedido($producto->id, $producto->nombre, $producto->precio, $producto->idTipoProducto, $idEstado, $estado, $idEmpleado, $id);
                    array_push($items, $item);
                }
            } else {
                $item = new ItemPedido($producto->id, $producto->nombre, $producto->precio, $producto->idTipoProducto, $idEstado, $estado, $idEmpleado, $id);
                array_push($items, $item);
            }

            
        }
        return $items;
    }

    public static function ControlarTipoProductoDelItem($idItem, $idTipoProducto) {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT CASE WHEN p.idTipoProducto = :idTipoProducto THEN 1 ELSE 0 END AS resultado 
        FROM itemsPedido i LEFT JOIN productos p ON i.idProducto = p.id WHERE i.id = :id';// AND p.idTipoProducto = :idTipoProducto';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':id', $idItem, PDO::PARAM_INT);
        $consulta->bindValue(':idTipoProducto', $idTipoProducto, PDO::PARAM_INT);
        $consulta->execute();

        $consulta->bindColumn('resultado', $resultado, PDO::PARAM_BOOL);
        $consulta->fetch(PDO::FETCH_BOUND);
        return $resultado;
    }

    public static function EliminarItems($idPedido)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'DELETE FROM itemsPedido WHERE idPedido = :idPedido';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':idPedido', $idPedido, PDO::PARAM_INT);
        return $consulta->execute();
    }
    public static function EliminarItem($idItem)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'DELETE FROM itemsPedido WHERE id = :id';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':id', $idItem, PDO::PARAM_INT);
        return $consulta->execute();
    }
    
    public static function SetEstadoItemsPorTipoProducto($idPedido, $idEstado, $idTipoProducto)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'DELETE FROM itemsPedido WHERE idPedido = :idPedido';
        $query = 'UPDATE itemsPedido i LEFT JOIN productos p ON i.idProducto = p.id SET i.idEstado=:idEstado WHERE i.idPedido = :idPedido AND p.idTipoProducto=:idTipoProducto';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':idPedido', $idPedido, PDO::PARAM_INT);
        $consulta->bindValue(':idEstado', $idEstado, PDO::PARAM_INT);
        $consulta->bindValue(':idTipoProducto', $idTipoProducto, PDO::PARAM_INT);

        return $consulta->execute();
    }
    public function SetEstadoItem($id, $idEstado)
    {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'UPDATE itemsPedido SET idEstado = :idEstado WHERE id = :id';

        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->bindValue(':idEstado', $idEstado, PDO::PARAM_INT);

        return $consulta->execute();
    }
    public function ToResenia() {
        return [
            'idProducto' => $this->idProducto,
            'producto' => $this->producto,
            'puntaje' => $this->puntaje,
            'idEmpleado' => $this->idEmpleado
        ];
    }
    public function jsonSerialize(){
        return [
            'id' => $this->id,
            'idProducto' => $this->idProducto,
            'producto' => $this->producto,
            'precioUnitario' => $this->precioUnitario,
            'estado' => $this->estado,
            'idEmpleado' => $this->idEmpleado


        ];
    }
}

?>