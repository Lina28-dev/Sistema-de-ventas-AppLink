<?php
// La sesión ya está iniciada en index.php
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    // Validar que los campos requeridos existan
    if (!isset($_POST['nick']) || !isset($_POST['password'])) {
        throw new Exception('Faltan campos requeridos');
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

    // Crear sesión
    $_SESSION['user_id'] = $user['id_usuario'];
    $_SESSION['user_nick'] = $user['nick'];
    $_SESSION['user_name'] = $user['nombre'] . ' ' . $user['apellido'];
    $_SESSION['is_admin'] = $user['is_admin'];
    $_SESSION['is_medium'] = $user['is_medium'];
    $_SESSION['is_visitor'] = $user['is_visitor'];
    $_SESSION['authenticated'] = true;

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
            'is_admin' => $user['is_admin'],
            'is_medium' => $user['is_medium'],
            'is_visitor' => $user['is_visitor']
        ]
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
