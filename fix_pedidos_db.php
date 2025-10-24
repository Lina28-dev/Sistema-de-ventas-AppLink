<?php
// Script para verificar y corregir la estructura de fs_pedidos
try {
    $host = 'localhost';
    $dbname = 'fs_clientes';
    $username = 'root';
    $password = '';
    
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verificar estructura de fs_pedidos
    echo "Estructura actual de fs_pedidos:\n";
    try {
        $stmt = $db->query("DESCRIBE fs_pedidos");
        $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columnas as $columna) {
            echo "- {$columna['Field']} ({$columna['Type']}) {$columna['Null']} {$columna['Key']}\n";
        }
    } catch (Exception $e) {
        echo "Error al verificar fs_pedidos: " . $e->getMessage() . "\n";
    }
    
    // Verificar si existe la columna cliente_nombre
    $columnas_existentes = [];
    try {
        $stmt = $db->query("SHOW COLUMNS FROM fs_pedidos");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $columnas_existentes[] = $row['Field'];
        }
        
        echo "\nColumnas existentes: " . implode(', ', $columnas_existentes) . "\n";
        
        // Agregar cliente_nombre si no existe
        if (!in_array('cliente_nombre', $columnas_existentes)) {
            echo "\nAgregando columna cliente_nombre...\n";
            $db->exec("ALTER TABLE fs_pedidos ADD COLUMN cliente_nombre VARCHAR(255) NULL AFTER cliente_id");
            echo "Columna cliente_nombre agregada exitosamente.\n";
        } else {
            echo "\nLa columna cliente_nombre ya existe.\n";
        }
        
    } catch (Exception $e) {
        echo "Error al verificar/agregar columnas: " . $e->getMessage() . "\n";
    }
    
    echo "\nConfiguracion completada.\n";
    
} catch (Exception $e) {
    echo "Error de conexión: " . $e->getMessage() . "\n";
}
?>