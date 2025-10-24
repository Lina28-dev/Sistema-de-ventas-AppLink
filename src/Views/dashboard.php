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
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        
        /* Estilos para gráficos */
        .chart-container {
            position: relative;
            height: 300px;
            margin: 20px 0;
        }
        
        .chart-card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-radius: 0.75rem;
            margin-bottom: 20px;
        }
        
        .chart-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #FF1493;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .metrics-row {
            background: linear-gradient(135deg, #FF1493 0%, #FF69B4 100%);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            color: white;
        }
        
        .metric-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .metric-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .metric-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .loading-spinner {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 200px;
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
            
            .chart-container {
                height: 250px;
            }
            
            .metric-value {
                font-size: 1.5rem;
            }
            
            .chart-title {
                font-size: 1rem;
            }
            
            .metrics-row {
                padding: 15px;
                margin-bottom: 20px;
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
            
            .chart-container {
                height: 200px;
            }
            
            .metric-value {
                font-size: 1.3rem;
            }
            
            .metric-label {
                font-size: 0.8rem;
            }
            
            .chart-title {
                font-size: 0.9rem;
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
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="dashboard-title">
                        <i class="fas fa-home text-pink"></i>
                        Sistema de Ventas
                    </div>
                    <div>
                        <button class="btn btn-success btn-sm me-2" onclick="mostrarModalVentas()" title="Gestionar ventas">
                            <i class="fas fa-edit"></i> Ventas
                        </button>
                        <button class="btn btn-outline-primary btn-sm" onclick="refrescarDatos()" title="Actualizar datos">
                            <i class="fas fa-sync-alt"></i> Actualizar
                        </button>
                    </div>
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
                <!-- Métricas principales -->
                <div class="metrics-row">
                    <div class="row">
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="metric-card">
                                <div class="metric-value" id="ventasHoy">$0</div>
                                <div class="metric-label"><i class="fas fa-calendar-day"></i> Ventas Hoy</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="metric-card">
                                <div class="metric-value" id="ventasMes">$0</div>
                                <div class="metric-label"><i class="fas fa-calendar-alt"></i> Ventas del Mes</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="metric-card">
                                <div class="metric-value" id="transacciones">0</div>
                                <div class="metric-label"><i class="fas fa-shopping-cart"></i> Transacciones</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="metric-card">
                                <div class="metric-value" id="ticketPromedio">$0</div>
                                <div class="metric-label"><i class="fas fa-receipt"></i> Ticket Promedio</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Gráficos de reportes -->
                <div class="row">
                    <!-- Gráfico de ventas diarias -->
                    <div class="col-lg-8 col-md-12">
                        <div class="card chart-card">
                            <div class="card-body">
                                <div class="chart-title">
                                    <i class="fas fa-chart-line"></i>
                                    Ventas de los Últimos 7 Días
                                </div>
                                <div class="chart-container">
                                    <canvas id="ventasDiariasChart"></canvas>
                                    <div class="loading-spinner" id="loadingVentas">
                                        <div class="spinner-border text-primary" role="status"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Gráfico de productos top -->
                    <div class="col-lg-4 col-md-12">
                        <div class="card chart-card">
                            <div class="card-body">
                                <div class="chart-title">
                                    <i class="fas fa-trophy"></i>
                                    Productos Más Vendidos
                                </div>
                                <div class="chart-container">
                                    <canvas id="productosTopChart"></canvas>
                                    <div class="loading-spinner" id="loadingProductos">
                                        <div class="spinner-border text-success" role="status"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Gráfico de ventas mensuales -->
                    <div class="col-lg-6 col-md-12">
                        <div class="card chart-card">
                            <div class="card-body">
                                <div class="chart-title">
                                    <i class="fas fa-chart-bar"></i>
                                    Tendencia Mensual
                                </div>
                                <div class="chart-container">
                                    <canvas id="ventasMensualesChart"></canvas>
                                    <div class="loading-spinner" id="loadingMensuales">
                                        <div class="spinner-border text-info" role="status"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Gráfico de clientes top -->
                    <div class="col-lg-6 col-md-12">
                        <div class="card chart-card">
                            <div class="card-body">
                                <div class="chart-title">
                                    <i class="fas fa-users"></i>
                                    Mejores Clientes
                                </div>
                                <div class="chart-container">
                                    <canvas id="clientesTopChart"></canvas>
                                    <div class="loading-spinner" id="loadingClientes">
                                        <div class="spinner-border text-warning" role="status"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal para Gestión de Ventas -->
    <div class="modal fade" id="modalVentas" tabindex="-1" aria-labelledby="modalVentasLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalVentasLabel">
                        <i class="fas fa-shopping-cart"></i> Gestión de Ventas
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Toolbar de acciones -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="input-group" style="max-width: 300px;">
                            <input type="text" class="form-control form-control-sm" id="buscarVenta" placeholder="Buscar por cliente o producto...">
                            <button class="btn btn-outline-secondary btn-sm" onclick="buscarVentas()">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <div>
                            <button class="btn btn-success btn-sm" onclick="mostrarFormularioVenta()">
                                <i class="fas fa-plus"></i> Nueva Venta
                            </button>
                            <button class="btn btn-info btn-sm" onclick="cargarVentas()">
                                <i class="fas fa-sync-alt"></i> Actualizar
                            </button>
                        </div>
                    </div>

                    <!-- Formulario para nueva/editar venta -->
                    <div id="formularioVenta" class="card mb-3" style="display: none;">
                        <div class="card-header">
                            <h6 class="mb-0" id="tituloFormulario">
                                <i class="fas fa-plus-circle"></i> Nueva Venta
                            </h6>
                        </div>
                        <div class="card-body">
                            <form id="formVenta">
                                <input type="hidden" id="ventaId" name="id">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label for="clienteVenta" class="form-label">Cliente *</label>
                                        <input type="text" class="form-control form-control-sm" id="clienteVenta" name="cliente" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="productoVenta" class="form-label">Producto *</label>
                                        <input type="text" class="form-control form-control-sm" id="productoVenta" name="producto" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="cantidadVenta" class="form-label">Cantidad *</label>
                                        <input type="number" class="form-control form-control-sm" id="cantidadVenta" name="cantidad" min="1" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="precioVenta" class="form-label">Precio Unit. *</label>
                                        <input type="number" class="form-control form-control-sm" id="precioVenta" name="precio_unitario" step="0.01" min="0" required>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-4">
                                        <label for="fechaVenta" class="form-label">Fecha y Hora</label>
                                        <input type="datetime-local" class="form-control form-control-sm" id="fechaVenta" name="fecha_venta">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Total Calculado</label>
                                        <div class="form-control form-control-sm bg-light" id="totalCalculado">$0</div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fas fa-save"></i> Guardar Venta
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="cancelarFormulario()">
                                        <i class="fas fa-times"></i> Cancelar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tabla de ventas -->
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th>Producto</th>
                                    <th>Cant.</th>
                                    <th>Precio Unit.</th>
                                    <th>Total</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaVentasBody">
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Cargando...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <nav aria-label="Paginación de ventas">
                        <ul class="pagination pagination-sm justify-content-center" id="paginacionVentas">
                        </ul>
                    </nav>
                </div>
            </div>
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
        // Variables globales para los gráficos
        let ventasDiariasChart, productosTopChart, ventasMensualesChart, clientesTopChart;
        
        // URL de la API de reportes
        const API_REPORTES = '/Sistema-de-ventas-AppLink-main/src/Controllers/ReporteController.php';
        
        // Configuración de colores para los gráficos
        const colores = {
            primario: '#FF1493',
            secundario: '#FF69B4',
            exito: '#28a745',
            info: '#17a2b8',
            advertencia: '#ffc107',
            peligro: '#dc3545',
            gradiente: {
                rosa: ['#FF1493', '#FF69B4', '#FFB6C1', '#FFC0CB', '#FFDBF0'],
                azul: ['#007bff', '#0056b3', '#004085', '#002752', '#001a3d'],
                verde: ['#28a745', '#1e7e34', '#155724', '#0f3f1c', '#0a2e15']
            }
        };
        
        // Inicialización
        document.addEventListener('DOMContentLoaded', function() {
            inicializarDashboard();
            cargarMetricas();
            cargarGraficos();
        });
        
        // Función principal de inicialización
        function inicializarDashboard() {
            const mobileToggle = document.getElementById('mobileToggle');
            const sidebar = document.querySelector('.sidebar');
            
            if (mobileToggle && sidebar) {
                mobileToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                });
                
                document.addEventListener('click', function(e) {
                    if (window.innerWidth <= 768) {
                        if (!sidebar.contains(e.target) && !mobileToggle.contains(e.target)) {
                            sidebar.classList.remove('show');
                        }
                    }
                });
            }
            
            // Inicializar tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Confirmación logout
            var logoutLink = document.querySelector('a[href$="logout"]');
            if (logoutLink) {
                logoutLink.addEventListener('click', function(e) {
                    if (!confirm('¿Seguro que deseas cerrar sesión?')) {
                        e.preventDefault();
                    }
                });
            }
        }
        
        // Cargar métricas principales
        async function cargarMetricas() {
            try {
                const response = await fetch(`${API_REPORTES}?tipo=dashboard`);
                const resultado = await response.json();
                
                if (resultado.success) {
                    const datos = resultado.datos;
                    document.getElementById('ventasHoy').textContent = `$${formatearNumero(datos.ventas_hoy)}`;
                    document.getElementById('ventasMes').textContent = `$${formatearNumero(datos.ventas_mes)}`;
                    document.getElementById('transacciones').textContent = formatearNumero(datos.transacciones);
                    document.getElementById('ticketPromedio').textContent = `$${formatearNumero(datos.ticket_promedio)}`;
                }
            } catch (error) {
                console.error('Error cargando métricas:', error);
            }
        }
        
        // Cargar todos los gráficos
        async function cargarGraficos() {
            await Promise.all([
                cargarGraficoVentasDiarias(),
                cargarGraficoProductosTop(),
                cargarGraficoVentasMensuales(),
                cargarGraficoClientesTop()
            ]);
        }
        
        // Gráfico de ventas diarias
        async function cargarGraficoVentasDiarias() {
            try {
                const response = await fetch(`${API_REPORTES}?tipo=ventas-diarias`);
                const resultado = await response.json();
                
                document.getElementById('loadingVentas').style.display = 'none';
                
                if (resultado.success) {
                    const ctx = document.getElementById('ventasDiariasChart').getContext('2d');
                    
                    ventasDiariasChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: resultado.datos.fechas,
                            datasets: [{
                                label: 'Ventas ($)',
                                data: resultado.datos.ventas,
                                borderColor: colores.primario,
                                backgroundColor: colores.primario + '20',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.4,
                                pointBackgroundColor: colores.primario,
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 6
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return '$' + formatearNumero(value);
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            } catch (error) {
                console.error('Error cargando gráfico de ventas diarias:', error);
                document.getElementById('loadingVentas').innerHTML = '<div class="text-danger">Error cargando datos</div>';
            }
        }
        
        // Gráfico de productos top
        async function cargarGraficoProductosTop() {
            try {
                const response = await fetch(`${API_REPORTES}?tipo=productos-top`);
                const resultado = await response.json();
                
                document.getElementById('loadingProductos').style.display = 'none';
                
                if (resultado.success) {
                    const ctx = document.getElementById('productosTopChart').getContext('2d');
                    
                    productosTopChart = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: resultado.datos.productos.map(p => p.length > 15 ? p.substring(0, 15) + '...' : p),
                            datasets: [{
                                data: resultado.datos.cantidades,
                                backgroundColor: colores.gradiente.rosa,
                                borderWidth: 2,
                                borderColor: '#fff'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: 10,
                                        usePointStyle: true,
                                        font: {
                                            size: 11
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            } catch (error) {
                console.error('Error cargando gráfico de productos:', error);
                document.getElementById('loadingProductos').innerHTML = '<div class="text-danger">Error cargando datos</div>';
            }
        }
        
        // Gráfico de ventas mensuales
        async function cargarGraficoVentasMensuales() {
            try {
                const response = await fetch(`${API_REPORTES}?tipo=ventas-mensuales`);
                const resultado = await response.json();
                
                document.getElementById('loadingMensuales').style.display = 'none';
                
                if (resultado.success) {
                    const ctx = document.getElementById('ventasMensualesChart').getContext('2d');
                    
                    ventasMensualesChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: resultado.datos.meses,
                            datasets: [{
                                label: 'Ventas Mensuales',
                                data: resultado.datos.ventas,
                                backgroundColor: colores.gradiente.azul,
                                borderColor: colores.info,
                                borderWidth: 1,
                                borderRadius: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return '$' + formatearNumero(value);
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            } catch (error) {
                console.error('Error cargando gráfico mensuales:', error);
                document.getElementById('loadingMensuales').innerHTML = '<div class="text-danger">Error cargando datos</div>';
            }
        }
        
        // Gráfico de clientes top
        async function cargarGraficoClientesTop() {
            try {
                const response = await fetch(`${API_REPORTES}?tipo=clientes-top`);
                const resultado = await response.json();
                
                document.getElementById('loadingClientes').style.display = 'none';
                
                if (resultado.success) {
                    const ctx = document.getElementById('clientesTopChart').getContext('2d');
                    
                    clientesTopChart = new Chart(ctx, {
                        type: 'horizontalBar',
                        data: {
                            labels: resultado.datos.clientes,
                            datasets: [{
                                label: 'Total Gastado',
                                data: resultado.datos.gastos,
                                backgroundColor: colores.gradiente.verde,
                                borderColor: colores.exito,
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            indexAxis: 'y',
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return '$' + formatearNumero(value);
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            } catch (error) {
                console.error('Error cargando gráfico de clientes:', error);
                document.getElementById('loadingClientes').innerHTML = '<div class="text-danger">Error cargando datos</div>';
            }
        }
        
        // Función para formatear números
        function formatearNumero(numero) {
            return new Intl.NumberFormat('es-CO', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(numero);
        }
        
        // Función para refrescar todos los datos
        function refrescarDatos() {
            cargarMetricas();
            
            // Destruir gráficos existentes
            if (ventasDiariasChart) ventasDiariasChart.destroy();
            if (productosTopChart) productosTopChart.destroy();
            if (ventasMensualesChart) ventasMensualesChart.destroy();
            if (clientesTopChart) clientesTopChart.destroy();
            
            // Mostrar spinners de carga
            document.getElementById('loadingVentas').style.display = 'flex';
            document.getElementById('loadingProductos').style.display = 'flex';
            document.getElementById('loadingMensuales').style.display = 'flex';
            document.getElementById('loadingClientes').style.display = 'flex';
            
            // Recargar gráficos
            cargarGraficos();
        }
        
        // Auto-refresh cada 5 minutos
        setInterval(refrescarDatos, 300000);

        // ========== GESTIÓN DE VENTAS ==========
        
        let paginaActual = 1;
        let ventaEditando = null;

        // Mostrar modal de ventas
        function mostrarModalVentas() {
            const modal = new bootstrap.Modal(document.getElementById('modalVentas'));
            modal.show();
            cargarVentas();
        }

        // Cargar lista de ventas
        async function cargarVentas(pagina = 1, busqueda = '') {
            try {
                paginaActual = pagina;
                const url = `/Sistema-de-ventas-AppLink-main/src/Controllers/VentaControllerDashboard.php?accion=listar&pagina=${pagina}&limite=10&busqueda=${encodeURIComponent(busqueda)}`;
                
                const response = await fetch(url);
                const data = await response.json();
                
                if (data.success) {
                    mostrarVentas(data.datos);
                    mostrarPaginacion(data.paginacion);
                } else {
                    document.getElementById('tablaVentasBody').innerHTML = `
                        <tr><td colspan="8" class="text-center text-danger">Error: ${data.error || 'No se pudieron cargar las ventas'}</td></tr>
                    `;
                }
            } catch (error) {
                console.error('Error cargando ventas:', error);
                document.getElementById('tablaVentasBody').innerHTML = `
                    <tr><td colspan="8" class="text-center text-danger">Error de conexión</td></tr>
                `;
            }
        }

        // Mostrar ventas en la tabla
        function mostrarVentas(ventas) {
            const tbody = document.getElementById('tablaVentasBody');
            
            if (ventas.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No hay ventas registradas</td></tr>';
                return;
            }
            
            tbody.innerHTML = ventas.map(venta => `
                <tr>
                    <td>${venta.id}</td>
                    <td>${venta.fecha_formateada}</td>
                    <td>${venta.cliente}</td>
                    <td>${venta.producto}</td>
                    <td>${venta.cantidad}</td>
                    <td>$${formatearNumero(venta.precio_unitario)}</td>
                    <td class="fw-bold">$${formatearNumero(venta.total)}</td>
                    <td>
                        <button class="btn btn-sm btn-warning me-1" onclick="editarVenta(${venta.id})" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="eliminarVenta(${venta.id})" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        // Mostrar paginación
        function mostrarPaginacion(paginacion) {
            const container = document.getElementById('paginacionVentas');
            const { pagina_actual, total_paginas } = paginacion;
            
            if (total_paginas <= 1) {
                container.innerHTML = '';
                return;
            }
            
            let html = '';
            
            // Botón anterior
            html += `<li class="page-item ${pagina_actual === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="cargarVentas(${pagina_actual - 1}, document.getElementById('buscarVenta').value)">Anterior</a>
            </li>`;
            
            // Páginas numéricas
            for (let i = 1; i <= total_paginas; i++) {
                if (i === pagina_actual || i === 1 || i === total_paginas || (i >= pagina_actual - 1 && i <= pagina_actual + 1)) {
                    html += `<li class="page-item ${i === pagina_actual ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="cargarVentas(${i}, document.getElementById('buscarVenta').value)">${i}</a>
                    </li>`;
                } else if (i === pagina_actual - 2 || i === pagina_actual + 2) {
                    html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
            }
            
            // Botón siguiente
            html += `<li class="page-item ${pagina_actual === total_paginas ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="cargarVentas(${pagina_actual + 1}, document.getElementById('buscarVenta').value)">Siguiente</a>
            </li>`;
            
            container.innerHTML = html;
        }

        // Mostrar formulario de nueva venta
        function mostrarFormularioVenta() {
            ventaEditando = null;
            document.getElementById('tituloFormulario').innerHTML = '<i class="fas fa-plus-circle"></i> Nueva Venta';
            document.getElementById('formVenta').reset();
            document.getElementById('ventaId').value = '';
            document.getElementById('fechaVenta').value = '';
            document.getElementById('totalCalculado').textContent = '$0';
            document.getElementById('formularioVenta').style.display = 'block';
            document.getElementById('clienteVenta').focus();
        }

        // Editar venta existente
        async function editarVenta(id) {
            try {
                const response = await fetch(`/Sistema-de-ventas-AppLink-main/src/Controllers/VentaControllerDashboard.php?accion=obtener&id=${id}`);
                const data = await response.json();
                
                if (data.success) {
                    const venta = data.datos;
                    ventaEditando = id;
                    
                    document.getElementById('tituloFormulario').innerHTML = '<i class="fas fa-edit"></i> Editar Venta';
                    document.getElementById('ventaId').value = venta.id;
                    document.getElementById('clienteVenta').value = venta.cliente;
                    document.getElementById('productoVenta').value = venta.producto;
                    document.getElementById('cantidadVenta').value = venta.cantidad;
                    document.getElementById('precioVenta').value = venta.precio_unitario;
                    document.getElementById('fechaVenta').value = venta.fecha_solo + 'T' + venta.hora_solo;
                    document.getElementById('totalCalculado').textContent = '$' + formatearNumero(venta.total);
                    document.getElementById('formularioVenta').style.display = 'block';
                    document.getElementById('clienteVenta').focus();
                } else {
                    alert('Error al cargar la venta: ' + data.error);
                }
            } catch (error) {
                console.error('Error editando venta:', error);
                alert('Error de conexión al editar la venta');
            }
        }

        // Eliminar venta
        async function eliminarVenta(id) {
            if (!confirm('¿Estás seguro de que deseas eliminar esta venta?')) {
                return;
            }
            
            try {
                const response = await fetch(`/Sistema-de-ventas-AppLink-main/src/Controllers/VentaControllerDashboard.php?accion=eliminar&id=${id}`, {
                    method: 'DELETE'
                });
                const data = await response.json();
                
                if (data.success) {
                    alert('Venta eliminada exitosamente');
                    cargarVentas(paginaActual, document.getElementById('buscarVenta').value);
                    // Actualizar gráficos
                    refrescarDatos();
                } else {
                    alert('Error al eliminar: ' + data.error);
                }
            } catch (error) {
                console.error('Error eliminando venta:', error);
                alert('Error de conexión al eliminar la venta');
            }
        }

        // Cancelar formulario
        function cancelarFormulario() {
            document.getElementById('formularioVenta').style.display = 'none';
            ventaEditando = null;
        }

        // Buscar ventas
        function buscarVentas() {
            const busqueda = document.getElementById('buscarVenta').value;
            cargarVentas(1, busqueda);
        }

        // Calcular total automáticamente
        function calcularTotal() {
            const cantidad = parseFloat(document.getElementById('cantidadVenta').value) || 0;
            const precio = parseFloat(document.getElementById('precioVenta').value) || 0;
            const total = cantidad * precio;
            document.getElementById('totalCalculado').textContent = '$' + formatearNumero(total);
        }

        // Event listeners para calcular total automáticamente
        document.getElementById('cantidadVenta').addEventListener('input', calcularTotal);
        document.getElementById('precioVenta').addEventListener('input', calcularTotal);

        // Buscar con Enter
        document.getElementById('buscarVenta').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                buscarVentas();
            }
        });

        // Manejar envío del formulario
        document.getElementById('formVenta').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            
            try {
                let url, method;
                
                if (ventaEditando) {
                    url = `/Sistema-de-ventas-AppLink-main/src/Controllers/VentaControllerDashboard.php?accion=actualizar&id=${ventaEditando}`;
                    method = 'PUT';
                } else {
                    url = `/Sistema-de-ventas-AppLink-main/src/Controllers/VentaControllerDashboard.php?accion=crear`;
                    method = 'POST';
                }
                
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert(result.mensaje || 'Venta guardada exitosamente');
                    cancelarFormulario();
                    cargarVentas(paginaActual, document.getElementById('buscarVenta').value);
                    // Actualizar gráficos
                    refrescarDatos();
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                console.error('Error guardando venta:', error);
                alert('Error de conexión al guardar la venta');
            }
        });
    </script>
</body>
</html>
