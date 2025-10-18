<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Verificación de contraseña del administrador</h2>";

try {
    $conn = new mysqli('localhost', 'root', '', 'fs_clientes');
    if ($conn->connect_error) {
        throw new Exception("Error de conexión: " . $conn->connect_error);
    }

    // Obtener la contraseña actual del admin
    $sql = "SELECT password FROM fs_usuarios WHERE nick = 'admin'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $stored_hash = $user['password'];
        
        echo "<p>Hash almacenado: " . $stored_hash . "</p>";
        
        // Crear nuevo hash para comparar
        $test_password = 'admin123';
        $new_hash = password_hash($test_password, PASSWORD_DEFAULT);
        
        echo "<p>Nuevo hash de prueba: " . $new_hash . "</p>";
        
        // Verificar si la contraseña coincide
        if (password_verify($test_password, $stored_hash)) {
            echo "<p style='color: green;'>✓ La contraseña de prueba coincide con el hash almacenado</p>";
        } else {
            echo "<p style='color: red;'>✗ La contraseña de prueba NO coincide con el hash almacenado</p>";
            
            // Actualizar la contraseña
            $new_password = password_hash('admin123', PASSWORD_DEFAULT);
            $update_sql = "UPDATE fs_usuarios SET password = '$new_password' WHERE nick = 'admin'";
            
            if ($conn->query($update_sql)) {
                echo "<p style='color: green;'>✓ Contraseña actualizada exitosamente</p>";
                echo "<div style='background-color: #f8f9fa; padding: 15px; margin: 15px 0; border-radius: 5px;'>";
                echo "<h3>Nuevas credenciales:</h3>";
                echo "<p>Usuario: admin<br>Contraseña: admin123</p>";
                echo "</div>";
            } else {
                echo "<p style='color: red;'>✗ Error al actualizar la contraseña: " . $conn->error . "</p>";
            }
        }
    } else {
        echo "<p style='color: red;'>✗ No se encontró el usuario admin</p>";
    }

    // Botón para volver
    echo "<div style='margin-top: 20px;'>";
    echo "<a href='test_login.php' style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Probar Login</a>";
    echo "<a href='home.php' style='background-color: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Volver al inicio</a>";
    echo "</div>";

} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}
?>
