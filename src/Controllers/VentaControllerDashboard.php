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

/**
 * Obtener una venta específica por ID
 */
function obtenerVenta($pdo, $id) {
    try {
        $stmt = $pdo->prepare("
            SELECT id, fecha_venta, cliente, producto, cantidad, 
                   precio_unitario, total,
                   DATE_FORMAT(fecha_venta, '%Y-%m-%d') as fecha_solo,
                   DATE_FORMAT(fecha_venta, '%H:%i') as hora_solo
            FROM ventas 
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        $venta = $stmt->fetch();
        
        if (!$venta) {
            http_response_code(404);
            echo json_encode(['error' => 'Venta no encontrada']);
            return;
        }
        
        // Formatear números
        $venta['precio_unitario'] = (float)$venta['precio_unitario'];
        $venta['total'] = (float)$venta['total'];
        $venta['cantidad'] = (int)$venta['cantidad'];
        
        echo json_encode([
            'success' => true,
            'datos' => $venta
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener venta: ' . $e->getMessage()]);
    }
}

/**
 * Crear una nueva venta
 */
function crearVenta($pdo, $data) {
    try {
        // Validar datos requeridos
        $requeridos = ['cliente', 'producto', 'cantidad', 'precio_unitario'];
        foreach ($requeridos as $campo) {
            if (empty($data[$campo])) {
                http_response_code(400);
                echo json_encode(['error' => "El campo '$campo' es requerido"]);
                return;
            }
        }
        
        // Validar tipos de datos
        if (!is_numeric($data['cantidad']) || $data['cantidad'] <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'La cantidad debe ser un número positivo']);
            return;
        }
        
        if (!is_numeric($data['precio_unitario']) || $data['precio_unitario'] <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'El precio unitario debe ser un número positivo']);
            return;
        }
        
        // Calcular total
        $cantidad = (int)$data['cantidad'];
        $precioUnitario = (float)$data['precio_unitario'];
        $total = $cantidad * $precioUnitario;
        
        // Establecer fecha (usar proporcionada o actual)
        $fechaVenta = !empty($data['fecha_venta']) ? $data['fecha_venta'] : date('Y-m-d H:i:s');
        
        // Insertar venta
        $stmt = $pdo->prepare("
            INSERT INTO ventas (fecha_venta, cliente, producto, cantidad, precio_unitario, total) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $fechaVenta,
            trim($data['cliente']),
            trim($data['producto']),
            $cantidad,
            $precioUnitario,
            $total
        ]);
        
        $ventaId = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'mensaje' => 'Venta creada exitosamente',
            'id' => $ventaId,
            'total' => $total
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al crear venta: ' . $e->getMessage()]);
    }
}

/**
 * Actualizar una venta existente
 */
function actualizarVenta($pdo, $id, $data) {
    try {
        // Verificar que la venta existe
        $stmt = $pdo->prepare("SELECT id FROM ventas WHERE id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(['error' => 'Venta no encontrada']);
            return;
        }
        
        // Validar datos requeridos
        $requeridos = ['cliente', 'producto', 'cantidad', 'precio_unitario'];
        foreach ($requeridos as $campo) {
            if (empty($data[$campo])) {
                http_response_code(400);
                echo json_encode(['error' => "El campo '$campo' es requerido"]);
                return;
            }
        }
        
        // Validar tipos de datos
        if (!is_numeric($data['cantidad']) || $data['cantidad'] <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'La cantidad debe ser un número positivo']);
            return;
        }
        
        if (!is_numeric($data['precio_unitario']) || $data['precio_unitario'] <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'El precio unitario debe ser un número positivo']);
            return;
        }
        
        // Calcular total
        $cantidad = (int)$data['cantidad'];
        $precioUnitario = (float)$data['precio_unitario'];
        $total = $cantidad * $precioUnitario;
        
        // Establecer fecha (usar proporcionada o mantener existente)
        $fechaVenta = !empty($data['fecha_venta']) ? $data['fecha_venta'] : null;
        
        // Construir consulta de actualización
        if ($fechaVenta) {
            $sql = "
                UPDATE ventas 
                SET fecha_venta = ?, cliente = ?, producto = ?, cantidad = ?, precio_unitario = ?, total = ?
                WHERE id = ?
            ";
            $params = [$fechaVenta, trim($data['cliente']), trim($data['producto']), $cantidad, $precioUnitario, $total, $id];
        } else {
            $sql = "
                UPDATE ventas 
                SET cliente = ?, producto = ?, cantidad = ?, precio_unitario = ?, total = ?
                WHERE id = ?
            ";
            $params = [trim($data['cliente']), trim($data['producto']), $cantidad, $precioUnitario, $total, $id];
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        echo json_encode([
            'success' => true,
            'mensaje' => 'Venta actualizada exitosamente',
            'total' => $total
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar venta: ' . $e->getMessage()]);
    }
}

/**
 * Eliminar una venta
 */
function eliminarVenta($pdo, $id) {
    try {
        // Verificar que la venta existe
        $stmt = $pdo->prepare("SELECT id FROM ventas WHERE id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(['error' => 'Venta no encontrada']);
            return;
        }
        
        // Eliminar venta
        $stmt = $pdo->prepare("DELETE FROM ventas WHERE id = ?");
        $stmt->execute([$id]);
        
        echo json_encode([
            'success' => true,
            'mensaje' => 'Venta eliminada exitosamente'
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al eliminar venta: ' . $e->getMessage()]);
    }
}
?>