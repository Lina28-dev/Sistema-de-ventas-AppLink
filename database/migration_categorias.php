<?php
/**
 * Migración: Sistema de Categorías - Fase 1
 * Crear tablas para categorización de productos, clientes y tipos de venta
 */

require_once __DIR__ . '/../src/Utils/Database.php';

class MigracionCategorias {
    private $conn;
    
    public function __construct() {
        $database = Database::getInstance();
        $this->conn = $database->getConnection();
    }
    
    public function ejecutar() {
        try {
            $this->conn->beginTransaction();
            
            echo "🚀 Iniciando migración del sistema de categorías...\n";
            
            // Crear tabla de categorías de productos
            $this->crearTablaCategoriaProductos();
            
            // Crear tabla de categorías de clientes
            $this->crearTablaCategoriaClientes();
            
            // Crear tabla de tipos de venta
            $this->crearTablaTiposVenta();
            
            // Modificar tabla de productos
            $this->modificarTablaProductos();
            
            // Modificar tabla de clientes
            $this->modificarTablaClientes();
            
            // Modificar tabla de ventas
            $this->modificarTablaVentas();
            
            // Insertar datos iniciales
            $this->insertarDatosIniciales();
            
            $this->conn->commit();
            echo "✅ Migración completada exitosamente!\n";
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            echo "❌ Error en la migración: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
    
    private function crearTablaCategoriaProductos() {
        $sql = "CREATE TABLE IF NOT EXISTS categorias_productos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL UNIQUE,
            descripcion TEXT,
            color VARCHAR(7) DEFAULT '#FF1493',
            icono VARCHAR(50) DEFAULT 'fas fa-tag',
            activo TINYINT(1) DEFAULT 1,
            orden INT DEFAULT 0,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_activo (activo),
            INDEX idx_orden (orden)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->conn->exec($sql);
        echo "✓ Tabla categorias_productos creada\n";
    }
    
    private function crearTablaCategoriaClientes() {
        $sql = "CREATE TABLE IF NOT EXISTS categorias_clientes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL UNIQUE,
            descripcion TEXT,
            color VARCHAR(7) DEFAULT '#28a745',
            descuento_porcentaje DECIMAL(5,2) DEFAULT 0.00,
            limite_credito DECIMAL(15,2) DEFAULT 0.00,
            dias_credito INT DEFAULT 0,
            activo TINYINT(1) DEFAULT 1,
            orden INT DEFAULT 0,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_activo (activo),
            INDEX idx_orden (orden)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->conn->exec($sql);
        echo "✓ Tabla categorias_clientes creada\n";
    }
    
    private function crearTablaTiposVenta() {
        $sql = "CREATE TABLE IF NOT EXISTS tipos_venta (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL UNIQUE,
            descripcion TEXT,
            color VARCHAR(7) DEFAULT '#17a2b8',
            permite_credito TINYINT(1) DEFAULT 0,
            requiere_descuento TINYINT(1) DEFAULT 0,
            activo TINYINT(1) DEFAULT 1,
            orden INT DEFAULT 0,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_activo (activo),
            INDEX idx_orden (orden)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->conn->exec($sql);
        echo "✓ Tabla tipos_venta creada\n";
    }
    
    private function modificarTablaProductos() {
        // Verificar si la columna categoria_id ya existe
        $sql = "SHOW COLUMNS FROM fs_productos LIKE 'categoria_id'";
        $stmt = $this->conn->query($sql);
        
        if ($stmt->rowCount() == 0) {
            $sql = "ALTER TABLE fs_productos 
                    ADD COLUMN categoria_id INT NULL,
                    ADD INDEX idx_categoria_id (categoria_id),
                    ADD FOREIGN KEY fk_producto_categoria (categoria_id) 
                        REFERENCES categorias_productos(id) ON DELETE SET NULL";
            
            $this->conn->exec($sql);
            echo "✓ Tabla fs_productos modificada - agregada categoria_id\n";
        } else {
            echo "✓ Tabla fs_productos ya tiene categoria_id\n";
        }
    }
    
    private function modificarTablaClientes() {
        // Verificar si las columnas ya existen
        $columns = ['categoria_id', 'segmento'];
        $existingColumns = [];
        
        foreach ($columns as $column) {
            $sql = "SHOW COLUMNS FROM fs_clientes LIKE '$column'";
            $stmt = $this->conn->query($sql);
            if ($stmt->rowCount() > 0) {
                $existingColumns[] = $column;
            }
        }
        
        $alterSql = "ALTER TABLE fs_clientes ";
        $modifications = [];
        
        if (!in_array('categoria_id', $existingColumns)) {
            $modifications[] = "ADD COLUMN categoria_id INT NULL";
        }
        
        if (!in_array('segmento', $existingColumns)) {
            $modifications[] = "ADD COLUMN segmento ENUM('VIP', 'Regular', 'Nuevo', 'Inactivo') DEFAULT 'Nuevo'";
        }
        
        if (!empty($modifications)) {
            $alterSql .= implode(', ', $modifications);
            
            if (!in_array('categoria_id', $existingColumns)) {
                $alterSql .= ", ADD INDEX idx_categoria_cliente (categoria_id)";
                $alterSql .= ", ADD FOREIGN KEY fk_cliente_categoria (categoria_id) 
                              REFERENCES categorias_clientes(id) ON DELETE SET NULL";
            }
            
            $alterSql .= ", ADD INDEX idx_segmento (segmento)";
            
            $this->conn->exec($alterSql);
            echo "✓ Tabla fs_clientes modificada\n";
        } else {
            echo "✓ Tabla fs_clientes ya tiene las columnas necesarias\n";
        }
    }
    
    private function modificarTablaVentas() {
        // Verificar si la columna tipo_venta_id ya existe
        $sql = "SHOW COLUMNS FROM fs_ventas LIKE 'tipo_venta_id'";
        $stmt = $this->conn->query($sql);
        
        if ($stmt->rowCount() == 0) {
            $sql = "ALTER TABLE fs_ventas 
                    ADD COLUMN tipo_venta_id INT NULL,
                    ADD COLUMN descuento_aplicado DECIMAL(5,2) DEFAULT 0.00,
                    ADD INDEX idx_tipo_venta (tipo_venta_id),
                    ADD FOREIGN KEY fk_venta_tipo (tipo_venta_id) 
                        REFERENCES tipos_venta(id) ON DELETE SET NULL";
            
            $this->conn->exec($sql);
            echo "✓ Tabla fs_ventas modificada\n";
        } else {
            echo "✓ Tabla fs_ventas ya tiene tipo_venta_id\n";
        }
    }
    
    private function insertarDatosIniciales() {
        // Categorías de productos para lencería
        $categoriasProductos = [
            ['nombre' => 'Lencería Íntima', 'descripcion' => 'Brasieres, panties y conjuntos íntimos', 'color' => '#FF1493', 'icono' => 'fas fa-heart', 'orden' => 1],
            ['nombre' => 'Ropa Interior Deportiva', 'descripcion' => 'Brasieres deportivos y ropa interior activa', 'color' => '#28a745', 'icono' => 'fas fa-running', 'orden' => 2],
            ['nombre' => 'Pijamas y Camisones', 'descripcion' => 'Ropa de dormir y loungewear', 'color' => '#6f42c1', 'icono' => 'fas fa-moon', 'orden' => 3],
            ['nombre' => 'Accesorios', 'descripcion' => 'Tirantes, almohadillas y accesorios de lencería', 'color' => '#fd7e14', 'icono' => 'fas fa-gems', 'orden' => 4],
            ['nombre' => 'Ropa de Baño', 'descripcion' => 'Bikinis, trajes de baño y coverups', 'color' => '#20c997', 'icono' => 'fas fa-swimmer', 'orden' => 5]
        ];
        
        foreach ($categoriasProductos as $categoria) {
            $sql = "INSERT IGNORE INTO categorias_productos (nombre, descripcion, color, icono, orden) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                $categoria['nombre'], 
                $categoria['descripcion'], 
                $categoria['color'], 
                $categoria['icono'], 
                $categoria['orden']
            ]);
        }
        echo "✓ Categorías de productos insertadas\n";
        
        // Categorías de clientes
        $categoriasClientes = [
            ['nombre' => 'VIP', 'descripcion' => 'Clientes de alto valor con beneficios especiales', 'color' => '#ffc107', 'descuento_porcentaje' => 15.00, 'limite_credito' => 500000.00, 'dias_credito' => 30, 'orden' => 1],
            ['nombre' => 'Premium', 'descripcion' => 'Clientes frecuentes con descuentos preferenciales', 'color' => '#e83e8c', 'descuento_porcentaje' => 10.00, 'limite_credito' => 300000.00, 'dias_credito' => 15, 'orden' => 2],
            ['nombre' => 'Regular', 'descripcion' => 'Clientes habituales del negocio', 'color' => '#17a2b8', 'descuento_porcentaje' => 5.00, 'limite_credito' => 150000.00, 'dias_credito' => 0, 'orden' => 3],
            ['nombre' => 'Nuevo', 'descripcion' => 'Clientes recién incorporados', 'color' => '#28a745', 'descuento_porcentaje' => 0.00, 'limite_credito' => 50000.00, 'dias_credito' => 0, 'orden' => 4],
            ['nombre' => 'Mayorista', 'descripcion' => 'Clientes que compran al por mayor', 'color' => '#6610f2', 'descuento_porcentaje' => 20.00, 'limite_credito' => 1000000.00, 'dias_credito' => 45, 'orden' => 5]
        ];
        
        foreach ($categoriasClientes as $categoria) {
            $sql = "INSERT IGNORE INTO categorias_clientes (nombre, descripcion, color, descuento_porcentaje, limite_credito, dias_credito, orden) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                $categoria['nombre'], 
                $categoria['descripcion'], 
                $categoria['color'], 
                $categoria['descuento_porcentaje'], 
                $categoria['limite_credito'], 
                $categoria['dias_credito'], 
                $categoria['orden']
            ]);
        }
        echo "✓ Categorías de clientes insertadas\n";
        
        // Tipos de venta
        $tiposVenta = [
            ['nombre' => 'Contado', 'descripcion' => 'Venta pagada inmediatamente', 'color' => '#28a745', 'permite_credito' => 0, 'requiere_descuento' => 0, 'orden' => 1],
            ['nombre' => 'Crédito', 'descripcion' => 'Venta a plazo según límite del cliente', 'color' => '#ffc107', 'permite_credito' => 1, 'requiere_descuento' => 0, 'orden' => 2],
            ['nombre' => 'Mayorista', 'descripcion' => 'Venta al por mayor con descuentos especiales', 'color' => '#6610f2', 'permite_credito' => 1, 'requiere_descuento' => 1, 'orden' => 3],
            ['nombre' => 'Promocional', 'descripcion' => 'Venta con descuentos promocionales', 'color' => '#e83e8c', 'permite_credito' => 0, 'requiere_descuento' => 1, 'orden' => 4],
            ['nombre' => 'Liquidación', 'descripcion' => 'Venta de productos en liquidación', 'color' => '#dc3545', 'permite_credito' => 0, 'requiere_descuento' => 1, 'orden' => 5]
        ];
        
        foreach ($tiposVenta as $tipo) {
            $sql = "INSERT IGNORE INTO tipos_venta (nombre, descripcion, color, permite_credito, requiere_descuento, orden) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                $tipo['nombre'], 
                $tipo['descripcion'], 
                $tipo['color'], 
                $tipo['permite_credito'], 
                $tipo['requiere_descuento'], 
                $tipo['orden']
            ]);
        }
        echo "✓ Tipos de venta insertados\n";
    }
    
    public function rollback() {
        try {
            $this->conn->beginTransaction();
            
            echo "🔄 Revirtiendo migración...\n";
            
            // Eliminar foreign keys y columnas agregadas
            $this->conn->exec("ALTER TABLE fs_productos DROP FOREIGN KEY IF EXISTS fk_producto_categoria");
            $this->conn->exec("ALTER TABLE fs_productos DROP COLUMN IF EXISTS categoria_id");
            
            $this->conn->exec("ALTER TABLE fs_clientes DROP FOREIGN KEY IF EXISTS fk_cliente_categoria");
            $this->conn->exec("ALTER TABLE fs_clientes DROP COLUMN IF EXISTS categoria_id");
            $this->conn->exec("ALTER TABLE fs_clientes DROP COLUMN IF EXISTS segmento");
            
            $this->conn->exec("ALTER TABLE fs_ventas DROP FOREIGN KEY IF EXISTS fk_venta_tipo");
            $this->conn->exec("ALTER TABLE fs_ventas DROP COLUMN IF EXISTS tipo_venta_id");
            $this->conn->exec("ALTER TABLE fs_ventas DROP COLUMN IF EXISTS descuento_aplicado");
            
            // Eliminar tablas
            $this->conn->exec("DROP TABLE IF EXISTS categorias_productos");
            $this->conn->exec("DROP TABLE IF EXISTS categorias_clientes");
            $this->conn->exec("DROP TABLE IF EXISTS tipos_venta");
            
            $this->conn->commit();
            echo "✅ Rollback completado\n";
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            echo "❌ Error en rollback: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
}

// Ejecutar migración si se llama directamente
if (basename(__FILE__) == basename($_SERVER["SCRIPT_NAME"])) {
    $migration = new MigracionCategorias();
    
    // Verificar parámetros de línea de comandos
    if (isset($argv[1]) && $argv[1] === 'rollback') {
        $migration->rollback();
    } else {
        $migration->ejecutar();
    }
}
?>