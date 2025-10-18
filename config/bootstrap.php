<?php
// Cargar el autoloader si usamos Composer
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
}

// Cargar variables de entorno
if (file_exists(__DIR__ . '/../.env')) {
    $env = parse_ini_file(__DIR__ . '/../.env');
    foreach ($env as $key => $value) {
        $_ENV[$key] = $value;
    }
}

// Cargar configuración
$config = require __DIR__ . '/../config/app.php';

// Configurar zona horaria
date_default_timezone_set($config['app']['timezone']);

// Configuración de errores
if ($config['app']['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Asegurar que existe el directorio de logs
if (!file_exists($config['logging']['path'])) {
    mkdir($config['logging']['path'], 0755, true);
}

// Iniciar sesión
session_start();
