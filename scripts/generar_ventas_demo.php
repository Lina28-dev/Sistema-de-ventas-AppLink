<?php
/**
 * Script para simular ventas en tiempo real
 * Este archivo se puede ejecutar para generar ventas de prueba
 */

require_once __DIR__ . '/../config/app.php';

$config = include __DIR__ . '/../config/app.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset={$config['db']['charset']}", 
        $config['db']['user'], 
        $config['db']['pass']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Crear tabla de ventas si no existe
    $createVentasTable = "
    CREATE TABLE IF NOT EXISTS fs_ventas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cliente_id INT,
        total DECIMAL(10,2) NOT NULL,
        descuento DECIMAL(5,2) DEFAULT 0,
        metodo_pago VARCHAR(50) DEFAULT 'efectivo',
        estado VARCHAR(20) DEFAULT 'completada',
        productos JSON,
        fecha_venta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        usuario_id INT,
        INDEX idx_fecha_venta (fecha_venta),
        INDEX idx_estado (estado)
    )";
    $pdo->exec($createVentasTable);
    
    // Productos de ejemplo
    $productos = [
        ['nombre' => 'Labial Rosa', 'precio' => 25000],
        ['nombre' => 'Base de Maquillaje', 'precio' => 45000],
        ['nombre' => 'Sombras Palette', 'precio' => 35000],
        ['nombre' => 'Rímel Negro', 'precio' => 20000],
        ['nombre' => 'Rubor Coral', 'precio' => 18000],
        ['nombre' => 'Delineador Negro', 'precio' => 15000],
        ['nombre' => 'Gloss Transparente', 'precio' => 12000],
        ['nombre' => 'Polvo Compacto', 'precio' => 30000]
    ];
    
    // Métodos de pago
    $metodosPago = ['efectivo', 'tarjeta', 'transferencia', 'nequi'];
    
    // Generar entre 1 y 3 ventas
    $numVentas = rand(1, 3);
    
    for ($i = 0; $i < $numVentas; $i++) {
        // Seleccionar productos aleatorios
        $numProductos = rand(1, 3);
        $productosVenta = [];
        $total = 0;
        
        for ($j = 0; $j < $numProductos; $j++) {
            $producto = $productos[array_rand($productos)];
            $cantidad = rand(1, 2);
            $subtotal = $producto['precio'] * $cantidad;
            $total += $subtotal;
            
            $productosVenta[] = [
                'nombre' => $producto['nombre'],
                'precio' => $producto['precio'],
                'cantidad' => $cantidad,
                'subtotal' => $subtotal
            ];
        }
        
        // Aplicar descuento aleatorio (0-15%)
        $descuento = rand(0, 15);
        $totalConDescuento = $total * (1 - $descuento / 100);
        
        // Método de pago aleatorio
        $metodoPago = $metodosPago[array_rand($metodosPago)];
        
        // Fecha aleatoria en las últimas 2 horas
        $horasAtras = rand(0, 120); // minutos
        $fechaVenta = date('Y-m-d H:i:s', strtotime("-{$horasAtras} minutes"));
        
        // Insertar venta
        $stmt = $pdo->prepare("
            INSERT INTO fs_ventas (cliente_id, total, descuento, metodo_pago, estado, productos, fecha_venta, usuario_id) 
            VALUES (?, ?, ?, ?, 'completada', ?, ?, ?)
        ");
        
        $stmt->execute([
            rand(1, 10), // cliente_id aleatorio
            $totalConDescuento,
            $descuento,
            $metodoPago,
            json_encode($productosVenta),
            $fechaVenta,
            1 // usuario_id
        ]);
        
        echo "Venta generada: $" . number_format($totalConDescuento, 0) . " - " . $metodoPago . " - " . $fechaVenta . "\n";
    }
    
    echo "\n✅ Generadas {$numVentas} ventas de prueba exitosamente!\n";
    echo "Puedes ver las actualizaciones en el dashboard en tiempo real.\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>