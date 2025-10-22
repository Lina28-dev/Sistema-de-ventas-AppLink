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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Lilipink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background: linear-gradient(180deg, #343a40 0%, #212529 100%); color: white; }
        .sidebar .nav-link { color: rgba(255,255,255,0.8); padding: 12px 20px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: #FF1493; color: white; }
        .sidebar .nav-link i { margin-right: 10px; }
        .main-content { padding: 20px; }
        .card-stat { border-left: 4px solid #FF1493; }
        .welcome-card {
            background: #ffffff;
            color: #333;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .welcome-card i {
            font-size: 3rem;
            margin-right: 20px;
            color: #FF1493;
        }
        .dashboard-title {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        .dashboard-title i {
            margin-right: 15px;
            color: #FF1493;
        }
        .real-time-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            background-color: #28a745;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        .chart-container {
            position: relative;
            height: 300px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-md-block sidebar">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <img src="/Sistema-de-ventas-AppLink-main/public/assets/images/logo.jpg" alt="logo" class="img-fluid mb-2" style="max-height:80px;object-fit:contain;">
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="clientes">
                                <i class="fas fa-users"></i> Clientes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="pedidos">
                                <i class="fas fa-shopping-cart"></i> Pedidos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="ventas">
                                <i class="fas fa-cash-register"></i> Ventas
                            </a>
                        </li>
                        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="usuarios">
                                <i class="fas fa-user-cog"></i> Usuarios
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="reportes">
                                <i class="fas fa-chart-bar"></i> Reportes
                            </a>
                        </li>
                        <li class="nav-item mt-3 pt-3 border-top">
                            <a class="nav-link" href="perfil">
                                <i class="fas fa-user"></i> Mi Perfil
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../Auth/logout.php">
                                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-10 ms-sm-auto px-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <div class="dashboard-title">
                        <i class="fas fa-home"></i>
                        Dashboard - Sistema de Ventas
                    </div>
                    <div class="text-muted">
                        <i class="far fa-clock"></i>
                        <span id="fechaHoraDashboard"><?php date_default_timezone_set('America/Bogota'); echo date('d/m/Y H:i:s'); ?></span>
                    </div>
                </div>

                <!-- Navegación por pestañas -->
                <ul class="nav nav-tabs mb-4" id="dashboardTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="resumen-tab" data-bs-toggle="tab" data-bs-target="#resumen" type="button" role="tab">
                            <i class="fas fa-chart-line"></i> Resumen
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="actividad-tab" data-bs-toggle="tab" data-bs-target="#actividad" type="button" role="tab">
                            <i class="fas fa-clock"></i> Actividad Reciente
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="estadisticas-tab" data-bs-toggle="tab" data-bs-target="#estadisticas" type="button" role="tab">
                            <i class="fas fa-chart-bar"></i> Estadísticas
                        </button>
                    </li>
                </ul>

                <!-- Contenido de pestañas -->
                <div class="tab-content" id="dashboardTabContent">
                    <!-- Pestaña Resumen -->
                    <div class="tab-pane fade show active" id="resumen" role="tabpanel">
                        <div class="welcome-card mb-4">
                            <i class="fas fa-user-circle"></i>
                            <div>
                                <h4 class="mb-1">Bienvenido/a, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h4>
                                <p class="mb-0">Tipo de usuario: <?php echo getUserType(); ?></p>
                            </div>
                        </div>

                        <!-- Estadísticas principales -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card card-stat">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="text-muted">Ventas Hoy</h6>
                                                <h3 id="ventasHoy">$0</h3>
                                                <small class="text-success"><i class="fas fa-arrow-up"></i> <span id="ventasHoyChange">+0%</span> vs ayer</small>
                                            </div>
                                            <div class="real-time-indicator" title="Datos en tiempo real"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card card-stat">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="text-muted">Ventas del Mes</h6>
                                                <h3 id="ventasMes">$0</h3>
                                                <small class="text-success"><i class="fas fa-arrow-up"></i> <span id="ventasMesChange">+0%</span> vs mes anterior</small>
                                            </div>
                                            <div class="real-time-indicator" title="Datos en tiempo real"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card card-stat">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="text-muted">Transacciones</h6>
                                                <h3 id="transacciones">0</h3>
                                                <small class="text-muted">Transacciones totales</small>
                                            </div>
                                            <div class="real-time-indicator" title="Datos en tiempo real"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card card-stat">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="text-muted">Ticket Promedio</h6>
                                                <h3 id="ticketPromedio">$0</h3>
                                                <small class="text-info"><i class="fas fa-info-circle"></i> Promedio por venta</small>
                                            </div>
                                            <div class="real-time-indicator" title="Datos en tiempo real"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Gráfico de ventas en tiempo real -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5><i class="fas fa-chart-line"></i> Ventas en Tiempo Real - Últimas 24 Horas</h5>
                                        <span class="badge bg-success">LIVE <span class="real-time-indicator"></span></span>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-container">
                                            <canvas id="ventasRealTimeChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pestaña Actividad Reciente -->
                    <div class="tab-pane fade" id="actividad" role="tabpanel">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5><i class="fas fa-clock"></i> Últimas Ventas en Tiempo Real</h5>
                                <span class="badge bg-success">LIVE <span class="real-time-indicator"></span></span>
                            </div>
                            <div class="card-body">
                                <div id="ultimasVentas">
                                    <p class="text-muted text-center">Cargando últimas ventas...</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pestaña Estadísticas -->
                    <div class="tab-pane fade" id="estadisticas" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5><i class="fas fa-chart-pie"></i> Ventas por Categoría</h5>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="ventasPorCategoria"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5><i class="fas fa-chart-line"></i> Tendencia de Ventas</h5>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="tendenciaVentas"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Toasts y tooltips -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div id="dashboardToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ¡Bienvenido al dashboard!
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
        </div>
    </div>
    
    <script>
        // Funciones del Dashboard
        document.addEventListener('DOMContentLoaded', function() {
            // Variables globales
            let realTimeChart = null;
            let previousStats = {};
            
            // Mostrar toast de bienvenida
            const toastElement = document.getElementById('dashboardToast');
            const toast = new bootstrap.Toast(toastElement);
            
            // Función para mostrar toast
            function showToast(message, type = 'success') {
                const toastBody = toastElement.querySelector('.toast-body');
                toastBody.textContent = message;
                
                // Cambiar color según tipo
                toastElement.className = `toast align-items-center text-bg-${type} border-0`;
                toast.show();
            }
            
            // Cargar estadísticas del dashboard
            loadDashboardStats();
            
            // Actualizar fecha y hora cada segundo
            updateDateTime();
            setInterval(updateDateTime, 1000);
            
            // Actualizar estadísticas cada 30 segundos
            setInterval(loadDashboardStats, 30000);
            
            // Inicializar tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Confirmación al cerrar sesión
            const logoutLink = document.querySelector('a[href$="logout.php"]');
            if (logoutLink) {
                logoutLink.addEventListener('click', function(e) {
                    if (!confirm('¿Seguro que deseas cerrar sesión?')) {
                        e.preventDefault();
                    }
                });
            }
            
            // Mostrar toast de bienvenida después de 500ms
            setTimeout(() => {
                showToast('¡Dashboard en tiempo real activado!');
            }, 500);
        });
        
        // Función para actualizar fecha y hora
        function updateDateTime() {
            const now = new Date();
            const options = {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            };
            const dateTimeString = now.toLocaleDateString('es-CO', options).replace(',', '');
            const dateTimeElement = document.getElementById('fechaHoraDashboard');
            if (dateTimeElement) {
                dateTimeElement.textContent = dateTimeString;
            }
        }
        
        // Función para cargar estadísticas del dashboard
        function loadDashboardStats() {
            // Cargar estadísticas de ventas
            fetch('../../api/ventas.php?action=estadisticas')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateDashboardStats(data.data);
                        updateRealTimeChart(data.data.ventasUltimas24h);
                        updateUltimasVentas(data.data.ultimasVentas);
                    }
                })
                .catch(error => {
                    console.error('Error al cargar estadísticas:', error);
                    showToast('Error al cargar datos en tiempo real', 'warning');
                });
        }
        
        // Función para actualizar estadísticas en el dashboard
        function updateDashboardStats(stats) {
            // Calcular cambios porcentuales
            const ventasHoyChange = calculatePercentageChange(previousStats.ventasHoy, stats.ventasHoy);
            const ventasMesChange = calculatePercentageChange(previousStats.ventasMes, stats.ventasMes);
            
            // Actualizar valores
            document.getElementById('ventasHoy').textContent = formatCurrency(stats.ventasHoy || 0);
            document.getElementById('ventasMes').textContent = formatCurrency(stats.ventasMes || 0);
            document.getElementById('transacciones').textContent = stats.transacciones || 0;
            document.getElementById('ticketPromedio').textContent = formatCurrency(stats.ticketPromedio || 0);
            
            // Actualizar indicadores de cambio
            updateChangeIndicator('ventasHoyChange', ventasHoyChange);
            updateChangeIndicator('ventasMesChange', ventasMesChange);
            
            // Guardar stats anteriores para comparación
            previousStats = { ...stats };
        }
        
        // Función para calcular cambio porcentual
        function calculatePercentageChange(oldValue, newValue) {
            if (!oldValue || oldValue === 0) return newValue > 0 ? 100 : 0;
            return Math.round(((newValue - oldValue) / oldValue) * 100);
        }
        
        // Función para actualizar indicadores de cambio
        function updateChangeIndicator(elementId, change) {
            const element = document.getElementById(elementId);
            if (element) {
                element.textContent = `${change >= 0 ? '+' : ''}${change}%`;
                const parent = element.closest('small');
                if (parent) {
                    parent.className = change >= 0 ? 'text-success' : 'text-danger';
                    const icon = parent.querySelector('i');
                    if (icon) {
                        icon.className = change >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down';
                    }
                }
            }
        }
        
        // Función para actualizar gráfico en tiempo real
        function updateRealTimeChart(ventasData) {
            const ctx = document.getElementById('ventasRealTimeChart');
            if (!ctx) return;
            
            // Preparar datos para las últimas 24 horas
            const labels = [];
            const amounts = [];
            const counts = [];
            
            // Crear array de 24 horas
            for (let i = 0; i < 24; i++) {
                const hora = (new Date().getHours() - 23 + i + 24) % 24;
                labels.push(`${hora}:00`);
                
                const ventaHora = ventasData.find(v => parseInt(v.hora) === hora);
                amounts.push(ventaHora ? parseFloat(ventaHora.monto) : 0);
                counts.push(ventaHora ? parseInt(ventaHora.cantidad) : 0);
            }
            
            if (realTimeChart) {
                // Actualizar datos existentes
                realTimeChart.data.labels = labels;
                realTimeChart.data.datasets[0].data = amounts;
                realTimeChart.data.datasets[1].data = counts;
                realTimeChart.update('none'); // Animación rápida
            } else {
                // Crear nuevo gráfico
                realTimeChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Monto en ventas ($)',
                            data: amounts,
                            borderColor: '#FF1493',
                            backgroundColor: 'rgba(255, 20, 147, 0.1)',
                            yAxisID: 'y',
                            tension: 0.4
                        }, {
                            label: 'Cantidad de ventas',
                            data: counts,
                            borderColor: '#17a2b8',
                            backgroundColor: 'rgba(23, 162, 184, 0.1)',
                            yAxisID: 'y1',
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        scales: {
                            x: {
                                display: true,
                                title: {
                                    display: true,
                                    text: 'Hora del día'
                                }
                            },
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                title: {
                                    display: true,
                                    text: 'Monto ($)'
                                }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                title: {
                                    display: true,
                                    text: 'Cantidad'
                                },
                                grid: {
                                    drawOnChartArea: false,
                                }
                            }
                        },
                        plugins: {
                            title: {
                                display: true,
                                text: 'Ventas por Hora - Últimas 24h'
                            }
                        }
                    }
                });
            }
        }
        
        // Función para actualizar últimas ventas
        function updateUltimasVentas(ventas) {
            const container = document.getElementById('ultimasVentas');
            if (!container) return;
            
            if (!ventas || ventas.length === 0) {
                container.innerHTML = '<p class="text-muted text-center">No hay ventas recientes</p>';
                return;
            }
            
            const ventasHTML = ventas.map(venta => {
                const fecha = new Date(venta.fecha_venta);
                const timeAgo = getTimeAgo(fecha);
                
                return `
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-shopping-cart text-success"></i>
                            </div>
                            <div>
                                <strong>Venta #${venta.id}</strong>
                                <br><small class="text-muted">${venta.cliente_nombre}</small>
                            </div>
                        </div>
                        <div class="text-end">
                            <strong class="text-success">${formatCurrency(venta.total)}</strong>
                            <br><small class="text-muted">${timeAgo}</small>
                        </div>
                    </div>
                `;
            }).join('');
            
            container.innerHTML = `<div class="list-group">${ventasHTML}</div>`;
        }
        
        // Función para formatear moneda
        function formatCurrency(amount) {
            return new Intl.NumberFormat('es-CO', {
                style: 'currency',
                currency: 'COP',
                minimumFractionDigits: 0
            }).format(amount);
        }
        
        // Función para calcular tiempo transcurrido
        function getTimeAgo(date) {
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMins / 60);
            const diffDays = Math.floor(diffHours / 24);
            
            if (diffMins < 1) return 'Ahora mismo';
            if (diffMins < 60) return `Hace ${diffMins} min`;
            if (diffHours < 24) return `Hace ${diffHours}h`;
            return `Hace ${diffDays} días`;
        }
    </script>
</body>
</html>
</body>
</html>
