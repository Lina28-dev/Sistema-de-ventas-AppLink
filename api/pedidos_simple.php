<?php
/**
 * API simplificada para pedidos usando conexión directa MySQL
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    // Conexión directa a MySQL
    $host = 'localhost';
    $dbname = 'fs_clientes';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';
    
    switch ($method) {
        case 'GET':
            if ($action === 'estadisticas') {
                obtenerEstadisticas($pdo);
            } else {
                listarPedidos($pdo);
            }
            break;
            
        case 'POST':
            crearPedido($pdo);
            break;
            
        case 'PUT':
            $id = $_GET['id'] ?? null;
            if ($id) {
                actualizarPedido($pdo, $id);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'ID requerido']);
            }
            break;
            
        case 'DELETE':
            $id = $_GET['id'] ?? null;
            if ($id) {
                eliminarPedido($pdo, $id);
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
    try {
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
    } catch (Exception $e) {
        echo json_encode(['error' => 'Error al listar pedidos: ' . $e->getMessage()]);
    }
}

function obtenerEstadisticas($pdo) {
    try {
        $stats = [];
        $stats['total'] = $pdo->query("SELECT COUNT(*) FROM fs_pedidos")->fetchColumn();
        $stats['pendientes'] = $pdo->query("SELECT COUNT(*) FROM fs_pedidos WHERE estado = 'pendiente'")->fetchColumn();
        $stats['completados'] = $pdo->query("SELECT COUNT(*) FROM fs_pedidos WHERE estado = 'completado'")->fetchColumn();
        $stats['hoy'] = $pdo->query("SELECT COUNT(*) FROM fs_pedidos WHERE DATE(fecha_pedido) = CURRENT_DATE")->fetchColumn();
        
        echo json_encode([
            'success' => true,
            'data' => $stats
        ]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Error al obtener estadísticas: ' . $e->getMessage()]);
    }
}

function crearPedido($pdo) {
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
            $input['cliente_nombre'] ?? 'Cliente General',
            json_encode($input['productos'] ?? []),
            $input['total'] ?? 0,
            $input['estado'] ?? 'pendiente',
            $input['fecha_entrega'] ?? null,
            $input['observaciones'] ?? null,
            1 // ID usuario por defecto
        ]);
        
        $pedidoId = $pdo->lastInsertId();
        
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

function actualizarPedido($pdo, $id) {
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
        
        echo json_encode([
            'success' => true,
            'message' => 'Pedido actualizado exitosamente'
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar pedido: ' . $e->getMessage()]);
    }
}

function eliminarPedido($pdo, $id) {
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
        
        echo json_encode([
            'success' => true,
            'message' => 'Pedido eliminado exitosamente'
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al eliminar pedido: ' . $e->getMessage()]);
    }
}
?>