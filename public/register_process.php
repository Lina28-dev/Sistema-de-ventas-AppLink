<?php
/**
 * Procesamiento del registro de usuarios
 * Sistema de Ventas AppLink
 */

// Configuración de errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuración de respuesta JSON
header('Content-Type: application/json');

// Función para responder con JSON
function jsonResponse($success, $message, $data = null) {
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
    // Verificar que MySQL esté disponible
    if (!extension_loaded('mysqli')) {
        jsonResponse(false, 'Extensión MySQLi no está disponible en el servidor');
    }
    
    // Conectar a la base de datos
    $conn = new mysqli('localhost', 'root', '', 'fs_clientes');
    
    if ($conn->connect_error) {
        jsonResponse(false, 'Error de conexión a la base de datos: ' . $conn->connect_error . '. Verifica que XAMPP esté ejecutándose y que la base de datos "fs_clientes" exista.');
    }
    
    // Verificar que la tabla fs_usuarios existe
    $check_table = $conn->query("SHOW TABLES LIKE 'fs_usuarios'");
    if ($check_table->num_rows == 0) {
        jsonResponse(false, 'La tabla fs_usuarios no existe. Ejecuta las migraciones de base de datos primero.');
    }
    
    // Verificar que el campo cc existe
    $check_cc_field = $conn->query("SHOW COLUMNS FROM fs_usuarios LIKE 'cc'");
    if ($check_cc_field->num_rows == 0) {
        jsonResponse(false, 'El campo CC no existe en la tabla. Ejecuta el script de migración de CC primero.');
    }

    // Obtener y validar datos del formulario
    $nombre = trim($_POST['nombre'] ?? '');
    $nick = trim($_POST['nick'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $cc = trim($_POST['cc'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $tipo_usuario = $_POST['tipo_usuario'] ?? 'cliente';

    // Validaciones básicas
    if (empty($nombre) || empty($nick) || empty($email) || empty($cc) || empty($password)) {
        jsonResponse(false, 'Todos los campos obligatorios deben ser completados');
    }

    // Validar formato de email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(false, 'El formato del correo electrónico no es válido');
    }

    // Validar formato de CC
    if (!preg_match('/^\d{7,11}$/', $cc)) {
        jsonResponse(false, 'La CC debe contener entre 7 y 11 dígitos numéricos');
    }

    // Validar que las contraseñas coincidan
    if ($password !== $confirm_password) {
        jsonResponse(false, 'Las contraseñas no coinciden');
    }

    // Validar longitud de contraseña
    if (strlen($password) < 8) {
        jsonResponse(false, 'La contraseña debe tener al menos 8 caracteres');
    }

    // Verificar que la CC no esté ya registrada
    $check_cc = $conn->prepare("SELECT id_usuario FROM fs_usuarios WHERE cc = ?");
    $check_cc->bind_param("s", $cc);
    $check_cc->execute();
    if ($check_cc->get_result()->num_rows > 0) {
        jsonResponse(false, 'La cédula de ciudadanía ya está registrada');
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
        $stmt_user = $conn->prepare("INSERT INTO fs_usuarios (nombre, apellido, nick, cc, email, password, is_visitor) VALUES (?, ?, ?, ?, ?, ?, 1)");
        $stmt_user->bind_param("ssssss", $first_name, $last_name, $nick, $cc, $email, $password_hash);
        
        if (!$stmt_user->execute()) {
            throw new Exception("Error al crear usuario: " . $stmt_user->error);
        }
        
        $user_id = $conn->insert_id;

        // Obtener datos adicionales para cliente
        $direccion = trim($_POST['direccion'] ?? '');
        $ciudad = trim($_POST['ciudad'] ?? '');

        // Crear registro en tabla de clientes si existe
        $check_clientes_table = $conn->query("SHOW TABLES LIKE 'fs_clientes'");
        if ($check_clientes_table->num_rows > 0) {
            $stmt_client = $conn->prepare("INSERT INTO fs_clientes (nombre_completo, CC, telefono, email, direccion, ciudad) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_client->bind_param("ssssss", $nombre, $cc, $telefono, $email, $direccion, $ciudad);
            $stmt_client->execute();
        }

        jsonResponse(true, 'Registro exitoso como cliente. Ahora puedes iniciar sesión.');

    } else {
        // Registro como empleado
        $codigo_empleado = trim($_POST['codigo_empleado'] ?? '');
        $departamento = trim($_POST['departamento'] ?? '');

        if (empty($codigo_empleado) || empty($departamento)) {
            jsonResponse(false, 'El código de empleado y departamento son obligatorios');
        }

        // Crear usuario empleado (por defecto con permisos medios)
        $stmt_emp = $conn->prepare("INSERT INTO fs_usuarios (nombre, apellido, nick, cc, email, password, is_medium, is_visitor) VALUES (?, ?, ?, ?, ?, ?, 1, 0)");
        $stmt_emp->bind_param("ssssss", $first_name, $last_name, $codigo_empleado, $cc, $email, $password_hash);
        
        if (!$stmt_emp->execute()) {
            throw new Exception("Error al crear empleado: " . $stmt_emp->error);
        }

        jsonResponse(true, 'Registro exitoso como empleado. Tu cuenta está pendiente de activación por un administrador.');
    }

} catch (Exception $e) {
    error_log("Error en registro: " . $e->getMessage());
    jsonResponse(false, 'Error interno del servidor: ' . $e->getMessage());
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>