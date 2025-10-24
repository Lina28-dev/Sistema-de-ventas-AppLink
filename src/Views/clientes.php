<?php
// Iniciar sesión solo si no está activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Location: ../../public/index.php');
    exit;
}

// Función para mostrar el tipo de usuario
function getUserType() {
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) return 'Administrador';
    if (isset($_SESSION['is_medium']) && $_SESSION['is_medium']) return 'Usuario Medio';
    if (isset($_SESSION['is_visitor']) && $_SESSION['is_visitor']) return 'Visitante';
    return 'Usuario';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Gestión de Clientes - AppLink</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Sidebar CSS -->
    <link href="css/sidebar.css" rel="stylesheet">
    <!-- Sistema de Temas -->
    <link href="css/theme-system.css" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }
        body { 
            background-color: #f8f9fa; 
            font-size: 0.875rem;
        }
        
        /* Sidebar Styles */
        .sidebar {
            background: linear-gradient(135deg, #FF1493 0%, #9932CC 100%);
            min-height: 100vh;
            padding: 0;
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .sidebar-content {
            padding: 20px;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .logo-container {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 15px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1.2rem;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .sidebar-nav {
            flex-grow: 1;
        }
        
        .nav-item {
            display: block;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 5px;
            transition: all 0.3s ease;
        }
        
        .nav-item:hover, .nav-item.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
        }
        
        .nav-item i {
            margin-right: 10px;
            width: 20px;
        }
        
        .sidebar-bottom {
            margin-top: auto;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .logout {
            color: rgba(255, 255, 255, 0.6) !important;
        }
        
        .logout:hover {
            background: rgba(255, 0, 0, 0.2);
            color: white !important;
        }
        
        /* Main content adjustment */
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        
        /* Mobile Toggle */
        .mobile-toggle {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            background: #FF1493;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px;
            z-index: 1001;
            font-size: 16px;
        }
        
        /* Cards y estadísticas */
        .card-stat { 
            border-left: 4px solid #FF1493;
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-radius: 0.75rem;
        }
        
        .welcome-card {
            background: linear-gradient(135deg, #FF1493 0%, #FF69B4 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 15px rgba(255, 20, 147, 0.3);
        }
        
        .welcome-card i {
            font-size: 2.5rem;
            margin-right: 20px;
            opacity: 0.9;
        }
        
        .page-title {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            color: #FF1493;
        }
        
        .page-title i {
            margin-right: 15px;
        }
        
        /* Botones */
        .btn-pink { 
            background: linear-gradient(135deg, #FF1493, #FF69B4);
            border: none;
            color: white;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-pink:hover { 
            background: linear-gradient(135deg, #E91E63, #FF1493);
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(255, 20, 147, 0.3);
        }
        
        /* Tarjetas de clientes mejoradas */
        .cliente-card {
            transition: all 0.3s ease;
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            position: relative;
        }
        
        .cliente-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(255, 20, 147, 0.2);
        }
        
        .cliente-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #FF1493, #FF69B4);
        }
        
        .cliente-avatar {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #FF1493, #FF69B4);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.5rem;
            margin: 0 auto 15px;
            box-shadow: 0 4px 15px rgba(255, 20, 147, 0.3);
        }
        
        .categoria-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 20px;
            padding: 4px 10px;
        }
        
        .categoria-vip {
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: #333;
        }
        
        .categoria-regular {
            background: linear-gradient(135deg, #17a2b8, #138496);
            color: white;
        }
        
        .categoria-nuevo {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }
        
        .descuento-badge {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            font-weight: 600;
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 0.75rem;
        }
        
        /* Filtros avanzados */
        .filtros-avanzados {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
        }
        
        .filtro-chip {
            display: inline-block;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 20px;
            padding: 6px 15px;
            margin: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.85rem;
        }
        
        .filtro-chip:hover {
            background: #FF1493;
            color: white;
            border-color: #FF1493;
        }
        
        .filtro-chip.active {
            background: #FF1493;
            color: white;
            border-color: #FF1493;
        }
        
        /* Buscador mejorado */
        .search-container {
            position: relative;
        }
        
        .search-container .form-control {
            border-radius: 25px;
            padding-left: 45px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .search-container .form-control:focus {
            border-color: #FF1493;
            box-shadow: 0 0 0 0.2rem rgba(255, 20, 147, 0.25);
        }
        
        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            z-index: 5;
        }
        
        /* Tabla mejorada */
        .table-modern {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .table-modern thead {
            background: linear-gradient(135deg, #FF1493, #FF69B4);
            color: white;
        }
        
        .table-modern tbody tr {
            transition: all 0.3s ease;
        }
        
        .table-modern tbody tr:hover {
            background-color: rgba(255, 20, 147, 0.05);
        }
        
        /* Estadísticas mejoradas */
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px 20px;
            text-align: center;
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #FF1493, #FF69B4);
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.5rem;
            color: white;
        }
        
        .stat-icon.total { background: linear-gradient(135deg, #FF1493, #FF69B4); }
        .stat-icon.historial { background: linear-gradient(135deg, #17a2b8, #138496); }
        .stat-icon.nuevos { background: linear-gradient(135deg, #28a745, #20c997); }
        .stat-icon.activos { background: linear-gradient(135deg, #ffc107, #fd7e14); }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #6c757d;
            font-weight: 500;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .mobile-toggle {
                display: block;
            }
            
            .main-content {
                margin-left: 0;
                padding: 15px;
                padding-top: 60px;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
            
            .welcome-card {
                flex-direction: column;
                text-align: center;
                padding: 15px;
            }
            
            .welcome-card i {
                margin-right: 0;
                margin-bottom: 10px;
            }
            
            .cliente-avatar {
                width: 50px;
                height: 50px;
                font-size: 1.2rem;
            }
            
            .stat-number {
                font-size: 2rem;
            }
            
            .filtros-avanzados {
                padding: 15px;
            }
        }
        
        @media (max-width: 576px) {
            .main-content {
                padding: 10px;
                padding-top: 60px;
            }
            
            .page-title {
                font-size: 1.25rem;
            }
            
            .stat-number {
                font-size: 1.5rem;
            }
            
            .cliente-avatar {
                width: 45px;
                height: 45px;
                font-size: 1rem;
            }
        }
        
        /* Animaciones */
        .fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .stagger-animation {
            animation-delay: calc(var(--delay) * 0.1s);
        }
        
        /* Loading states */
        .loading-skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: 4px;
        }
        
        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
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
                $activePage = 'clientes';
                $sidebarPath = __DIR__ . '/partials/sidebar.php';
                if (!file_exists($sidebarPath)) {
                    $sidebarPath = __DIR__ . '/../Views/partials/sidebar.php';
                }
                if (file_exists($sidebarPath)) {
                    include $sidebarPath;
                } else {
                    // Fallback sidebar básico
                    echo '<div class="col-md-2 sidebar">
                        <div class="sidebar-content">
                            <div class="logo-section text-center mb-4">
                                <div class="logo-container">AppLink</div>
                            </div>
                            <nav class="sidebar-nav">
                                <a href="dashboard" class="nav-item">
                                    <i class="fas fa-home"></i> Dashboard
                                </a>
                                <a href="ventas" class="nav-item">
                                    <i class="fas fa-shopping-cart"></i> Ventas
                                </a>
                                <a href="clientes" class="nav-item active">
                                    <i class="fas fa-users"></i> Clientes
                                </a>
                                <a href="pedidos" class="nav-item">
                                    <i class="fas fa-clipboard-list"></i> Pedidos
                                </a>
                                <a href="usuarios" class="nav-item">
                                    <i class="fas fa-user-cog"></i> Usuarios
                                </a>
                                <div class="sidebar-bottom">
                                    <a href="logout" class="nav-item logout">
                                        <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                                    </a>
                                </div>
                            </div>
                        </div>';
                }
            ?>

            <!-- Main content -->
            <main class="col-md-10 px-4 main-content">
                <!-- Header de la página -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="page-title">
                        <i class="fas fa-users"></i>
                        Gestión de Clientes
                    </div>
                    <div>
                        <button class="btn btn-success btn-sm me-2" onclick="exportarClientes()" title="Exportar clientes">
                            <i class="fas fa-file-excel"></i> Exportar
                        </button>
                        <button class="btn btn-outline-primary btn-sm" onclick="refrescarDatos()" title="Actualizar datos">
                            <i class="fas fa-sync-alt"></i> Actualizar
                        </button>
                    </div>
                </div>

                <!-- Tarjeta de bienvenida -->
                <div class="welcome-card fade-in-up">
                    <i class="fas fa-users-cog"></i>
                    <div>
                        <h4 class="mb-1">Centro de Gestión de Clientes</h4>
                        <p class="mb-0">Administra tu base de clientes, segmentación y categorías</p>
                    </div>
                </div>

                <!-- Estadísticas principales -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card fade-in-up" style="--delay: 1">
                            <div class="stat-icon total">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-number" id="totalClientes">0</div>
                            <div class="stat-label">Total Clientes</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card fade-in-up" style="--delay: 2">
                            <div class="stat-icon historial">
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="stat-number" id="clientesVIP">0</div>
                            <div class="stat-label">Clientes VIP</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card fade-in-up" style="--delay: 3">
                            <div class="stat-icon nuevos">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="stat-number" id="nuevosMes">0</div>
                            <div class="stat-label">Nuevos Este Mes</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card fade-in-up" style="--delay: 4">
                            <div class="stat-icon activos">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="stat-number" id="clientesActivos">0</div>
                            <div class="stat-label">Activos</div>
                        </div>
                    </div>
                </div>
                <!-- Filtros avanzados -->
                <div class="filtros-avanzados fade-in-up" style="--delay: 5">
                    <div class="row align-items-center">
                        <!-- Buscador -->
                        <div class="col-lg-6 col-md-12 mb-3">
                            <div class="search-container">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" class="form-control" id="buscarCliente" 
                                       placeholder="Buscar por nombres, apellidos, cédula o teléfono..." 
                                       onkeyup="buscarClienteEnTiempoReal()" 
                                       onkeypress="if(event.key==='Enter')buscarCliente()">
                            </div>
                        </div>
                        
                        <!-- Controles de vista y acciones -->
                        <div class="col-lg-6 col-md-12 mb-3">
                            <div class="d-flex justify-content-end gap-2">
                                <!-- Filtro por categoría -->
                                <select class="form-select" id="filtroCategoria" onchange="filtrarPorCategoria()" style="max-width: 150px;">
                                    <option value="">Todas las categorías</option>
                                    <option value="VIP">VIP</option>
                                    <option value="Regular">Regular</option>
                                    <option value="Nuevo">Nuevo</option>
                                </select>
                                
                                <!-- Botones de vista -->
                                <div class="btn-group" role="group">
                                    <button class="btn btn-outline-secondary" id="vistaTabla" onclick="cambiarVista('tabla')" title="Vista Tabla">
                                        <i class="fas fa-list"></i>
                                    </button>
                                    <button class="btn btn-pink" id="vistaTarjetas" onclick="cambiarVista('tarjetas')" title="Vista Tarjetas">
                                        <i class="fas fa-th-large"></i>
                                    </button>
                                </div>
                                
                                <!-- Botón nuevo cliente -->
                                <button class="btn btn-pink" onclick="mostrarModalNuevoCliente()">
                                    <i class="fas fa-user-plus"></i> Nuevo Cliente
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Filtros por chips -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex flex-wrap align-items-center">
                                <span class="me-3 text-muted fw-500">Filtros rápidos:</span>
                                <span class="filtro-chip" data-filtro="todos" onclick="aplicarFiltroRapido('todos')">
                                    <i class="fas fa-users me-1"></i> Todos
                                </span>
                                <span class="filtro-chip" data-filtro="con_descuento" onclick="aplicarFiltroRapido('con_descuento')">
                                    <i class="fas fa-percent me-1"></i> Con Descuento
                                </span>
                                <span class="filtro-chip" data-filtro="vip" onclick="aplicarFiltroRapido('vip')">
                                    <i class="fas fa-crown me-1"></i> VIP
                                </span>
                                <span class="filtro-chip" data-filtro="nuevos" onclick="aplicarFiltroRapido('nuevos')">
                                    <i class="fas fa-user-plus me-1"></i> Nuevos
                                </span>
                                <span class="filtro-chip" data-filtro="activos" onclick="aplicarFiltroRapido('activos')">
                                    <i class="fas fa-chart-line me-1"></i> Activos
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contenedor principal -->
                <div class="card" style="border: none; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <div class="card-body p-0">
                        <!-- Toolbar superior -->
                        <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                            <div>
                                <span class="text-muted" id="contadorClientes">Cargando clientes...</span>
                                <span class="badge bg-info ms-2" id="resultadosBusqueda" style="display: none;"></span>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-secondary btn-sm" onclick="limpiarBusqueda()" title="Limpiar filtros">
                                    <i class="fas fa-times"></i>
                                </button>
                                <button class="btn btn-outline-primary btn-sm" onclick="refrescarClientes()" title="Actualizar">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                        </div>
                        <!-- Estado de carga -->
                        <div id="estadoCarga" class="text-center py-5" style="display: none;">
                            <div class="loading-spinner">
                                <i class="fas fa-spinner fa-spin fa-3x mb-3" style="color: #FF1493;"></i>
                                <p class="text-muted">Cargando clientes...</p>
                            </div>
                        </div>
                        
                        <!-- Vista Tabla -->
                        <div id="vistaTablaContainer" style="display: none;" class="p-3">
                            <div class="table-responsive">
                                <table class="table table-hover table-modern mb-0">
                                    <thead>
                                        <tr>
                                            <th>Cliente</th>
                                            <th>Identificación</th>
                                            <th>Contacto</th>
                                            <th>Ubicación</th>
                                            <th>Categoría</th>
                                            <th>Descuento</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tablaClientes">
                                        <tr><td colspan="7" class="text-center text-muted py-4">Cargando clientes...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Vista Tarjetas -->
                        <div id="vistaTarjetasContainer" class="row p-3">
                            <!-- Las tarjetas se cargarán aquí -->
                        </div>
                        
                        <!-- Paginación -->
                        <div class="d-flex justify-content-center p-3 border-top">
                            <nav><ul class="pagination pagination-sm mb-0" id="paginacion"></ul></nav>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal para Nuevo/Editar Cliente -->
    <div class="modal fade" id="modalCliente" tabindex="-1" aria-labelledby="modalClienteLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content" style="border-radius: 15px;">
                <div class="modal-header" style="background: linear-gradient(135deg, #FF1493, #FF69B4); color: white; border-radius: 15px 15px 0 0;">
                    <h5 class="modal-title" id="modalClienteLabel">
                        <i class="fas fa-user-plus"></i> Nuevo Cliente
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="formCliente">
                        <input type="hidden" id="clienteId" name="id">
                        
                        <!-- Información Personal -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-muted mb-3"><i class="fas fa-user me-2"></i>Información Personal</h6>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombres *</label>
                                <input type="text" class="form-control" id="nombres" name="nombres" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Apellidos *</label>
                                <input type="text" class="form-control" id="apellidos" name="apellidos" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tipo de Identificación *</label>
                                <select class="form-select" id="tipoId" name="tipo_identificacion" required>
                                    <option value="CC">Cédula de Ciudadanía</option>
                                    <option value="CE">Cédula de Extranjería</option>
                                    <option value="NIT">NIT</option>
                                    <option value="PAS">Pasaporte</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Número de Identificación *</label>
                                <input type="text" class="form-control" id="numeroId" name="identificacion" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Teléfono *</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono" required>
                            </div>
                        </div>

                        <!-- Información de Contacto -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-muted mb-3"><i class="fas fa-envelope me-2"></i>Información de Contacto</h6>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Teléfono Alternativo</label>
                                <input type="tel" class="form-control" id="telefonoAlt" name="telefono_alternativo">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Dirección</label>
                                <input type="text" class="form-control" id="direccion" name="direccion" placeholder="Dirección completa">
                            </div>
                        </div>

                        <!-- Información de Ubicación -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-muted mb-3"><i class="fas fa-map-marker-alt me-2"></i>Ubicación</h6>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Ciudad *</label>
                                <select class="form-select" id="ciudad" name="ciudad" required>
                                    <option value="">Seleccione una ciudad</option>
                                    <option value="Bogotá">Bogotá</option>
                                    <option value="Medellín">Medellín</option>
                                    <option value="Cartagena">Cartagena</option>
                                    <option value="Santa Marta">Santa Marta</option>
                                    <option value="Bucaramanga">Bucaramanga</option>
                                    <option value="Popayán">Popayán</option>
                                    <option value="Boyacá">Boyacá</option>
                                    <option value="Tolima">Tolima</option>
                                    <option value="Neiva">Neiva</option>
                                    <option value="Nariño">Nariño</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Departamento</label>
                                <input type="text" class="form-control" id="departamento" name="departamento" placeholder="Departamento">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Código Postal</label>
                                <input type="text" class="form-control" id="codigoPostal" name="codigo_postal">
                            </div>
                        </div>

                        <!-- Categorización y Descuentos -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-muted mb-3"><i class="fas fa-tags me-2"></i>Categorización</h6>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Categoría del Cliente</label>
                                <select class="form-select" id="categoriaCliente" name="categoria_id">
                                    <option value="">Seleccione una categoría</option>
                                    <option value="1">VIP</option>
                                    <option value="2">Regular</option>
                                    <option value="3">Nuevo</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Descuento (%)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="descuento" name="descuento" min="0" max="50" value="0">
                                    <span class="input-group-text">%</span>
                                </div>
                                <small class="text-muted">Descuento aplicable (0-50%)</small>
                            </div>
                        </div>

                        <!-- Notas adicionales -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label">Notas adicionales</label>
                                <textarea class="form-control" id="notas" name="notas" rows="3" placeholder="Información adicional sobre el cliente..."></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-pink" onclick="guardarCliente()">
                        <i class="fas fa-save"></i> Guardar Cliente
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Ver Detalles del Cliente -->
    <div class="modal fade" id="modalDetallesCliente" tabindex="-1" aria-labelledby="modalDetallesClienteLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius: 15px;">
                <div class="modal-header" style="background: linear-gradient(135deg, #17a2b8, #138496); color: white; border-radius: 15px 15px 0 0;">
                    <h5 class="modal-title" id="modalDetallesClienteLabel">
                        <i class="fas fa-user-circle"></i> Detalles del Cliente
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="detallesClienteContent">
                    <!-- El contenido se cargará dinámicamente -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="editarClienteDesdeDetalles()">
                        <i class="fas fa-edit"></i> Editar
                    </button>
                    <button type="button" class="btn btn-pink" onclick="crearVentaDesdeDetalles()">
                        <i class="fas fa-shopping-cart"></i> Nueva Venta
                    </button>
                </div>
            </div>
        </div>
    </div>
            </main>
        </div>
    </div>
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/theme-system.js"></script>
    
    <!-- Toasts para notificaciones -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div id="notificationToast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastMessage">
                    <!-- Mensaje se carga aquí -->
                </div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
        </div>
    </div>
    <script>
        // Variables globales
        let clientesData = [];
        let categoriasData = [];
        let vistaActual = 'tarjetas';
        let paginaActual = 1;
        let totalClientes = 0;
        let busquedaTimeout;
        let clienteEditando = null;
        
        // Inicialización
        document.addEventListener('DOMContentLoaded', function() {
            inicializarSistema();
            cargarCategorias();
            cargarClientes();
            actualizarEstadisticas();
        });
        
        function inicializarSistema() {
            // Toggle sidebar en móviles
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
            
            // Aplicar animaciones con delay
            const elementos = document.querySelectorAll('.fade-in-up');
            elementos.forEach((el, index) => {
                el.style.setProperty('--delay', index);
            });
        }
        
        // ========== GESTIÓN DE CATEGORÍAS ==========
        
        async function cargarCategorias() {
            try {
                const response = await fetch('../../src/Controllers/CategoriaController.php?accion=listar&tipo=clientes');
                const data = await response.json();
                
                if (data.success) {
                    categoriasData = data.data || [];
                    // Actualizar selector de categorías
                    const selectCategorias = document.getElementById('categoriaCliente');
                    selectCategorias.innerHTML = '<option value="">Seleccione una categoría</option>';
                    categoriasData.forEach(categoria => {
                        selectCategorias.innerHTML += `<option value="${categoria.id}">${categoria.nombre}</option>`;
                    });
                }
            } catch (error) {
                console.error('Error al cargar categorías:', error);
            }
        }
        
        // ========== BÚSQUEDA Y FILTROS ==========
        
        async function buscarCliente() {
            const termino = document.getElementById('buscarCliente').value.trim();
            
            if (!termino) {
                cargarClientes();
                return;
            }
            
            mostrarEstadoCarga(true);
            
            try {
                const response = await fetch(`../../src/Controllers/ClienteControllerAPI.php?accion=buscar&termino=${encodeURIComponent(termino)}&limite=50`);
                const data = await response.json();
                
                if (data.success) {
                    clientesData = data.data || [];
                    mostrarClientes(clientesData);
                    document.getElementById('resultadosBusqueda').style.display = 'inline';
                    document.getElementById('resultadosBusqueda').textContent = `${clientesData.length} resultados`;
                } else {
                    mostrarNotificacion('Error al buscar clientes', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarNotificacion('Error de conexión al buscar clientes', 'error');
            } finally {
                mostrarEstadoCarga(false);
            }
        }
        
        function buscarClienteEnTiempoReal() {
            clearTimeout(busquedaTimeout);
            busquedaTimeout = setTimeout(() => {
                const termino = document.getElementById('buscarCliente').value.trim();
                if (termino.length >= 2) {
                    buscarCliente();
                } else if (termino.length === 0) {
                    limpiarBusqueda();
                }
            }, 500);
        }
        
        function limpiarBusqueda() {
            document.getElementById('buscarCliente').value = '';
            document.getElementById('filtroCategoria').value = '';
            document.getElementById('resultadosBusqueda').style.display = 'none';
            
            // Limpiar filtros activos
            document.querySelectorAll('.filtro-chip').forEach(chip => {
                chip.classList.remove('active');
            });
            document.querySelector('[data-filtro="todos"]').classList.add('active');
            
            cargarClientes();
        }
        
        function filtrarPorCategoria() {
            const categoriaSeleccionada = document.getElementById('filtroCategoria').value;
            let clientesFiltrados = [...clientesData];
            
            if (categoriaSeleccionada) {
                clientesFiltrados = clientesFiltrados.filter(c => 
                    c.categoria_id && c.categoria_id.toString() === categoriaSeleccionada
                );
            }
            
            mostrarClientes(clientesFiltrados);
            actualizarContador(clientesFiltrados.length);
        }
        
        function aplicarFiltroRapido(filtro) {
            // Actualizar chips activos
            document.querySelectorAll('.filtro-chip').forEach(chip => {
                chip.classList.remove('active');
            });
            document.querySelector(`[data-filtro="${filtro}"]`).classList.add('active');
            
            let clientesFiltrados = [...clientesData];
            
            switch(filtro) {
                case 'con_descuento':
                    clientesFiltrados = clientesFiltrados.filter(c => c.descuento > 0);
                    break;
                case 'vip':
                    clientesFiltrados = clientesFiltrados.filter(c => 
                        c.categoria_id === 1 || (c.categoria && c.categoria.toLowerCase().includes('vip'))
                    );
                    break;
                case 'nuevos':
                    const fechaLimite = new Date();
                    fechaLimite.setMonth(fechaLimite.getMonth() - 1);
                    clientesFiltrados = clientesFiltrados.filter(c => 
                        c.fecha_registro && new Date(c.fecha_registro) > fechaLimite
                    );
                    break;
                case 'activos':
                    clientesFiltrados = clientesFiltrados.filter(c => c.activo !== 0);
                    break;
                case 'todos':
                default:
                    // No filtrar
                    break;
            }
            
            mostrarClientes(clientesFiltrados);
            actualizarContador(clientesFiltrados.length);
        }
        
        // ========== CARGA DE DATOS ==========
        
        async function cargarClientes() {
            mostrarEstadoCarga(true);
            
            try {
                const response = await fetch('../../src/Controllers/ClienteControllerAPI.php?accion=listar&limite=100');
                const data = await response.json();
                
                if (data.success) {
                    clientesData = data.data || [];
                    mostrarClientes(clientesData);
                    actualizarContador(clientesData.length);
                } else {
                    mostrarNotificacion('Error al cargar clientes', 'error');
                    clientesData = [];
                    mostrarClientes([]);
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarNotificacion('Error de conexión', 'error');
                clientesData = [];
                mostrarClientes([]);
            } finally {
                mostrarEstadoCarga(false);
            }
        }
        
        function refrescarDatos() {
            cargarClientes();
            actualizarEstadisticas();
            mostrarNotificacion('Datos actualizados', 'success');
        }
        
        function refrescarClientes() {
            refrescarDatos();
        }
        
        // ========== VISUALIZACIÓN ==========
        
        function cambiarVista(vista) {
            vistaActual = vista;
            
            const btnTabla = document.getElementById('vistaTabla');
            const btnTarjetas = document.getElementById('vistaTarjetas');
            const containerTabla = document.getElementById('vistaTablaContainer');
            const containerTarjetas = document.getElementById('vistaTarjetasContainer');
            
            if (vista === 'tabla') {
                btnTabla.classList.remove('btn-outline-secondary');
                btnTabla.classList.add('btn-pink');
                btnTarjetas.classList.remove('btn-pink');
                btnTarjetas.classList.add('btn-outline-secondary');
                containerTabla.style.display = 'block';
                containerTarjetas.style.display = 'none';
            } else {
                btnTarjetas.classList.remove('btn-outline-secondary');
                btnTarjetas.classList.add('btn-pink');
                btnTabla.classList.remove('btn-pink');
                btnTabla.classList.add('btn-outline-secondary');
                containerTabla.style.display = 'none';
                containerTarjetas.style.display = 'block';
            }
            
            mostrarClientes(clientesData);
        }
        
        function mostrarClientes(clientes) {
            if (vistaActual === 'tabla') {
                mostrarClientesTabla(clientes);
            } else {
                mostrarClientesTarjetas(clientes);
            }
        }
        
        function mostrarClientesTabla(clientes) {
            const tbody = document.getElementById('tablaClientes');
            
            if (clientes.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-5"><i class="fas fa-users fa-3x mb-3"></i><br><h6>No se encontraron clientes</h6><p>Intenta con otros términos de búsqueda</p></td></tr>';
                return;
            }
            
            tbody.innerHTML = clientes.map((cliente, index) => {
                const nombreCompleto = cliente.nombres && cliente.apellidos 
                    ? `${cliente.nombres} ${cliente.apellidos}` 
                    : (cliente.nombre || 'Sin nombre');
                    
                const categoria = obtenerCategoriaCliente(cliente);
                
                return `
                <tr class="fade-in-up" style="--delay: ${index}">
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="cliente-avatar me-3" style="width: 45px; height: 45px; font-size: 1rem;">
                                ${nombreCompleto.charAt(0).toUpperCase()}
                            </div>
                            <div>
                                <strong class="d-block">${nombreCompleto}</strong>
                                ${cliente.email ? `<small class="text-muted"><i class="fas fa-envelope me-1"></i>${cliente.email}</small>` : ''}
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-secondary">${cliente.identificacion || 'Sin ID'}</span>
                        <br><small class="text-muted">${cliente.tipo_identificacion || 'CC'}</small>
                    </td>
                    <td>
                        ${cliente.telefono ? `<i class="fas fa-phone text-muted me-1"></i>${cliente.telefono}` : '<span class="text-muted">No registrado</span>'}
                        ${cliente.telefono_alternativo ? `<br><small class="text-muted">${cliente.telefono_alternativo}</small>` : ''}
                    </td>
                    <td>
                        <i class="fas fa-map-marker-alt text-muted me-1"></i>
                        ${cliente.ciudad || 'No registrado'}
                        ${cliente.direccion ? `<br><small class="text-muted">${cliente.direccion}</small>` : ''}
                    </td>
                    <td>
                        <span class="badge ${categoria.clase}">${categoria.nombre}</span>
                    </td>
                    <td>
                        ${cliente.descuento > 0 ? 
                            `<span class="descuento-badge">${cliente.descuento}%</span>` : 
                            '<span class="text-muted">Sin descuento</span>'
                        }
                    </td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-info" onclick="verDetallesCompletos(${cliente.id})" title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-outline-primary" onclick="editarCliente(${cliente.id})" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-pink" onclick="crearVenta(${cliente.id})" title="Nueva venta">
                                <i class="fas fa-shopping-cart"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                `;
            }).join('');
        }
        
        function mostrarClientesTarjetas(clientes) {
            const container = document.getElementById('vistaTarjetasContainer');
            
            if (clientes.length === 0) {
                container.innerHTML = `
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-users fa-4x text-muted mb-4"></i>
                        <h5 class="text-muted">No se encontraron clientes</h5>
                        <p class="text-muted">Intenta con otros términos de búsqueda o verifica los filtros aplicados</p>
                        <button class="btn btn-pink mt-3" onclick="mostrarModalNuevoCliente()">
                            <i class="fas fa-user-plus me-2"></i>Agregar Primer Cliente
                        </button>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = clientes.map((cliente, index) => {
                const nombreCompleto = cliente.nombres && cliente.apellidos 
                    ? `${cliente.nombres} ${cliente.apellidos}` 
                    : (cliente.nombre || 'Sin nombre');
                const iniciales = nombreCompleto.split(' ').map(n => n.charAt(0)).join('').substring(0, 2).toUpperCase();
                const categoria = obtenerCategoriaCliente(cliente);
                
                return `
                <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                    <div class="cliente-card fade-in-up stagger-animation" style="--delay: ${index}">
                        <span class="categoria-badge ${categoria.clase}">${categoria.nombre}</span>
                        <div class="card-body text-center p-4">
                            <div class="cliente-avatar mx-auto mb-3">
                                ${iniciales}
                            </div>
                            <h6 class="card-title mb-2 fw-600">${nombreCompleto}</h6>
                            
                            <div class="mb-2">
                                <small class="text-muted d-block">
                                    <i class="fas fa-id-card me-1"></i>${cliente.identificacion || 'Sin ID'}
                                </small>
                            </div>
                            
                            ${cliente.telefono ? 
                                `<div class="mb-2">
                                    <small class="text-muted d-block">
                                        <i class="fas fa-phone me-1"></i>${cliente.telefono}
                                    </small>
                                </div>` : ''
                            }
                            
                            ${cliente.ciudad ? 
                                `<div class="mb-2">
                                    <small class="text-muted d-block">
                                        <i class="fas fa-map-marker-alt me-1"></i>${cliente.ciudad}
                                    </small>
                                </div>` : ''
                            }
                            
                            ${cliente.descuento > 0 ? 
                                `<div class="mb-3">
                                    <span class="descuento-badge">Descuento ${cliente.descuento}%</span>
                                </div>` : 
                                '<div class="mb-3"><span class="badge bg-light text-dark">Sin descuento</span></div>'
                            }
                            
                            <div class="d-grid gap-2">
                                <div class="btn-group">
                                    <button class="btn btn-outline-info btn-sm" onclick="verDetallesCompletos(${cliente.id})" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-primary btn-sm" onclick="editarCliente(${cliente.id})" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-pink btn-sm" onclick="crearVenta(${cliente.id})" title="Nueva venta">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                `;
            }).join('');
        }
        
        // ========== FUNCIONES AUXILIARES ==========
        
        function obtenerCategoriaCliente(cliente) {
            // Determinar categoría del cliente
            if (cliente.categoria_id) {
                const categoria = categoriasData.find(c => c.id == cliente.categoria_id);
                if (categoria) {
                    const clases = {
                        'VIP': 'categoria-vip',
                        'Regular': 'categoria-regular',
                        'Nuevo': 'categoria-nuevo'
                    };
                    return {
                        nombre: categoria.nombre,
                        clase: clases[categoria.nombre] || 'categoria-regular'
                    };
                }
            }
            
            // Fallback basado en descuento o datos
            if (cliente.descuento >= 10) {
                return { nombre: 'VIP', clase: 'categoria-vip' };
            } else if (cliente.descuento > 0) {
                return { nombre: 'Regular', clase: 'categoria-regular' };
            } else {
                return { nombre: 'Nuevo', clase: 'categoria-nuevo' };
            }
        }
        
        function mostrarEstadoCarga(mostrar) {
            const estadoCarga = document.getElementById('estadoCarga');
            const containerTabla = document.getElementById('vistaTablaContainer');
            const containerTarjetas = document.getElementById('vistaTarjetasContainer');
            
            if (mostrar) {
                estadoCarga.style.display = 'flex';
                containerTabla.style.display = 'none';
                containerTarjetas.style.display = 'none';
            } else {
                estadoCarga.style.display = 'none';
                cambiarVista(vistaActual);
            }
        }
        
        function actualizarContador(cantidad) {
            document.getElementById('contadorClientes').textContent = `Mostrando ${cantidad} cliente${cantidad !== 1 ? 's' : ''}`;
        }
        
        function mostrarNotificacion(mensaje, tipo = 'info') {
            const toast = document.getElementById('notificationToast');
            const toastMessage = document.getElementById('toastMessage');
            
            // Configurar colores según el tipo
            const colores = {
                'success': 'text-bg-success',
                'error': 'text-bg-danger',
                'warning': 'text-bg-warning',
                'info': 'text-bg-info'
            };
            
            // Limpiar clases anteriores
            toast.className = 'toast align-items-center border-0';
            toast.classList.add(colores[tipo] || colores.info);
            
            toastMessage.textContent = mensaje;
            
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
        }
        
        // ========== GESTIÓN DE MODALES ==========
        
        function mostrarModalNuevoCliente() {
            clienteEditando = null;
            document.getElementById('modalClienteLabel').innerHTML = '<i class="fas fa-user-plus"></i> Nuevo Cliente';
            document.getElementById('formCliente').reset();
            document.getElementById('clienteId').value = '';
            
            const modal = new bootstrap.Modal(document.getElementById('modalCliente'));
            modal.show();
        }
        
        function editarCliente(clienteId) {
            const cliente = clientesData.find(c => c.id == clienteId);
            if (!cliente) {
                mostrarNotificacion('Cliente no encontrado', 'error');
                return;
            }
            
            clienteEditando = clienteId;
            document.getElementById('modalClienteLabel').innerHTML = '<i class="fas fa-user-edit"></i> Editar Cliente';
            
            // Llenar formulario
            document.getElementById('clienteId').value = cliente.id;
            document.getElementById('nombres').value = cliente.nombres || '';
            document.getElementById('apellidos').value = cliente.apellidos || '';
            document.getElementById('tipoId').value = cliente.tipo_identificacion || 'CC';
            document.getElementById('numeroId').value = cliente.identificacion || '';
            document.getElementById('telefono').value = cliente.telefono || '';
            document.getElementById('email').value = cliente.email || '';
            document.getElementById('telefonoAlt').value = cliente.telefono_alternativo || '';
            document.getElementById('direccion').value = cliente.direccion || '';
            document.getElementById('ciudad').value = cliente.ciudad || '';
            document.getElementById('departamento').value = cliente.departamento || '';
            document.getElementById('codigoPostal').value = cliente.codigo_postal || '';
            document.getElementById('categoriaCliente').value = cliente.categoria_id || '';
            document.getElementById('descuento').value = cliente.descuento || 0;
            document.getElementById('notas').value = cliente.notas || '';
            
            const modal = new bootstrap.Modal(document.getElementById('modalCliente'));
            modal.show();
        }
        
        async function guardarCliente() {
            const form = document.getElementById('formCliente');
            const formData = new FormData(form);
            const clienteData = Object.fromEntries(formData.entries());
            
            // Validaciones
            if (!clienteData.nombres || !clienteData.apellidos) {
                mostrarNotificacion('Por favor complete nombres y apellidos (campos obligatorios)', 'warning');
                return;
            }
            
            // Preparar datos
            clienteData.nombre_completo = `${clienteData.nombres} ${clienteData.apellidos}`;
            
            // Si no hay identificación, asignar una temporal
            if (!clienteData.identificacion) {
                clienteData.identificacion = `TEMP_${Date.now()}`;
            }
            
            // Si no hay teléfono, asignar uno temporal
            if (!clienteData.telefono) {
                clienteData.telefono = '0000000000';
            }
            
            console.log('Datos a enviar:', clienteData);
            
            try {
                const url = clienteEditando 
                    ? `../../src/Controllers/ClienteControllerAPI.php?accion=actualizar&id=${clienteEditando}`
                    : '../../src/Controllers/ClienteControllerAPI.php?accion=crear';
                
                console.log('URL:', url);
                
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(clienteData)
                });
                
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                // Verificar si la respuesta es JSON válida
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const textResponse = await response.text();
                    console.error('Respuesta no es JSON:', textResponse);
                    mostrarNotificacion('Error del servidor: Respuesta inválida', 'error');
                    return;
                }
                
                const result = await response.json();
                console.log('Resultado:', result);
                
                if (result.success) {
                    mostrarNotificacion(
                        clienteEditando ? 'Cliente actualizado exitosamente' : 'Cliente creado exitosamente',
                        'success'
                    );
                    
                    // Cerrar modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalCliente'));
                    modal.hide();
                    
                    // Recargar datos
                    await cargarClientes();
                    await actualizarEstadisticas();
                } else {
                    console.error('Error del servidor:', result.error);
                    mostrarNotificacion('Error: ' + (result.error || 'Error desconocido'), 'error');
                }
            } catch (error) {
                console.error('Error completo:', error);
                
                // Verificar si es un error de red
                if (error.name === 'TypeError' && error.message.includes('fetch')) {
                    mostrarNotificacion('Error de red: No se pudo conectar al servidor. Verifica que XAMPP esté funcionando.', 'error');
                } else if (error.name === 'SyntaxError') {
                    mostrarNotificacion('Error: El servidor devolvió una respuesta no válida. Revisa la consola para más detalles.', 'error');
                } else {
                    mostrarNotificacion('Error de conexión: ' + error.message, 'error');
                }
                
                // Información adicional para debugging
                console.log('Error details:', {
                    name: error.name,
                    message: error.message,
                    stack: error.stack
                });
            }
        }
        
        function verDetallesCompletos(clienteId) {
            const cliente = clientesData.find(c => c.id == clienteId);
            if (!cliente) {
                mostrarNotificacion('Cliente no encontrado', 'error');
                return;
            }
            
            const nombreCompleto = cliente.nombres && cliente.apellidos 
                ? `${cliente.nombres} ${cliente.apellidos}` 
                : (cliente.nombre || 'Sin nombre');
            
            const categoria = obtenerCategoriaCliente(cliente);
            
            const contenido = `
                <div class="row">
                    <div class="col-md-4 text-center mb-4">
                        <div class="cliente-avatar mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2rem;">
                            ${nombreCompleto.charAt(0).toUpperCase()}
                        </div>
                        <h5>${nombreCompleto}</h5>
                        <span class="badge ${categoria.clase}">${categoria.nombre}</span>
                    </div>
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <strong>Identificación:</strong><br>
                                <span class="text-muted">${cliente.tipo_identificacion || 'CC'}: ${cliente.identificacion || 'No registrado'}</span>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <strong>Teléfono:</strong><br>
                                <span class="text-muted">${cliente.telefono || 'No registrado'}</span>
                                ${cliente.telefono_alternativo ? `<br><small>Alt: ${cliente.telefono_alternativo}</small>` : ''}
                            </div>
                            <div class="col-sm-6 mb-3">
                                <strong>Email:</strong><br>
                                <span class="text-muted">${cliente.email || 'No registrado'}</span>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <strong>Ciudad:</strong><br>
                                <span class="text-muted">${cliente.ciudad || 'No registrado'}</span>
                            </div>
                            <div class="col-12 mb-3">
                                <strong>Dirección:</strong><br>
                                <span class="text-muted">${cliente.direccion || 'No registrada'}</span>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <strong>Descuento:</strong><br>
                                <span class="text-muted">${cliente.descuento || 0}%</span>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <strong>Fecha de registro:</strong><br>
                                <span class="text-muted">${cliente.fecha_registro ? new Date(cliente.fecha_registro).toLocaleDateString() : 'No disponible'}</span>
                            </div>
                            ${cliente.notas ? `
                                <div class="col-12 mb-3">
                                    <strong>Notas:</strong><br>
                                    <span class="text-muted">${cliente.notas}</span>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('detallesClienteContent').innerHTML = contenido;
            
            // Configurar botones del modal
            window.clienteDetallesId = clienteId;
            
            const modal = new bootstrap.Modal(document.getElementById('modalDetallesCliente'));
            modal.show();
        }
        
        function editarClienteDesdeDetalles() {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalDetallesCliente'));
            modal.hide();
            
            setTimeout(() => {
                editarCliente(window.clienteDetallesId);
            }, 300);
        }
        
        function crearVentaDesdeDetalles() {
            crearVenta(window.clienteDetallesId);
        }
        
        // ========== ACCIONES ==========
        
        function crearVenta(clienteId) {
            // Redirigir a ventas con el cliente preseleccionado
            window.location.href = 'ventas.php?cliente=' + clienteId;
        }
        
        function exportarClientes() {
            if (clientesData.length === 0) {
                mostrarNotificacion('No hay clientes para exportar', 'warning');
                return;
            }
            
            // Crear CSV con los datos de clientes
            const headers = ['Nombre', 'Identificación', 'Teléfono', 'Email', 'Ciudad', 'Categoría', 'Descuento'];
            const csvData = [
                headers.join(','),
                ...clientesData.map(cliente => {
                    const nombreCompleto = cliente.nombres && cliente.apellidos 
                        ? `${cliente.nombres} ${cliente.apellidos}` 
                        : (cliente.nombre || 'Sin nombre');
                    const categoria = obtenerCategoriaCliente(cliente);
                    
                    return [
                        `"${nombreCompleto}"`,
                        `"${cliente.identificacion || ''}"`,
                        `"${cliente.telefono || ''}"`,
                        `"${cliente.email || ''}"`,
                        `"${cliente.ciudad || ''}"`,
                        `"${categoria.nombre}"`,
                        `"${cliente.descuento || 0}%"`
                    ].join(',');
                })
            ].join('\n');
            
            // Descargar archivo
            const blob = new Blob([csvData], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', `clientes_${new Date().toISOString().split('T')[0]}.csv`);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            mostrarNotificacion(`${clientesData.length} clientes exportados exitosamente`, 'success');
        }
        
        // ========== ESTADÍSTICAS ==========
        
        async function actualizarEstadisticas() {
            try {
                const response = await fetch('../../src/Controllers/ClienteControllerAPI.php?accion=estadisticas');
                const data = await response.json();
                
                if (data.success && data.data) {
                    document.getElementById('totalClientes').textContent = data.data.total_clientes || clientesData.length;
                    document.getElementById('clientesVIP').textContent = data.data.clientes_vip || 0;
                    document.getElementById('nuevosMes').textContent = data.data.nuevos_mes || 0;
                    document.getElementById('clientesActivos').textContent = data.data.activos || clientesData.length;
                } else {
                    // Fallback con datos locales
                    actualizarEstadisticasLocal();
                }
            } catch (error) {
                console.error('Error al cargar estadísticas:', error);
                actualizarEstadisticasLocal();
            }
        }
        
        function actualizarEstadisticasLocal() {
            document.getElementById('totalClientes').textContent = clientesData.length;
            document.getElementById('clientesVIP').textContent = clientesData.filter(c => 
                c.categoria_id === 1 || c.descuento >= 10
            ).length;
            document.getElementById('nuevosMes').textContent = 0;
            document.getElementById('clientesActivos').textContent = clientesData.filter(c => c.activo !== 0).length;
        }
    </script>
</body>
</html>
