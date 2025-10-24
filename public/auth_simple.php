<?php
// Configurar zona horaria y errores
date_default_timezone_set('America/Bogota');
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Inicializar la sesión con configuración segura si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0);
    session_start();
}

// Configurar header para JSON
header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    // Validar que los campos requeridos existan
    if (!isset($_POST['nick']) || !isset($_POST['password']) || !isset($_POST['rol'])) {
        throw new Exception('Faltan campos requeridos');
    }

    // Validar que el rol seleccionado sea válido
    $allowed_roles = ['administrador', 'empleado', 'cliente'];
    $selected_role = $_POST['rol'];
    
    if (!in_array($selected_role, $allowed_roles)) {
        throw new Exception('Tipo de usuario no válido');
    }

    // Conexión a la base de datos
    $conn = new mysqli('localhost', 'root', '', 'fs_clientes');
    if ($conn->connect_error) {
        throw new Exception("Error de conexión: " . $conn->connect_error);
    }

    // Escapar valores para prevenir SQL injection
    $nick = $conn->real_escape_string($_POST['nick']);
    $password = $_POST['password'];

    // Buscar usuario
    $sql = "SELECT * FROM fs_usuarios WHERE nick = '$nick'";
    $result = $conn->query($sql);

    if ($result->num_rows === 0) {
        throw new Exception('Usuario no encontrado');
    }

    $user = $result->fetch_assoc();

    // Verificar contraseña
    if (!password_verify($password, $user['password'])) {
        throw new Exception('Contraseña incorrecta');
    }

    // Determinar rol del usuario
    $user_role = 'cliente'; // Por defecto
    if (!empty($user['rol'])) {
        $user_role = $user['rol'];
    } else {
        // Lógica legacy para usuarios sin rol definido
        if ($user['is_admin']) {
            $user_role = 'administrador';
        } elseif ($user['is_medium']) {
            $user_role = 'empleado';
        }
    }

    // Verificar que el rol seleccionado coincida con el del usuario
    if ($user_role !== $selected_role) {
        throw new Exception("Su cuenta no tiene permisos de $selected_role. Por favor, seleccione el tipo de usuario correcto.");
    }

    // Crear sesión con información de roles
    $_SESSION['user_id'] = $user['id_usuario'];
    $_SESSION['user_nick'] = $user['nick'];
    $_SESSION['user_name'] = $user['nombre'] . ' ' . $user['apellido'];
    $_SESSION['user_role'] = $user_role;
    $_SESSION['is_admin'] = ($user_role === 'administrador');
    $_SESSION['is_medium'] = ($user_role === 'empleado');
    $_SESSION['is_visitor'] = ($user_role === 'cliente');
    $_SESSION['authenticated'] = true;
    $_SESSION['login_time'] = time();

    // Definir permisos basados en el rol
    $_SESSION['permissions'] = [
        'dashboard' => true,
        'pedidos' => in_array($user_role, ['administrador', 'empleado', 'cliente']),
        'ventas' => in_array($user_role, ['administrador', 'empleado']),
        'inventario' => ($user_role === 'administrador'),
        'clientes' => ($user_role === 'administrador'),
        'reportes' => in_array($user_role, ['administrador', 'empleado']),
        'usuarios' => ($user_role === 'administrador'),
        'configuracion' => ($user_role === 'administrador')
    ];

    // Registrar último acceso
    $update_sql = "UPDATE fs_usuarios SET ultimo_acceso = NOW() WHERE id_usuario = " . $user['id_usuario'];
    $conn->query($update_sql);

    echo json_encode([
        'success' => true,
        'message' => 'Login exitoso',
        'user' => [
            'nick' => $user['nick'],
            'nombre' => $user['nombre'],
            'apellido' => $user['apellido'],
            'rol' => $user_role,
            'permissions' => $_SESSION['permissions']
        ],
        'redirect' => '/Sistema-de-ventas-AppLink-main/public/dashboard'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>