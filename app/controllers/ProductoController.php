<?php

require_once './models/Producto.php';
require_once './models/TipoProducto.php';
require_once './utils/utils.php';
require_once './interfaces/IController.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpException;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

class ProductoController implements IController {


    public function Get(Request $req, Response $res, array $args = []) {
        if (!isset($args['id'])) {
            throw new HttpBadRequestException($req, 'Debe enviar Id');   
        }
        $id = $args['id'];
        if (!is_numeric($id)) {
            throw new HttpBadRequestException($req, 'Id debe ser un numero');   
        }
        $producto = Producto::GetProducto($id);
        if (!isset($producto)) {
            throw new HttpNotFoundException($req, 'Producto no existe');   
        }
        $res->getBody()->write(json_encode($producto));
        return $res; 
    }

    public function GetAll(Request $req, Response $res, array $args = []) {
        $productos = Producto::GetProductos();
        $res->getBody()->write(json_encode($productos));
        return $res; 
    }

    public function GetAllPorCriterio(Request $req, Response $res, array $args = []) {
        if (!isset($args['tipoProducto'])) {
            throw new HttpBadRequestException($req, 'Debe enviar el tipo de producto');
            
        }
        $tipoProducto = TipoProducto::GetTipoPorNombre($args['tipoProducto']);
        if (!isset($tipoProducto)) {
            throw new HttpBadRequestException($req, 'Tipo de producto invalido');
            
        }
        $productos = Producto::GetProductosPorIdTipo($tipoProducto->id);
        $res->getBody()->write(json_encode($productos));
        return $res;
    }

    public function Create(Request $req, Response $res, array $args = []) {//ver si no habria que pedir el estado, si es una mesa nueva no deberia estar cerrada
        $parametros = $req->getParsedBody();
        $codigo = generarCodigo(5);

        if (!isset($parametros['nombre']) || !isset($parametros['precio']) ||
        !isset($parametros['stock']) || !isset($parametros['tipoProducto'])) {
            throw new HttpBadRequestException($req, 'Debe enviar nombre, precio, stock y tipo de producto');
        }
        $tipoProducto = TipoProducto::GetTipoPorNombre($parametros['tipoProducto']);
        if (!isset($tipoProducto)) {
            throw new HttpBadRequestException($req, 'Tipo de producto invalido');
            
        }

        if (!is_numeric($parametros['precio']) || !is_numeric($parametros['stock'])) {
            throw new HttpBadRequestException($req, 'Precio y sotck debe ser numeros');
        }

        $producto = new Producto($parametros['nombre'], floatval($parametros['precio']), intval($parametros['stock']), $tipoProducto->id, $tipoProducto->nombre);

        $id = $producto->CrearProducto();
        $res->getBody()->write(json_encode(['mensaje' => "Producto creado", 'id' => $id]));

        return $res;
    }

    public function Delete(Request $req, Response $res, array $args = []) {
        if (!isset($args['id'])) {
            throw new HttpBadRequestException($req, 'Debe enviar Id del producto');   
        }
        $id = $args['id'];
        if (!is_numeric($id)) {
            throw new HttpBadRequestException($req, 'Id debe ser un numero');   
        }
        $producto = Producto::GetProducto($id);
        if (!isset($producto)) {
            throw new HttpNotFoundException($req, 'Producto no existe');   
        }
        $producto->baja = true;
        $producto->ModificarProducto();
        $res->getBody()->write(json_encode(['mensaje' => "Producto eliminado"]));
        return $res; 
    }

    public function Update(Request $req, Response $res, array $args = []) {
        $parametros = $req->getParsedBody();

        if (!isset($parametros['id']) || !isset($parametros['nombre']) || !isset($parametros['precio']) ||
        !isset($parametros['stock']) || !isset($parametros['tipoProducto'])) {
            throw new HttpBadRequestException($req, 'Debe enviar id, nombre, precio, stock y tipo de producto');
        }
        $tipoProducto = TipoProducto::GetTipoPorNombre($parametros['tipoProducto']);
        if (!isset($tipoProducto)) {
            throw new HttpBadRequestException($req, 'Tipo de producto invalido');
            
        }

        if (!is_numeric($parametros['precio']) || !is_numeric($parametros['stock'])) {
            throw new HttpBadRequestException($req, 'Precio y stock deben ser numeros');
        }

        $producto = Producto::GetProducto(intval($parametros['id']));
        
        if (!isset($producto)) {
            throw new HttpNotFoundException($req, 'Producto no existe');   
        }
        $producto->nombre = $parametros['nombre'];
        $producto->precio = floatval($parametros['precio']);
        $producto->stock = intval($parametros['stock']);
        $producto->idTipoProducto = $tipoProducto->id;
        $producto->tipoProducto = $tipoProducto->nombre;
        
        $producto->ModificarProducto();

        $res->getBody()->write(json_encode(['mensaje' => "Producto modificado"]));

        return $res;
    }
}

?>