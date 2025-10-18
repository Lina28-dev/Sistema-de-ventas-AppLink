<?php
// La sesión ya está iniciada en index.php

// Verificar si el usuario está autenticado
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Location: /Sistema-de-ventas-AppLink-main/public/ventas.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Lilipink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Reportes</h1>
        <p>Página en construcción...</p>
        <a href="/Sistema-de-ventas-AppLink-main/public/dashboard" class="btn btn-primary">Volver al Dashboard</a>
    </div>
</body>
</html>

