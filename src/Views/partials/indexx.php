<?php
session_start();

// Debug: verificar el estado de la sesión
error_log("Estado de la sesión en indexx.php: " . print_r($_SESSION, true));

if (!isset($_SESSION['usuario_nombre'])) {
    $_SESSION['error'] = "Por favor inicia sesión para acceder al sistema";
    header("Location: login.php");
    exit();
}

include('header.php');
?>

<link rel="stylesheet" href="css/index.css">
<body>
    <div style="text-align: right; margin: 10px;">
        <a href="Login/logout.php" style="color: red; font-weight: bold;">Cerrar Sesión</a>
    </div>

    <div class="container">
        <div class="menu_central">
            <a href="consulta.php">
                <div class="menu1 menu">
                    <div class="boton">
                        <p class="texto_boton">Consulta de stock producto.</p>
                    </div>
                </div>
            </a>

            <a href="alta_baja.php">
                <div class="menu3 menu">
                    <div class="boton">
                        <p class="texto_boton">Gestion de Inventario.</p>
                    </div>
                </div>
            </a>

            <a href="pedidos.php">
                <div class="menu2 menu">
                    <div class="boton">
                        <p class="texto_boton">Orden de pedido.</p>
                    </div>
                </div>
            </a>

            <a href="ventas.php">
                <div class="menu3 menu">
                    <div class="boton">
                        <p class="texto_boton">Ventas.</p>
                    </div>
                </div>
            </a>

            <a href="clientes.php"?>
                <div class="menu5 menu">
                    <div class="boton">
                    <p class="texto_boton">Registro de clientes</p>
                    </div>
                </div>
            </a>