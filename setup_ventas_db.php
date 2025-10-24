<?php
// Conexión directa a la base de datos
try {
    $host = 'localhost';
    $dbname = 'fs_clientes';
    $username = 'root';
    $password = '';
    
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verificar tablas existentes
    $stmt = $db->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'fs_clientes'");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Tablas existentes:\n";
    foreach ($tablas as $tabla) {
        echo "- $tabla\n";
    }
    
    // Verificar estructura de fs_ventas
    echo "\nEstructura de fs_ventas:\n";
    $stmt = $db->query("DESCRIBE fs_ventas");
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columnas as $columna) {
        echo "- {$columna['Field']} ({$columna['Type']})\n";
    }
    
    // Verificar si existe tabla de items de venta
    $stmt = $db->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'fs_clientes' AND table_name LIKE '%venta%' OR table_name LIKE '%item%'");
    $tablasVenta = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "\nTablas relacionadas con ventas:\n";
    foreach ($tablasVenta as $tabla) {
        echo "- $tabla\n";
    }
    
    // Crear tabla venta_items si no existe
    $db->exec("
        CREATE TABLE IF NOT EXISTS venta_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            venta_id INT NOT NULL,
            producto_id INT NOT NULL,
            cantidad INT NOT NULL DEFAULT 1,
            precio_unitario DECIMAL(10,2) NOT NULL,
            subtotal DECIMAL(10,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (venta_id) REFERENCES fs_ventas(id) ON DELETE CASCADE,
            FOREIGN KEY (producto_id) REFERENCES fs_productos(id) ON DELETE CASCADE
        )
    ");
    
    // Insertar datos de prueba si no existen
    $stmt = $db->query("SELECT COUNT(*) FROM fs_ventas");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        echo "\nInsertando datos de prueba...\n";
        
        // Ventas de prueba
        $ventas_prueba = [
            [
                'cliente_id' => 1,
                'fecha' => '2024-10-20 10:30:00',
                'total' => 150.00,
                'estado' => 'completada',
                'metodo_pago' => 'efectivo',
                'observaciones' => 'Venta de prueba 1'
            ],
            [
                'cliente_id' => 2,
                'fecha' => '2024-10-21 14:15:00',
                'total' => 320.50,
                'estado' => 'pendiente',
                'metodo_pago' => 'tarjeta',
                'observaciones' => 'Venta de prueba 2'
            ],
            [
                'cliente_id' => null,
                'fecha' => '2024-10-22 09:45:00',
                'total' => 85.00,
                'estado' => 'borrador',
                'metodo_pago' => 'efectivo',
                'observaciones' => 'Cliente general'
            ]
        ];
        
        $stmt = $db->prepare("
            INSERT INTO fs_ventas (cliente_id, fecha, total, estado, metodo_pago, observaciones) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($ventas_prueba as $venta) {
            $stmt->execute([
                $venta['cliente_id'],
                $venta['fecha'],
                $venta['total'],
                $venta['estado'],
                $venta['metodo_pago'],
                $venta['observaciones']
            ]);
            
            $ventaId = $db->lastInsertId();
            
            // Agregar items de prueba
            $db->prepare("
                INSERT INTO venta_items (venta_id, producto_id, cantidad, precio_unitario, subtotal) 
                VALUES (?, 1, 2, 25.00, 50.00), (?, 2, 1, 100.00, 100.00)
            ")->execute([$ventaId, $ventaId]);
        }
        
        echo "Datos de prueba insertados exitosamente.\n";
    }
    
    echo "\nConfiguración de base de datos completada.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>