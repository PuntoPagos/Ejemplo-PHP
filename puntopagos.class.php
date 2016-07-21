<?php
/**
 * Clases de Punto Pagos
 *
 * @author dvinales & guillehorno
 */

require_once('puntopagos.inc.php');

class PuntoPagos {

    /**
    * Crea la transaccion para después redireccionar a PuntoPagos donde se realizará la selección
    * de el medio de pago
    * @param sring $trx_id Id de la transaccion asignado por la aplicacion cliente
    * @param sring $monto Monto del pago a cobrar a través de PuntoPagos
    * @return array Respuesta
    */

    public static function CrearTransaccion($trx_id, $monto)
    {
        $funcion = 'transaccion/crear';
        $monto_str = number_format($monto, 2, '.', '');
        $data = '{"trx_id":"'.$trx_id.'","monto":"'.$monto_str.'"}';
        $header_array = PuntoPagos::TraerHeader($funcion, $trx_id, $monto_str);
        return json_decode(PuntoPagos::ExecuteCommand(PUNTOPAGOS_URL.'/'.$funcion, $header_array, $data));
    }

   /**
    *  Crea la transaccion ya habiendo seleccionado un medio de pago en la aplicacion, haciendo
    * la redireccion correspondiente al medio de pago elegido
    * @param sring $trx_id Id de la transaccion asignado por la aplicacion cliente
    * @param sring $medio_pago Medio de pago elegido por el usuario, segun codificacion disponible en la documentacion de PuntoPagos
    * @param sring $monto Monto del pago a cobrar a través de PuntoPagos
    * @return array Respuesta
    */

    public static function CrearTransaccionMP($trx_id, $medio_pago, $monto)
    {
        $funcion = 'transaccion/crear';
        $monto_str = number_format($monto, 2, '.', '');
        $data = '{"trx_id":"'.$trx_id.'","medio_pago":"'.$medio_pago.'","monto":"'.$monto_str.'"}';
        $header_array = PuntoPagos::TraerHeader($funcion, $trx_id, $monto_str);
        return json_decode(PuntoPagos::ExecuteCommand(PUNTOPAGOS_URL.'/'.$funcion, $header_array, $data));
    }

    /**
    *  Metodo para consultar el estado de una transacción en particular
    * la redireccion correspondiente al medio de pago elegido
    * @param sring $token Token de la transaccion
    * @param sring $trx_id Id de la transaccion asignado por la aplicacion cliente
    * @param sring $monto Monto del pago de la transaccion a consultar
    * @return array Respuesta
    */

    function ConsultarTransaccion($token, $trx_id, $monto){
        $funcion = 'transaccion';
        $header_funcion = 'transaccion/traer';
        $monto_str = number_format($monto, 2, '.', '');
        $header_array = PuntoPagos::TraerHeaderConsulta($header_funcion, $token, $trx_id, $monto_str);
        return json_decode(PuntoPagos::ExecuteCommandGET(PUNTOPAGOS_URL.'/'.$funcion.'/'.$token, $header_array));
    }

    public static function FirmarMensaje($str) {
        $signature = base64_encode(hash_hmac('sha1', $str, PUNTOPAGOS_SECRET, true));
        return "PP ".PUNTOPAGOS_KEY.":".$signature;
    }

    public static function TraerHeader($funcion, $trx_id, $monto_str)
    {
        $fecha = date("D, d M Y H:i:s", time())." GMT";
        $mensaje = $funcion."\n".$trx_id."\n".$monto_str."\n".$fecha;
        $firma = PuntoPagos::FirmarMensaje($mensaje);
        $header_array = array('Accept: application/json',
                              "Content-Type: application/json; charset=utf-8",
                              'Accept-Charset: utf-8',
                              'Fecha: '. $fecha,
                              'Autorizacion:'.$firma);
        return $header_array;
    }
    
    function TraerHeaderConsulta($funcion, $token, $trx_id, $monto_str) {
	      $fecha = date("D, d M Y H:i:s", time())." GMT";
	      $mensaje = $funcion."\n".$token."\n".$trx_id."\n".$monto_str."\n".$fecha;
	      $firma = PuntoPagos::FirmarMensaje($mensaje);
     	 	$header_array = array('Accept: application/json',
                              "Content-Type: application/json; charset=utf-8",
                              'Accept-Charset: utf-8',
                              'Fecha: '. $fecha,
                              'Autorizacion:'.$firma);
	      return $header_array;
	  }

    public static function ExecuteCommand($url, $header_array, $data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header_array);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'PuntoPagos-curl');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        //execute post
        $result = curl_exec($ch);
        $error =  curl_error($ch);
        curl_close($ch);
        if($result){
            return $result;
        }
        else{
            return $error;
        }

    }
    
    public static function ExecuteCommandGET($url, $header_array) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header_array);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($ch, CURLOPT_USERAGENT, 'PuntoPagos-curl');
        //execute get
        $result = curl_exec($ch);
        $error =  curl_error($ch);
        curl_close($ch);
        if($result){
            return $result;
        }
        else{
            return $error;
        }

    }
}
