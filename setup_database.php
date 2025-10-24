<?php
// Script de configuración automática de la base de datos
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔧 Configuración Automática - AppLink</h2>";

function createMySQLSetup() {
    try {
        // Conexión a MySQL
        $conn = new mysqli('localhost', 'root', '');
        
        if ($conn->connect_error) {
            throw new Exception("Error de conexión: " . $conn->connect_error);
        }
        
        echo "<p style='color: green;'>✅ Conectado a MySQL</p>";
        
        // Crear base de datos si no existe
        $conn->query("CREATE DATABASE IF NOT EXISTS fs_clientes");
        $conn->select_db('fs_clientes');
        
        echo "<p style='color: green;'>✅ Base de datos 'fs_clientes' lista</p>";
        
        // Crear tabla fs_usuarios con estructura completa
        $sql_table = "
        CREATE TABLE IF NOT EXISTS fs_usuarios (
            id_usuario INT AUTO_INCREMENT PRIMARY KEY,
            nick VARCHAR(50) UNIQUE NOT NULL,
            nombre VARCHAR(100) NOT NULL,
            apellido VARCHAR(100) NOT NULL,
            email VARCHAR(150) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            rol ENUM('administrador', 'empleado', 'cliente') DEFAULT 'cliente',
            is_admin BOOLEAN DEFAULT FALSE,
            is_medium BOOLEAN DEFAULT FALSE,
            cc VARCHAR(20),
            telefono VARCHAR(20),
            direccion TEXT,
            ciudad VARCHAR(100),
            ultimo_acceso TIMESTAMP NULL,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            activo BOOLEAN DEFAULT TRUE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        if ($conn->query($sql_table)) {
            echo "<p style='color: green;'>✅ Tabla 'fs_usuarios' creada/verificada</p>";
        } else {
            throw new Exception("Error creando tabla: " . $conn->error);
        }
        
        // Verificar si ya existen usuarios
        $result = $conn->query("SELECT COUNT(*) as count FROM fs_usuarios");
        $count = $result->fetch_assoc()['count'];
        
        if ($count == 0) {
            // Crear usuarios por defecto
            $users = [
                [
                    'nick' => 'admin',
                    'nombre' => 'Administrador',
                    'apellido' => 'Sistema',
                    'email' => 'admin@applink.com',
                    'password' => password_hash('admin123', PASSWORD_DEFAULT),
                    'rol' => 'administrador',
                    'is_admin' => 1,
                    'cc' => '12345678'
                ],
                [
                    'nick' => 'empleado1',
                    'nombre' => 'María',
                    'apellido' => 'González',
                    'email' => 'maria@applink.com',
                    'password' => password_hash('empleado123', PASSWORD_DEFAULT),
                    'rol' => 'empleado',
                    'is_medium' => 1,
                    'cc' => '87654321'
                ],
                [
                    'nick' => 'cliente1',
                    'nombre' => 'Carlos',
                    'apellido' => 'López',
                    'email' => 'carlos@email.com',
                    'password' => password_hash('cliente123', PASSWORD_DEFAULT),
                    'rol' => 'cliente',
                    'cc' => '11223344'
                ]
            ];
            
            foreach ($users as $user) {
                $sql = "INSERT INTO fs_usuarios (nick, nombre, apellido, email, password, rol, is_admin, is_medium, cc) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ssssssiii', 
                    $user['nick'], 
                    $user['nombre'], 
                    $user['apellido'], 
                    $user['email'], 
                    $user['password'], 
                    $user['rol'],
                    $user['is_admin'] ?? 0,
                    $user['is_medium'] ?? 0,
                    $user['cc']
                );
                
                if ($stmt->execute()) {
                    echo "<p style='color: blue;'>👤 Usuario '{$user['nick']}' creado</p>";
                } else {
                    echo "<p style='color: red;'>❌ Error creando usuario '{$user['nick']}': " . $stmt->error . "</p>";
                }
            }
            
            echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
            echo "<h3>🔑 Credenciales de Acceso:</h3>";
            echo "<strong>Administrador:</strong> admin / admin123<br>";
            echo "<strong>Empleado:</strong> empleado1 / empleado123<br>";
            echo "<strong>Cliente:</strong> cliente1 / cliente123<br>";
            echo "</div>";
            
        } else {
            echo "<p style='color: orange;'>ℹ️ Ya existen $count usuarios en la base de datos</p>";
        }
        
        $conn->close();
        return true;
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error MySQL: " . $e->getMessage() . "</p>";
        return false;
    }
}

function createPostgreSQLSetup() {
    try {
        $config = require __DIR__ . '/config/app.php';
        $dsn = "pgsql:host={$config['db']['host']};port={$config['db']['port']};dbname={$config['db']['name']}";
        $pdo = new PDO($dsn, $config['db']['user'], $config['db']['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        echo "<p style='color: green;'>✅ Conectado a PostgreSQL</p>";
        
        // Crear tabla usuarios
        $sql_table = "
        CREATE TABLE IF NOT EXISTS usuarios (
            id SERIAL PRIMARY KEY,
            nick VARCHAR(50) UNIQUE NOT NULL,
            usuario VARCHAR(50) UNIQUE,
            nombre VARCHAR(100) NOT NULL,
            apellido VARCHAR(100) NOT NULL,
            email VARCHAR(150) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            contrasena VARCHAR(255),
            rol VARCHAR(20) DEFAULT 'cliente',
            is_admin BOOLEAN DEFAULT FALSE,
            is_medium BOOLEAN DEFAULT FALSE,
            cc VARCHAR(20),
            telefono VARCHAR(20),
            direccion TEXT,
            ciudad VARCHAR(100),
            ultimo_acceso TIMESTAMP,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            activo BOOLEAN DEFAULT TRUE
        );
        ";
        
        $pdo->exec($sql_table);
        echo "<p style='color: green;'>✅ Tabla 'usuarios' creada/verificada en PostgreSQL</p>";
        
        // Verificar usuarios existentes
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM usuarios");
        $count = $stmt->fetch()['count'];
        
        if ($count == 0) {
            // Crear usuarios por defecto
            $users = [
                ['admin', 'admin', 'Administrador', 'Sistema', 'admin@applink.com', 'admin123', 'administrador', true, false],
                ['empleado1', 'empleado1', 'María', 'González', 'maria@applink.com', 'empleado123', 'empleado', false, true],
                ['cliente1', 'cliente1', 'Carlos', 'López', 'carlos@email.com', 'cliente123', 'cliente', false, false]
            ];
            
            $sql = "INSERT INTO usuarios (nick, usuario, nombre, apellido, email, password, contrasena, rol, is_admin, is_medium) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            
            foreach ($users as $user) {
                $hashed_password = password_hash($user[5], PASSWORD_DEFAULT);
                $stmt->execute([
                    $user[0], $user[1], $user[2], $user[3], $user[4], 
                    $hashed_password, $hashed_password, $user[6], $user[7], $user[8]
                ]);
                echo "<p style='color: blue;'>👤 Usuario PostgreSQL '{$user[0]}' creado</p>";
            }
        }
        
        return true;
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error PostgreSQL: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Ejecutar configuraciones
echo "<h3>Configurando MySQL...</h3>";
$mysql_success = createMySQLSetup();

echo "<h3>Configurando PostgreSQL...</h3>";
$postgresql_success = createPostgreSQLSetup();

if ($mysql_success || $postgresql_success) {
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>✅ Configuración Completada</h3>";
    echo "<p>El sistema está listo para usar. Puedes hacer login con las credenciales mostradas arriba.</p>";
    echo "<p><a href='/Sistema-de-ventas-AppLink-main/public/' class='btn' style='background: #e91e63; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ir al Sistema</a></p>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>❌ Error en la Configuración</h3>";
    echo "<p>Hubo problemas configurando la base de datos. Revisa la configuración de conexión.</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><strong>Tiempo de ejecución:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><a href='/Sistema-de-ventas-AppLink-main/debug_connection.php'>🔍 Verificar Conexión</a></p>";
?>