<?php
namespace App\Models;

require_once __DIR__ . '/BaseModel.php';

class Cliente extends BaseModel {
    protected $table = 'fs_clientes';
    protected $primaryKey = 'id_cliente';
    protected $fillable = [
        'nombre_completo', 'dni', 'telefono', 'email', 'direccion',
        'ciudad', 'provincia', 'codigo_postal', 'es_revendedora', 
        'descuento_porcentaje', 'activo'
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
    public static function buscar($termino, $limite = 20) {
        $cliente = new self();
        $stmt = $cliente->db->prepare("
            SELECT * FROM fs_clientes 
            WHERE (nombre_completo LIKE ? OR dni LIKE ? OR email LIKE ? OR telefono LIKE ?)
            AND activo = 1
            ORDER BY nombre_completo
            LIMIT ?
        ");
        
        $busqueda = "%$termino%";
        $stmt->execute([$busqueda, $busqueda, $busqueda, $busqueda, $limite]);
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
     * Aplicar descuento a un precio
     */
    public function aplicarDescuento($precio) {
        if ($this->descuento_porcentaje > 0) {
            $descuento = ($precio * $this->descuento_porcentaje) / 100;
            return $precio - $descuento;
        }
        return $precio;
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

