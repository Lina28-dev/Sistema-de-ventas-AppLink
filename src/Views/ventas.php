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
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#nueva">Nueva Venta</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#historial">Historial</button></li>
                </ul>
                <div class="tab-content p-3">
                    <div class="tab-pane fade show active" id="nueva">
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
                                                        <img src="/Sistema-de-ventas-AppLink-main/public/img/panty-invisible.jpg" alt="Panty Invisible Clásico" class="img-fluid mb-2" style="max-height:120px;object-fit:contain;">
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
                                                        <img src="/Sistema-de-ventas-AppLink-main/public/img/brasier-pushup.jpg" alt="Brasier Push Up Encaje" class="img-fluid mb-2" style="max-height:120px;object-fit:contain;">
                                                        <h6>Braiser Push Up Encaje</h6>
                                                        <p class="text-muted mb-1">SKU: 7702433240013</p>
                                                        <h5 class="text-success">$59.990</h5>
                                                        <span class="badge bg-info">Stock: 8</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="card product-card" onclick="agregarAlCarrito(3, 'Pijama Short Algodón', 79990, 5)">
                                                    <div class="card-body text-center">
                                                        <img src="/Sistema-de-ventas-AppLink-main/public/img/pijama-short.jpg" alt="Pijama Short Algodón" class="img-fluid mb-2" style="max-height:120px;object-fit:contain;">
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
                                                        <img src="/Sistema-de-ventas-AppLink-main/public/img/camiseta-mc.jpg" alt="Camiseta Manga Corta" class="img-fluid mb-2" style="max-height:120px;object-fit:contain;">
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
                                                        <img src="/Sistema-de-ventas-AppLink-main/public/img/boxer-algodon.jpg" alt="Bóxer Algodón" class="img-fluid mb-2" style="max-height:120px;object-fit:contain;">
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
                                                        <img src="/Sistema-de-ventas-AppLink-main/public/img/medias-tobilleras.jpg" alt="Medias Tobilleras" class="img-fluid mb-2" style="max-height:120px;object-fit:contain;">
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
                                        <input type="text" class="form-control form-control-sm mb-2" id="buscarCliente" placeholder="Buscar cliente...">
                                        <div id="clienteInfo" class="alert alert-light"><small class="text-muted">Cliente general</small></div>
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
                                        <div class="d-flex justify-content-between mb-3">
                                            <strong>Total:</strong>
                                            <h4 class="text-success mb-0" id="total">$0.0</h4>
                                        </div>
                                        <select class="form-select form-select-sm mb-2" id="metodoPago">
                                            <option value="efectivo">Efectivo</option>
                                            <option value="tarjeta">Tarjeta</option>
                                            <option value="transferencia">Transferencia</option>
                                        </select>
                                        <button class="btn btn-pink w-100" id="btnFinalizar" onclick="finalizarVenta()" disabled>
                                            <i class="fas fa-check-circle"></i> Finalizar Venta
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="historial">
                        <table class="table table-hover">
                            <thead>
                                <tr><th>#</th><th>Fecha</th><th>Cliente</th><th>Total</th><th>Método</th><th>Acciones</th></tr>
                            </thead>
                            <tbody id="historialVentas">
                                <tr><td colspan="6" class="text-center text-muted">No hay ventas registradas</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>
</main>
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
                container.innerHTML = '<div class="text-center text-muted py-4"><i class="fas fa-shopping-cart fa-3x mb-2"></i><p>El carrito está vacío</p></div>';
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
        // Toast dinámico
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
