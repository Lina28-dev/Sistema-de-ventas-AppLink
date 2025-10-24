
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios - Sistema de Ventas AppLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/Sistema-de-ventas-AppLink-main/public/css/sidebar.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .main-content { padding: 20px; }
        .card-stat { border-left: 4px solid #FF1493; }
        .btn-pink { background-color: #FF1493; border-color: #FF1493; color: white; }
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
        }
        
        .nav-tabs .nav-link {
            border: none;
            color: #6c757d;
            font-weight: 500;
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
    <script>
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

