<?php
// La sesión ya está iniciada en index.php

// Verificar si el usuario está autenticado
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Location: /Sistema-de-ventas-AppLink-main/public/');
    exit;
}

// Función para mostrar el tipo de usuario
function getUserType() {
    if ($_SESSION['is_admin']) return 'Administrador';
    if ($_SESSION['is_medium']) return 'Usuario Medio';
    if ($_SESSION['is_visitor']) return 'Visitante';
    return 'Usuario';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Dashboard - Gestor de Ventas Lilipink</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Sidebar CSS -->
    <link href="/Sistema-de-ventas-AppLink-main/public/css/sidebar.css" rel="stylesheet">
    <!-- Sistema de Temas -->
    <link href="/Sistema-de-ventas-AppLink-main/public/css/theme-system.css" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }
        body { 
            background-color: #f8f9fa; 
            font-size: 0.875rem;
        }
        .main-content { padding: 20px; }
        .card-stat { border-left: 4px solid #FF1493; }
        .welcome-card {
            background: #ffffffff;
            color: black;
            padding: 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        .welcome-card i {
            font-size: 2.5rem;
            margin-right: 16px;
        }
        .dashboard-title {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        .card-body h6 {
            font-size: 0.75rem;
            font-weight: 500;
        }
        .card-body h3 {
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        /* Responsive Styles */
        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
                margin-left: 0 !important;
            }
            
            .dashboard-title {
                font-size: 1.5rem;
                margin-top: 50px;
            }
            
            .welcome-card {
                flex-direction: column;
                text-align: center;
                padding: 15px;
            }
            
            .welcome-card i {
                font-size: 2rem;
                margin-right: 0;
                margin-bottom: 10px;
            }
            
            .card-body h3 {
                font-size: 1.25rem;
            }
            
            .row .col-md-3 {
                margin-bottom: 15px;
            }
        }
        
        @media (max-width: 576px) {
            .dashboard-title {
                font-size: 1.25rem;
            }
            
            .card-body h3 {
                font-size: 1.1rem;
            }
            
            .welcome-card {
                padding: 12px;
            }
            
            .main-content {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Botón toggle para móviles -->
    <button class="mobile-toggle" id="mobileToggle">
        <i class="fas fa-bars"></i>
    </button>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar centralizado -->
            <?php 
                $activePage = 'dashboard';
                include __DIR__ . '/partials/sidebar.php';
            ?>

            <!-- Main content -->
            <main class="col-md-10 px-4 main-content">
                <div class="dashboard-title">
                    <i class="fas fa-home text-pink"></i>
                    Sistema de Ventas
                </div>
                <div class="mb-3" style="font-size:1.1rem; color:#555;">
                    <i class="far fa-clock text-pink"></i>
                    Fecha y hora actual: <span id="fechaHoraDashboard"><?php date_default_timezone_set('America/Bogota'); echo date('d/m/Y H:i:s'); ?></span>
                </div>
                <div class="welcome-card mb-4">
                    <i class="fas fa-user-circle"></i>
                    <div>
                        <h4 class="mb-1">Bienvenido/a, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h4>
                        <p class="mb-0">Tipo de usuario: <?php echo getUserType(); ?></p>
                    </div>
                </div>
                <div class="row my-4">
                    <div class="col-md-3"><div class="card card-stat"><div class="card-body"><h6 class="text-muted">Ventas Hoy</h6><h3 id="ventasHoy">$0.00</h3></div></div></div>
                    <div class="col-md-3"><div class="card card-stat"><div class="card-body"><h6 class="text-muted">Ventas del Mes</h6><h3 id="ventasMes">$0.00</h3></div></div></div>
                    <div class="col-md-3"><div class="card card-stat"><div class="card-body"><h6 class="text-muted">Transacciones</h6><h3 id="transacciones">0</h3></div></div></div>
                    <div class="col-md-3"><div class="card card-stat"><div class="card-body"><h6 class="text-muted">Ventas Promedio</h6><h3 id="ticketPromedio">$0.00</h3></div></div></div>
                </div>
                
                </div>
                <!-- Aquí puedes agregar más contenido del dashboard -->
            </main>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/Sistema-de-ventas-AppLink-main/public/js/theme-system.js"></script>
    <!-- Toasts y tooltips -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div id="dashboardToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ¡Acción realizada con éxito!
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
        </div>
    </div>
    <script>
        // Toggle sidebar en móviles
        document.addEventListener('DOMContentLoaded', function() {
            const mobileToggle = document.getElementById('mobileToggle');
            const sidebar = document.querySelector('.sidebar');
            
            if (mobileToggle && sidebar) {
                mobileToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                });
                
                // Cerrar sidebar al hacer click fuera en móviles
                document.addEventListener('click', function(e) {
                    if (window.innerWidth <= 768) {
                        if (!sidebar.contains(e.target) && !mobileToggle.contains(e.target)) {
                            sidebar.classList.remove('show');
                        }
                    }
                });
            }
            
            // Mostrar toast de ejemplo al cargar
            var toastEl = document.getElementById('dashboardToast');
            if (toastEl) {
                var toast = new bootstrap.Toast(toastEl);
                toast.show();
            }
            
            // Inicializar tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Confirmación al cerrar sesión
            var logoutLink = document.querySelector('a[href$="logout"]');
            if (logoutLink) {
                logoutLink.addEventListener('click', function(e) {
                    if (!confirm('¿Seguro que deseas cerrar sesión?')) {
                        e.preventDefault();
                    }
                });
            }
        });
    </script>
</body>
</html>
