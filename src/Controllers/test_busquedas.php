<?php
/**
 * Test para verificar búsqueda de productos y clientes
 */

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
    
    echo "=== TEST DE BÚSQUEDA DE PRODUCTOS ===\n";
    
    // Test 1: Buscar "brasier"
    echo "\n1. Búsqueda por 'brasier':\n";
    $termino = "brasier";
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
        ORDER BY nombre ASC
        LIMIT 10
    ";
    
    $stmt = $pdo->prepare($sql);
    $terminoBusqueda = "%{$termino}%";
    $stmt->execute([
        $terminoBusqueda, $terminoBusqueda, $terminoBusqueda,
        $terminoBusqueda, $terminoBusqueda
    ]);
    
    $productos = $stmt->fetchAll();
    
    if (empty($productos)) {
        echo "  - No se encontraron productos con 'brasier'\n";
    } else {
        foreach ($productos as $producto) {
            echo "  - {$producto['codigo']}: {$producto['nombre']} - Stock: {$producto['cantidad']}\n";
        }
    }
    
    // Test 2: Buscar todos los productos disponibles
    echo "\n2. Productos disponibles (stock > 0):\n";
    $sql = "
        SELECT 
            codigo,
            nombre,
            stock_actual,
            precio
        FROM fs_productos 
        WHERE activo = 1 AND stock_actual > 0
        ORDER BY nombre ASC
    ";
    
    $stmt = $pdo->query($sql);
    $productos = $stmt->fetchAll();
    
    foreach ($productos as $producto) {
        echo "  - {$producto['codigo']}: {$producto['nombre']} - Stock: {$producto['stock_actual']} - \${$producto['precio']}\n";
    }
    
    echo "\n=== TEST DE BÚSQUEDA DE CLIENTES ===\n";
    
    // Test 3: Buscar clientes por nombres
    echo "\n3. Búsqueda por 'María':\n";
    $termino = "María";
    $sql = "
        SELECT 
            id,
            COALESCE(nombres, SUBSTRING_INDEX(nombre_completo, ' ', 1)) as nombres,
            COALESCE(apellidos, SUBSTRING_INDEX(nombre_completo, ' ', -1)) as apellidos,
            nombre_completo as nombre,
            CC as identificacion,
            telefono,
            email,
            ciudad,
            descuento
        FROM fs_clientes 
        WHERE 1=1 
        AND (
            nombre_completo LIKE ? OR 
            COALESCE(nombres, '') LIKE ? OR 
            COALESCE(apellidos, '') LIKE ? OR
            CC LIKE ? OR 
            telefono LIKE ? OR 
            email LIKE ?
        )
        ORDER BY nombre_completo ASC
        LIMIT 10
    ";
    
    $stmt = $pdo->prepare($sql);
    $terminoBusqueda = "%{$termino}%";
    $stmt->execute([
        $terminoBusqueda, $terminoBusqueda, $terminoBusqueda,
        $terminoBusqueda, $terminoBusqueda, $terminoBusqueda
    ]);
    
    $clientes = $stmt->fetchAll();
    
    if (empty($clientes)) {
        echo "  - No se encontraron clientes con 'María'\n";
    } else {
        foreach ($clientes as $cliente) {
            echo "  - {$cliente['nombres']} {$cliente['apellidos']} - CC: {$cliente['identificacion']} - Desc: {$cliente['descuento']}%\n";
        }
    }
    
    // Test 4: Todos los clientes
    echo "\n4. Todos los clientes:\n";
    $sql = "SELECT nombres, apellidos, nombre_completo, CC, descuento FROM fs_clientes ORDER BY nombre_completo";
    $stmt = $pdo->query($sql);
    $clientes = $stmt->fetchAll();
    
    foreach ($clientes as $cliente) {
        $nombres = $cliente['nombres'] ?: 'N/A';
        $apellidos = $cliente['apellidos'] ?: 'N/A';
        echo "  - {$nombres} {$apellidos} ({$cliente['nombre_completo']}) - CC: {$cliente['CC']} - Desc: {$cliente['descuento']}%\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>