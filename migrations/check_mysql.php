<?php
// Verificar estado de MySQL
echo "Verificando conexión MySQL...\n";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=fs_clientes', 'root', '');
    echo "✅ MySQL conectado exitosamente\n";
    
    echo "\nTablas existentes:\n";
    $stmt = $pdo->query('SHOW TABLES');
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tablas)) {
        echo "⚠️ No hay tablas en la base de datos fs_clientes\n";
        echo "🔧 Esto es normal si es una instalación nueva\n";
    } else {
        foreach ($tablas as $tabla) {
            echo "📊 Tabla: $tabla\n";
            
            // Verificar estructura de la tabla
            $stmt2 = $pdo->query("DESCRIBE $tabla");
            $columnas = $stmt2->fetchAll(PDO::FETCH_ASSOC);
            echo "   Columnas: " . implode(', ', array_column($columnas, 'Field')) . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error MySQL: " . $e->getMessage() . "\n";
    echo "🔧 Posibles causas:\n";
    echo "   - XAMPP MySQL no está corriendo\n";
    echo "   - Base de datos fs_clientes no existe\n";
    echo "   - Credenciales incorrectas\n";
}
?>