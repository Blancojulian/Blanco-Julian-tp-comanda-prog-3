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
        
        $id = $args['id'];
        
        $producto = Producto::GetProducto($id);
        if (!isset($producto)) {
            throw new HttpNotFoundException($req, 'Producto no existe');   
        }
        $payload = json_encode($producto);
        $res->getBody()->write($payload);
        return $res; 
    }

    public function GetAll(Request $req, Response $res, array $args = []) {
        $productos = Producto::GetProductos();
        $payload = json_encode($productos);
        $res->getBody()->write($payload);
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

        $tipoProducto = TipoProducto::GetTipoPorNombre($parametros['tipoProducto']);

        //($nombre, $precio, $stock, $idTipoProducto, $tipoProducto, $fechaAlta = null, $fechaModificacion = null, $fechaBaja = null, $id = null) {

        $producto = new Producto($parametros['nombre'], floatval($parametros['precio']), intval($parametros['stock']), $tipoProducto->id, $tipoProducto->nombre);

        $id = $producto->CrearProducto();
        $res->getBody()->write(json_encode(['mensaje' => "Producto creado", 'id' => $id]));

        return $res;
    }

    public function Delete(Request $req, Response $res, array $args = []) {
        
        $id = $args['id'];
        
        $producto = Producto::GetProducto($id);
        if (!isset($producto)) {
            throw new HttpNotFoundException($req, 'Producto no existe');   
        }
        Producto::BajaProducto($producto->id);
        $res->getBody()->write(json_encode(['mensaje' => "Producto eliminado"]));
        return $res; 
    }

    public function Update(Request $req, Response $res, array $args = []) {
        $parametros = $req->getParsedBody();

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

        $payload = json_encode(['mensaje' => "Producto modificado"]);
        $res->getBody()->write($payload);

        return $res;
    }

    public function DescargarCsv(Request $req, Response $res, array $args = []) {
        $nombreArchivo = 'productos.csv';
        $productos = Producto::GetProductos();

        $titulos = "nombre,precio,stock,idTipoProducto,tipoProducto,fechaAlta,fechaModificacion,fechaBaja,id";
        //$res->getBody()->write($titulos) . PHP_EOL);

        foreach ($productos as $producto) {
            $res->getBody()->write($producto->ToCsvLine() . "\n");
        }

        return $res->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Content-Disposition', "attachment; filename=$nombreArchivo")
            ->withAddedHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->withHeader('Cache-Control', 'post-check=0, pre-check=0')
            ->withHeader('Pragma', 'no-cache');
            //->withBody((new \Slim\Psr7\Stream(fopen($nombreArchivo, 'rb'))));

    }
    
    public function SubirCsv(Request $req, Response $res, array $args = []) {
        $uploadedFiles = $req->getUploadedFiles();
        $titulos = "nombre,precio,stock,idTipoProducto,tipoProducto,fechaAlta,fechaModificacion,fechaBaja,id";
        
        if (!isset($uploadedFiles['archivo']) || $uploadedFiles['archivo']->getError() !== UPLOAD_ERR_OK) {
            throw new HttpBadRequestException($req, 'No se adjunto archivo de productos');
        }
        $ext = pathinfo($uploadedFiles['archivo']->getClientFilename(), PATHINFO_EXTENSION);
        if ($ext !== 'csv') {
            throw new HttpBadRequestException($req, 'La extensión incorrecta, solo se permiten archivos .csv');
        }
        try {
            $uploadedFile = $uploadedFiles['archivo'];
            $productos = Producto::ParsearCsv($uploadedFile->getFilePath());
            foreach ($productos as $p) {
                $p->CrearProducto();
            }
            $payload = json_encode(['mensaje' => 'Producto subidos']);

            
        } catch (\Exception $e) {
            $payload = json_encode(['error' => 'Error al procesar el archivo, formato incorrecto']);
            $res = $res->withStatus(400);
        }

        $res->getBody()->write($payload);
        return $res;

    }

    public function GetProductosSegunMasVentas(Request $req, Response $res, array $args = []) {
        $productos = Producto::GetProductosSegunVentas();
        $payload = json_encode($productos);
        $res->getBody()->write($payload);
        return $res; 
    }

}

?>