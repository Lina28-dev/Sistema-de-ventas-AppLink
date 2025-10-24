<?php
namespace App\Models;

require_once __DIR__ . '/BaseModel.php';

class Cliente extends BaseModel {
    protected $table = 'fs_clientes';
    protected $primaryKey = 'id_cliente';
    protected $fillable = [
        'nombre_completo', 'dni', 'telefono', 'email', 'direccion',
        'ciudad', 'provincia', 'codigo_postal', 'es_revendedora', 
        'descuento_porcentaje', 'activo', 'categoria_id', 'segmento'
    ];
    
    /**
     * Verificar si es revendedora
     */
    public function esRevendedora() {
        return (bool)$this->es_revendedora;
    }
    
    /**
     * Obtener descuento formateado
     */
    public function getDescuentoFormateado() {
        return number_format($this->descuento_porcentaje, 2) . '%';
    }
    
    /**
     * Obtener badge de tipo de cliente
     */
    public function getTipoBadge() {
        if ($this->esRevendedora()) {
            return '<span class="badge bg-warning text-dark"><i class="fas fa-store"></i> Revendedora</span>';
        }
        return '<span class="badge bg-primary"><i class="fas fa-user"></i> Cliente</span>';
    }
    
    /**
     * Obtener badge de segmento
     */
    public function getSegmentoBadge() {
        $colores = [
            'VIP' => 'bg-warning',
            'Premium' => 'bg-danger', 
            'Regular' => 'bg-info',
            'Nuevo' => 'bg-success',
            'Inactivo' => 'bg-secondary'
        ];
        
        $iconos = [
            'VIP' => 'fas fa-crown',
            'Premium' => 'fas fa-star',
            'Regular' => 'fas fa-user-check',
            'Nuevo' => 'fas fa-user-plus',
            'Inactivo' => 'fas fa-user-slash'
        ];
        
        $segmento = $this->segmento ?: 'Nuevo';
        $color = $colores[$segmento] ?? 'bg-secondary';
        $icono = $iconos[$segmento] ?? 'fas fa-user';
        
        return '<span class="badge ' . $color . '"><i class="' . $icono . '"></i> ' . $segmento . '</span>';
    }
    
    /**
     * Obtener información de categoría
     */
    public function getCategoria() {
        if (!$this->categoria_id) {
            return null;
        }
        
        $stmt = $this->db->prepare("
            SELECT * FROM categorias_clientes 
            WHERE id = ? AND activo = 1
        ");
        $stmt->execute([$this->categoria_id]);
        return $stmt->fetch();
    }
    
    /**
     * Obtener descuento por categoría
     */
    public function getDescuentoCategoria() {
        $categoria = $this->getCategoria();
        return $categoria ? $categoria['descuento_porcentaje'] : 0;
    }
    
    /**
     * Obtener límite de crédito por categoría
     */
    public function getLimiteCredito() {
        $categoria = $this->getCategoria();
        return $categoria ? $categoria['limite_credito'] : 0;
    }
    
    /**
     * Obtener días de crédito por categoría
     */
    public function getDiasCredito() {
        $categoria = $this->getCategoria();
        return $categoria ? $categoria['dias_credito'] : 0;
    }
    
    /**
     * Obtener historial de ventas del cliente
     */
    public function getHistorialVentas($limite = 10) {
        $stmt = $this->db->prepare("
            SELECT v.*, u.nombre as vendedor
            FROM fs_ventas v
            LEFT JOIN fs_usuarios u ON v.id_usuario = u.id_usuario
            WHERE v.id_cliente = ?
            ORDER BY v.fecha_venta DESC
            LIMIT ?
        ");
        $stmt->execute([$this->id_cliente, $limite]);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener estadísticas del cliente
     */
    public function getEstadisticas() {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_compras,
                SUM(total) as total_gastado,
                AVG(total) as promedio_compra,
                MAX(fecha_venta) as ultima_compra
            FROM fs_ventas 
            WHERE id_cliente = ? AND estado = 'completada'
        ");
        $stmt->execute([$this->id_cliente]);
        return $stmt->fetch();
    }
    
    /**
     * Buscar clientes por término
     */
    public static function buscar($termino, $limite = 20, $categoria_id = null, $segmento = null) {
        $cliente = new self();
        
        $sql = "
            SELECT c.*, cat.nombre as categoria_nombre, cat.color as categoria_color 
            FROM fs_clientes c
            LEFT JOIN categorias_clientes cat ON c.categoria_id = cat.id
            WHERE (c.nombre_completo LIKE ? OR c.dni LIKE ? OR c.email LIKE ? OR c.telefono LIKE ?)
            AND c.activo = 1";
        
        $params = ["%$termino%", "%$termino%", "%$termino%", "%$termino%"];
        
        if ($categoria_id) {
            $sql .= " AND c.categoria_id = ?";
            $params[] = $categoria_id;
        }
        
        if ($segmento) {
            $sql .= " AND c.segmento = ?";
            $params[] = $segmento;
        }
        
        $sql .= " ORDER BY c.nombre_completo LIMIT ?";
        $params[] = $limite;
        
        $stmt = $cliente->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener clientes por categoría
     */
    public static function obtenerPorCategoria($categoria_id, $limite = null) {
        $cliente = new self();
        
        $sql = "
            SELECT c.*, cat.nombre as categoria_nombre, cat.color as categoria_color
            FROM fs_clientes c
            LEFT JOIN categorias_clientes cat ON c.categoria_id = cat.id
            WHERE c.categoria_id = ? AND c.activo = 1
            ORDER BY c.nombre_completo";
        
        if ($limite) {
            $sql .= " LIMIT ?";
        }
        
        $stmt = $cliente->db->prepare($sql);
        $params = [$categoria_id];
        
        if ($limite) {
            $params[] = $limite;
        }
        
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener clientes por segmento
     */
    public static function obtenerPorSegmento($segmento, $limite = null) {
        $cliente = new self();
        
        $sql = "
            SELECT c.*, cat.nombre as categoria_nombre, cat.color as categoria_color
            FROM fs_clientes c
            LEFT JOIN categorias_clientes cat ON c.categoria_id = cat.id
            WHERE c.segmento = ? AND c.activo = 1
            ORDER BY c.nombre_completo";
        
        if ($limite) {
            $sql .= " LIMIT ?";
        }
        
        $stmt = $cliente->db->prepare($sql);
        $params = [$segmento];
        
        if ($limite) {
            $params[] = $limite;
        }
        
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Validar email único
     */
    public function validarEmailUnico($email, $excluirId = null) {
        $sql = "SELECT COUNT(*) FROM fs_clientes WHERE email = ?";
        $params = [$email];
        
        if ($excluirId) {
            $sql .= " AND id_cliente != ?";
            $params[] = $excluirId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchColumn() == 0;
    }
    
    /**
     * Validar DNI único
     */
    public function validarDniUnico($dni, $excluirId = null) {
        if (empty($dni)) return true; // DNI es opcional
        
        $sql = "SELECT COUNT(*) FROM fs_clientes WHERE dni = ?";
        $params = [$dni];
        
        if ($excluirId) {
            $sql .= " AND id_cliente != ?";
            $params[] = $excluirId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchColumn() == 0;
    }
    
    /**
     * Aplicar descuento a un precio (incluye descuento de categoría)
     */
    public function aplicarDescuento($precio) {
        $descuento_individual = $this->descuento_porcentaje > 0 ? $this->descuento_porcentaje : 0;
        $descuento_categoria = $this->getDescuentoCategoria();
        
        // Usar el mayor descuento entre individual y categoría
        $descuento_total = max($descuento_individual, $descuento_categoria);
        
        if ($descuento_total > 0) {
            $descuento = ($precio * $descuento_total) / 100;
            return $precio - $descuento;
        }
        return $precio;
    }
    
    /**
     * Actualizar segmento basado en historial de compras
     */
    public function actualizarSegmento() {
        $estadisticas = $this->getEstadisticas();
        
        if (!$estadisticas) {
            $this->segmento = 'Nuevo';
            return $this->save();
        }
        
        $total_gastado = $estadisticas['total_gastado'] ?: 0;
        $total_compras = $estadisticas['total_compras'] ?: 0;
        $ultima_compra = $estadisticas['ultima_compra'];
        
        // Verificar si es inactivo (más de 6 meses sin comprar)
        if ($ultima_compra) {
            $fecha_limite = date('Y-m-d', strtotime('-6 months'));
            if ($ultima_compra < $fecha_limite) {
                $this->segmento = 'Inactivo';
                return $this->save();
            }
        }
        
        // Clasificar por total gastado
        if ($total_gastado >= 1000000 && $total_compras >= 10) {
            $this->segmento = 'VIP';
        } elseif ($total_gastado >= 500000 && $total_compras >= 5) {
            $this->segmento = 'Premium';
        } elseif ($total_compras >= 3) {
            $this->segmento = 'Regular';
        } else {
            $this->segmento = 'Nuevo';
        }
        
        return $this->save();
    }
    
    /**
     * Obtener clientes revendedoras
     */
    public static function getRevendedoras() {
        $cliente = new self();
        return $cliente->all(['es_revendedora' => 1, 'activo' => 1]);
    }
    
    /**
     * Obtener dirección completa formateada
     */
    public function getDireccionCompleta() {
        $partes = array_filter([
            $this->direccion,
            $this->ciudad,
            $this->provincia,
            $this->codigo_postal
        ]);
        
        return implode(', ', $partes);
    }
    
    /**
     * Marcar como inactivo (soft delete)
     */
    public function desactivar() {
        $this->activo = 0;
        return $this->save();
    }
    
    /**
     * Reactivar cliente
     */
    public function activar() {
        $this->activo = 1;
        return $this->save();
    }
    
    /**
     * Validar datos del cliente
     */
    public function validar() {
        $errores = [];
        
        if (empty($this->nombre_completo)) {
            $errores[] = 'El nombre es requerido';
        }
        
        if (!empty($this->email) && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El email no es válido';
        }
        
        if (!empty($this->email) && !$this->validarEmailUnico($this->email, $this->id_cliente)) {
            $errores[] = 'El email ya está registrado';
        }
        
        if (!empty($this->dni) && !$this->validarDniUnico($this->dni, $this->id_cliente)) {
            $errores[] = 'El DNI ya está registrado';
        }
        
        if ($this->descuento_porcentaje < 0 || $this->descuento_porcentaje > 100) {
            $errores[] = 'El descuento debe estar entre 0 y 100%';
        }
        
        return $errores;
    }
}

