
<?php
// La sesión ya está iniciada en index.php
// Verificar si el usuario está autenticado y es administrador
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Location: /Sistema-de-ventas-AppLink-main/public/');
    exit;
}
if (!$_SESSION['is_admin']) {
    header('Location: /Sistema-de-ventas-AppLink-main/public/dashboard');
    exit;
}

// Incluir dependencias
require_once __DIR__ . '/../Utils/Database.php';
require_once __DIR__ . '/../Utils/CSRFToken.php';

// Obtener conexión a la base de datos
$config = require __DIR__ . '/../../config/app.php';
try {
    $pdo = new PDO(
        "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset={$config['db']['charset']}",
        $config['db']['user'],
        $config['db']['pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    // Obtener usuarios de la base de datos
    $stmt = $pdo->query("SELECT * FROM fs_usuarios ORDER BY nombre");
    $usuarios = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $usuarios = [];
    $error_db = "Error de conexión: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Usuarios - Sistema de Ventas AppLink</title>
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
        .main-content { padding: 20px; }
        .card-stat { border-left: 4px solid #FF1493; }
        .btn-pink { 
            background-color: #FF1493; 
            border-color: #FF1493; 
            color: white;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .btn-pink:hover { background-color: #DC143C; border-color: #DC143C; color: white; }
        .text-pink { color: #FF1493; }
        
        /* Estilos adicionales para consistencia visual */
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-radius: 0.5rem;
        }
        
        .table th {
            border-top: none;
            font-weight: 600;
            color: #495057;
            background-color: #f8f9fa;
            font-size: 0.75rem;
        }
        
        .table td {
            font-size: 0.8rem;
        }
        
        .nav-tabs .nav-link {
            border: none;
            color: #6c757d;
            font-weight: 500;
            font-size: 0.85rem;
        }
        
        .nav-tabs .nav-link.active {
            background-color: #FF1493;
            color: white;
            border-radius: 0.375rem 0.375rem 0 0;
        }
        
        .nav-tabs .nav-link:hover {
            border-color: transparent;
            color: #FF1493;
        }
        
        h1 {
            font-size: 1.75rem;
            font-weight: 600;
        }
        
        .card-body h6 {
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .fs-2 {
            font-size: 1.5rem !important;
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
            
            .fs-2 {
                font-size: 1.25rem !important;
            }
            
            .d-flex.justify-content-between {
                flex-direction: column;
                gap: 15px;
            }
            
            .btn-pink {
                width: 100%;
            }
            
            .table-responsive {
                font-size: 0.75rem;
            }
            
            .nav-tabs .nav-link {
                font-size: 0.8rem;
                padding: 8px 12px;
            }
            
            .row .col-md-3, .row .col-md-6 {
                margin-bottom: 15px;
            }
        }
        
        @media (max-width: 576px) {
            h1 {
                font-size: 1.25rem;
            }
            
            .main-content {
                padding: 10px;
            }
            
            .card {
                margin-bottom: 10px;
            }
            
            .table {
                font-size: 0.7rem;
            }
            
            .btn {
                padding: 4px 8px;
                font-size: 0.7rem;
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
                $activePage = 'usuarios';
                include __DIR__ . '/partials/sidebar.php';
            ?>
            
            <!-- Contenido principal -->
            <main class="col-md-10 px-4 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="fw-bold"><i class="fas fa-user-cog"></i> Gestión de Usuarios</h1>
                    <button class="btn btn-pink" id="btnNuevoUsuario" onclick="var tab = new bootstrap.Tab(document.getElementById('nuevo-tab')); tab.show();"><i class="fas fa-plus"></i> Nuevo Usuario</button>
                </div>
                    <!-- Cards de métricas -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <h6 class="mb-1">Total Usuarios</h6>
                                    <span class="fs-2 fw-bold text-pink"><?php echo count($usuarios); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <h6 class="mb-1">Administradores</h6>
                                    <span class="fs-2 fw-bold text-pink"><?php echo count(array_filter($usuarios, function($u) { return $u['is_admin']; })); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <h6 class="mb-1">Usuarios Regulares</h6>
                                    <span class="fs-2 fw-bold text-pink"><?php echo count(array_filter($usuarios, function($u) { return !$u['is_admin']; })); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <h6 class="mb-1">Activos</h6>
                                    <span class="fs-2 fw-bold text-pink"><?php echo count($usuarios); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Navegación de pestañas -->
                    <ul class="nav nav-tabs mb-3" id="usuariosTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="lista-tab" data-bs-toggle="tab" data-bs-target="#listaUsuarios" type="button" role="tab" aria-controls="listaUsuarios" aria-selected="true">Lista de Usuarios</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="nuevo-tab" data-bs-toggle="tab" data-bs-target="#nuevoUsuario" type="button" role="tab" aria-controls="nuevoUsuario" aria-selected="false">Nuevo Usuario</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="usuariosTabsContent">
                        <div class="tab-pane fade show active" id="listaUsuarios" role="tabpanel" aria-labelledby="lista-tab">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <input type="text" class="form-control" id="buscarUsuario" placeholder="Buscar por nombre, usuario o email..." onkeyup="buscarUsuario()">
                                        </div>
                                        <div class="col-md-3">
                                            <select class="form-select" id="filtroRol" onchange="filtrarPorRol()">
                                                <option value="">Todos los roles</option>
                                                <option value="admin">Administrador</option>
                                                <option value="user">Usuario</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 text-end">
                                            <button class="btn btn-success"><i class="fas fa-file-excel"></i> Exportar</button>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Nombre</th>
                                                    <th>Apellido</th>
                                                    <th>Usuario</th>
                                                    <th>Email</th>
                                                    <th>Rol</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tablaUsuarios">
                                                <?php if (!empty($usuarios)): ?>
                                                    <?php foreach ($usuarios as $usuario): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($usuario['id_usuario']); ?></td>
                                                        <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                                                        <td><?php echo htmlspecialchars($usuario['apellido']); ?></td>
                                                        <td><?php echo htmlspecialchars($usuario['nick']); ?></td>
                                                        <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                                        <td>
                                                            <?php if ($usuario['is_admin']): ?>
                                                                <span class="badge bg-primary">Administrador</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-secondary">Usuario</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <button class="btn btn-sm btn-outline-primary" title="Editar" onclick="editarUsuario(<?php echo $usuario['id_usuario']; ?>)">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="eliminarUsuario(<?php echo $usuario['id_usuario']; ?>)">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="7" class="text-center text-muted">
                                                            <?php echo isset($error_db) ? $error_db : 'No hay usuarios registrados'; ?>
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="nuevoUsuario" role="tabpanel" aria-labelledby="nuevo-tab">
                            <div class="card">
                                <div class="card-body">
                                    <form id="formUsuario">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="nombreUsuario" class="form-label">Nombre</label>
                                                <input type="text" class="form-control" id="nombreUsuario" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="apellidoUsuario" class="form-label">Apellido</label>
                                                <input type="text" class="form-control" id="apellidoUsuario" required>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="usuarioUsuario" class="form-label">Usuario</label>
                                                <input type="text" class="form-control" id="usuarioUsuario" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="emailUsuario" class="form-label">Email</label>
                                                <input type="email" class="form-control" id="emailUsuario" required>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="rolUsuario" class="form-label">Rol</label>
                                                <select class="form-select" id="rolUsuario" required>
                                                    <option value="">Selecciona un rol</option>
                                                    <option value="admin">Administrador</option>
                                                    <option value="user">Usuario</option>
                                                </select>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-pink">Guardar Usuario</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>
                <!-- Toasts y tooltips -->
    <!-- Toasts y tooltips -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div id="usuariosToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ¡Acción realizada con éxito!
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/Sistema-de-ventas-AppLink-main/public/js/theme-system.js"></script>
    <script>
        // Variables globales
        let usuarios = <?php echo json_encode($usuarios); ?>;
        let usuarioEditando = null;
        
        // URL base para API
        const API_URL = '/Sistema-de-ventas-AppLink-main/src/Controllers/UsuarioController.php?api=1';
        
        // Event listener para el formulario
        document.getElementById('formUsuario').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const nombre = document.getElementById('nombreUsuario').value.trim();
            const apellido = document.getElementById('apellidoUsuario').value.trim();
            const nick = document.getElementById('usuarioUsuario').value.trim();
            const email = document.getElementById('emailUsuario').value.trim();
            const rol = document.getElementById('rolUsuario').value;
            
            if (!nombre || !apellido || !nick || !email || !rol) {
                mostrarToast('Completa todos los campos', 'danger');
                return;
            }
            
            if (!validarEmail(email)) {
                mostrarToast('Email no válido', 'danger');
                return;
            }
            
            const datos = { nombre, apellido, nick, email, rol };
            
            if (usuarioEditando) {
                datos.id = usuarioEditando.id_usuario;
                actualizarUsuario(datos);
            } else {
                crearUsuario(datos);
            }
        });
        
        // Función para crear usuario
        async function crearUsuario(datos) {
            try {
                const response = await fetch(API_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(datos)
                });
                
                const resultado = await response.json();
                
                if (resultado.success) {
                    mostrarToast(resultado.message, 'success');
                    limpiarFormulario();
                    await cargarUsuarios();
                    // Cambiar a la pestaña de lista
                    var tab = new bootstrap.Tab(document.getElementById('lista-tab'));
                    tab.show();
                } else {
                    mostrarToast(resultado.error || 'Error al crear usuario', 'danger');
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarToast('Error de conexión', 'danger');
            }
        }
        
        // Función para actualizar usuario
        async function actualizarUsuario(datos) {
            try {
                const response = await fetch(API_URL, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(datos)
                });
                
                const resultado = await response.json();
                
                if (resultado.success) {
                    mostrarToast(resultado.message, 'success');
                    limpiarFormulario();
                    await cargarUsuarios();
                    // Cambiar a la pestaña de lista
                    var tab = new bootstrap.Tab(document.getElementById('lista-tab'));
                    tab.show();
                } else {
                    mostrarToast(resultado.error || 'Error al actualizar usuario', 'danger');
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarToast('Error de conexión', 'danger');
            }
        }
        
        // Función para eliminar usuario
        async function eliminarUsuario(id) {
            if (!confirm('¿Seguro que deseas eliminar este usuario?')) {
                return;
            }
            
            try {
                const response = await fetch(API_URL, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: id })
                });
                
                const resultado = await response.json();
                
                if (resultado.success) {
                    mostrarToast(resultado.message, 'success');
                    await cargarUsuarios();
                } else {
                    mostrarToast(resultado.error || 'Error al eliminar usuario', 'danger');
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarToast('Error de conexión', 'danger');
            }
        }
        
        // Función para cargar usuarios desde la API
        async function cargarUsuarios() {
            try {
                const response = await fetch(API_URL);
                const resultado = await response.json();
                
                if (resultado.success) {
                    usuarios = resultado.usuarios;
                    actualizarTablaUsuarios();
                    actualizarMetricas();
                } else {
                    mostrarToast('Error al cargar usuarios', 'danger');
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarToast('Error de conexión', 'danger');
            }
        }
        
        // Función para actualizar la tabla de usuarios
        function actualizarTablaUsuarios() {
            const tbody = document.getElementById('tablaUsuarios');
            
            if (usuarios.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No hay usuarios registrados</td></tr>';
            } else {
                tbody.innerHTML = usuarios.map(u => `
                    <tr>
                        <td>${u.id_usuario}</td>
                        <td>${u.nombre}</td>
                        <td>${u.apellido}</td>
                        <td>${u.nick}</td>
                        <td>${u.email}</td>
                        <td>
                            ${u.is_admin ? '<span class="badge bg-primary">Administrador</span>' : '<span class="badge bg-secondary">Usuario</span>'}
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" title="Editar" onclick="editarUsuario(${u.id_usuario})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="eliminarUsuario(${u.id_usuario})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `).join('');
            }
        }
        
        // Función para editar usuario
        function editarUsuario(id) {
            const usuario = usuarios.find(u => u.id_usuario === id);
            if (usuario) {
                usuarioEditando = usuario;
                document.getElementById('nombreUsuario').value = usuario.nombre;
                document.getElementById('apellidoUsuario').value = usuario.apellido;
                document.getElementById('usuarioUsuario').value = usuario.nick;
                document.getElementById('emailUsuario').value = usuario.email;
                document.getElementById('rolUsuario').value = usuario.is_admin ? 'admin' : 'user';
                
                mostrarToast('Editando usuario', 'info');
                // Cambiar a la pestaña de edición
                var tab = new bootstrap.Tab(document.getElementById('nuevo-tab'));
                tab.show();
            }
        }
        
        // Función para limpiar formulario
        function limpiarFormulario() {
            document.getElementById('formUsuario').reset();
            usuarioEditando = null;
        }
        
        // Función para actualizar métricas
        function actualizarMetricas() {
            const totalUsuarios = usuarios.length;
            const administradores = usuarios.filter(u => u.is_admin).length;
            const usuariosRegulares = usuarios.filter(u => !u.is_admin).length;
            
            // Actualizar las métricas en las cards
            const metricas = document.querySelectorAll('.fs-2.fw-bold.text-pink');
            if (metricas.length >= 4) {
                metricas[0].textContent = totalUsuarios;
                metricas[1].textContent = administradores;
                metricas[2].textContent = usuariosRegulares;
                metricas[3].textContent = totalUsuarios; // Activos
            }
        }
        
        // Función para buscar usuario
        function buscarUsuario() {
            const termino = document.getElementById('buscarUsuario').value.toLowerCase();
            const filtrados = usuarios.filter(u =>
                u.nombre.toLowerCase().includes(termino) ||
                u.apellido.toLowerCase().includes(termino) ||
                u.nick.toLowerCase().includes(termino) ||
                u.email.toLowerCase().includes(termino)
            );
            mostrarUsuariosFiltrados(filtrados);
        }
        
        // Función para mostrar usuarios filtrados
        function mostrarUsuariosFiltrados(filtrados) {
            const tbody = document.getElementById('tablaUsuarios');
            
            if (filtrados.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No se encontraron usuarios</td></tr>';
            } else {
                tbody.innerHTML = filtrados.map(u => `
                    <tr>
                        <td>${u.id_usuario}</td>
                        <td>${u.nombre}</td>
                        <td>${u.apellido}</td>
                        <td>${u.nick}</td>
                        <td>${u.email}</td>
                        <td>
                            ${u.is_admin ? '<span class="badge bg-primary">Administrador</span>' : '<span class="badge bg-secondary">Usuario</span>'}
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" title="Editar" onclick="editarUsuario(${u.id_usuario})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="eliminarUsuario(${u.id_usuario})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `).join('');
            }
        }
        
        // Función para filtrar por rol
        function filtrarPorRol() {
            const rol = document.getElementById('filtroRol').value;
            let filtrados = usuarios;
            
            if (rol === 'admin') {
                filtrados = usuarios.filter(u => u.is_admin);
            } else if (rol === 'user') {
                filtrados = usuarios.filter(u => !u.is_admin);
            }
            
            mostrarUsuariosFiltrados(filtrados);
        }
        
        // Función para validar email
        function validarEmail(email) {
            return /^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email);
        }
        
        // Función para mostrar toast
        function mostrarToast(mensaje, tipo) {
            var toastEl = document.getElementById('usuariosToast');
            if (toastEl) {
                toastEl.className = 'toast align-items-center text-bg-' + (tipo || 'success') + ' border-0';
                toastEl.querySelector('.toast-body').textContent = mensaje;
                var toast = new bootstrap.Toast(toastEl);
                toast.show();
            }
        }
        
        // Inicialización
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
            
            // Cargar datos iniciales
            actualizarTablaUsuarios();
            actualizarMetricas();
        });
    </script>
</body>
</html>

