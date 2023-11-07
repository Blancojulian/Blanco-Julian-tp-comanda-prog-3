<?php

require_once './db/AccesoDatos.php';

class EstadoPedido {

    public int $id;
    public string $estado;

    public function __construct($estado, $id = null) {
        $this->id = $id;
        $this->estado = $estado;
    }

    public function CrearEstado() {
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'INSERT INTO estadosPedido (estado) VALUES (:estado) ';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->execute();
        return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }
 
    public static function GetEstadoPorNombreEstado($strEstado) {
        
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT id, estado FROM estadosPedido WHERE estado = :estado';
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':estado', $strEstado, PDO::PARAM_STR);
        $consulta->execute();
        
        $consulta->bindColumn('id', $id, PDO::PARAM_INT);
        $consulta->bindColumn('estado', $estado, PDO::PARAM_STR);
        $estadoPedido = null;

        if ($data = $consulta->fetch(PDO::FETCH_BOUND)) {
            $estadoPedido = new EstadoPedido($estado, $id);
        }
        
        return $estadoPedido;
    }

    public static function GetEstadoPorId($idEstado) {
        
        $objetoAccesoDato = AccesoDatos::getObjetoAcceso();
        $query = 'SELECT id, estado FROM estadosPedido WHERE id = :id';        
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':id', $idEstado, PDO::PARAM_INT);
        $consulta->execute();
        
        $consulta->bindColumn('id', $id, PDO::PARAM_INT);
        $consulta->bindColumn('estado', $estado, PDO::PARAM_STR);
        $estadoPedido = null;

        if ($consulta->fetch(PDO::FETCH_BOUND)) {
            $estadoPedido = new EstadoPedido($estado, $id);
        }
        
        return $estadoPedido;
    }
}

?>