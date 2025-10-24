<?php
/**
 * Script para crear datos demo con estructura existente
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
    
    echo "Conectado a la base de datos exitosamente.\n";
    
    // Limpiar productos existentes
    echo "Limpiando productos existentes...\n";
    $pdo->exec("DELETE FROM fs_productos WHERE codigo IN ('BRA001', 'BRA002', 'BRA003', 'PAN001', 'PIJ001', 'CAM001')");
    
    // Crear productos demo
    echo "Creando productos de demostración...\n";
    crearProductosDemo($pdo);
    
    // Agregar campos nombres y apellidos a clientes si no existen
    echo "Actualizando tabla de clientes...\n";
    try {
        $pdo->exec("ALTER TABLE fs_clientes ADD COLUMN nombres VARCHAR(255) AFTER id");
        echo "Campo 'nombres' agregado.\n";
    } catch (Exception $e) {
        echo "Campo 'nombres' ya existe o error: " . $e->getMessage() . "\n";
    }
    
    try {
        $pdo->exec("ALTER TABLE fs_clientes ADD COLUMN apellidos VARCHAR(255) AFTER nombres");
        echo "Campo 'apellidos' agregado.\n";
    } catch (Exception $e) {
        echo "Campo 'apellidos' ya existe o error: " . $e->getMessage() . "\n";
    }
    
    // Limpiar clientes existentes de demo
    echo "Limpiando clientes demo existentes...\n";
    $pdo->exec("DELETE FROM fs_clientes WHERE CC IN ('1234567890', '0987654321', '1122334455', '5566778899', '9988776655')");
    
    // Crear clientes demo
    echo "Creando clientes de demostración...\n";
    crearClientesDemo($pdo);
    
    echo "\n¡Datos creados exitosamente!\n";
    echo "Productos creados: 6 (incluyendo 3 brasiers)\n";
    echo "Clientes demo creados: 5 (con nombres y apellidos)\n";
    
    // Mostrar productos creados
    echo "\n=== PRODUCTOS CREADOS ===\n";
    $stmt = $pdo->query("SELECT codigo, nombre, descripcion, precio FROM fs_productos WHERE codigo LIKE 'BRA%' OR codigo LIKE 'PAN%' OR codigo LIKE 'PIJ%' OR codigo LIKE 'CAM%'");
    $productos = $stmt->fetchAll();
    foreach ($productos as $producto) {
        echo "- {$producto['codigo']}: {$producto['nombre']} - \${$producto['precio']}\n";
    }
    
    // Mostrar clientes creados
    echo "\n=== CLIENTES CREADOS ===\n";
    $stmt = $pdo->query("SELECT nombres, apellidos, nombre_completo, CC FROM fs_clientes WHERE CC IN ('1234567890', '0987654321', '1122334455', '5566778899', '9988776655')");
    $clientes = $stmt->fetchAll();
    foreach ($clientes as $cliente) {
        $nombres = $cliente['nombres'] ?: 'N/A';
        $apellidos = $cliente['apellidos'] ?: 'N/A';
        echo "- {$nombres} {$apellidos} ({$cliente['nombre_completo']}) - CC: {$cliente['CC']}\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

function crearProductosDemo($pdo) {
    $productosDemo = [
        [
            'codigo' => 'BRA001',
            'nombre' => 'Brasier Push Up Encaje',
            'descripcion' => 'Brasier push up con encaje delicado, realza y da forma natural',
            'color' => 'Negro',
            'talle' => '34B',
            'marca' => 'Lilipink',
            'stock_actual' => 15,
            'precio' => 65990,
            'precio_costo' => 35000
        ],
        [
            'codigo' => 'BRA002', 
            'nombre' => 'Brasier Deportivo',
            'descripcion' => 'Brasier deportivo con soporte medio, ideal para ejercicio',
            'color' => 'Rosa',
            'talle' => '32A',
            'marca' => 'Lilipink',
            'stock_actual' => 12,
            'precio' => 45990,
            'precio_costo' => 25000
        ],
        [
            'codigo' => 'BRA003',
            'nombre' => 'Brasier Sin Copas',
            'descripcion' => 'Brasier sin copas, cómodo y natural para uso diario',
            'color' => 'Blanco',
            'talle' => '36C',
            'marca' => 'Lilipink',
            'stock_actual' => 8,
            'precio' => 39990,
            'precio_costo' => 22000
        ],
        [
            'codigo' => 'PAN001',
            'nombre' => 'Panty Invisible Clásico',
            'descripcion' => 'Panty invisible sin costuras, perfecto bajo cualquier prenda',
            'color' => 'Nude',
            'talle' => 'M',
            'marca' => 'Lilipink',
            'stock_actual' => 25,
            'precio' => 24990,
            'precio_costo' => 12000
        ],
        [
            'codigo' => 'PIJ001',
            'nombre' => 'Pijama Short Algodón',
            'descripcion' => 'Pijama de short en algodón suave, ideal para el descanso',
            'color' => 'Azul',
            'talle' => 'L',
            'marca' => 'Lilipink',
            'stock_actual' => 10,
            'precio' => 79990,
            'precio_costo' => 45000
        ],
        [
            'codigo' => 'CAM001',
            'nombre' => 'Camiseta Manga Corta',
            'descripcion' => 'Camiseta básica de manga corta en algodón',
            'color' => 'Blanco',
            'talle' => 'M',
            'marca' => 'Lilipink',
            'stock_actual' => 18,
            'precio' => 29990,
            'precio_costo' => 15000
        ]
    ];
    
    foreach ($productosDemo as $producto) {
        $stmt = $pdo->prepare("
            INSERT INTO fs_productos 
            (codigo, nombre, descripcion, color, talle, marca, stock_actual, precio, precio_costo, activo) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");
        
        $stmt->execute([
            $producto['codigo'],
            $producto['nombre'],
            $producto['descripcion'],
            $producto['color'],
            $producto['talle'],
            $producto['marca'],
            $producto['stock_actual'],
            $producto['precio'],
            $producto['precio_costo']
        ]);
        
        echo "  - Producto creado: {$producto['nombre']}\n";
    }
}

function crearClientesDemo($pdo) {
    $clientesDemo = [
        [
            'nombres' => 'María Elena',
            'apellidos' => 'González Rodríguez',
            'CC' => '1234567890',
            'telefono' => '3001234567',
            'email' => 'maria.gonzalez@email.com',
            'ciudad' => 'Bogotá',
            'descuento' => 5
        ],
        [
            'nombres' => 'Ana Sofía',
            'apellidos' => 'Martínez López',
            'CC' => '0987654321',
            'telefono' => '3109876543',
            'email' => 'ana.martinez@email.com',
            'ciudad' => 'Medellín',
            'descuento' => 10
        ],
        [
            'nombres' => 'Carolina',
            'apellidos' => 'Pérez García',
            'CC' => '1122334455',
            'telefono' => '3201122334',
            'email' => 'carolina.perez@email.com',
            'ciudad' => 'Cali',
            'descuento' => 0
        ],
        [
            'nombres' => 'Daniela',
            'apellidos' => 'Ramírez Silva',
            'CC' => '5566778899',
            'telefono' => '3155566778',
            'email' => 'daniela.ramirez@email.com',
            'ciudad' => 'Barranquilla',
            'descuento' => 15
        ],
        [
            'nombres' => 'Lucía Isabel',
            'apellidos' => 'Torres Moreno',
            'CC' => '9988776655',
            'telefono' => '3179988776',
            'email' => 'lucia.torres@email.com',
            'ciudad' => 'Cartagena',
            'descuento' => 8
        ]
    ];
    
    foreach ($clientesDemo as $cliente) {
        $stmt = $pdo->prepare("
            INSERT INTO fs_clientes 
            (nombres, apellidos, nombre_completo, CC, telefono, email, ciudad, descuento) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $cliente['nombres'],
            $cliente['apellidos'],
            $cliente['nombres'] . ' ' . $cliente['apellidos'],
            $cliente['CC'],
            $cliente['telefono'],
            $cliente['email'],
            $cliente['ciudad'],
            $cliente['descuento']
        ]);
        
        echo "  - Cliente creado: {$cliente['nombres']} {$cliente['apellidos']}\n";
    }
}
?>