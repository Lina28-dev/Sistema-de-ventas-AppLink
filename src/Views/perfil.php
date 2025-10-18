<?php
// La sesión ya está iniciada en index.php

// Verificar si el usuario está autenticado
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Location: /gestor-ventas-lilipink-main/public/');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Lilipink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Mi Perfil</h1>
        <div class="card">
            <div class="card-body">
                <h5>Información del Usuario</h5>
                <p><strong>Nombre:</strong> <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
                <p><strong>Usuario:</strong> <?php echo htmlspecialchars($_SESSION['user_nick']); ?></p>
                <p><strong>Tipo:</strong> 
                    <?php 
                    if ($_SESSION['is_admin']) echo 'Administrador';
                    elseif ($_SESSION['is_medium']) echo 'Usuario Medio';
                    else echo 'Visitante';
                    ?>
                </p>
            </div>
        </div>
        <a href="/gestor-ventas-lilipink-main/public/dashboard" class="btn btn-primary mt-3">Volver al Dashboard</a>
    </div>
</body>
</html>
