<?php
namespace App\Utils;

/**
 * Manejador mejorado de errores y excepciones
 */
class ErrorHandler {
    private static $logPath;
    
    public static function init() {
        self::$logPath = __DIR__ . '/../../logs/';
        
        // Crear directorio de logs si no existe
        if (!is_dir(self::$logPath)) {
            mkdir(self::$logPath, 0755, true);
        }
        
        // Configurar manejadores de errores
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleFatalError']);
        
        // Configurar logs según el entorno
        $config = require __DIR__ . '/../../config/app.php';
        if ($config['app']['debug']) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        } else {
            error_reporting(0);
            ini_set('display_errors', 0);
        }
    }
    
    /**
     * Maneja errores PHP
     */
    public static function handleError($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        
        $error = [
            'type' => 'Error',
            'severity' => $severity,
            'message' => $message,
            'file' => $file,
            'line' => $line,
            'timestamp' => date('Y-m-d H:i:s'),
            'trace' => debug_backtrace()
        ];
        
        self::logError($error);
        
        $config = require __DIR__ . '/../../config/app.php';
        if ($config['app']['debug']) {
            self::displayError($error);
        } else {
            self::displayGenericError();
        }
        
        return true;
    }
    
    /**
     * Maneja excepciones no capturadas
     */
    public static function handleException($exception) {
        $error = [
            'type' => 'Exception',
            'class' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'timestamp' => date('Y-m-d H:i:s'),
            'trace' => $exception->getTrace()
        ];
        
        self::logError($error);
        
        $config = require __DIR__ . '/../../config/app.php';
        if ($config['app']['debug']) {
            self::displayError($error);
        } else {
            self::displayGenericError();
        }
    }
    
    /**
     * Maneja errores fatales
     */
    public static function handleFatalError() {
        $error = error_get_last();
        
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $errorData = [
                'type' => 'Fatal Error',
                'message' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line'],
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            self::logError($errorData);
            
            $config = require __DIR__ . '/../../config/app.php';
            if (!$config['app']['debug']) {
                self::displayGenericError();
            }
        }
    }
    
    /**
     * Registra el error en archivo de log
     */
    private static function logError($error) {
        $logFile = self::$logPath . 'error_' . date('Y-m-d') . '.log';
        $logEntry = json_encode($error, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Muestra error detallado (solo en modo debug)
     */
    private static function displayError($error) {
        if (headers_sent()) {
            echo "\n\n";
        } else {
            http_response_code(500);
            header('Content-Type: text/html; charset=utf-8');
        }
        
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Error del Sistema</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
                .error-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .error-title { color: #d32f2f; font-size: 24px; margin-bottom: 20px; }
                .error-details { background: #f8f8f8; padding: 15px; border-radius: 4px; margin: 10px 0; }
                .error-trace { background: #fffbf0; padding: 15px; border-radius: 4px; font-family: monospace; font-size: 12px; }
                .back-link { display: inline-block; background: #1976d2; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class='error-container'>
                <h1 class='error-title'>🚨 {$error['type']}</h1>
                <div class='error-details'>
                    <strong>Mensaje:</strong> {$error['message']}<br>
                    <strong>Archivo:</strong> {$error['file']}<br>
                    <strong>Línea:</strong> {$error['line']}<br>
                    <strong>Fecha:</strong> {$error['timestamp']}
                </div>";
        
        if (isset($error['trace'])) {
            echo "<details>
                    <summary>Stack Trace</summary>
                    <div class='error-trace'>" . print_r($error['trace'], true) . "</div>
                  </details>";
        }
        
        echo "<a href='/Sistema-de-ventas-AppLink-main/public/' class='back-link'>🏠 Volver al Inicio</a>
            </div>
        </body>
        </html>";
    }
    
    /**
     * Muestra error genérico (modo producción)
     */
    private static function displayGenericError() {
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=utf-8');
        }
        
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Error del Sistema</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
                .error-card { background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); text-align: center; max-width: 500px; }
                .error-icon { font-size: 60px; margin-bottom: 20px; }
                h1 { color: #333; margin-bottom: 15px; }
                p { color: #666; margin-bottom: 30px; }
                .btn { background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 25px; display: inline-block; transition: background 0.3s; }
                .btn:hover { background: #5a67d8; }
            </style>
        </head>
        <body>
            <div class='error-card'>
                <div class='error-icon'>⚠️</div>
                <h1>¡Ups! Algo salió mal</h1>
                <p>Ha ocurrido un error inesperado. Nuestro equipo ha sido notificado y está trabajando para solucionarlo.</p>
                <a href='/Sistema-de-ventas-AppLink-main/public/' class='btn'>🏠 Volver al Inicio</a>
            </div>
        </body>
        </html>";
    }
    
    /**
     * Método público para lanzar errores personalizados
     */
    public static function throwError($message, $code = 500, $type = 'Application Error') {
        throw new \Exception("[$type] $message", $code);
    }
    
    /**
     * Validar y sanitizar entrada de usuario
     */
    public static function validateInput($data, $rules) {
        $errors = [];
        $sanitized = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            // Aplicar sanitización
            if (isset($rule['sanitize'])) {
                switch ($rule['sanitize']) {
                    case 'string':
                        $value = trim(strip_tags($value));
                        break;
                    case 'email':
                        $value = filter_var($value, FILTER_SANITIZE_EMAIL);
                        break;
                    case 'int':
                        $value = (int)$value;
                        break;
                    case 'float':
                        $value = (float)$value;
                        break;
                }
            }
            
            // Aplicar validaciones
            if (isset($rule['required']) && $rule['required'] && empty($value)) {
                $errors[$field] = "El campo $field es requerido";
                continue;
            }
            
            if (!empty($value) && isset($rule['validate'])) {
                switch ($rule['validate']) {
                    case 'email':
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[$field] = "El campo $field debe ser un email válido";
                        }
                        break;
                    case 'min_length':
                        if (strlen($value) < $rule['min']) {
                            $errors[$field] = "El campo $field debe tener al menos {$rule['min']} caracteres";
                        }
                        break;
                    case 'max_length':
                        if (strlen($value) > $rule['max']) {
                            $errors[$field] = "El campo $field no puede tener más de {$rule['max']} caracteres";
                        }
                        break;
                }
            }
            
            $sanitized[$field] = $value;
        }
        
        if (!empty($errors)) {
            throw new \Exception('Errores de validación: ' . json_encode($errors));
        }
        
        return $sanitized;
    }
}