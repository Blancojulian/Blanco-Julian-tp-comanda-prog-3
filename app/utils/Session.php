<?php

class Session {

    public static function StartSession() {
        if(session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    private static function GetRol() {
        self::StartSession();
        return isset($_SESSION['rol']) ? $_SESSION['rol'] : null;
    }

    private function CloseSession() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
    }
}

?>