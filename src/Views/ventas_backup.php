<?php
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header("Location: /Sistema-de-ventas-AppLink-main/public/");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Ventas - Lilipink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="/Sistema-de-ventas-AppLink-main/public/css/sidebar.css" rel="stylesheet">
    <link href="/Sistema-de-ventas-AppLink-main/public/css/theme-system.css" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }
        body { 
            background-color: #f8f9fa;
            font-size: 0.875rem;
        }
        .card-stat { border-left: 4px solid #FF1493; }
        .btn-pink { 
            background-color: #FF1493; 
            border-color: #FF1493; 
            color: white;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .btn-pink:hover { background-color: #FF69B4; color: white; }
        .product-card { 
            cursor: pointer; 
            transition: all 0.3s; 
            border: 2px solid transparent;
        }
        .product-card:hover { border-color: #FF1493; transform: scale(1.05); }
        .card-body h6 {
            font-size: 0.75rem;
            font-weight: 500;
        }
        .card-body h3, .card-body h5 {
            font-size: 1.25rem;
            font-weight: 600;
        }
        h1 {
            font-size: 1.75rem;
            font-weight: 600;
        }
        
        /* Responsive Styles */
        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
                margin-left: 0 !important;
            }
            
            h1 {
                font-size: 1.5rem;
                margin-top: 50px;
            }
            
            .card-body h3, .card-body h5 {
                font-size: 1.1rem;
            }
            
            .row .col-md-3, .row .col-md-4, .row .col-md-8 {
                margin-bottom: 15px;
            }
            
            .nav-tabs {
                flex-wrap: wrap;
            }
            
            .nav-tabs .nav-link {
                font-size: 0.8rem;
                padding: 8px 12px;
            }
            
            .product-card:hover {
                transform: none;
            }
        }
        
        @media (max-width: 576px) {
            h1 {
                font-size: 1.25rem;
            }
            
            .card-body h3, .card-body h5 {
                font-size: 1rem;
            }
            
            .btn-pink {
                font-size: 0.75rem;
                padding: 6px 12px;
            }
            
            .main-content {
                padding: 10px;
            }
            
            .card {
                margin-bottom: 10px;
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
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#nueva">Nueva Venta</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#historial">Historial</button></li>
                </ul>
                <div class="tab-content p-3">
                    <div class="tab-pane fade show active" id="nueva">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="mb-0"><i class="fas fa-search"></i> Buscar Productos</h5>
                                            <button class="btn btn-outline-secondary btn-sm" onclick="cargarTodosProductos()"><i class="fas fa-list"></i> Ver Todos</button>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-8">
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="buscarProducto" placeholder="Buscar por nombre, código, color..." onkeypress="if(event.key==='Enter')buscarProducto()">
                                                    <button class="btn btn-pink" onclick="buscarProducto()"><i class="fas fa-search"></i> Buscar</button>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <select class="form-select" id="filtroCategoria" onchange="filtrarPorCategoria()">
                                                    <option value="">Todas las categorías</option>
                                                    <option value="Lencería">Lencería</option>
                                                    <option value="Pijamas">Pijamas</option>
                                                    <option value="Casual">Casual</option>
                                                    <option value="Masculino">Masculino</option>
                                                    <option value="Vestidos">Vestidos</option>
                                                    <option value="Accesorios">Accesorios</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div id="estadoBusqueda" class="alert alert-info d-none">
                                            <i class="fas fa-info-circle"></i> <span id="mensajeEstado"></span>
                                        </div>
                                        <div class="row" id="productosLista">
                                            <div class="col-12 text-center text-muted py-4">
                                                <i class="fas fa-search fa-3x mb-3"></i>
                                                <p>Busca productos para agregarlos al carrito</p>
                                                <small>Usa el campo de búsqueda o haz clic en "Ver Todos" para comenzar</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card mb-3">
                                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0"><i class="fas fa-user"></i> Cliente</h6>
                                        <button class="btn btn-outline-primary btn-sm" onclick="limpiarCliente()"><i class="fas fa-times"></i></button>
                                    </div>
                                    <div class="card-body">
                                        <div class="input-group mb-2">
                                            <input type="text" class="form-control form-control-sm" id="buscarCliente" placeholder="Buscar por nombre, cédula o teléfono..." onkeypress="if(event.key==='Enter')buscarCliente()">
                                            <button class="btn btn-outline-secondary btn-sm" onclick="buscarCliente()"><i class="fas fa-search"></i></button>
                                        </div>
                                        <div id="clienteInfo" class="alert alert-light mb-0">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-user-circle fa-2x text-muted me-2"></i>
                                                <div>
                                                    <strong id="clienteNombre">Cliente General</strong><br>
                                                    <small class="text-muted" id="clienteDetalles">Sin descuentos especiales</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="resultadosCliente" class="mt-2" style="display: none;"></div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0"><i class="fas fa-shopping-cart"></i> Carrito (<span id="cantidadItems">0</span>)</h6>
                                        <button class="btn btn-outline-danger btn-sm" onclick="limpiarCarrito()" id="btnLimpiarCarrito" style="display: none;"><i class="fas fa-trash"></i></button>
                                    </div>
                                    <div class="card-body" style="max-height: 400px; overflow-y: auto;" id="carritoItems">
                                        <div class="text-center text-muted py-4">
                                            <i class="fas fa-shopping-cart fa-3x mb-2"></i>
                                            <p>El carrito está vacío</p>
                                            <small>Agrega productos para comenzar la venta</small>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-white">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Subtotal:</span>
                                            <span id="subtotal">$0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2" id="descuentoRow" style="display: none;">
                                            <span class="text-success">Descuento (<span id="porcentajeDescuento">0</span>%):</span>
                                            <span class="text-success" id="montoDescuento">-$0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-3 border-top pt-2">
                                            <strong>Total a Pagar:</strong>
                                            <h4 class="text-success mb-0" id="total">$0.00</h4>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small">Método de Pago:</label>
                                            <select class="form-select form-select-sm" id="metodoPago">
                                                <option value="efectivo">💰 Efectivo</option>
                                                <option value="tarjeta">💳 Tarjeta Débito/Crédito</option>
                                                <option value="transferencia">📱 Transferencia/Nequi</option>
                                                <option value="mixto">🔄 Pago Mixto</option>
                                            </select>
                                        </div>
                                        <div class="mb-2" id="campoEfectivo" style="display: none;">
                                            <label class="form-label small">Efectivo Recibido:</label>
                                            <input type="number" class="form-control form-control-sm" id="efectivoRecibido" placeholder="0.00" onchange="calcularCambio()">
                                            <small class="text-muted" id="cambioTexto"></small>
                                        </div>
                                        <button class="btn btn-pink w-100" id="btnFinalizar" onclick="finalizarVenta()" disabled>
                                            <i class="fas fa-check-circle"></i> Finalizar Venta
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="historial">
                        <div class="card">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h6 class="mb-0"><i class="fas fa-history"></i> Historial de Ventas</h6>
                                <div>
                                    <button class="btn btn-outline-success btn-sm me-2" onclick="exportarVentas()"><i class="fas fa-file-excel"></i> Exportar</button>
                                    <button class="btn btn-outline-primary btn-sm" onclick="actualizarHistorial()"><i class="fas fa-sync"></i> Actualizar</button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <input type="date" class="form-control form-control-sm" id="fechaDesde" onchange="filtrarVentas()">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="date" class="form-control form-control-sm" id="fechaHasta" onchange="filtrarVentas()">
                                    </div>
                                    <div class="col-md-4">
                                        <select class="form-select form-select-sm" id="filtroMetodo" onchange="filtrarVentas()">
                                            <option value="">Todos los métodos</option>
                                            <option value="efectivo">Efectivo</option>
                                            <option value="tarjeta">Tarjeta</option>
                                            <option value="transferencia">Transferencia</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Fecha/Hora</th>
                                                <th>Cliente</th>
                                                <th>Items</th>
                                                <th>Total</th>
                                                <th>Método</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="historialVentas">
                                            <tr><td colspan="8" class="text-center text-muted py-4">No hay ventas registradas</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <span class="text-muted small" id="contadorVentas">Mostrando 0 ventas</span>
                                    <nav><ul class="pagination pagination-sm mb-0" id="paginacionVentas"></ul></nav>
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
    <!-- Modal Detalle Venta -->
    <div class="modal fade" id="modalDetalleVenta" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-receipt"></i> Detalle de Venta #<span id="numeroVenta"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="contenidoDetalleVenta">
                    <!-- Contenido del detalle -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="imprimirRecibo()"><i class="fas fa-print"></i> Imprimir</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Confirmación Venta -->
    <div class="modal fade" id="modalConfirmacionVenta" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-check-circle"></i> ¡Venta Exitosa!</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                        <h4>Venta #<span id="numeroVentaConfirmacion"></span></h4>
                        <p class="text-muted">Total: $<span id="totalVentaConfirmacion"></span></p>
                    </div>
                    <div id="cambioInfo" class="alert alert-info" style="display: none;">
                        <strong>Cambio a entregar: $<span id="cambioMonto"></span></strong>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-primary" onclick="imprimirRecibo()"><i class="fas fa-print"></i> Imprimir Recibo</button>
                    <button type="button" class="btn btn-success" onclick="nuevaVenta()"><i class="fas fa-plus"></i> Nueva Venta</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toasts -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div id="ventasToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ¡Venta procesada exitosamente!
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
        </div>
        <div id="errorToast" class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="errorMessage">
                    Error en la operación
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/Sistema-de-ventas-AppLink-main/public/js/theme-system.js"></script>
    <script src="/Sistema-de-ventas-AppLink-main/public/js/ventas.js"></script>
        function buscarProducto() {
            const busqueda = document.getElementById('buscarProducto').value;
            const container = document.getElementById('productosLista');
            
            if (!busqueda.trim()) {
                container.innerHTML = `
                    <div class="col-12 text-center text-muted py-4">
                        <i class="fas fa-search fa-3x mb-3"></i>
                        <p>Ingresa un término de búsqueda</p>
                        <small>Busca productos por nombre, SKU o categoría</small>
                    </div>
                `;
                return;
            }
            
            // Aquí se integraría con la API o controlador para buscar productos
            container.innerHTML = `
                <div class="col-12 text-center text-muted py-4">
                    <i class="fas fa-spinner fa-spin fa-3x mb-3"></i>
                    <p>Buscando productos...</p>
                    <small>Conectando con la base de datos</small>
                </div>
            `;
            
            // Simulación de búsqueda sin resultados por el momento
            setTimeout(() => {
                container.innerHTML = `
                    <div class="col-12 text-center text-muted py-4">
                        <i class="fas fa-search fa-3x mb-3"></i>
                        <p>No se encontraron productos</p>
                        <small>Intenta con otro término de búsqueda</small>
                    </div>
                `;
            }, 1000);
        }
        
        function cargarProductos() {
            const container = document.getElementById('productosLista');
            container.innerHTML = `
                <div class="col-12 text-center text-muted py-4">
                    <i class="fas fa-box-open fa-3x mb-3"></i>
                    <p>No hay productos disponibles</p>
                    <small>Los productos se cargarán dinámicamente desde la base de datos</small>
                </div>
            `;
        }
        
        let carrito = [];
        let ventas = [];
        
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
            const total = carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
            document.getElementById("subtotal").textContent = "$" + total.toFixed(2);
            document.getElementById("total").textContent = "$" + total.toFixed(2);
            document.getElementById("cantidadItems").textContent = carrito.reduce((sum, item) => sum + item.cantidad, 0);
        }
        function finalizarVenta() {
            if (carrito.length === 0) return;
            const total = carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
            const metodo = document.getElementById("metodoPago").value;
            const venta = {
                id: ventas.length + 1,
                fecha: new Date().toLocaleDateString(),
                cliente: "Cliente general",
                items: [...carrito],
                total: total,
                metodo: metodo
            };
            ventas.push(venta);
            mostrarToast('Venta finalizada exitosamente!', 'success');
            carrito = [];
            actualizarCarrito();
            actualizarHistorial();
            actualizarEstadisticas();
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
        // Inicializar tooltips y confirmación logout
        window.addEventListener('DOMContentLoaded', function() {
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
            
            // Cargar productos iniciales
            cargarProductos();
            
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
        });
    </script>
</body>
</html>
