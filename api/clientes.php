<?php
/**
 * API REST para gestión de clientes
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

session_start();

// Verificar autenticación - más flexible para desarrollo
$permitir_acceso = false;

// Permitir si viene del mismo dominio
$referer = $_SERVER['HTTP_REFERER'] ?? '';
if (strpos($referer, 'localhost/Sistema-de-ventas-AppLink-main') !== false) {
    $permitir_acceso = true;
}

// Permitir si está autenticado en sesión
if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
    $permitir_acceso = true;
}

// Para desarrollo: permitir acceso local temporal
if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1') {
    $permitir_acceso = true;
}

if (!$permitir_acceso) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../src/Utils/AuditoriaLogger.php';

$config = include __DIR__ . '/../config/app.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset={$config['db']['charset']}", 
        $config['db']['user'], 
        $config['db']['pass']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Crear tabla fs_clientes si no existe
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS fs_clientes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre_completo VARCHAR(255) NOT NULL,
            email VARCHAR(255),
            telefono VARCHAR(50),
            direccion TEXT,
            ciudad VARCHAR(100),
            localidad VARCHAR(100),
            codigo_postal VARCHAR(20),
            CC VARCHAR(50),
            descuento DECIMAL(5,2) DEFAULT 0,
            revendedora TINYINT(1) DEFAULT 0,
            fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            id_usuario INT,
            INDEX idx_nombre (nombre_completo),
            INDEX idx_telefono (telefono),
            INDEX idx_email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Crear tablas de auditoría si no existen
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS auditoria_general (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tabla VARCHAR(100) NOT NULL,
            accion VARCHAR(50) NOT NULL,
            registro_id VARCHAR(100),
            datos_anteriores JSON,
            datos_nuevos JSON,
            usuario_id INT,
            usuario_nombre VARCHAR(255),
            ip_address VARCHAR(45),
            user_agent TEXT,
            observaciones TEXT,
            fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_tabla (tabla),
            INDEX idx_accion (accion),
            INDEX idx_usuario (usuario_id),
            INDEX idx_fecha (fecha_hora)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS auditoria_sesiones (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT,
            usuario_nombre VARCHAR(255),
            accion VARCHAR(50) NOT NULL,
            ip_address VARCHAR(45),
            user_agent TEXT,
            duracion_sesion INT,
            detalles JSON,
            fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_usuario (usuario_id),
            INDEX idx_accion (accion),
            INDEX idx_fecha (fecha_hora)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS metricas_diarias (
            id INT AUTO_INCREMENT PRIMARY KEY,
            fecha DATE NOT NULL UNIQUE,
            logins_exitosos INT DEFAULT 0,
            logins_fallidos INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_fecha (fecha)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Inicializar auditoría solo si la clase existe
    $auditoria = null;
    if (class_exists('AuditoriaLogger')) {
        $auditoria = new AuditoriaLogger($pdo);
    }
    
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';
    
    switch ($method) {
        case 'GET':
            if ($action === 'listar') {
                listarClientes($pdo);
            } elseif ($action === 'estadisticas') {
                obtenerEstadisticas($pdo);
            } elseif ($action === 'buscar') {
                buscarClientes($pdo, $_GET['termino'] ?? '');
            } else {
                listarClientes($pdo);
            }
            break;
            
        case 'POST':
            crearCliente($pdo, $auditoria);
            break;
            
        case 'PUT':
            $id = $_GET['id'] ?? null;
            if ($id) {
                actualizarCliente($pdo, $auditoria, $id);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'ID requerido']);
            }
            break;
            
        case 'DELETE':
            $id = $_GET['id'] ?? null;
            if ($id) {
                eliminarCliente($pdo, $auditoria, $id);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'ID requerido']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error interno: ' . $e->getMessage()]);
}

function listarClientes($pdo) {
    $stmt = $pdo->query("
        SELECT 
            id,
            nombre_completo,
            email,
            telefono,
            direccion,
            ciudad,
            localidad,
            codigo_postal,
            CC,
            descuento,
            revendedora,
            fecha_registro,
            id_usuario
        FROM fs_clientes 
        ORDER BY fecha_registro DESC
    ");
    
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $clientes,
        'total' => count($clientes)
    ]);
}

function obtenerEstadisticas($pdo) {
    $stats = [];
    
    // Total clientes
    $stats['total'] = $pdo->query("SELECT COUNT(*) FROM fs_clientes")->fetchColumn();
    
    // Clientes con historial (revendedoras)
    $stats['con_historial'] = $pdo->query("SELECT COUNT(*) FROM fs_clientes WHERE revendedora = 1")->fetchColumn();
    
    // Nuevos este mes
    $stats['nuevos_mes'] = $pdo->query("
        SELECT COUNT(*) FROM fs_clientes 
        WHERE MONTH(fecha_registro) = MONTH(CURDATE()) 
        AND YEAR(fecha_registro) = YEAR(CURDATE())
    ")->fetchColumn();
    
    // Activos
    $stats['activos'] = $stats['total'];
    
    echo json_encode([
        'success' => true,
        'data' => $stats
    ]);
}

function crearCliente($pdo, $auditoria) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Datos inválidos']);
        return;
    }
    
    // Validar campos requeridos
    $requeridos = ['nombre', 'telefono'];
    foreach ($requeridos as $campo) {
        if (empty($input[$campo])) {
            http_response_code(400);
            echo json_encode(['error' => "El campo $campo es requerido"]);
            return;
        }
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO fs_clientes 
            (nombre_completo, email, telefono, direccion, ciudad, localidad, codigo_postal, CC, descuento, revendedora, id_usuario) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $input['nombre'],
            $input['email'] ?? null,
            $input['telefono'],
            $input['direccion'] ?? null,
            $input['ciudad'] ?? null,
            $input['localidad'] ?? null,
            $input['codigo_postal'] ?? null,
            $input['cc'] ?? null,
            (int)($input['descuento'] ?? 0),
            isset($input['tipo']) && $input['tipo'] === '1' ? 1 : 0,
            $_SESSION['user_id'] ?? null
        ]);
        
        $clienteId = $pdo->lastInsertId();
        
        // Registrar en auditoría si está disponible
        if ($auditoria) {
            $auditoria->registrarActividad(
                'fs_clientes',
                'INSERT',
                $clienteId,
                null,
                $input,
                "Cliente creado: {$input['nombre']}"
            );
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Cliente creado exitosamente',
            'id' => $clienteId
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al crear cliente: ' . $e->getMessage()]);
    }
}

function actualizarCliente($pdo, $auditoria, $id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Datos inválidos']);
        return;
    }
    
    try {
        // Obtener datos anteriores para auditoría
        $stmt = $pdo->prepare("SELECT * FROM fs_clientes WHERE id = ?");
        $stmt->execute([$id]);
        $datosAnteriores = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$datosAnteriores) {
            http_response_code(404);
            echo json_encode(['error' => 'Cliente no encontrado']);
            return;
        }
        
        $stmt = $pdo->prepare("
            UPDATE fs_clientes SET 
                nombre_completo = ?, 
                email = ?, 
                telefono = ?, 
                direccion = ?, 
                ciudad = ?, 
                localidad = ?, 
                codigo_postal = ?, 
                CC = ?, 
                descuento = ?, 
                revendedora = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $input['nombre'],
            $input['email'] ?? null,
            $input['telefono'],
            $input['direccion'] ?? null,
            $input['ciudad'] ?? null,
            $input['localidad'] ?? null,
            $input['codigo_postal'] ?? null,
            $input['cc'] ?? null,
            (int)($input['descuento'] ?? 0),
            isset($input['tipo']) && $input['tipo'] === '1' ? 1 : 0,
            $id
        ]);
        
        // Registrar en auditoría si está disponible
        if ($auditoria) {
            $auditoria->registrarActividad(
                'fs_clientes',
                'UPDATE',
                $id,
                $datosAnteriores,
                $input,
                "Cliente actualizado: {$input['nombre']}"
            );
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Cliente actualizado exitosamente'
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar cliente: ' . $e->getMessage()]);
    }
}

function eliminarCliente($pdo, $auditoria, $id) {
    try {
        // Obtener datos para auditoría
        $stmt = $pdo->prepare("SELECT * FROM fs_clientes WHERE id = ?");
        $stmt->execute([$id]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$cliente) {
            http_response_code(404);
            echo json_encode(['error' => 'Cliente no encontrado']);
            return;
        }
        
        $stmt = $pdo->prepare("DELETE FROM fs_clientes WHERE id = ?");
        $stmt->execute([$id]);
        
        // Registrar en auditoría si está disponible
        if ($auditoria) {
            $auditoria->registrarActividad(
                'fs_clientes',
                'DELETE',
                $id,
                $cliente,
                null,
                "Cliente eliminado: {$cliente['nombre_completo']}"
            );
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Cliente eliminado exitosamente'
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al eliminar cliente: ' . $e->getMessage()]);
    }
}

function buscarClientes($pdo, $termino) {
    $termino = "%$termino%";
    
    $stmt = $pdo->prepare("
        SELECT 
            id, nombre_completo, email, telefono, direccion, ciudad, localidad, 
            codigo_postal, CC, descuento, revendedora, fecha_registro, id_usuario
        FROM fs_clientes 
        WHERE nombre_completo LIKE ? 
           OR telefono LIKE ? 
           OR email LIKE ? 
           OR CC LIKE ?
        ORDER BY fecha_registro DESC
    ");
    
    $stmt->execute([$termino, $termino, $termino, $termino]);
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $clientes,
        'total' => count($clientes)
    ]);
}
?>