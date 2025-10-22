<?php
/**
 * Autoloader mejorado para Sistema de Ventas AppLink
 * Soporta PSR-4 y namespaces
 */

class AppAutoloader {
    
    private static $namespaces = [];
    private static $registered = false;
    
    /**
     * Registrar autoloader
     */
    public static function register() {
        if (self::$registered) {
            return;
        }
        
        // Registrar namespaces principales
        self::addNamespace('App\\', __DIR__ . '/app/');
        self::addNamespace('App\\Services\\', __DIR__ . '/app/Services/');
        self::addNamespace('App\\Services\\Business\\', __DIR__ . '/app/Services/Business/');
        self::addNamespace('App\\Services\\Validation\\', __DIR__ . '/app/Services/Validation/');
        self::addNamespace('App\\Middleware\\', __DIR__ . '/app/Middleware/');
        self::addNamespace('Config\\', __DIR__ . '/config/');
        
        // Registrar función de autoload
        spl_autoload_register([self::class, 'load']);
        
        self::$registered = true;
    }
    
    /**
     * Añadir namespace
     */
    public static function addNamespace($namespace, $directory) {
        $namespace = trim($namespace, '\\') . '\\';
        $directory = rtrim($directory, '/') . '/';
        
        if (!isset(self::$namespaces[$namespace])) {
            self::$namespaces[$namespace] = [];
        }
        
        self::$namespaces[$namespace][] = $directory;
    }
    
    /**
     * Cargar clase
     */
    public static function load($className) {
        $className = ltrim($className, '\\');
        
        foreach (self::$namespaces as $namespace => $directories) {
            if (strpos($className, $namespace) === 0) {
                $relativeClass = substr($className, strlen($namespace));
                
                foreach ($directories as $directory) {
                    $file = $directory . str_replace('\\', '/', $relativeClass) . '.php';
                    
                    if (file_exists($file)) {
                        require_once $file;
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
    
    /**
     * Obtener namespaces registrados
     */
    public static function getNamespaces() {
        return self::$namespaces;
    }
}

// Auto-registrar si se incluye directamente
if (!defined('AUTOLOADER_REGISTERED')) {
    AppAutoloader::register();
    define('AUTOLOADER_REGISTERED', true);
}
?>