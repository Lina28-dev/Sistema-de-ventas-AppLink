<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Session - AppLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .debug-container { max-width: 800px; margin: 2rem auto; }
        .session-card { background: white; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); padding: 2rem; margin-bottom: 1.5rem; }
        .session-title { color: #e91e63; border-bottom: 2px solid #e91e63; padding-bottom: 0.5rem; margin-bottom: 1rem; }
        .permission-item { padding: 0.5rem; margin: 0.25rem 0; border-radius: 5px; }
        .permission-true { background-color: #d4edda; color: #155724; }
        .permission-false { background-color: #f8d7da; color: #721c24; }
        pre { background: #f8f9fa; padding: 1rem; border-radius: 5px; border-left: 4px solid #e91e63; }
    </style>
</head>
<body>
    <div class="debug-container">
        <div class="session-card">
            <h2 class="session-title">
                <i class="fas fa-bug"></i> Debug de Sesión - Sistema AppLink
            </h2>
            
            <?php if (isset($_SESSION['authenticated']) && $_SESSION['authenticated']): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <strong>Usuario Autenticado</strong>
                </div>
                
                <h4><i class="fas fa-user"></i> Información del Usuario</h4>
                <div class="row">
                    <div class="col-md-6">
                        <strong>ID:</strong> <?php echo $_SESSION['user_id'] ?? 'No definido'; ?><br>
                        <strong>Nick:</strong> <?php echo $_SESSION['user_nick'] ?? 'No definido'; ?><br>
                        <strong>Nombre:</strong> <?php echo $_SESSION['user_name'] ?? 'No definido'; ?><br>
                    </div>
                    <div class="col-md-6">
                        <strong>Rol:</strong> 
                        <span class="badge bg-primary"><?php echo $_SESSION['user_role'] ?? 'No definido'; ?></span><br>
                        <strong>Es Admin:</strong> 
                        <span class="badge <?php echo ($_SESSION['is_admin'] ?? false) ? 'bg-success' : 'bg-secondary'; ?>">
                            <?php echo ($_SESSION['is_admin'] ?? false) ? 'SÍ' : 'NO'; ?>
                        </span><br>
                        <strong>Login Time:</strong> 
                        <?php echo isset($_SESSION['login_time']) ? date('Y-m-d H:i:s', $_SESSION['login_time']) : 'No definido'; ?>
                    </div>
                </div>
                
                <h4 class="mt-4"><i class="fas fa-key"></i> Permisos</h4>
                <?php if (isset($_SESSION['permissions']) && is_array($_SESSION['permissions'])): ?>
                    <div class="row">
                        <?php foreach ($_SESSION['permissions'] as $permission => $value): ?>
                            <div class="col-md-6">
                                <div class="permission-item <?php echo $value ? 'permission-true' : 'permission-false'; ?>">
                                    <strong><?php echo ucfirst($permission); ?>:</strong>
                                    <span class="float-end">
                                        <i class="fas <?php echo $value ? 'fa-check text-success' : 'fa-times text-danger'; ?>"></i>
                                        <?php echo $value ? 'PERMITIDO' : 'DENEGADO'; ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> No se encontraron permisos en la sesión
                    </div>
                <?php endif; ?>
                
                <h4 class="mt-4"><i class="fas fa-database"></i> Datos Completos de Sesión</h4>
                <pre><?php print_r($_SESSION); ?></pre>
                
            <?php else: ?>
                <div class="alert alert-danger">
                    <i class="fas fa-times-circle"></i> <strong>Usuario NO Autenticado</strong>
                </div>
                <p>La sesión no contiene información de autenticación válida.</p>
                <pre><?php print_r($_SESSION); ?></pre>
            <?php endif; ?>
            
            <div class="mt-4">
                <a href="/Sistema-de-ventas-AppLink-main/public/" class="btn btn-primary">
                    <i class="fas fa-home"></i> Ir al Inicio
                </a>
                <a href="/Sistema-de-ventas-AppLink-main/public/dashboard" class="btn btn-success">
                    <i class="fas fa-tachometer-alt"></i> Ir al Dashboard
                </a>
                <a href="/Sistema-de-ventas-AppLink-main/src/Auth/logout.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>