<?php
/**
 * Script para crear usuario de prueba
 * Sistema de Ventas AppLink
 */

try {
    $conn = new mysqli('localhost', 'root', '', 'fs_clientes');
    
    if ($conn->connect_error) {
        throw new Exception("Error de conexión: " . $conn->connect_error);
    }
    
    echo "<h2>🧪 Creando usuario de prueba para testing</h2>";
    
    // Datos del usuario de prueba
    $nombre = "Juan";
    $apellido = "Pérez";
    $nick = "juantest";
    $cc = "12345678";
    $email = "juan@test.com";
    $password = password_hash("123456", PASSWORD_BCRYPT);
    
    // Verificar si ya existe
    $check = $conn->prepare("SELECT id_usuario FROM fs_usuarios WHERE email = ? OR cc = ? OR nick = ?");
    $check->bind_param("sss", $email, $cc, $nick);
    $check->execute();
    
    if ($check->get_result()->num_rows > 0) {
        echo "<p style='color: blue;'>ℹ️ El usuario de prueba ya existe</p>";
    } else {
        // Crear usuario
        $stmt = $conn->prepare("INSERT INTO fs_usuarios (nombre, apellido, nick, cc, email, password, is_visitor) VALUES (?, ?, ?, ?, ?, ?, 1)");
        $stmt->bind_param("ssssss", $nombre, $apellido, $nick, $cc, $email, $password);
        
        if ($stmt->execute()) {
            echo "<p style='color: green;'>✅ Usuario de prueba creado exitosamente</p>";
        } else {
            throw new Exception("Error al crear usuario: " . $stmt->error);
        }
    }
    
    echo "<div style='background: #f0f8ff; padding: 15px; border-left: 4px solid #007bff; margin: 15px 0;'>";
    echo "<strong>🔑 Datos para testing:</strong><br>";
    echo "Email: <code>juan@test.com</code><br>";
    echo "CC: <code>12345678</code><br>";
    echo "Usuario: <code>juantest</code><br>";
    echo "Contraseña: <code>123456</code>";
    echo "</div>";
    
    echo "<h3>🧪 Cómo probar:</h3>";
    echo "<ol>";
    echo "<li>Ve a <a href='reset_password.php'>reset_password.php</a></li>";
    echo "<li>Ingresa email: <strong>juan@test.com</strong></li>";
    echo "<li>Ingresa CC: <strong>12345678</strong></li>";
    echo "<li>Haz clic en 'Restablecer Contraseña'</li>";
    echo "<li>Deberías recibir una nueva contraseña temporal</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<p style='color: red; font-weight: bold;'>❌ Error: " . $e->getMessage() . "</p>";
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>