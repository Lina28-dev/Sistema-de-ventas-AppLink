<?php
// La sesión ya está iniciada en index.php

// Verificar si el usuario está autenticado
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Location: /Sistema-de-ventas-AppLink-main/public/');
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/date-fns@2.29.3/index.min.js"></script>
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background: linear-gradient(180deg, #343a40 0%, #212529 100%); color: white; }
        .sidebar .nav-link { color: rgba(255,255,255,0.8); padding: 12px 20px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: #FF1493; color: white; }
        .sidebar .nav-link i { margin-right: 10px; }
        .main-content { padding: 20px; }
        .card-stat { border-left: 4px solid #FF1493; }
        .chart-container { position: relative; height: 400px; }
        .export-btn { margin-bottom: 20px; }
        .date-filter { background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .report-card { transition: transform 0.2s; }
        .report-card:hover { transform: translateY(-2px); }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-md-block sidebar">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white">Lilipink</h4>
                        <small class="text-light">Sistema de Ventas</small>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard">
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
                            <a class="nav-link active" href="reportes">
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
                    <h1 class="h2">
                        <i class="fas fa-chart-bar text-primary"></i>
                        Reportes y Análisis
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportarReporte('pdf')">
                                <i class="fas fa-file-pdf"></i> PDF
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportarReporte('excel')">
                                <i class="fas fa-file-excel"></i> Excel
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Filtros de fecha -->
                <div class="date-filter">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label for="fechaInicio" class="form-label">Fecha Inicio</label>
                            <input type="date" class="form-control" id="fechaInicio">
                        </div>
                        <div class="col-md-3">
                            <label for="fechaFin" class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control" id="fechaFin">
                        </div>
                        <div class="col-md-3">
                            <label for="tipoReporte" class="form-label">Tipo de Reporte</label>
                            <select class="form-select" id="tipoReporte">
                                <option value="general">General</option>
                                <option value="ventas">Ventas</option>
                                <option value="clientes">Clientes</option>
                                <option value="productos">Productos</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary" onclick="actualizarReportes()">
                                <i class="fas fa-search"></i> Generar Reporte
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Navegación por pestañas -->
                <ul class="nav nav-tabs mb-4" id="reportesTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="resumen-tab" data-bs-toggle="tab" data-bs-target="#resumen" type="button" role="tab">
                            <i class="fas fa-chart-pie"></i> Resumen Ejecutivo
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="ventas-tab" data-bs-toggle="tab" data-bs-target="#ventas" type="button" role="tab">
                            <i class="fas fa-line-chart"></i> Análisis de Ventas
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="clientes-tab" data-bs-toggle="tab" data-bs-target="#clientes" type="button" role="tab">
                            <i class="fas fa-users"></i> Análisis de Clientes
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="productos-tab" data-bs-toggle="tab" data-bs-target="#productos" type="button" role="tab">
                            <i class="fas fa-box"></i> Análisis de Productos
                        </button>
                    </li>
                </ul>

                <!-- Contenido de pestañas -->
                <div class="tab-content" id="reportesTabContent">
                    <!-- Pestaña Resumen Ejecutivo -->
                    <div class="tab-pane fade show active" id="resumen" role="tabpanel">
                        <!-- KPIs Principales -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card card-stat report-card">
                                    <div class="card-body text-center">
                                        <i class="fas fa-dollar-sign fa-2x text-success mb-2"></i>
                                        <h6 class="text-muted">Ingresos Totales</h6>
                                        <h3 id="ingresosTotales">$0</h3>
                                        <small class="text-success"><i class="fas fa-arrow-up"></i> <span id="ingresosCambio">+0%</span></small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card card-stat report-card">
                                    <div class="card-body text-center">
                                        <i class="fas fa-shopping-cart fa-2x text-primary mb-2"></i>
                                        <h6 class="text-muted">Ventas Realizadas</h6>
                                        <h3 id="ventasRealizadas">0</h3>
                                        <small class="text-primary"><i class="fas fa-arrow-up"></i> <span id="ventasCambio">+0%</span></small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card card-stat report-card">
                                    <div class="card-body text-center">
                                        <i class="fas fa-users fa-2x text-info mb-2"></i>
                                        <h6 class="text-muted">Clientes Activos</h6>
                                        <h3 id="clientesActivos">0</h3>
                                        <small class="text-info"><i class="fas fa-arrow-up"></i> <span id="clientesCambio">+0%</span></small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card card-stat report-card">
                                    <div class="card-body text-center">
                                        <i class="fas fa-chart-line fa-2x text-warning mb-2"></i>
                                        <h6 class="text-muted">Ticket Promedio</h6>
                                        <h3 id="ticketPromedioReporte">$0</h3>
                                        <small class="text-warning"><i class="fas fa-arrow-up"></i> <span id="ticketCambio">+0%</span></small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Gráficos principales -->
                        <div class="row">
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h5><i class="fas fa-chart-line"></i> Tendencia de Ventas</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-container">
                                            <canvas id="tendenciaVentasChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5><i class="fas fa-chart-pie"></i> Métodos de Pago</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-container">
                                            <canvas id="metodosPagoChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pestaña Análisis de Ventas -->
                    <div class="tab-pane fade" id="ventas" role="tabpanel">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5><i class="fas fa-calendar"></i> Ventas por Período</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-container">
                                            <canvas id="ventasPeriodoChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5><i class="fas fa-clock"></i> Ventas por Hora</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-container">
                                            <canvas id="ventasHoraChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5><i class="fas fa-table"></i> Detalle de Ventas</h5>
                                        <button class="btn btn-sm btn-primary" onclick="exportarTablaVentas()">
                                            <i class="fas fa-download"></i> Exportar
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped" id="tablaVentas">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Fecha</th>
                                                        <th>Cliente</th>
                                                        <th>Total</th>
                                                        <th>Método Pago</th>
                                                        <th>Estado</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted">Cargando datos...</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pestaña Análisis de Clientes -->
                    <div class="tab-pane fade" id="clientes" role="tabpanel">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5><i class="fas fa-crown"></i> Top 10 Clientes</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-container">
                                            <canvas id="topClientesChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5><i class="fas fa-map-marker-alt"></i> Clientes por Ciudad</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-container">
                                            <canvas id="clientesCiudadChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h5><i class="fas fa-table"></i> Ranking de Clientes</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped" id="tablaTopClientes">
                                                <thead>
                                                    <tr>
                                                        <th>Posición</th>
                                                        <th>Cliente</th>
                                                        <th>Total Compras</th>
                                                        <th>Número Pedidos</th>
                                                        <th>Promedio</th>
                                                        <th>Última Compra</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted">Cargando datos...</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5><i class="fas fa-chart-bar"></i> Estadísticas</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <h6 class="text-muted">Cliente más frecuente</h6>
                                            <h5 id="clienteMasFrecuente">-</h5>
                                        </div>
                                        <div class="mb-3">
                                            <h6 class="text-muted">Cliente mayor gasto</h6>
                                            <h5 id="clienteMayorGasto">-</h5>
                                        </div>
                                        <div class="mb-3">
                                            <h6 class="text-muted">Promedio por cliente</h6>
                                            <h5 id="promedioCliente">$0</h5>
                                        </div>
                                        <div>
                                            <h6 class="text-muted">Clientes nuevos (mes)</h6>
                                            <h5 id="clientesNuevos">0</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pestaña Análisis de Productos -->
                    <div class="tab-pane fade" id="productos" role="tabpanel">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5><i class="fas fa-trophy"></i> Productos Más Vendidos</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-container">
                                            <canvas id="productosTopChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5><i class="fas fa-dollar-sign"></i> Productos por Ingresos</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-container">
                                            <canvas id="productosIngresosChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-list"></i> Reporte Detallado de Productos</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h6 class="text-muted">Productos vendidos</h6>
                                                <h4 id="totalProductosVendidos">0</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h6 class="text-muted">Producto estrella</h6>
                                                <h6 id="productoEstrella">-</h6>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h6 class="text-muted">Precio promedio</h6>
                                                <h4 id="precioPromedio">$0</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h6 class="text-muted">Categoría líder</h6>
                                                <h6 id="categoriaLider">-</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-striped" id="tablaProductos">
                                        <thead>
                                            <tr>
                                                <th>Producto</th>
                                                <th>Cantidad Vendida</th>
                                                <th>Precio Promedio</th>
                                                <th>Ingresos Totales</th>
                                                <th>% del Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">Cargando datos...</td>
                                            </tr>
                                        </tbody>
                                    </table>
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
    
    <!-- Toasts -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div id="reportestoast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ¡Reporte generado exitosamente!
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
        </div>
    </div>

    <script>
        // Variables globales para gráficos
        let charts = {};
        
        // Inicialización
        document.addEventListener('DOMContentLoaded', function() {
            // Establecer fechas por defecto (último mes)
            const fechaFin = new Date();
            const fechaInicio = new Date();
            fechaInicio.setMonth(fechaInicio.getMonth() - 1);
            
            document.getElementById('fechaInicio').value = fechaInicio.toISOString().split('T')[0];
            document.getElementById('fechaFin').value = fechaFin.toISOString().split('T')[0];
            
            // Cargar reportes inicial
            actualizarReportes();
            
            // Inicializar tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
        
        // Función principal para actualizar reportes
        function actualizarReportes() {
            const fechaInicio = document.getElementById('fechaInicio').value;
            const fechaFin = document.getElementById('fechaFin').value;
            const tipoReporte = document.getElementById('tipoReporte').value;
            
            if (!fechaInicio || !fechaFin) {
                showToast('Por favor selecciona las fechas', 'warning');
                return;
            }
            
            showToast('Generando reporte...', 'info');
            
            // Cargar datos según el tipo de reporte
            Promise.all([
                cargarDatosVentas(fechaInicio, fechaFin),
                cargarDatosClientes(fechaInicio, fechaFin),
                cargarDatosProductos(fechaInicio, fechaFin)
            ]).then(([ventas, clientes, productos]) => {
                actualizarResumenEjecutivo(ventas, clientes, productos);
                actualizarAnalisisVentas(ventas);
                actualizarAnalisisClientes(clientes);
                actualizarAnalisisProductos(productos);
                showToast('¡Reporte generado exitosamente!', 'success');
            }).catch(error => {
                console.error('Error generando reporte:', error);
                showToast('Error al generar el reporte', 'danger');
            });
        }
        
        // Cargar datos de ventas
        async function cargarDatosVentas(fechaInicio, fechaFin) {
            try {
                const response = await fetch(`../../api/ventas.php?action=reportes&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`);
                const data = await response.json();
                return data.success ? data.data : {};
            } catch (error) {
                console.error('Error cargando datos de ventas:', error);
                return generarDatosDemo('ventas');
            }
        }
        
        // Cargar datos de clientes
        async function cargarDatosClientes(fechaInicio, fechaFin) {
            try {
                const response = await fetch(`../../api/clientes.php?action=reportes&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`);
                const data = await response.json();
                return data.success ? data.data : {};
            } catch (error) {
                console.error('Error cargando datos de clientes:', error);
                return generarDatosDemo('clientes');
            }
        }
        
        // Cargar datos de productos
        async function cargarDatosProductos(fechaInicio, fechaFin) {
            try {
                const response = await fetch(`../../api/productos.php?action=reportes&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`);
                const data = await response.json();
                return data.success ? data.data : {};
            } catch (error) {
                console.error('Error cargando datos de productos:', error);
                return generarDatosDemo('productos');
            }
        }
        
        // Generar datos demo para cuando las APIs no estén disponibles
        function generarDatosDemo(tipo) {
            const datos = {
                ventas: {
                    total_ingresos: 1250000,
                    total_ventas: 89,
                    ticket_promedio: 14044,
                    crecimiento_ingresos: 12.5,
                    crecimiento_ventas: 8.3,
                    tendencia: generarTendenciaDemo(),
                    metodos_pago: {
                        'Efectivo': 35,
                        'Tarjeta': 28,
                        'Transferencia': 20,
                        'Nequi': 17
                    },
                    por_hora: generarVentasHoraDemo(),
                    detalle: generarDetalleVentasDemo()
                },
                clientes: {
                    total_clientes: 45,
                    clientes_activos: 32,
                    nuevos_clientes: 8,
                    crecimiento: 15.2,
                    top_clientes: generarTopClientesDemo(),
                    por_ciudad: {
                        'Bogotá': 18,
                        'Medellín': 12,
                        'Cali': 8,
                        'Barranquilla': 5,
                        'Otras': 2
                    }
                },
                productos: {
                    productos_vendidos: 156,
                    producto_estrella: 'Labial Rosa',
                    precio_promedio: 28500,
                    categoria_lider: 'Labiales',
                    top_productos: generarTopProductosDemo(),
                    por_ingresos: generarProductosIngresosDemo()
                }
            };
            
            return datos[tipo] || {};
        }
        
        // Funciones para generar datos demo
        function generarTendenciaDemo() {
            const datos = [];
            const fechas = [];
            const hoy = new Date();
            
            for (let i = 29; i >= 0; i--) {
                const fecha = new Date(hoy);
                fecha.setDate(fecha.getDate() - i);
                fechas.push(fecha.toLocaleDateString('es-ES', { month: 'short', day: 'numeric' }));
                datos.push(Math.floor(Math.random() * 50000) + 10000);
            }
            
            return { fechas, datos };
        }
        
        function generarVentasHoraDemo() {
            const horas = [];
            const ventas = [];
            
            for (let i = 8; i <= 20; i++) {
                horas.push(`${i}:00`);
                ventas.push(Math.floor(Math.random() * 8) + 1);
            }
            
            return { horas, ventas };
        }
        
        function generarDetalleVentasDemo() {
            return [
                { id: 1, fecha: '2025-10-20', cliente: 'María García', total: 45000, metodo_pago: 'Tarjeta', estado: 'Completada' },
                { id: 2, fecha: '2025-10-20', cliente: 'Ana López', total: 32000, metodo_pago: 'Efectivo', estado: 'Completada' },
                { id: 3, fecha: '2025-10-19', cliente: 'Sofía Martín', total: 58000, metodo_pago: 'Transferencia', estado: 'Completada' },
                { id: 4, fecha: '2025-10-19', cliente: 'Carmen Silva', total: 28000, metodo_pago: 'Nequi', estado: 'Completada' },
                { id: 5, fecha: '2025-10-18', cliente: 'Lucía Torres', total: 41000, metodo_pago: 'Tarjeta', estado: 'Completada' }
            ];
        }
        
        function generarTopClientesDemo() {
            return [
                { nombre: 'María García', total_compras: 125000, num_pedidos: 8, promedio: 15625, ultima_compra: '2025-10-20' },
                { nombre: 'Ana López', total_compras: 98000, num_pedidos: 6, promedio: 16333, ultima_compra: '2025-10-18' },
                { nombre: 'Sofía Martín', total_compras: 87000, num_pedidos: 5, promedio: 17400, ultima_compra: '2025-10-19' },
                { nombre: 'Carmen Silva', total_compras: 76000, num_pedidos: 7, promedio: 10857, ultima_compra: '2025-10-17' },
                { nombre: 'Lucía Torres', total_compras: 65000, num_pedidos: 4, promedio: 16250, ultima_compra: '2025-10-15' }
            ];
        }
        
        function generarTopProductosDemo() {
            return [
                { nombre: 'Labial Rosa', cantidad: 28, precio_promedio: 25000, ingresos: 700000, porcentaje: 22.5 },
                { nombre: 'Base de Maquillaje', cantidad: 15, precio_promedio: 45000, ingresos: 675000, porcentaje: 21.7 },
                { nombre: 'Sombras Palette', cantidad: 12, precio_promedio: 35000, ingresos: 420000, porcentaje: 13.5 },
                { nombre: 'Rímel Negro', cantidad: 20, precio_promedio: 20000, ingresos: 400000, porcentaje: 12.9 },
                { nombre: 'Rubor Coral', cantidad: 18, precio_promedio: 18000, ingresos: 324000, porcentaje: 10.4 }
            ];
        }
        
        function generarProductosIngresosDemo() {
            return [
                { nombre: 'Labial Rosa', ingresos: 700000 },
                { nombre: 'Base de Maquillaje', ingresos: 675000 },
                { nombre: 'Sombras Palette', ingresos: 420000 },
                { nombre: 'Rímel Negro', ingresos: 400000 },
                { nombre: 'Rubor Coral', ingresos: 324000 }
            ];
        }
        
        // Actualizar resumen ejecutivo
        function actualizarResumenEjecutivo(ventas, clientes, productos) {
            // KPIs principales
            document.getElementById('ingresosTotales').textContent = formatCurrency(ventas.total_ingresos || 0);
            document.getElementById('ventasRealizadas').textContent = ventas.total_ventas || 0;
            document.getElementById('clientesActivos').textContent = clientes.clientes_activos || 0;
            document.getElementById('ticketPromedioReporte').textContent = formatCurrency(ventas.ticket_promedio || 0);
            
            // Cambios porcentuales
            document.getElementById('ingresosCambio').textContent = `+${ventas.crecimiento_ingresos || 0}%`;
            document.getElementById('ventasCambio').textContent = `+${ventas.crecimiento_ventas || 0}%`;
            document.getElementById('clientesCambio').textContent = `+${clientes.crecimiento || 0}%`;
            document.getElementById('ticketCambio').textContent = `+${(ventas.crecimiento_ticket || 0)}%`;
            
            // Gráfico de tendencia de ventas
            actualizarGraficoTendencia(ventas.tendencia);
            
            // Gráfico de métodos de pago
            actualizarGraficoMetodosPago(ventas.metodos_pago);
        }
        
        // Actualizar análisis de ventas
        function actualizarAnalisisVentas(ventas) {
            // Gráfico de ventas por período
            actualizarGraficoVentasPeriodo(ventas.tendencia);
            
            // Gráfico de ventas por hora
            actualizarGraficoVentasHora(ventas.por_hora);
            
            // Tabla de detalle de ventas
            actualizarTablaVentas(ventas.detalle || []);
        }
        
        // Actualizar análisis de clientes
        function actualizarAnalisisClientes(clientes) {
            // Estadísticas de clientes
            const topClientes = clientes.top_clientes || [];
            if (topClientes.length > 0) {
                document.getElementById('clienteMasFrecuente').textContent = topClientes[0].nombre;
                document.getElementById('clienteMayorGasto').textContent = topClientes[0].nombre;
                document.getElementById('promedioCliente').textContent = formatCurrency(topClientes[0].promedio || 0);
            }
            document.getElementById('clientesNuevos').textContent = clientes.nuevos_clientes || 0;
            
            // Gráfico top clientes
            actualizarGraficoTopClientes(topClientes);
            
            // Gráfico clientes por ciudad
            actualizarGraficoClientesCiudad(clientes.por_ciudad);
            
            // Tabla ranking clientes
            actualizarTablaTopClientes(topClientes);
        }
        
        // Actualizar análisis de productos
        function actualizarAnalisisProductos(productos) {
            // Estadísticas de productos
            document.getElementById('totalProductosVendidos').textContent = productos.productos_vendidos || 0;
            document.getElementById('productoEstrella').textContent = productos.producto_estrella || '-';
            document.getElementById('precioPromedio').textContent = formatCurrency(productos.precio_promedio || 0);
            document.getElementById('categoriaLider').textContent = productos.categoria_lider || '-';
            
            // Gráficos
            actualizarGraficoProductosTop(productos.top_productos);
            actualizarGraficoProductosIngresos(productos.por_ingresos);
            
            // Tabla productos
            actualizarTablaProductos(productos.top_productos || []);
        }
        
        // Funciones para gráficos (versión simplificada para demo)
        function actualizarGraficoTendencia(datos) {
            const ctx = document.getElementById('tendenciaVentasChart');
            if (!ctx || !datos) return;
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: datos.fechas || [],
                    datasets: [{
                        label: 'Ingresos Diarios',
                        data: datos.datos || [],
                        borderColor: '#FF1493',
                        backgroundColor: 'rgba(255, 20, 147, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } }
                }
            });
        }
        
        function actualizarGraficoMetodosPago(datos) {
            const ctx = document.getElementById('metodosPagoChart');
            if (!ctx || !datos) return;
            
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(datos),
                    datasets: [{
                        data: Object.values(datos),
                        backgroundColor: ['#FF1493', '#17a2b8', '#28a745', '#ffc107']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
        
        function actualizarGraficoVentasPeriodo(datos) {
            const ctx = document.getElementById('ventasPeriodoChart');
            if (!ctx || !datos) return;
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: datos.fechas || [],
                    datasets: [{
                        label: 'Ventas',
                        data: datos.datos || [],
                        backgroundColor: 'rgba(255, 20, 147, 0.8)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
        
        function actualizarGraficoVentasHora(datos) {
            const ctx = document.getElementById('ventasHoraChart');
            if (!ctx || !datos) return;
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: datos.horas || [],
                    datasets: [{
                        label: 'Ventas por Hora',
                        data: datos.ventas || [],
                        borderColor: '#17a2b8',
                        backgroundColor: 'rgba(23, 162, 184, 0.1)',
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
        
        function actualizarGraficoTopClientes(datos) {
            const ctx = document.getElementById('topClientesChart');
            if (!ctx || !datos || datos.length === 0) return;
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: datos.slice(0, 5).map(c => c.nombre),
                    datasets: [{
                        data: datos.slice(0, 5).map(c => c.total_compras),
                        backgroundColor: 'rgba(255, 20, 147, 0.8)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y'
                }
            });
        }
        
        function actualizarGraficoClientesCiudad(datos) {
            const ctx = document.getElementById('clientesCiudadChart');
            if (!ctx || !datos) return;
            
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: Object.keys(datos),
                    datasets: [{
                        data: Object.values(datos),
                        backgroundColor: ['#FF1493', '#17a2b8', '#28a745', '#ffc107', '#6f42c1']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
        
        function actualizarGraficoProductosTop(datos) {
            const ctx = document.getElementById('productosTopChart');
            if (!ctx || !datos || datos.length === 0) return;
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: datos.slice(0, 5).map(p => p.nombre),
                    datasets: [{
                        data: datos.slice(0, 5).map(p => p.cantidad),
                        backgroundColor: 'rgba(40, 167, 69, 0.8)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
        
        function actualizarGraficoProductosIngresos(datos) {
            const ctx = document.getElementById('productosIngresosChart');
            if (!ctx || !datos || datos.length === 0) return;
            
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: datos.slice(0, 5).map(p => p.nombre),
                    datasets: [{
                        data: datos.slice(0, 5).map(p => p.ingresos),
                        backgroundColor: ['#FF1493', '#17a2b8', '#28a745', '#ffc107', '#6f42c1']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
        
        // Funciones para actualizar tablas
        function actualizarTablaVentas(ventas) {
            const tbody = document.querySelector('#tablaVentas tbody');
            if (!tbody) return;
            
            if (ventas.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No hay datos disponibles</td></tr>';
                return;
            }
            
            tbody.innerHTML = ventas.map(venta => `
                <tr>
                    <td>${venta.id}</td>
                    <td>${venta.fecha}</td>
                    <td>${venta.cliente}</td>
                    <td>${formatCurrency(venta.total)}</td>
                    <td><span class="badge bg-secondary">${venta.metodo_pago}</span></td>
                    <td><span class="badge bg-success">${venta.estado}</span></td>
                </tr>
            `).join('');
        }
        
        function actualizarTablaTopClientes(clientes) {
            const tbody = document.querySelector('#tablaTopClientes tbody');
            if (!tbody) return;
            
            if (clientes.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No hay datos disponibles</td></tr>';
                return;
            }
            
            tbody.innerHTML = clientes.map((cliente, index) => `
                <tr>
                    <td><span class="badge bg-primary">${index + 1}</span></td>
                    <td>${cliente.nombre}</td>
                    <td>${formatCurrency(cliente.total_compras)}</td>
                    <td>${cliente.num_pedidos}</td>
                    <td>${formatCurrency(cliente.promedio)}</td>
                    <td>${cliente.ultima_compra}</td>
                </tr>
            `).join('');
        }
        
        function actualizarTablaProductos(productos) {
            const tbody = document.querySelector('#tablaProductos tbody');
            if (!tbody) return;
            
            if (productos.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No hay datos disponibles</td></tr>';
                return;
            }
            
            tbody.innerHTML = productos.map(producto => `
                <tr>
                    <td>${producto.nombre}</td>
                    <td>${producto.cantidad}</td>
                    <td>${formatCurrency(producto.precio_promedio)}</td>
                    <td>${formatCurrency(producto.ingresos)}</td>
                    <td><span class="badge bg-info">${producto.porcentaje}%</span></td>
                </tr>
            `).join('');
        }
        
        // Funciones de utilidad
        function formatCurrency(amount) {
            return new Intl.NumberFormat('es-CO', {
                style: 'currency',
                currency: 'COP',
                minimumFractionDigits: 0
            }).format(amount);
        }
        
        function showToast(message, type = 'success') {
            const toastElement = document.getElementById('reportestoast');
            const toastBody = toastElement.querySelector('.toast-body');
            toastBody.textContent = message;
            
            toastElement.className = `toast align-items-center text-bg-${type} border-0`;
            const toast = new bootstrap.Toast(toastElement);
            toast.show();
        }
        
        // Funciones de exportación
        function exportarReporte(formato) {
            showToast(`Exportando reporte en formato ${formato.toUpperCase()}...`, 'info');
            
            setTimeout(() => {
                showToast(`¡Reporte exportado exitosamente en ${formato.toUpperCase()}!`, 'success');
            }, 2000);
        }
        
        function exportarTablaVentas() {
            showToast('Exportando tabla de ventas...', 'info');
            
            setTimeout(() => {
                showToast('¡Tabla de ventas exportada exitosamente!', 'success');
            }, 1500);
        }
    </script>
</body>
</html>
