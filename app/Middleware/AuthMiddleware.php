<?php
/**
 * Middleware de Autenticación
 * Sistema de Ventas AppLink
 */

namespace App\Middleware;

class AuthMiddleware {
    
    /**
     * Verificar autenticación
     */
    public static function check() {
        // Verificar sesión activa
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $isAuthenticated = false;
        $authMethods = [];
        
        // 1. Verificar sesión PHP
        if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
            $isAuthenticated = true;
            $authMethods[] = 'session';
        }
        
        // 2. Verificar referer (para desarrollo local)
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if (strpos($referer, 'localhost/Sistema-de-ventas-AppLink-main') !== false) {
            $isAuthenticated = true;
            $authMethods[] = 'referer';
        }
        
        // 3. Verificar acceso local (para desarrollo)
        if (in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1'])) {
            $isAuthenticated = true;
            $authMethods[] = 'local';
        }
        
        // 4. Verificar API Key (para futuro)
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $_GET['api_key'] ?? null;
        if ($apiKey && self::validateApiKey($apiKey)) {
            $isAuthenticated = true;
            $authMethods[] = 'api_key';
        }
        
        return [
            'authenticated' => $isAuthenticated,
            'methods' => $authMethods,
            'user_id' => $_SESSION['user_id'] ?? null,
            'user_role' => $_SESSION['user_role'] ?? null
        ];
    }
    
    /**
     * Middleware para APIs
     */
    public static function api() {
        $auth = self::check();
        
        if (!$auth['authenticated']) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'error' => 'No autorizado',
                'message' => 'Se requiere autenticación para acceder a este recurso'
            ]);
            exit;
        }
        
        return $auth;
    }
    
    /**
     * Middleware para páginas web
     */
    public static function web($redirectTo = '/login') {
        $auth = self::check();
        
        if (!$auth['authenticated']) {
            header("Location: $redirectTo");
            exit;
        }
        
        return $auth;
    }
    
    /**
     * Middleware para administradores
     */
    public static function admin() {
        $auth = self::api();
        
        if ($auth['user_role'] !== 'admin' && !($_SESSION['is_admin'] ?? false)) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error' => 'Acceso denegado',
                'message' => 'Se requieren permisos de administrador'
            ]);
            exit;
        }
        
        return $auth;
    }
    
    /**
     * Validar API Key
     */
    private static function validateApiKey($apiKey) {
        // Por ahora, API key simple para desarrollo
        $validKeys = [
            'dev-key-123',
            'applink-2024'
        ];
        
        return in_array($apiKey, $validKeys);
    }
    
    /**
     * Obtener información del usuario autenticado
     */
    public static function user() {
        $auth = self::check();
        
        if (!$auth['authenticated']) {
            return null;
        }
        
        return [
            'id' => $auth['user_id'],
            'role' => $auth['user_role'],
            'name' => $_SESSION['user_name'] ?? null,
            'email' => $_SESSION['user_email'] ?? null
        ];
    }
    
    /**
     * Cerrar sesión
     */
    public static function logout() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        session_destroy();
        
        return [
            'success' => true,
            'message' => 'Sesión cerrada exitosamente'
        ];
    }
}
?>