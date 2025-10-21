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
        .badge-cliente { background-color: #6c757d; }
        .badge-clienteconhistorial { background-color: #FF1493; }
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

                <!-- Pestañas -->
                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#lista" type="button">
                            <i class="fas fa-list"></i> Lista de Clientes
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#nuevo" type="button">
                            <i class="fas fa-plus"></i> Nuevo Cliente
                        </button>
                    </li>
                </ul>

                <!-- Contenido de las pestañas -->
                <div class="tab-content p-3">
                    <!-- Lista de Clientes -->
                    <div class="tab-pane fade show active" id="lista">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Lista de Clientes</h5>
                                <div class="d-flex gap-2">
                                    <div class="input-group" style="width: 300px;">
                                        <input type="text" class="form-control" id="buscarCliente" placeholder="Buscar por nombre, teléfono o identificación...">
                                        <button class="btn btn-outline-secondary" type="button" onclick="buscarCliente()">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                    <select class="form-select" style="width: 150px;">
                                        <option>Todos</option>
                                        <option>Cliente</option>
                                        <option>Revendedora</option>
                                    </select>
                                    <button class="btn btn-success" onclick="exportarClientes()">
                                        <i class="fas fa-file-excel"></i> Exportar
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
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
                                            <tr><td colspan="9" class="text-center text-muted">Cargando clientes...</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <span id="contadorClientes">Mostrando 0 clientes</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Nuevo Cliente -->
                    <div class="tab-pane fade" id="nuevo">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Nuevo Cliente</h5>
                            </div>
                            <div class="card-body">
                                <form id="formCliente">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label"><i class="fas fa-user"></i> Nombre Completo *</label>
                                                <input type="text" class="form-control" id="nombreCompleto" required>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label"><i class="fas fa-id-card"></i> Tipo ID</label>
                                                <select class="form-select" id="tipoId">
                                                    <option value="CC">Cédula</option>
                                                    <option value="CE">Cédula Extranjería</option>
                                                    <option value="PA">Pasaporte</option>
                                                    <option value="TI">Tarjeta Identidad</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label"><i class="fas fa-hashtag"></i> Número C.C *</label>
                                                <input type="text" class="form-control" id="numeroId" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label"><i class="fas fa-phone"></i> Teléfono *</label>
                                                <input type="tel" class="form-control" id="telefono" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label"><i class="fas fa-envelope"></i> Email</label>
                                                <input type="email" class="form-control" id="email">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label"><i class="fas fa-map-marker-alt"></i> Dirección</label>
                                                <input type="text" class="form-control" id="direccion">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label"><i class="fas fa-city"></i> Ciudad</label>
                                                <input type="text" class="form-control" id="ciudad" value="Bogotá" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label"><i class="fas fa-map-pin"></i> Localidad</label>
                                                <select class="form-select" id="localidad">
                                                    <option value="">Seleccione...</option>
                                                    <option value="Usaquén">Usaquén</option>
                                                    <option value="Chapinero">Chapinero</option>
                                                    <option value="Santa Fe">Santa Fe</option>
                                                    <option value="San Cristóbal">San Cristóbal</option>
                                                    <option value="Usme">Usme</option>
                                                    <option value="Tunjuelito">Tunjuelito</option>
                                                    <option value="Bosa">Bosa</option>
                                                    <option value="Kennedy">Kennedy</option>
                                                    <option value="Fontibón">Fontibón</option>
                                                    <option value="Engativá">Engativá</option>
                                                    <option value="Suba">Suba</option>
                                                    <option value="Barrios Unidos">Barrios Unidos</option>
                                                    <option value="Teusaquillo">Teusaquillo</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label"><i class="fas fa-mail-bulk"></i> Código Postal</label>
                                                <input type="text" class="form-control" id="codigoPostal">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label"><i class="fas fa-tags"></i> Tipo de Cliente</label>
                                                <div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="tipoCliente" id="esCliente" value="0" checked>
                                                        <label class="form-check-label" for="esCliente">Cliente</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="tipoCliente" id="esRevendedora" value="1">
                                                        <label class="form-check-label" for="esRevendedora">Cliente con Historial</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6" id="campoDescuento" style="display: none;">
                                            <div class="mb-3">
                                                <label class="form-label"><i class="fas fa-percent"></i> Descuento (%)</label>
                                                <input type="number" class="form-control" id="descuento" min="0" max="100" value="0">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-pink">
                                            <i class="fas fa-save"></i> Guardar Cliente
                                        </button>
                                        <button type="button" class="btn btn-secondary" onclick="limpiarFormulario()">
                                            <i class="fas fa-broom"></i> Limpiar
                                        </button>
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
        console.log('🚀 Sistema de Clientes iniciado');
        
        const API_URL = '/Sistema-de-ventas-AppLink-main/api/clientes.php';
        let clientes = [];
        let clienteEditando = null;

        async function cargarClientes() {
            try {
                console.log('🔍 Cargando clientes desde API...');
                const response = await fetch(API_URL + '?action=listar');
                console.log('📡 Respuesta recibida:', response.status);
                
                const data = await response.json();
                console.log('📊 Datos recibidos:', data);
                
                if (data.success) {
                    console.log('✅ Procesando', data.data.length, 'clientes');
                    clientes = data.data.map(c => ({
                        id: c.id,
                        nombre: c.nombre_completo,
                        tipoId: c.CC ? c.CC.split(' ')[0] : 'CC',
                        numeroId: c.CC ? c.CC.split(' ').slice(1).join(' ') : '',
                        telefono: c.telefono,
                        email: c.email || '',
                        direccion: c.direccion || '',
                        ciudad: c.ciudad || '',
                        localidad: c.localidad || '',
                        codigoPostal: c.codigo_postal || '',
                        tipo: c.revendedora ? '1' : '0',
                        descuento: parseInt(c.descuento) || 0,
                        fechaRegistro: new Date(c.fecha_registro).toLocaleDateString('es-ES')
                    }));
                    actualizarTablaClientes();
                    await actualizarEstadisticas();
                } else {
                    console.error('❌ Error cargando clientes:', data.error);
                }
            } catch (error) {
                console.error('🚨 Error de conexión:', error);
            }
        }

        async function actualizarEstadisticas() {
            try {
                const response = await fetch(API_URL + '?action=estadisticas');
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('totalClientes').textContent = data.data.total || 0;
                    document.getElementById('totalRevendedoras').textContent = data.data.con_historial || 0;
                    document.getElementById('nuevosMes').textContent = data.data.nuevos_mes || 0;
                    document.getElementById('clientesActivos').textContent = data.data.activos || 0;
                } else {
                    document.getElementById('totalClientes').textContent = clientes.length;
                    document.getElementById('totalRevendedoras').textContent = clientes.filter(c => c.tipo === '1').length;
                    document.getElementById('nuevosMes').textContent = clientes.length;
                    document.getElementById('clientesActivos').textContent = clientes.length;
                }
            } catch (error) {
                console.error('Error cargando estadísticas:', error);
                document.getElementById('totalClientes').textContent = clientes.length;
                document.getElementById('totalRevendedoras').textContent = clientes.filter(c => c.tipo === '1').length;
                document.getElementById('nuevosMes').textContent = clientes.length;
                document.getElementById('clientesActivos').textContent = clientes.length;
            }
        }

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
                    <td><span class="badge ${c.tipo === '1' ? 'badge-clienteconhistorial' : 'badge-cliente'}">${c.tipo === '1' ? 'Cliente con Historial' : 'Cliente'}</span></td>
                    <td>${c.descuento}%</td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="editarCliente('${c.id}')"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-success" onclick="verDetalles('${c.id}')"><i class="fas fa-eye"></i></button>
                        <button class="btn btn-sm btn-danger" onclick="eliminarCliente('${c.id}')"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>`).join('');
            }
            document.getElementById('contadorClientes').textContent = `Mostrando ${clientes.length} clientes`;
        }

        document.querySelectorAll('input[name="tipoCliente"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.getElementById('campoDescuento').style.display = this.value === '1' ? 'block' : 'none';
                if (this.value === '0') document.getElementById('descuento').value = '0';
            });
        });

        document.getElementById('formCliente').addEventListener('submit', async function(e) {
            e.preventDefault();
            console.log('📝 Enviando datos de cliente...');
            
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
            
            console.log('📋 Datos a enviar:', clienteData);

            try {
                let response;
                if (clienteEditando) {
                    response = await fetch(API_URL + '?id=' + clienteEditando.id, {
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
                console.log('💾 Respuesta del servidor:', data);
                
                if (data.success) {
                    alert(data.message);
                    console.log('✅ Cliente guardado exitosamente');
                    await cargarClientes();
                    limpiarFormulario();
                    document.querySelector('[data-bs-target="#lista"]').click();
                } else {
                    console.error('❌ Error del servidor:', data.error);
                    alert('Error: ' + data.error);
                }
            } catch (error) {
                console.error('🚨 Error de conexión al guardar:', error);
                alert('Error de conexión al guardar cliente');
            }
        });

        function limpiarFormulario() {
            document.getElementById('formCliente').reset();
            document.getElementById('campoDescuento').style.display = 'none';
            clienteEditando = null;
        }

        function exportarClientes() {
            alert('Función de exportación en desarrollo. Se exportarán ' + clientes.length + ' clientes a Excel.');
        }

        async function eliminarCliente(id) {
            if (confirm('¿Está seguro de eliminar este cliente?')) {
                try {
                    const response = await fetch(API_URL + '?id=' + id, {
                        method: 'DELETE'
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        alert(data.message);
                        await cargarClientes();
                    } else {
                        alert('Error: ' + data.error);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error de conexión al eliminar cliente');
                }
            }
        }

        function editarCliente(id) {
            const cliente = clientes.find(c => c.id == id);
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
                    document.getElementById('esRevendedora').checked = true;
                    document.getElementById('descuento').value = cliente.descuento;
                    document.getElementById('campoDescuento').style.display = 'block';
                } else {
                    document.getElementById('esCliente').checked = true;
                    document.getElementById('campoDescuento').style.display = 'none';
                }
                
                document.querySelector('[data-bs-target="#nuevo"]').click();
            }
        }

        function verDetalles(id) {
            const cliente = clientes.find(c => c.id == id);
            if (cliente) {
                alert('DETALLES DEL CLIENTE\n\nID: ' + cliente.id + '\nNombre: ' + cliente.nombre + '\nIdentificación: ' + cliente.tipoId + ' ' + cliente.numeroId + '\nTeléfono: ' + cliente.telefono + '\nEmail: ' + (cliente.email || 'No registrado') + '\nDirección: ' + (cliente.direccion || 'No registrada') + '\nLocalidad: ' + cliente.localidad + ', ' + cliente.ciudad + '\nTipo: ' + (cliente.tipo === '1' ? 'Revendedora' : 'Cliente') + '\nDescuento: ' + cliente.descuento + '%\nFecha Registro: ' + cliente.fechaRegistro);
            }
        }

        async function buscarCliente() {
            const termino = document.getElementById('buscarCliente').value.trim();
            
            if (termino === '') {
                await cargarClientes();
                return;
            }

            try {
                const response = await fetch(API_URL + '?action=buscar&termino=' + encodeURIComponent(termino));
                const data = await response.json();
                
                if (data.success) {
                    clientes = data.data.map(c => ({
                        id: c.id,
                        nombre: c.nombre_completo,
                        tipoId: c.CC ? c.CC.split(' ')[0] : 'CC',
                        numeroId: c.CC ? c.CC.split(' ').slice(1).join(' ') : '',
                        telefono: c.telefono,
                        email: c.email || '',
                        direccion: c.direccion || '',
                        ciudad: c.ciudad || '',
                        localidad: c.localidad || '',
                        codigoPostal: c.codigo_postal || '',
                        tipo: c.revendedora ? '1' : '0',
                        descuento: parseInt(c.descuento) || 0,
                        fechaRegistro: new Date(c.fecha_registro).toLocaleDateString('es-ES')
                    }));
                    actualizarTablaClientes();
                    await actualizarEstadisticas();
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 Página cargada, iniciando carga de datos...');
            cargarClientes();
            actualizarEstadisticas();
        });
    </script>
</body>
</html>