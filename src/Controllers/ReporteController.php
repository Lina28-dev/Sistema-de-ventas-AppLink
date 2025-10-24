<?php
/**
 * Controlador de Reportes y Analytics
 * Proporciona datos para gráficos del dashboard
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
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

// Obtener tipo de reporte
$tipo = $_GET['tipo'] ?? 'dashboard';

switch ($tipo) {
    case 'dashboard':
        obtenerMetricasDashboard($pdo);
        break;
    case 'ventas-diarias':
        obtenerVentasDiarias($pdo);
        break;
    case 'productos-top':
        obtenerProductosTop($pdo);
        break;
    case 'ventas-mensuales':
        obtenerVentasMensuales($pdo);
        break;
    case 'clientes-top':
        obtenerClientesTop($pdo);
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Tipo de reporte no válido']);
        break;
}

/**
 * Obtener métricas principales del dashboard
 */
function obtenerMetricasDashboard($pdo) {
    try {
        // Crear tabla de ventas si no existe (datos demo)
        crearDatosDemo($pdo);
        
        $hoy = date('Y-m-d');
        $mesActual = date('Y-m');
        
        // Ventas de hoy
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(total), 0) as ventas_hoy,
                   COUNT(*) as transacciones_hoy
            FROM ventas 
            WHERE DATE(fecha_venta) = ?
        ");
        $stmt->execute([$hoy]);
        $ventasHoy = $stmt->fetch();
        
        // Ventas del mes
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(total), 0) as ventas_mes,
                   COUNT(*) as transacciones_mes
            FROM ventas 
            WHERE DATE_FORMAT(fecha_venta, '%Y-%m') = ?
        ");
        $stmt->execute([$mesActual]);
        $ventasMes = $stmt->fetch();
        
        // Ticket promedio
        $stmt = $pdo->query("
            SELECT COALESCE(AVG(total), 0) as ticket_promedio
            FROM ventas 
            WHERE fecha_venta >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $ticketPromedio = $stmt->fetch();
        
        echo json_encode([
            'success' => true,
            'datos' => [
                'ventas_hoy' => round($ventasHoy['ventas_hoy'], 2),
                'ventas_mes' => round($ventasMes['ventas_mes'], 2),
                'transacciones' => $ventasHoy['transacciones_hoy'] + $ventasMes['transacciones_mes'],
                'ticket_promedio' => round($ticketPromedio['ticket_promedio'], 2)
            ]
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener métricas: ' . $e->getMessage()]);
    }
}

/**
 * Obtener ventas de los últimos 7 días
 */
function obtenerVentasDiarias($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT DATE(fecha_venta) as fecha,
                   COALESCE(SUM(total), 0) as total_ventas,
                   COUNT(*) as num_transacciones
            FROM ventas 
            WHERE fecha_venta >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY DATE(fecha_venta)
            ORDER BY fecha ASC
        ");
        
        $resultados = $stmt->fetchAll();
        
        // Completar días faltantes con 0
        $fechas = [];
        $ventas = [];
        $transacciones = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $fecha = date('Y-m-d', strtotime("-$i days"));
            $fechas[] = date('d/m', strtotime($fecha));
            
            $encontrado = false;
            foreach ($resultados as $resultado) {
                if ($resultado['fecha'] == $fecha) {
                    $ventas[] = (float)$resultado['total_ventas'];
                    $transacciones[] = (int)$resultado['num_transacciones'];
                    $encontrado = true;
                    break;
                }
            }
            
            if (!$encontrado) {
                $ventas[] = 0;
                $transacciones[] = 0;
            }
        }
        
        echo json_encode([
            'success' => true,
            'datos' => [
                'fechas' => $fechas,
                'ventas' => $ventas,
                'transacciones' => $transacciones
            ]
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener ventas diarias: ' . $e->getMessage()]);
    }
}

/**
 * Obtener productos más vendidos
 */
function obtenerProductosTop($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT producto,
                   SUM(cantidad) as total_vendido,
                   SUM(total) as ingresos_totales
            FROM ventas 
            WHERE fecha_venta >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY producto
            ORDER BY total_vendido DESC
            LIMIT 5
        ");
        
        $resultados = $stmt->fetchAll();
        
        $productos = [];
        $cantidades = [];
        $ingresos = [];
        
        foreach ($resultados as $resultado) {
            $productos[] = $resultado['producto'];
            $cantidades[] = (int)$resultado['total_vendido'];
            $ingresos[] = (float)$resultado['ingresos_totales'];
        }
        
        echo json_encode([
            'success' => true,
            'datos' => [
                'productos' => $productos,
                'cantidades' => $cantidades,
                'ingresos' => $ingresos
            ]
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener productos top: ' . $e->getMessage()]);
    }
}

/**
 * Obtener ventas de los últimos 6 meses
 */
function obtenerVentasMensuales($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT DATE_FORMAT(fecha_venta, '%Y-%m') as mes,
                   COALESCE(SUM(total), 0) as total_ventas
            FROM ventas 
            WHERE fecha_venta >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(fecha_venta, '%Y-%m')
            ORDER BY mes ASC
        ");
        
        $resultados = $stmt->fetchAll();
        
        $meses = [];
        $ventas = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $mes = date('Y-m', strtotime("-$i months"));
            $mesNombre = date('M Y', strtotime("-$i months"));
            $meses[] = $mesNombre;
            
            $encontrado = false;
            foreach ($resultados as $resultado) {
                if ($resultado['mes'] == $mes) {
                    $ventas[] = (float)$resultado['total_ventas'];
                    $encontrado = true;
                    break;
                }
            }
            
            if (!$encontrado) {
                $ventas[] = 0;
            }
        }
        
        echo json_encode([
            'success' => true,
            'datos' => [
                'meses' => $meses,
                'ventas' => $ventas
            ]
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener ventas mensuales: ' . $e->getMessage()]);
    }
}

/**
 * Obtener clientes con más compras
 */
function obtenerClientesTop($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT cliente,
                   COUNT(*) as num_compras,
                   SUM(total) as total_gastado
            FROM ventas 
            WHERE fecha_venta >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY cliente
            ORDER BY total_gastado DESC
            LIMIT 5
        ");
        
        $resultados = $stmt->fetchAll();
        
        $clientes = [];
        $compras = [];
        $gastos = [];
        
        foreach ($resultados as $resultado) {
            $clientes[] = $resultado['cliente'];
            $compras[] = (int)$resultado['num_compras'];
            $gastos[] = (float)$resultado['total_gastado'];
        }
        
        echo json_encode([
            'success' => true,
            'datos' => [
                'clientes' => $clientes,
                'compras' => $compras,
                'gastos' => $gastos
            ]
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener clientes top: ' . $e->getMessage()]);
    }
}

/**
 * Crear datos demo si la tabla está vacía
 */
function crearDatosDemo($pdo) {
    try {
        // Verificar si existe la tabla ventas
        $stmt = $pdo->query("SHOW TABLES LIKE 'ventas'");
        if (!$stmt->fetch()) {
            // Crear tabla de ventas
            $pdo->exec("
                CREATE TABLE ventas (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    fecha_venta DATETIME DEFAULT CURRENT_TIMESTAMP,
                    cliente VARCHAR(100),
                    producto VARCHAR(100),
                    cantidad INT,
                    precio_unitario DECIMAL(10,2),
                    total DECIMAL(10,2)
                )
            ");
        }
        
        // Verificar si hay datos
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM ventas");
        $count = $stmt->fetch()['total'];
        
        if ($count == 0) {
            // Insertar datos demo
            $datosDemo = [
                ['María García', 'Panty Invisible Clásico', 2, 24990, 49980],
                ['Carlos López', 'Brasier Push Up Encaje', 1, 59990, 59990],
                ['Ana Martínez', 'Pijama Short Algodón', 1, 79990, 79990],
                ['Luis Rodríguez', 'Camiseta Manga Corta', 3, 29990, 89970],
                ['Elena Pérez', 'Bóxer Algodón', 2, 19990, 39980],
                ['Diego Silva', 'Medias Tobilleras', 5, 9990, 49950],
                ['Carmen Torres', 'Panty Invisible Clásico', 1, 24990, 24990],
                ['Roberto Díaz', 'Brasier Push Up Encaje', 2, 59990, 119980],
                ['Patricia Ruiz', 'Pijama Short Algodón', 1, 79990, 79990],
                ['Miguel Castro', 'Camiseta Manga Corta', 1, 29990, 29990]
            ];
            
            $stmt = $pdo->prepare("
                INSERT INTO ventas (cliente, producto, cantidad, precio_unitario, total, fecha_venta) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            foreach ($datosDemo as $index => $venta) {
                $fecha = date('Y-m-d H:i:s', strtotime("-" . rand(0, 30) . " days -" . rand(0, 23) . " hours"));
                $stmt->execute([
                    $venta[0], $venta[1], $venta[2], $venta[3], $venta[4], $fecha
                ]);
            }
        }
        
    } catch (Exception $e) {
        // Si hay error, continuar sin datos demo
        error_log("Error creando datos demo: " . $e->getMessage());
    }
}
?>