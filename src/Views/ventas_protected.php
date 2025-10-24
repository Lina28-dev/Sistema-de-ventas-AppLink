<?php
/**
 * Ejemplo de protección de página - Empleados y Administradores
 * Sistema de Ventas AppLink
 */

session_start();

// Incluir middleware de roles
require_once __DIR__ . '/../Middleware/RoleMiddleware.php';

// Verificar que solo empleados y administradores puedan acceder
if (!RoleMiddleware::requireAnyRole(['administrador', 'empleado'])) {
    // Si no tiene permisos, se redirige automáticamente
    exit;
}

// Si llegamos aquí, el usuario tiene permisos
$current_user = RoleMiddleware::getCurrentUser();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Ventas - AppLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info">
                    <h4><i class="<?php echo RoleMiddleware::getRoleIcon(); ?> me-2"></i>Gestión de Ventas</h4>
                    <p>Bienvenido <strong><?php echo htmlspecialchars($current_user['nombre']); ?></strong> 
                    (<?php echo RoleMiddleware::getRoleName(); ?>), 
                    tienes acceso al módulo de ventas.</p>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-line me-2"></i>Panel de Ventas</h5>
                    </div>
                    <div class="card-body">
                        <p>Aquí puedes gestionar las ventas del sistema.</p>
                        
                        <?php if (RoleMiddleware::isAdmin()): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-star me-2"></i>
                                <strong>Vista de Administrador:</strong> Tienes acceso completo a todas las funciones.
                            </div>
                        <?php endif; ?>
                        
                        <!-- Contenido para empleados y administradores -->
                    </div>
                </div>
                
                <div class="mt-3">
                    <a href="/Sistema-de-ventas-AppLink-main/public/dashboard" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-2"></i>Volver al Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>