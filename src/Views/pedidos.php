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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos - Lilipink</title>
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
        .badge-pendiente { background-color: #ffc107; }
        .badge-proceso { background-color: #17a2b8; }
        .badge-entregado { background-color: #28a745; }
        .badge-cancelado { background-color: #dc3545; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar centralizado -->
            <?php 
                $activePage = 'pedidos';
                include __DIR__ . '/partials/sidebar.php';
            ?>
            <main class="col-md-10 px-4">
                <h1 class="mt-3"><i class="fas fa-box"></i> Gesti�n de Pedidos</h1>
                <div class="row my-4">
                    <div class="col-md-3"><div class="card card-stat"><div class="card-body"><h6 class="text-muted">Pendientes</h6><h3 id="pedidosPendientes">0</h3></div></div></div>
                    <div class="col-md-3"><div class="card card-stat"><div class="card-body"><h6 class="text-muted">En Proceso</h6><h3 id="pedidosProceso">0</h3></div></div></div>
                    <div class="col-md-3"><div class="card card-stat"><div class="card-body"><h6 class="text-muted">Entregados Hoy</h6><h3 id="pedidosEntregados">0</h3></div></div></div>
                    <div class="col-md-3"><div class="card card-stat"><div class="card-body"><h6 class="text-muted">Total del Mes</h6><h3 id="pedidosMes">0</h3></div></div></div>
                </div>
                <ul class="nav nav-tabs">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#nuevo">Nuevo Pedido</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#pendientes">Pendientes</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#historial">Historial</button></li>
                </ul>
                <div class="tab-content p-3">
                    <div class="tab-pane fade show active" id="nuevo">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5>Buscar Productos para Pedido</h5>
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" id="buscarProductoPedido" placeholder="Buscar producto...">
                                            <button class="btn btn-pink" onclick="buscarProductoPedido()"><i class="fas fa-search"></i></button>
                                        </div>
                                        <div class="row" id="productosListaPedido">
                                            <div class="col-md-4 mb-3">
                                                <div class="card product-card" onclick="agregarAlPedido(1, 'Panty Invisible Cl�sico', 24990, 15)">
                                                    <div class="card-body text-center">
                                                        <img src="/Sistema-de-ventas-AppLink-main/public/img/panty-invisible.jpg" alt="Panty Invisible Cl�sico" class="img-fluid mb-2" style="max-height:120px;object-fit:contain;">
                                                        <h6>Panty Invisible Cl�sico</h6>
                                                        <p class="text-muted mb-1">SKU: 7702433250012</p>
                                                        <h5 class="text-success">$24.990</h5>
                                                        <span class="badge bg-info">Stock: 15</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="card product-card" onclick="agregarAlPedido(2, 'Brasier Push Up Encaje', 59990, 8)">
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
                                                <div class="card product-card" onclick="agregarAlPedido(3, 'Pijama Short Algod�n', 79990, 5)">
                                                    <div class="card-body text-center">
                                                        <img src="/Sistema-de-ventas-AppLink-main/public/img/pijama-short.jpg" alt="Pijama Short Algod�n" class="img-fluid mb-2" style="max-height:120px;object-fit:contain;">
                                                        <h6>Pijama Short Algod�n</h6>
                                                        <p class="text-muted mb-1">SKU: 7702433230014</p>
                                                        <h5 class="text-success">$79.990</h5>
                                                        <span class="badge bg-info">Stock: 5</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="card product-card" onclick="agregarAlPedido(4, 'Camiseta Manga Corta', 29990, 12)">
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
                                                <div class="card product-card" onclick="agregarAlPedido(5, 'B�xer Algod�n', 19990, 20)">
                                                    <div class="card-body text-center">
                                                        <img src="/Sistema-de-ventas-AppLink-main/public/img/boxer-algodon.jpg" alt="B�xer Algod�n" class="img-fluid mb-2" style="max-height:120px;object-fit:contain;">
                                                        <h6>B�xer Algod�n</h6>
                                                        <p class="text-muted mb-1">SKU: 7702433210016</p>
                                                        <h5 class="text-success">$19.990</h5>
                                                        <span class="badge bg-info">Stock: 20</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="card product-card" onclick="agregarAlPedido(6, 'Medias Tobilleras', 9990, 30)">
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
                                    <div class="card-header bg-white"><h6><i class="fas fa-truck"></i> Proveedor</h6></div>
                                    <div class="card-body">
                                        <select class="form-select form-select-sm" id="proveedorSelect">
                                            <option value="1">Proveedor Central S.A.</option>
                                            <option value="2">Distribuidora del Norte</option>
                                            <option value="3">Importaciones XYZ</option>
                                        </select>
                                        <div class="mt-2">
                                            <small class="text-muted">Fecha Entrega Estimada:</small>
                                            <input type="date" class="form-control form-control-sm mt-1" id="fechaEntrega">
                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header bg-white"><h6><i class="fas fa-clipboard-list"></i> Lista de Pedido (<span id="cantidadItemsPedido">0</span>)</h6></div>
                                    <div class="card-body" style="max-height: 400px; overflow-y: auto;" id="pedidoItems">
                                        <div class="text-center text-muted py-4">
                                            <i class="fas fa-clipboard-list fa-3x mb-2"></i>
                                            <p>No hay productos en el pedido</p>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-white">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Subtotal:</span>
                                            <span id="subtotalPedido">$0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-3">
                                            <strong>Total:</strong>
                                            <h4 class="text-info mb-0" id="totalPedido">$0.00</h4>
                                        </div>
                                        <textarea class="form-control form-control-sm mb-2" id="notasPedido" rows="2" placeholder="Notas adicionales..."></textarea>
                                        <button class="btn btn-pink w-100" id="btnGenerarPedido" onclick="generarPedido()" disabled>
                                            <i class="fas fa-paper-plane"></i> Generar Pedido
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="pendientes">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr><th>#</th><th>Proveedor</th><th>Fecha Pedido</th><th>Entrega Est.</th><th>Total</th><th>Estado</th><th>Acciones</th></tr>
                                </thead>
                                <tbody id="pedidosPendientesTabla">
                                    <tr><td colspan="7" class="text-center text-muted">No hay pedidos pendientes</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="historial">
                        <div class="mb-3">
                            <div class="row">
                                <div class="col-md-3">
                                    <select class="form-select form-select-sm" id="filtroEstado">
                                        <option value="">Todos los estados</option>
                                        <option value="pendiente">Pendiente</option>
                                        <option value="proceso">En Proceso</option>
                                        <option value="entregado">Entregado</option>
                                        <option value="cancelado">Cancelado</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="date" class="form-control form-control-sm" id="filtroFecha">
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr><th>#</th><th>Proveedor</th><th>Fecha Pedido</th><th>Fecha Entrega</th><th>Total</th><th>Estado</th><th>Acciones</th></tr>
                                </thead>
                                <tbody id="historialPedidos">
                                    <tr><td colspan="7" class="text-center text-muted">No hay pedidos registrados</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let pedidoActual = [];
        let pedidos = [];
        const proveedores = {
            1: "Proveedor Central S.A.",
            2: "Distribuidora del Norte",
            3: "Importaciones XYZ"
        };
        
        // Cargar pedidos al iniciar
        document.addEventListener('DOMContentLoaded', function() {
            cargarPedidos();
        });
        
        async function cargarPedidos() {
            try {
                const response = await fetch('/Sistema-de-ventas-AppLink-main/api/pedidos.php?action=listar');
                const data = await response.json();
                
                if (data.success) {
                    pedidos = data.data.map(p => ({
                        id: p.numero_pedido,
                        proveedor: p.cliente_nombre || 'N/A',
                        fechaPedido: new Date(p.fecha_pedido).toLocaleDateString(),
                        fechaEntrega: p.fecha_entrega || '',
                        items: JSON.parse(p.productos || '[]'),
                        total: parseFloat(p.total) || 0,
                        estado: p.estado || 'pendiente',
                        notas: p.observaciones || ''
                    }));
                    actualizarPedidosPendientes();
                    actualizarHistorialPedidos();
                    actualizarEstadisticasPedidos();
                } else {
                    console.error('Error cargando pedidos:', data.error);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }
        
        document.getElementById("fechaEntrega").valueAsDate = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000);
        function agregarAlPedido(id, nombre, precio, stockActual) {
            const itemExistente = pedidoActual.find(item => item.id === id);
            if (itemExistente) {
                itemExistente.cantidad++;
            } else {
                pedidoActual.push({ id, nombre, precio, cantidad: 10, stockActual });
            }
            actualizarPedido();
        }
        function actualizarPedido() {
            const container = document.getElementById("pedidoItems");
            const btnGenerar = document.getElementById("btnGenerarPedido");
            if (pedidoActual.length === 0) {
                container.innerHTML = '<div class="text-center text-muted py-4"><i class="fas fa-clipboard-list fa-3x mb-2"></i><p>No hay productos en el pedido</p></div>';
                btnGenerar.disabled = true;
            } else {
                let html = '';
                pedidoActual.forEach(item => {
                    const subtotal = item.precio * item.cantidad;
                    html += `<div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <div class="flex-grow-1">
                            <small class="d-block">${item.nombre}</small>
                            <small class="text-muted">$${item.precio.toFixed(2)} x ${item.cantidad}</small>
                            <br><small class="badge bg-secondary">Stock: ${item.stockActual}</small>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary btn-sm" onclick="cambiarCantidadPedido(${item.id}, -5)">-5</button>
                            <button class="btn btn-outline-secondary btn-sm" disabled>${item.cantidad}</button>
                            <button class="btn btn-outline-secondary btn-sm" onclick="cambiarCantidadPedido(${item.id}, 5)">+5</button>
                        </div>
                        <span class="ms-2">$${subtotal.toFixed(2)}</span>
                        <button class="btn btn-sm btn-danger ms-2" onclick="eliminarDelPedido(${item.id})"><i class="fas fa-trash"></i></button>
                    </div>`;
                });
                container.innerHTML = html;
                btnGenerar.disabled = false;
            }
            calcularTotalPedido();
        }
        function cambiarCantidadPedido(id, cambio) {
            const item = pedidoActual.find(i => i.id === id);
            if (item) {
                item.cantidad += cambio;
                if (item.cantidad <= 0) {
                    eliminarDelPedido(id);
                } else {
                    actualizarPedido();
                }
            }
        }
        function eliminarDelPedido(id) {
            pedidoActual = pedidoActual.filter(item => item.id !== id);
            actualizarPedido();
        }
        function calcularTotalPedido() {
            const total = pedidoActual.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
            document.getElementById("subtotalPedido").textContent = "$" + total.toFixed(2);
            document.getElementById("totalPedido").textContent = "$" + total.toFixed(2);
            document.getElementById("cantidadItemsPedido").textContent = pedidoActual.reduce((sum, item) => sum + item.cantidad, 0);
        }
        async function generarPedido() {
            if (pedidoActual.length === 0) return;
            
            const proveedorId = document.getElementById("proveedorSelect").value;
            const fechaEntrega = document.getElementById("fechaEntrega").value;
            const notas = document.getElementById("notasPedido").value;
            const total = pedidoActual.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
            
            const pedidoData = {
                cliente_nombre: proveedores[proveedorId],
                productos: pedidoActual,
                total: total,
                estado: 'pendiente',
                fecha_entrega: fechaEntrega,
                observaciones: notas
            };
            
            try {
                const response = await fetch('/Sistema-de-ventas-AppLink-main/api/pedidos.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(pedidoData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert("Pedido generado exitosamente!\nNúmero: " + data.numero_pedido + "\nTotal: $" + total.toFixed(2));
                    pedidoActual = [];
                    document.getElementById("notasPedido").value = "";
                    actualizarPedido();
                    await cargarPedidos();
                    actualizarPedidosPendientes();
                    actualizarHistorialPedidos();
                    actualizarEstadisticasPedidos();
                } else {
                    alert('Error al generar pedido: ' + data.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error de conexión al generar pedido');
            }
        }
        function actualizarPedidosPendientes() {
            const tbody = document.getElementById("pedidosPendientesTabla");
            const pendientes = pedidos.filter(p => p.estado === "pendiente" || p.estado === "proceso");
            if (pendientes.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No hay pedidos pendientes</td></tr>';
            } else {
                tbody.innerHTML = pendientes.map(p => `<tr>
                    <td>${p.id}</td>
                    <td>${p.proveedor}</td>
                    <td>${p.fechaPedido}</td>
                    <td>${p.fechaEntrega}</td>
                    <td>$${p.total.toFixed(2)}</td>
                    <td><span class="badge badge-${p.estado}">${p.estado.toUpperCase()}</span></td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="verDetallePedido('${p.id}')"><i class="fas fa-eye"></i></button>
                        <button class="btn btn-sm btn-success" onclick="cambiarEstadoPedido('${p.id}', 'entregado')"><i class="fas fa-check"></i></button>
                        <button class="btn btn-sm btn-danger" onclick="cambiarEstadoPedido('${p.id}', 'cancelado')"><i class="fas fa-times"></i></button>
                    </td>
                </tr>`).join('');
            }
        }
        function actualizarHistorialPedidos() {
            const tbody = document.getElementById("historialPedidos");
            if (pedidos.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No hay pedidos registrados</td></tr>';
            } else {
                tbody.innerHTML = pedidos.map(p => `<tr>
                    <td>${p.id}</td>
                    <td>${p.proveedor}</td>
                    <td>${p.fechaPedido}</td>
                    <td>${p.fechaEntrega}</td>
                    <td>$${p.total.toFixed(2)}</td>
                    <td><span class="badge badge-${p.estado}">${p.estado.toUpperCase()}</span></td>
                    <td><button class="btn btn-sm btn-info" onclick="verDetallePedido('${p.id}')"><i class="fas fa-eye"></i></button></td>
                </tr>`).join('');
            }
        }
        function actualizarEstadisticasPedidos() {
            const pendientes = pedidos.filter(p => p.estado === "pendiente").length;
            const proceso = pedidos.filter(p => p.estado === "proceso").length;
            const entregadosHoy = pedidos.filter(p => p.estado === "entregado" && p.fechaPedido === new Date().toLocaleDateString()).length;
            document.getElementById("pedidosPendientes").textContent = pendientes;
            document.getElementById("pedidosProceso").textContent = proceso;
            document.getElementById("pedidosEntregados").textContent = entregadosHoy;
            document.getElementById("pedidosMes").textContent = pedidos.length;
        }
        function cambiarEstadoPedido(id, nuevoEstado) {
            const pedido = pedidos.find(p => p.id === id);
            if (pedido) {
                pedido.estado = nuevoEstado;
                actualizarPedidosPendientes();
                actualizarHistorialPedidos();
                actualizarEstadisticasPedidos();
                alert("Estado actualizado a: " + nuevoEstado.toUpperCase());
            }
        }
        function verDetallePedido(id) {
            const pedido = pedidos.find(p => p.id === id);
            if (pedido) {
                let detalle = "PEDIDO " + id + "\n";
                detalle += "Proveedor: " + pedido.proveedor + "\n";
                detalle += "Fecha: " + pedido.fechaPedido + "\n";
                detalle += "Entrega: " + pedido.fechaEntrega + "\n\n";
                detalle += "PRODUCTOS:\n";
                detalle += pedido.items.map(i => i.nombre + " x" + i.cantidad + " = $" + (i.precio * i.cantidad).toFixed(2)).join("\n");
                detalle += "\n\nTOTAL: $" + pedido.total.toFixed(2);
                if (pedido.notas) detalle += "\n\nNotas: " + pedido.notas;
                alert(detalle);
            }
        }
    </script>
</body>
</html>
