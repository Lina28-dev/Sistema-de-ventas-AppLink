<?php
// Cargar variables de entorno desde .env si existe
if (file_exists(__DIR__ . '/../.env')) {
    $env = parse_ini_file(__DIR__ . '/../.env');
    foreach ($env as $key => $value) {
        $_ENV[$key] = $value;
    }
}

// Configuración de base de datos
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'fs_clientes');
define('DB_CHARSET', 'utf8mb4');

// Configuración de la aplicación
define('APP_NAME', 'Sistema de Ventas AppLink');
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost/gestor-ventas-lilipink-main');
define('APP_DEBUG', $_ENV['APP_DEBUG'] ?? false);

// Configuración de sesión
define('SESSION_LIFETIME', 3600); // 1 hora
define('SECURE_COOKIES', $_ENV['SECURE_COOKIES'] ?? false);

// Configuración de logging
define('LOG_PATH', __DIR__ . '/../logs');
define('LOG_LEVEL', $_ENV['LOG_LEVEL'] ?? 'ERROR');

// Configuración de seguridad
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 300); // 5 minutos

// Zona horaria
date_default_timezone_set('America/Bogota');

// Configuración de errores
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Asegurar que existe el directorio de logs
if (!file_exists(LOG_PATH)) {
    mkdir(LOG_PATH, 0755, true);
}