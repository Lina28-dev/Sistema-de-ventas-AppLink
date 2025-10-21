<?php
// Configuración de CORS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Configuración de la base de datos
$host = 'localhost';
$dbname = 'sistema_ventas';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Crear tabla de usuarios si no existe
    $createTable = "
    CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        rol ENUM('admin', 'vendedor', 'usuario') DEFAULT 'usuario',
        activo BOOLEAN DEFAULT TRUE,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($createTable);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error de conexión a la base de datos: ' . $e->getMessage()
    ]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleGet();
            break;
        case 'POST':
            handlePost();
            break;
        case 'PUT':
            handlePut();
            break;
        case 'DELETE':
            handleDelete();
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}

function handleGet() {
    global $pdo;
    
    if (isset($_GET['id'])) {
        // Obtener usuario específico
        $stmt = $pdo->prepare("SELECT id, nombre, email, rol, activo, fecha_creacion FROM usuarios WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario) {
            echo json_encode(['success' => true, 'data' => $usuario]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        }
    } else {
        // Obtener todos los usuarios con estadísticas
        $stmt = $pdo->query("SELECT id, nombre, email, rol, activo, fecha_creacion FROM usuarios ORDER BY fecha_creacion DESC");
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener estadísticas
        $statsStmt = $pdo->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as activos,
                SUM(CASE WHEN rol = 'admin' THEN 1 ELSE 0 END) as administradores
            FROM usuarios
        ");
        $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $usuarios,
            'stats' => $stats,
            'total' => count($usuarios)
        ]);
    }
}

function handlePost() {
    global $pdo;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Si los datos vienen por FormData
    if (empty($input)) {
        $input = $_POST;
    }
    
    // Validar datos requeridos
    if (empty($input['nombre']) || empty($input['email']) || empty($input['password'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Nombre, email y contraseña son requeridos']);
        return;
    }
    
    // Validar email
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email no válido']);
        return;
    }
    
    // Verificar si el email ya existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$input['email']]);
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'El email ya está registrado']);
        return;
    }
    
    // Encriptar contraseña
    $hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);
    
    // Insertar usuario
    $stmt = $pdo->prepare("
        INSERT INTO usuarios (nombre, email, password, rol, activo) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $rol = isset($input['rol']) ? $input['rol'] : 'usuario';
    $activo = isset($input['activo']) ? (bool)$input['activo'] : true;
    
    $stmt->execute([
        $input['nombre'],
        $input['email'],
        $hashedPassword,
        $rol,
        $activo
    ]);
    
    $userId = $pdo->lastInsertId();
    
    // Registrar en log
    $logStmt = $pdo->prepare("
        INSERT INTO audit_log (tabla, accion, registro_id, datos_anteriores, datos_nuevos) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $logStmt->execute([
        'usuarios',
        'INSERT',
        $userId,
        null,
        json_encode($input)
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Usuario creado exitosamente',
        'id' => $userId
    ]);
}

function handlePut() {
    global $pdo;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($_GET['id']) || empty($input)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID y datos son requeridos']);
        return;
    }
    
    $id = $_GET['id'];
    
    // Obtener datos actuales
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    $currentData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$currentData) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        return;
    }
    
    // Construir query de actualización dinámicamente
    $updates = [];
    $params = [];
    
    if (isset($input['nombre'])) {
        $updates[] = "nombre = ?";
        $params[] = $input['nombre'];
    }
    
    if (isset($input['email'])) {
        // Verificar si el email ya existe (excepto el usuario actual)
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
        $stmt->execute([$input['email'], $id]);
        if ($stmt->fetch()) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'El email ya está registrado']);
            return;
        }
        $updates[] = "email = ?";
        $params[] = $input['email'];
    }
    
    if (isset($input['password']) && !empty($input['password'])) {
        $updates[] = "password = ?";
        $params[] = password_hash($input['password'], PASSWORD_DEFAULT);
    }
    
    if (isset($input['rol'])) {
        $updates[] = "rol = ?";
        $params[] = $input['rol'];
    }
    
    if (isset($input['activo'])) {
        $updates[] = "activo = ?";
        $params[] = (bool)$input['activo'];
    }
    
    if (empty($updates)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No hay datos para actualizar']);
        return;
    }
    
    $params[] = $id;
    
    $sql = "UPDATE usuarios SET " . implode(', ', $updates) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    // Registrar en log
    $logStmt = $pdo->prepare("
        INSERT INTO audit_log (tabla, accion, registro_id, datos_anteriores, datos_nuevos) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $logStmt->execute([
        'usuarios',
        'UPDATE',
        $id,
        json_encode($currentData),
        json_encode($input)
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Usuario actualizado exitosamente'
    ]);
}

function handleDelete() {
    global $pdo;
    
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID es requerido']);
        return;
    }
    
    $id = $_GET['id'];
    
    // Obtener datos actuales
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    $currentData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$currentData) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        return;
    }
    
    // No permitir eliminar el último admin
    if ($currentData['rol'] === 'admin') {
        $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'admin'");
        $adminCount = $stmt->fetchColumn();
        
        if ($adminCount <= 1) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No se puede eliminar el último administrador']);
            return;
        }
    }
    
    // Eliminar usuario
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    
    // Registrar en log
    $logStmt = $pdo->prepare("
        INSERT INTO audit_log (tabla, accion, registro_id, datos_anteriores, datos_nuevos) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $logStmt->execute([
        'usuarios',
        'DELETE',
        $id,
        json_encode($currentData),
        null
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Usuario eliminado exitosamente'
    ]);
}

// Crear tabla de log si no existe
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS audit_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tabla VARCHAR(50) NOT NULL,
            accion VARCHAR(10) NOT NULL,
            registro_id INT,
            datos_anteriores JSON,
            datos_nuevos JSON,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
} catch(PDOException $e) {
    // Log silencioso del error
    error_log("Error creando tabla audit_log: " . $e->getMessage());
}
?>