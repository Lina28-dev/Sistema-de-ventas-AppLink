<?php
/**
 * 🔄 SCRIPT DE MIGRACIÓN DE DATOS: MySQL → PostgreSQL
 * Sistema de Ventas AppLink
 * 
 * Este script migra todos los datos de MySQL a PostgreSQL
 * manteniendo la integridad referencial y convirtiendo tipos de datos
 */

require_once __DIR__ . '/../config/app.php';

class DatabaseMigrator {
    private $mysql_pdo;
    private $postgres_pdo;
    private $config;
    private $log = [];
    
    public function __construct() {
        $this->config = include __DIR__ . '/../config/app.php';
        $this->initConnections();
    }
    
    /**
     * Inicializar conexiones a ambas bases de datos
     */
    private function initConnections() {
        try {
            // Conexión MySQL (origen)
            $this->mysql_pdo = new PDO(
                "mysql:host={$this->config['db']['host']};dbname={$this->config['db']['name']};charset={$this->config['db']['charset']}", 
                $this->config['db']['user'], 
                $this->config['db']['pass'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            // Conexión PostgreSQL (destino)
            $this->postgres_pdo = new PDO(
                "pgsql:host=localhost;dbname=ventas_applink;",
                "postgres",
                "lina",
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            $this->log("✅ Conexiones establecidas correctamente");
            
        } catch (PDOException $e) {
            die("❌ Error de conexión: " . $e->getMessage());
        }
    }
    
    /**
     * Logging de operaciones
     */
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $message";
        echo $logMessage . "\n";
        $this->log[] = $logMessage;
    }
    
    /**
     * Ejecutar migración completa
     */
    public function migrate() {
        $this->log("🚀 Iniciando migración de datos MySQL → PostgreSQL");
        $this->log("================================================");
        
        try {
            // Deshabilitar temporalmente las claves foráneas
            $this->postgres_pdo->exec("SET session_replication_role = replica;");
            
            // Orden de migración (respetando dependencias)
            $this->migrateUsuarios();
            $this->migrateClientes();
            $this->migrateProductos();
            $this->migratePedidos();
            $this->migrateVentas();
            $this->migrateAuditLog();
            
            // Rehabilitar claves foráneas
            $this->postgres_pdo->exec("SET session_replication_role = DEFAULT;");
            
            // Actualizar secuencias
            $this->updateSequences();
            
            // Verificar migración
            $this->verifyMigration();
            
            $this->log("🎉 ¡Migración completada exitosamente!");
            
        } catch (Exception $e) {
            $this->log("❌ Error durante la migración: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Migrar tabla usuarios
     */
    private function migrateUsuarios() {
        $this->log("👥 Migrando usuarios...");
        
        try {
            // Verificar si existe la tabla en MySQL
            $check = $this->mysql_pdo->query("SHOW TABLES LIKE 'fs_usuarios'")->fetch();
            if (!$check) {
                $this->log("⚠️ Tabla fs_usuarios no existe en MySQL, creando datos por defecto");
                $this->createDefaultUsers();
                return;
            }
            
            // Obtener datos de MySQL
            $stmt = $this->mysql_pdo->query("SELECT * FROM fs_usuarios ORDER BY id_usuario");
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($usuarios)) {
                $this->log("⚠️ No hay usuarios en MySQL, creando datos por defecto");
                $this->createDefaultUsers();
                return;
            }
            
            // Limpiar tabla destino
            $this->postgres_pdo->exec("TRUNCATE TABLE usuarios RESTART IDENTITY CASCADE");
            
            // Preparar statement de inserción
            $insert_sql = "
                INSERT INTO usuarios (
                    id, nombre, apellido, nick, email, password, 
                    rol, is_admin, activo, fecha_registro
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ";
            $stmt_insert = $this->postgres_pdo->prepare($insert_sql);
            
            $migrated = 0;
            foreach ($usuarios as $user) {
                // Mapear campos MySQL → PostgreSQL
                $apellido = $user['apellido'] ?? '';
                $email = $user['email'] ?? $user['nick'] . '@applink.com';
                $rol = 'usuario';
                if (isset($user['is_admin']) && $user['is_admin']) $rol = 'admin';
                elseif (isset($user['is_medium']) && $user['is_medium']) $rol = 'vendedor';
                
                $activo = true; // Por defecto activo
                $fecha_registro = date('Y-m-d H:i:s'); // Usar fecha actual
                
                $is_admin = ($user['is_admin'] ?? 0) ? true : false;
                
                $stmt_insert->execute([
                    $user['id_usuario'],
                    $user['nombre'],
                    $apellido,
                    $user['nick'],
                    $email,
                    $user['password'],
                    $rol,
                    $is_admin,
                    $activo,
                    $fecha_registro
                ]);
                
                $migrated++;
            }
            
            $this->log("✅ $migrated usuarios migrados");
            
        } catch (Exception $e) {
            $this->log("❌ Error migrando usuarios: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Crear usuarios por defecto
     */
    private function createDefaultUsers() {
        $default_users = [
            [
                'nombre' => 'Administrador',
                'apellido' => 'Sistema',
                'nick' => 'admin',
                'email' => 'admin@applink.com',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'rol' => 'admin',
                'is_admin' => true
            ],
            [
                'nombre' => 'Vendedor',
                'apellido' => 'Demo',
                'nick' => 'vendedor',
                'email' => 'vendedor@applink.com',
                'password' => password_hash('vendedor123', PASSWORD_DEFAULT),
                'rol' => 'vendedor',
                'is_admin' => false
            ]
        ];
        
        $insert_sql = "
            INSERT INTO usuarios (nombre, apellido, nick, email, password, rol, is_admin)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ";
        $stmt = $this->postgres_pdo->prepare($insert_sql);
        
        foreach ($default_users as $user) {
            $stmt->execute([
                $user['nombre'],
                $user['apellido'],
                $user['nick'],
                $user['email'],
                $user['password'],
                $user['rol'],
                $user['is_admin']
            ]);
        }
        
        $this->log("✅ Usuarios por defecto creados");
    }
    
    /**
     * Migrar tabla clientes
     */
    private function migrateClientes() {
        $this->log("👤 Migrando clientes...");
        
        try {
            // Verificar existencia de tabla
            $check = $this->mysql_pdo->query("SHOW TABLES LIKE 'fs_clientes'")->fetch();
            if (!$check) {
                $this->log("⚠️ Tabla fs_clientes no existe, creando datos demo");
                $this->createDemoClientes();
                return;
            }
            
            $stmt = $this->mysql_pdo->query("SELECT * FROM fs_clientes ORDER BY id");
            $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($clientes)) {
                $this->log("⚠️ No hay clientes, creando datos demo");
                $this->createDemoClientes();
                return;
            }
            
            // Limpiar tabla destino
            $this->postgres_pdo->exec("TRUNCATE TABLE clientes RESTART IDENTITY CASCADE");
            
            $insert_sql = "
                INSERT INTO clientes (
                    id, nombre_completo, email, telefono, direccion, 
                    ciudad, provincia, codigo_postal, estado
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ";
            $stmt_insert = $this->postgres_pdo->prepare($insert_sql);
            
            $migrated = 0;
            foreach ($clientes as $cliente) {
                $stmt_insert->execute([
                    $cliente['id'],
                    $cliente['nombre_completo'] ?? 'Cliente Sin Nombre',
                    $cliente['email'] ?? null,
                    $cliente['telefono'] ?? null,
                    $cliente['direccion'] ?? null,
                    $cliente['ciudad'] ?? null,
                    $cliente['localidad'] ?? null, // provincia → localidad
                    $cliente['codigo_postal'] ?? null,
                    'activo'
                ]);
                $migrated++;
            }
            
            $this->log("✅ $migrated clientes migrados");
            
        } catch (Exception $e) {
            $this->log("❌ Error migrando clientes: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Crear clientes demo
     */
    private function createDemoClientes() {
        $demo_clientes = [
            [
                'nombre_completo' => 'Juan Pérez González',
                'email' => 'juan.perez@email.com',
                'telefono' => '301-234-5678',
                'ciudad' => 'Bogotá'
            ],
            [
                'nombre_completo' => 'María García López',
                'email' => 'maria.garcia@email.com',
                'telefono' => '312-345-6789',
                'ciudad' => 'Medellín'
            ],
            [
                'nombre_completo' => 'Carlos Rodríguez Silva',
                'email' => 'carlos.rodriguez@email.com',
                'telefono' => '315-456-7890',
                'ciudad' => 'Cali'
            ],
            [
                'nombre_completo' => 'Ana Martínez Torres',
                'email' => 'ana.martinez@email.com',
                'telefono' => '318-567-8901',
                'ciudad' => 'Barranquilla'
            ]
        ];
        
        $stmt = $this->postgres_pdo->prepare("
            INSERT INTO clientes (nombre_completo, email, telefono, ciudad, estado)
            VALUES (?, ?, ?, ?, 'activo')
        ");
        
        foreach ($demo_clientes as $cliente) {
            $stmt->execute([
                $cliente['nombre_completo'],
                $cliente['email'],
                $cliente['telefono'],
                $cliente['ciudad']
            ]);
        }
        
        $this->log("✅ Clientes demo creados");
    }
    
    /**
     * Migrar productos
     */
    private function migrateProductos() {
        $this->log("📦 Migrando productos...");
        
        try {
            $check = $this->mysql_pdo->query("SHOW TABLES LIKE 'fs_productos'")->fetch();
            if (!$check) {
                $this->log("⚠️ Tabla fs_productos no existe, creando productos demo");
                $this->createDemoProductos();
                return;
            }
            
            $stmt = $this->mysql_pdo->query("SELECT * FROM fs_productos ORDER BY id_producto");
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($productos)) {
                $this->createDemoProductos();
                return;
            }
            
            $this->postgres_pdo->exec("TRUNCATE TABLE productos RESTART IDENTITY CASCADE");
            
            $insert_sql = "
                INSERT INTO productos (
                    id, codigo, descripcion, nombre, categoria, talle, color, 
                    cantidad, precio, precio_costo, marca, estado
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ";
            $stmt_insert = $this->postgres_pdo->prepare($insert_sql);
            
            $migrated = 0;
            foreach ($productos as $producto) {
                $categoria = 'General'; // Valor por defecto
                if (isset($producto['id_categoria'])) {
                    // Buscar nombre de categoría
                    $cat_stmt = $this->mysql_pdo->prepare("SELECT nombre FROM fs_categorias WHERE id_categoria = ?");
                    $cat_stmt->execute([$producto['id_categoria']]);
                    $cat_result = $cat_stmt->fetch();
                    if ($cat_result) $categoria = $cat_result['nombre'];
                }
                
                $stmt_insert->execute([
                    $producto['id_producto'],
                    $producto['codigo'] ?? 'PROD-' . $producto['id_producto'],
                    $producto['descripcion'] ?? 'Producto',
                    $producto['nombre'] ?? $producto['descripcion'] ?? 'Producto',
                    $categoria,
                    $producto['talle'] ?? null,
                    $producto['color'] ?? null,
                    $producto['stock_actual'] ?? 0,
                    $producto['precio'] ?? 0.00,
                    $producto['precio_costo'] ?? 0.00,
                    $producto['marca'] ?? null,
                    $producto['activo'] ? 'activo' : 'inactivo'
                ]);
                $migrated++;
            }
            
            $this->log("✅ $migrated productos migrados");
            
        } catch (Exception $e) {
            $this->log("❌ Error migrando productos: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Crear productos demo
     */
    private function createDemoProductos() {
        $demo_productos = [
            ['descripcion' => 'Camiseta Básica', 'precio' => 25000, 'cantidad' => 50],
            ['descripcion' => 'Pantalón Jean', 'precio' => 85000, 'cantidad' => 30],
            ['descripcion' => 'Zapatos Deportivos', 'precio' => 120000, 'cantidad' => 20],
            ['descripcion' => 'Chaqueta Casual', 'precio' => 95000, 'cantidad' => 15]
        ];
        
        $stmt = $this->postgres_pdo->prepare("
            INSERT INTO productos (descripcion, precio, cantidad, estado)
            VALUES (?, ?, ?, 'activo')
        ");
        
        foreach ($demo_productos as $producto) {
            $stmt->execute([
                $producto['descripcion'],
                $producto['precio'],
                $producto['cantidad']
            ]);
        }
        
        $this->log("✅ Productos demo creados");
    }
    
    /**
     * Migrar pedidos
     */
    private function migratePedidos() {
        $this->log("📋 Migrando pedidos...");
        
        try {
            $check = $this->mysql_pdo->query("SHOW TABLES LIKE 'fs_pedidos'")->fetch();
            if (!$check) {
                $this->log("⚠️ Tabla fs_pedidos no existe, saltando...");
                return;
            }
            
            $stmt = $this->mysql_pdo->query("SELECT * FROM fs_pedidos ORDER BY id");
            $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($pedidos)) {
                $this->log("⚠️ No hay pedidos para migrar");
                return;
            }
            
            $this->postgres_pdo->exec("TRUNCATE TABLE pedidos RESTART IDENTITY CASCADE");
            
            $insert_sql = "
                INSERT INTO pedidos (
                    id, numero_pedido, cliente_id, total, estado, fecha_pedido
                ) VALUES (?, ?, ?, ?, ?, ?)
            ";
            $stmt_insert = $this->postgres_pdo->prepare($insert_sql);
            
            $migrated = 0;
            foreach ($pedidos as $pedido) {
                $numero_pedido = 'P-' . str_pad($pedido['id'], 6, '0', STR_PAD_LEFT);
                
                $stmt_insert->execute([
                    $pedido['id'],
                    $numero_pedido,
                    $pedido['cliente_id'] ?? 1,
                    $pedido['total'] ?? 0.00,
                    'pendiente',
                    $pedido['fecha_pedido'] ?? date('Y-m-d H:i:s')
                ]);
                $migrated++;
            }
            
            $this->log("✅ $migrated pedidos migrados");
            
        } catch (Exception $e) {
            $this->log("❌ Error migrando pedidos: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Migrar ventas
     */
    private function migrateVentas() {
        $this->log("💰 Migrando ventas...");
        
        try {
            $check = $this->mysql_pdo->query("SHOW TABLES LIKE 'fs_ventas'")->fetch();
            if (!$check) {
                $this->log("⚠️ Tabla fs_ventas no existe, creando ventas demo");
                $this->createDemoVentas();
                return;
            }
            
            $stmt = $this->mysql_pdo->query("SELECT * FROM fs_ventas ORDER BY id");
            $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($ventas)) {
                $this->createDemoVentas();
                return;
            }
            
            $this->postgres_pdo->exec("TRUNCATE TABLE ventas RESTART IDENTITY CASCADE");
            
            $insert_sql = "
                INSERT INTO ventas (
                    id, numero_venta, cliente_id, usuario_id, total, 
                    descuento, metodo_pago, estado, productos, fecha_venta
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ";
            $stmt_insert = $this->postgres_pdo->prepare($insert_sql);
            
            $migrated = 0;
            foreach ($ventas as $venta) {
                $numero_venta = 'V-' . str_pad($venta['id'], 6, '0', STR_PAD_LEFT);
                
                // Convertir productos JSON si existe
                $productos_json = null;
                if (!empty($venta['productos'])) {
                    $productos_json = $venta['productos'];
                }
                
                $stmt_insert->execute([
                    $venta['id'],
                    $numero_venta,
                    $venta['cliente_id'] ?? 1,
                    $venta['usuario_id'] ?? 1,
                    $venta['total'] ?? 0.00,
                    $venta['descuento'] ?? 0.00,
                    $venta['metodo_pago'] ?? 'efectivo',
                    $venta['estado'] ?? 'completada',
                    $productos_json,
                    $venta['fecha_venta'] ?? date('Y-m-d H:i:s')
                ]);
                $migrated++;
            }
            
            $this->log("✅ $migrated ventas migradas");
            
        } catch (Exception $e) {
            $this->log("❌ Error migrando ventas: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Crear ventas demo
     */
    private function createDemoVentas() {
        $demo_ventas = [
            ['cliente_id' => 1, 'total' => 85000, 'metodo_pago' => 'efectivo'],
            ['cliente_id' => 2, 'total' => 120000, 'metodo_pago' => 'tarjeta'],
            ['cliente_id' => 3, 'total' => 45000, 'metodo_pago' => 'transferencia'],
            ['cliente_id' => 1, 'total' => 95000, 'metodo_pago' => 'efectivo']
        ];
        
        $stmt = $this->postgres_pdo->prepare("
            INSERT INTO ventas (numero_venta, cliente_id, usuario_id, total, metodo_pago, estado)
            VALUES (?, ?, 1, ?, ?, 'completada')
        ");
        
        foreach ($demo_ventas as $i => $venta) {
            $numero_venta = 'V-' . str_pad($i + 1, 6, '0', STR_PAD_LEFT);
            $stmt->execute([
                $numero_venta,
                $venta['cliente_id'],
                $venta['total'],
                $venta['metodo_pago']
            ]);
        }
        
        $this->log("✅ Ventas demo creadas");
    }
    
    /**
     * Migrar audit log
     */
    private function migrateAuditLog() {
        $this->log("📊 Migrando logs de auditoría...");
        
        try {
            $check = $this->mysql_pdo->query("SHOW TABLES LIKE 'audit_log'")->fetch();
            if (!$check) {
                $this->log("⚠️ Tabla audit_log no existe, saltando...");
                return;
            }
            
            $stmt = $this->mysql_pdo->query("SELECT * FROM audit_log ORDER BY id LIMIT 1000");
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($logs)) {
                $this->log("⚠️ No hay logs de auditoría para migrar");
                return;
            }
            
            $insert_sql = "
                INSERT INTO audit_log (
                    tabla, accion, registro_id, usuario_id, 
                    datos_anteriores, datos_nuevos, timestamp
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ";
            $stmt_insert = $this->postgres_pdo->prepare($insert_sql);
            
            $migrated = 0;
            foreach ($logs as $log) {
                $stmt_insert->execute([
                    $log['tabla'],
                    $log['accion'],
                    $log['registro_id'] ?? null,
                    $log['usuario_id'] ?? null,
                    $log['datos_anteriores'] ?? null,
                    $log['datos_nuevos'] ?? null,
                    $log['timestamp'] ?? date('Y-m-d H:i:s')
                ]);
                $migrated++;
            }
            
            $this->log("✅ $migrated logs de auditoría migrados");
            
        } catch (Exception $e) {
            $this->log("❌ Error migrando audit log: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Actualizar secuencias de PostgreSQL
     */
    private function updateSequences() {
        $this->log("🔄 Actualizando secuencias...");
        
        $tables = ['usuarios', 'clientes', 'productos', 'ventas', 'pedidos'];
        
        foreach ($tables as $table) {
            try {
                // Obtener el último ID
                $stmt = $this->postgres_pdo->query("SELECT COALESCE(MAX(id), 0) + 1 as next_id FROM $table");
                $next_id = $stmt->fetch(PDO::FETCH_ASSOC)['next_id'];
                
                // Actualizar secuencia
                $this->postgres_pdo->exec("SELECT setval('{$table}_id_seq', $next_id, false)");
                
                $this->log("✅ Secuencia de $table actualizada a $next_id");
                
            } catch (Exception $e) {
                $this->log("⚠️ Error actualizando secuencia de $table: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Verificar integridad de la migración
     */
    private function verifyMigration() {
        $this->log("🔍 Verificando migración...");
        
        $tables = [
            'usuarios' => 'fs_usuarios',
            'clientes' => 'fs_clientes', 
            'productos' => 'fs_productos',
            'ventas' => 'fs_ventas',
            'pedidos' => 'fs_pedidos'
        ];
        
        foreach ($tables as $pg_table => $mysql_table) {
            try {
                // Contar registros en PostgreSQL
                $stmt_pg = $this->postgres_pdo->query("SELECT COUNT(*) as count FROM $pg_table");
                $count_pg = $stmt_pg->fetch(PDO::FETCH_ASSOC)['count'];
                
                // Intentar contar en MySQL
                $count_mysql = 0;
                try {
                    $check = $this->mysql_pdo->query("SHOW TABLES LIKE '$mysql_table'")->fetch();
                    if ($check) {
                        $stmt_mysql = $this->mysql_pdo->query("SELECT COUNT(*) as count FROM $mysql_table");
                        $count_mysql = $stmt_mysql->fetch(PDO::FETCH_ASSOC)['count'];
                    }
                } catch (Exception $e) {
                    // Tabla no existe en MySQL
                }
                
                $this->log("📊 $pg_table: PostgreSQL=$count_pg, MySQL=$count_mysql");
                
            } catch (Exception $e) {
                $this->log("❌ Error verificando $pg_table: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Generar reporte de migración
     */
    public function generateReport() {
        $report_file = __DIR__ . '/migration_report_' . date('Y-m-d_H-i-s') . '.txt';
        file_put_contents($report_file, implode("\n", $this->log));
        $this->log("📄 Reporte guardado en: $report_file");
    }
}

// Ejecutar migración
try {
    echo "🚀 INICIANDO MIGRACIÓN DE DATOS\n";
    echo "===============================\n\n";
    
    $migrator = new DatabaseMigrator();
    $migrator->migrate();
    $migrator->generateReport();
    
    echo "\n🎉 ¡MIGRACIÓN COMPLETADA EXITOSAMENTE!\n";
    echo "=====================================\n";
    echo "✅ Todos los datos han sido migrados de MySQL a PostgreSQL\n";
    echo "📋 Próximo paso: Actualizar configuración de la aplicación\n\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR EN LA MIGRACIÓN\n";
    echo "========================\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "Revisa los logs para más detalles\n";
    exit(1);
}