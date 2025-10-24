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
<link rel="stylesheet" href="assets/css/theme-styles.css">
<body class="theme-transition">
    <div class="d-flex justify-content-end p-3">
        <a href="Login/logout.php" class="btn btn-outline-danger">
            <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
        </a>
    </div>

    <div class="container-fluid px-4">
        <div class="row">
            <div class="col-12">
                <h1 class="text-center mb-5">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Panel de Control
                </h1>
            </div>
        </div>
        
        <div class="row g-4 justify-content-center">
            <div class="col-lg-3 col-md-6">
                <a href="alta_baja.php" class="text-decoration-none">
                    <div class="card menu h-100 shadow-hover">
                        <div class="card-body text-center">
                            <i class="fas fa-boxes fa-3x mb-3"></i>
                            <h5 class="card-title">Gestión de Inventario</h5>
                            <p class="card-text">Administra tu inventario de productos</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-lg-3 col-md-6">
                <a href="pedidos.php" class="text-decoration-none">
                    <div class="card menu h-100 shadow-hover">
                        <div class="card-body text-center">
                            <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                            <h5 class="card-title">Orden de Pedido</h5>
                            <p class="card-text">Gestiona las órdenes de pedidos</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-lg-3 col-md-6">
                <a href="ventas.php" class="text-decoration-none">
                    <div class="card menu h-100 shadow-hover">
                        <div class="card-body text-center">
                            <i class="fas fa-chart-line fa-3x mb-3"></i>
                            <h5 class="card-title">Ventas</h5>
                            <p class="card-text">Registra y consulta las ventas</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-lg-3 col-md-6">
                <a href="clientes.php" class="text-decoration-none">
                    <div class="card menu h-100 shadow-hover">
                        <div class="card-body text-center">
                            <i class="fas fa-users fa-3x mb-3"></i>
                            <h5 class="card-title">Registro de Clientes</h5>
                            <p class="card-text">Administra la base de clientes</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Theme Switcher -->
    <script src="assets/js/theme-switcher.js"></script>
    
    <style>
        .shadow-hover {
            transition: all 0.3s ease;
        }
        
        .shadow-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px var(--shadow-hover) !important;
        }
        
        .card.menu {
            border: none;
            background: var(--card-bg);
            color: var(--text-primary);
        }
        
        .card.menu:hover {
            background: var(--menu-hover-bg);
            color: #ffffff;
        }
        
        .card.menu i {
            color: var(--primary-color);
        }
        
        .card.menu:hover i {
            color: #ffffff;
        }
    </style>
</body>
</html>
