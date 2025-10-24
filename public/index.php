<?php
// Configuración inicial de errores
error_reporting(E_ALL);
ini_set('display_errors', 1); // Mostrar errores directamente para debug

// Configurar zona horaria
date_default_timezone_set('America/Bogota');

// Inicializar la sesión con configuración segura
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Cambiar a 1 en HTTPS
session_start();

// Cargar configuración básica
require_once __DIR__ . '/../config/paths.php';

// Autoloader simplificado
spl_autoload_register(function ($class) {
    // Convertir namespace a ruta de archivo
    $class = str_replace('App\\', '', $class);
    $class = str_replace('\\', '/', $class);
    
    $paths = [
        __DIR__ . '/../src/' . $class . '.php',
        __DIR__ . '/../src/Models/' . basename($class) . '.php',
        __DIR__ . '/../src/Controllers/' . basename($class) . '.php',
        __DIR__ . '/../src/Utils/' . basename($class) . '.php',
        __DIR__ . '/../src/Middleware/' . basename($class) . '.php'
    ];

    foreach ($paths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }
    
    return false;
});

// Enrutamiento básico
$request = $_SERVER['REQUEST_URI'];
$base = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
$request = str_replace($base, '', $request);

// Limpiar query string
$request = strtok($request, '?');

// Eliminar .php si existe
$request = str_replace('.php', '', $request);

// Enrutamiento simple
switch ($request) {
    case '/':
    case '':
        require __DIR__ . '/../src/Views/home.php';
        break;
    case '/dashboard':
        require __DIR__ . '/../src/Views/dashboard.php';
        break;
    case '/usuarios':
        require __DIR__ . '/../src/Views/usuarios.php';
        break;
    case '/clientes':
        require __DIR__ . '/../src/Views/clientes.php';
        break;
    case '/ventas':
        require __DIR__ . '/../src/Views/ventas.php';
        break;
    case '/reportes':
        require __DIR__ . '/../src/Views/reportes.php';
        break;
    case '/pedidos':
        require __DIR__ . '/../src/Views/pedidos.php';
        break;
    case '/inventario':
        require __DIR__ . '/../src/Views/inventario.php';
        break;
    case '/login':
        require __DIR__ . '/../src/Views/auth/login.php';
        break;
    case '/auth':
        require_once __DIR__ . '/../src/Auth/auth.php';
        break;
    case '/logout':
        require_once __DIR__ . '/../src/Auth/logout.php';
        break;
    case '/reset_password':
        require_once __DIR__ . '/../src/Auth/reset_password.php';
        break;
    default:
        http_response_code(404);
        echo '<h1>404 - Página no encontrada</h1>';
        break;
}
?>
