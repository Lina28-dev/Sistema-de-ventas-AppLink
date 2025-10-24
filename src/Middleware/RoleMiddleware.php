<?php
/**
 * Middleware de Autorización basado en Roles
 * Sistema de Ventas AppLink
 */

class RoleMiddleware {
    
    /**
     * Verificar si el usuario tiene acceso a un módulo específico
     */
    public static function checkAccess($module) {
        if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
            self::redirectToLogin('Debe iniciar sesión para acceder');
            return false;
        }

        if (!isset($_SESSION['permissions'][$module]) || !$_SESSION['permissions'][$module]) {
            self::accessDenied();
            return false;
        }

        return true;
    }

    /**
     * Verificar rol específico
     */
    public static function requireRole($required_role) {
        if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
            self::redirectToLogin('Debe iniciar sesión para acceder');
            return false;
        }

        $user_role = $_SESSION['user_role'] ?? 'cliente';
        
        // El administrador tiene acceso a todo
        if ($user_role === 'administrador') {
            return true;
        }

        if ($user_role !== $required_role) {
            self::accessDenied();
            return false;
        }

        return true;
    }

    /**
     * Verificar múltiples roles permitidos
     */
    public static function requireAnyRole($allowed_roles) {
        if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
            self::redirectToLogin('Debe iniciar sesión para acceder');
            return false;
        }

        $user_role = $_SESSION['user_role'] ?? 'cliente';
        
        if (in_array($user_role, $allowed_roles)) {
            return true;
        }

        self::accessDenied();
        return false;
    }

    /**
     * Obtener información del usuario actual
     */
    public static function getCurrentUser() {
        if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'] ?? null,
            'nick' => $_SESSION['user_nick'] ?? null,
            'nombre' => $_SESSION['user_name'] ?? null,
            'rol' => $_SESSION['user_role'] ?? 'cliente',
            'permissions' => $_SESSION['permissions'] ?? []
        ];
    }

    /**
     * Verificar si el usuario es administrador
     */
    public static function isAdmin() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'administrador';
    }

    /**
     * Verificar si el usuario es empleado
     */
    public static function isEmployee() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'empleado';
    }

    /**
     * Verificar si el usuario es cliente
     */
    public static function isClient() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'cliente';
    }

    /**
     * Obtener nombre del rol en español
     */
    public static function getRoleName($role = null) {
        $role = $role ?? ($_SESSION['user_role'] ?? 'cliente');
        
        $roles = [
            'administrador' => 'Administrador',
            'empleado' => 'Empleado',
            'cliente' => 'Cliente'
        ];

        return $roles[$role] ?? 'Usuario';
    }

    /**
     * Obtener ícono del rol
     */
    public static function getRoleIcon($role = null) {
        $role = $role ?? ($_SESSION['user_role'] ?? 'cliente');
        
        $icons = [
            'administrador' => 'fas fa-crown',
            'empleado' => 'fas fa-user-tie',
            'cliente' => 'fas fa-user'
        ];

        return $icons[$role] ?? 'fas fa-user';
    }

    /**
     * Redirigir al login
     */
    private static function redirectToLogin($message = 'Acceso denegado') {
        $_SESSION['error'] = $message;
        header('Location: /Sistema-de-ventas-AppLink-main/public/');
        exit;
    }

    /**
     * Mostrar página de acceso denegado
     */
    private static function accessDenied() {
        http_response_code(403);
        $user_role = $_SESSION['user_role'] ?? 'cliente';
        $user_name = $_SESSION['user_name'] ?? 'Usuario';
        
        echo '<!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Acceso Denegado - AppLink</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
            <style>
                body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
                .access-denied-container { min-height: 100vh; display: flex; align-items: center; justify-content: center; }
                .access-denied-card { background: white; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); padding: 3rem; text-align: center; max-width: 500px; }
                .access-denied-icon { font-size: 4rem; color: #dc3545; margin-bottom: 1.5rem; }
                .role-badge { background: linear-gradient(135deg, #e91e63, #ff4081); color: white; padding: 0.5rem 1rem; border-radius: 20px; display: inline-block; margin: 1rem 0; }
            </style>
        </head>
        <body>
            <div class="access-denied-container">
                <div class="access-denied-card">
                    <div class="access-denied-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h2 class="mb-3">Acceso Denegado</h2>
                    <p class="text-muted mb-3">No tienes permisos suficientes para acceder a esta sección.</p>
                    <div class="role-badge">
                        <i class="' . self::getRoleIcon($user_role) . ' me-2"></i>
                        Tu rol: ' . self::getRoleName($user_role) . '
                    </div>
                    <p class="mt-3"><strong>Usuario:</strong> ' . htmlspecialchars($user_name) . '</p>
                    <div class="mt-4">
                        <a href="/Sistema-de-ventas-AppLink-main/public/dashboard" class="btn btn-primary me-2">
                            <i class="fas fa-home me-2"></i>Ir al Dashboard
                        </a>
                        <a href="/Sistema-de-ventas-AppLink-main/public/" class="btn btn-outline-secondary">
                            <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                        </a>
                    </div>
                </div>
            </div>
        </body>
        </html>';
        exit;
    }

    /**
     * Generar menú dinámico basado en permisos
     */
    public static function getMenuItems() {
        $permissions = $_SESSION['permissions'] ?? [];
        $role = $_SESSION['user_role'] ?? 'cliente';
        
        $menu_items = [];
        
        // Dashboard siempre disponible
        $menu_items[] = [
            'title' => 'Dashboard',
            'icon' => 'fas fa-tachometer-alt',
            'url' => '/Sistema-de-ventas-AppLink-main/public/dashboard',
            'permission' => 'dashboard'
        ];

        // Pedidos (todos los roles)
        if ($permissions['pedidos'] ?? false) {
            $menu_items[] = [
                'title' => 'Pedidos',
                'icon' => 'fas fa-shopping-cart',
                'url' => '/Sistema-de-ventas-AppLink-main/public/pedidos',
                'permission' => 'pedidos'
            ];
        }

        // Ventas (administrador y empleado)
        if ($permissions['ventas'] ?? false) {
            $menu_items[] = [
                'title' => 'Ventas',
                'icon' => 'fas fa-chart-line',
                'url' => '/Sistema-de-ventas-AppLink-main/public/ventas',
                'permission' => 'ventas'
            ];
        }

        // Inventario (solo administrador)
        if ($permissions['inventario'] ?? false) {
            $menu_items[] = [
                'title' => 'Inventario',
                'icon' => 'fas fa-boxes',
                'url' => '/Sistema-de-ventas-AppLink-main/public/inventario',
                'permission' => 'inventario'
            ];
        }

        // Clientes (solo administrador)
        if ($permissions['clientes'] ?? false) {
            $menu_items[] = [
                'title' => 'Clientes',
                'icon' => 'fas fa-users',
                'url' => '/Sistema-de-ventas-AppLink-main/public/clientes',
                'permission' => 'clientes'
            ];
        }

        // Reportes (administrador y empleado)
        if ($permissions['reportes'] ?? false) {
            $menu_items[] = [
                'title' => 'Reportes',
                'icon' => 'fas fa-chart-bar',
                'url' => '/Sistema-de-ventas-AppLink-main/public/reportes',
                'permission' => 'reportes'
            ];
        }

        // Usuarios (solo administrador)
        if ($permissions['usuarios'] ?? false) {
            $menu_items[] = [
                'title' => 'Usuarios',
                'icon' => 'fas fa-user-cog',
                'url' => '/Sistema-de-ventas-AppLink-main/public/usuarios',
                'permission' => 'usuarios'
            ];
        }

        // Configuración (solo administrador)
        if ($permissions['configuracion'] ?? false) {
            $menu_items[] = [
                'title' => 'Configuración',
                'icon' => 'fas fa-cog',
                'url' => '/Sistema-de-ventas-AppLink-main/public/configuracion',
                'permission' => 'configuracion'
            ];
        }

        return $menu_items;
    }
}
?>