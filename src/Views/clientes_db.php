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
    <title>Clientes - Sistema de Ventas AppLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/Sistema-de-ventas-AppLink-main/public/css/base.css" rel="stylesheet">
    <link href="/Sistema-de-ventas-AppLink-main/public/css/clientes.css" rel="stylesheet">
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
        .loading { display: none; }
        .loading.show { display: block; }
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
                
                <!-- Estadísticas -->
                <div class="row my-4">
                    <div class="col-md-3">
                        <div class="card card-stat">
                            <div class="card-body">
                                <h6 class="text-muted">Total Clientes</h6>
                                <h3 id="totalClientes">0</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-stat">
                            <div class="card-body">
                                <h6 class="text-muted">Clientes con Historial</h6>
                                <h3 id="totalRevendedoras">0</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-stat">
                            <div class="card-body">
                                <h6 class="text-muted">Nuevos Este Mes</h6>
                                <h3 id="nuevosMes">0</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-stat">
                            <div class="card-body">
                                <h6 class="text-muted">Activos</h6>
                                <h3 id="clientesActivos">0</h3>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Navegación por pestañas -->
                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#lista">Lista de Clientes</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#nuevo">Nuevo Cliente</button>
                    </li>
                </ul>
                
                <!-- Contenido de las pestañas -->
                <div class="tab-content p-3">
                    <!-- Lista de Clientes -->
                    <div class="tab-pane fade show active" id="lista">
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="buscarCliente" class="form-label">Buscar Cliente</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="buscarCliente" placeholder="Nombre, teléfono, CC...">
                                            <button class="btn btn-outline-secondary" onclick="buscarCliente()">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="filtroTipo" class="form-label">Filtrar por Tipo</label>
                                        <select class="form-select" id="filtroTipo" onchange="filtrarClientes()">
                                            <option value="">Todos los tipos</option>
                                            <option value="revendedor">Cliente con Historial</option>
                                            <option value="cliente">Cliente</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">&nbsp;</label>
                                        <div class="d-grid">
                                            <button class="btn btn-success" onclick="exportarClientes()">
                                                <i class="fas fa-file-excel me-1"></i>Exportar
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">&nbsp;</label>
                                        <div class="d-grid">
                                            <button class="btn btn-info" onclick="cargarClientes()">
                                                <i class="fas fa-sync-alt me-1"></i>Actualizar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tabla de clientes -->
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Lista de Clientes</h5>
                                <span id="contadorClientes" class="badge bg-secondary">Cargando...</span>
                            </div>
                            <div class="card-body">
                                <div class="loading text-center p-4" id="loadingTable">
                                    <div class="spinner-border" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                    <p class="mt-2">Cargando clientes...</p>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>#</th>
                                                <th>Nombre</th>
                                                <th>Identificación</th>
                                                <th>Teléfono</th>
                                                <th>Email</th>
                                                <th>Localidad</th>
                                                <th>Tipo</th>
                                                <th>Descuento</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tablaClientes">
                                            <tr>
                                                <td colspan="9" class="text-center text-muted">Cargando clientes...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Nuevo Cliente -->
                    <div class="tab-pane fade" id="nuevo">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-user-plus me-2"></i>
                                    <span id="modalTitle">Nuevo Cliente</span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <form id="formCliente" novalidate>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="nombreCompleto" class="form-label">Nombre Completo *</label>
                                            <input type="text" class="form-control" id="nombreCompleto" required>
                                            <div class="invalid-feedback">Ingrese el nombre completo</div>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="tipoId" class="form-label">Tipo ID</label>
                                            <select class="form-select" id="tipoId">
                                                <option value="CC">CC</option>
                                                <option value="CE">CE</option>
                                                <option value="TI">TI</option>
                                                <option value="PA">PA</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="numeroId" class="form-label">Número ID</label>
                                            <input type="text" class="form-control" id="numeroId">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="telefono" class="form-label">Teléfono *</label>
                                            <input type="tel" class="form-control" id="telefono" required>
                                            <div class="invalid-feedback">Ingrese el teléfono</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email">
                                        </div>
                                        <div class="col-md-12">
                                            <label for="direccion" class="form-label">Dirección</label>
                                            <input type="text" class="form-control" id="direccion">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="ciudad" class="form-label">Ciudad</label>
                                            <input type="text" class="form-control" id="ciudad">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="localidad" class="form-label">Localidad</label>
                                            <input type="text" class="form-control" id="localidad">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="codigoPostal" class="form-label">Código Postal</label>
                                            <input type="text" class="form-control" id="codigoPostal">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Tipo de Cliente *</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="tipoCliente" id="esCliente" value="0" checked onchange="toggleDescuento()">
                                                <label class="form-check-label" for="esCliente">Cliente</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="tipoCliente" id="esRevendedor" value="1" onchange="toggleDescuento()">
                                                <label class="form-check-label" for="esRevendedor">Cliente con Historial</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6" id="campoDescuento" style="display: none;">
                                            <label for="descuento" class="form-label">Descuento (%)</label>
                                            <input type="number" class="form-control" id="descuento" min="0" max="100" value="0">
                                        </div>
                                    </div>
                                    
                                    <div class="row mt-4">
                                        <div class="col-12">
                                            <button type="button" class="btn btn-secondary me-2" onclick="limpiarFormulario()">
                                                <i class="fas fa-times me-1"></i>Cancelar
                                            </button>
                                            <button type="button" class="btn btn-pink" onclick="agregarCliente()">
                                                <i class="fas fa-save me-1"></i>Guardar Cliente
                                            </button>
                                        </div>
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
        
        // API Base URL
        const API_URL = '/Sistema-de-ventas-AppLink-main/api/clientes.php';
        
        // Cargar datos al iniciar
        document.addEventListener('DOMContentLoaded', function() {
            cargarClientes();
            cargarEstadisticas();
        });
        
        async function cargarClientes() {
            document.getElementById('loadingTable').classList.add('show');
            try {
                const response = await fetch(`${API_URL}?action=listar`);
                const data = await response.json();
                
                if (data.success) {
                    clientes = data.data;
                    actualizarTablaClientes();
                } else {
                    mostrarError('Error al cargar clientes: ' + data.error);
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarError('Error de conexión al cargar clientes');
            } finally {
                document.getElementById('loadingTable').classList.remove('show');
            }
        }
        
        async function cargarEstadisticas() {
            try {
                const response = await fetch(`${API_URL}?action=estadisticas`);
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('totalClientes').textContent = data.data.total;
                    document.getElementById('totalRevendedoras').textContent = data.data.con_historial;
                    document.getElementById('nuevosMes').textContent = data.data.nuevos_mes;
                    document.getElementById('clientesActivos').textContent = data.data.activos;
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        async function agregarCliente() {
            const form = document.getElementById('formCliente');
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }
            
            const clienteData = {
                nombre: document.getElementById('nombreCompleto').value,
                telefono: document.getElementById('telefono').value,
                email: document.getElementById('email').value || null,
                direccion: document.getElementById('direccion').value || null,
                ciudad: document.getElementById('ciudad').value || null,
                localidad: document.getElementById('localidad').value || null,
                codigo_postal: document.getElementById('codigoPostal').value || null,
                cc: (document.getElementById('tipoId').value + ' ' + document.getElementById('numeroId').value).trim() || null,
                tipo: document.querySelector('input[name="tipoCliente"]:checked').value,
                descuento: parseInt(document.getElementById('descuento').value) || 0
            };
            
            try {
                let response;
                if (clienteEditando) {
                    response = await fetch(`${API_URL}?id=${clienteEditando.id}`, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(clienteData)
                    });
                } else {
                    response = await fetch(API_URL, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(clienteData)
                    });
                }
                
                const data = await response.json();
                
                if (data.success) {
                    mostrarExito(data.message);
                    await cargarClientes();
                    await cargarEstadisticas();
                    limpiarFormulario();
                    
                    // Cambiar a la pestaña de lista
                    const tabLista = new bootstrap.Tab(document.querySelector('[data-bs-target="#lista"]'));
                    tabLista.show();
                } else {
                    mostrarError(data.error);
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarError('Error de conexión al guardar cliente');
            }
        }

        function actualizarTablaClientes() {
            const tbody = document.getElementById('tablaClientes');
            
            if (clientes.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted">No hay clientes registrados</td></tr>';
            } else {
                tbody.innerHTML = clientes.map((c, i) => `
                    <tr>
                        <td>${i + 1}</td>
                        <td>${c.nombre_completo}</td>
                        <td>${c.CC || '-'}</td>
                        <td>${c.telefono}</td>
                        <td>${c.email || '-'}</td>
                        <td>${c.localidad || '-'}</td>
                        <td><span class="badge ${c.revendedora == 1 ? 'badge-revendedor' : 'badge-cliente'}">${c.revendedora == 1 ? 'Con Historial' : 'Cliente'}</span></td>
                        <td>${c.descuento}%</td>
                        <td>
                            <button class="btn btn-sm btn-info" onclick="editarCliente(${c.id})" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-success" onclick="verDetalles(${c.id})" title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="eliminarCliente(${c.id})" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `).join('');
            }
            
            document.getElementById('contadorClientes').textContent = `${clientes.length} clientes`;
        }

        function editarCliente(id) {
            const cliente = clientes.find(c => c.id == id);
            if (cliente) {
                clienteEditando = cliente;
                
                document.getElementById('modalTitle').textContent = 'Editar Cliente';
                document.getElementById('nombreCompleto').value = cliente.nombre_completo;
                document.getElementById('telefono').value = cliente.telefono;
                document.getElementById('email').value = cliente.email || '';
                document.getElementById('direccion').value = cliente.direccion || '';
                document.getElementById('ciudad').value = cliente.ciudad || '';
                document.getElementById('localidad').value = cliente.localidad || '';
                document.getElementById('codigoPostal').value = cliente.codigo_postal || '';
                
                // Manejar CC
                if (cliente.CC) {
                    const ccParts = cliente.CC.split(' ');
                    if (ccParts.length >= 2) {
                        document.getElementById('tipoId').value = ccParts[0];
                        document.getElementById('numeroId').value = ccParts.slice(1).join(' ');
                    }
                }
                
                // Manejar tipo
                if (cliente.revendedora == 1) {
                    document.getElementById('esRevendedor').checked = true;
                    document.getElementById('campoDescuento').style.display = 'block';
                    document.getElementById('descuento').value = cliente.descuento;
                } else {
                    document.getElementById('esCliente').checked = true;
                    document.getElementById('campoDescuento').style.display = 'none';
                }
                
                // Cambiar a pestaña nuevo
                const tabNuevo = new bootstrap.Tab(document.querySelector('[data-bs-target="#nuevo"]'));
                tabNuevo.show();
            }
        }
        
        function verDetalles(id) {
            const cliente = clientes.find(c => c.id == id);
            if (cliente) {
                alert(`DETALLES DEL CLIENTE\n\nID: ${cliente.id}\nNombre: ${cliente.nombre_completo}\nIdentificación: ${cliente.CC || 'No registrada'}\nTeléfono: ${cliente.telefono}\nEmail: ${cliente.email || 'No registrado'}\nDirección: ${cliente.direccion || 'No registrada'}\nLocalidad: ${cliente.localidad || ''}, ${cliente.ciudad || ''}\nTipo: ${cliente.revendedora == 1 ? 'Cliente con Historial' : 'Cliente'}\nDescuento: ${cliente.descuento}%\nFecha Registro: ${cliente.fecha_registro}`);
            }
        }
        
        async function eliminarCliente(id) {
            if (confirm('¿Está seguro de eliminar este cliente?')) {
                try {
                    const response = await fetch(`${API_URL}?id=${id}`, {
                        method: 'DELETE'
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        mostrarExito(data.message);
                        await cargarClientes();
                        await cargarEstadisticas();
                    } else {
                        mostrarError(data.error);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    mostrarError('Error de conexión al eliminar cliente');
                }
            }
        }
        
        function limpiarFormulario() {
            document.getElementById('formCliente').reset();
            document.getElementById('campoDescuento').style.display = 'none';
            document.getElementById('modalTitle').textContent = 'Nuevo Cliente';
            clienteEditando = null;
        }
        
        async function buscarCliente() {
            const termino = document.getElementById('buscarCliente').value.trim();
            
            if (termino === '') {
                await cargarClientes();
                return;
            }
            
            try {
                const response = await fetch(`${API_URL}?action=buscar&termino=${encodeURIComponent(termino)}`);
                const data = await response.json();
                
                if (data.success) {
                    clientes = data.data;
                    actualizarTablaClientes();
                } else {
                    mostrarError('Error al buscar clientes: ' + data.error);
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarError('Error de conexión al buscar clientes');
            }
        }
        
        function filtrarClientes() {
            const tipo = document.getElementById('filtroTipo').value;
            if (tipo === '') {
                cargarClientes();
            } else {
                const filtrados = clientes.filter(c => 
                    (tipo === 'revendedor' && c.revendedora == 1) || 
                    (tipo === 'cliente' && c.revendedora == 0)
                );
                mostrarClientesFiltrados(filtrados);
            }
        }
        
        function mostrarClientesFiltrados(filtrados) {
            clientes = filtrados;
            actualizarTablaClientes();
        }
        
        function exportarClientes() {
            alert('Función de exportación en desarrollo. Se exportarán ' + clientes.length + ' clientes a Excel.');
        }
        
        function toggleDescuento() {
            const esRevendedor = document.getElementById('esRevendedor').checked;
            const campoDescuento = document.getElementById('campoDescuento');
            
            if (esRevendedor) {
                campoDescuento.style.display = 'block';
            } else {
                campoDescuento.style.display = 'none';
                document.getElementById('descuento').value = 0;
            }
        }
        
        // Funciones de utilidad
        function mostrarExito(mensaje) {
            const toast = document.createElement('div');
            toast.className = 'toast align-items-center text-white bg-success border-0';
            toast.style.position = 'fixed';
            toast.style.top = '20px';
            toast.style.right = '20px';
            toast.style.zIndex = '9999';
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">${mensaje}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            document.body.appendChild(toast);
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            setTimeout(() => toast.remove(), 5000);
        }
        
        function mostrarError(mensaje) {
            const toast = document.createElement('div');
            toast.className = 'toast align-items-center text-white bg-danger border-0';
            toast.style.position = 'fixed';
            toast.style.top = '20px';
            toast.style.right = '20px';
            toast.style.zIndex = '9999';
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">${mensaje}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            document.body.appendChild(toast);
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            setTimeout(() => toast.remove(), 5000);
        }

        // Búsqueda con Enter
        document.getElementById('buscarCliente').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                buscarCliente();
            }
        });
    </script>
</body>
</html>