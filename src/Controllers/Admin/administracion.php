<?php
session_start(); // Add session_start at the beginning
include('header.php');
include('navegador.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración</title>
    <link rel="stylesheet" href="css/base.css">
</head>
<body>
    <script>
        let infoPagina = document.getElementById('infoPagina');
        infoPagina.innerHTML = 'Panel de Administracion';
        let infoGeneral = document.getElementById('infoGeneralText');
        infoGeneral.innerHTML = "Sistema de Ventas Lili Pink";
    </script>
</body>
</html>