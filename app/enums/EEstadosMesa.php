<?php
enum EstadosMesa: int
{
    case ClienteEsperando = 1;
    case ClienteComiendo = 2;
    case ClientePagando = 3;
    case Cerrada = 4;
}
?>