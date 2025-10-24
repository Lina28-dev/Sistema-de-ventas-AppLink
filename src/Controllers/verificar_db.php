<?php
/**
 * Script para verificar y actualizar estructura de base de datos
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
    
    // Verificar estructura de tablas
    echo "\n=== VERIFICANDO ESTRUCTURA DE TABLAS ===\n";
    
    // Verificar tabla productos
    echo "\nTabla fs_productos:\n";
    try {
        $stmt = $pdo->query("DESCRIBE fs_productos");
        $columns = $stmt->fetchAll();
        foreach ($columns as $column) {
            echo "  - {$column['Field']} ({$column['Type']})\n";
        }
    } catch (Exception $e) {
        echo "  No existe o error: " . $e->getMessage() . "\n";
    }
    
    // Verificar tabla clientes
    echo "\nTabla fs_clientes:\n";
    try {
        $stmt = $pdo->query("DESCRIBE fs_clientes");
        $columns = $stmt->fetchAll();
        foreach ($columns as $column) {
            echo "  - {$column['Field']} ({$column['Type']})\n";
        }
    } catch (Exception $e) {
        echo "  No existe o error: " . $e->getMessage() . "\n";
    }
    
    // Contar registros
    echo "\n=== CONTANDO REGISTROS ===\n";
    
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM fs_productos");
        $count = $stmt->fetch()['total'];
        echo "Productos: $count\n";
        
        if ($count > 0) {
            $stmt = $pdo->query("SELECT codigo, nombre FROM fs_productos LIMIT 5");
            $productos = $stmt->fetchAll();
            foreach ($productos as $producto) {
                echo "  - {$producto['codigo']}: {$producto['nombre']}\n";
            }
        }
    } catch (Exception $e) {
        echo "Error contando productos: " . $e->getMessage() . "\n";
    }
    
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM fs_clientes");
        $count = $stmt->fetch()['total'];
        echo "Clientes: $count\n";
        
        if ($count > 0) {
            $stmt = $pdo->query("SELECT nombre FROM fs_clientes LIMIT 5");
            $clientes = $stmt->fetchAll();
            foreach ($clientes as $cliente) {
                echo "  - {$cliente['nombre']}\n";
            }
        }
    } catch (Exception $e) {
        echo "Error contando clientes: " . $e->getMessage() . "\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>