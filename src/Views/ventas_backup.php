<?php
// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Configurar para mostrar errores temporalmente
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar si estamos accediendo directamente o a través del enrutador
$is_direct_access = strpos($_SERVER['REQUEST_URI'], '/src/Views/ventas.php') !== false;

if ($is_direct_access) {
    // Acceso directo - simular autenticación para testing
    $_SESSION['authenticated'] = true;
    $_SESSION['user_name'] = 'Usuario Test';
    echo "<!-- Acceso directo detectado - Sesión simulada -->";
} else {
    // Acceso a través del enrutador - verificar autenticación normal
    if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
        header("Location: /Sistema-de-ventas-AppLink-main/public/");
        exit();
    }
}

echo "<!-- Debug Info -->";
echo "<!-- Session authenticated: " . (isset($_SESSION['authenticated']) ? ($_SESSION['authenticated'] ? 'true' : 'false') : 'not set') . " -->";
echo "<!-- Request URI: " . $_SERVER['REQUEST_URI'] . " -->";
echo "<!-- Direct access: " . ($is_direct_access ? 'yes' : 'no') . " -->";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventas - Lilipink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/Sistema-de-ventas-AppLink-main/public/css/destinatarios.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background: linear-gradient(180deg, #343a40 0%, #212529 100%); color: white; }
        .sidebar .nav-link { color: rgba(255,255,255,0.8); padding: 12px 20px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: rgba(255,255,255,0.1); color: white; }
        .card-stat { border-left: 4px solid #FF1493; }
        .btn-pink { background-color: #FF1493; border-color: #FF1493; color: white; }
        .btn-pink:hover { background-color: #FF69B4; color: white; }
        .product-card { cursor: pointer; transition: all 0.3s; border: 2px solid transparent; }
        .product-card:hover { border-color: #FF1493; transform: scale(1.05); }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar centralizado -->
            <?php 
                $activePage = 'ventas';
                include __DIR__ . '/partials/sidebar.php';
            ?>
            <main class="col-md-10 px-4">
                <!-- Mensaje de estado de carga -->
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> <strong>Sistema de Ventas Cargado Correctamente</strong>
                    <span id="estadoCarga">Inicializando...</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                
                <h1 class="mt-3"><i class="fas fa-shopping-cart"></i> Sistema de Ventas</h1>
                <div class="row my-4">
                    <div class="col-md-3"><div class="card card-stat"><div class="card-body"><h6 class="text-muted">Ventas Hoy</h6><h3 id="ventasHoy">$0.00</h3></div></div></div>
                    <div class="col-md-3"><div class="card card-stat"><div class="card-body"><h6 class="text-muted">Ventas del Mes</h6><h3 id="ventasMes">$0.00</h3></div></div></div>
                    <div class="col-md-3"><div class="card card-stat"><div class="card-body"><h6 class="text-muted">Transacciones</h6><h3 id="transacciones">0</h3></div></div></div>
                    <div class="col-md-3"><div class="card card-stat"><div class="card-body"><h6 class="text-muted">Ventas Promedio</h6><h3 id="ticketPromedio">$0.00</h3></div></div></div>
                </div>
                <ul class="nav nav-tabs">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#nueva"><i class="fas fa-plus"></i> Nueva Venta</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#gestionar"><i class="fas fa-edit"></i> Gestionar Ventas</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#historial"><i class="fas fa-history"></i> Historial</button></li>
                </ul>
                <div class="tab-content p-3">
                    <div class="tab-pane fade show active" id="nueva">
                        <!-- Información de la venta -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5><i class="fas fa-info-circle"></i> Información de la Venta</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label for="fechaVenta" class="form-label">Fecha de Venta</label>
                                                <input type="datetime-local" class="form-control" id="fechaVenta">
                                            </div>
                                            <div class="col-md-3">
                                                <label for="numeroVenta" class="form-label">Número de Venta</label>
                                                <input type="text" class="form-control" id="numeroVenta" readonly>
                                            </div>
                                            <div class="col-md-3">
                                                <label for="estadoVenta" class="form-label">Estado</label>
                                                <select class="form-select" id="estadoVenta">
                                                    <option value="borrador">Borrador</option>
                                                    <option value="pendiente">Pendiente</option>
                                                    <option value="procesando">Procesando</option>
                                                    <option value="completada">Completada</option>
                                                    <option value="cancelada">Cancelada</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label for="vendedor" class="form-label">Vendedor</label>
                                                <input type="text" class="form-control" id="vendedor" value="<?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-3">
                                                <label for="tipoDestinatario" class="form-label field-required">Tipo de Destinatario</label>
                                                <div class="input-group">
                                                    <select class="form-select tipo-destinatario-select" id="tipoDestinatario" onchange="actualizarOpcionesDestinatario()">
                                                        <option value="cliente">👤 Cliente</option>
                                                        <option value="proveedor">🏢 Proveedor</option>
                                                        <option value="interno">🏛️ Uso Interno</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-5">
                                                <label for="destinatarioSelect" class="form-label">Destinatario</label>
                                                <div class="input-group">
                                                    <select class="form-select" id="destinatarioSelect">
                                                        <option value="">Seleccionar destinatario...</option>
                                                        <option value="general">Cliente General</option>
                                                    </select>
                                                    <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#nuevoClienteModal">
                                                        <i class="fas fa-user-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="categoriaVenta" class="form-label">Categoría</label>
                                                <select class="form-select" id="categoriaVenta">
                                                    <option value="venta">Venta</option>
                                                    <option value="devolucion">Devolución</option>
                                                    <option value="intercambio">Intercambio</option>
                                                    <option value="muestra">Muestra</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-12">
                                                <label for="observaciones" class="form-label">Observaciones</label>
                                                <textarea class="form-control" id="observaciones" rows="2" placeholder="Notas adicionales..."></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5>Buscar Productos</h5>
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" id="buscarProducto" placeholder="Buscar producto...">
                                            <button class="btn btn-pink" onclick="buscarProducto()"><i class="fas fa-search"></i></button>
                                        </div>
                                        <div class="row" id="productosLista">
                                            <div class="col-md-4 mb-3">
                                                <div class="card product-card" onclick="agregarAlCarrito(1, 'Panty Invisible Clásico', 24990, 15)">
                                                    <div class="card-body text-center">
                                                        <img src="/Sistema-de-ventas-AppLink-main/public/assets/images/panty-invisible.jpg" alt="Panty Invisible Clásico" class="img-fluid mb-2" style="max-height:120px;object-fit:contain;">
                                                        <h6>Panty Invisible Clásico</h6>
                                                        <p class="text-muted mb-1">SKU: 7702433250012</p>
                                                        <h5 class="text-success">$24.990</h5>
                                                        <span class="badge bg-info">Stock: 15</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="card product-card" onclick="agregarAlCarrito(2, 'Brasier Push Up Encaje', 59990, 8)">
                                                    <div class="card-body text-center">
                                                        <img src="/Sistema-de-ventas-AppLink-main/public/assets/images/brasier-pushup.jpg" alt="Brasier Push Up Encaje" class="img-fluid mb-2" style="max-height:120px;object-fit:contain;">
                                                        <h6>Braísier Push Up Encaje</h6>
                                                        <p class="text-muted mb-1">SKU: 7702433240013</p>
                                                        <h5 class="text-success">$59.990</h5>
                                                        <span class="badge bg-info">Stock: 8</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="card product-card" onclick="agregarAlCarrito(3, 'Pijama Short Algodón', 79990, 5)">
                                                    <div class="card-body text-center">
                                                        <img src="/Sistema-de-ventas-AppLink-main/public/assets/images/pijama-short.jpg" alt="Pijama Short Algodón" class="img-fluid mb-2" style="max-height:120px;object-fit:contain;">
                                                        <h6>Pijama Short Algodón</h6>
                                                        <p class="text-muted mb-1">SKU: 7702433230014</p>
                                                        <h5 class="text-success">$79.990</h5>
                                                        <span class="badge bg-info">Stock: 5</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="card product-card" onclick="agregarAlCarrito(4, 'Camiseta Manga Corta', 29990, 12)">
                                                    <div class="card-body text-center">
                                                        <img src="/Sistema-de-ventas-AppLink-main/public/assets/images/camiseta-mc.jpg" alt="Camiseta Manga Corta" class="img-fluid mb-2" style="max-height:120px;object-fit:contain;">
                                                        <h6>Camiseta Manga Corta</h6>
                                                        <p class="text-muted mb-1">SKU: 7702433220015</p>
                                                        <h5 class="text-success">$29.990</h5>
                                                        <span class="badge bg-info">Stock: 12</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="card product-card" onclick="agregarAlCarrito(5, 'Bóxer Algodón', 19990, 20)">
                                                    <div class="card-body text-center">
                                                        <img src="/Sistema-de-ventas-AppLink-main/public/assets/images/boxer-algodon.jpg" alt="Bóxer Algodón" class="img-fluid mb-2" style="max-height:120px;object-fit:contain;">
                                                        <h6>Bóxer Algodón</h6>
                                                        <p class="text-muted mb-1">SKU: 7702433210016</p>
                                                        <h5 class="text-success">$19.990</h5>
                                                        <span class="badge bg-info">Stock: 20</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="card product-card" onclick="agregarAlCarrito(6, 'Medias Tobilleras', 9990, 30)">
                                                    <div class="card-body text-center">
                                                        <img src="/Sistema-de-ventas-AppLink-main/public/assets/images/medias-tobilleras.jpg" alt="Medias Tobilleras" class="img-fluid mb-2" style="max-height:120px;object-fit:contain;">
                                                        <h6>Medias Tobilleras</h6>
                                                        <p class="text-muted mb-1">SKU: 7702433200017</p>
                                                        <h5 class="text-success">$9.990</h5>
                                                        <span class="badge bg-info">Stock: 30</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card mb-3">
                                    <div class="card-header bg-white"><h6><i class="fas fa-user"></i> Cliente</h6></div>
                                    <div class="card-body">
                                        <div class="input-group mb-2">
                                            <input type="text" class="form-control form-control-sm" id="buscarCliente" placeholder="Buscar cliente por nombre o teléfono...">
                                            <button class="btn btn-outline-secondary btn-sm" onclick="buscarClientes()">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                        <div id="clienteInfo" class="alert alert-light p-2">
                                            <small class="text-muted">
                                                <i class="fas fa-user-circle"></i> Cliente general
                                                <button class="btn btn-sm btn-link float-end p-0" onclick="limpiarCliente()" title="Usar cliente general">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </small>
                                        </div>
                                        <div id="clientesEncontrados" class="list-group" style="max-height: 200px; overflow-y: auto; display: none;">
                                            <!-- Clientes encontrados aparecerán aquí -->
                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header bg-white"><h6><i class="fas fa-shopping-cart"></i> Carrito (<span id="cantidadItems">0</span>)</h6></div>
                                    <div class="card-body" style="max-height: 400px; overflow-y: auto;" id="carritoItems">
                                        <div class="text-center text-muted py-4">
                                            <i class="fas fa-shopping-cart fa-3x mb-2"></i>
                                            <p>El carrito está vacío</p>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-white">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Subtotal:</span>
                                            <span id="subtotal">$0.0</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2" id="descuentoContainer" style="display: none !important;">
                                            <span>Descuento (<span id="porcentajeDescuento">0</span>%):</span>
                                            <span id="montoDescuento">-$0.0</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-3">
                                            <strong>Total:</strong>
                                            <h4 class="text-success mb-0" id="total">$0.0</h4>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label"><i class="fas fa-credit-card"></i> Método de Pago</label>
                                            <select class="form-select form-select-sm" id="metodoPago">
                                                <option value="efectivo">💵 Efectivo</option>
                                                <option value="tarjeta">💳 Tarjeta de Crédito/Débito</option>
                                                <option value="transferencia">🏦 Transferencia Bancaria</option>
                                                <option value="nequi">📱 Nequi</option>
                                                <option value="daviplata">📱 DaviPlata</option>
                                                <option value="pse">🏛️ PSE</option>
                                            </select>
                                        </div>
                                        <button class="btn btn-pink w-100" id="btnFinalizar" onclick="finalizarVenta()" disabled>
                                            <i class="fas fa-check-circle"></i> Finalizar Venta
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pestaña Gestionar Ventas -->
                    <div class="tab-pane fade" id="gestionar">
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5><i class="fas fa-filter"></i> Filtros de Búsqueda</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label for="filtroFechaInicio" class="form-label">Fecha Inicio</label>
                                                <input type="date" class="form-control" id="filtroFechaInicio">
                                            </div>
                                            <div class="col-md-3">
                                                <label for="filtroFechaFin" class="form-label">Fecha Fin</label>
                                                <input type="date" class="form-control" id="filtroFechaFin">
                                            </div>
                                            <div class="col-md-3">
                                                <label for="filtroEstado" class="form-label">Estado</label>
                                                <select class="form-select" id="filtroEstado">
                                                    <option value="">Todos los estados</option>
                                                    <option value="borrador">Borrador</option>
                                                    <option value="pendiente">Pendiente</option>
                                                    <option value="procesando">Procesando</option>
                                                    <option value="completada">Completada</option>
                                                    <option value="cancelada">Cancelada</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label for="filtroCliente" class="form-label">Cliente</label>
                                                <input type="text" class="form-control" id="filtroCliente" placeholder="Buscar por cliente...">
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-12">
                                                <button class="btn btn-primary" onclick="filtrarVentas()">
                                                    <i class="fas fa-search"></i> Buscar
                                                </button>
                                                <button class="btn btn-secondary" onclick="limpiarFiltros()">
                                                    <i class="fas fa-eraser"></i> Limpiar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5><i class="fas fa-cogs"></i> Gestionar Ventas</h5>
                                <div>
                                    <button class="btn btn-success btn-sm" onclick="crearNuevaVenta()">
                                        <i class="fas fa-plus"></i> Nueva Venta
                                    </button>
                                    <button class="btn btn-info btn-sm" onclick="actualizarListaVentas()">
                                        <i class="fas fa-sync"></i> Actualizar
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Fecha</th>
                                                <th>Cliente</th>
                                                <th>Total</th>
                                                <th>Estado</th>
                                                <th>Método Pago</th>
                                                <th>Vendedor</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="listaVentasGestion">
                                            <tr>
                                                <td colspan="8" class="text-center text-muted">Cargando ventas...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pestaña Historial mejorada -->
                    <div class="tab-pane fade" id="historial">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5><i class="fas fa-history"></i> Historial Completo de Ventas</h5>
                                <div>
                                    <button class="btn btn-outline-primary btn-sm" onclick="exportarHistorial()">
                                        <i class="fas fa-download"></i> Exportar
                                    </button>
                                    <button class="btn btn-outline-info btn-sm" onclick="actualizarHistorial()">
                                        <i class="fas fa-sync"></i> Actualizar
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Fecha/Hora</th>
                                                <th>Cliente</th>
                                                <th>Productos</th>
                                                <th>Subtotal</th>
                                                <th>Descuento</th>
                                                <th>Total</th>
                                                <th>Estado</th>
                                                <th>Método</th>
                                                <th>Vendedor</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="historialVentas">
                                            <tr>
                                                <td colspan="11" class="text-center text-muted">No hay ventas registradas</td>
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
</main>
        </div>
    </div>
    
    <!-- Modal para editar venta -->
    <div class="modal fade" id="editarVentaModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Editar Venta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarVenta">
                        <input type="hidden" id="editVentaId">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="editFechaVenta" class="form-label">Fecha de Venta</label>
                                <input type="datetime-local" class="form-control" id="editFechaVenta">
                            </div>
                            <div class="col-md-6">
                                <label for="editEstadoVenta" class="form-label">Estado</label>
                                <select class="form-select" id="editEstadoVenta">
                                    <option value="borrador">Borrador</option>
                                    <option value="pendiente">Pendiente</option>
                                    <option value="procesando">Procesando</option>
                                    <option value="completada">Completada</option>
                                    <option value="cancelada">Cancelada</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label for="editClienteVenta" class="form-label">Cliente</label>
                                <select class="form-select" id="editClienteVenta">
                                    <option value="">Seleccionar cliente...</option>
                                    <option value="general">Cliente General</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="editMetodoPago" class="form-label">Método de Pago</label>
                                <select class="form-select" id="editMetodoPago">
                                    <option value="efectivo">Efectivo</option>
                                    <option value="tarjeta">Tarjeta</option>
                                    <option value="transferencia">Transferencia</option>
                                    <option value="nequi">Nequi</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label for="editDescuento" class="form-label">Descuento (%)</label>
                                <input type="number" class="form-control" id="editDescuento" min="0" max="100" step="0.1">
                            </div>
                            <div class="col-md-6">
                                <label for="editTotal" class="form-label">Total</label>
                                <input type="number" class="form-control" id="editTotal" step="0.01" readonly>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <label for="editObservaciones" class="form-label">Observaciones</label>
                                <textarea class="form-control" id="editObservaciones" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <h6>Productos en la venta:</h6>
                                <div id="editProductosLista" class="border p-3 rounded bg-light">
                                    <!-- Aquí se cargarán los productos -->
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarEdicionVenta()">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para crear cliente rápido -->
    <div class="modal fade" id="nuevoClienteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-plus"></i> Nuevo Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formNuevoCliente">
                        <div class="mb-3">
                            <label for="nuevoClienteNombre" class="form-label">Nombre Completo</label>
                            <input type="text" class="form-control" id="nuevoClienteNombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="nuevoClienteEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="nuevoClienteEmail">
                        </div>
                        <div class="mb-3">
                            <label for="nuevoClienteTelefono" class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" id="nuevoClienteTelefono">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarNuevoCliente()">
                        <i class="fas fa-save"></i> Guardar Cliente
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para ver detalles de venta -->
    <div class="modal fade" id="detalleVentaModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-receipt"></i> Detalle de Venta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detalleVentaContent">
                    <!-- Contenido se carga dinámicamente -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="imprimirFactura()">
                        <i class="fas fa-print"></i> Imprimir Factura
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toasts y tooltips -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div id="ventasToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ¡Venta finalizada exitosamente!
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        console.log('🚀 Iniciando carga de ventas.php');
        console.log('📍 URL actual:', window.location.href);
        console.log('📊 Bootstrap cargado:', typeof bootstrap !== 'undefined');
        
        // Actualizar mensaje de estado
        function actualizarEstadoCarga(mensaje) {
            const estadoElement = document.getElementById('estadoCarga');
            if (estadoElement) {
                estadoElement.textContent = mensaje;
            }
        }
        
        actualizarEstadoCarga('JavaScript cargando...');
        
        // Variables globales del sistema
        let carrito = [];
        let ventas = [];
        let clienteSeleccionado = null;

        // Función para mostrar notificaciones
        function mostrarToast(mensaje, tipo = 'info') {
            try {
                // Crear el elemento toast si no existe
                let toastContainer = document.getElementById('toastContainer');
                if (!toastContainer) {
                    toastContainer = document.createElement('div');
                    toastContainer.id = 'toastContainer';
                    toastContainer.className = 'position-fixed top-0 end-0 p-3';
                    toastContainer.style.zIndex = '1055';
                    document.body.appendChild(toastContainer);
                }

                // Crear el toast
                const toastId = 'toast-' + Date.now();
                const toastHtml = `
                    <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-header bg-${tipo} text-white">
                            <strong class="me-auto">
                                ${tipo === 'success' ? '✅ Éxito' : 
                                  tipo === 'danger' ? '❌ Error' : 
                                  tipo === 'warning' ? '⚠️ Advertencia' : 
                                  '📢 Información'}
                            </strong>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body">
                            ${mensaje}
                        </div>
                    </div>
                `;

                toastContainer.insertAdjacentHTML('beforeend', toastHtml);

                // Mostrar el toast
                const toastElement = document.getElementById(toastId);
                const toast = new bootstrap.Toast(toastElement);
                toast.show();

                // Eliminar el toast después de que se oculte
                toastElement.addEventListener('hidden.bs.toast', function () {
                    toastElement.remove();
                });

            } catch (error) {
                console.error('Error mostrando toast:', error);
                // Fallback a alert si falla
                alert(mensaje);
            }
        }

        // Función para actualizar el carrito
        function actualizarCarrito() {
            const carritoDiv = document.getElementById('carritoItems');
            const totalDiv = document.getElementById('total');
            
            if (!carritoDiv) {
                console.warn('Elemento carritoItems no encontrado');
                return;
            }
            
            try {
                if (carrito.length === 0) {
                    carritoDiv.innerHTML = '<p class="text-muted text-center">🛒 El carrito está vacío</p>';
                    if (totalDiv) totalDiv.textContent = '$0.00';
                    return;
                }
                
                const total = carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
                
                carritoDiv.innerHTML = carrito.map(item => `
                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                        <div>
                            <strong>${item.nombre}</strong><br>
                            <small class="text-muted">$${item.precio} x ${item.cantidad}</small>
                        </div>
                        <div>
                            <strong>$${(item.precio * item.cantidad).toFixed(2)}</strong>
                            <button class="btn btn-sm btn-danger ms-2" onclick="eliminarDelCarrito(${item.id})" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `).join('');
                
                if (totalDiv) {
                    totalDiv.textContent = `$${total.toFixed(2)}`;
                }
                
            } catch (error) {
                console.error('Error actualizando carrito:', error);
                carritoDiv.innerHTML = '<p class="text-danger">Error actualizando carrito</p>';
            }
        }
        
        // Función para eliminar del carrito
        function eliminarDelCarrito(id) {
            try {
                const index = carrito.findIndex(item => item.id === id);
                if (index !== -1) {
                    carrito.splice(index, 1);
                    actualizarCarrito();
                    mostrarToast('Producto eliminado del carrito', 'info');
                }
            } catch (error) {
                console.error('Error eliminando del carrito:', error);
                mostrarToast('Error al eliminar producto', 'danger');
            }
        }

        // Función para actualizar el carrito
        function actualizarCarrito() {
            const carritoDiv = document.getElementById('carritoItems');
            const totalDiv = document.getElementById('total');
            
            if (!carritoDiv) {
                console.warn('Elemento carritoItems no encontrado');
                return;
            }
            
            try {
                if (carrito.length === 0) {
                    carritoDiv.innerHTML = '<p class="text-muted text-center">🛒 El carrito está vacío</p>';
                    if (totalDiv) totalDiv.textContent = '$0.00';
                    return;
                }
                
                const total = carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
                
                carritoDiv.innerHTML = carrito.map(item => `
                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                        <div>
                            <strong>${item.nombre}</strong><br>
                            <small class="text-muted">$${item.precio} x ${item.cantidad}</small>
                        </div>
                        <div>
                            <strong>$${(item.precio * item.cantidad).toFixed(2)}</strong>
                            <button class="btn btn-sm btn-danger ms-2" onclick="eliminarDelCarrito(${item.id})" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `).join('');
                
                if (totalDiv) {
                    totalDiv.textContent = `$${total.toFixed(2)}`;
                }
                
            } catch (error) {
                console.error('Error actualizando carrito:', error);
                carritoDiv.innerHTML = '<p class="text-danger">Error actualizando carrito</p>';
            }
        }
        
        // Función para eliminar del carrito
        function eliminarDelCarrito(id) {
            try {
                const index = carrito.findIndex(item => item.id === id);
                if (index !== -1) {
                    carrito.splice(index, 1);
                    actualizarCarrito();
                    mostrarToast('Producto eliminado del carrito', 'info');
                }
            } catch (error) {
                console.error('Error eliminando del carrito:', error);
                mostrarToast('Error al eliminar producto', 'danger');
            }
        }
        
        // Funciones para manejar productos
        function agregarAlCarrito(producto) {
            try {
                const productoExistente = carrito.find(item => item.id === producto.id);
                
                if (productoExistente) {
                    productoExistente.cantidad += 1;
                } else {
                    carrito.push({
                        id: producto.id,
                        nombre: producto.nombre,
                        precio: producto.precio,
                        cantidad: 1
                    });
                }
                
                actualizarCarrito();
                mostrarToast(`✅ ${producto.nombre} agregado al carrito`, 'success');
                
            } catch (error) {
                console.error('Error agregando al carrito:', error);
                mostrarToast('Error al agregar producto', 'danger');
            }
        }

        function limpiarCarrito() {
            if (carrito.length === 0) {
                mostrarToast('El carrito ya está vacío', 'info');
                return;
            }
            
            if (confirm('¿Está seguro de que desea vaciar el carrito?')) {
                carrito = [];
                actualizarCarrito();
                mostrarToast('🗑️ Carrito vaciado', 'info');
            }
        }

        function finalizarVenta() {
            try {
                if (carrito.length === 0) {
                    mostrarToast('⚠️ Agregue productos al carrito antes de finalizar', 'warning');
                    return;
                }
                
                const clienteSelect = document.getElementById('destinatarioSelect');
                const cliente = clienteSelect ? clienteSelect.options[clienteSelect.selectedIndex]?.text || 'Cliente General' : 'Cliente General';
                
                const subtotal = carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
                const descuento = parseFloat(document.getElementById('descuentoVenta')?.value) || 0;
                const total = subtotal * (1 - descuento / 100);
                
                const nuevaVenta = {
                    id: ventas.length + 1,
                    fecha: new Date().toISOString(),
                    cliente: cliente,
                    items: [...carrito],
                    total: total,
                    metodo: document.getElementById('metodoPago')?.value || 'efectivo',
                    estado: 'completada',
                    vendedor: '<?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?>',
                    observaciones: document.getElementById('observaciones')?.value || '',
                    descuento: descuento
                };
                
                ventas.push(nuevaVenta);
                carrito = [];
                
                actualizarCarrito();
                actualizarListaVentasGestion();
                actualizarHistorial();
                actualizarEstadisticas();
                
                mostrarToast(`🎉 Venta #${nuevaVenta.id} finalizada exitosamente`, 'success');
                
                // Preguntar si desea generar factura
                setTimeout(() => {
                    if (confirm('¿Desea generar la factura de esta venta?')) {
                        generarFactura(nuevaVenta.id);
                    }
                }, 1000);
                
            } catch (error) {
                console.error('Error finalizando venta:', error);
                mostrarToast('❌ Error al finalizar la venta', 'danger');
            }
        }

        // Cargar ventas al iniciar
        document.addEventListener('DOMContentLoaded', function() {
            // Limpiar caché si es necesario
            console.log('🚀 Iniciando sistema de ventas...');
            console.log('📅 Fecha actual:', new Date().toISOString());
            
            cargarVentas();
        });
        
        async function cargarVentas() {
            try {
                console.log('🔄 Cargando ventas desde API...');
                const response = await fetch('/Sistema-de-ventas-AppLink-main/api/ventas.php?action=listar');
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    ventas = data.data.map(v => ({
                        id: v.numero_venta,
                        fecha: v.fecha_venta || new Date().toISOString().slice(0, 16),
                        cliente: v.cliente_nombre || 'Cliente general',
                        items: JSON.parse(v.productos || '[]'),
                        total: parseFloat(v.total) || 0,
                        metodo: v.metodo_pago || 'efectivo',
                        estado: v.estado || 'pendiente',
                        vendedor: v.vendedor || '<?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?>',
                        observaciones: v.observaciones || '',
                        descuento: parseFloat(v.descuento) || 0
                    }));
                    
                    // Si no hay ventas, agregar datos de ejemplo para testing
                    if (ventas.length === 0) {
                        ventas = [
                            {
                                id: 1,
                                fecha: new Date().toISOString().slice(0, 16),
                                cliente: 'pepito perez',
                                items: [
                                    { id: 1, nombre: 'Producto Demo 1', precio: 25.50, cantidad: 2 },
                                    { id: 2, nombre: 'Producto Demo 2', precio: 15.00, cantidad: 1 }
                                ],
                                total: 66.00,
                                metodo: 'efectivo',
                                estado: 'pendiente',
                                vendedor: '<?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?>',
                                observaciones: 'Venta de ejemplo',
                                descuento: 0
                            },
                            {
                                id: 2,
                                fecha: new Date(Date.now() - 86400000).toISOString().slice(0, 16),
                                cliente: 'Cliente Prueba Auditoría',
                                items: [
                                    { id: 3, nombre: 'Producto Demo 3', precio: 45.00, cantidad: 1 }
                                ],
                                total: 45.00,
                                metodo: 'tarjeta',
                                estado: 'borrador',
                                vendedor: '<?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?>',
                                observaciones: '',
                                descuento: 0
                            }
                        ];
                    }
                    
                    actualizarListaVentasGestion();
                    actualizarHistorial();
                    actualizarEstadisticas();
                } else {
                    console.error('Error cargando ventas:', data.error);
                    // Datos de ejemplo en caso de error de API
                    ventas = [
                        {
                            id: 1,
                            fecha: new Date().toISOString().slice(0, 16),
                            cliente: 'Cliente de Ejemplo',
                            items: [{ id: 1, nombre: 'Producto de Prueba', precio: 30.00, cantidad: 1 }],
                            total: 30.00,
                            metodo: 'efectivo',
                            estado: 'pendiente',
                            vendedor: '<?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?>',
                            observaciones: 'Venta de prueba',
                            descuento: 0
                        }
                    ];
                    actualizarListaVentasGestion();
                    actualizarHistorial();
                    actualizarEstadisticas();
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }
        
        async function buscarClientes() {
            const termino = document.getElementById('buscarCliente').value.trim();
            
            if (termino.length < 2) {
                document.getElementById('clientesEncontrados').style.display = 'none';
                return;
            }
            
            try {
                const response = await fetch(`/Sistema-de-ventas-AppLink-main/api/clientes.php?action=buscar&termino=${encodeURIComponent(termino)}`);
                const data = await response.json();
                
                if (data.success && data.data.length > 0) {
                    mostrarClientesEncontrados(data.data);
                } else {
                    document.getElementById('clientesEncontrados').innerHTML = '<div class="list-group-item text-muted">No se encontraron clientes</div>';
                    document.getElementById('clientesEncontrados').style.display = 'block';
                }
            } catch (error) {
                console.error('Error buscando clientes:', error);
            }
        }
        
        function mostrarClientesEncontrados(clientes) {
            const container = document.getElementById('clientesEncontrados');
            let html = '';
            
            clientes.slice(0, 5).forEach(cliente => {
                const descuento = cliente.revendedora ? parseInt(cliente.descuento) || 0 : 0;
                html += `
                    <div class="list-group-item list-group-item-action" onclick="seleccionarCliente(${cliente.id}, '${cliente.nombre_completo}', '${cliente.telefono}', ${descuento})">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">${cliente.nombre_completo}</h6>
                            ${descuento > 0 ? `<span class="badge bg-success">${descuento}% DESC</span>` : ''}
                        </div>
                        <p class="mb-1"><i class="fas fa-phone"></i> ${cliente.telefono}</p>
                        <small class="text-muted">${cliente.email || 'Sin email'}</small>
                    </div>
                `;
            });
            
            container.innerHTML = html;
            container.style.display = 'block';
        }
        
        function seleccionarCliente(id, nombre, telefono, descuento) {
            clienteSeleccionado = { id, nombre, telefono, descuento };
            
            document.getElementById('clienteInfo').innerHTML = `
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong><i class="fas fa-user"></i> ${nombre}</strong><br>
                        <small class="text-muted"><i class="fas fa-phone"></i> ${telefono}</small>
                        ${descuento > 0 ? `<br><span class="badge bg-success">${descuento}% Descuento</span>` : ''}
                    </div>
                    <button class="btn btn-sm btn-outline-danger" onclick="limpiarCliente()" title="Usar cliente general">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            document.getElementById('clientesEncontrados').style.display = 'none';
            document.getElementById('buscarCliente').value = '';
            
            // Recalcular total con descuento
            calcularTotal();
        }
        
        function limpiarCliente() {
            clienteSeleccionado = null;
            document.getElementById('clienteInfo').innerHTML = `
                <small class="text-muted">
                    <i class="fas fa-user-circle"></i> Cliente general
                </small>
            `;
            document.getElementById('clientesEncontrados').style.display = 'none';
            calcularTotal();
        }
        
        // Agregar evento de búsqueda mientras escribe
        document.addEventListener('DOMContentLoaded', function() {
            const buscarInput = document.getElementById('buscarCliente');
            if (buscarInput) {
                buscarInput.addEventListener('input', function() {
                    if (this.value.length >= 2) {
                        buscarClientes();
                    } else {
                        document.getElementById('clientesEncontrados').style.display = 'none';
                    }
                });
            }
        });
        
        function agregarAlCarrito(id, nombre, precio, stock) {
            const itemExistente = carrito.find(item => item.id === id);
            if (itemExistente) {
                if (itemExistente.cantidad < stock) {
                    itemExistente.cantidad++;
                } else {
                    mostrarToast('No hay suficiente stock', 'danger');
                    return;
                }
            } else {
                carrito.push({ id, nombre, precio, cantidad: 1, stock });
            }
            actualizarCarrito();
        }
        function actualizarCarrito() {
            const container = document.getElementById("carritoItems");
            const btnFinalizar = document.getElementById("btnFinalizar");
            if (carrito.length === 0) {
                container.innerHTML = '<div class="text-center text-muted py-4"><i class="fas fa-shopping-cart fa-3x mb-2"></i><p>El carrito est� vac�o</p></div>';
                btnFinalizar.disabled = true;
            } else {
                let html = '';
                carrito.forEach(item => {
                    const subtotal = item.precio * item.cantidad;
                    html += `<div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <div class="flex-grow-1">
                            <small class="d-block">${item.nombre}</small>
                            <small class="text-muted">$${item.precio.toFixed(2)} x ${item.cantidad}</small>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary btn-sm" onclick="cambiarCantidad(${item.id}, -1)" data-bs-toggle="tooltip" title="Quitar uno">-</button>
                            <button class="btn btn-outline-secondary btn-sm" disabled>${item.cantidad}</button>
                            <button class="btn btn-outline-secondary btn-sm" onclick="cambiarCantidad(${item.id}, 1)" data-bs-toggle="tooltip" title="Agregar uno">+</button>
                        </div>
                        <span class="ms-2">$${subtotal.toFixed(2)}</span>
                        <button class="btn btn-sm btn-danger ms-2" onclick="eliminarDelCarrito(${item.id})" data-bs-toggle="tooltip" title="Eliminar"><i class="fas fa-trash"></i></button>
                    </div>`;
                });
                container.innerHTML = html;
                btnFinalizar.disabled = false;
            }
            calcularTotal();
        }
        function cambiarCantidad(id, cambio) {
            const item = carrito.find(i => i.id === id);
            if (item) {
                item.cantidad += cambio;
                if (item.cantidad <= 0) {
                    eliminarDelCarrito(id);
                } else if (item.cantidad > item.stock) {
                    mostrarToast('No hay suficiente stock', 'danger');
                    item.cantidad = item.stock;
                } else {
                    actualizarCarrito();
                }
            }
        }
        function eliminarDelCarrito(id) {
            carrito = carrito.filter(item => item.id !== id);
            actualizarCarrito();
        }
        function calcularTotal() {
            const subtotal = carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
            let descuento = 0;
            let total = subtotal;
            
            // Aplicar descuento si hay cliente seleccionado
            if (clienteSeleccionado && clienteSeleccionado.descuento > 0) {
                descuento = (subtotal * clienteSeleccionado.descuento) / 100;
                total = subtotal - descuento;
                
                // Mostrar descuento
                document.getElementById('descuentoContainer').style.display = 'flex';
                document.getElementById('porcentajeDescuento').textContent = clienteSeleccionado.descuento;
                document.getElementById('montoDescuento').textContent = '-$' + descuento.toFixed(2);
            } else {
                document.getElementById('descuentoContainer').style.display = 'none';
            }
            
            document.getElementById("subtotal").textContent = "$" + subtotal.toFixed(2);
            document.getElementById("total").textContent = "$" + total.toFixed(2);
            document.getElementById("cantidadItems").textContent = carrito.reduce((sum, item) => sum + item.cantidad, 0);
        }
        
        async function finalizarVenta() {
            if (carrito.length === 0) {
                mostrarToast('El carrito está vacío', 'warning');
                return;
            }
            
            const subtotal = carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
            const descuentoPorcentaje = clienteSeleccionado ? clienteSeleccionado.descuento : 0;
            const descuentoMonto = (subtotal * descuentoPorcentaje) / 100;
            const total = subtotal - descuentoMonto;
            const metodo = document.getElementById("metodoPago").value;
            const cliente = clienteSeleccionado ? clienteSeleccionado.nombre : "Cliente general";
            
            const ventaData = {
                cliente_id: clienteSeleccionado ? clienteSeleccionado.id : null,
                cliente_nombre: cliente,
                productos: carrito,
                subtotal: subtotal,
                descuento: descuentoMonto,
                total: total,
                metodo_pago: metodo,
                estado: 'completada'
            };
            
            try {
                const response = await fetch('/Sistema-de-ventas-AppLink-main/api/ventas.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(ventaData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    mostrarToast(`¡Venta finalizada exitosamente!\nNúmero: ${data.numero_venta}\nCliente: ${cliente}\nTotal: $${total.toFixed(2)}`, 'success');
                    carrito = [];
                    limpiarCliente();
                    actualizarCarrito();
                    await cargarVentas();
                    actualizarHistorial();
                    actualizarEstadisticas();
                } else {
                    mostrarToast('Error al finalizar venta: ' + data.error, 'danger');
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarToast('Error de conexión al finalizar venta', 'danger');
            }
        }
        function actualizarHistorial() {
            const tbody = document.getElementById("historialVentas");
            if (ventas.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No hay ventas registradas</td></tr>';
            } else {
                tbody.innerHTML = ventas.map(v => `<tr>
                    <td>${v.id}</td>
                    <td>${v.fecha}</td>
                    <td>${v.cliente}</td>
                    <td>$${v.total.toFixed(2)}</td>
                    <td>${v.metodo}</td>
                    <td><button class="btn btn-sm btn-info" onclick="verDetalle(${v.id})" data-bs-toggle="tooltip" title="Ver detalle"><i class="fas fa-eye"></i></button></td>
                </tr>`).join('');
            }
        }
        function actualizarEstadisticas() {
            const hoy = new Date();
            const inicioHoy = new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate());
            const inicioMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
            
            // Filtrar ventas por períodos
            const ventasHoy = ventas.filter(v => {
                const fechaVenta = new Date(v.fecha);
                return fechaVenta >= inicioHoy && v.estado !== 'cancelada';
            });
            
            const ventasMes = ventas.filter(v => {
                const fechaVenta = new Date(v.fecha);
                return fechaVenta >= inicioMes && v.estado !== 'cancelada';
            });
            
            const ventasCompletadas = ventas.filter(v => v.estado === 'completada');
            
            // Cálculos detallados
            const totalHoy = ventasHoy.reduce((sum, v) => sum + (v.total || 0), 0);
            const totalMes = ventasMes.reduce((sum, v) => sum + (v.total || 0), 0);
            const totalTransacciones = ventas.filter(v => v.estado !== 'cancelada').length;
            const transaccionesCompletadas = ventasCompletadas.length;
            const promedioTicket = totalTransacciones > 0 ? totalMes / totalTransacciones : 0;
            
            // Actualizar interfaz con animaciones
            actualizarElementoConAnimacion('ventasHoy', `$${totalHoy.toLocaleString('es-CO', {minimumFractionDigits: 2})}`);
            actualizarElementoConAnimacion('ventasMes', `$${totalMes.toLocaleString('es-CO', {minimumFractionDigits: 2})}`);
            actualizarElementoConAnimacion('transacciones', totalTransacciones.toString());
            actualizarElementoConAnimacion('ticketPromedio', `$${promedioTicket.toLocaleString('es-CO', {minimumFractionDigits: 2})}`);
        }
        
        function actualizarElementoConAnimacion(id, valor) {
            const elemento = document.getElementById(id);
            if (!elemento) return;
            
            elemento.style.transform = 'scale(1.1)';
            elemento.style.transition = 'transform 0.2s ease';
            
            setTimeout(() => {
                elemento.textContent = valor;
                elemento.style.transform = 'scale(1)';
            }, 100);
        }
        function verDetalle(id) {
            const venta = ventas.find(v => v.id === id);
            if (venta) {
                mostrarToast('📋 Mostrando detalle de venta', 'info');
                verDetalleCompleto(id);
            }
        }
        
        function busquedaRapida(termino) {
            if (!termino || termino.length < 2) {
                actualizarListaVentasGestion(ventas);
                return;
            }
            
            const terminoLower = termino.toLowerCase().trim();
            const ventasEncontradas = ventas.filter(venta => {
                // Buscar en múltiples campos
                const campos = [
                    venta.id?.toString(),
                    venta.cliente?.toLowerCase(),
                    venta.estado?.toLowerCase(),
                    venta.vendedor?.toLowerCase(),
                    venta.metodo?.toLowerCase(),
                    venta.observaciones?.toLowerCase(),
                    // Buscar en productos
                    ...(venta.items?.map(item => item.nombre?.toLowerCase()) || [])
                ].filter(Boolean);
                
                return campos.some(campo => campo.includes(terminoLower));
            });
            
            actualizarListaVentasGestion(ventasEncontradas);
            
            const mensaje = ventasEncontradas.length > 0 
                ? `🔍 ${ventasEncontradas.length} resultados para "${termino}"`
                : `🔍 Sin resultados para "${termino}"`;
            
            mostrarToast(mensaje, ventasEncontradas.length > 0 ? 'info' : 'warning');
        }
        
        function configurarBusquedaRapida() {
            // Agregar campo de búsqueda rápida si no existe
            const busquedaExistente = document.getElementById('busquedaRapida');
            if (busquedaExistente) return;
            
            const headerCard = document.querySelector('#gestionar .card-header');
            if (headerCard) {
                const busquedaHTML = `
                    <div class="input-group" style="width: 300px;">
                        <input type="text" 
                               class="form-control" 
                               id="busquedaRapida" 
                               placeholder="🔍 Buscar en ventas..." 
                               onkeyup="busquedaRapida(this.value)">
                        <button class="btn btn-outline-secondary" type="button" onclick="document.getElementById('busquedaRapida').value=''; busquedaRapida('')">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
                
                const buttonDiv = headerCard.querySelector('div');
                if (buttonDiv) {
                    buttonDiv.insertAdjacentHTML('afterbegin', busquedaHTML);
                }
            }
        }
        // Toast dinámico mejorado
        function mostrarToast(mensaje, tipo, duracion) {
            var toastEl = document.getElementById('ventasToast');
            if (toastEl) {
                toastEl.className = 'toast align-items-center text-bg-' + (tipo || 'success') + ' border-0';
                toastEl.querySelector('.toast-body').textContent = mensaje;
                
                var toast = new bootstrap.Toast(toastEl, {
                    autohide: true,
                    delay: duracion || (tipo === 'danger' ? 6000 : tipo === 'warning' ? 4000 : 3000)
                });
                toast.show();
                
                // Log para debugging
                console.log(`📢 Toast: [${tipo?.toUpperCase() || 'INFO'}] ${mensaje}`);
            }
        }
        // Inicializar tooltips y confirmaci�n logout
        window.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                new bootstrap.Tooltip(tooltipTriggerEl);
            });
            var logoutLink = document.querySelector('a[href$="logout"]');
            if (logoutLink) {
                logoutLink.addEventListener('click', function(e) {
                    if (!confirm('¿Seguro que deseas cerrar sesión?')) {
                        e.preventDefault();
                    }
                });
            }
            
            // Inicializar formulario y cargar datos
            inicializarFormulario();
            actualizarListaVentasGestion();
            
            // Configurar funciones avanzadas
            configurarBusquedaRapida();
            
            // Validar que todas las funciones críticas estén disponibles
            const funcionesRequeridas = [
                'confirmarVenta', 'eliminarVenta', 'generarFactura', 
                'verDetalleCompleto', 'formatDateTime', 'getEstadoColor',
                'recuperarVentaEliminada', 'exportarHistorial', 'busquedaRapida'
            ];
            
            const funcionesFaltantes = funcionesRequeridas.filter(fn => typeof window[fn] !== 'function');
            if (funcionesFaltantes.length > 0) {
                console.error('❌ Funciones faltantes:', funcionesFaltantes);
                mostrarToast('⚠️ Error: Algunas funciones no están disponibles. Recarga la página.', 'danger');
            } else {
                console.log('✅ Todas las funciones JavaScript están disponibles');
                mostrarToast('🚀 Sistema de ventas mejorado cargado exitosamente', 'success');
            }
            
            // Configurar atajos de teclado
            document.addEventListener('keydown', function(e) {
                // Ctrl + F para búsqueda rápida
                if (e.ctrlKey && e.key === 'f') {
                    e.preventDefault();
                    const busquedaInput = document.getElementById('busquedaRapida');
                    if (busquedaInput) {
                        busquedaInput.focus();
                        busquedaInput.select();
                    }
                }
                
                // Ctrl + N para nueva venta
                if (e.ctrlKey && e.key === 'n') {
                    e.preventDefault();
                    crearNuevaVenta();
                }
                
                // Escape para limpiar filtros
                if (e.key === 'Escape') {
                    const busquedaInput = document.getElementById('busquedaRapida');
                    if (busquedaInput && busquedaInput.value) {
                        busquedaInput.value = '';
                        busquedaRapida('');
                        busquedaInput.blur();
                    }
                }
            });
            
            console.log('🎯 Sistema inicializado con mejoras:', {
                version: '2.0',
                funciones: funcionesRequeridas.length,
                ventasCargadas: ventas.length,
                fecha: new Date().toISOString()
            });
        });
        
        // Funciones para gestión de ventas
        function filtrarVentas() {
            const filtros = {
                fechaInicio: document.getElementById('filtroFechaInicio')?.value,
                fechaFin: document.getElementById('filtroFechaFin')?.value,
                estado: document.getElementById('filtroEstado')?.value,
                cliente: document.getElementById('filtroCliente')?.value?.toLowerCase().trim(),
                montoMinimo: parseFloat(document.getElementById('filtroMontoMinimo')?.value) || 0,
                montoMaximo: parseFloat(document.getElementById('filtroMontoMaximo')?.value) || Infinity
            };
            
            console.log('🔍 Aplicando filtros:', filtros);
            
            let ventasFiltradas = ventas.filter(venta => {
                // Filtro por fecha de inicio
                if (filtros.fechaInicio) {
                    const fechaVenta = new Date(venta.fecha);
                    const fechaInicio = new Date(filtros.fechaInicio);
                    if (fechaVenta < fechaInicio) return false;
                }
                
                // Filtro por fecha fin
                if (filtros.fechaFin) {
                    const fechaVenta = new Date(venta.fecha);
                    const fechaFin = new Date(filtros.fechaFin + 'T23:59:59'); // Incluir todo el día
                    if (fechaVenta > fechaFin) return false;
                }
                
                // Filtro por estado
                if (filtros.estado && venta.estado !== filtros.estado) return false;
                
                // Filtro por cliente (búsqueda flexible)
                if (filtros.cliente) {
                    const nombreCliente = (venta.cliente || '').toLowerCase();
                    const coincide = nombreCliente.includes(filtros.cliente) ||
                                   filtros.cliente.split(' ').every(palabra => nombreCliente.includes(palabra));
                    if (!coincide) return false;
                }
                
                // Filtro por monto mínimo
                if (filtros.montoMinimo > 0 && (venta.total || 0) < filtros.montoMinimo) return false;
                
                // Filtro por monto máximo
                if (filtros.montoMaximo < Infinity && (venta.total || 0) > filtros.montoMaximo) return false;
                
                return true;
            });
            
            // Ordenar resultados por fecha (más recientes primero)
            ventasFiltradas.sort((a, b) => new Date(b.fecha) - new Date(a.fecha));
            
            // Calcular estadísticas de filtro
            const totalFiltrado = ventasFiltradas.reduce((sum, v) => sum + (v.total || 0), 0);
            const estadisticasFiltro = {
                total: ventasFiltradas.length,
                valor: totalFiltrado,
                promedio: ventasFiltradas.length > 0 ? totalFiltrado / ventasFiltradas.length : 0
            };
            
            actualizarListaVentasGestion(ventasFiltradas);
            
            // Mostrar resultados detallados
            const mensaje = ventasFiltradas.length > 0 
                ? `🔍 ${ventasFiltradas.length} ventas encontradas | Total: $${totalFiltrado.toFixed(2)} | Promedio: $${estadisticasFiltro.promedio.toFixed(2)}`
                : '🔍 No se encontraron ventas con los filtros aplicados';
                
            mostrarToast(mensaje, ventasFiltradas.length > 0 ? 'info' : 'warning');
            
            // Actualizar interfaz de filtros
            actualizarResumenFiltros(estadisticasFiltro, filtros);
            
            console.log('✅ Filtros aplicados:', {
                filtros,
                resultados: ventasFiltradas.length,
                valorTotal: totalFiltrado
            });
        }
        
        function actualizarResumenFiltros(estadisticas, filtrosAplicados) {
            // Crear o actualizar resumen de filtros
            let resumenDiv = document.getElementById('resumenFiltros');
            if (!resumenDiv) {
                resumenDiv = document.createElement('div');
                resumenDiv.id = 'resumenFiltros';
                resumenDiv.className = 'alert alert-info mt-2';
                const filtrosCard = document.querySelector('.card-body');
                if (filtrosCard) {
                    filtrosCard.appendChild(resumenDiv);
                }
            }
            
            const filtrosActivos = Object.entries(filtrosAplicados)
                .filter(([key, value]) => value && value !== '' && value !== 0 && value !== Infinity)
                .map(([key, value]) => {
                    switch(key) {
                        case 'fechaInicio': return `Desde: ${new Date(value).toLocaleDateString()}`;
                        case 'fechaFin': return `Hasta: ${new Date(value).toLocaleDateString()}`;
                        case 'estado': return `Estado: ${value}`;
                        case 'cliente': return `Cliente: "${value}"`;
                        case 'montoMinimo': return `Mínimo: $${value}`;
                        case 'montoMaximo': return `Máximo: $${value}`;
                        default: return `${key}: ${value}`;
                    }
                });
            
            if (filtrosActivos.length > 0) {
                resumenDiv.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>🔍 Filtros activos:</strong> ${filtrosActivos.join(' | ')}
                        </div>
                        <div>
                            <strong>Resultados:</strong> ${estadisticas.total} ventas | 
                            <strong>Total:</strong> $${estadisticas.valor.toFixed(2)}
                        </div>
                        <button class="btn btn-sm btn-outline-secondary" onclick="limpiarFiltros()">
                            ✖ Limpiar
                        </button>
                    </div>
                `;
                resumenDiv.style.display = 'block';
            } else {
                resumenDiv.style.display = 'none';
            }
        }
        
        function limpiarFiltros() {
            const campos = [
                'filtroFechaInicio', 'filtroFechaFin', 'filtroEstado', 
                'filtroCliente', 'filtroMontoMinimo', 'filtroMontoMaximo'
            ];
            
            // Limpiar todos los campos con animación
            campos.forEach(campoId => {
                const campo = document.getElementById(campoId);
                if (campo) {
                    // Efecto visual de limpieza
                    campo.style.background = '#fff3cd';
                    campo.value = '';
                    
                    setTimeout(() => {
                        campo.style.background = '';
                        campo.style.transition = 'background 0.3s ease';
                    }, 200);
                }
            });
            
            // Ocultar resumen de filtros
            const resumenDiv = document.getElementById('resumenFiltros');
            if (resumenDiv) {
                resumenDiv.style.display = 'none';
            }
            
            // Restaurar vista completa
            actualizarListaVentasGestion(ventas);
            
            // Actualizar estadísticas generales
            actualizarEstadisticas();
            
            mostrarToast('🧽 Filtros limpiados - Mostrando todas las ventas', 'info');
            
            console.log('🧽 Filtros limpiados, mostrando', ventas.length, 'ventas');
        }
        
        function actualizarListaVentasGestion(ventasData = ventas) {
            try {
                console.log('📋 Actualizando lista de ventas:', ventasData.length);
                
                const tbody = document.getElementById("listaVentasGestion");
                if (!tbody) {
                    console.error('❌ Elemento listaVentasGestion no encontrado');
                    return;
                }
                
                if (!ventasData || ventasData.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No hay ventas que coincidan con los filtros</td></tr>';
                } else {
                tbody.innerHTML = ventasData.map(v => {
                    // Validar datos de la venta
                    const id = v.id || 'N/A';
                    const fecha = v.fecha ? formatDateTime(v.fecha) : 'Sin fecha';
                    const cliente = v.cliente || 'Sin cliente';
                    const total = (v.total || 0).toFixed(2);
                    const estado = v.estado || 'borrador';
                    const metodo = v.metodo || 'No especificado';
                    const vendedor = v.vendedor || 'Sistema';
                    
                    return `
                    <tr>
                        <td><strong>#${id}</strong></td>
                        <td>${fecha}</td>
                        <td>${cliente}</td>
                        <td><strong>$${total}</strong></td>
                        <td><span class="badge bg-${getEstadoColor(estado)}">${estado}</span></td>
                        <td><span class="badge bg-secondary">${metodo}</span></td>
                        <td>${vendedor}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-outline-primary" onclick="editarVenta(${v.id})" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-info" onclick="verDetalleCompleto(${v.id})" title="Ver detalle">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-success" onclick="confirmarVenta(${v.id})" title="Confirmar venta">
                                    <i class="fas fa-check-circle"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-warning" onclick="duplicarVenta(${v.id})" title="Duplicar">
                                    <i class="fas fa-copy"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="eliminarVenta(${v.id})" title="Eliminar venta">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    `;
                }).join('');
            }
            
            } catch (error) {
                console.error('❌ Error actualizando lista de ventas:', error);
                const tbody = document.getElementById("listaVentasGestion");
                if (tbody) {
                    tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Error cargando ventas</td></tr>';
                }
            }
        }
        
        function actualizarListaVentas() {
            mostrarToast('Actualizando lista de ventas...', 'info');
            cargarVentas().then(() => {
                mostrarToast('Lista actualizada exitosamente', 'success');
            }).catch(error => {
                console.error('Error actualizando ventas:', error);
                mostrarToast('Error al actualizar la lista', 'danger');
            });
        }
        
        function editarVenta(id) {
            const venta = ventas.find(v => v.id === id);
            if (!venta) {
                mostrarToast('Venta no encontrada', 'danger');
                return;
            }
            
            // Cargar datos en el modal
            document.getElementById('editVentaId').value = venta.id;
            document.getElementById('editFechaVenta').value = venta.fecha;
            document.getElementById('editEstadoVenta').value = venta.estado;
            document.getElementById('editClienteVenta').value = venta.cliente_id || 'general';
            document.getElementById('editMetodoPago').value = venta.metodo;
            document.getElementById('editDescuento').value = venta.descuento || 0;
            document.getElementById('editTotal').value = venta.total;
            document.getElementById('editObservaciones').value = venta.observaciones || '';
            
            // Mostrar productos
            const productosDiv = document.getElementById('editProductosLista');
            productosDiv.innerHTML = venta.items.map(item => `
                <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                    <div>
                        <strong>${item.nombre}</strong><br>
                        <small class="text-muted">Precio: $${item.precio} | Cantidad: ${item.cantidad}</small>
                    </div>
                    <div>
                        <strong>$${(item.precio * item.cantidad).toFixed(2)}</strong>
                    </div>
                </div>
            `).join('');
            
            // Mostrar modal
            new bootstrap.Modal(document.getElementById('editarVentaModal')).show();
        }
        
        function guardarEdicionVenta() {
            const ventaId = parseInt(document.getElementById('editVentaId').value);
            const ventaIndex = ventas.findIndex(v => v.id === ventaId);
            
            if (ventaIndex === -1) {
                mostrarToast('Error: Venta no encontrada', 'danger');
                return;
            }
            
            // Actualizar datos de la venta
            ventas[ventaIndex].fecha = document.getElementById('editFechaVenta').value;
            ventas[ventaIndex].estado = document.getElementById('editEstadoVenta').value;
            ventas[ventaIndex].cliente = document.getElementById('editClienteVenta').options[document.getElementById('editClienteVenta').selectedIndex].text;
            ventas[ventaIndex].metodo = document.getElementById('editMetodoPago').value;
            ventas[ventaIndex].descuento = parseFloat(document.getElementById('editDescuento').value) || 0;
            ventas[ventaIndex].observaciones = document.getElementById('editObservaciones').value;
            
            // Recalcular total si hay descuento
            const subtotal = ventas[ventaIndex].items.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
            ventas[ventaIndex].total = subtotal * (1 - ventas[ventaIndex].descuento / 100);
            
            // Actualizar vistas
            actualizarListaVentasGestion();
            actualizarHistorial();
            actualizarEstadisticas();
            
            // Cerrar modal y mostrar mensaje
            bootstrap.Modal.getInstance(document.getElementById('editarVentaModal')).hide();
            mostrarToast('Venta actualizada exitosamente', 'success');
        }
        
        function cambiarEstadoVenta(id, nuevoEstado) {
            const ventaIndex = ventas.findIndex(v => v.id === id);
            if (ventaIndex === -1) {
                mostrarToast('Venta no encontrada', 'danger');
                return;
            }
            
            const estadoAnterior = ventas[ventaIndex].estado;
            ventas[ventaIndex].estado = nuevoEstado;
            
            actualizarListaVentasGestion();
            actualizarHistorial();
            
            mostrarToast(`Estado cambiado de "${estadoAnterior}" a "${nuevoEstado}"`, 'success');
        }
        
        function duplicarVenta(id) {
            const ventaOriginal = ventas.find(v => v.id === id);
            if (!ventaOriginal) {
                mostrarToast('Venta no encontrada', 'danger');
                return;
            }
            
            const nuevaVenta = {
                ...ventaOriginal,
                id: ventas.length + 1,
                fecha: new Date().toISOString().slice(0, 16),
                estado: 'borrador'
            };
            
            ventas.push(nuevaVenta);
            actualizarListaVentasGestion();
            actualizarHistorial();
            actualizarEstadisticas();
            
            mostrarToast(`Venta #${id} duplicada como #${nuevaVenta.id}`, 'success');
        }
        
        function cancelarVenta(id) {
            if (!confirm('¿Está seguro de que desea cancelar esta venta?')) {
                return;
            }
            
            cambiarEstadoVenta(id, 'cancelada');
        }

        function confirmarVenta(id) {
            const venta = ventas.find(v => v.id === id);
            if (!venta) {
                mostrarToast('❌ Venta no encontrada', 'danger');
                return;
            }

            // Validaciones completas
            const validaciones = [
                {
                    condicion: !venta.items || venta.items.length === 0,
                    mensaje: '⚠️ No se puede confirmar una venta sin productos'
                },
                {
                    condicion: venta.estado === 'completada',
                    mensaje: 'ℹ️ Esta venta ya está confirmada'
                },
                {
                    condicion: venta.estado === 'cancelada',
                    mensaje: '❌ No se puede confirmar una venta cancelada'
                },
                {
                    condicion: !venta.cliente || venta.cliente.trim() === '',
                    mensaje: '⚠️ La venta debe tener un cliente asignado'
                }
            ];

            const validacionFallida = validaciones.find(v => v.condicion);
            if (validacionFallida) {
                mostrarToast(validacionFallida.mensaje, 'warning');
                return;
            }

            // Calcular totales para confirmación
            const subtotal = venta.items.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
            const descuentoAplicado = subtotal * (venta.descuento || 0) / 100;
            const totalFinal = subtotal - descuentoAplicado;

            const confirmacionHTML = `
                <div class="text-center">
                    <h5>🛒 Confirmar Venta #${id}</h5>
                    <div class="card mt-3">
                        <div class="card-body">
                            <p><strong>Cliente:</strong> ${venta.cliente}</p>
                            <p><strong>Productos:</strong> ${venta.items.length} artículo(s)</p>
                            <p><strong>Subtotal:</strong> $${subtotal.toFixed(2)}</p>
                            ${venta.descuento ? `<p><strong>Descuento:</strong> ${venta.descuento}% (-$${descuentoAplicado.toFixed(2)})</p>` : ''}
                            <h4><strong>Total:</strong> <span class="text-success">$${totalFinal.toFixed(2)}</span></h4>
                        </div>
                    </div>
                    <p class="mt-3 text-muted">Esta acción cambiará el estado a "Completada"</p>
                </div>
            `;

            if (confirm(`¿Confirmar la venta #${id} por $${totalFinal.toFixed(2)}?\n\nCliente: ${venta.cliente}\nProductos: ${venta.items.length} artículo(s)`)) {
                // Cambiar estado a completada
                venta.estado = 'completada';
                venta.fecha_confirmacion = new Date().toISOString();
                
                // Actualizar vistas
                actualizarListaVentasGestion();
                actualizarHistorial();
                actualizarEstadisticas();
                
                mostrarToast(`✅ Venta #${id} confirmada exitosamente`, 'success');
                
                // Opciones post-confirmación
                setTimeout(() => {
                    const opciones = confirm('🧾 ¿Desea generar la factura de esta venta?\n\n✅ Sí - Generar e imprimir factura\n❌ No - Solo confirmar');
                    if (opciones) {
                        generarFactura(id);
                    }
                }, 1000);

                // Log para auditoría
                console.log(`✅ Venta confirmada:`, {
                    id: id,
                    cliente: venta.cliente,
                    total: totalFinal,
                    fecha: new Date().toISOString()
                });
            }
        }

        function eliminarVenta(id) {
            const venta = ventas.find(v => v.id === id);
            if (!venta) {
                mostrarToast('❌ Venta no encontrada', 'danger');
                return;
            }

            // Verificar permisos y estado
            const restricciones = {
                'completada': {
                    permitir: true,
                    mensaje: '⚠️ ADVERTENCIA: Esta venta está COMPLETADA.\n\n🔴 Eliminarla puede afectar:\n• Inventario\n• Reportes contables\n• Historial de cliente\n\n¿Continuar con la eliminación?',
                    requiereConfirmacionDoble: true
                },
                'procesando': {
                    permitir: true,
                    mensaje: '⚠️ Esta venta está en proceso. ¿Desea eliminarla?',
                    requiereConfirmacionDoble: false
                }
            };

            const restriccion = restricciones[venta.estado];
            if (restriccion && !restriccion.permitir) {
                mostrarToast(`❌ No se puede eliminar una venta en estado: ${venta.estado}`, 'danger');
                return;
            }

            // Calcular impacto de la eliminación
            const impacto = {
                productos: venta.items?.length || 0,
                valorTotal: venta.total || 0,
                cliente: venta.cliente || 'Sin cliente',
                fecha: venta.fecha || 'Sin fecha'
            };

            let confirmMessage = `🗑️ ELIMINAR VENTA #${id}\n\n`;
            confirmMessage += `👤 Cliente: ${impacto.cliente}\n`;
            confirmMessage += `📦 Productos: ${impacto.productos} artículo(s)\n`;
            confirmMessage += `💰 Valor: $${impacto.valorTotal.toFixed(2)}\n`;
            confirmMessage += `📅 Fecha: ${formatDateTime(impacto.fecha)}\n`;
            confirmMessage += `📊 Estado: ${venta.estado.toUpperCase()}\n\n`;
            confirmMessage += `⚠️ ESTA ACCIÓN NO SE PUEDE DESHACER\n\n`;
            confirmMessage += `¿Está completamente seguro?`;

            // Primera confirmación
            if (!confirm(confirmMessage)) {
                return;
            }

            // Segunda confirmación para ventas críticas
            if (restriccion?.requiereConfirmacionDoble) {
                const segundaConfirmacion = confirm(`🔴 CONFIRMACIÓN FINAL\n\nEsta es su última oportunidad para cancelar.\n\n¿REALMENTE desea eliminar la venta #${id}?\n\nEscriba mentalmente 'SÍ ELIMINAR' y presione OK para continuar.`);
                if (!segundaConfirmacion) {
                    mostrarToast('✅ Eliminación cancelada por el usuario', 'info');
                    return;
                }
            }

            // Crear backup antes de eliminar (para posible recuperación)
            const backupVenta = {
                ...venta,
                fechaEliminacion: new Date().toISOString(),
                eliminadaPor: '<?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?>'
            };

            // Guardar en localStorage para recuperación temporal
            try {
                const ventasEliminadas = JSON.parse(localStorage.getItem('ventasEliminadas') || '[]');
                ventasEliminadas.push(backupVenta);
                // Mantener solo las últimas 10 eliminadas
                if (ventasEliminadas.length > 10) {
                    ventasEliminadas.shift();
                }
                localStorage.setItem('ventasEliminadas', JSON.stringify(ventasEliminadas));
            } catch (e) {
                console.warn('No se pudo crear backup local:', e);
            }

            // Eliminar la venta del array
            const index = ventas.findIndex(v => v.id === id);
            if (index !== -1) {
                ventas.splice(index, 1);
                
                // Actualizar todas las vistas
                actualizarListaVentasGestion();
                actualizarHistorial();
                actualizarEstadisticas();
                
                mostrarToast(`🗑️ Venta #${id} eliminada exitosamente`, 'success');
                
                // Mostrar opción de deshacer por 10 segundos
                setTimeout(() => {
                    const deshacer = confirm(`⏪ ¿Desea RECUPERAR la venta #${id} eliminada?\n\nEsta opción estará disponible solo por unos minutos.`);
                    if (deshacer) {
                        recuperarVentaEliminada(id);
                    }
                }, 3000);

                // Log de auditoría
                console.log(`🗑️ Venta eliminada:`, {
                    id: id,
                    cliente: impacto.cliente,
                    total: impacto.valorTotal,
                    estado: venta.estado,
                    fecha: new Date().toISOString(),
                    usuario: '<?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?>'
                });
            }
        }

        function recuperarVentaEliminada(id) {
            try {
                const ventasEliminadas = JSON.parse(localStorage.getItem('ventasEliminadas') || '[]');
                const ventaParaRecuperar = ventasEliminadas.find(v => v.id === id);
                
                if (!ventaParaRecuperar) {
                    mostrarToast('❌ No se encontró la venta para recuperar', 'danger');
                    return;
                }

                // Verificar que no exista ya una venta con el mismo ID
                const ventaExistente = ventas.find(v => v.id === id);
                if (ventaExistente) {
                    mostrarToast('⚠️ Ya existe una venta con este ID', 'warning');
                    return;
                }

                // Limpiar datos de eliminación y restaurar
                const ventaRestaurada = { ...ventaParaRecuperar };
                delete ventaRestaurada.fechaEliminacion;
                delete ventaRestaurada.eliminadaPor;
                ventaRestaurada.estado = 'borrador'; // Restaurar como borrador para revisión
                
                // Agregar venta restaurada
                ventas.push(ventaRestaurada);
                
                // Remover del backup
                const nuevasVentasEliminadas = ventasEliminadas.filter(v => v.id !== id);
                localStorage.setItem('ventasEliminadas', JSON.stringify(nuevasVentasEliminadas));
                
                // Actualizar vistas
                actualizarListaVentasGestion();
                actualizarHistorial();
                actualizarEstadisticas();
                
                mostrarToast(`✅ Venta #${id} recuperada exitosamente (estado: borrador)`, 'success');
                
                console.log(`↩️ Venta recuperada:`, {
                    id: id,
                    cliente: ventaRestaurada.cliente,
                    fecha: new Date().toISOString()
                });
                
            } catch (error) {
                console.error('Error recuperando venta:', error);
                mostrarToast('❌ Error al recuperar la venta', 'danger');
            }
        }

        function generarFactura(id, opciones = {}) {
            const venta = ventas.find(v => v.id === id);
            if (!venta) {
                mostrarToast('❌ Venta no encontrada', 'danger');
                return;
            }

            // Validar que la venta esté en estado apropiado
            if (venta.estado === 'borrador') {
                const proceder = confirm('⚠️ Esta venta está en borrador.\n\n¿Desea generar la factura de todas formas?');
                if (!proceder) return;
            }

            // Calcular totales detallados
            const subtotal = venta.items.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
            const descuentoValor = subtotal * (venta.descuento || 0) / 100;
            const totalFinal = subtotal - descuentoValor;

            // Opciones de factura
            const tipoFactura = opciones.tipo || 'completa'; // 'completa', 'simple', 'ticket'
            const incluirLogo = opciones.logo !== false;
            const mostrarDescuentos = venta.descuento > 0;

            mostrarToast('📄 Generando factura...', 'info');

            // Crear ventana de impresión con la factura mejorada
            const facturaWindow = window.open('', '_blank', 'width=800,height=900,scrollbars=yes');
            const facturaHTML = `
                <!DOCTYPE html>
                <html lang="es">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Factura #${venta.id} - LILIPINK</title>
                    <style>
                        * { margin: 0; padding: 0; box-sizing: border-box; }
                        body { 
                            font-family: 'Arial', sans-serif; 
                            margin: 20px; 
                            background: white;
                            color: #333;
                            line-height: 1.4;
                        }
                        .header { 
                            text-align: center; 
                            border-bottom: 3px solid #FF1493; 
                            padding-bottom: 20px; 
                            margin-bottom: 30px;
                        }
                        .header h1 { 
                            color: #FF1493; 
                            font-size: 2.5em; 
                            margin-bottom: 10px;
                            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
                        }
                        .factura-info { 
                            background: #f8f9fa;
                            padding: 15px;
                            border-radius: 8px;
                            margin-bottom: 20px;
                        }
                        .info-row { 
                            display: flex; 
                            justify-content: space-between; 
                            margin-bottom: 8px;
                        }
                        .info-section { 
                            flex: 1; 
                            margin-right: 20px;
                        }
                        .info-section:last-child { margin-right: 0; }
                        .productos { 
                            width: 100%; 
                            border-collapse: collapse; 
                            margin: 20px 0;
                            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                        }
                        .productos th { 
                            background: linear-gradient(135deg, #FF1493, #FF69B4);
                            color: white;
                            padding: 12px 8px;
                            text-align: left;
                            font-weight: bold;
                        }
                        .productos td { 
                            border: 1px solid #ddd; 
                            padding: 10px 8px; 
                            text-align: left;
                        }
                        .productos tr:nth-child(even) { background-color: #f9f9f9; }
                        .productos tr:hover { background-color: #f0f0f0; }
                        .totales { 
                            background: #f8f9fa;
                            padding: 20px;
                            border-radius: 8px;
                            margin-top: 20px;
                        }
                        .total-row { 
                            display: flex; 
                            justify-content: space-between; 
                            margin-bottom: 8px;
                            font-size: 1.1em;
                        }
                        .total-final { 
                            border-top: 2px solid #FF1493;
                            padding-top: 10px;
                            font-size: 1.3em;
                            font-weight: bold;
                            color: #FF1493;
                        }
                        .footer { 
                            text-align: center; 
                            margin-top: 40px; 
                            padding-top: 20px;
                            border-top: 1px solid #ddd;
                            color: #666;
                        }
                        .qr-code { 
                            text-align: center; 
                            margin: 20px 0; 
                        }
                        .estado-badge {
                            display: inline-block;
                            padding: 4px 12px;
                            border-radius: 20px;
                            font-size: 0.9em;
                            font-weight: bold;
                            text-transform: uppercase;
                        }
                        .estado-completada { background: #d4edda; color: #155724; }
                        .estado-pendiente { background: #fff3cd; color: #856404; }
                        .estado-borrador { background: #d1ecf1; color: #0c5460; }
                        @media print {
                            body { margin: 0; }
                            .no-print { display: none; }
                        }
                    </style>
                </head>
                <body>
                    <div class="header">
                        ${incluirLogo ? '<div style="font-size: 3em; margin-bottom: 10px;">💄</div>' : ''}
                        <h1>LILIPINK</h1>
                        <p style="font-size: 1.2em; color: #666;">Sistema de Ventas</p>
                        <p style="font-size: 1.1em; margin-top: 10px;">Factura de Venta #${venta.id}</p>
                    </div>
                    
                    <div class="factura-info">
                        <div style="display: flex; justify-content: space-between;">
                            <div class="info-section">
                                <h3 style="color: #FF1493; margin-bottom: 10px;">📋 Datos de la Venta</h3>
                                <div class="info-row"><strong>Fecha:</strong> <span>${formatDateTime(venta.fecha)}</span></div>
                                <div class="info-row"><strong>Estado:</strong> <span class="estado-badge estado-${venta.estado}">${venta.estado}</span></div>
                                <div class="info-row"><strong>Método de Pago:</strong> <span>${venta.metodo || 'No especificado'}</span></div>
                            </div>
                            <div class="info-section">
                                <h3 style="color: #FF1493; margin-bottom: 10px;">👤 Cliente</h3>
                                <div class="info-row"><strong>Nombre:</strong> <span>${venta.cliente}</span></div>
                                <div class="info-row"><strong>Vendedor:</strong> <span>${venta.vendedor || 'Sistema'}</span></div>
                                ${venta.observaciones ? `<div class="info-row"><strong>Observaciones:</strong> <span>${venta.observaciones}</span></div>` : ''}
                            </div>
                        </div>
                    </div>
                    
                    <h3 style="color: #FF1493; margin-bottom: 15px;">🛍️ Productos</h3>
                    <table class="productos">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th style="text-align: center;">Cantidad</th>
                                <th style="text-align: right;">Precio Unit.</th>
                                <th style="text-align: right;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${venta.items.map((item, index) => `
                                <tr>
                                    <td>
                                        <strong>${item.nombre}</strong>
                                        ${item.descripcion ? `<br><small style="color: #666;">${item.descripcion}</small>` : ''}
                                    </td>
                                    <td style="text-align: center;">${item.cantidad}</td>
                                    <td style="text-align: right;">$${item.precio.toFixed(2)}</td>
                                    <td style="text-align: right;"><strong>$${(item.precio * item.cantidad).toFixed(2)}</strong></td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                    
                    <div class="totales">
                        <div class="total-row">
                            <span>Subtotal (${venta.items.length} producto${venta.items.length !== 1 ? 's' : ''}):</span>
                            <span>$${subtotal.toFixed(2)}</span>
                        </div>
                        ${mostrarDescuentos ? `
                            <div class="total-row">
                                <span>Descuento (${venta.descuento}%):</span>
                                <span style="color: #dc3545;">-$${descuentoValor.toFixed(2)}</span>
                            </div>
                        ` : ''}
                        <div class="total-row total-final">
                            <span>TOTAL A PAGAR:</span>
                            <span>$${totalFinal.toFixed(2)}</span>
                        </div>
                    </div>
                    
                    <div class="footer">
                        <p>📧 Gracias por su compra | 🌐 www.lilipink.com | 📱 WhatsApp: +57 300 123 4567</p>
                        <p style="margin-top: 10px; font-size: 0.9em;">Factura generada el ${new Date().toLocaleString('es-CO')}</p>
                        <p style="margin-top: 5px; font-size: 0.8em; color: #999;">Sistema de Ventas LILIPINK v2.0</p>
                    </div>
                    
                    <script>
                        // Auto-imprimir después de cargar
                        window.onload = function() {
                            setTimeout(() => {
                                window.print();
                            }, 500);
                        };
                        
                        // Cerrar ventana después de imprimir
                        window.onafterprint = function() { 
                            setTimeout(() => {
                                window.close(); 
                            }, 1000);
                        };
                        
                        // Información para desarrolladores
                        console.log('📄 Factura generada:', {
                            id: '${venta.id}',
                            cliente: '${venta.cliente}',
                            total: ${totalFinal},
                            productos: ${venta.items.length},
                            fecha: '${new Date().toISOString()}'
                        });
                    </script>
                </body>
                </html>
            `;
            
            try {
                facturaWindow.document.write(facturaHTML);
                facturaWindow.document.close();
                
                // Actualizar estado de la venta si es necesario
                if (venta.estado === 'pendiente') {
                    venta.estado = 'procesando';
                    actualizarListaVentasGestion();
                }
                
                mostrarToast('✅ Factura generada y enviada a impresión', 'success');
                
                // Log para auditoría
                console.log('📄 Factura generada:', {
                    ventaId: id,
                    cliente: venta.cliente,
                    total: totalFinal,
                    fecha: new Date().toISOString()
                });
                
            } catch (error) {
                console.error('Error generando factura:', error);
                mostrarToast('❌ Error al generar la factura', 'danger');
            }
        }
        
        function verDetalleCompleto(id) {
            const venta = ventas.find(v => v.id === id);
            if (!venta) {
                mostrarToast('Venta no encontrada', 'danger');
                return;
            }
            
            const detalleHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Información General</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Número:</strong></td><td>#${venta.id}</td></tr>
                            <tr><td><strong>Fecha:</strong></td><td>${formatDateTime(venta.fecha)}</td></tr>
                            <tr><td><strong>Cliente:</strong></td><td>${venta.cliente}</td></tr>
                            <tr><td><strong>Estado:</strong></td><td><span class="badge bg-${getEstadoColor(venta.estado)}">${venta.estado}</span></td></tr>
                            <tr><td><strong>Método de Pago:</strong></td><td>${venta.metodo}</td></tr>
                            <tr><td><strong>Vendedor:</strong></td><td>${venta.vendedor || 'Sistema'}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Resumen Financiero</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Subtotal:</strong></td><td>$${(venta.total / (1 - (venta.descuento || 0) / 100)).toFixed(2)}</td></tr>
                            <tr><td><strong>Descuento:</strong></td><td>${venta.descuento || 0}%</td></tr>
                            <tr><td><strong>Total:</strong></td><td><strong>$${venta.total.toFixed(2)}</strong></td></tr>
                        </table>
                    </div>
                </div>
                <hr>
                <h6>Productos</h6>
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Precio Unit.</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${venta.items.map(item => `
                                <tr>
                                    <td>${item.nombre}</td>
                                    <td>${item.cantidad}</td>
                                    <td>$${item.precio.toFixed(2)}</td>
                                    <td>$${(item.precio * item.cantidad).toFixed(2)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
                ${venta.observaciones ? `<hr><h6>Observaciones</h6><p>${venta.observaciones}</p>` : ''}
            `;
            
            document.getElementById('detalleVentaContent').innerHTML = detalleHTML;
            new bootstrap.Modal(document.getElementById('detalleVentaModal')).show();
        }
        
        function crearNuevaVenta() {
            document.querySelector('[data-bs-target="#nueva"]').click();
            mostrarToast('Iniciando nueva venta...', 'info');
        }
        
        function exportarHistorial(formato = 'csv') {
            mostrarToast('📊 Preparando exportación...', 'info');
            
            if (ventas.length === 0) {
                mostrarToast('⚠️ No hay ventas para exportar', 'warning');
                return;
            }

            const fechaExportacion = new Date().toISOString().slice(0, 10);
            
            try {
                switch (formato.toLowerCase()) {
                    case 'csv':
                        exportarCSV(fechaExportacion);
                        break;
                    case 'excel':
                        exportarExcel(fechaExportacion);
                        break;
                    case 'json':
                        exportarJSON(fechaExportacion);
                        break;
                    case 'pdf':
                        exportarPDF(fechaExportacion);
                        break;
                    default:
                        exportarCSV(fechaExportacion);
                }
            } catch (error) {
                console.error('Error en exportación:', error);
                mostrarToast('❌ Error al exportar datos', 'danger');
            }
        }

        function exportarCSV(fecha) {
            const headers = ['ID', 'Fecha', 'Cliente', 'Estado', 'Método Pago', 'Productos', 'Subtotal', 'Descuento', 'Total', 'Vendedor'];
            
            const csvData = ventas.map(venta => {
                const subtotal = venta.items?.reduce((sum, item) => sum + (item.precio * item.cantidad), 0) || 0;
                const productos = venta.items?.map(item => `${item.nombre} (${item.cantidad})`).join('; ') || '';
                
                return [
                    venta.id,
                    formatDateTime(venta.fecha),
                    `"${venta.cliente}"`,
                    venta.estado,
                    venta.metodo || '',
                    `"${productos}"`,
                    subtotal.toFixed(2),
                    `${venta.descuento || 0}%`,
                    venta.total.toFixed(2),
                    venta.vendedor || 'Sistema'
                ].join(',');
            });
            
            const csvContent = [headers.join(','), ...csvData].join('\n');
            descargarArchivo(csvContent, `ventas_${fecha}.csv`, 'text/csv');
            
            mostrarToast('📊 Archivo CSV exportado exitosamente', 'success');
        }

        function exportarJSON(fecha) {
            const exportData = {
                exportadoEl: new Date().toISOString(),
                totalVentas: ventas.length,
                valorTotal: ventas.reduce((sum, v) => sum + v.total, 0),
                ventas: ventas.map(venta => ({
                    ...venta,
                    subtotal: venta.items?.reduce((sum, item) => sum + (item.precio * item.cantidad), 0) || 0
                }))
            };
            
            const jsonContent = JSON.stringify(exportData, null, 2);
            descargarArchivo(jsonContent, `ventas_${fecha}.json`, 'application/json');
            
            mostrarToast('📊 Archivo JSON exportado exitosamente', 'success');
        }

        function descargarArchivo(contenido, nombreArchivo, tipoMime) {
            const blob = new Blob([contenido], { type: tipoMime + ';charset=utf-8;' });
            const link = document.createElement('a');
            
            if (link.download !== undefined) {
                const url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                link.setAttribute('download', nombreArchivo);
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(url);
            } else {
                mostrarToast('❌ Su navegador no soporta descargas automáticas', 'danger');
            }
        }
        
        function guardarNuevoCliente() {
            const nombre = document.getElementById('nuevoClienteNombre').value;
            const email = document.getElementById('nuevoClienteEmail').value;
            const telefono = document.getElementById('nuevoClienteTelefono').value;
            
            if (!nombre.trim()) {
                mostrarToast('El nombre es requerido', 'warning');
                return;
            }
            
            const nuevoClienteId = 'cliente_' + Date.now();
            
            const clienteSelect = document.getElementById('destinatarioSelect');
            const option = new Option(nombre, nuevoClienteId);
            clienteSelect.add(option);
            clienteSelect.value = nuevoClienteId;
            
            bootstrap.Modal.getInstance(document.getElementById('nuevoClienteModal')).hide();
            document.getElementById('formNuevoCliente').reset();
            
            mostrarToast('Cliente creado exitosamente', 'success');
        }
        
        function imprimirFactura() {
            mostrarToast('Preparando factura para impresión...', 'info');
            window.print();
        }
        
        // Funciones de utilidad
        function formatDateTime(dateString) {
            try {
                if (!dateString) return 'Sin fecha';
                const date = new Date(dateString);
                if (isNaN(date.getTime())) return 'Fecha inválida';
                return date.toLocaleDateString('es-CO') + ' ' + date.toLocaleTimeString('es-CO', {hour: '2-digit', minute: '2-digit'});
            } catch (error) {
                console.error('Error en formatDateTime:', error);
                return 'Error de fecha';
            }
        }
        
        function getEstadoColor(estado) {
            try {
                if (!estado) return 'secondary';
                const colores = {
                    'borrador': 'secondary',
                    'pendiente': 'warning',
                    'procesando': 'info',
                    'completada': 'success',
                    'cancelada': 'danger'
                };
                return colores[estado.toLowerCase()] || 'secondary';
            } catch (error) {
                console.error('Error en getEstadoColor:', error);
                return 'secondary';
            }
        }
        
        // Inicializar valores por defecto
        function inicializarFormulario() {
            try {
                console.log('⚙️ Inicializando formulario...');
                const ahora = new Date();
                
                // Configurar fecha y hora actual
                const fechaInput = document.getElementById('fechaVenta');
                const numeroInput = document.getElementById('numeroVenta');
                const vendedorInput = document.getElementById('vendedor');
            
            if (fechaInput) {
                fechaInput.value = ahora.toISOString().slice(0, 16);
                fechaInput.min = new Date(ahora.getTime() - 7 * 24 * 60 * 60 * 1000).toISOString().slice(0, 16); // Máximo 7 días atrás
                fechaInput.max = new Date(ahora.getTime() + 24 * 60 * 60 * 1000).toISOString().slice(0, 16); // Máximo 1 día adelante
            }
            
            if (numeroInput) {
                const numeroVenta = 'V-' + ahora.getFullYear().toString().slice(-2) + 
                                  (ahora.getMonth() + 1).toString().padStart(2, '0') + 
                                  ahora.getDate().toString().padStart(2, '0') + '-' + 
                                  Date.now().toString().slice(-4);
                numeroInput.value = numeroVenta;
            }
            
            // Configurar vendedor actual
            if (vendedorInput && !vendedorInput.value) {
                vendedorInput.value = '<?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?>';
            }
            
            // Inicializar estado por defecto
            const estadoSelect = document.getElementById('estadoVenta');
            if (estadoSelect && !estadoSelect.value) {
                estadoSelect.value = 'borrador';
            }
            
            // Limpiar carrito si tiene datos anteriores
            if (carrito.length > 0) {
                const limpiar = confirm('🛍️ Hay productos en el carrito de una sesión anterior.\n\n¿Desea limpiar el carrito para empezar una nueva venta?');
                if (limpiar) {
                    carrito = [];
                    actualizarCarrito();
                }
            }
            
            // Inicializar opciones de destinatario
            actualizarOpcionesDestinatario();
            
            // Cargar datos iniciales
            cargarClientes();
            
            // Configurar eventos de validación en tiempo real
            configurarValidacionesFormulario();
            
            console.log('✅ Formulario inicializado:', {
                fecha: ahora.toISOString(),
                numeroVenta: numeroInput?.value,
                vendedor: vendedorInput?.value
            });
            
            } catch (error) {
                console.error('❌ Error inicializando formulario:', error);
                mostrarToast('Error al inicializar formulario', 'danger');
            }
        }
        
        function configurarValidacionesFormulario() {
            try {
                console.log('🔧 Configurando validaciones...');
                
                // Validación de fecha
                const fechaInput = document.getElementById('fechaVenta');
            if (fechaInput) {
                fechaInput.addEventListener('change', function() {
                    const fechaSeleccionada = new Date(this.value);
                    const ahora = new Date();
                    const hace7Dias = new Date(ahora.getTime() - 7 * 24 * 60 * 60 * 1000);
                    
                    if (fechaSeleccionada < hace7Dias) {
                        mostrarToast('⚠️ No se pueden crear ventas con fecha mayor a 7 días en el pasado', 'warning');
                        this.value = ahora.toISOString().slice(0, 16);
                    } else if (fechaSeleccionada > new Date(ahora.getTime() + 24 * 60 * 60 * 1000)) {
                        mostrarToast('⚠️ No se pueden crear ventas con fecha futura', 'warning');
                        this.value = ahora.toISOString().slice(0, 16);
                    }
                });
            }
            
            // Validación de número de venta
            const numeroInput = document.getElementById('numeroVenta');
            if (numeroInput) {
                numeroInput.addEventListener('blur', function() {
                    const numeroExistente = ventas.find(v => v.id && v.id.toString() === this.value);
                    if (numeroExistente) {
                        mostrarToast('⚠️ Este número de venta ya existe', 'warning');
                        // Generar nuevo número
                        const ahora = new Date();
                        this.value = 'V-' + ahora.getFullYear().toString().slice(-2) + 
                                   (ahora.getMonth() + 1).toString().padStart(2, '0') + 
                                   ahora.getDate().toString().padStart(2, '0') + '-' + 
                                   Date.now().toString().slice(-4);
                    }
                });
            }
        }
        
        // Función para actualizar opciones según el tipo de destinatario
        function actualizarOpcionesDestinatario() {
            const tipo = document.getElementById('tipoDestinatario').value;
            const select = document.getElementById('destinatarioSelect');
            const modal = document.querySelector('[data-bs-target="#nuevoClienteModal"]');
            
            // Agregar efecto de carga
            select.classList.add('loading-options');
            
            // Limpiar opciones actuales
            select.innerHTML = '<option value="">Cargando opciones...</option>';
            
            setTimeout(() => {
                select.innerHTML = '<option value="">Seleccionar destinatario...</option>';
                
                if (tipo === 'cliente') {
                    // Cargar clientes
                    console.log('📡 Cargando clientes desde API...');
                    fetch('/Sistema-de-ventas-AppLink-main/api/clientes.php?action=listar')
                        .then(response => {
                            console.log('📋 Respuesta de API recibida:', response.status);
                            return response.json();
                        })
                        .then(data => {
                            console.log('📊 Datos de clientes:', data);
                            if (data.success && data.data && Array.isArray(data.data)) {
                                if (data.data.length > 0) {
                                    data.data.forEach(cliente => {
                                        const nombreCliente = cliente.nombre_completo || cliente.nombre || 'Cliente sin nombre';
                                        const option = new Option(`👤 ${nombreCliente}`, cliente.id);
                                        select.add(option);
                                    });
                                    console.log(`✅ ${data.data.length} clientes cargados en ventas`);
                                } else {
                                    select.innerHTML = '<option value="">No hay clientes registrados</option>';
                                }
                            } else {
                                console.error('❌ Error en estructura de respuesta:', data);
                                select.innerHTML = '<option value="">Error: Datos inválidos</option>';
                            }
                            // Agregar cliente general
                            const generalOption = new Option('👤 Cliente General', 'general');
                            select.add(generalOption);
                        })
                        .catch(error => {
                            console.error('❌ Error cargando clientes:', error);
                            select.innerHTML = '<option value="">Error de conexión</option>';
                        });
                    modal.style.display = '';
                    modal.disabled = false;
                    modal.title = 'Agregar nuevo cliente';
                } else if (tipo === 'proveedor') {
                    // Cargar proveedores (datos simulados por ahora)
                    const proveedores = [
                        { id: 'prov1', nombre: 'Proveedor Central S.A.' },
                        { id: 'prov2', nombre: 'Distribuidora del Norte' },
                        { id: 'prov3', nombre: 'Importaciones XYZ' },
                        { id: 'prov4', nombre: 'Textiles Premium' },
                        { id: 'prov5', nombre: 'Mayorista Nacional' }
                    ];
                    
                    proveedores.forEach(proveedor => {
                        const option = new Option(`🏢 ${proveedor.nombre}`, proveedor.id);
                        select.add(option);
                    });
                    modal.style.display = 'none';
                    modal.disabled = true;
                    modal.title = 'No disponible para proveedores';
                } else if (tipo === 'interno') {
                    // Cargar departamentos internos
                    const departamentos = [
                        { id: 'dept1', nombre: 'Almacén General' },
                        { id: 'dept2', nombre: 'Departamento de Ventas' },
                        { id: 'dept3', nombre: 'Control de Calidad' },
                        { id: 'dept4', nombre: 'Administración' },
                        { id: 'dept5', nombre: 'Sucursal Centro' }
                    ];
                    
                    departamentos.forEach(dept => {
                        const option = new Option(`🏛️ ${dept.nombre}`, dept.id);
                        select.add(option);
                    });
                    modal.style.display = 'none';
                    modal.disabled = true;
                    modal.title = 'No disponible para uso interno';
                }
                
                // Remover efecto de carga y agregar highlight
                select.classList.remove('loading-options');
                select.classList.add('destinatario-highlight');
                setTimeout(() => select.classList.remove('destinatario-highlight'), 600);
            }, 300);
        }
        
        function cargarClientes() {
            fetch('../../api/clientes.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        const select = document.getElementById('destinatarioSelect');
                        data.data.forEach(cliente => {
                            const option = new Option(cliente.nombre, cliente.id);
                            select.add(option);
                        });
                    }
                })
                .catch(error => {
                    console.log('Clientes no disponibles, usando datos por defecto');
                });
        }

        // Función para formatear fechas
        function formatDateTime(dateString) {
            try {
                const date = new Date(dateString);
                if (isNaN(date.getTime())) {
                    return 'Fecha inválida';
                }
                
                const now = new Date();
                const diff = now - date;
                const minutes = Math.floor(diff / (1000 * 60));
                const hours = Math.floor(diff / (1000 * 60 * 60));
                const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                
                if (minutes < 60) {
                    return `hace ${minutes} min`;
                } else if (hours < 24) {
                    return `hace ${hours}h`;
                } else if (days < 7) {
                    return `hace ${days}d`;
                } else {
                    return date.toLocaleDateString('es-ES', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric'
                    });
                }
            } catch (error) {
                console.error('Error formateando fecha:', error);
                return 'Fecha inválida';
            }
        }

        // Función para cargar historial
        function cargarHistorial() {
            try {
                const historialDiv = document.getElementById('historialVentas');
                if (!historialDiv) {
                    console.warn('Elemento historialVentas no encontrado');
                    return;
                }

                if (ventas.length === 0) {
                    historialDiv.innerHTML = `
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>No hay ventas registradas</p>
                        </div>
                    `;
                    return;
                }

                const historialHtml = ventas.map(venta => `
                    <div class="card mb-2">
                        <div class="card-body p-3">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <strong>Venta #${venta.id}</strong><br>
                                    <small class="text-muted">${formatDateTime(venta.fecha)}</small>
                                </div>
                                <div class="col-md-3">
                                    <span class="text-primary">${venta.cliente}</span><br>
                                    <small class="text-muted">${venta.items?.length || 0} productos</small>
                                </div>
                                <div class="col-md-2">
                                    <span class="h6 text-success">$${(venta.total || 0).toFixed(2)}</span>
                                </div>
                                <div class="col-md-2">
                                    <span class="badge bg-${venta.estado === 'completada' ? 'success' : 'warning'}">
                                        ${venta.estado || 'completada'}
                                    </span>
                                </div>
                                <div class="col-md-2">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="verDetalleVenta(${venta.id})" title="Ver detalle">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-success" onclick="generarFactura(${venta.id})" title="Generar factura">
                                            <i class="fas fa-file-invoice"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('');

                historialDiv.innerHTML = historialHtml;
                
            } catch (error) {
                console.error('Error cargando historial:', error);
                const historialDiv = document.getElementById('historialVentas');
                if (historialDiv) {
                    historialDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            Error cargando el historial de ventas
                        </div>
                    `;
                }
            }
        }

        // Función para ver detalle de venta
        function verDetalleVenta(ventaId) {
            try {
                const venta = ventas.find(v => v.id === ventaId);
                if (!venta) {
                    mostrarToast('Venta no encontrada', 'danger');
                    return;
                }

                const modal = new bootstrap.Modal(document.getElementById('modalDetalleVenta') || document.createElement('div'));
                
                // Crear modal si no existe
                let modalElement = document.getElementById('modalDetalleVenta');
                if (!modalElement) {
                    const modalHtml = `
                        <div class="modal fade" id="modalDetalleVenta" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Detalle de Venta #${venta.id}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body" id="modalDetalleBody">
                                        <!-- Contenido se carga dinámicamente -->
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                        <button type="button" class="btn btn-primary" onclick="generarFactura(${venta.id})">Generar Factura</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    document.body.insertAdjacentHTML('beforeend', modalHtml);
                    modalElement = document.getElementById('modalDetalleVenta');
                }

                // Llenar contenido del modal
                const modalBody = document.getElementById('modalDetalleBody');
                modalBody.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Información General</h6>
                            <p><strong>Cliente:</strong> ${venta.cliente}</p>
                            <p><strong>Fecha:</strong> ${new Date(venta.fecha).toLocaleString('es-ES')}</p>
                            <p><strong>Vendedor:</strong> ${venta.vendedor || 'N/A'}</p>
                            <p><strong>Método de Pago:</strong> ${venta.metodo || 'efectivo'}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Totales</h6>
                            <p><strong>Subtotal:</strong> $${((venta.total || 0) / (1 - (venta.descuento || 0) / 100)).toFixed(2)}</p>
                            <p><strong>Descuento:</strong> ${venta.descuento || 0}%</p>
                            <p><strong>Total:</strong> <span class="text-success">$${(venta.total || 0).toFixed(2)}</span></p>
                        </div>
                    </div>
                    <hr>
                    <h6>Productos</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unit.</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${(venta.items || []).map(item => `
                                    <tr>
                                        <td>${item.nombre}</td>
                                        <td>${item.cantidad}</td>
                                        <td>$${(item.precio || 0).toFixed(2)}</td>
                                        <td>$${((item.precio || 0) * (item.cantidad || 0)).toFixed(2)}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                    ${venta.observaciones ? `
                        <hr>
                        <h6>Observaciones</h6>
                        <p>${venta.observaciones}</p>
                    ` : ''}
                `;

                const modalInstance = new bootstrap.Modal(modalElement);
                modalInstance.show();

            } catch (error) {
                console.error('Error mostrando detalle de venta:', error);
                mostrarToast('Error al mostrar detalle de la venta', 'danger');
            }
        }
        // Función de inicialización global
        function inicializarSistemaVentas() {
            try {
                console.log('🚀 Inicializando sistema de ventas completo...');
                
                // Verificar que todos los elementos necesarios existan
                const elementosRequeridos = [
                    'tipoDestinatario',
                    'destinatarioSelect',
                    'carritoItems',
                    'total'
                ];
                
                const elementosFaltantes = elementosRequeridos.filter(id => !document.getElementById(id));
                
                if (elementosFaltantes.length > 0) {
                    console.warn('⚠️ Elementos faltantes:', elementosFaltantes);
                }
                
                // Inicializar datos
                if (typeof carrito === 'undefined') window.carrito = [];
                if (typeof ventas === 'undefined') window.ventas = [];
                
                // Cargar datos iniciales
                cargarVentas();
                actualizarOpcionesDestinatario();
                
                console.log('✅ Sistema de ventas inicializado correctamente');
                
            } catch (error) {
                console.error('❌ Error inicializando sistema:', error);
                mostrarToast('Error inicializando el sistema de ventas', 'danger');
            }
        }

        // Auto-inicializar cuando el DOM esté listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', inicializarSistemaVentas);
        } else {
            inicializarSistemaVentas();
        }

        console.log('✅ Sistema de ventas inicializado completamente');
        actualizarEstadoCarga('Sistema listo para usar ✅');
        
    </script>
    
    <script>
        // Verificación final de la página
        window.addEventListener('load', function() {
            console.log('🎯 Página completamente cargada');
            console.log('📋 Total elementos DOM:', document.querySelectorAll('*').length);
            
            // Verificar elementos críticos
            const elementosCriticos = [
                'tipoDestinatario',
                'destinatarioSelect', 
                'carritoItems',
                'total'
            ];
            
            let elementosFaltantes = [];
            elementosCriticos.forEach(id => {
                const elemento = document.getElementById(id);
                if (!elemento) {
                    elementosFaltantes.push(id);
                }
                console.log(`🔍 ${id}:`, elemento ? '✅ OK' : '❌ FALTA');
            });
            
            if (elementosFaltantes.length > 0) {
                console.error('❌ Elementos faltantes:', elementosFaltantes);
                alert('Error cargando la página. Elementos faltantes: ' + elementosFaltantes.join(', '));
            } else {
                console.log('🎉 Todos los elementos críticos encontrados');
            }
        });
    </script>
</body>
</html>
