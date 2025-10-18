<?php
/**
 * Script de migración completa de base de datos
 * Sistema de Ventas AppLink
 */

try {
    $conn = new mysqli('localhost', 'root', '', 'fs_clientes');
    
    if ($conn->connect_error) {
        throw new Exception("Error de conexión: " . $conn->connect_error);
    }
    
    echo "<h2>🚀 Ejecutando Migraciones de Base de Datos</h2>";
    
    // 1. Tabla de usuarios (mejorada)
    $sql_usuarios = "CREATE TABLE IF NOT EXISTS fs_usuarios (
        id_usuario INT PRIMARY KEY AUTO_INCREMENT,
        nombre VARCHAR(50) NOT NULL,
        apellido VARCHAR(50) NOT NULL,
        nick VARCHAR(40) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        is_admin TINYINT(1) DEFAULT 0,
        is_medium TINYINT(1) DEFAULT 0,
        is_visitor TINYINT(1) DEFAULT 1,
        activo TINYINT(1) DEFAULT 1,
        ultimo_acceso TIMESTAMP NULL,
        password_changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    // 2. Tabla de categorías
    $sql_categorias = "CREATE TABLE IF NOT EXISTS fs_categorias (
        id_categoria INT PRIMARY KEY AUTO_INCREMENT,
        nombre VARCHAR(50) NOT NULL UNIQUE,
        descripcion TEXT,
        activo TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    // 3. Tabla de productos (mejorada)
    $sql_productos = "CREATE TABLE IF NOT EXISTS fs_productos (
        id_producto INT PRIMARY KEY AUTO_INCREMENT,
        codigo VARCHAR(20) UNIQUE,
        nombre VARCHAR(100) NOT NULL,
        descripcion TEXT,
        id_categoria INT,
        precio DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        precio_costo DECIMAL(10,2) DEFAULT 0.00,
        stock_actual INT DEFAULT 0,
        stock_minimo INT DEFAULT 0,
        talle VARCHAR(10),
        color VARCHAR(30),
        marca VARCHAR(50),
        modelo VARCHAR(50),
        activo TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (id_categoria) REFERENCES fs_categorias(id_categoria)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    // 4. Tabla de clientes (mejorada)
    $sql_clientes = "CREATE TABLE IF NOT EXISTS fs_clientes (
        id_cliente INT PRIMARY KEY AUTO_INCREMENT,
        nombre_completo VARCHAR(100) NOT NULL,
        dni VARCHAR(20),
        telefono VARCHAR(20),
        email VARCHAR(255),
        direccion TEXT,
        ciudad VARCHAR(50),
        provincia VARCHAR(50),
        codigo_postal VARCHAR(10),
        es_revendedora TINYINT(1) DEFAULT 0,
        descuento_porcentaje DECIMAL(5,2) DEFAULT 0.00,
        activo TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_dni (dni),
        INDEX idx_email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    // 5. Tabla de ventas
    $sql_ventas = "CREATE TABLE IF NOT EXISTS fs_ventas (
        id_venta INT PRIMARY KEY AUTO_INCREMENT,
        numero_venta VARCHAR(20) UNIQUE,
        id_cliente INT,
        id_usuario INT NOT NULL,
        fecha_venta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        descuento DECIMAL(10,2) DEFAULT 0.00,
        impuestos DECIMAL(10,2) DEFAULT 0.00,
        total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        estado ENUM('pendiente', 'completada', 'cancelada') DEFAULT 'pendiente',
        metodo_pago ENUM('efectivo', 'tarjeta', 'transferencia', 'otro') DEFAULT 'efectivo',
        notas TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_cliente) REFERENCES fs_clientes(id_cliente),
        FOREIGN KEY (id_usuario) REFERENCES fs_usuarios(id_usuario)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    // 6. Tabla de detalle de ventas
    $sql_venta_detalles = "CREATE TABLE IF NOT EXISTS fs_venta_detalles (
        id_detalle INT PRIMARY KEY AUTO_INCREMENT,
        id_venta INT NOT NULL,
        id_producto INT NOT NULL,
        cantidad INT NOT NULL DEFAULT 1,
        precio_unitario DECIMAL(10,2) NOT NULL,
        descuento DECIMAL(10,2) DEFAULT 0.00,
        subtotal DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (id_venta) REFERENCES fs_ventas(id_venta) ON DELETE CASCADE,
        FOREIGN KEY (id_producto) REFERENCES fs_productos(id_producto)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    // 7. Tabla de movimientos de stock
    $sql_movimientos = "CREATE TABLE IF NOT EXISTS fs_movimientos_stock (
        id_movimiento INT PRIMARY KEY AUTO_INCREMENT,
        id_producto INT NOT NULL,
        id_usuario INT NOT NULL,
        tipo_movimiento ENUM('entrada', 'salida', 'ajuste') NOT NULL,
        cantidad INT NOT NULL,
        stock_anterior INT NOT NULL,
        stock_nuevo INT NOT NULL,
        motivo VARCHAR(255),
        referencia VARCHAR(100),
        fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_producto) REFERENCES fs_productos(id_producto),
        FOREIGN KEY (id_usuario) REFERENCES fs_usuarios(id_usuario)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    // 8. Tabla de sesiones (para seguridad)
    $sql_sesiones = "CREATE TABLE IF NOT EXISTS fs_sesiones (
        id_sesion VARCHAR(128) PRIMARY KEY,
        id_usuario INT NOT NULL,
        ip_address VARCHAR(45),
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        activa TINYINT(1) DEFAULT 1,
        FOREIGN KEY (id_usuario) REFERENCES fs_usuarios(id_usuario) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    // Ejecutar las migraciones
    $tablas = [
        'usuarios' => $sql_usuarios,
        'categorías' => $sql_categorias,
        'productos' => $sql_productos,
        'clientes' => $sql_clientes,
        'ventas' => $sql_ventas,
        'venta_detalles' => $sql_venta_detalles,
        'movimientos_stock' => $sql_movimientos,
        'sesiones' => $sql_sesiones
    ];
    
    foreach ($tablas as $nombre => $sql) {
        if ($conn->query($sql)) {
            echo "<p style='color: green;'>✅ Tabla {$nombre} creada/verificada exitosamente</p>";
        } else {
            echo "<p style='color: red;'>❌ Error al crear tabla {$nombre}: " . $conn->error . "</p>";
        }
    }
    
    // Insertar datos iniciales
    echo "<h3>📊 Insertando datos iniciales...</h3>";
    
    // Categorías por defecto
    $categorias_default = [
        ['Ropa Interior Femenina', 'Brasieres, panties, conjuntos'],
        ['Ropa Interior Masculina', 'Bóxers, camisetas'],
        ['Pijamas', 'Pijamas para toda la familia'],
        ['Medias y Calcetines', 'Medias, calcetines, tobilleras'],
        ['Accesorios', 'Complementos y accesorios']
    ];
    
    $stmt_cat = $conn->prepare("INSERT IGNORE INTO fs_categorias (nombre, descripcion) VALUES (?, ?)");
    foreach ($categorias_default as $cat) {
        $stmt_cat->execute($cat);
    }
    echo "<p style='color: green;'>✅ Categorías por defecto insertadas</p>";
    
    // Usuario administrador
    $admin_exists = $conn->query("SELECT id_usuario FROM fs_usuarios WHERE nick = 'admin'")->num_rows;
    if ($admin_exists == 0) {
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt_admin = $conn->prepare("INSERT INTO fs_usuarios (nombre, apellido, nick, password, email, is_admin) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_admin->execute(['Administrador', 'Sistema', 'admin', $password, 'admin@applink.com', 1]);
        echo "<p style='color: green;'>✅ Usuario administrador creado</p>";
        echo "<div style='background: #f0f8ff; padding: 15px; border-left: 4px solid #007bff; margin: 15px 0;'>";
        echo "<strong>🔑 Credenciales de acceso:</strong><br>";
        echo "Usuario: <code>admin</code><br>";
        echo "Contraseña: <code>admin123</code>";
        echo "</div>";
    } else {
        echo "<p style='color: blue;'>ℹ️ Usuario administrador ya existe</p>";
    }
    
    echo "<h3>🎉 Migración completada exitosamente</h3>";
    echo "<a href='/Sistema-de-ventas-AppLink-main/public/' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ir al Sistema</a>";
    
} catch (Exception $e) {
    echo "<p style='color: red; font-weight: bold;'>❌ Error: " . $e->getMessage() . "</p>";
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>