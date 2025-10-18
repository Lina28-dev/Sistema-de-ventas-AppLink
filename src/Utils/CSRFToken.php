<?php
class CSRFToken {
    public static function generar() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    // Alias para compatibilidad
    public static function generate() {
        return self::generar();
    }
    
    public static function validar($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    // Alias para compatibilidad
    public static function validate($token) {
        return self::validar($token);
    }
}

