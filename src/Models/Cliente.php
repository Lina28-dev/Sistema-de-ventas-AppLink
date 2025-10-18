<?php
class Cliente {
    public $id;
    public $nombre_completo;
    public $tipo_identificacion;
    public $numero_identificacion;
    public $telefono;
    public $email;
    public $direccion;
    public $provincia;
    public $localidad;
    public $codigo_postal;
    public $revendedora = 0;
    public $descuento = 0;
    public $fecha_creacion;
    public $fecha_actualizacion;
    
    public function esRevendedor() {
        return $this->revendedora == 1;
    }
    
    public function getDescuentoFormateado() {
        return $this->descuento . '%';
    }
    
    public function getTipoBadge() {
        return $this->esRevendedor() ? 
            '<span class="badge-revendedor"><i class="fas fa-store"></i> Revendedora</span>' :
            '<span class="badge-cliente"><i class="fas fa-user"></i> Cliente</span>';
    }
}
