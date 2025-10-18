<?php
namespace App\Models;

require_once __DIR__ . '/BaseModel.php';

class Usuario extends BaseModel {
    protected $table = 'fs_usuarios';
    protected $primaryKey = 'id_usuario';
    protected $fillable = [
        'nombre', 'apellido', 'nick', 'password', 'email',
        'is_admin', 'is_medium', 'is_visitor', 'activo'
    ];
    
    /**
     * Verificar si es administrador
     */
    public function esAdmin() {
        return (bool)$this->is_admin;
    }
    
    /**
     * Verificar si es usuario medio
     */
    public function esMedio() {
        return (bool)$this->is_medium;
    }
    
    /**
     * Verificar si es visitante
     */
    public function esVisitante() {
        return (bool)$this->is_visitor;
    }
    
    /**
     * Obtener tipo de usuario
     */
    public function getTipoUsuario() {
        if ($this->esAdmin()) return 'Administrador';
        if ($this->esMedio()) return 'Usuario Medio';
        return 'Visitante';
    }
    
    /**
     * Obtener badge de rol
     */
    public function getRolBadge() {
        if ($this->esAdmin()) {
            return '<span class="badge bg-danger"><i class="fas fa-crown"></i> Administrador</span>';
        } elseif ($this->esMedio()) {
            return '<span class="badge bg-warning text-dark"><i class="fas fa-user-tie"></i> Usuario Medio</span>';
        } else {
            return '<span class="badge bg-secondary"><i class="fas fa-user"></i> Visitante</span>';
        }
    }
    
    /**
     * Obtener nombre completo
     */
    public function getNombreCompleto() {
        return trim($this->nombre . ' ' . $this->apellido);
    }
    
    /**
     * Verificar contraseña
     */
    public function verificarPassword($password) {
        return password_verify($password, $this->password);
    }
    
    /**
     * Cambiar contraseña
     */
    public function cambiarPassword($nuevaPassword) {
        $this->password = password_hash($nuevaPassword, PASSWORD_DEFAULT);
        $this->password_changed_at = date('Y-m-d H:i:s');
        return $this->save();
    }
    
    /**
     * Actualizar último acceso
     */
    public function actualizarUltimoAcceso() {
        $this->ultimo_acceso = date('Y-m-d H:i:s');
        return $this->save();
    }
    
    /**
     * Buscar usuario por nick o email
     */
    public static function buscarPorCredencial($credencial) {
        $usuario = new self();
        $stmt = $usuario->db->prepare("
            SELECT * FROM fs_usuarios 
            WHERE (nick = ? OR email = ?) AND activo = 1
            LIMIT 1
        ");
        $stmt->execute([$credencial, $credencial]);
        $data = $stmt->fetch();
        
        if ($data) {
            $instance = new self();
            $instance->attributes = $data;
            return $instance;
        }
        
        return null;
    }
    
    /**
     * Validar nick único
     */
    public function validarNickUnico($nick, $excluirId = null) {
        $sql = "SELECT COUNT(*) FROM fs_usuarios WHERE nick = ?";
        $params = [$nick];
        
        if ($excluirId) {
            $sql .= " AND id_usuario != ?";
            $params[] = $excluirId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchColumn() == 0;
    }
    
    /**
     * Obtener permisos del usuario
     */
    public function getPermisos() {
        return [
            'ver_dashboard' => true,
            'ver_ventas' => true,
            'crear_ventas' => $this->esMedio() || $this->esAdmin(),
            'ver_clientes' => true,
            'crear_clientes' => $this->esMedio() || $this->esAdmin(),
            'editar_clientes' => $this->esMedio() || $this->esAdmin(),
            'ver_productos' => true,
            'crear_productos' => $this->esAdmin(),
            'editar_productos' => $this->esAdmin(),
            'ver_usuarios' => $this->esAdmin(),
            'crear_usuarios' => $this->esAdmin(),
            'editar_usuarios' => $this->esAdmin(),
            'ver_reportes' => $this->esMedio() || $this->esAdmin(),
            'configuracion' => $this->esAdmin()
        ];
    }
    
    /**
     * Verificar si tiene permiso
     */
    public function tienePermiso($permiso) {
        $permisos = $this->getPermisos();
        return isset($permisos[$permiso]) && $permisos[$permiso];
    }
}

