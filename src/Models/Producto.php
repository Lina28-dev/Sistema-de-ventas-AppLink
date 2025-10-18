<?php
namespace App\Models;

require_once __DIR__ . '/BaseModel.php';

class Producto extends BaseModel {
    protected $table = 'fs_productos';
    protected $primaryKey = 'id_producto';
    protected $fillable = [
        'codigo', 'nombre', 'descripcion', 'id_categoria', 'precio',
        'precio_costo', 'stock_actual', 'stock_minimo', 'talle',
        'color', 'marca', 'modelo', 'activo'
    ];
    
    /**
     * Obtener categoría del producto
     */
    public function getCategoria() {
        if ($this->id_categoria) {
            $stmt = $this->db->prepare("SELECT * FROM fs_categorias WHERE id_categoria = ?");
            $stmt->execute([$this->id_categoria]);
            return $stmt->fetch();
        }
        return null;
    }
    
    /**
     * Verificar si tiene stock suficiente
     */
    public function tieneStock($cantidad = 1) {
        return $this->stock_actual >= $cantidad;
    }
    
    /**
     * Verificar si está en stock mínimo
     */
    public function enStockMinimo() {
        return $this->stock_actual <= $this->stock_minimo;
    }
    
    /**
     * Obtener badge de estado de stock
     */
    public function getStockBadge() {
        if ($this->stock_actual <= 0) {
            return '<span class="badge bg-danger">Sin Stock</span>';
        } elseif ($this->enStockMinimo()) {
            return '<span class="badge bg-warning text-dark">Stock Bajo</span>';
        } else {
            return '<span class="badge bg-success">En Stock</span>';
        }
    }
    
    /**
     * Formatear precio
     */
    public function getPrecioFormateado() {
        $config = require __DIR__ . '/../../config/app.php';
        $simbolo = $config['ventas']['simbolo_moneda'] ?? '$';
        $decimales = $config['ventas']['decimales'] ?? 2;
        
        return $simbolo . number_format($this->precio, $decimales);
    }
    
    /**
     * Calcular margen de ganancia
     */
    public function getMargenGanancia() {
        if ($this->precio_costo > 0) {
            $margen = (($this->precio - $this->precio_costo) / $this->precio_costo) * 100;
            return round($margen, 2);
        }
        return 0;
    }
    
    /**
     * Actualizar stock
     */
    public function actualizarStock($cantidad, $tipo = 'manual', $motivo = '', $idUsuario = null) {
        $stockAnterior = $this->stock_actual;
        $this->stock_actual = max(0, $this->stock_actual + $cantidad);
        
        if ($this->save()) {
            // Registrar movimiento de stock
            $stmt = $this->db->prepare("
                INSERT INTO fs_movimientos_stock 
                (id_producto, id_usuario, tipo_movimiento, cantidad, stock_anterior, stock_nuevo, motivo)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $tipoMovimiento = $cantidad > 0 ? 'entrada' : 'salida';
            if ($tipo === 'ajuste') $tipoMovimiento = 'ajuste';
            
            $stmt->execute([
                $this->id_producto,
                $idUsuario,
                $tipoMovimiento,
                abs($cantidad),
                $stockAnterior,
                $this->stock_actual,
                $motivo
            ]);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Obtener historial de movimientos
     */
    public function getHistorialMovimientos($limite = 20) {
        $stmt = $this->db->prepare("
            SELECT m.*, u.nombre as usuario_nombre
            FROM fs_movimientos_stock m
            LEFT JOIN fs_usuarios u ON m.id_usuario = u.id_usuario
            WHERE m.id_producto = ?
            ORDER BY m.fecha DESC
            LIMIT ?
        ");
        $stmt->execute([$this->id_producto, $limite]);
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar productos
     */
    public static function buscar($termino, $categoria = null, $limite = 20) {
        $producto = new self();
        $sql = "
            SELECT p.*, c.nombre as categoria_nombre
            FROM fs_productos p
            LEFT JOIN fs_categorias c ON p.id_categoria = c.id_categoria
            WHERE p.activo = 1
            AND (p.nombre LIKE ? OR p.codigo LIKE ? OR p.descripcion LIKE ?)
        ";
        
        $params = ["%$termino%", "%$termino%", "%$termino%"];
        
        if ($categoria) {
            $sql .= " AND p.id_categoria = ?";
            $params[] = $categoria;
        }
        
        $sql .= " ORDER BY p.nombre LIMIT ?";
        $params[] = $limite;
        
        $stmt = $producto->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener productos con stock bajo
     */
    public static function getStockBajo() {
        $producto = new self();
        $stmt = $producto->db->prepare("
            SELECT * FROM fs_productos 
            WHERE stock_actual <= stock_minimo 
            AND activo = 1
            ORDER BY stock_actual ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener productos más vendidos
     */
    public static function getMasVendidos($limite = 10) {
        $producto = new self();
        $stmt = $producto->db->prepare("
            SELECT 
                p.*,
                SUM(vd.cantidad) as total_vendido,
                COUNT(vd.id_detalle) as veces_vendido
            FROM fs_productos p
            INNER JOIN fs_venta_detalles vd ON p.id_producto = vd.id_producto
            INNER JOIN fs_ventas v ON vd.id_venta = v.id_venta
            WHERE v.estado = 'completada'
            AND v.fecha_venta >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY p.id_producto
            ORDER BY total_vendido DESC
            LIMIT ?
        ");
        $stmt->execute([$limite]);
        return $stmt->fetchAll();
    }
    
    /**
     * Generar código automático
     */
    public function generarCodigo() {
        // Buscar el último código numérico
        $stmt = $this->db->prepare("
            SELECT codigo FROM fs_productos 
            WHERE codigo REGEXP '^[0-9]+$'
            ORDER BY CAST(codigo AS UNSIGNED) DESC 
            LIMIT 1
        ");
        $stmt->execute();
        $ultimoCodigo = $stmt->fetchColumn();
        
        if ($ultimoCodigo) {
            $nuevoCodigo = (int)$ultimoCodigo + 1;
        } else {
            $nuevoCodigo = 1001; // Empezar desde 1001
        }
        
        return str_pad($nuevoCodigo, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Validar código único
     */
    public function validarCodigoUnico($codigo, $excluirId = null) {
        $sql = "SELECT COUNT(*) FROM fs_productos WHERE codigo = ?";
        $params = [$codigo];
        
        if ($excluirId) {
            $sql .= " AND id_producto != ?";
            $params[] = $excluirId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchColumn() == 0;
    }
    
    /**
     * Obtener estadísticas del producto
     */
    public function getEstadisticas() {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(vd.id_detalle) as total_ventas,
                SUM(vd.cantidad) as cantidad_vendida,
                SUM(vd.subtotal) as ingresos_generados,
                AVG(vd.precio_unitario) as precio_promedio_venta
            FROM fs_venta_detalles vd
            INNER JOIN fs_ventas v ON vd.id_venta = v.id_venta
            WHERE vd.id_producto = ? AND v.estado = 'completada'
        ");
        $stmt->execute([$this->id_producto]);
        return $stmt->fetch();
    }
    
    /**
     * Validar datos del producto
     */
    public function validar() {
        $errores = [];
        
        if (empty($this->nombre)) {
            $errores[] = 'El nombre es requerido';
        }
        
        if (empty($this->codigo)) {
            $this->codigo = $this->generarCodigo();
        }
        
        if (!$this->validarCodigoUnico($this->codigo, $this->id_producto)) {
            $errores[] = 'El código ya está en uso';
        }
        
        if ($this->precio <= 0) {
            $errores[] = 'El precio debe ser mayor a 0';
        }
        
        if ($this->stock_actual < 0) {
            $errores[] = 'El stock no puede ser negativo';
        }
        
        return $errores;
    }
}

