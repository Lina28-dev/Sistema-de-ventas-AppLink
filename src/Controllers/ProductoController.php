<?php
/**
 * Controlador de Productos para Sistema de Ventas
 * Maneja todas las operaciones CRUD de productos
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

// Crear tabla si no existe
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS fs_productos (
            id_producto INT PRIMARY KEY AUTO_INCREMENT,
            codigo VARCHAR(50) UNIQUE,
            descripcion VARCHAR(255) NOT NULL,
            nombre VARCHAR(255),
            color VARCHAR(100),
            talle VARCHAR(20),
            estampa VARCHAR(255),
            cantidad INT DEFAULT 0,
            precio DECIMAL(10,2) DEFAULT 0,
            precio_costo DECIMAL(10,2) DEFAULT 0,
            categoria VARCHAR(100) DEFAULT 'General',
            marca VARCHAR(100),
            estado ENUM('activo', 'inactivo') DEFAULT 'activo',
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    
    // Verificar si hay productos, si no crear datos demo
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM fs_productos");
    $count = $stmt->fetch()['total'];
    
    if ($count == 0) {
        crearProductosDemo($pdo);
    }
    
} catch (PDOException $e) {
    // La tabla puede ya existir, continuar
}

// Determinar método HTTP y acción
$metodo = $_SERVER['REQUEST_METHOD'];
$accion = $_GET['accion'] ?? '';

switch ($metodo) {
    case 'GET':
        if ($accion === 'buscar') {
            buscarProductos($pdo);
        } elseif ($accion === 'obtener' && isset($_GET['id'])) {
            obtenerProducto($pdo, $_GET['id']);
        } elseif ($accion === 'categorias') {
            obtenerCategorias($pdo);
        } elseif ($accion === 'listar') {
            listarProductos($pdo);
        } else {
            listarProductos($pdo);
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if ($accion === 'crear') {
            crearProducto($pdo, $data);
        }
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        if ($accion === 'actualizar' && isset($_GET['id'])) {
            actualizarProducto($pdo, $_GET['id'], $data);
        }
        break;
        
    case 'DELETE':
        if ($accion === 'eliminar' && isset($_GET['id'])) {
            eliminarProducto($pdo, $_GET['id']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        break;
}

/**
 * Buscar productos por término de búsqueda
 */
function buscarProductos($pdo) {
    try {
        $termino = $_GET['termino'] ?? '';
        $limite = $_GET['limite'] ?? 20;
        
        if (empty($termino)) {
            echo json_encode([
                'success' => true,
                'data' => [],
                'message' => 'Término de búsqueda vacío'
            ]);
            return;
        }
        
        $sql = "
            SELECT 
                id_producto,
                codigo,
                nombre,
                descripcion,
                color,
                talle,
                stock_actual as cantidad,
                precio,
                precio_costo,
                marca,
                activo
            FROM fs_productos 
            WHERE activo = 1 
            AND (
                descripcion LIKE ? OR 
                nombre LIKE ? OR 
                codigo LIKE ? OR 
                color LIKE ? OR 
                marca LIKE ?
            )
            ORDER BY nombre ASC, descripcion ASC
            LIMIT ?
        ";
        
        $stmt = $pdo->prepare($sql);
        $terminoBusqueda = "%{$termino}%";
        $stmt->execute([
            $terminoBusqueda, $terminoBusqueda, $terminoBusqueda,
            $terminoBusqueda, $terminoBusqueda,
            (int)$limite
        ]);
        
        $productos = $stmt->fetchAll();
        
        // Formatear productos para la respuesta
        $productosFormateados = array_map(function($producto) {
            return [
                'id' => $producto['id_producto'],
                'codigo' => $producto['codigo'] ?? 'PROD' . str_pad($producto['id_producto'], 4, '0', STR_PAD_LEFT),
                'nombre' => $producto['nombre'] ?: $producto['descripcion'],
                'descripcion' => $producto['descripcion'],
                'color' => $producto['color'],
                'talle' => $producto['talle'],
                'stock' => (int)$producto['cantidad'],
                'precio' => (float)$producto['precio'],
                'precio_costo' => (float)$producto['precio_costo'],
                'marca' => $producto['marca'],
                'disponible' => $producto['cantidad'] > 0
            ];
        }, $productos);
        
        echo json_encode([
            'success' => true,
            'data' => $productosFormateados,
            'total' => count($productosFormateados),
            'termino' => $termino
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al buscar productos: ' . $e->getMessage()
        ]);
    }
}

/**
 * Listar todos los productos con paginación
 */
function listarProductos($pdo) {
    try {
        $pagina = $_GET['pagina'] ?? 1;
        $limite = $_GET['limite'] ?? 50;
        $categoria = $_GET['categoria'] ?? '';
        
        $offset = ($pagina - 1) * $limite;
        
        // Construir consulta base
        $whereClause = "WHERE p.activo = 1";
        $params = [];
        
        // Filtro por categoría
        $categoria_id = $_GET['categoria_id'] ?? '';
        if (!empty($categoria_id)) {
            $whereClause .= " AND p.categoria_id = ?";
            $params[] = $categoria_id;
        }
        
        // Para disponibles solo
        $disponibles = $_GET['disponibles'] ?? '';
        if (!empty($disponibles)) {
            $whereClause .= " AND p.stock_actual > 0";
        }
        
        // Filtro de búsqueda
        $busqueda = $_GET['busqueda'] ?? '';
        if (!empty($busqueda)) {
            $whereClause .= " AND (p.codigo LIKE ? OR p.nombre LIKE ? OR p.descripcion LIKE ? OR c.nombre LIKE ?)";
            $searchTerm = "%$busqueda%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Contar total
        $sqlCount = "SELECT COUNT(*) as total FROM fs_productos p 
                     LEFT JOIN categorias_productos c ON p.categoria_id = c.id 
                     {$whereClause}";
        $stmtCount = $pdo->prepare($sqlCount);
        $stmtCount->execute($params);
        $total = $stmtCount->fetch()['total'];
        
        // Obtener productos con categorías
        $sql = "
            SELECT 
                p.id_producto,
                p.codigo,
                p.nombre,
                p.descripcion,
                p.color,
                p.talle,
                p.stock_actual as cantidad,
                p.precio,
                p.precio_costo,
                p.marca,
                p.activo,
                p.categoria_id,
                p.created_at as fecha_creacion,
                c.nombre as categoria_nombre,
                c.color as categoria_color,
                c.icono as categoria_icono
            FROM fs_productos p
            LEFT JOIN categorias_productos c ON p.categoria_id = c.id
            {$whereClause}
            ORDER BY p.created_at DESC
            LIMIT ? OFFSET ?
        ";
        
        $params[] = (int)$limite;
        $params[] = (int)$offset;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $productos = $stmt->fetchAll();
        
        // Formatear productos con información de categoría
        $productosFormateados = array_map(function($producto) {
            return [
                'id' => $producto['id_producto'],
                'codigo' => $producto['codigo'] ?? 'PROD' . str_pad($producto['id_producto'], 4, '0', STR_PAD_LEFT),
                'nombre' => $producto['nombre'] ?: $producto['descripcion'],
                'descripcion' => $producto['descripcion'],
                'color' => $producto['color'],
                'talle' => $producto['talle'],
                'stock' => (int)$producto['cantidad'],
                'precio' => (float)$producto['precio'],
                'precio_costo' => (float)$producto['precio_costo'],
                'marca' => $producto['marca'],
                'disponible' => $producto['cantidad'] > 0,
                'fecha_creacion' => $producto['fecha_creacion'],
                'categoria_id' => $producto['categoria_id'],
                'categoria_nombre' => $producto['categoria_nombre'],
                'categoria_color' => $producto['categoria_color'],
                'categoria_icono' => $producto['categoria_icono']
            ];
        }, $productos);
        
        echo json_encode([
            'success' => true,
            'data' => $productosFormateados,
            'pagination' => [
                'total' => (int)$total,
                'pagina' => (int)$pagina,
                'limite' => (int)$limite,
                'total_paginas' => ceil($total / $limite)
            ]
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al listar productos: ' . $e->getMessage()
        ]);
    }
}

/**
 * Obtener un producto específico
 */
function obtenerProducto($pdo, $id) {
    try {
        $sql = "
            SELECT 
                id_producto,
                codigo,
                COALESCE(nombre, descripcion) as nombre,
                descripcion,
                color,
                talle,
                estampa,
                cantidad,
                precio,
                precio_costo,
                categoria,
                marca,
                estado
            FROM fs_productos 
            WHERE id_producto = ?
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $producto = $stmt->fetch();
        
        if (!$producto) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Producto no encontrado'
            ]);
            return;
        }
        
        $productoFormateado = [
            'id' => $producto['id_producto'],
            'codigo' => $producto['codigo'] ?? 'PROD' . str_pad($producto['id_producto'], 4, '0', STR_PAD_LEFT),
            'nombre' => $producto['nombre'] ?: $producto['descripcion'],
            'descripcion' => $producto['descripcion'],
            'color' => $producto['color'],
            'talle' => $producto['talle'],
            'estampa' => $producto['estampa'],
            'stock' => (int)$producto['cantidad'],
            'precio' => (float)$producto['precio'],
            'precio_costo' => (float)$producto['precio_costo'],
            'categoria' => $producto['categoria'],
            'marca' => $producto['marca'],
            'disponible' => $producto['cantidad'] > 0
        ];
        
        echo json_encode([
            'success' => true,
            'data' => $productoFormateado
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al obtener producto: ' . $e->getMessage()
        ]);
    }
}

/**
 * Crear productos de demostración
 */
function crearProductosDemo($pdo) {
    $productosDemo = [
        [
            'codigo' => 'BRA001',
            'nombre' => 'Brasier Push Up Encaje',
            'descripcion' => 'Brasier push up con encaje delicado, realza y da forma natural',
            'color' => 'Negro',
            'talle' => '34B',
            'categoria' => 'Lencería',
            'marca' => 'Lilipink',
            'cantidad' => 15,
            'precio' => 65990,
            'precio_costo' => 35000
        ],
        [
            'codigo' => 'BRA002', 
            'nombre' => 'Brasier Deportivo',
            'descripcion' => 'Brasier deportivo con soporte medio, ideal para ejercicio',
            'color' => 'Rosa',
            'talle' => '32A',
            'categoria' => 'Deportivo',
            'marca' => 'Lilipink',
            'cantidad' => 12,
            'precio' => 45990,
            'precio_costo' => 25000
        ],
        [
            'codigo' => 'BRA003',
            'nombre' => 'Brasier Sin Copas',
            'descripcion' => 'Brasier sin copas, cómodo y natural para uso diario',
            'color' => 'Blanco',
            'talle' => '36C',
            'categoria' => 'Lencería',
            'marca' => 'Lilipink',
            'cantidad' => 8,
            'precio' => 39990,
            'precio_costo' => 22000
        ],
        [
            'codigo' => 'PAN001',
            'nombre' => 'Panty Invisible Clásico',
            'descripcion' => 'Panty invisible sin costuras, perfecto bajo cualquier prenda',
            'color' => 'Nude',
            'talle' => 'M',
            'categoria' => 'Lencería',
            'marca' => 'Lilipink',
            'cantidad' => 25,
            'precio' => 24990,
            'precio_costo' => 12000
        ],
        [
            'codigo' => 'PIJ001',
            'nombre' => 'Pijama Short Algodón',
            'descripcion' => 'Pijama de short en algodón suave, ideal para el descanso',
            'color' => 'Azul',
            'talle' => 'L',
            'categoria' => 'Pijamas',
            'marca' => 'Lilipink',
            'cantidad' => 10,
            'precio' => 79990,
            'precio_costo' => 45000
        ],
        [
            'codigo' => 'CAM001',
            'nombre' => 'Camiseta Manga Corta',
            'descripcion' => 'Camiseta básica de manga corta en algodón',
            'color' => 'Blanco',
            'talle' => 'M',
            'categoria' => 'Casual',
            'marca' => 'Lilipink',
            'cantidad' => 18,
            'precio' => 29990,
            'precio_costo' => 15000
        ]
    ];
    
    try {
        foreach ($productosDemo as $producto) {
            $stmt = $pdo->prepare("
                INSERT INTO fs_productos 
                (codigo, nombre, descripcion, color, talle, categoria, marca, cantidad, precio, precio_costo, estado) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'activo')
            ");
            
            $stmt->execute([
                $producto['codigo'],
                $producto['nombre'],
                $producto['descripcion'],
                $producto['color'],
                $producto['talle'],
                $producto['categoria'],
                $producto['marca'],
                $producto['cantidad'],
                $producto['precio'],
                $producto['precio_costo']
            ]);
        }
        
        error_log("Productos demo creados exitosamente");
    } catch (PDOException $e) {
        error_log("Error al crear productos demo: " . $e->getMessage());
    }
}

/**
 * Crear productos de demostración (función anterior preservada)
 */
function crearProductosDemoAnterior($pdo) {
    $productosDemo = [
        [
            'codigo' => 'BRA001',
            'nombre' => 'Brasier Push-Up Encaje',
            'descripcion' => 'Brasier con realce y copas moldeadas, encaje delicado',
            'color' => 'Negro',
            'talle' => '85B',
            'estampa' => 'Encaje floral',
            'cantidad' => 25,
            'precio' => 59990,
            'precio_costo' => 35000,
            'categoria' => 'Lencería',
            'marca' => 'Lilipink'
        ],
        [
            'codigo' => 'PAN001',
            'nombre' => 'Panty Invisible Clásico',
            'descripcion' => 'Panty sin costuras, invisible bajo la ropa',
            'color' => 'Nude',
            'talle' => 'M',
            'estampa' => 'Liso',
            'cantidad' => 50,
            'precio' => 24990,
            'precio_costo' => 15000,
            'categoria' => 'Lencería',
            'marca' => 'Lilipink'
        ],
        [
            'codigo' => 'PIJ001',
            'nombre' => 'Pijama Short Algodón',
            'descripción' => 'Pijama de dos piezas, short y blusa',
            'color' => 'Rosa',
            'talle' => 'L',
            'estampa' => 'Corazones',
            'cantidad' => 20,
            'precio' => 79990,
            'precio_costo' => 50000,
            'categoria' => 'Pijamas',
            'marca' => 'Lilipink'
        ],
        [
            'codigo' => 'CAM001',
            'nombre' => 'Camiseta Manga Corta',
            'descripcion' => 'Camiseta básica 100% algodón',
            'color' => 'Blanco',
            'talle' => 'S',
            'estampa' => 'Liso',
            'cantidad' => 30,
            'precio' => 29990,
            'precio_costo' => 18000,
            'categoria' => 'Casual',
            'marca' => 'Lilipink'
        ],
        [
            'codigo' => 'BOX001',
            'nombre' => 'Bóxer Algodón',
            'descripcion' => 'Bóxer masculino 100% algodón',
            'color' => 'Azul',
            'talle' => 'M',
            'estampa' => 'Rayas',
            'cantidad' => 40,
            'precio' => 19990,
            'precio_costo' => 12000,
            'categoria' => 'Masculino',
            'marca' => 'Lilipink'
        ],
        [
            'codigo' => 'VES001',
            'nombre' => 'Vestido Casual',
            'descripcion' => 'Vestido midi casual para el día',
            'color' => 'Verde',
            'talle' => 'M',
            'estampa' => 'Flores',
            'cantidad' => 15,
            'precio' => 89990,
            'precio_costo' => 55000,
            'categoria' => 'Vestidos',
            'marca' => 'Lilipink'
        ],
        [
            'codigo' => 'MED001',
            'nombre' => 'Medias Algodón Pack x3',
            'descripcion' => 'Pack de 3 pares de medias de algodón',
            'color' => 'Variado',
            'talle' => 'Único',
            'estampa' => 'Liso',
            'cantidad' => 60,
            'precio' => 14990,
            'precio_costo' => 9000,
            'categoria' => 'Accesorios',
            'marca' => 'Lilipink'
        ],
        [
            'codigo' => 'BLU001',
            'nombre' => 'Blusa Manga Larga',
            'descripcion' => 'Blusa elegante manga larga',
            'color' => 'Beige',
            'talle' => 'L',
            'estampa' => 'Liso',
            'cantidad' => 18,
            'precio' => 49990,
            'precio_costo' => 30000,
            'categoria' => 'Blusas',
            'marca' => 'Lilipink'
        ]
    ];
    
    $sql = "
        INSERT INTO fs_productos (
            codigo, nombre, descripcion, color, talle, estampa, 
            cantidad, precio, precio_costo, categoria, marca, estado
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'activo')
    ";
    
    $stmt = $pdo->prepare($sql);
    
    foreach ($productosDemo as $producto) {
        $stmt->execute([
            $producto['codigo'],
            $producto['nombre'],
            $producto['descripcion'],
            $producto['color'],
            $producto['talle'],
            $producto['estampa'],
            $producto['cantidad'],
            $producto['precio'],
            $producto['precio_costo'],
            $producto['categoria'],
            $producto['marca']
        ]);
    }
}

/**
 * Obtener todas las categorías de productos
 */
function obtenerCategorias($pdo) {
    try {
        $sql = "
            SELECT 
                id,
                nombre,
                descripcion,
                color,
                icono,
                activo,
                orden
            FROM categorias_productos 
            WHERE activo = 1
            ORDER BY orden ASC, nombre ASC
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $categorias = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'data' => $categorias
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al obtener categorías: ' . $e->getMessage()
        ]);
    }
}
?>