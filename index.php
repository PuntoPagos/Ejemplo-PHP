<?php
require_once('puntopagos.class.php');
$trx_id = rand(1, 1000000);
$monto = rand(10000, 100000);
$respuesta = PuntoPagos::CrearTransaccion($trx_id, $monto);
if ($respuesta->{'token'} != null){
    $url = PUNTOPAGOS_URL."/transaccion/procesar/".$respuesta->{'token'};
    ob_start();
    header("Location: $url");
    ob_flush();
}
else{
    echo $respuesta->{'error'};
}
