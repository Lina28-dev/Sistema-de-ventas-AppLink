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

    // Inicializar sistema de auditoría
    require_once __DIR__ . '/../Utils/AuditoriaLogger.php';
    $pdo = new PDO("mysql:host=localhost;dbname=fs_clientes;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $auditoria = new AuditoriaLogger($pdo);

    // Escapar valores para prevenir SQL injection
    $nick = $conn->real_escape_string($_POST['nick']);
    $password = $_POST['password'];

    // Buscar usuario
    $sql = "SELECT * FROM fs_usuarios WHERE nick = '$nick'";
    $result = $conn->query($sql);

    if ($result->num_rows === 0) {
        // Registrar intento de login con usuario inexistente
        try {
            $auditoria->registrarActividad(
                'fs_usuarios', 
                'LOGIN_FAILED', 
                0, 
                null, 
                ['nick' => $nick, 'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Desconocida'], 
                "Intento de login con usuario inexistente: $nick"
            );
        } catch (Exception $e) {
            // Si hay error con auditoría, no interrumpir el proceso
        }
        throw new Exception('Usuario no encontrado');
    }

    $user = $result->fetch_assoc();

    // Verificar contraseña
    if (!password_verify($password, $user['password'])) {
        // Registrar intento fallido
        $auditoria->registrarLogin($user['id_usuario'], $user['nick'], false);
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
    $_SESSION['login_time'] = time(); // Para calcular duración de sesión

    // Registrar último acceso
    $update_sql = "UPDATE fs_usuarios SET ultimo_acceso = NOW() WHERE id_usuario = " . $user['id_usuario'];
    $conn->query($update_sql);

    // Registrar login exitoso en auditoría
    $auditoria->registrarLogin($user['id_usuario'], $user['nombre'] . ' ' . $user['apellido'], true);

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
