<?php

require_once './models/Puesto.php';
require_once './models/Empleado.php';
require_once './utils/utils.php';
require_once './interfaces/IController.php';
require_once './utils/BaseRespuestaError.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpException;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;
//use FPDF;

class PdfController extends BaseRespuestaError {

    private $_rutaLogo = './Imagenes/Logo/logo.jpg';

    public function __invoke(Request $request, Response $response, array $args = []) {

        $pdf = new FPDF();
        $filename = 'logoEmpresa.pdf';

        $pdf->AddPage();

        $pdf->SetFont('Arial', 'B', 16);

        $pdf->Cell(40, 10, 'Logo Empresa');

        $pdf->Ln();//línea en blanco
        $ruta = 'ruta/a/la/imagen.jpg';
        $pdf->Image($this->_rutaLogo, 10, 30, 150, 150); // Parámetros: ruta, x, y, ancho, alto

        $pdfContent = $pdf->Output('S');

        $response->getBody()->write($pdfContent);

        $response = $response->withHeader('Content-Type', 'application/pdf');
        $response = $response->withHeader('Content-Disposition', "inline; filename=$filename");

        return $response;
    }

}

?>