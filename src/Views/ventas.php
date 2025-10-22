<php
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header("Location: /Sistema-de-ventas-AppLink-main/public/");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventas - Lilipink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
                                            <div class="col-md-6">
                                                <label for="clienteSelect" class="form-label">Cliente</label>
                                                <div class="input-group">
                                                    <select class="form-select" id="clienteSelect">
                                                        <option value="">Seleccionar cliente...</option>
                                                        <option value="general">Cliente General</option>
                                                    </select>
                                                    <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#nuevoClienteModal">
                                                        <i class="fas fa-user-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
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
                                                        <img src="../../public/assets/images/panty-invisible.jpg" alt="Panty Invisible Clásico" class="img-fluid mb-2" style="max-height:120px;object-fit:contain;">
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
                                                        <img src="../../public/assets/images/brasier-pushup.jpg" alt="Brasier Push Up Encaje" class="img-fluid mb-2" style="max-height:120px;object-fit:contain;">
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
                                                        <img src="../../public/assets/images/pijama-short.jpg" alt="Pijama Short Algodón" class="img-fluid mb-2" style="max-height:120px;object-fit:contain;">
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
                                                        <img src="../../public/assets/images/camiseta-mc.jpg" alt="Camiseta Manga Corta" class="img-fluid mb-2" style="max-height:120px;object-fit:contain;">
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
                                                        <img src="../../public/assets/images/boxer-algodon.jpg" alt="Bóxer Algodón" class="img-fluid mb-2" style="max-height:120px;object-fit:contain;">
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
                                                        <img src="../../public/assets/images/medias-tobilleras.jpg" alt="Medias Tobilleras" class="img-fluid mb-2" style="max-height:120px;object-fit:contain;">
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
        let carrito = [];
        let ventas = [];
        let clienteSeleccionado = null;
        
        // Cargar ventas al iniciar
        document.addEventListener('DOMContentLoaded', function() {
            cargarVentas();
        });
        
        async function cargarVentas() {
            try {
                const response = await fetch('/Sistema-de-ventas-AppLink-main/api/ventas.php?action=listar');
                const data = await response.json();
                
                if (data.success) {
                    ventas = data.data.map(v => ({
                        id: v.numero_venta,
                        fecha: new Date(v.fecha_venta).toLocaleDateString(),
                        cliente: v.cliente_nombre || 'Cliente general',
                        items: JSON.parse(v.productos || '[]'),
                        total: parseFloat(v.total) || 0,
                        metodo: v.metodo_pago || 'efectivo'
                    }));
                    actualizarHistorial();
                    actualizarEstadisticas();
                } else {
                    console.error('Error cargando ventas:', data.error);
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
            const totalHoy = ventas.reduce((sum, v) => sum + v.total, 0);
            document.getElementById("ventasHoy").textContent = "$" + totalHoy.toFixed(2);
            document.getElementById("ventasMes").textContent = "$" + totalHoy.toFixed(2);
            document.getElementById("transacciones").textContent = ventas.length;
            document.getElementById("ticketPromedio").textContent = ventas.length > 0 ? "$" + (totalHoy / ventas.length).toFixed(2) : "$0.00";
        }
        function verDetalle(id) {
            const venta = ventas.find(v => v.id === id);
            if (venta) {
                mostrarToast('Mostrando detalle de venta', 'info');
                alert("Detalle de Venta #" + id + "\n" + venta.items.map(i => i.nombre + " x" + i.cantidad).join("\n"));
            }
        }
        // Toast din�mico
        function mostrarToast(mensaje, tipo) {
            var toastEl = document.getElementById('ventasToast');
            if (toastEl) {
                toastEl.className = 'toast align-items-center text-bg-' + (tipo || 'success') + ' border-0';
                toastEl.querySelector('.toast-body').textContent = mensaje;
                var toast = new bootstrap.Toast(toastEl);
                toast.show();
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
        });
        
        // Funciones para gestión de ventas
        function filtrarVentas() {
            const fechaInicio = document.getElementById('filtroFechaInicio').value;
            const fechaFin = document.getElementById('filtroFechaFin').value;
            const estado = document.getElementById('filtroEstado').value;
            const cliente = document.getElementById('filtroCliente').value;
            
            let ventasFiltradas = ventas.filter(venta => {
                let cumpleFiltros = true;
                
                if (fechaInicio && venta.fecha < fechaInicio) cumpleFiltros = false;
                if (fechaFin && venta.fecha > fechaFin) cumpleFiltros = false;
                if (estado && venta.estado !== estado) cumpleFiltros = false;
                if (cliente && !venta.cliente.toLowerCase().includes(cliente.toLowerCase())) cumpleFiltros = false;
                
                return cumpleFiltros;
            });
            
            actualizarListaVentasGestion(ventasFiltradas);
            mostrarToast(`Filtro aplicado: ${ventasFiltradas.length} ventas encontradas`, 'info');
        }
        
        function limpiarFiltros() {
            document.getElementById('filtroFechaInicio').value = '';
            document.getElementById('filtroFechaFin').value = '';
            document.getElementById('filtroEstado').value = '';
            document.getElementById('filtroCliente').value = '';
            actualizarListaVentasGestion(ventas);
            mostrarToast('Filtros limpiados', 'info');
        }
        
        function actualizarListaVentasGestion(ventasData = ventas) {
            const tbody = document.getElementById("listaVentasGestion");
            if (ventasData.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No hay ventas que coincidan con los filtros</td></tr>';
            } else {
                tbody.innerHTML = ventasData.map(v => `
                    <tr>
                        <td><strong>#${v.id}</strong></td>
                        <td>${formatDateTime(v.fecha)}</td>
                        <td>${v.cliente}</td>
                        <td><strong>$${v.total.toFixed(2)}</strong></td>
                        <td><span class="badge bg-${getEstadoColor(v.estado)}">${v.estado}</span></td>
                        <td><span class="badge bg-secondary">${v.metodo}</span></td>
                        <td>${v.vendedor || 'Sistema'}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-outline-primary" onclick="editarVenta(${v.id})" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-info" onclick="verDetalleCompleto(${v.id})" title="Ver detalle">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-success" onclick="cambiarEstadoVenta(${v.id}, 'completada')" title="Finalizar">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-warning" onclick="duplicarVenta(${v.id})" title="Duplicar">
                                    <i class="fas fa-copy"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="cancelarVenta(${v.id})" title="Cancelar">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            }
        }
        
        function actualizarListaVentas() {
            mostrarToast('Actualizando lista de ventas...', 'info');
            setTimeout(() => {
                actualizarListaVentasGestion();
                mostrarToast('Lista actualizada exitosamente', 'success');
            }, 1000);
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
        
        function exportarHistorial() {
            mostrarToast('Exportando historial de ventas...', 'info');
            setTimeout(() => {
                mostrarToast('Historial exportado exitosamente', 'success');
            }, 2000);
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
            
            const clienteSelect = document.getElementById('clienteSelect');
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
            const date = new Date(dateString);
            return date.toLocaleDateString('es-CO') + ' ' + date.toLocaleTimeString('es-CO', {hour: '2-digit', minute: '2-digit'});
        }
        
        function getEstadoColor(estado) {
            const colores = {
                'borrador': 'secondary',
                'pendiente': 'warning',
                'procesando': 'info',
                'completada': 'success',
                'cancelada': 'danger'
            };
            return colores[estado] || 'secondary';
        }
        
        // Inicializar valores por defecto
        function inicializarFormulario() {
            const ahora = new Date();
            document.getElementById('fechaVenta').value = ahora.toISOString().slice(0, 16);
            document.getElementById('numeroVenta').value = 'V-' + Date.now().toString().slice(-6);
            cargarClientes();
        }
        
        function cargarClientes() {
            fetch('../../api/clientes.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        const select = document.getElementById('clienteSelect');
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
    </script>
</body>
</html>
