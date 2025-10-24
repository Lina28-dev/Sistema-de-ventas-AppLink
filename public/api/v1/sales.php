<?php
/**
 * API REST para gestión de ventas
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

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../src/Utils/AuditoriaLogger.php';

require_once __DIR__ . '/../config/Database.php';

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
                listarVentas($pdo);
            } elseif ($action === 'estadisticas') {
                obtenerEstadisticas($pdo);
            } elseif ($action === 'buscar') {
                buscarVentas($pdo, $_GET['termino'] ?? '');
            } else {
                listarVentas($pdo);
            }
            break;
            
        case 'POST':
            crearVenta($pdo, $auditoria);
            break;
            
        case 'PUT':
            $id = $_GET['id'] ?? null;
            if ($id) {
                actualizarVenta($pdo, $auditoria, $id);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'ID requerido']);
            }
            break;
            
        case 'DELETE':
            $id = $_GET['id'] ?? null;
            if ($id) {
                eliminarVenta($pdo, $auditoria, $id);
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

function listarVentas($pdo) {
    // Verificar si la tabla ventas existe
    $tableExists = $pdo->query("SELECT 1 FROM information_schema.tables WHERE table_name = 'ventas'")->rowCount() > 0;
    
    if (!$tableExists) {
        // Crear tabla si no existe
        $pdo->exec("
            CREATE TABLE fs_ventas (
                id INT AUTO_INCREMENT PRIMARY KEY,
                numero_venta VARCHAR(50) UNIQUE NOT NULL,
                cliente_id INT,
                cliente_nombre VARCHAR(255),
                productos JSON,
                subtotal DECIMAL(10,2),
                descuento DECIMAL(10,2) DEFAULT 0,
                total DECIMAL(10,2),
                metodo_pago ENUM('efectivo', 'tarjeta', 'transferencia', 'credito') DEFAULT 'efectivo',
                estado ENUM('completada', 'pendiente', 'cancelada') DEFAULT 'completada',
                fecha_venta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                observaciones TEXT,
                id_usuario INT,
                FOREIGN KEY (cliente_id) REFERENCES fs_clientes(id) ON DELETE SET NULL
            )
        ");
    }
    
    $stmt = $pdo->query("
        SELECT 
            v.*,
            c.nombre_completo as cliente_nombre_completo,
            c.telefono as cliente_telefono
        FROM ventas v
        LEFT JOIN clientes c ON v.cliente_id = c.id
        ORDER BY v.fecha_venta DESC
    ");
    
    $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $ventas,
        'total' => count($ventas)
    ]);
}

function obtenerEstadisticas($pdo) {
    $tableExists = $pdo->query("SELECT 1 FROM information_schema.tables WHERE table_name = 'ventas'")->rowCount() > 0;
    
    if (!$tableExists) {
        echo json_encode([
            'success' => true,
            'data' => [
                'total' => 0,
                'hoy' => 0,
                'mes' => 0,
                'total_dinero' => 0,
                'ventasHoy' => 0,
                'ventasMes' => 0,
                'transacciones' => 0,
                'ticketPromedio' => 0,
                'ventasUltimas24h' => []
            ]
        ]);
        return;
    }
    
    $stats = [];
    
    // Estadísticas básicas
    $stats['total'] = (int)$pdo->query("SELECT COUNT(*) FROM ventas WHERE estado = 'completada'")->fetchColumn();
    $stats['hoy'] = (int)$pdo->query("SELECT COUNT(*) FROM ventas WHERE DATE(fecha_venta) = CURRENT_DATE AND estado = 'completada'")->fetchColumn();
    $stats['mes'] = (int)$pdo->query("SELECT COUNT(*) FROM ventas WHERE EXTRACT(MONTH FROM fecha_venta) = EXTRACT(MONTH FROM CURRENT_DATE) AND EXTRACT(YEAR FROM fecha_venta) = EXTRACT(YEAR FROM CURRENT_DATE) AND estado = 'completada'")->fetchColumn();
    $stats['total_dinero'] = (float)$pdo->query("SELECT COALESCE(SUM(total), 0) FROM ventas WHERE estado = 'completada'")->fetchColumn();
    
    // Estadísticas para dashboard
    $stats['ventasHoy'] = (float)$pdo->query("SELECT COALESCE(SUM(total), 0) FROM ventas WHERE DATE(fecha_venta) = CURRENT_DATE AND estado = 'completada'")->fetchColumn();
    $stats['ventasMes'] = (float)$pdo->query("SELECT COALESCE(SUM(total), 0) FROM ventas WHERE EXTRACT(MONTH FROM fecha_venta) = EXTRACT(MONTH FROM CURRENT_DATE) AND EXTRACT(YEAR FROM fecha_venta) = EXTRACT(YEAR FROM CURRENT_DATE) AND estado = 'completada'")->fetchColumn();
    $stats['transacciones'] = $stats['total'];
    $stats['ticketPromedio'] = $stats['total'] > 0 ? round($stats['total_dinero'] / $stats['total'], 2) : 0;
    
    // Ventas de las últimas 24 horas para gráfico en tiempo real
    $ventasUltimas24h = $pdo->query("
        SELECT 
            EXTRACT(HOUR FROM fecha_venta) as hora,
            COUNT(*) as cantidad,
            COALESCE(SUM(total), 0) as monto
        FROM ventas 
        WHERE fecha_venta >= NOW() - INTERVAL '24 HOUR'
        AND estado = 'completada'
        GROUP BY EXTRACT(HOUR FROM fecha_venta)
        ORDER BY hora
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    $stats['ventasUltimas24h'] = $ventasUltimas24h;
    
    // Últimas ventas para actividad reciente
    $ultimasVentas = $pdo->query("
        SELECT 
            v.id,
            v.total,
            v.fecha_venta,
            COALESCE(c.nombre_completo, 'Cliente general') as cliente_nombre
        FROM ventas v
        LEFT JOIN clientes c ON v.cliente_id = c.id
        WHERE v.estado = 'completada'
        ORDER BY v.fecha_venta DESC
        LIMIT 10
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    $stats['ultimasVentas'] = $ultimasVentas;
    
    echo json_encode([
        'success' => true,
        'data' => $stats
    ]);
}

function crearVenta($pdo, $auditoria) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Datos inválidos']);
        return;
    }
    
    try {
        // Generar número de venta único
        $numeroVenta = 'VTA-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        $stmt = $pdo->prepare("
            INSERT INTO ventas 
            (numero_venta, cliente_id, productos, subtotal, descuento, total, metodo_pago, estado, observaciones, usuario_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $numeroVenta,
            $input['cliente_id'] ?? null,
            json_encode($input['productos'] ?? []),
            $input['subtotal'] ?? 0,
            $input['descuento'] ?? 0,
            $input['total'] ?? 0,
            $input['metodo_pago'] ?? 'efectivo',
            $input['estado'] ?? 'completada',
            $input['observaciones'] ?? null,
            $_SESSION['user_id'] ?? null
        ]);
        
        $ventaId = $pdo->lastInsertId();
        
        $auditoria->registrarActividad(
            'fs_ventas',
            'INSERT',
            $ventaId,
            null,
            $input,
            "Venta creada: $numeroVenta - Total: $" . ($input['total'] ?? 0)
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Venta registrada exitosamente',
            'id' => $ventaId,
            'numero_venta' => $numeroVenta
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al registrar venta: ' . $e->getMessage()]);
    }
}

function actualizarVenta($pdo, $auditoria, $id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Datos inválidos']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM ventas WHERE id = ?");
        $stmt->execute([$id]);
        $datosAnteriores = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$datosAnteriores) {
            http_response_code(404);
            echo json_encode(['error' => 'Venta no encontrada']);
            return;
        }
        
        $stmt = $pdo->prepare("
            UPDATE ventas SET 
                cliente_id = ?, 
                productos = ?, 
                subtotal = ?, 
                descuento = ?, 
                total = ?, 
                metodo_pago = ?, 
                estado = ?, 
                observaciones = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $input['cliente_id'] ?? null,
            json_encode($input['productos'] ?? []),
            $input['subtotal'] ?? 0,
            $input['descuento'] ?? 0,
            $input['total'] ?? 0,
            $input['metodo_pago'] ?? 'efectivo',
            $input['estado'] ?? 'completada',
            $input['observaciones'] ?? null,
            $id
        ]);
        
        $auditoria->registrarActividad(
            'fs_ventas',
            'UPDATE',
            $id,
            $datosAnteriores,
            $input,
            "Venta actualizada: {$datosAnteriores['numero_venta']}"
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Venta actualizada exitosamente'
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar venta: ' . $e->getMessage()]);
    }
}

function eliminarVenta($pdo, $auditoria, $id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM ventas WHERE id = ?");
        $stmt->execute([$id]);
        $venta = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$venta) {
            http_response_code(404);
            echo json_encode(['error' => 'Venta no encontrada']);
            return;
        }
        
        $stmt = $pdo->prepare("DELETE FROM ventas WHERE id = ?");
        $stmt->execute([$id]);
        
        $auditoria->registrarActividad(
            'fs_ventas',
            'DELETE',
            $id,
            $venta,
            null,
            "Venta eliminada: {$venta['numero_venta']}"
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Venta eliminada exitosamente'
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al eliminar venta: ' . $e->getMessage()]);
    }
}

function buscarVentas($pdo, $termino) {
    $termino = "%$termino%";
    
    $stmt = $pdo->prepare("
        SELECT 
            v.*,
            c.nombre_completo as cliente_nombre_completo,
            c.telefono as cliente_telefono
        FROM ventas v
        LEFT JOIN clientes c ON v.cliente_id = c.id
        WHERE v.numero_venta LIKE ? 
           OR c.nombre_completo LIKE ?
        ORDER BY v.fecha_venta DESC
    ");
    
    $stmt->execute([$termino, $termino]);
    $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $ventas,
        'total' => count($ventas)
    ]);
}
?>