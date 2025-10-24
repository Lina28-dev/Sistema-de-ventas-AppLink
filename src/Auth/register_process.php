<?php
/**
 * Procesamiento del registro de usuarios
 * Sistema de Ventas AppLink
 */

// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Inicializar sesión
session_start();

// Incluir configuración
require_once __DIR__ . '/../../config/config.php';

// Función para responder con JSON
function jsonResponse($success, $message, $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Método no permitido');
}

try {
    // Conectar a la base de datos
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Error de conexión: " . $conn->connect_error);
    }

    // Obtener y validar datos del formulario
    $nombre = trim($_POST['nombre'] ?? '');
    $nick = trim($_POST['nick'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $dni = trim($_POST['dni'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $tipo_usuario = $_POST['tipo_usuario'] ?? 'cliente';

    // Validaciones básicas
    if (empty($nombre) || empty($nick) || empty($email) || empty($dni) || empty($password)) {
        jsonResponse(false, 'Todos los campos obligatorios deben ser completados');
    }

    // Validar formato de email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(false, 'El formato del correo electrónico no es válido');
    }

    // Validar formato de DNI
    if (!preg_match('/^\d{7,11}$/', $dni)) {
        jsonResponse(false, 'El DNI debe contener entre 7 y 11 dígitos numéricos');
    }

    // Validar que las contraseñas coincidan
    if ($password !== $confirm_password) {
        jsonResponse(false, 'Las contraseñas no coinciden');
    }

    // Validar longitud de contraseña
    if (strlen($password) < 8) {
        jsonResponse(false, 'La contraseña debe tener al menos 8 caracteres');
    }

    // Verificar que el DNI no esté ya registrado
    $check_dni = $conn->prepare("SELECT id_usuario FROM fs_usuarios WHERE dni = ?");
    $check_dni->bind_param("s", $dni);
    $check_dni->execute();
    if ($check_dni->get_result()->num_rows > 0) {
        jsonResponse(false, 'El número de identificación ya está registrado');
    }

    // Verificar que el email no esté ya registrado
    $check_email = $conn->prepare("SELECT id_usuario FROM fs_usuarios WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    if ($check_email->get_result()->num_rows > 0) {
        jsonResponse(false, 'El correo electrónico ya está registrado');
    }

    // Verificar que el nick no esté ya registrado
    $check_nick = $conn->prepare("SELECT id_usuario FROM fs_usuarios WHERE nick = ?");
    $check_nick->bind_param("s", $nick);
    $check_nick->execute();
    if ($check_nick->get_result()->num_rows > 0) {
        jsonResponse(false, 'El nombre de usuario ya está registrado');
    }

    // Encriptar contraseña
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Determinar apellido (por si se ingresó nombre completo)
    $nombres = explode(' ', $nombre, 2);
    $first_name = $nombres[0];
    $last_name = isset($nombres[1]) ? $nombres[1] : '';

    // Si el tipo de usuario es cliente, también crear en la tabla de clientes
    if ($tipo_usuario === 'cliente') {
        // Primero crear el usuario
        $stmt_user = $conn->prepare("INSERT INTO fs_usuarios (nombre, apellido, nick, dni, email, password, is_visitor) VALUES (?, ?, ?, ?, ?, ?, 1)");
        $stmt_user->bind_param("ssssss", $first_name, $last_name, $nick, $dni, $email, $password_hash);
        
        if (!$stmt_user->execute()) {
            throw new Exception("Error al crear usuario: " . $stmt_user->error);
        }
        
        $user_id = $conn->insert_id;

        // Obtener datos adicionales para cliente
        $direccion = trim($_POST['direccion'] ?? '');
        $ciudad = trim($_POST['ciudad'] ?? '');

        // Crear registro en tabla de clientes
        $stmt_client = $conn->prepare("INSERT INTO fs_clientes (nombre_completo, dni, telefono, email, direccion, ciudad) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_client->bind_param("ssssss", $nombre, $dni, $telefono, $email, $direccion, $ciudad);
        
        if (!$stmt_client->execute()) {
            throw new Exception("Error al crear cliente: " . $stmt_client->error);
        }

        jsonResponse(true, 'Registro exitoso como cliente. Ahora puedes iniciar sesión.');

    } else {
        // Registro como empleado
        $codigo_empleado = trim($_POST['codigo_empleado'] ?? '');
        $departamento = trim($_POST['departamento'] ?? '');

        if (empty($codigo_empleado) || empty($departamento)) {
            jsonResponse(false, 'El código de empleado y departamento son obligatorios');
        }

        // Verificar código de empleado único
        $check_code = $conn->prepare("SELECT id_usuario FROM fs_usuarios WHERE nick = ?");
        $check_code->bind_param("s", $codigo_empleado);
        $check_code->execute();
        if ($check_code->get_result()->num_rows > 0) {
            jsonResponse(false, 'El código de empleado ya está en uso');
        }

        // Crear usuario empleado (por defecto con permisos medios)
        $stmt_emp = $conn->prepare("INSERT INTO fs_usuarios (nombre, apellido, nick, dni, email, password, is_medium, is_visitor) VALUES (?, ?, ?, ?, ?, ?, 1, 0)");
        $stmt_emp->bind_param("ssssss", $first_name, $last_name, $codigo_empleado, $dni, $email, $password_hash);
        
        if (!$stmt_emp->execute()) {
            throw new Exception("Error al crear empleado: " . $stmt_emp->error);
        }

        jsonResponse(true, 'Registro exitoso como empleado. Tu cuenta está pendiente de activación por un administrador.');
    }

} catch (Exception $e) {
    error_log("Error en registro: " . $e->getMessage());
    jsonResponse(false, 'Error interno del servidor. Por favor, inténtalo nuevamente.');
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>