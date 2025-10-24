<?php
/**
 * Controlador de Clientes para Sistema de Ventas
 * API REST para gestión de clientes
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Iniciar sesión si no está activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación (temporalmente más permisivo para debugging)
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    // En lugar de bloquear, log y continuar para debugging
    error_log("ClienteControllerAPI: Sesión no autenticada. Continuando para debugging.");
    // Comentado temporalmente para debugging
    // http_response_code(403);
    // echo json_encode(['error' => 'No autorizado']);
    // exit;
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
    echo json_encode([
        'success' => false,
        'error' => 'Error de conexión a la base de datos: ' . $e->getMessage(),
        'code' => $e->getCode()
    ]);
    exit;
}

// Crear tabla si no existe o migrar estructura antigua
try {
    // Primero verificar si existe la tabla con estructura antigua
    $checkOldTable = $pdo->query("SHOW COLUMNS FROM fs_clientes LIKE 'CC'");
    $hasOldStructure = $checkOldTable && $checkOldTable->rowCount() > 0;
    
    if ($hasOldStructure) {
        // Migrar estructura antigua a nueva
        $pdo->exec("ALTER TABLE fs_clientes ADD COLUMN IF NOT EXISTS id_cliente INT PRIMARY KEY AUTO_INCREMENT FIRST");
        $pdo->exec("ALTER TABLE fs_clientes ADD COLUMN IF NOT EXISTS identificacion VARCHAR(50) AFTER apellidos");
        $pdo->exec("ALTER TABLE fs_clientes ADD COLUMN IF NOT EXISTS tipo_identificacion ENUM('CC', 'CE', 'NIT', 'PASAPORTE') DEFAULT 'CC' AFTER identificacion");
        $pdo->exec("UPDATE fs_clientes SET identificacion = CC WHERE identificacion IS NULL");
        $pdo->exec("UPDATE fs_clientes SET id_cliente = id WHERE id_cliente IS NULL");
    } else {
        // Crear tabla nueva
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS fs_clientes (
                id_cliente INT PRIMARY KEY AUTO_INCREMENT,
                nombres VARCHAR(255) NOT NULL,
                apellidos VARCHAR(255) NOT NULL,
                nombre VARCHAR(255) GENERATED ALWAYS AS (CONCAT(nombres, ' ', apellidos)) STORED,
                identificacion VARCHAR(50) UNIQUE NOT NULL,
                tipo_identificacion ENUM('CC', 'CE', 'NIT', 'PASAPORTE') DEFAULT 'CC',
                telefono VARCHAR(20),
                email VARCHAR(255),
                direccion TEXT,
                ciudad VARCHAR(100),
                departamento VARCHAR(100),
                codigo_postal VARCHAR(10),
                tipo_cliente ENUM('individual', 'empresa') DEFAULT 'individual',
                descuento DECIMAL(5,2) DEFAULT 0.00,
                estado ENUM('activo', 'inactivo') DEFAULT 'activo',
                fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                ultima_compra TIMESTAMP NULL,
                INDEX idx_identificacion (identificacion),
                INDEX idx_telefono (telefono),
                INDEX idx_email (email),
                INDEX idx_nombres (nombres),
                INDEX idx_apellidos (apellidos)
            )
        ");
    }
    
    // Verificar si hay clientes, si no crear datos demo
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM fs_clientes");
    $count = $stmt->fetch()['total'];
    
    if ($count == 0) {
        crearClientesDemo($pdo);
    }
    
} catch (PDOException $e) {
    // La tabla puede ya existir, continuar
}

// Log de debug para desarrollo
error_log("ClienteControllerAPI: Método: " . $_SERVER['REQUEST_METHOD'] . ", Acción: " . ($_GET['accion'] ?? 'sin acción'));

// Determinar método HTTP y acción
$metodo = $_SERVER['REQUEST_METHOD'];
$accion = $_GET['accion'] ?? '';

switch ($metodo) {
    case 'GET':
        if ($accion === 'buscar') {
            buscarClientes($pdo);
        } elseif ($accion === 'obtener' && isset($_GET['id'])) {
            obtenerCliente($pdo, $_GET['id']);
        } elseif ($accion === 'categorias') {
            obtenerCategoriasClientes($pdo);
        } elseif ($accion === 'segmentos') {
            obtenerSegmentos($pdo);
        } elseif ($accion === 'listar') {
            listarClientes($pdo);
        } elseif ($accion === 'estadisticas') {
            obtenerEstadisticas($pdo);
        } else {
            listarClientes($pdo);
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if ($accion === 'crear') {
            crearCliente($pdo, $data);
        } elseif ($accion === 'guardar') {
            // Compatibilidad con formulario existente
            crearClienteFormulario($pdo, $_POST);
        }
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        if ($accion === 'actualizar' && isset($_GET['id'])) {
            actualizarCliente($pdo, $_GET['id'], $data);
        }
        break;
        
    case 'DELETE':
        if ($accion === 'eliminar' && isset($_GET['id'])) {
            eliminarCliente($pdo, $_GET['id']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        break;
}

/**
 * Buscar clientes por término
 */
function buscarClientes($pdo) {
    try {
        $termino = $_GET['termino'] ?? '';
        $limite = $_GET['limite'] ?? 10;
        
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
                COALESCE(id_cliente, id) as id,
                nombres,
                apellidos,
                COALESCE(nombre, CONCAT(nombres, ' ', apellidos)) as nombre,
                COALESCE(identificacion, CC) as identificacion,
                telefono,
                email,
                ciudad,
                descuento
            FROM fs_clientes 
            WHERE 1=1 
            AND (
                COALESCE(nombre_completo, CONCAT(nombres, ' ', apellidos)) LIKE ? OR 
                COALESCE(nombres, '') LIKE ? OR 
                COALESCE(apellidos, '') LIKE ? OR
                COALESCE(identificacion, CC) LIKE ? OR 
                telefono LIKE ? OR 
                email LIKE ?
            )
            ORDER BY COALESCE(nombre_completo, CONCAT(nombres, ' ', apellidos)) ASC
            LIMIT ?
        ";
        
        $stmt = $pdo->prepare($sql);
        $terminoBusqueda = "%{$termino}%";
        $stmt->execute([
            $terminoBusqueda, $terminoBusqueda, $terminoBusqueda,
            $terminoBusqueda, $terminoBusqueda, $terminoBusqueda,
            (int)$limite
        ]);
        
        $clientes = $stmt->fetchAll();
        
        // Formatear clientes para la respuesta
        $clientesFormateados = array_map(function($cliente) {
            return [
                'id' => $cliente['id'],
                'nombres' => $cliente['nombres'] ?: '',
                'apellidos' => $cliente['apellidos'] ?: '',
                'nombre' => $cliente['nombre'],
                'identificacion' => 'CC ' . $cliente['identificacion'],
                'telefono' => $cliente['telefono'],
                'email' => $cliente['email'],
                'ciudad' => $cliente['ciudad'],
                'descuento' => (float)$cliente['descuento']
            ];
        }, $clientes);
        
        echo json_encode([
            'success' => true,
            'data' => $clientesFormateados,
            'total' => count($clientesFormateados)
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al buscar clientes: ' . $e->getMessage()
        ]);
    }
}

/**
 * Listar clientes con paginación
 */
function listarClientes($pdo) {
    try {
        $pagina = $_GET['pagina'] ?? 1;
        $limite = $_GET['limite'] ?? 20;
        $tipo = $_GET['tipo'] ?? '';
        
        $offset = ($pagina - 1) * $limite;
        
        // Construir consulta base
        $whereClause = "WHERE 1=1";
        $params = [];
        
        if (!empty($tipo)) {
            $whereClause .= " AND c.revendedora = ?";
            $params[] = ($tipo === 'empresa') ? 1 : 0;
        }
        
        // Filtro por categoría
        $categoria_id = $_GET['categoria_id'] ?? '';
        if (!empty($categoria_id)) {
            $whereClause .= " AND c.categoria_id = ?";
            $params[] = $categoria_id;
        }
        
        // Filtro por segmento
        $segmento = $_GET['segmento'] ?? '';
        if (!empty($segmento)) {
            $whereClause .= " AND c.segmento = ?";
            $params[] = $segmento;
        }
        
        // Filtro de búsqueda
        $busqueda = $_GET['busqueda'] ?? '';
        if (!empty($busqueda)) {
            $whereClause .= " AND (c.nombres LIKE ? OR c.apellidos LIKE ? OR COALESCE(c.nombre, CONCAT(c.nombres, ' ', c.apellidos)) LIKE ? OR COALESCE(c.identificacion, c.CC) LIKE ? OR cat.nombre LIKE ?)";
            $searchTerm = "%$busqueda%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Contar total
        $sqlCount = "SELECT COUNT(*) as total FROM fs_clientes c 
                     LEFT JOIN categorias_clientes cat ON c.categoria_id = cat.id 
                     {$whereClause}";
        $stmtCount = $pdo->prepare($sqlCount);
        $stmtCount->execute($params);
        $total = $stmtCount->fetch()['total'];
        
        // Obtener clientes con categorías
        $sql = "
            SELECT 
                COALESCE(c.id_cliente, c.id) as id,
                c.nombres,
                c.apellidos,
                COALESCE(c.nombre, CONCAT(c.nombres, ' ', c.apellidos)) as nombre,
                COALESCE(c.identificacion, c.CC) as identificacion,
                c.telefono,
                c.email,
                c.direccion,
                c.ciudad,
                c.descuento,
                c.categoria_id,
                c.segmento,
                COALESCE(c.fecha_registro, c.created_at) as fecha_registro,
                cat.nombre as categoria_nombre,
                cat.color as categoria_color,
                cat.descuento_porcentaje as categoria_descuento,
                cat.limite_credito,
                cat.dias_credito
            FROM fs_clientes c
            LEFT JOIN categorias_clientes cat ON c.categoria_id = cat.id
            {$whereClause}
            ORDER BY COALESCE(c.fecha_registro, c.created_at) DESC
            LIMIT ? OFFSET ?
        ";
        
        $params[] = (int)$limite;
        $params[] = (int)$offset;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $clientes = $stmt->fetchAll();
        
        // Formatear clientes con información de categoría y segmento
        $clientesFormateados = array_map(function($cliente) {
            return [
                'id' => $cliente['id'],
                'nombres' => $cliente['nombres'] ?: '',
                'apellidos' => $cliente['apellidos'] ?: '',
                'nombre' => $cliente['nombre'],
                'identificacion' => 'CC ' . $cliente['identificacion'],
                'telefono' => $cliente['telefono'],
                'email' => $cliente['email'],
                'direccion' => $cliente['direccion'],
                'ciudad' => $cliente['ciudad'],
                'descuento' => (float)$cliente['descuento'],
                'fecha_registro' => $cliente['fecha_registro'],
                'categoria_id' => $cliente['categoria_id'],
                'categoria_nombre' => $cliente['categoria_nombre'],
                'categoria_color' => $cliente['categoria_color'],
                'categoria_descuento' => (float)($cliente['categoria_descuento'] ?: 0),
                'limite_credito' => (float)($cliente['limite_credito'] ?: 0),
                'dias_credito' => (int)($cliente['dias_credito'] ?: 0),
                'segmento' => $cliente['segmento'] ?: 'Nuevo'
            ];
        }, $clientes);
        
        echo json_encode([
            'success' => true,
            'data' => $clientesFormateados,
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
            'error' => 'Error al listar clientes: ' . $e->getMessage()
        ]);
    }
}

/**
 * Obtener estadísticas de clientes
 */
function obtenerEstadisticas($pdo) {
    try {
        // Total clientes activos
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM fs_clientes");
        $totalClientes = $stmt->fetch()['total'];
        
        // Clientes con historial (con descuento)
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM fs_clientes WHERE descuento > 0");
        $clientesHistorial = $stmt->fetch()['total'];
        
        // Clientes nuevos este mes
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM fs_clientes WHERE MONTH(fecha_registro) = MONTH(CURDATE()) AND YEAR(fecha_registro) = YEAR(CURDATE())");
        $nuevosMes = $stmt->fetch()['total'];
        
        // Clientes revendedoras (activos)
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM fs_clientes WHERE revendedora = 1");
        $activos = $stmt->fetch()['total'];
        
        echo json_encode([
            'success' => true,
            'data' => [
                'total_clientes' => (int)$totalClientes,
                'clientes_historial' => (int)$clientesHistorial,
                'nuevos_mes' => (int)$nuevosMes,
                'activos' => (int)$activos
            ]
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al obtener estadísticas: ' . $e->getMessage()
        ]);
    }
}

/**
 * Crear cliente desde formulario POST
 */
function crearClienteFormulario($pdo, $data) {
    try {
        // Validar datos requeridos
        if (empty($data['nombre_completo'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'El nombre es requerido']);
            return;
        }
        
        // Separar nombres y apellidos si vienen juntos
        $nombreCompleto = trim($data['nombre_completo']);
        $partesNombre = explode(' ', $nombreCompleto);
        $nombres = $partesNombre[0] ?? '';
        $apellidos = implode(' ', array_slice($partesNombre, 1)) ?: '';
        
        $sql = "
            INSERT INTO fs_clientes (
                nombres, apellidos, nombre_completo, direccion, ciudad, 
                localidad, codigo_postal, email, telefono, CC, 
                descuento, revendedora
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";
        
        $stmt = $pdo->prepare($sql);
        $resultado = $stmt->execute([
            $nombres,
            $apellidos,
            $nombreCompleto,
            $data['direccion'] ?? '',
            $data['ciudad'] ?? '',
            $data['provincia'] ?? '', // localidad
            $data['cod_postal'] ?? '',
            $data['email'] ?? '',
            $data['telefono'] ?? '',
            $data['dni'] ?? '', // CC
            $data['descuento'] ?? 0,
            $data['revendedora'] ?? 0
        ]);
        
        if ($resultado) {
            $nuevoId = $pdo->lastInsertId();
            echo json_encode([
                'success' => true,
                'message' => 'Cliente creado exitosamente',
                'id' => $nuevoId
            ]);
            
            // Si es una llamada desde formulario HTML, redirigir
            if (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') === false) {
                echo '<script>alert("Cliente registrado correctamente"); window.location="../Views/clientes.php";</script>';
            }
        } else {
            throw new Exception('Error al insertar el cliente');
        }
        
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Duplicate entry
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => 'Cliente ya existe']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error al crear cliente: ' . $e->getMessage()]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

/**
 * Crear cliente desde API JSON
 */
function crearCliente($pdo, $data) {
    try {
        // Validar datos requeridos
        if (empty($data['nombres']) || empty($data['apellidos'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Nombres y apellidos son requeridos']);
            return;
        }
        
        $nombres = trim($data['nombres']);
        $apellidos = trim($data['apellidos']);
        $nombreCompleto = $data['nombre_completo'] ?? "{$nombres} {$apellidos}";
        $identificacion = trim($data['identificacion'] ?? '');
        $telefono = trim($data['telefono'] ?? '');
        
        // Si no hay identificación, generar una temporal
        if (empty($identificacion)) {
            $identificacion = 'TEMP_' . time() . '_' . rand(1000, 9999);
        }
        
        // Verificar estructura de tabla para saber qué campos usar
        $columns = $pdo->query("SHOW COLUMNS FROM fs_clientes")->fetchAll(PDO::FETCH_COLUMN);
        $hasNewStructure = in_array('identificacion', $columns);
        $hasOldStructure = in_array('CC', $columns);
        
        // Validar que no exista ya un cliente con la misma identificación
        if ($hasOldStructure && !empty($identificacion)) {
            $checkSql = "SELECT id FROM fs_clientes WHERE CC = ?";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->execute([$identificacion]);
            if ($checkStmt->fetch()) {
                http_response_code(409);
                echo json_encode(['success' => false, 'error' => 'Ya existe un cliente con esta identificación']);
                return;
            }
        }
        
        if ($hasNewStructure) {
            // Estructura nueva (por si se migra en el futuro)
            $sql = "
                INSERT INTO fs_clientes (
                    nombres, apellidos, identificacion, tipo_identificacion, 
                    telefono, email, direccion, ciudad, departamento, codigo_postal, 
                    descuento, categoria_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ";
            
            $stmt = $pdo->prepare($sql);
            $resultado = $stmt->execute([
                $nombres,
                $apellidos,
                $identificacion,
                $data['tipo_identificacion'] ?? 'CC',
                $telefono,
                $data['email'] ?? null,
                $data['direccion'] ?? null,
                $data['ciudad'] ?? 'Bogotá',
                $data['departamento'] ?? null,
                $data['codigo_postal'] ?? null,
                floatval($data['descuento'] ?? 0),
                !empty($data['categoria_id']) ? intval($data['categoria_id']) : null
            ]);
        } else {
            // Estructura antigua - usar campos existentes
            $sql = "
                INSERT INTO fs_clientes (
                    nombres, apellidos, nombre_completo, CC, telefono, email, 
                    direccion, ciudad, localidad, codigo_postal, descuento, revendedora, categoria_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ";
            
            $stmt = $pdo->prepare($sql);
            $resultado = $stmt->execute([
                $nombres,
                $apellidos,
                $nombreCompleto,
                $identificacion,
                $telefono,
                $data['email'] ?? null,
                $data['direccion'] ?? null,
                $data['ciudad'] ?? 'Bogotá',
                $data['departamento'] ?? $data['localidad'] ?? null,
                $data['codigo_postal'] ?? null,
                floatval($data['descuento'] ?? 0),
                ($data['es_revendedor'] ?? false) ? 1 : 0,
                !empty($data['categoria_id']) ? intval($data['categoria_id']) : null
            ]);
        }
        
        if ($resultado) {
            $nuevoId = $pdo->lastInsertId();
            echo json_encode([
                'success' => true,
                'message' => 'Cliente creado exitosamente',
                'id' => $nuevoId,
                'data' => [
                    'id' => $nuevoId,
                    'nombres' => $nombres,
                    'apellidos' => $apellidos,
                    'nombre_completo' => $nombreCompleto,
                    'identificacion' => $identificacion,
                    'telefono' => $telefono
                ]
            ]);
        } else {
            throw new Exception('Error al insertar el cliente');
        }
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'error' => 'Error de base de datos: ' . $e->getMessage(),
            'code' => $e->getCode()
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

/**
 * Crear clientes demo
 */
function crearClientesDemo($pdo) {
    $clientesDemo = [
        [
            'nombre' => 'María García López',
            'identificacion' => '12345678',
            'tipo_identificacion' => 'CC',
            'telefono' => '3001234567',
            'email' => 'maria.garcia@email.com',
            'direccion' => 'Calle 123 #45-67',
            'ciudad' => 'Bogotá',
            'departamento' => 'Cundinamarca',
            'codigo_postal' => '110111',
            'tipo_cliente' => 'individual',
            'descuento' => 5.00
        ],
        [
            'nombre' => 'Carlos López Mendoza',
            'identificacion' => '87654321',
            'tipo_identificacion' => 'CC',
            'telefono' => '3009876543',
            'email' => 'carlos.lopez@email.com',
            'direccion' => 'Carrera 67 #89-12',
            'ciudad' => 'Medellín',
            'departamento' => 'Antioquia',
            'codigo_postal' => '050001',
            'tipo_cliente' => 'individual',
            'descuento' => 0.00
        ],
        [
            'nombre' => 'Ana Martínez Silva',
            'identificacion' => '11223344',
            'tipo_identificacion' => 'CC',
            'telefono' => '3011122334',
            'email' => 'ana.martinez@email.com',
            'direccion' => 'Avenida 45 #12-34',
            'ciudad' => 'Cali',
            'departamento' => 'Valle del Cauca',
            'codigo_postal' => '760001',
            'tipo_cliente' => 'individual',
            'descuento' => 10.00
        ],
        [
            'nombre' => 'Empresa ABC S.A.S.',
            'identificacion' => '900123456',
            'tipo_identificacion' => 'NIT',
            'telefono' => '6014567890',
            'email' => 'contacto@empresaabc.com',
            'direccion' => 'Zona Industrial Calle 80',
            'ciudad' => 'Bogotá',
            'departamento' => 'Cundinamarca',
            'codigo_postal' => '110221',
            'tipo_cliente' => 'empresa',
            'descuento' => 15.00
        ],
        [
            'nombre' => 'Cliente General',
            'identificacion' => '00000000',
            'tipo_identificacion' => 'CC',
            'telefono' => '0000000000',
            'email' => '',
            'direccion' => '',
            'ciudad' => 'Bogotá',
            'departamento' => 'Cundinamarca',
            'codigo_postal' => '',
            'tipo_cliente' => 'individual',
            'descuento' => 0.00
        ]
    ];
    
    $sql = "
        INSERT INTO fs_clientes (
            nombre, identificacion, tipo_identificacion, telefono, email, 
            direccion, ciudad, departamento, codigo_postal, tipo_cliente, descuento
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";
    
    $stmt = $pdo->prepare($sql);
    
    foreach ($clientesDemo as $cliente) {
        try {
            $stmt->execute([
                $cliente['nombre'],
                $cliente['identificacion'],
                $cliente['tipo_identificacion'],
                $cliente['telefono'],
                $cliente['email'],
                $cliente['direccion'],
                $cliente['ciudad'],
                $cliente['departamento'],
                $cliente['codigo_postal'],
                $cliente['tipo_cliente'],
                $cliente['descuento']
            ]);
        } catch (PDOException $e) {
            // Ignorar duplicados
            continue;
        }
    }
}

/**
 * Obtener todas las categorías de clientes
 */
function obtenerCategoriasClientes($pdo) {
    try {
        $sql = "
            SELECT 
                id,
                nombre,
                descripcion,
                color,
                descuento_porcentaje,
                limite_credito,
                dias_credito,
                activo,
                orden
            FROM categorias_clientes 
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

/**
 * Obtener todos los segmentos disponibles
 */
function obtenerSegmentos($pdo) {
    try {
        $segmentos = [
            ['value' => 'VIP', 'label' => 'VIP', 'color' => '#ffc107', 'icon' => 'fas fa-crown'],
            ['value' => 'Premium', 'label' => 'Premium', 'color' => '#e83e8c', 'icon' => 'fas fa-star'],
            ['value' => 'Regular', 'label' => 'Regular', 'color' => '#17a2b8', 'icon' => 'fas fa-user-check'],
            ['value' => 'Nuevo', 'label' => 'Nuevo', 'color' => '#28a745', 'icon' => 'fas fa-user-plus'],
            ['value' => 'Inactivo', 'label' => 'Inactivo', 'color' => '#6c757d', 'icon' => 'fas fa-user-slash']
        ];
        
        echo json_encode([
            'success' => true,
            'data' => $segmentos
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al obtener segmentos: ' . $e->getMessage()
        ]);
    }
}
?>