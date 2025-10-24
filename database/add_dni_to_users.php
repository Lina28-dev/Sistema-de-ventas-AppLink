<?php
/**
 * Script para agregar campo CC a la tabla fs_usuarios
 * Sistema de Ventas AppLink
 */

try {
    $conn = new mysqli('localhost', 'root', '', 'fs_clientes');
    
    if ($conn->connect_error) {
        throw new Exception("Error de conexión: " . $conn->connect_error);
    }
    
    echo "<h2>🔧 Agregando campo CC a tabla de usuarios</h2>";
    
    // Verificar si la columna ya existe
    $check_sql = "SHOW COLUMNS FROM fs_usuarios LIKE 'cc'";
    $result = $conn->query($check_sql);
    
    if ($result->num_rows == 0) {
        // La columna no existe, la agregamos
        $alter_sql = "ALTER TABLE fs_usuarios ADD COLUMN cc VARCHAR(20) UNIQUE AFTER email";
        
        if ($conn->query($alter_sql)) {
            echo "<p style='color: green;'>✅ Campo CC agregado exitosamente a la tabla fs_usuarios</p>";
            
            // Crear índice para el campo cc
            $index_sql = "CREATE INDEX idx_cc ON fs_usuarios(cc)";
            if ($conn->query($index_sql)) {
                echo "<p style='color: green;'>✅ Índice para CC creado exitosamente</p>";
            } else {
                echo "<p style='color: orange;'>⚠️ Advertencia: No se pudo crear el índice para CC: " . $conn->error . "</p>";
            }
            
        } else {
            throw new Exception("Error al agregar campo CC: " . $conn->error);
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ El campo CC ya existe en la tabla fs_usuarios</p>";
    }
    
    // Mostrar estructura actualizada de la tabla
    echo "<h3>📋 Estructura actual de la tabla fs_usuarios:</h3>";
    $describe_sql = "DESCRIBE fs_usuarios";
    $result = $conn->query($describe_sql);
    
    echo "<table style='border-collapse: collapse; width: 100%; border: 1px solid #ddd;'>";
    echo "<tr style='background-color: #f2f2f2;'>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Campo</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Tipo</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Nulo</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Clave</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Por defecto</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Extra</th>";
    echo "</tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['Field'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['Type'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['Null'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['Key'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . ($row['Default'] ?? 'NULL') . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>🎉 Migración de campo CC completada exitosamente</h3>";
    echo "<div style='background: #f0f8ff; padding: 15px; border-left: 4px solid #007bff; margin: 15px 0;'>";
    echo "<strong>📝 Notas importantes:</strong><br>";
    echo "• El campo CC es único para cada usuario<br>";
    echo "• Se debe actualizar el formulario de registro para incluir este campo<br>";
    echo "• El formulario de restablecimiento de contraseña ahora puede validar por CC + email";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red; font-weight: bold;'>❌ Error: " . $e->getMessage() . "</p>";
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>