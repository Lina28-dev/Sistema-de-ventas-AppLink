<?php
/**
 * Router para APIs versionadas
 * Sistema de Ventas AppLink
 */

class APIRouter {
    
    private static $routes = [
        'v1' => [
            'users' => 'users.php',
            'clients' => 'clients.php', 
            'sales' => 'sales.php',
            'orders' => 'orders.php'
        ]
    ];
    
    /**
     * Manejar petición de API
     */
    public static function handle($version, $endpoint) {
        // Validar versión
        if (!isset(self::$routes[$version])) {
            http_response_code(404);
            echo json_encode(['error' => 'API version not found']);
            return;
        }
        
        // Validar endpoint
        if (!isset(self::$routes[$version][$endpoint])) {
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found']);
            return;
        }
        
        // Incluir archivo de la API
        $apiFile = __DIR__ . "/../public/api/$version/" . self::$routes[$version][$endpoint];
        
        if (file_exists($apiFile)) {
            require_once $apiFile;
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'API file not found']);
        }
    }
    
    /**
     * Generar URL de API
     */
    public static function url($version, $endpoint, $params = []) {
        $base = "/Sistema-de-ventas-AppLink-main/public/api/$version/$endpoint";
        
        if (!empty($params)) {
            $base .= '?' . http_build_query($params);
        }
        
        return $base;
    }
    
    /**
     * Listar endpoints disponibles
     */
    public static function getAvailableEndpoints($version = null) {
        if ($version) {
            return self::$routes[$version] ?? [];
        }
        
        return self::$routes;
    }
}

// Función helper para generar URLs de API
function api_url($version, $endpoint, $params = []) {
    return APIRouter::url($version, $endpoint, $params);
}
?>