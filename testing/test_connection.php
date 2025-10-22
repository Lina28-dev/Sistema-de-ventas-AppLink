<?php
// Test de conexión a la base de datos - ACTUALIZADO
echo "<h2>🔧 Diagnóstico de Conexión MySQL - XAMPP</h2>";
echo "<div style='background: #f0f8ff; padding: 10px; margin: 10px 0; border-left: 4px solid #007cba;'>";
echo "<strong>Estado:</strong> Archivos corruptos eliminados ✅<br>";
echo "<strong>Hora:</strong> " . date('Y-m-d H:i:s') . "<br>";
echo "</div>";

// Configuración
$host = 'localhost';
$usuario = 'root';
$password = '';
$base_datos = 'fs_clientes';

try {
    // Intentar conexión con PDO
    $pdo = new PDO("mysql:host=$host", $usuario, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✅ Conexión exitosa al servidor MySQL</p>";
    
    // Verificar si la base de datos existe
    $stmt = $pdo->query("SHOW DATABASES LIKE '$base_datos'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ Base de datos '$base_datos' encontrada</p>";
        
        // Conectar a la base de datos específica
        $pdo_db = new PDO("mysql:host=$host;dbname=$base_datos", $usuario, $password);
        echo "<p style='color: green;'>✅ Conexión exitosa a la base de datos '$base_datos'</p>";
        
        // Mostrar tablas
        $stmt = $pdo_db->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<p><strong>Tablas encontradas:</strong> " . count($tables) . "</p>";
        if (!empty($tables)) {
            echo "<ul>";
            foreach ($tables as $table) {
                echo "<li>$table</li>";
            }
            echo "</ul>";
        }
        
    } else {
        echo "<p style='color: orange;'>⚠️ Base de datos '$base_datos' no encontrada</p>";
        echo "<p>Bases de datos disponibles:</p>";
        $stmt = $pdo->query("SHOW DATABASES");
        while ($row = $stmt->fetch()) {
            echo "<li>" . $row[0] . "</li>";
        }
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error de conexión: " . $e->getMessage() . "</p>";
    
    // Diagnósticos adicionales
    if (strpos($e->getMessage(), 'Connection refused') !== false) {
        echo "<div style='background: #ffeeee; padding: 10px; border: 1px solid #ff0000;'>";
        echo "<h3>Posibles soluciones:</h3>";
        echo "<ol>";
        echo "<li>Verificar que XAMPP esté instalado correctamente</li>";
        echo "<li>Abrir XAMPP Control Panel</li>";
        echo "<li>Hacer clic en 'Start' junto a MySQL</li>";
        echo "<li>Verificar que no haya conflictos de puerto</li>";
        echo "</ol>";
        echo "</div>";
    }
}

// Información del sistema
echo "<hr>";
echo "<h3>Información del Sistema</h3>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
echo "<p><strong>Servidor:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p><strong>Host:</strong> " . $_SERVER['HTTP_HOST'] . "</p>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2 { color: #333; }
li { margin: 5px 0; }
</style>