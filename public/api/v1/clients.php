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

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../src/Utils/AuditoriaLogger.php';

try {
    $pdo = new PDO(
        App\Config\Database::getDSN(),
        App\Config\Database::getUsername(),
        App\Config\Database::getPassword(),
        App\Config\Database::getOptions()
    );
    
    // Las tablas ya existen en PostgreSQL desde la migración
    // No necesitamos crear tablas aquí
    
    // Las tablas de auditoría se crearán por separado si es necesario
    // Por ahora comentamos para que funcione la API principal
    
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
            nombre,
            apellido,
            email,
            telefono,
            celular,
            direccion,
            ciudad,
            provincia,
            codigo_postal,
            pais,
            documento_numero as cc,
            descuento_personalizado as descuento,
            CASE WHEN estado = 'activo' THEN true ELSE false END as revendedora,
            created_at as fecha_registro,
            created_by as id_usuario
        FROM clientes 
        ORDER BY created_at DESC
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
    $stats['total'] = $pdo->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
    
    // Clientes activos
    $stats['con_historial'] = $pdo->query("SELECT COUNT(*) FROM clientes WHERE estado = 'activo'")->fetchColumn();
    
    // Nuevos este mes
    $stats['nuevos_mes'] = $pdo->query("
        SELECT COUNT(*) FROM clientes 
        WHERE EXTRACT(MONTH FROM created_at) = EXTRACT(MONTH FROM CURRENT_DATE) 
        AND EXTRACT(YEAR FROM created_at) = EXTRACT(YEAR FROM CURRENT_DATE)
    ")->fetchColumn();
    
    // Activos
    $stats['activos'] = $stats['con_historial'];
    
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
            INSERT INTO clientes 
            (nombre_completo, email, telefono, direccion, ciudad, localidad, codigo_postal, cc, descuento, revendedora, id_usuario) 
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
                'clientes',
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
        $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
        $stmt->execute([$id]);
        $datosAnteriores = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$datosAnteriores) {
            http_response_code(404);
            echo json_encode(['error' => 'Cliente no encontrado']);
            return;
        }
        
        $stmt = $pdo->prepare("
            UPDATE clientes SET 
                nombre_completo = ?, 
                email = ?, 
                telefono = ?, 
                direccion = ?, 
                ciudad = ?, 
                localidad = ?, 
                codigo_postal = ?, 
                cc = ?, 
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
                'clientes',
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
        $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
        $stmt->execute([$id]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$cliente) {
            http_response_code(404);
            echo json_encode(['error' => 'Cliente no encontrado']);
            return;
        }
        
        $stmt = $pdo->prepare("DELETE FROM clientes WHERE id = ?");
        $stmt->execute([$id]);
        
        // Registrar en auditoría si está disponible
        if ($auditoria) {
            $auditoria->registrarActividad(
                'clientes',
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
            codigo_postal, cc, descuento, revendedora, fecha_registro, id_usuario
        FROM clientes 
        WHERE nombre_completo LIKE ? 
           OR telefono LIKE ? 
           OR email LIKE ? 
           OR cc LIKE ?
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