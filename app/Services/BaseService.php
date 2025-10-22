<?php
/**
 * Clase base para todos los Services
 * Sistema de Ventas AppLink
 */

namespace App\Services;

abstract class BaseService {
    
    protected $db;
    protected $logger;
    
    public function __construct($database = null) {
        $this->db = $database ?: $this->getDatabase();
        $this->logger = $this->getLogger();
    }
    
    /**
     * Obtener conexión a la base de datos
     */
    protected function getDatabase() {
        require_once __DIR__ . '/../../config/Database.php';
        
        return new \PDO(
            \App\Config\Database::getDSN(),
            \App\Config\Database::getUsername(),
            \App\Config\Database::getPassword(),
            \App\Config\Database::getOptions()
        );
    }
    
    /**
     * Obtener logger
     */
    protected function getLogger() {
        // Por ahora retornamos null, después implementaremos logging
        return null;
    }
    
    /**
     * Manejar errores de manera consistente
     */
    protected function handleError($message, $exception = null) {
        $error = [
            'success' => false,
            'error' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        if ($exception && $this->isDebugMode()) {
            $error['debug'] = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine()
            ];
        }
        
        return $error;
    }
    
    /**
     * Respuesta de éxito estándar
     */
    protected function successResponse($data = null, $message = null) {
        $response = [
            'success' => true,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        if ($message !== null) {
            $response['message'] = $message;
        }
        
        return $response;
    }
    
    /**
     * Verificar si está en modo debug
     */
    protected function isDebugMode() {
        return defined('APP_DEBUG') && APP_DEBUG === true;
    }
    
    /**
     * Validar datos requeridos
     */
    protected function validateRequired($data, $required) {
        $missing = [];
        
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missing[] = $field;
            }
        }
        
        return $missing;
    }
}
?>