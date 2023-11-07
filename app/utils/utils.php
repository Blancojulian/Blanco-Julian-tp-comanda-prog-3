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


?>