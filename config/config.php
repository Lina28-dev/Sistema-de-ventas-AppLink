<?php
// Función para cargar variables de entorno desde .env
function loadEnvFile($filePath) {
    if (!file_exists($filePath)) {
        return [];
    }
    
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];
    
    foreach ($lines as $line) {
        // Ignorar comentarios y líneas vacías
        if (strpos(trim($line), '#') === 0 || empty(trim($line))) {
            continue;
        }
        
        // Buscar el patrón KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remover comillas si existen
            if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') || 
                (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                $value = substr($value, 1, -1);
            }
            
            $env[$key] = $value;
        }
    }
    
    return $env;
}

// Cargar variables de entorno desde .env si existe
$env = loadEnvFile(__DIR__ . '/../.env');
foreach ($env as $key => $value) {
    $_ENV[$key] = $value;
}

// Configuración de base de datos con validación
define('DB_HOST', !empty($_ENV['DB_HOST']) ? $_ENV['DB_HOST'] : 'localhost');
define('DB_USER', !empty($_ENV['DB_USER']) ? $_ENV['DB_USER'] : 'root');
define('DB_PASS', isset($_ENV['DB_PASS']) ? $_ENV['DB_PASS'] : '');
define('DB_NAME', !empty($_ENV['DB_NAME']) ? $_ENV['DB_NAME'] : 'fs_clientes');
define('DB_CHARSET', !empty($_ENV['DB_CHARSET']) ? $_ENV['DB_CHARSET'] : 'utf8mb4');
define('DB_PORT', !empty($_ENV['DB_PORT']) ? (int)$_ENV['DB_PORT'] : 3306);

// Configuración de la aplicación
define('APP_NAME', !empty($_ENV['APP_NAME']) ? $_ENV['APP_NAME'] : 'Sistema de Ventas AppLink');
define('APP_URL', !empty($_ENV['APP_URL']) ? $_ENV['APP_URL'] : 'http://localhost/Sistema-de-ventas-AppLink-main');
define('APP_DEBUG', !empty($_ENV['APP_DEBUG']) ? filter_var($_ENV['APP_DEBUG'], FILTER_VALIDATE_BOOLEAN) : false);
define('APP_TIMEZONE', !empty($_ENV['APP_TIMEZONE']) ? $_ENV['APP_TIMEZONE'] : 'America/Bogota');

// Configuración de sesión
define('SESSION_LIFETIME', !empty($_ENV['SESSION_LIFETIME']) ? (int)$_ENV['SESSION_LIFETIME'] : 3600);
define('SESSION_NAME', !empty($_ENV['SESSION_NAME']) ? $_ENV['SESSION_NAME'] : 'APPLINK_SESSION');
define('SECURE_COOKIES', !empty($_ENV['SESSION_SECURE']) ? filter_var($_ENV['SESSION_SECURE'], FILTER_VALIDATE_BOOLEAN) : false);

// Configuración de logging
define('LOG_PATH', __DIR__ . '/../logs');
define('LOG_LEVEL', !empty($_ENV['LOG_LEVEL']) ? $_ENV['LOG_LEVEL'] : 'ERROR');
define('LOG_ENABLED', !empty($_ENV['LOG_ENABLED']) ? filter_var($_ENV['LOG_ENABLED'], FILTER_VALIDATE_BOOLEAN) : true);

// Configuración de seguridad
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 300); // 5 minutos

// Configuración de ventas
define('CURRENCY', !empty($_ENV['CURRENCY']) ? $_ENV['CURRENCY'] : 'COP');
define('CURRENCY_SYMBOL', !empty($_ENV['CURRENCY_SYMBOL']) ? $_ENV['CURRENCY_SYMBOL'] : '$');
define('DEFAULT_TAX', !empty($_ENV['DEFAULT_TAX']) ? (float)$_ENV['DEFAULT_TAX'] : 0.19);

// Zona horaria
date_default_timezone_set(APP_TIMEZONE);

// Configuración de errores
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
} else {
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Asegurar que existe el directorio de logs
if (!file_exists(LOG_PATH)) {
    if (!mkdir(LOG_PATH, 0755, true) && !is_dir(LOG_PATH)) {
        error_log("No se pudo crear el directorio de logs: " . LOG_PATH);
    }
}

// Función de logging personalizada
function logMessage($level, $message, $context = []) {
    if (!LOG_ENABLED) return;
    
    $logFile = LOG_PATH . '/app_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';
    $logEntry = "[{$timestamp}] [{$level}] {$message}{$contextStr}" . PHP_EOL;
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

// Validar configuración crítica
try {
    // Verificar que las constantes críticas estén definidas
    $requiredConstants = ['DB_HOST', 'DB_NAME', 'APP_NAME', 'APP_URL'];
    foreach ($requiredConstants as $constant) {
        if (!defined($constant)) {
            throw new Exception("Configuración faltante: {$constant}");
        }
    }
    
    // Log de configuración cargada exitosamente
    if (LOG_ENABLED) {
        logMessage('INFO', 'Configuración cargada exitosamente', [
            'app_name' => APP_NAME,
            'debug_mode' => APP_DEBUG,
            'timezone' => APP_TIMEZONE
        ]);
    }
    
} catch (Exception $e) {
    // Log del error y usar valores por defecto
    error_log("Error en configuración: " . $e->getMessage());
    
    // Definir constantes faltantes con valores por defecto
    if (!defined('APP_NAME')) define('APP_NAME', 'Sistema de Ventas AppLink');
    if (!defined('APP_URL')) define('APP_URL', 'http://localhost');
    if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
    if (!defined('DB_NAME')) define('DB_NAME', 'fs_clientes');
}
