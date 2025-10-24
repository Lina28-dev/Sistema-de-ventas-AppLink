<?php
/**
 * Script para agregar sistema de roles a la tabla fs_usuarios
 * Sistema de Ventas AppLink - Roles: administrador, empleado, cliente
 */

header('Content-Type: text/html; charset=utf-8');

try {
    // Conectar a la base de datos
    $conn = new mysqli('localhost', 'root', '', 'fs_clientes');
    
    if ($conn->connect_error) {
        throw new Exception("Error de conexión: " . $conn->connect_error);
    }

    echo "<h2>🔧 Configuración del Sistema de Roles</h2>";

    // 1. Verificar si el campo 'rol' ya existe
    $check_sql = "SHOW COLUMNS FROM fs_usuarios LIKE 'rol'";
    $result = $check_sql = $conn->query($check_sql);
    
    if ($result->num_rows == 0) {
        // Agregar campo 'rol' con valores enum
        $alter_sql = "ALTER TABLE fs_usuarios ADD COLUMN rol ENUM('administrador', 'empleado', 'cliente') DEFAULT 'cliente' AFTER email";
        
        if ($conn->query($alter_sql)) {
            echo "<p style='color: green;'>✅ Campo 'rol' agregado exitosamente a la tabla fs_usuarios</p>";
            
            // Crear índice para optimizar consultas por rol
            $index_sql = "CREATE INDEX idx_rol ON fs_usuarios(rol)";
            $conn->query($index_sql);
            echo "<p style='color: blue;'>📚 Índice creado para el campo 'rol'</p>";
        } else {
            throw new Exception("Error al agregar campo rol: " . $conn->error);
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ El campo 'rol' ya existe en la tabla fs_usuarios</p>";
    }

    // 2. Actualizar usuarios existentes basándose en los flags actuales
    echo "<h3>🔄 Actualizando roles de usuarios existientes...</h3>";
    
    // Administradores (is_admin = 1)
    $update_admin = "UPDATE fs_usuarios SET rol = 'administrador' WHERE is_admin = 1";
    $conn->query($update_admin);
    $admin_count = $conn->affected_rows;
    echo "<p style='color: green;'>👑 $admin_count usuarios marcados como 'administrador'</p>";
    
    // Empleados (is_medium = 1 and is_admin = 0)
    $update_empleado = "UPDATE fs_usuarios SET rol = 'empleado' WHERE is_medium = 1 AND is_admin = 0";
    $conn->query($update_empleado);
    $empleado_count = $conn->affected_rows;
    echo "<p style='color: orange;'>👥 $empleado_count usuarios marcados como 'empleado'</p>";
    
    // Clientes (is_visitor = 1 and is_admin = 0 and is_medium = 0)
    $update_cliente = "UPDATE fs_usuarios SET rol = 'cliente' WHERE is_visitor = 1 AND is_admin = 0 AND is_medium = 0";
    $conn->query($update_cliente);
    $cliente_count = $conn->affected_rows;
    echo "<p style='color: blue;'>🛒 $cliente_count usuarios marcados como 'cliente'</p>";

    // 3. Crear usuario administrador por defecto si no existe
    echo "<h3>👑 Verificando usuario administrador...</h3>";
    
    $check_admin = $conn->query("SELECT * FROM fs_usuarios WHERE rol = 'administrador' LIMIT 1");
    
    if ($check_admin->num_rows == 0) {
        // Crear administrador por defecto
        $admin_password = password_hash('admin123', PASSWORD_BCRYPT);
        $create_admin = $conn->prepare("INSERT INTO fs_usuarios (nombre, apellido, nick, cc, email, password, rol, is_admin, is_medium, is_visitor) VALUES (?, ?, ?, ?, ?, ?, 'administrador', 1, 0, 0)");
        $nombre = "Administrador";
        $apellido = "Sistema";
        $nick = "admin";
        $cc = "1000000000";
        $email = "admin@applink.com";
        
        $create_admin->bind_param("ssssss", $nombre, $apellido, $nick, $cc, $email, $admin_password);
        
        if ($create_admin->execute()) {
            echo "<p style='color: green;'>✅ Usuario administrador creado exitosamente</p>";
            echo "<p style='background: #ffffcc; padding: 10px; border-radius: 5px;'>";
            echo "<strong>📋 Credenciales del Administrador:</strong><br>";
            echo "Usuario: <strong>admin</strong><br>";
            echo "Contraseña: <strong>admin123</strong><br>";
            echo "Email: <strong>admin@applink.com</strong><br>";
            echo "CC: <strong>1000000000</strong>";
            echo "</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ No se pudo crear el usuario administrador (posiblemente ya existe)</p>";
        }
    } else {
        echo "<p style='color: green;'>✅ Ya existe al menos un usuario administrador</p>";
    }

    // 4. Mostrar resumen de la estructura actual
    echo "<h3>📋 Estructura actual de la tabla fs_usuarios:</h3>";
    $describe_sql = "DESCRIBE fs_usuarios";
    $describe_result = $conn->query($describe_sql);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th style='padding: 8px;'>Campo</th>";
    echo "<th style='padding: 8px;'>Tipo</th>";
    echo "<th style='padding: 8px;'>Nulo</th>";
    echo "<th style='padding: 8px;'>Clave</th>";
    echo "<th style='padding: 8px;'>Por Defecto</th>";
    echo "<th style='padding: 8px;'>Extra</th>";
    echo "</tr>";
    
    while ($row = $describe_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td style='padding: 8px;'>" . $row['Field'] . "</td>";
        echo "<td style='padding: 8px;'>" . $row['Type'] . "</td>";
        echo "<td style='padding: 8px;'>" . $row['Null'] . "</td>";
        echo "<td style='padding: 8px;'>" . $row['Key'] . "</td>";
        echo "<td style='padding: 8px;'>" . $row['Default'] . "</td>";
        echo "<td style='padding: 8px;'>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    // 5. Mostrar distribución de roles
    echo "<h3>📊 Distribución actual de roles:</h3>";
    $roles_sql = "SELECT rol, COUNT(*) as cantidad FROM fs_usuarios GROUP BY rol";
    $roles_result = $conn->query($roles_sql);
    
    echo "<table border='1' style='border-collapse: collapse; margin-top: 10px;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th style='padding: 8px;'>Rol</th>";
    echo "<th style='padding: 8px;'>Cantidad de Usuarios</th>";
    echo "</tr>";
    
    while ($row = $roles_result->fetch_assoc()) {
        $color = '';
        switch ($row['rol']) {
            case 'administrador': $color = 'color: #d4af37;'; break;
            case 'empleado': $color = 'color: #ff8c00;'; break;
            case 'cliente': $color = 'color: #4169e1;'; break;
        }
        echo "<tr>";
        echo "<td style='padding: 8px; $color'><strong>" . ucfirst($row['rol']) . "</strong></td>";
        echo "<td style='padding: 8px; text-align: center;'>" . $row['cantidad'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<h3 style='color: green;'>🎉 ¡Sistema de roles configurado exitosamente!</h3>";
    echo "<div style='background: #e6ffe6; padding: 15px; border-radius: 5px; margin-top: 20px;'>";
    echo "<h4>📋 Roles y Permisos Configurados:</h4>";
    echo "<ul>";
    echo "<li><strong>👑 Administrador:</strong> Acceso completo a todo el sistema (tú)</li>";
    echo "<li><strong>👥 Empleado:</strong> Acceso a pedidos y ventas</li>";
    echo "<li><strong>🛒 Cliente:</strong> Acceso solo a pedidos</li>";
    echo "</ul>";
    echo "</div>";

} catch (Exception $e) {
    echo "<p style='color: red; background: #ffe6e6; padding: 10px; border-radius: 5px;'>";
    echo "❌ Error: " . $e->getMessage();
    echo "</p>";
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>