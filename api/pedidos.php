<?php
/**
 * API REST para gestión de pedidos
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

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
    
    $auditoria = new AuditoriaLogger($pdo);
    
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';
    
    switch ($method) {
        case 'GET':
            if ($action === 'listar') {
                listarPedidos($pdo);
            } elseif ($action === 'estadisticas') {
                obtenerEstadisticas($pdo);
            } elseif ($action === 'buscar') {
                buscarPedidos($pdo, $_GET['termino'] ?? '');
            } else {
                listarPedidos($pdo);
            }
            break;
            
        case 'POST':
            crearPedido($pdo, $auditoria);
            break;
            
        case 'PUT':
            $id = $_GET['id'] ?? null;
            if ($id) {
                actualizarPedido($pdo, $auditoria, $id);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'ID requerido']);
            }
            break;
            
        case 'DELETE':
            $id = $_GET['id'] ?? null;
            if ($id) {
                eliminarPedido($pdo, $auditoria, $id);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'ID requerido']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}

function listarPedidos($pdo) {
    $stmt = $pdo->query("
        SELECT 
            p.*,
            c.nombre as cliente_nombre_completo,
            c.telefono as cliente_telefono
        FROM fs_pedidos p
        LEFT JOIN fs_clientes c ON p.cliente_id = c.id
        ORDER BY p.fecha_pedido DESC
    ");
    
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $pedidos,
        'total' => count($pedidos)
    ]);
}

function obtenerEstadisticas($pdo) {
    $stats = [];
    $stats['total'] = $pdo->query("SELECT COUNT(*) FROM fs_pedidos")->fetchColumn();
    $stats['pendientes'] = $pdo->query("SELECT COUNT(*) FROM fs_pedidos WHERE estado = 'pendiente'")->fetchColumn();
    $stats['completados'] = $pdo->query("SELECT COUNT(*) FROM fs_pedidos WHERE estado = 'completado'")->fetchColumn();
    $stats['hoy'] = $pdo->query("SELECT COUNT(*) FROM fs_pedidos WHERE DATE(fecha_pedido) = CURRENT_DATE")->fetchColumn();
    
    echo json_encode([
        'success' => true,
        'data' => $stats
    ]);
}

function crearPedido($pdo, $auditoria) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Datos inválidos']);
        return;
    }
    
    try {
        // Generar número de pedido único
        $numeroPedido = 'PED-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        $stmt = $pdo->prepare("
            INSERT INTO fs_pedidos 
            (numero_pedido, cliente_id, cliente_nombre, productos, total, estado, fecha_entrega, observaciones, id_usuario) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $numeroPedido,
            $input['cliente_id'] ?? null,
            $input['cliente_nombre'] ?? null,
            json_encode($input['productos'] ?? []),
            $input['total'] ?? 0,
            $input['estado'] ?? 'pendiente',
            $input['fecha_entrega'] ?? null,
            $input['observaciones'] ?? null,
            $_SESSION['user_id'] ?? null
        ]);
        
        $pedidoId = $pdo->lastInsertId();
        
        $auditoria->registrarActividad(
            'fs_pedidos',
            'INSERT',
            $pedidoId,
            null,
            $input,
            "Pedido creado: $numeroPedido"
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Pedido creado exitosamente',
            'id' => $pedidoId,
            'numero_pedido' => $numeroPedido
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al crear pedido: ' . $e->getMessage()]);
    }
}

function actualizarPedido($pdo, $auditoria, $id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Datos inválidos']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM fs_pedidos WHERE id = ?");
        $stmt->execute([$id]);
        $datosAnteriores = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$datosAnteriores) {
            http_response_code(404);
            echo json_encode(['error' => 'Pedido no encontrado']);
            return;
        }
        
        $stmt = $pdo->prepare("
            UPDATE fs_pedidos SET 
                cliente_id = ?, 
                cliente_nombre = ?, 
                productos = ?, 
                total = ?, 
                estado = ?, 
                fecha_entrega = ?, 
                observaciones = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $input['cliente_id'] ?? null,
            $input['cliente_nombre'] ?? null,
            json_encode($input['productos'] ?? []),
            $input['total'] ?? 0,
            $input['estado'] ?? 'pendiente',
            $input['fecha_entrega'] ?? null,
            $input['observaciones'] ?? null,
            $id
        ]);
        
        $auditoria->registrarActividad(
            'fs_pedidos',
            'UPDATE',
            $id,
            $datosAnteriores,
            $input,
            "Pedido actualizado: {$datosAnteriores['numero_pedido']}"
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Pedido actualizado exitosamente'
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar pedido: ' . $e->getMessage()]);
    }
}

function eliminarPedido($pdo, $auditoria, $id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM fs_pedidos WHERE id = ?");
        $stmt->execute([$id]);
        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$pedido) {
            http_response_code(404);
            echo json_encode(['error' => 'Pedido no encontrado']);
            return;
        }
        
        $stmt = $pdo->prepare("DELETE FROM fs_pedidos WHERE id = ?");
        $stmt->execute([$id]);
        
        $auditoria->registrarActividad(
            'fs_pedidos',
            'DELETE',
            $id,
            $pedido,
            null,
            "Pedido eliminado: {$pedido['numero_pedido']}"
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Pedido eliminado exitosamente'
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al eliminar pedido: ' . $e->getMessage()]);
    }
}

function buscarPedidos($pdo, $termino) {
    $termino = "%$termino%";
    
    $stmt = $pdo->prepare("
        SELECT 
            p.*,
            c.nombre as cliente_nombre_completo,
            c.telefono as cliente_telefono
        FROM fs_pedidos p
        LEFT JOIN fs_clientes c ON p.cliente_id = c.id
        WHERE p.numero_pedido LIKE ? 
           OR p.cliente_nombre LIKE ? 
           OR c.nombre LIKE ?
        ORDER BY p.fecha_pedido DESC
    ");
    
    $stmt->execute([$termino, $termino, $termino]);
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $pedidos,
        'total' => count($pedidos)
    ]);
}
?>
