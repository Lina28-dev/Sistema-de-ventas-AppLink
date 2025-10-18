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
    <title>Clientes - Lilipink</title>
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
        .badge-revendedor { background-color: #FF1493; color: white; }
        .badge-cliente { background-color: #17a2b8; color: white; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar centralizado -->
            <?php 
                $activePage = 'clientes';
                include __DIR__ . '/partials/sidebar.php';
            ?>
            <main class="col-md-10 px-4">
                <h1 class="mt-3"><i class="fas fa-users"></i> Gestión de Clientes</h1>
                <div class="row my-4">
                    <div class="col-md-3"><div class="card card-stat"><div class="card-body"><h6 class="text-muted">Total Clientes</h6><h3 id="totalClientes">0</h3></div></div></div>
                    <div class="col-md-3"><div class="card card-stat"><div class="card-body"><h6 class="text-muted">Clientes con Historial</h6><h3 id="totalRevendedoras">0</h3></div></div></div>
                    <div class="col-md-3"><div class="card card-stat"><div class="card-body"><h6 class="text-muted">Nuevos Este Mes</h6><h3 id="nuevosMes">0</h3></div></div></div>
                    <div class="col-md-3"><div class="card card-stat"><div class="card-body"><h6 class="text-muted">Activos</h6><h3 id="clientesActivos">0</h3></div></div></div>
                </div>
                <ul class="nav nav-tabs">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#lista">Lista de Clientes</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#nuevo">Nuevo Cliente</button></li>
                </ul>
                <div class="tab-content p-3">
                    <div class="tab-pane fade show active" id="lista">
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="buscarCliente" placeholder="Buscar por nombre, teléfono o identificación...">
                                            <button class="btn btn-pink" onclick="buscarCliente()"><i class="fas fa-search"></i></button>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-select" id="filtroTipo" onchange="filtrarClientes()">
                                            <option value="">Todos</option>
                                            <option value="cliente">Clientes</option>
                                            <option value="revendedor">Empleados</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <button class="btn btn-success w-100" onclick="exportarClientes()"><i class="fas fa-file-excel"></i> Exportar</button>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr><th>#</th><th>Nombre</th><th>Identificación</th><th>Teléfono</th><th>Email</th><th>Localidad</th><th>Tipo</th><th>Descuento</th><th>Acciones</th></tr>
                                        </thead>
                                        <tbody id="tablaClientes">
                                            <tr><td colspan="9" class="text-center text-muted">No hay clientes registrados</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted" id="contadorClientes">Mostrando 0 clientes</span>
                                    <nav><ul class="pagination pagination-sm mb-0" id="paginacion"></ul></nav>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="nuevo">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-user-plus"></i> Registrar Nuevo Cliente</h5>
                                <form id="formCliente">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label"><i class="fas fa-user"></i> Nombre Completo *</label>
                                            <input type="text" class="form-control" id="nombreCompleto" required>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label"><i class="fas fa-id-card"></i> Tipo ID *</label>
                                            <select class="form-select" id="tipoId" required>
                                                <option value="CC">Cédula</option>
                                                <option value="CE">Cédula Extranjería</option>
                                                <option value="NIT">NIT</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label"><i class="fas fa-hashtag"></i> Número C.C *</label>
                                            <input type="text" class="form-control" id="numeroId" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label"><i class="fas fa-phone"></i> Teléfono *</label>
                                            <input type="tel" class="form-control" id="telefono" required>
                                        </div>
                                        <div class="col-md-8 mb-3">
                                            <label class="form-label"><i class="fas fa-envelope"></i> Email</label>
                                            <input type="email" class="form-control" id="email">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label"><i class="fas fa-map-marker-alt"></i> Dirección</label>
                                            <input type="text" class="form-control" id="direccion">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label"><i class="fas fa-city"></i> Ciudad *</label>
                                            <input type="text" class="form-control" id="ciudad" value="Bogotá" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label"><i class="fas fa-map"></i> Localidad *</label>
                                            <select class="form-select" id="localidad" required>
                                                <option value="">Seleccione...</option>
                                                <option value="Usaquén">Usaquén</option>
                                                <option value="Chapinero">Chapinero</option>
                                                <option value="Santa Fe">Santa Fe</option>
                                                <option value="San Cristóbal">San Cristóbal</option>
                                                <option value="Usme">Usme</option>
                                                <option value="Kennedy">Kennedy</option>
                                                <option value="Fontibón">Fontibón</option>
                                                <option value="Engativá">Engativá</option>
                                                <option value="Suba">Suba</option>
                                                <option value="Bosa">Bosa</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label"><i class="fas fa-mail-bulk"></i> Código Postal</label>
                                            <input type="text" class="form-control" id="codigoPostal">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label"><i class="fas fa-user-tag"></i> Tipo de Cliente</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="tipoCliente" id="esCliente" value="0" checked>
                                                <label class="form-check-label" for="esCliente">Cliente Normal</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="tipoCliente" id="esRevendedor" value="1">
                                                <label class="form-check-label" for="esRevendedor">Cliente con Historial</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3" id="campoDescuento" style="display: none;">
                                            <label class="form-label"><i class="fas fa-percent"></i> Descuento (%)</label>
                                            <input type="number" class="form-control" id="descuento" min="0" max="50" value="0">
                                            <small class="text-muted">Solo para clientes con historial de compra (0-50%)</small>
                                        </div>
                                    </div>
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-pink btn-lg"><i class="fas fa-save"></i> Guardar Cliente</button>
                                        <button type="button" class="btn btn-secondary" onclick="limpiarFormulario()"><i class="fas fa-times"></i> Cancelar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let clientes = [];
        let clienteEditando = null;
        document.querySelectorAll('input[name="tipoCliente"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.getElementById('campoDescuento').style.display = this.value === '1' ? 'block' : 'none';
                if (this.value === '0') document.getElementById('descuento').value = '0';
            });
        });
        document.getElementById('formCliente').addEventListener('submit', function(e) {
            e.preventDefault();
            const cliente = {
                id: clienteEditando ? clienteEditando.id : 'C' + String(clientes.length + 1).padStart(4, '0'),
                nombre: document.getElementById('nombreCompleto').value,
                tipoId: document.getElementById('tipoId').value,
                numeroId: document.getElementById('numeroId').value,
                telefono: document.getElementById('telefono').value,
                email: document.getElementById('email').value,
                direccion: document.getElementById('direccion').value,
                ciudad: document.getElementById('ciudad').value,
                localidad: document.getElementById('localidad').value,
                codigoPostal: document.getElementById('codigoPostal').value,
                tipo: document.querySelector('input[name="tipoCliente"]:checked').value,
                descuento: document.getElementById('descuento').value,
                fechaRegistro: new Date().toLocaleDateString()
            };
            if (clienteEditando) {
                const index = clientes.findIndex(c => c.id === clienteEditando.id);
                clientes[index] = cliente;
                clienteEditando = null;
                alert('Cliente actualizado exitosamente');
            } else {
                clientes.push(cliente);
                alert('Cliente registrado exitosamente');
            }
            limpiarFormulario();
            actualizarTablaClientes();
            actualizarEstadisticas();
            document.querySelector('[data-bs-target="#lista"]').click();
        });
        function actualizarTablaClientes() {
            const tbody = document.getElementById('tablaClientes');
            if (clientes.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted">No hay clientes registrados</td></tr>';
            } else {
                tbody.innerHTML = clientes.map((c, i) => `<tr>
                    <td>${i + 1}</td>
                    <td>${c.nombre}</td>
                    <td>${c.tipoId} ${c.numeroId}</td>
                    <td>${c.telefono}</td>
                    <td>${c.email || '-'}</td>
                    <td>${c.localidad}</td>
                    <td><span class="badge ${c.tipo === '1' ? 'badge-revendedor' : 'badge-cliente'}">${c.tipo === '1' ? 'Revendedora' : 'Cliente'}</span></td>
                    <td>${c.descuento}%</td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="editarCliente('${c.id}')" title="Editar"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-success" onclick="verDetalles('${c.id}')" title="Ver detalles"><i class="fas fa-eye"></i></button>
                        <button class="btn btn-sm btn-danger" onclick="eliminarCliente('${c.id}')" title="Eliminar"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>`).join('');
            }
            document.getElementById('contadorClientes').textContent = `Mostrando ${clientes.length} clientes`;
        }
        function actualizarEstadisticas() {
            document.getElementById('totalClientes').textContent = clientes.length;
            document.getElementById('totalRevendedoras').textContent = clientes.filter(c => c.tipo === '1').length;
            document.getElementById('nuevosMes').textContent = clientes.length;
            document.getElementById('clientesActivos').textContent = clientes.length;
        }
        function editarCliente(id) {
            const cliente = clientes.find(c => c.id === id);
            if (cliente) {
                clienteEditando = cliente;
                document.getElementById('nombreCompleto').value = cliente.nombre;
                document.getElementById('tipoId').value = cliente.tipoId;
                document.getElementById('numeroId').value = cliente.numeroId;
                document.getElementById('telefono').value = cliente.telefono;
                document.getElementById('email').value = cliente.email;
                document.getElementById('direccion').value = cliente.direccion;
                document.getElementById('ciudad').value = cliente.ciudad;
                document.getElementById('localidad').value = cliente.localidad;
                document.getElementById('codigoPostal').value = cliente.codigoPostal;
                if (cliente.tipo === '1') {
                    document.getElementById('esClienteconHistorial').checked = true;
                    document.getElementById('campoDescuento').style.display = 'block';
                    document.getElementById('descuento').value = cliente.descuento;
                } else {
                    document.getElementById('esCliente').checked = true;
                }
                document.querySelector('[data-bs-target="#nuevo"]').click();
            }
        }
        function verDetalles(id) {
            const cliente = clientes.find(c => c.id === id);
            if (cliente) {
                alert(`DETALLES DEL CLIENTE\n\nID: ${cliente.id}\nNombre: ${cliente.nombre}\nIdentificación: ${cliente.tipoId} ${cliente.numeroId}\nTeléfono: ${cliente.telefono}\nEmail: ${cliente.email || 'No registrado'}\nDirección: ${cliente.direccion || 'No registrada'}\nLocalidad: ${cliente.localidad}, ${cliente.ciudad}\nTipo: ${cliente.tipo === '1' ? 'Revendedora' : 'Cliente'}\nDescuento: ${cliente.descuento}%\nFecha Registro: ${cliente.fechaRegistro}`);
            }
        }
        function eliminarCliente(id) {
            if (confirm('¿Está seguro de eliminar este cliente?')) {
                clientes = clientes.filter(c => c.id !== id);
                actualizarTablaClientes();
                actualizarEstadisticas();
            }
        }
        function limpiarFormulario() {
            document.getElementById('formCliente').reset();
            document.getElementById('campoDescuento').style.display = 'none';
            clienteEditando = null;
        }
        function buscarCliente() {
            const termino = document.getElementById('buscarCliente').value.toLowerCase();
            const filtrados = clientes.filter(c => 
                c.nombre.toLowerCase().includes(termino) || 
                c.telefono.includes(termino) || 
                c.numeroId.includes(termino)
            );
            mostrarClientesFiltrados(filtrados);
        }
        function filtrarClientes() {
            const tipo = document.getElementById('filtroTipo').value;
            if (tipo === '') {
                actualizarTablaClientes();
            } else {
                const filtrados = clientes.filter(c => (tipo === 'clienteconhistorial' && c.tipo === '1') || (tipo === 'cliente' && c.tipo === '0'));
                mostrarClientesFiltrados(filtrados);
            }
        }
        function mostrarClientesFiltrados(filtrados) {
            const tbody = document.getElementById('tablaClientes');
            if (filtrados.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted">No se encontraron clientes</td></tr>';
            } else {
                tbody.innerHTML = filtrados.map((c, i) => `<tr>
                    <td>${i + 1}</td>
                    <td>${c.nombre}</td>
                    <td>${c.tipoId} ${c.numeroId}</td>
                    <td>${c.telefono}</td>
                    <td>${c.email || '-'}</td>
                    <td>${c.localidad}</td>
                    <td><span class="badge ${c.tipo === '1' ? 'badge-clienteconhistorial' : 'badge-cliente'}">${c.tipo === '1' ? 'Cliente con Historial' : 'Cliente'}</span></td>
                    <td>${c.descuento}%</td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="editarCliente('${c.id}')"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-success" onclick="verDetalles('${c.id}')"><i class="fas fa-eye"></i></button>
                        <button class="btn btn-sm btn-danger" onclick="eliminarCliente('${c.id}')"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>`).join('');
            }
            document.getElementById('contadorClientes').textContent = `Mostrando ${filtrados.length} clientes`;
        }
        function exportarClientes() {
            alert('Función de exportación en desarrollo. Se exportarán ' + clientes.length + ' clientes a Excel.');
        }
        actualizarEstadisticas();
    </script>
</body>
</html>
