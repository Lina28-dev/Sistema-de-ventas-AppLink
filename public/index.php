<?php
// Inicializar la sesión
session_start();

// Cargar configuración de rutas
require_once __DIR__ . '/../config/paths.php';
require_once __DIR__ . '/../config/config.php';

// Autoloader para las clases
spl_autoload_register(function ($class) {
    $paths = [
        MODELS_PATH,
        CONTROLLERS_PATH,
        UTILS_PATH
    ];

    foreach ($paths as $path) {
        $file = $path . '/' . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
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
    case '/consulta':
        require VIEWS_PATH . '/consulta.php';
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