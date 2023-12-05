<?php

function generarCodigo($longitud) {
    $codigo = '';
    $patron = '1234567890';
    $letras = 'abcdefghijklmnopqrstuvwxyz';
    $patron .= strtoupper($letras);
    $max = strlen($patron)-1;
    for($i=0; $i < $longitud; $i++) {
        $codigo .= $patron[mt_rand(0, $max)];
    }
    return $codigo;
}  

function EsFechaValida($fecha, $formato = 'Y/m/d')
{
    $f = DateTime::createFromFormat($formato, $fecha);
    return $f && $f->format($formato) === $fecha;
}

function FormatearNumero($numero) {
    return str_pad($numero, 6, "0", STR_PAD_LEFT);
}

function moveUploadedFile(string $directory, string $filename, $uploadedFile)//UploadedFileInterface $uploadedFile, no toma la interface
    {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);

        $filename .= ".$extension";

        $uploadedFile->moveTo($directory . $filename);

        return $filename;
    }

function EsVacioONuloOEnBlanco($str) {
    return !isset($str) || empty(trim($str));
}
function EsNumeroEntero($num) {
    return is_numeric($num) && !str_contains($num, '.');
}

function CalcularTiempoRestante(DateTime $fechaFinal) {

    $ahora = new DateTime();
    $intervalo = $ahora->diff($fechaFinal);
    $formato = $intervalo->h != 0 ? '%HH:%IM:%sS' : '%IM:%sS';
    
    return $intervalo->format($formato);
}

function array_find_index($callback, $array) {
    $index = -1;
    foreach ($array as $key=>$value) {
      if ($callback($value)) {
        $index = $key;
        break;
      }
    }
    return $index;
}

?>