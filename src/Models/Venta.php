<?php
namespace App\Models;

require_once __DIR__ . '/BaseModel.php';

class Venta extends BaseModel {
    protected $table = 'fs_ventas';
    protected $primaryKey = 'id_venta';
    protected $fillable = [
        'numero_venta', 'id_cliente', 'id_usuario', 'fecha_venta',
        'subtotal', 'descuento', 'impuestos', 'total', 'estado',
        'metodo_pago', 'notas'
    ];
    
    private $detalles = [];
    
    /**
     * Obtener cliente asociado
     */
    public function getCliente() {
        if ($this->id_cliente) {
            $cliente = new Cliente();
            return $cliente->find($this->id_cliente);
        }
        return null;
    }
    
    /**
     * Obtener usuario que realizó la venta
     */
    public function getUsuario() {
        if ($this->id_usuario) {
            $usuario = new Usuario();
            return $usuario->find($this->id_usuario);
        }
        return null;
    }
    
    /**
     * Obtener detalles de la venta
     */
    public function getDetalles() {
        if (empty($this->detalles) && $this->id_venta) {
            $stmt = $this->db->prepare("
                SELECT vd.*, p.nombre as producto_nombre, p.codigo as producto_codigo
                FROM fs_venta_detalles vd
                JOIN fs_productos p ON vd.id_producto = p.id_producto
                WHERE vd.id_venta = ?
                ORDER BY vd.id_detalle
            ");
            $stmt->execute([$this->id_venta]);
            $this->detalles = $stmt->fetchAll();
        }
        return $this->detalles;
    }
    
    /**
     * Agregar producto a la venta
     */
    public function agregarProducto($idProducto, $cantidad, $precioUnitario, $descuento = 0) {
        $subtotal = ($cantidad * $precioUnitario) - $descuento;
        
        $stmt = $this->db->prepare("
            INSERT INTO fs_venta_detalles (id_venta, id_producto, cantidad, precio_unitario, descuento, subtotal)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        if ($stmt->execute([$this->id_venta, $idProducto, $cantidad, $precioUnitario, $descuento, $subtotal])) {
            // Actualizar stock del producto
            $this->actualizarStock($idProducto, -$cantidad);
            
            // Recalcular totales de la venta
            $this->recalcularTotales();
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Recalcular totales de la venta
     */
    private function recalcularTotales() {
        $stmt = $this->db->prepare("
            SELECT 
                SUM(subtotal) as subtotal_calculado,
                SUM(descuento) as descuento_total
            FROM fs_venta_detalles 
            WHERE id_venta = ?
        ");
        $stmt->execute([$this->id_venta]);
        $totales = $stmt->fetch();
        
        $this->subtotal = $totales['subtotal_calculado'] ?? 0;
        $this->descuento = $totales['descuento_total'] ?? 0;
        
        // Calcular impuestos (19% por defecto)
        $config = require __DIR__ . '/../../config/app.php';
        $tasaImpuesto = $config['ventas']['impuesto_default'] ?? 0.19;
        $this->impuestos = $this->subtotal * $tasaImpuesto;
        
        // Total final
        $this->total = $this->subtotal + $this->impuestos;
        
        // Actualizar en base de datos
        $this->save();
    }
    
    /**
     * Actualizar stock de producto
     */
    private function actualizarStock($idProducto, $cantidad) {
        // Obtener stock actual
        $stmt = $this->db->prepare("SELECT stock_actual FROM fs_productos WHERE id_producto = ?");
        $stmt->execute([$idProducto]);
        $stockActual = $stmt->fetchColumn();
        
        $nuevoStock = $stockActual + $cantidad;
        
        // Actualizar stock
        $stmt = $this->db->prepare("UPDATE fs_productos SET stock_actual = ? WHERE id_producto = ?");
        $stmt->execute([$nuevoStock, $idProducto]);
        
        // Registrar movimiento
        $stmt = $this->db->prepare("
            INSERT INTO fs_movimientos_stock 
            (id_producto, id_usuario, tipo_movimiento, cantidad, stock_anterior, stock_nuevo, motivo, referencia)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $tipoMovimiento = $cantidad > 0 ? 'entrada' : 'salida';
        $motivo = $cantidad > 0 ? 'Devolución de venta' : 'Venta de producto';
        $referencia = "Venta #{$this->numero_venta}";
        
        $stmt->execute([
            $idProducto, 
            $this->id_usuario, 
            $tipoMovimiento, 
            abs($cantidad), 
            $stockActual, 
            $nuevoStock, 
            $motivo, 
            $referencia
        ]);
    }
    
    /**
     * Generar número de venta automático
     */
    public function generarNumeroVenta() {
        $config = require __DIR__ . '/../../config/app.php';
        $prefijo = $config['ventas']['facturacion']['prefijo'] ?? 'FAC';
        $longitud = $config['ventas']['facturacion']['longitud_numero'] ?? 6;
        
        // Obtener último número
        $stmt = $this->db->prepare("
            SELECT numero_venta 
            FROM fs_ventas 
            WHERE numero_venta LIKE ? 
            ORDER BY id_venta DESC 
            LIMIT 1
        ");
        $stmt->execute([$prefijo . '%']);
        $ultimoNumero = $stmt->fetchColumn();
        
        if ($ultimoNumero) {
            $numero = (int)str_replace($prefijo, '', $ultimoNumero) + 1;
        } else {
            $numero = 1;
        }
        
        return $prefijo . str_pad($numero, $longitud, '0', STR_PAD_LEFT);
    }
    
    /**
     * Finalizar venta
     */
    public function finalizar() {
        $this->estado = 'completada';
        $this->fecha_venta = date('Y-m-d H:i:s');
        
        if (!$this->numero_venta) {
            $this->numero_venta = $this->generarNumeroVenta();
        }
        
        return $this->save();
    }
    
    /**
     * Cancelar venta
     */
    public function cancelar($motivo = '') {
        // Restaurar stock de todos los productos
        $detalles = $this->getDetalles();
        foreach ($detalles as $detalle) {
            $this->actualizarStock($detalle['id_producto'], $detalle['cantidad']);
        }
        
        $this->estado = 'cancelada';
        $this->notas = $motivo;
        
        return $this->save();
    }
    
    /**
     * Obtener ventas por período
     */
    public static function getVentasPorPeriodo($fechaInicio, $fechaFin) {
        $venta = new self();
        $stmt = $venta->db->prepare("
            SELECT v.*, c.nombre_completo as cliente_nombre, u.nombre as usuario_nombre
            FROM fs_ventas v
            LEFT JOIN fs_clientes c ON v.id_cliente = c.id_cliente
            LEFT JOIN fs_usuarios u ON v.id_usuario = u.id_usuario
            WHERE DATE(v.fecha_venta) BETWEEN ? AND ?
            ORDER BY v.fecha_venta DESC
        ");
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener estadísticas de ventas
     */
    public static function getEstadisticas($periodo = '30 days') {
        $venta = new self();
        $stmt = $venta->db->prepare("
            SELECT 
                COUNT(*) as total_ventas,
                SUM(total) as total_facturado,
                AVG(total) as promedio_venta,
                COUNT(DISTINCT id_cliente) as clientes_unicos
            FROM fs_ventas 
            WHERE fecha_venta >= DATE_SUB(NOW(), INTERVAL $periodo)
            AND estado = 'completada'
        ");
        $stmt->execute();
        return $stmt->fetch();
    }
}

