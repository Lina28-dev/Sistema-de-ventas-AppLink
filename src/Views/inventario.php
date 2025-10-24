<?php
session_start();
$page_title = "Gestión de Inventario";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>
        
        <!-- Main content -->
        <main class="main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <div class="page-title">
                    <i class="fas fa-boxes"></i>
                    Gestión de Inventario
                </div>
                <div class="text-muted">
                    <i class="far fa-clock"></i>
                    <span id="fechaHoraInventario"><?php date_default_timezone_set('America/Bogota'); echo date('d/m/Y H:i:s'); ?></span>
                </div>
            </div>

            <!-- Estadísticas de Inventario -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card card-stat">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted">Total Productos</h6>
                                    <h3 class="text-primary">1,245</h3>
                                    <small class="text-muted">En inventario</small>
                                </div>
                                <i class="fas fa-box text-primary fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-stat">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted">Stock Bajo</h6>
                                    <h3 class="text-warning">23</h3>
                                    <small class="text-warning">Requieren reposición</small>
                                </div>
                                <i class="fas fa-exclamation-triangle text-warning fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-stat">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted">Sin Stock</h6>
                                    <h3 class="text-danger">5</h3>
                                    <small class="text-danger">Agotados</small>
                                </div>
                                <i class="fas fa-times-circle text-danger fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-stat">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted">Valor Total</h6>
                                    <h3 class="text-success">$85,420,000</h3>
                                    <small class="text-success">Inventario valorizado</small>
                                </div>
                                <i class="fas fa-dollar-sign text-success fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones de Inventario -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-tools"></i> Gestión de Inventario</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <button class="btn btn-primary btn-lg w-100 mb-3">
                                        <i class="fas fa-plus me-2"></i>
                                        Agregar Producto
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-warning btn-lg w-100 mb-3">
                                        <i class="fas fa-edit me-2"></i>
                                        Actualizar Stock
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-info btn-lg w-100 mb-3">
                                        <i class="fas fa-search me-2"></i>
                                        Buscar Producto
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-success btn-lg w-100 mb-3">
                                        <i class="fas fa-download me-2"></i>
                                        Exportar Inventario
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de Productos -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5><i class="fas fa-list"></i> Inventario de Productos</h5>
                            <div>
                                <button class="btn btn-sm btn-outline-primary me-2">
                                    <i class="fas fa-filter"></i> Filtrar
                                </button>
                                <span class="badge bg-success">LIVE <span class="real-time-indicator"></span></span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Código</th>
                                            <th>Producto</th>
                                            <th>Categoría</th>
                                            <th>Stock Actual</th>
                                            <th>Stock Mínimo</th>
                                            <th>Precio</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>PROD-001</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-cube text-primary me-2"></i>
                                                    Producto A
                                                </div>
                                            </td>
                                            <td>Categoría 1</td>
                                            <td class="fw-bold text-success">150</td>
                                            <td>20</td>
                                            <td>$25,000</td>
                                            <td><span class="badge bg-success">En Stock</span></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-primary" title="Ver detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-warning" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-info" title="Ajustar stock">
                                                        <i class="fas fa-plus-minus"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>PROD-002</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-cube text-primary me-2"></i>
                                                    Producto B
                                                </div>
                                            </td>
                                            <td>Categoría 2</td>
                                            <td class="fw-bold text-warning">15</td>
                                            <td>20</td>
                                            <td>$45,000</td>
                                            <td><span class="badge bg-warning">Stock Bajo</span></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-primary" title="Ver detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-warning" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger" title="Reabastecer">
                                                        <i class="fas fa-truck"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>PROD-003</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-cube text-primary me-2"></i>
                                                    Producto C
                                                </div>
                                            </td>
                                            <td>Categoría 1</td>
                                            <td class="fw-bold text-danger">0</td>
                                            <td>10</td>
                                            <td>$35,000</td>
                                            <td><span class="badge bg-danger">Agotado</span></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-primary" title="Ver detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-warning" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger" title="Reabastecer urgente">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                    </button>
                                                </div>
                                            </td>
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

<script>
    // Actualizar fecha y hora cada segundo
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
        const dateTimeElement = document.getElementById('fechaHoraInventario');
        if (dateTimeElement) {
            dateTimeElement.textContent = dateTimeString;
        }
    }
    
    setInterval(updateDateTime, 1000);
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>