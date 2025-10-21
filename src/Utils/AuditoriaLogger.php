<?php
/**
 * Clase AuditoriaLogger - Registra automáticamente la actividad del usuario
 */

class AuditoriaLogger {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Registrar login de usuario
     */
    public function registrarLogin($usuario_id, $usuario_nombre, $exitoso = true) {
        try {
            $accion = $exitoso ? 'LOGIN' : 'LOGIN_FAILED';
            $ip = $this->obtenerIP();
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido';
            
            $stmt = $this->pdo->prepare("
                INSERT INTO auditoria_sesiones 
                (usuario_id, usuario_nombre, accion, ip_address, user_agent, detalles) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $detalles = json_encode([
                'timestamp' => date('Y-m-d H:i:s'),
                'navegador' => $this->obtenerNavegador($user_agent),
                'dispositivo' => $this->obtenerDispositivo($user_agent)
            ]);
            
            $stmt->execute([$usuario_id, $usuario_nombre, $accion, $ip, $user_agent, $detalles]);
            
            // Actualizar métricas diarias
            if ($exitoso) {
                $this->actualizarMetricasDiarias('logins_exitosos', 1);
            } else {
                $this->actualizarMetricasDiarias('logins_fallidos', 1);
            }
            
            return true;
        } catch (PDOException $e) {
            error_log("Error registrando login: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Registrar logout de usuario
     */
    public function registrarLogout($usuario_id, $usuario_nombre, $duracion_sesion = null) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO auditoria_sesiones 
                (usuario_id, usuario_nombre, accion, ip_address, user_agent, duracion_sesion) 
                VALUES (?, ?, 'LOGOUT', ?, ?, ?)
            ");
            
            $ip = $this->obtenerIP();
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido';
            
            $stmt->execute([$usuario_id, $usuario_nombre, $ip, $user_agent, $duracion_sesion]);
            
            return true;
        } catch (PDOException $e) {
            error_log("Error registrando logout: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Registrar cambio manual (para acciones específicas)
     */
    public function registrarActividad($tabla, $accion, $registro_id, $datos_anteriores = null, $datos_nuevos = null, $observaciones = null) {
        try {
            $usuario_id = $_SESSION['user_id'] ?? null;
            $usuario_nombre = $_SESSION['user_name'] ?? 'Sistema';
            $ip = $this->obtenerIP();
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido';
            
            $stmt = $this->pdo->prepare("
                INSERT INTO auditoria_general 
                (tabla, accion, registro_id, datos_anteriores, datos_nuevos, usuario_id, usuario_nombre, ip_address, user_agent, observaciones) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $tabla, 
                $accion, 
                $registro_id, 
                $datos_anteriores ? json_encode($datos_anteriores) : null,
                $datos_nuevos ? json_encode($datos_nuevos) : null,
                $usuario_id, 
                $usuario_nombre, 
                $ip, 
                $user_agent, 
                $observaciones
            ]);
            
            return true;
        } catch (PDOException $e) {
            error_log("Error registrando actividad: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar métricas diarias
     */
    private function actualizarMetricasDiarias($campo, $incremento) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO metricas_diarias (fecha, $campo) 
                VALUES (CURDATE(), ?)
                ON DUPLICATE KEY UPDATE 
                    $campo = $campo + ?,
                    updated_at = NOW()
            ");
            
            $stmt->execute([$incremento, $incremento]);
        } catch (PDOException $e) {
            error_log("Error actualizando métricas: " . $e->getMessage());
        }
    }
    
    /**
     * Obtener IP real del usuario
     */
    private function obtenerIP() {
        $ip_keys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'Desconocida';
    }
    
    /**
     * Detectar navegador
     */
    private function obtenerNavegador($user_agent) {
        if (strpos($user_agent, 'Chrome') !== false) return 'Chrome';
        if (strpos($user_agent, 'Firefox') !== false) return 'Firefox';
        if (strpos($user_agent, 'Safari') !== false) return 'Safari';
        if (strpos($user_agent, 'Edge') !== false) return 'Edge';
        if (strpos($user_agent, 'Opera') !== false) return 'Opera';
        return 'Desconocido';
    }
    
    /**
     * Detectar dispositivo
     */
    private function obtenerDispositivo($user_agent) {
        if (strpos($user_agent, 'Mobile') !== false) return 'Móvil';
        if (strpos($user_agent, 'Tablet') !== false) return 'Tablet';
        return 'Escritorio';
    }
    
    /**
     * Obtener resumen de actividad del usuario
     */
    public function obtenerResumenUsuario($usuario_id, $dias = 7) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    accion,
                    COUNT(*) as total,
                    MAX(fecha_hora) as ultima_vez
                FROM auditoria_sesiones 
                WHERE usuario_id = ? 
                AND fecha_hora >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY accion
                ORDER BY total DESC
            ");
            
            $stmt->execute([$usuario_id, $dias]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error obteniendo resumen: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener estadísticas generales
     */
    public function obtenerEstadisticas() {
        try {
            $stats = [];
            
            // Total de registros de auditoría
            $stats['total_registros'] = $this->pdo->query("SELECT COUNT(*) FROM auditoria_general")->fetchColumn();
            
            // Registros de hoy
            $stats['registros_hoy'] = $this->pdo->query("
                SELECT COUNT(*) FROM auditoria_general 
                WHERE DATE(fecha_hora) = CURDATE()
            ")->fetchColumn();
            
            // Usuarios más activos
            $stmt = $this->pdo->query("
                SELECT usuario_nombre, COUNT(*) as actividades
                FROM auditoria_general 
                WHERE fecha_hora >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                GROUP BY usuario_nombre 
                ORDER BY actividades DESC 
                LIMIT 5
            ");
            $stats['usuarios_activos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Tablas más modificadas
            $stmt = $this->pdo->query("
                SELECT tabla, COUNT(*) as modificaciones
                FROM auditoria_general 
                WHERE fecha_hora >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                GROUP BY tabla 
                ORDER BY modificaciones DESC 
                LIMIT 5
            ");
            $stats['tablas_activas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $stats;
        } catch (PDOException $e) {
            error_log("Error obteniendo estadísticas: " . $e->getMessage());
            return [];
        }
    }
}

/**
 * Función global para facilitar el registro de auditoría
 */
function registrar_auditoria($pdo, $tabla, $accion, $registro_id, $datos_anteriores = null, $datos_nuevos = null, $observaciones = null) {
    $logger = new AuditoriaLogger($pdo);
    return $logger->registrarActividad($tabla, $accion, $registro_id, $datos_anteriores, $datos_nuevos, $observaciones);
}

/**
 * Función para inicializar el sistema de auditoría en cada página
 */
function inicializar_auditoria($pdo) {
    return new AuditoriaLogger($pdo);
}

?>