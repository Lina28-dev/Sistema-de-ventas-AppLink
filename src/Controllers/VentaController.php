<?php
/**
 * Controlador de Ventas CRUD para Dashboard
 * Maneja todas las operaciones de ventas: crear, leer, actualizar, eliminar
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Iniciar sesión si no está activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Obtener configuración de base de datos
$config = require __DIR__ . '/../../config/app.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset={$config['db']['charset']}",
        $config['db']['user'],
        $config['db']['pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

// Determinar método HTTP
$metodo = $_SERVER['REQUEST_METHOD'];
$accion = $_GET['accion'] ?? '';

switch ($metodo) {
    case 'GET':
        if ($accion === 'listar') {
            listarVentas($pdo);
        } elseif ($accion === 'obtener' && isset($_GET['id'])) {
            obtenerVenta($pdo, $_GET['id']);
        } else {
            listarVentas($pdo);
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if ($accion === 'crear') {
            crearVenta($pdo, $data);
        }
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        if ($accion === 'actualizar' && isset($_GET['id'])) {
            actualizarVenta($pdo, $_GET['id'], $data);
        }
        break;
        
    case 'DELETE':
        if ($accion === 'eliminar' && isset($_GET['id'])) {
            eliminarVenta($pdo, $_GET['id']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        break;
}
    
/**
 * Listar todas las ventas con paginación
 */
function listarVentas($pdo) {
    try {
        $limite = (int)($_GET['limite'] ?? 10);
        $pagina = (int)($_GET['pagina'] ?? 1);
        $offset = ($pagina - 1) * $limite;
        $busqueda = $_GET['busqueda'] ?? '';
        
        // Construir consulta base
        $whereClause = '';
        $params = [];
        
        if (!empty($busqueda)) {
            $whereClause = "WHERE cliente LIKE ? OR producto LIKE ?";
            $params = ["%$busqueda%", "%$busqueda%"];
        }
        
        // Contar total de registros
        $stmtCount = $pdo->prepare("SELECT COUNT(*) as total FROM ventas $whereClause");
        $stmtCount->execute($params);
        $totalRegistros = $stmtCount->fetch()['total'];
        
        // Obtener ventas con paginación
        $sql = "
            SELECT id, fecha_venta, cliente, producto, cantidad, 
                   precio_unitario, total,
                   DATE_FORMAT(fecha_venta, '%d/%m/%Y %H:%i') as fecha_formateada
            FROM ventas 
            $whereClause
            ORDER BY fecha_venta DESC 
            LIMIT ? OFFSET ?
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_merge($params, [$limite, $offset]));
        $ventas = $stmt->fetchAll();
        
        // Formatear números
        foreach ($ventas as &$venta) {
            $venta['precio_unitario'] = (float)$venta['precio_unitario'];
            $venta['total'] = (float)$venta['total'];
            $venta['cantidad'] = (int)$venta['cantidad'];
        }
        
        echo json_encode([
            'success' => true,
            'datos' => $ventas,
            'paginacion' => [
                'total' => $totalRegistros,
                'pagina_actual' => $pagina,
                'limite' => $limite,
                'total_paginas' => ceil($totalRegistros / $limite)
            ]
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al listar ventas: ' . $e->getMessage()]);
    }
}
    
    public static function obtenerVenta($id) {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                SELECT v.*, c.nombre as cliente_nombre, u.nombre as vendedor_nombre
                FROM fs_ventas v 
                LEFT JOIN fs_clientes c ON v.cliente_id = c.id 
                LEFT JOIN fs_usuarios u ON v.id_usuario = u.id
                WHERE v.id = ?
            ");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$row) {
                return ['error' => 'Venta no encontrada'];
            }
            
            $venta = [
                'id' => (int)$row['id'],
                'numero_venta' => $row['numero_venta'],
                'fecha' => $row['fecha_venta'],
                'cliente' => $row['cliente_nombre'] ?? $row['cliente_nombre'] ?? 'Cliente General',
                'cliente_id' => $row['cliente_id'],
                'total' => (float)$row['total'],
                'subtotal' => (float)$row['subtotal'],
                'estado' => $row['estado'] ?? 'pendiente',
                'metodo' => $row['metodo_pago'] ?? 'efectivo',
                'vendedor' => $row['vendedor_nombre'] ?? 'Sistema',
                'vendedor_id' => $row['id_usuario'],
                'descuento' => (float)($row['descuento'] ?? 0),
                'observaciones' => $row['observaciones'] ?? '',
                'productos' => $row['productos'] ?? '[]',
                'items' => json_decode($row['productos'] ?? '[]', true) ?: []
            ];
            
            return ['success' => true, 'data' => $venta];
        } catch (Exception $e) {
            Logger::error('Error al obtener venta: ' . $e->getMessage());
            return ['error' => 'Error al obtener la venta'];
        }
    }
    
    public static function crearVenta() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $db = Database::getConnection();
            
            $db->beginTransaction();
            
            // Insertar venta
            $numeroVenta = 'V-' . date('YmdHis') . '-' . rand(100, 999);
            $productos_json = json_encode($input['items'] ?? []);
            $subtotal = ($input['total'] ?? 0) + ($input['descuento'] ?? 0);
            
            $stmt = $db->prepare("
                INSERT INTO fs_ventas (numero_venta, cliente_id, cliente_nombre, fecha_venta, total, subtotal, estado, metodo_pago, id_usuario, descuento, observaciones, productos) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $numeroVenta,
                $input['cliente_id'] ?? null,
                $input['cliente_nombre'] ?? 'Cliente General',
                $input['fecha'] ?? date('Y-m-d H:i:s'),
                $input['total'] ?? 0,
                $subtotal,
                $input['estado'] ?? 'pendiente',
                $input['metodo'] ?? 'efectivo',
                $input['vendedor_id'] ?? null,
                $input['descuento'] ?? 0,
                $input['observaciones'] ?? '',
                $productos_json
            ]);
            
            $ventaId = $db->lastInsertId();
            
            // Los items se guardan en JSON en el campo productos
            
            $db->commit();
            return ['success' => true, 'id' => $ventaId, 'message' => 'Venta creada exitosamente'];
            
        } catch (Exception $e) {
            $db->rollback();
            Logger::error('Error al crear venta: ' . $e->getMessage());
            return ['error' => 'Error al crear la venta'];
        }
    }
    
    public static function actualizarVenta($data) {
        try {
            $db = Database::getConnection();
            
            $stmt = $db->prepare("
                UPDATE fs_ventas SET 
                    cliente_id = ?, cliente_nombre = ?, fecha_venta = ?, estado = ?, metodo_pago = ?, 
                    descuento = ?, observaciones = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $data['cliente_id'],
                $data['cliente_nombre'] ?? 'Cliente General',
                $data['fecha'],
                $data['estado'],
                $data['metodo'],
                $data['descuento'] ?? 0,
                $data['observaciones'] ?? '',
                $data['id']
            ]);
            
            return ['success' => true, 'message' => 'Venta actualizada exitosamente'];
            
        } catch (Exception $e) {
            Logger::error('Error al actualizar venta: ' . $e->getMessage());
            return ['error' => 'Error al actualizar la venta'];
        }
    }
    
    public static function cambiarEstado($id, $estado) {
        try {
            $db = Database::getConnection();
            
            $stmt = $db->prepare("UPDATE fs_ventas SET estado = ? WHERE id = ?");
            $stmt->execute([$estado, $id]);
            
            return ['success' => true, 'message' => "Estado cambiado a: $estado"];
            
        } catch (Exception $e) {
            Logger::error('Error al cambiar estado: ' . $e->getMessage());
            return ['error' => 'Error al cambiar el estado'];
        }
    }
    
    public static function duplicarVenta($id) {
        try {
            $db = Database::getConnection();
            $db->beginTransaction();
            
            // Obtener venta original
            $stmt = $db->prepare("SELECT * FROM fs_ventas WHERE id = ?");
            $stmt->execute([$id]);
            $venta = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$venta) {
                return ['error' => 'Venta no encontrada'];
            }
            
            // Crear nueva venta
            $numeroVenta = 'V-' . date('YmdHis') . '-' . rand(100, 999);
            $stmtInsert = $db->prepare("
                INSERT INTO fs_ventas (numero_venta, cliente_id, cliente_nombre, fecha_venta, total, subtotal, estado, metodo_pago, id_usuario, descuento, observaciones, productos) 
                VALUES (?, ?, ?, ?, ?, ?, 'pendiente', ?, ?, ?, ?, ?)
            ");
            
            $stmtInsert->execute([
                $numeroVenta,
                $venta['cliente_id'],
                $venta['cliente_nombre'],
                date('Y-m-d H:i:s'),
                $venta['total'],
                $venta['subtotal'],
                $venta['metodo_pago'],
                $venta['id_usuario'],
                $venta['descuento'],
                'Duplicado de venta #' . $venta['numero_venta'],
                $venta['productos']
            ]);
            
            $nuevaVentaId = $db->lastInsertId();
            
            $db->commit();
            return ['success' => true, 'id' => $nuevaVentaId, 'message' => 'Venta duplicada exitosamente'];
            
        } catch (Exception $e) {
            $db->rollback();
            Logger::error('Error al duplicar venta: ' . $e->getMessage());
            return ['error' => 'Error al duplicar la venta'];
        }
    }
    
    public static function cancelarVenta($id) {
        return self::cambiarEstado($id, 'cancelada');
    }
    
    public static function listarVentas($filtros = []) {
        try {
            $db = Database::getConnection();
            $where = ['1=1'];
            $params = [];
            
            if (!empty($filtros['fecha_inicio'])) {
                $where[] = 'DATE(v.fecha_venta) >= ?';
                $params[] = $filtros['fecha_inicio'];
            }
            
            if (!empty($filtros['fecha_fin'])) {
                $where[] = 'DATE(v.fecha_venta) <= ?';
                $params[] = $filtros['fecha_fin'];
            }
            
            if (!empty($filtros['estado'])) {
                $where[] = 'v.estado = ?';
                $params[] = $filtros['estado'];
            }
            
            if (!empty($filtros['cliente'])) {
                $where[] = 'c.nombre LIKE ?';
                $params[] = '%' . $filtros['cliente'] . '%';
            }
            
            $sql = "
                SELECT v.*, c.nombre as cliente_nombre, u.nombre as vendedor_nombre
                FROM fs_ventas v 
                LEFT JOIN fs_clientes c ON v.cliente_id = c.id 
                LEFT JOIN fs_usuarios u ON v.id_usuario = u.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY v.fecha_venta DESC
            ";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            $ventas = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $ventas[] = [
                    'id' => (int)$row['id'],
                    'numero_venta' => $row['numero_venta'],
                    'fecha' => $row['fecha_venta'],
                    'cliente' => $row['cliente_nombre'] ?? $row['cliente_nombre'] ?? 'Cliente General',
                    'cliente_id' => $row['cliente_id'],
                    'total' => (float)$row['total'],
                    'subtotal' => (float)$row['subtotal'],
                    'estado' => $row['estado'] ?? 'pendiente',
                    'metodo' => $row['metodo_pago'] ?? 'efectivo',
                    'vendedor' => $row['vendedor_nombre'] ?? 'Sistema',
                    'vendedor_id' => $row['id_usuario'],
                    'descuento' => (float)($row['descuento'] ?? 0),
                    'observaciones' => $row['observaciones'] ?? '',
                    'items' => json_decode($row['productos'] ?? '[]', true) ?: []
                ];
            }
            
            return ['success' => true, 'data' => $ventas];
            
        } catch (Exception $e) {
            Logger::error('Error al listar ventas: ' . $e->getMessage());
            return ['error' => 'Error al obtener las ventas'];
        }
    }
    
    private static function obtenerItemsVenta($ventaId) {
        // Los items ahora se obtienen del campo productos (JSON) en fs_ventas
        // Esta función se mantiene por compatibilidad pero no se usa
        return [];
    }
    
    public static function obtenerDetalleVenta($id) {
        $venta = self::obtenerVenta($id);
        if (isset($venta['error'])) {
            return $venta;
        }
        
        return $venta;
    }
}

