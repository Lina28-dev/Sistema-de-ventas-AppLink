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

// Log de debug
$debug_log = [];

try {
    $debug_log[] = "Iniciando proceso de autenticación";
    
    // Configurar header para JSON
    header('Content-Type: application/json; charset=utf-8');
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }
    
    $debug_log[] = "Método POST confirmado";
    
    // Validar que los campos requeridos existan
    if (!isset($_POST['nick']) || !isset($_POST['password']) || !isset($_POST['rol'])) {
        $debug_log[] = "Faltan campos: nick=" . (isset($_POST['nick']) ? 'OK' : 'FALTA') . 
                      ", password=" . (isset($_POST['password']) ? 'OK' : 'FALTA') . 
                      ", rol=" . (isset($_POST['rol']) ? 'OK' : 'FALTA');
        throw new Exception('Faltan campos requeridos');
    }
    
    $debug_log[] = "Campos requeridos presentes";
    
    // Validar que el rol seleccionado sea válido
    $allowed_roles = ['administrador', 'empleado', 'cliente'];
    $selected_role = $_POST['rol'];
    
    if (!in_array($selected_role, $allowed_roles)) {
        $debug_log[] = "Rol inválido: " . $selected_role;
        throw new Exception('Tipo de usuario no válido');
    }
    
    $debug_log[] = "Rol válido: " . $selected_role;
    
    // Conexión a la base de datos MySQL (XAMPP)
    $debug_log[] = "Intentando conexión a MySQL";
    
    try {
        // Conexión directa a MySQL para XAMPP
        $conn = new mysqli('localhost', 'root', '', 'fs_clientes');
        
        if ($conn->connect_error) {
            $debug_log[] = "Error MySQL: " . $conn->connect_error;
            throw new Exception("Error de conexión: " . $conn->connect_error);
        }
        
        // Configurar charset
        $conn->set_charset("utf8mb4");
        $debug_log[] = "Conexión MySQL exitosa";
        
    } catch (Exception $db_error) {
        $debug_log[] = "Error de conexión: " . $db_error->getMessage();
        throw new Exception("Error de conexión. Verifique que MySQL esté ejecutándose en XAMPP.");
    }
    
    $debug_log[] = "Conexión exitosa a la base de datos";
    
    // Escapar valores para prevenir SQL injection
    $nick = $_POST['nick'];
    $password = $_POST['password'];
    
    $debug_log[] = "Buscando usuario: " . $nick;
    
    // Buscar usuario en MySQL
    $nick_escaped = $conn->real_escape_string($nick);
    $sql = "SELECT * FROM fs_usuarios WHERE nick = '$nick_escaped'";
    $result = $conn->query($sql);
    
    if (!$result) {
        $debug_log[] = "Error en consulta SQL: " . $conn->error;
        throw new Exception('Error en la consulta de base de datos');
    }
    
    if ($result->num_rows === 0) {
        $debug_log[] = "Usuario no encontrado en MySQL";
        throw new Exception('Usuario no encontrado');
    }
    
    $user = $result->fetch_assoc();
    $user_id = $user['id_usuario'];
    
    $debug_log[] = "Usuario encontrado - ID: " . $user_id;
    
    // Verificar contraseña
    $debug_log[] = "Verificando contraseña";
    $stored_password = $user['password'];
    
    if (!password_verify($password, $stored_password)) {
        // También verificar si la contraseña está en texto plano (para compatibilidad)
        if ($password !== $stored_password) {
            $debug_log[] = "Contraseña incorrecta";
            throw new Exception('Contraseña incorrecta');
        } else {
            $debug_log[] = "Contraseña correcta (texto plano - se recomienda actualizar)";
        }
    } else {
        $debug_log[] = "Contraseña correcta (hash)";
    }
    
    // Determinar rol del usuario
    $user_role = 'cliente'; // Por defecto
    if (!empty($user['rol'])) {
        $user_role = $user['rol'];
        $debug_log[] = "Rol del usuario (campo rol): " . $user_role;
    } else {
        // Lógica legacy para usuarios sin rol definido
        if ($user['is_admin']) {
            $user_role = 'administrador';
            $debug_log[] = "Rol del usuario (is_admin): administrador";
        } elseif ($user['is_medium']) {
            $user_role = 'empleado';
            $debug_log[] = "Rol del usuario (is_medium): empleado";
        } else {
            $debug_log[] = "Rol del usuario (por defecto): cliente";
        }
    }
    
    // Verificar que el rol seleccionado coincida con el del usuario
    if ($user_role !== $selected_role) {
        $debug_log[] = "Rol no coincide - Usuario: $user_role, Seleccionado: $selected_role";
        throw new Exception("Su cuenta no tiene permisos de $selected_role. Su rol es: $user_role");
    }
    
    $debug_log[] = "Rol confirmado: " . $user_role;
    
    // Crear sesión con información de roles
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_nick'] = $user['nick'] ?? $user['usuario'];
    $_SESSION['user_name'] = ($user['nombre'] ?? '') . ' ' . ($user['apellido'] ?? '');
    $_SESSION['user_role'] = $user_role;
    $_SESSION['is_admin'] = ($user_role === 'administrador');
    $_SESSION['is_medium'] = ($user_role === 'empleado');
    $_SESSION['is_visitor'] = ($user_role === 'cliente');
    $_SESSION['authenticated'] = true;
    $_SESSION['login_time'] = time();
    
    $debug_log[] = "Sesión creada exitosamente";
    
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
    if (isset($using_postgresql) && $using_postgresql) {
        $stmt = $pdo->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = :id");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
    } else {
        $update_sql = "UPDATE fs_usuarios SET ultimo_acceso = NOW() WHERE id_usuario = " . $user_id;
        $conn->query($update_sql);
    }
    
    $debug_log[] = "Login completado exitosamente";
    
    // Incluir el middleware para manejo de redirecciones
    require_once __DIR__ . '/../Middleware/AuthMiddleware.php';
    $redirect_url = AuthMiddleware::redirectAfterLogin();
    
    echo json_encode([
        'success' => true,
        'message' => 'Login exitoso',
        'user' => [
            'nick' => $user['nick'] ?? $user['usuario'],
            'nombre' => $user['nombre'] ?? '',
            'apellido' => $user['apellido'] ?? '',
            'rol' => $user_role,
            'permissions' => $_SESSION['permissions']
        ],
        'redirect' => $redirect_url
    ]);

} catch (Exception $e) {
    $debug_log[] = "Error capturado: " . $e->getMessage();
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>