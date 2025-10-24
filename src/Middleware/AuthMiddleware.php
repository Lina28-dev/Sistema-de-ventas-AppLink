<?php
// Middleware de autenticación mejorado
class AuthMiddleware {
    
    public static function checkAuth($redirect_to_login = true) {
        // Iniciar sesión si no está activa
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Verificar autenticación
        if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
            if ($redirect_to_login) {
                // Guardar la URL actual para redirigir después del login
                $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
                header('Location: /Sistema-de-ventas-AppLink-main/public/');
                exit();
            }
            return false;
        }
        
        // Verificar timeout de sesión (1 hora)
        if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 3600) {
            self::logout();
            if ($redirect_to_login) {
                $_SESSION['error'] = 'Su sesión ha expirado. Por favor, inicie sesión nuevamente.';
                header('Location: /Sistema-de-ventas-AppLink-main/public/');
                exit();
            }
            return false;
        }
        
        return true;
    }
    
    public static function checkRole($required_role) {
        if (!self::checkAuth()) {
            return false;
        }
        
        $user_role = $_SESSION['user_role'] ?? 'cliente';
        
        // El administrador tiene acceso a todo
        if ($user_role === 'administrador') {
            return true;
        }
        
        // Verificar rol específico
        switch ($required_role) {
            case 'administrador':
                return $user_role === 'administrador';
            case 'empleado':
                return in_array($user_role, ['administrador', 'empleado']);
            case 'cliente':
                return in_array($user_role, ['administrador', 'empleado', 'cliente']);
            default:
                return false;
        }
    }
    
    public static function requireRole($required_role) {
        if (!self::checkRole($required_role)) {
            http_response_code(403);
            $_SESSION['error'] = "No tiene permisos para acceder a esta sección. Se requiere rol: $required_role";
            header('Location: /Sistema-de-ventas-AppLink-main/public/dashboard');
            exit();
        }
    }
    
    public static function getUserInfo() {
        if (!self::checkAuth(false)) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'] ?? null,
            'nick' => $_SESSION['user_nick'] ?? '',
            'name' => $_SESSION['user_name'] ?? '',
            'role' => $_SESSION['user_role'] ?? 'cliente',
            'is_admin' => $_SESSION['is_admin'] ?? false,
            'is_medium' => $_SESSION['is_medium'] ?? false,
            'permissions' => $_SESSION['permissions'] ?? []
        ];
    }
    
    public static function logout() {
        // Destruir todas las variables de sesión
        $_SESSION = array();
        
        // Destruir la cookie de sesión
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destruir la sesión
        session_destroy();
    }
    
    public static function redirectAfterLogin() {
        $redirect_url = $_SESSION['redirect_after_login'] ?? '/Sistema-de-ventas-AppLink-main/public/dashboard';
        unset($_SESSION['redirect_after_login']);
        
        // Asegurar que no redirija a la página de login
        if (strpos($redirect_url, '/public/') === false || $redirect_url === '/Sistema-de-ventas-AppLink-main/public/') {
            $redirect_url = '/Sistema-de-ventas-AppLink-main/public/dashboard';
        }
        
        return $redirect_url;
    }
}
?>