<?php
// Configuración inicial de errores
error_reporting(E_ALL);
ini_set('display_errors', 0); // Se mostrará mediante ErrorHandler

// Inicializar el manejador de errores mejorado
require_once __DIR__ . '/../src/Utils/ErrorHandlerImproved.php';
App\Utils\ErrorHandler::init();

// Configurar zona horaria
date_default_timezone_set('America/Bogota');

// Inicializar la sesión con configuración segura
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Cambiar a 1 en HTTPS
session_start();

// Cargar configuración
require_once __DIR__ . '/../config/paths.php';
require_once __DIR__ . '/../config/app.php';

// Autoloader mejorado para namespaces
spl_autoload_register(function ($class) {
    // Convertir namespace a ruta de archivo
    $class = str_replace('App\\', '', $class);
    $class = str_replace('\\', '/', $class);
    
    $paths = [
        __DIR__ . '/../src/' . $class . '.php',
        MODELS_PATH . '/' . basename($class) . '.php',
        CONTROLLERS_PATH . '/' . basename($class) . '.php',
        UTILS_PATH . '/' . basename($class) . '.php'
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
        require VIEWS_PATH . '/home.php';
        break;
    case '/dashboard':
        require VIEWS_PATH . '/dashboard.php';
        break;
    case '/usuarios':
        require VIEWS_PATH . '/usuarios.php';
        break;
    case '/clientes':
        require VIEWS_PATH . '/clientes.php';
        break;
    case '/ventas':
        require VIEWS_PATH . '/ventas.php';
        break;
    case '/reportes':
        require VIEWS_PATH . '/reportes.php';
        break;
    case '/perfil':
        require VIEWS_PATH . '/perfil.php';
        break;
    case '/pedidos':
        require VIEWS_PATH . '/pedidos.php';
        break;
    case '/alta-baja':
        require VIEWS_PATH . '/alta_baja.php';
        break;
    case '/login':
        require VIEWS_PATH . '/auth/login.php';
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
        require VIEWS_PATH . '/404.php';
        break;
}
?>
