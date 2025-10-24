<?php
/**
 * Test de conexión a la base de datos
 * Sistema de Ventas AppLink
 */

echo "<h2>🔍 Diagnóstico de Conexión a Base de Datos</h2>";

// Test 1: Verificar extensión MySQL
echo "<h3>1. Verificando extensión MySQL</h3>";
if (extension_loaded('mysqli')) {
    echo "<p style='color: green;'>✅ Extensión MySQLi está disponible</p>";
} else {
    echo "<p style='color: red;'>❌ Extensión MySQLi NO está disponible</p>";
}

// Test 2: Intentar conexión
echo "<h3>2. Probando conexión a MySQL</h3>";
try {
    $conn = new mysqli('localhost', 'root', '', '');
    
    if ($conn->connect_error) {
        echo "<p style='color: red;'>❌ Error de conexión a MySQL: " . $conn->connect_error . "</p>";
    } else {
        echo "<p style='color: green;'>✅ Conexión a MySQL exitosa</p>";
        
        // Test 3: Verificar si existe la base de datos
        echo "<h3>3. Verificando base de datos 'fs_clientes'</h3>";
        $result = $conn->query("SHOW DATABASES LIKE 'fs_clientes'");
        
        if ($result->num_rows > 0) {
            echo "<p style='color: green;'>✅ Base de datos 'fs_clientes' existe</p>";
            
            // Test 4: Conectar a la base de datos específica
            $conn->select_db('fs_clientes');
            echo "<p style='color: green;'>✅ Conexión a base de datos 'fs_clientes' exitosa</p>";
            
            // Test 5: Verificar tabla usuarios
            echo "<h3>4. Verificando tabla 'fs_usuarios'</h3>";
            $result = $conn->query("SHOW TABLES LIKE 'fs_usuarios'");
            
            if ($result->num_rows > 0) {
                echo "<p style='color: green;'>✅ Tabla 'fs_usuarios' existe</p>";
                
                // Test 6: Verificar estructura de la tabla
                echo "<h3>5. Verificando estructura de la tabla</h3>";
                $result = $conn->query("DESCRIBE fs_usuarios");
                
                echo "<table style='border: 1px solid #ddd; border-collapse: collapse; margin: 10px 0;'>";
                echo "<tr style='background: #f5f5f5;'><th style='border: 1px solid #ddd; padding: 8px;'>Campo</th><th style='border: 1px solid #ddd; padding: 8px;'>Tipo</th></tr>";
                
                $has_cc = false;
                while ($row = $result->fetch_assoc()) {
                    echo "<tr><td style='border: 1px solid #ddd; padding: 8px;'>" . $row['Field'] . "</td>";
                    echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['Type'] . "</td></tr>";
                    
                    if ($row['Field'] === 'cc') {
                        $has_cc = true;
                    }
                }
                echo "</table>";
                
                if ($has_cc) {
                    echo "<p style='color: green;'>✅ Campo 'cc' existe en la tabla</p>";
                } else {
                    echo "<p style='color: red;'>❌ Campo 'cc' NO existe en la tabla</p>";
                    echo "<p><strong>Solución:</strong> Ejecutar el script de migración</p>";
                }
                
            } else {
                echo "<p style='color: red;'>❌ Tabla 'fs_usuarios' NO existe</p>";
            }
            
        } else {
            echo "<p style='color: red;'>❌ Base de datos 'fs_clientes' NO existe</p>";
            echo "<p><strong>Solución:</strong> Crear la base de datos primero</p>";
        }
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<h3>6. Información del servidor</h3>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>MySQL Client Version:</strong> " . mysqli_get_client_info() . "</p>";

echo "<h3>7. Próximos pasos</h3>";
echo "<ul>";
echo "<li>Si MySQL no está funcionando: Iniciar XAMPP</li>";
echo "<li>Si la BD no existe: <a href='../database/migrate_complete.php'>Ejecutar migración completa</a></li>";
echo "<li>Si falta campo CC: <a href='../database/add_dni_to_users.php'>Ejecutar script CC</a></li>";
echo "<li>Después: <a href='test_register.php'>Probar registro</a></li>";
echo "</ul>";
?>