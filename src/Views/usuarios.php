
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
$config = require __DIR__ . '/../../config/app_postgresql.php';
try {
    $pdo = new PDO(
        "pgsql:host={$config['db']['host']};port={$config['db']['port']};dbname={$config['db']['name']}",
        $config['db']['user'],
        $config['db']['pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    // Obtener usuarios de la base de datos (actualizado para PostgreSQL)
    $stmt = $pdo->query("SELECT * FROM usuarios ORDER BY nombre, apellido");
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Usuarios - Lilipink</title>
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
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar centralizado -->
            <?php 
                $activePage = 'usuarios';
                include __DIR__ . '/partials/sidebar.php';
            ?>
            <main class="col-md-10 px-4">
                <h1 class="mt-3"><i class="fas fa-users-cog"></i> Gestión de Usuarios</h1>
                
                <!-- Estadísticas -->
                <div class="row my-4">
                    <div class="col-md-3">
                        <div class="card card-stat">
                            <div class="card-body">
                                <h6 class="text-muted">Total Usuarios</h6>
                                <h3><?php echo count($usuarios); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-stat">
                            <div class="card-body">
                                <h6 class="text-muted">Administradores</h6>
                                <h3><?php echo count(array_filter($usuarios, function($u) { return $u['is_admin']; })); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-stat">
                            <div class="card-body">
                                <h6 class="text-muted">Usuarios Regulares</h6>
                                <h3><?php echo count(array_filter($usuarios, function($u) { return !$u['is_admin']; })); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-stat">
                            <div class="card-body">
                                <h6 class="text-muted">Activos</h6>
                                <h3><?php echo count($usuarios); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pestañas -->
                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#listaUsuarios" type="button">
                            <i class="fas fa-list"></i> Lista de Usuarios
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#nuevoUsuario" type="button">
                            <i class="fas fa-plus"></i> Nuevo Usuario
                        </button>
                    </li>
                </ul>
                <!-- Contenido de las pestañas -->
                <div class="tab-content p-3">
                    <!-- Lista de Usuarios -->
                    <div class="tab-pane fade show active" id="listaUsuarios">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Lista de Usuarios</h5>
                                <div class="d-flex gap-2">
                                    <div class="input-group" style="width: 300px;">
                                        <input type="text" class="form-control" id="buscarUsuario" placeholder="Buscar por nombre, usuario o email..." onkeyup="buscarUsuario()">
                                        <button class="btn btn-outline-secondary" type="button">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                    <select class="form-select" style="width: 150px;" id="filtroRol" onchange="filtrarPorRol()">
                                        <option value="">Todos los roles</option>
                                        <option value="admin">Administrador</option>
                                        <option value="user">Usuario</option>
                                    </select>
                                    <button class="btn btn-success">
                                        <i class="fas fa-file-excel"></i> Exportar
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
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
                                                    <td><?php echo htmlspecialchars($usuario['id']); ?></td>
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
                                                        <button class="btn btn-sm btn-info" title="Editar" onclick="editarUsuario(<?php echo $usuario['id']; ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-danger" title="Eliminar" onclick="eliminarUsuario(<?php echo $usuario['id']; ?>)">
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
                    <!-- Nuevo Usuario -->
                    <div class="tab-pane fade" id="nuevoUsuario">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Nuevo Usuario</h5>
                            </div>
                            <div class="card-body">
                                <form id="formUsuario">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="nombreUsuario" class="form-label"><i class="fas fa-user"></i> Nombre *</label>
                                                <input type="text" class="form-control" id="nombreUsuario" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="apellidoUsuario" class="form-label"><i class="fas fa-user"></i> Apellido *</label>
                                                <input type="text" class="form-control" id="apellidoUsuario" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="usuarioUsuario" class="form-label"><i class="fas fa-at"></i> Usuario *</label>
                                                <input type="text" class="form-control" id="usuarioUsuario" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="emailUsuario" class="form-label"><i class="fas fa-envelope"></i> Email *</label>
                                                <input type="email" class="form-control" id="emailUsuario" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="rolUsuario" class="form-label"><i class="fas fa-user-tag"></i> Rol *</label>
                                                <select class="form-select" id="rolUsuario" required>
                                                    <option value="">Selecciona un rol</option>
                                                    <option value="admin">Administrador</option>
                                                    <option value="user">Usuario</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="passwordUsuario" class="form-label"><i class="fas fa-lock"></i> Contraseña *</label>
                                                <input type="password" class="form-control" id="passwordUsuario" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-pink">
                                            <i class="fas fa-save"></i> Guardar Usuario
                                        </button>
                                        <button type="button" class="btn btn-secondary" onclick="limpiarFormularioUsuario()">
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
    <!-- Script actualizado <?php echo time(); ?> -->
    <script>
        // Funciones para manejo de usuarios
        document.addEventListener('DOMContentLoaded', function() {
            // Toast para mensajes
            const toastElement = document.getElementById('usuariosToast');
            const toast = new bootstrap.Toast(toastElement);
            
            // Función para mostrar toast
            function showToast(message, type = 'success') {
                const toastBody = toastElement.querySelector('.toast-body');
                toastBody.textContent = message;
                
                // Cambiar color según tipo
                toastElement.className = `toast align-items-center text-bg-${type} border-0`;
                toast.show();
            }
            
            // Manejar formulario de nuevo usuario
            const userForm = document.getElementById('userForm');
            if (userForm) {
                userForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    
                    fetch('../../api/usuarios.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast('Usuario creado exitosamente');
                            userForm.reset();
                            // Recargar la lista de usuarios
                            loadUsers();
                        } else {
                            showToast(data.message || 'Error al crear usuario', 'danger');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('Error de conexión', 'danger');
                    });
                });
            }
            
            // Función para cargar usuarios
            function loadUsers() {
                fetch('../../api/usuarios.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            updateUsersTable(data.data);
                            updateStats(data.stats);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('Error al cargar usuarios', 'danger');
                    });
            }
            
            // Función para actualizar tabla de usuarios
            function updateUsersTable(users) {
                const tbody = document.querySelector('#usersTable tbody');
                if (!tbody) return;
                
                tbody.innerHTML = '';
                
                users.forEach(user => {
                    const row = tbody.insertRow();
                    row.innerHTML = `
                        <td>${user.id}</td>
                        <td>${user.nombre}</td>
                        <td>${user.email}</td>
                        <td><span class="badge bg-${user.rol === 'admin' ? 'primary' : 'secondary'}">${user.rol}</span></td>
                        <td>${new Date(user.fecha_creacion).toLocaleDateString()}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editUser(${user.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteUser(${user.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    `;
                });
            }
            
            // Función para actualizar estadísticas
            function updateStats(stats) {
                document.getElementById('totalUsers').textContent = stats.total || 0;
                document.getElementById('activeUsers').textContent = stats.activos || 0;
                document.getElementById('adminUsers').textContent = stats.administradores || 0;
            }
            
            // Cargar usuarios al iniciar
            loadUsers();
        });
        
        // Función global para editar usuario
        function editUser(id) {
            // Implementar edición de usuario
            console.log('Editar usuario:', id);
        }
        
        // Función global para eliminar usuario
        function deleteUser(id) {
            if (confirm('¿Está seguro de que desea eliminar este usuario?')) {
                fetch(`../../api/usuarios.php?id=${id}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Usuario eliminado exitosamente');
                        loadUsers();
                    } else {
                        showToast(data.message || 'Error al eliminar usuario', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error de conexión', 'danger');
                });
            }
        }
        
        // Cargar usuarios desde PHP
        let usuarios = <?php echo json_encode($usuarios); ?>;
        let usuarioEditando = null;
        document.getElementById('formUsuario').addEventListener('submit', function(e) {
            e.preventDefault();
            const nombre = document.getElementById('nombreUsuario').value.trim();
            const apellido = document.getElementById('apellidoUsuario').value.trim();
            const usuario = document.getElementById('usuarioUsuario').value.trim();
            const email = document.getElementById('emailUsuario').value.trim();
            const rol = document.getElementById('rolUsuario').value;
            if (!nombre || !apellido || !usuario || !email || !rol) {
                mostrarToast('Completa todos los campos', 'danger');
                return;
            }
            if (!validarEmail(email)) {
                mostrarToast('Email no válido', 'danger');
                return;
            }
            const nuevoUsuario = {
                id: usuarioEditando ? usuarioEditando.id : usuarios.length + 1,
                nombre,
                apellido,
                usuario,
                email,
                rol
            };
            if (usuarioEditando) {
                const idx = usuarios.findIndex(u => u.id === usuarioEditando.id);
                usuarios[idx] = nuevoUsuario;
                usuarioEditando = null;
                mostrarToast('Usuario actualizado', 'success');
            } else {
                usuarios.push(nuevoUsuario);
                mostrarToast('Usuario registrado', 'success');
            }
            limpiarFormulario();
            actualizarTablaUsuarios();
        });
        function actualizarTablaUsuarios() {
            const tbody = document.getElementById('tablaUsuarios');
            if (usuarios.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No hay usuarios registrados</td></tr>';
            } else {
                tbody.innerHTML = usuarios.map(u => `<tr>
                    <td>${u.id}</td>
                    <td>${u.nombre}</td>
                    <td>${u.apellido}</td>
                    <td>${u.usuario}</td>
                    <td>${u.email}</td>
                    <td><span class="badge bg-${u.rol === 'admin' ? 'primary' : 'secondary'}">${u.rol === 'admin' ? 'Administrador' : 'Usuario'}</span></td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="editarUsuario(${u.id})" data-bs-toggle="tooltip" title="Editar usuario"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-danger" onclick="eliminarUsuario(${u.id})" data-bs-toggle="tooltip" title="Eliminar usuario"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>`).join('');
            }
        }
        function editarUsuario(id) {
            const usuario = usuarios.find(u => u.id === id);
            if (usuario) {
                usuarioEditando = usuario;
                document.getElementById('nombreUsuario').value = usuario.nombre;
                document.getElementById('apellidoUsuario').value = usuario.apellido;
                document.getElementById('usuarioUsuario').value = usuario.usuario;
                document.getElementById('emailUsuario').value = usuario.email;
                document.getElementById('rolUsuario').value = usuario.rol;
                mostrarToast('Editando usuario', 'info');
                // Cambia a la pestaña de edición
                var tab = new bootstrap.Tab(document.getElementById('nuevo-tab'));
                tab.show();
            }
        }
        function eliminarUsuario(id) {
            if (confirm('¿Seguro que deseas eliminar este usuario?')) {
                usuarios = usuarios.filter(u => u.id !== id);
                actualizarTablaUsuarios();
                mostrarToast('Usuario eliminado', 'success');
            }
        }
        function limpiarFormulario() {
            document.getElementById('formUsuario').reset();
            usuarioEditando = null;
        }
        function buscarUsuario() {
            const termino = document.getElementById('buscarUsuario').value.toLowerCase();
            const filtrados = usuarios.filter(u =>
                u.nombre.toLowerCase().includes(termino) ||
                u.apellido.toLowerCase().includes(termino) ||
                u.usuario.toLowerCase().includes(termino) ||
                u.email.toLowerCase().includes(termino)
            );
            mostrarUsuariosFiltrados(filtrados);
        }
        function mostrarUsuariosFiltrados(filtrados) {
            const tbody = document.getElementById('tablaUsuarios');
            if (filtrados.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No se encontraron usuarios</td></tr>';
            } else {
                tbody.innerHTML = filtrados.map(u => `<tr>
                    <td>${u.id}</td>
                    <td>${u.nombre}</td>
                    <td>${u.apellido}</td>
                    <td>${u.usuario}</td>
                    <td>${u.email}</td>
                    <td><span class="badge bg-${u.rol === 'admin' ? 'primary' : 'secondary'}">${u.rol === 'admin' ? 'Administrador' : 'Usuario'}</span></td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="editarUsuario(${u.id})" data-bs-toggle="tooltip" title="Editar usuario"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-danger" onclick="eliminarUsuario(${u.id})" data-bs-toggle="tooltip" title="Eliminar usuario"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>`).join('');
            }
        }
        function filtrarPorRol() {
            const rol = document.getElementById('filtroRol').value;
            let filtrados = usuarios;
            if (rol) {
                filtrados = usuarios.filter(u => u.rol === rol);
            }
            mostrarUsuariosFiltrados(filtrados);
        }
        function validarEmail(email) {
            return /^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email);
        }
        // Toast dinámico
        function mostrarToast(mensaje, tipo) {
            var toastEl = document.getElementById('usuariosToast');
            if (toastEl) {
                toastEl.className = 'toast align-items-center text-bg-' + (tipo || 'success') + ' border-0';
                toastEl.querySelector('.toast-body').textContent = mensaje;
                var toast = new bootstrap.Toast(toastEl);
                toast.show();
            }
        }
        // Inicializar tooltips y confirmación logout
        window.addEventListener('DOMContentLoaded', function() {
            actualizarTablaUsuarios();
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

