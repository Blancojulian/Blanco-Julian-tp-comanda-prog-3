<?php

require_once './models/Empleado.php';
require_once './models/Puesto.php';
require_once './interfaces/IController.php';
require_once './utils/BaseRespuestaError.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpException;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

class EmpleadoController extends BaseRespuestaError implements IController {

    public function Get(Request $req, Response $res, array $args = []) {
        if (!isset($args['id'])) {
            throw new HttpBadRequestException($req, 'Debe enviar id');   
        }
        $id = $args['id'];
        if (!is_numeric($id)) {
            throw new HttpBadRequestException($req, 'Id debe ser un numero');   
        }
        $empleado = Empleado::GetEmpleado($id);
        if (!isset($empleado)) {
            throw new HttpNotFoundException($req, 'Empleado no existe');
            
        }
        $res->getBody()->write(json_encode($empleado));
        return $res; 
    }

    public function GetAll(Request $req, Response $res, array $args = []) {
        $empleados = Empleado::GetEmpleados();
        $res->getBody()->write(json_encode($empleados));
        return $res; 
    }
    public function GetAllPorCriterio(Request $req, Response $res, array $args = []) {
        if (!isset($args['puesto'])) {
            throw new HttpBadRequestException($req, 'debe enviar el puesto');
            
        }
        $puesto = Puesto::GetPuestoPorNombre($args['puesto']);
        if (!isset($puesto)) {
            throw new HttpBadRequestException($req, 'Debe enviar un puesto valido');
            
        }
        $empleados = Empleado::GetEmpleadosPorPuesto($puesto->id);
        $res->getBody()->write(json_encode($empleados));
        return $res;
    }
    public function Create(Request $req, Response $res, array $args = []) {
        $parametros = $req->getParsedBody();

        if (!isset($parametros['contrasenia']) || EsVacioONuloOEnBlanco($parametros['contrasenia'])) {
            throw new HttpBadRequestException($req, 'Debe enviar contraseña');
        }
        
        $puesto = Puesto::GetPuestoPorNombre($parametros['puesto']);
        
        //($nombre, $apellido, $dni, $email, $idPuesto, $puesto, $contrasenia, $fechaAlta = null, $fechaModificacion = null, $fechaBaja = null, $id = null) {
        $email = strtolower($parametros['email']);

        $empleado = new Empleado($parametros['nombre'], $parametros['apellido'], $parametros['dni'], 
        $email, $puesto->id, $puesto->nombre, $parametros['contrasenia']);
        
        $id = $empleado->CrearEmpleado();

        $payload = json_encode(['mensaje' => 'Empleado creado', 'id' => $id]);
        $res->getBody()->write($payload);
        
        return $res;
    }

    public function Delete(Request $req, Response $res, array $args = []) {
        if (!isset($args['id'])) {
            throw new HttpBadRequestException($req, 'Debe enviar el id');   
        }
        $id = $args['id'];
        if (!is_numeric($id)) {
            throw new HttpBadRequestException($req, 'Id debe ser un numero');   
        }
        $empleado = Empleado::GetEmpleado($id);
        if (!isset($empleado)) {
            throw new HttpNotFoundException($req, 'Empleado no existe');   
        }
        Empleado::BajaEmpleado($empleado->id);
        $res->getBody()->write(json_encode(['mensaje' => "Empleado eliminado"]));
        return $res; 
    }

    public function SuspencionEmpleado(Request $req, Response $res, array $args = []) {
        $parametros = $req->getQueryParams();

        if (!isset($parametros['accion']) || ($parametros['accion'] !== 'suspender' && $parametros['accion'] !== 'habilitar')) {
            throw new HttpBadRequestException($req, 'Debe enviar la accion, suspender o habilitar');   
        }

        if (!isset($args['id'])) {
            throw new HttpBadRequestException($req, 'Debe enviar el id');   
        }
        if (!is_numeric($args['id'])) {
            throw new HttpBadRequestException($req, 'Id debe ser un numero');   
        }
        $id = $args['id'];
        $suspender = $parametros['accion'] == 'suspender';
        $mensaje = $suspender ? 'Empleado suspendido' : 'Empleado habilitado';
        
        $empleado = Empleado::GetEmpleado($id);
        if (!isset($empleado)) {
            throw new HttpNotFoundException($req, 'Empleado no existe');   
        }
        if ($suspender && isset($empleado->fechaSuspension)) {
            throw new HttpNotFoundException($req, 'Empleado ya suspendido');   
        } else if (!$suspender && !isset($empleado->fechaSuspension)) {
            throw new HttpNotFoundException($req, 'Empleado esta habilitado');   
        }

        Empleado::SuspenderEmpleado($empleado->id, $suspender);
        $payload = json_encode(['mensaje' => $mensaje]);
        $res->getBody()->write($payload);
        return $res; 

    }


    public function Update(Request $req, Response $res, array $args = []) {
        $parametros = $req->getParsedBody();

        if (!isset($parametros['id']) || !isset($parametros['nombre']) || !isset($parametros['apellido']) ||
        !isset($parametros['dni']) || !isset($parametros['puesto'])) {
            throw new HttpBadRequestException($req, 'Debe enviar id, nombre, apellido, dni y puesto');
        }
        if (!is_numeric($parametros['dni'])) {
            throw new HttpBadRequestException($req, 'DNI debe ser un numero');   
        }
        $puesto = Puesto::GetPuestoPorNombre($parametros['puesto']);
        if (!isset($puesto)) {
            throw new HttpBadRequestException($req, 'Debe enviar un puesto valido');
        }

        $empleado = Empleado::GetEmpleado(intval($parametros['id']));
        
        if (!isset($empleado)) {   
            throw new HttpNotFoundException($req, 'Empleado no existe');   

        }
        $empleado->nombre = $parametros['nombre'];
        $empleado->apellido = $parametros['apellido'];
        $empleado->dni = $parametros['dni'];
        $empleado->idPuesto = $puesto->id;
        $empleado->puesto = $puesto->nombre;
        
        $empleado->ModificarEmpleado();

        $res->getBody()->write(json_encode(['mensaje' => "Empleado modificado", 'id' => $empleado->id]));

        return $res;
    }
}

?>