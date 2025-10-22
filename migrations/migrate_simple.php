<?php
/**
 * 🔄 MIGRACIÓN SIMPLIFICADA DE DATOS: MySQL → PostgreSQL
 * Sistema de Ventas AppLink - Versión simplificada
 */

echo "🚀 INICIANDO MIGRACIÓN SIMPLIFICADA\n";
echo "===================================\n\n";

try {
    // Conexiones
    $mysql = new PDO("mysql:host=localhost;dbname=fs_clientes", "root", "");
    $postgres = new PDO("pgsql:host=localhost;dbname=ventas_applink", "postgres", "lina");
    
    echo "✅ Conexiones establecidas\n\n";
    
    // 1. MIGRAR USUARIOS
    echo "👥 Migrando usuarios...\n";
    $stmt = $mysql->query("SELECT * FROM fs_usuarios");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Limpiar tabla usuarios
    $postgres->exec("TRUNCATE TABLE usuarios RESTART IDENTITY CASCADE");
    
    $insert_user = $postgres->prepare("
        INSERT INTO usuarios (id, nombre, apellido, nick, email, password, rol, is_admin, activo) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $migrated_users = 0;
    foreach ($usuarios as $user) {
        $is_admin = ($user['is_admin'] == '1' || $user['is_admin'] == 1) ? true : false;
        $rol = $is_admin ? 'admin' : 'usuario';
        $email = $user['email'] ?: $user['nick'] . '@applink.com';
        $apellido = trim($user['apellido']) ?: 'Sin Apellido';
        
        $insert_user->execute([
            (int)$user['id_usuario'],
            $user['nombre'],
            $apellido,
            $user['nick'],
            $email,
            $user['password'],
            $rol,
            $is_admin ? 't' : 'f',  // PostgreSQL boolean como string
            't'                     // Activo siempre true
        ]);
        $migrated_users++;
    }
    echo "✅ $migrated_users usuarios migrados\n\n";
    
    // 2. MIGRAR CLIENTES
    echo "👤 Migrando clientes...\n";
    $stmt = $mysql->query("SELECT * FROM fs_clientes");
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $postgres->exec("TRUNCATE TABLE clientes RESTART IDENTITY CASCADE");
    
    $insert_client = $postgres->prepare("
        INSERT INTO clientes (id, nombre_completo, email, telefono, direccion, ciudad, provincia, codigo_postal, estado) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $migrated_clients = 0;
    foreach ($clientes as $cliente) {
        $insert_client->execute([
            $cliente['id'],
            $cliente['nombre_completo'],
            $cliente['email'] ?: null,
            $cliente['telefono'] ?: null,
            $cliente['direccion'] ?: null,
            $cliente['ciudad'] ?: null,
            $cliente['localidad'] ?: null,
            $cliente['codigo_postal'] ?: null,
            'activo'
        ]);
        $migrated_clients++;
    }
    echo "✅ $migrated_clients clientes migrados\n\n";
    
    // 3. MIGRAR PRODUCTOS
    echo "📦 Migrando productos...\n";
    $stmt = $mysql->query("SELECT p.*, c.nombre as categoria_nombre FROM fs_productos p LEFT JOIN fs_categorias c ON p.id_categoria = c.id_categoria");
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $postgres->exec("TRUNCATE TABLE productos RESTART IDENTITY CASCADE");
    
    $insert_product = $postgres->prepare("
        INSERT INTO productos (id, codigo, descripcion, nombre, categoria, talle, color, cantidad, precio, precio_costo, marca, estado) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $migrated_products = 0;
    foreach ($productos as $producto) {
        $estado = ($producto['activo'] == 1 || $producto['activo'] === true) ? 'activo' : 'inactivo';
        
        $insert_product->execute([
            $producto['id_producto'],
            $producto['codigo'] ?: 'PROD-' . $producto['id_producto'],
            $producto['descripcion'] ?: 'Producto',
            $producto['nombre'] ?: $producto['descripcion'] ?: 'Producto',
            $producto['categoria_nombre'] ?: 'General',
            $producto['talle'] ?: null,
            $producto['color'] ?: null,
            $producto['stock_actual'] ?: 0,
            $producto['precio'] ?: 0.00,
            $producto['precio_costo'] ?: 0.00,
            $producto['marca'] ?: null,
            $estado
        ]);
        $migrated_products++;
    }
    echo "✅ $migrated_products productos migrados\n\n";
    
    // 4. MIGRAR VENTAS
    echo "💰 Migrando ventas...\n";
    $stmt = $mysql->query("SELECT * FROM fs_ventas");
    $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $postgres->exec("TRUNCATE TABLE ventas RESTART IDENTITY CASCADE");
    
    $insert_sale = $postgres->prepare("
        INSERT INTO ventas (id, numero_venta, cliente_id, usuario_id, subtotal, descuento, total, metodo_pago, estado, productos, fecha_venta) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $migrated_sales = 0;
    foreach ($ventas as $venta) {
        $insert_sale->execute([
            $venta['id'],
            $venta['numero_venta'] ?: 'V-' . str_pad($venta['id'], 6, '0', STR_PAD_LEFT),
            $venta['cliente_id'] ?: null,
            $venta['id_usuario'] ?: null,
            $venta['subtotal'] ?: 0.00,
            $venta['descuento'] ?: 0.00,
            $venta['total'] ?: 0.00,
            $venta['metodo_pago'] ?: 'efectivo',
            $venta['estado'] ?: 'completada',
            $venta['productos'] ?: null,
            $venta['fecha_venta'] ?: date('Y-m-d H:i:s')
        ]);
        $migrated_sales++;
    }
    echo "✅ $migrated_sales ventas migradas\n\n";
    
    // 5. ACTUALIZAR SECUENCIAS
    echo "🔄 Actualizando secuencias...\n";
    $tables = ['usuarios', 'clientes', 'productos', 'ventas'];
    
    foreach ($tables as $table) {
        $stmt = $postgres->query("SELECT COALESCE(MAX(id), 0) + 1 as next_id FROM $table");
        $next_id = $stmt->fetch(PDO::FETCH_ASSOC)['next_id'];
        $postgres->exec("SELECT setval('{$table}_id_seq', $next_id, false)");
        echo "✅ Secuencia de $table: $next_id\n";
    }
    
    echo "\n🎉 ¡MIGRACIÓN COMPLETADA EXITOSAMENTE!\n";
    echo "=====================================\n";
    echo "📊 Resumen:\n";
    echo "   👥 Usuarios: $migrated_users\n";
    echo "   👤 Clientes: $migrated_clients\n";
    echo "   📦 Productos: $migrated_products\n";
    echo "   💰 Ventas: $migrated_sales\n";
    echo "\n📋 Próximo paso: Actualizar configuración de la aplicación\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}
?>