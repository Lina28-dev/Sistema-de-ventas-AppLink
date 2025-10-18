<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Verificación de la base de datos</h2>";

try {
    // Intentar conexión
    $conn = new mysqli('localhost', 'root', '', 'fs_clientes');

    if ($conn->connect_error) {
        throw new Exception("Error de conexión: " . $conn->connect_error);
    }

    echo "<p style='color: green;'>✓ Conexión a la base de datos exitosa</p>";

    // Verificar si la tabla fs_usuarios existe
    $result = $conn->query("SHOW TABLES LIKE 'fs_usuarios'");
    if ($result->num_rows == 0) {
        // Crear la tabla si no existe
        $sql = "CREATE TABLE fs_usuarios (
            id_usuario INT PRIMARY KEY AUTO_INCREMENT,
            nombre VARCHAR(30) NOT NULL,
            apellido VARCHAR(40) NOT NULL,
            nick VARCHAR(40) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            is_admin INT DEFAULT 0,
            is_medium INT DEFAULT 0,
            is_visitor INT DEFAULT 1,
            password_changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($sql)) {
            echo "<p style='color: green;'>✓ Tabla fs_usuarios creada exitosamente</p>";
            
            // Insertar usuario administrador
            $password = password_hash('admin123', PASSWORD_DEFAULT);
            $sql = "INSERT INTO fs_usuarios (nombre, apellido, nick, password, email, is_admin) 
                    VALUES ('Administrador', 'Sistema', 'admin', '$password', 'admin@lilipink.com', 1)";
            
            if ($conn->query($sql)) {
                echo "<p style='color: green;'>✓ Usuario administrador creado exitosamente</p>";
                echo "<div style='background-color: #f8f9fa; padding: 15px; margin: 15px 0; border-radius: 5px;'>";
                echo "<h3>Credenciales de acceso:</h3>";
                echo "<p>Usuario: admin<br>Contraseña: admin123</p>";
                echo "</div>";
            } else {
                echo "<p style='color: red;'>✗ Error al crear usuario administrador: " . $conn->error . "</p>";
            }
        } else {
            echo "<p style='color: red;'>✗ Error al crear tabla: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color: green;'>✓ Tabla fs_usuarios ya existe</p>";
        
        // Verificar si existe el usuario admin
        $result = $conn->query("SELECT * FROM fs_usuarios WHERE nick = 'admin'");
        if ($result->num_rows == 0) {
            $password = password_hash('admin123', PASSWORD_DEFAULT);
            $sql = "INSERT INTO fs_usuarios (nombre, apellido, nick, password, email, is_admin) 
                    VALUES ('Administrador', 'Sistema', 'admin', '$password', 'admin@lilipink.com', 1)";
            
            if ($conn->query($sql)) {
                echo "<p style='color: green;'>✓ Usuario administrador creado exitosamente</p>";
                echo "<div style='background-color: #f8f9fa; padding: 15px; margin: 15px 0; border-radius: 5px;'>";
                echo "<h3>Credenciales de acceso:</h3>";
                echo "<p>Usuario: admin<br>Contraseña: admin123</p>";
                echo "</div>";
            }
        } else {
            echo "<p style='color: green;'>✓ Usuario administrador ya existe</p>";
        }
    }

    // Crear un botón para volver al inicio
    echo "<div style='margin-top: 20px;'>";
    echo "<a href='home.php' style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Volver al inicio</a>";
    echo "</div>";

} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}
?>
