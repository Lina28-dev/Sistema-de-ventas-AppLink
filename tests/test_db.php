<?php
require_once(__DIR__ . '/classes/Database.php');

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Verificar si la tabla existe
    $tableCheck = $conn->query("SHOW TABLES LIKE 'fs_usuarios'");
    if ($tableCheck->rowCount() == 0) {
        echo "La tabla fs_usuarios no existe.<br>";
        
        // Crear la tabla
        $sql = "CREATE TABLE IF NOT EXISTS fs_usuarios (
            id_usuario INT AUTO_INCREMENT PRIMARY KEY,
            nick VARCHAR(50) NOT NULL UNIQUE,
            nombre VARCHAR(100) NOT NULL,
            password VARCHAR(255) NOT NULL,
            is_admin TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $conn->exec($sql);
        echo "Tabla fs_usuarios creada.<br>";
    }
    
    // Verificar si existe el usuario admin
    $stmt = $conn->prepare("SELECT * FROM fs_usuarios WHERE nick = ?");
    $stmt->execute(['admin']);
    $user = $stmt->fetch();
    
    if (!$user) {
        // Crear usuario admin
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $sql = "INSERT INTO fs_usuarios (nick, nombre, password, is_admin) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['admin', 'Administrador', $hashedPassword, 1]);
        echo "Usuario admin creado.<br>";
    } else {
        echo "El usuario admin ya existe.<br>";
        // Actualizar la contraseña
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $sql = "UPDATE fs_usuarios SET password = ? WHERE nick = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$hashedPassword, 'admin']);
        echo "Contraseña de admin actualizada.<br>";
    }
    
    // Mostrar todos los usuarios
    $stmt = $conn->query("SELECT id_usuario, nick, nombre, is_admin FROM fs_usuarios");
    echo "<br>Usuarios en la base de datos:<br>";
    while ($row = $stmt->fetch()) {
        echo "ID: " . $row['id_usuario'] . ", Nick: " . $row['nick'] . 
             ", Nombre: " . $row['nombre'] . ", Es admin: " . $row['is_admin'] . "<br>";
    }
    
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}
?>
