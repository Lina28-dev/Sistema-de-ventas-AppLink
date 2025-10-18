<?php
require_once(__DIR__ . '/classes/Database.php');

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Verificar si la tabla existe
    $tableExists = $conn->query("SHOW TABLES LIKE 'fs_usuarios'")->rowCount() > 0;
    
    if (!$tableExists) {
        // Crear la tabla si no existe
        $conn->exec("CREATE TABLE IF NOT EXISTS fs_usuarios (
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
        )");
        
        // Insertar usuario administrador por defecto
        $defaultPassword = password_hash('password', PASSWORD_DEFAULT);
        $conn->exec("INSERT INTO fs_usuarios (nombre, apellido, nick, password, email, is_admin) 
                    VALUES ('Administrador', 'Sistema', 'admin', '$defaultPassword', 'admin@lilipink.com', 1)");
        
        echo "Tabla fs_usuarios creada y usuario administrador configurado.<br>";
        echo "Usuario: admin<br>";
        echo "Contraseña: password<br>";
    } else {
        // Verificar si existe el usuario admin
        $stmt = $conn->prepare("SELECT * FROM fs_usuarios WHERE nick = ?");
        $stmt->execute(['admin']);
        
        if ($stmt->rowCount() == 0) {
            // Crear usuario admin si no existe
            $defaultPassword = password_hash('password', PASSWORD_DEFAULT);
            $conn->exec("INSERT INTO fs_usuarios (nombre, apellido, nick, password, email, is_admin) 
                        VALUES ('Administrador', 'Sistema', 'admin', '$defaultPassword', 'admin@lilipink.com', 1)");
            
            echo "Usuario administrador creado.<br>";
            echo "Usuario: admin<br>";
            echo "Contraseña: password<br>";
        } else {
            echo "La configuración está correcta. Puedes iniciar sesión con las credenciales proporcionadas.<br>";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>