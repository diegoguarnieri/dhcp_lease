<?php

require_once 'routeros_api.class.php';

class Api {

    public static function getDhcpLeaseMikrotik($ip, $user, $password) {

        $mikrotik = new RouterosAPI();
        //$mikrotik->debug = true;

        if($mikrotik->connect($ip, $user, $password)) {
            $mikrotik->write('/ip/dhcp-server/lease/print');
            $read = $mikrotik->read(false);
            $array = $mikrotik->parseResponse($read);
            $mikrotik->disconnect();

            return $array;
        }
    }
}

?>

