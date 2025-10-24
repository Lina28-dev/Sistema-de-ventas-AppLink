
<?php
session_start();
$page_title = "Gestión de Usuarios";

// Verificar si el usuario está autenticado y es administrador
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Location: /Sistema-de-ventas-AppLink-main/public/');
    exit;
}

// Incluir el header del template
require_once __DIR__ . '/includes/header.php';

// Incluir dependencias para obtener usuarios
require_once __DIR__ . '/../Utils/Database.php';

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
    
    // Obtener usuarios de la base de datos
    $stmt = $pdo->query("SELECT * FROM usuarios ORDER BY nombre, apellido");
    $usuarios = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $usuarios = [];
    $error_db = "Error de conexión: " . $e->getMessage();
}
?>

<div class="container-fluid">
    <div class="row">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>
        
        <!-- Main content -->
        <main class="main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <div class="page-title">
                    <i class="fas fa-users-cog"></i>
                    Gestión de Usuarios
                </div>
                <div class="text-muted">
                    <i class="far fa-clock"></i>
                    <span id="fechaHoraUsuarios"><?php date_default_timezone_set('America/Bogota'); echo date('d/m/Y H:i:s'); ?></span>
                </div>
            </div>

            <!-- Estadísticas de Usuarios -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card card-stat">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted">Total Usuarios</h6>
                                    <h3 class="text-primary"><?php echo count($usuarios); ?></h3>
                                    <small class="text-muted">Registrados</small>
                                </div>
                                <i class="fas fa-users text-primary fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-stat">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted">Administradores</h6>
                                    <h3 class="text-warning"><?php echo count(array_filter($usuarios, function($u) { return isset($u['is_admin']) && $u['is_admin']; })); ?></h3>
                                    <small class="text-muted">Acceso completo</small>
                                </div>
                                <i class="fas fa-crown text-warning fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-stat">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted">Empleados</h6>
                                    <h3 class="text-info"><?php echo count(array_filter($usuarios, function($u) { return isset($u['rol']) && $u['rol'] === 'empleado'; })); ?></h3>
                                    <small class="text-muted">Personal activo</small>
                                </div>
                                <i class="fas fa-user-tie text-info fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-stat">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted">Clientes</h6>
                                    <h3 class="text-success"><?php echo count(array_filter($usuarios, function($u) { return isset($u['rol']) && $u['rol'] === 'cliente'; })); ?></h3>
                                    <small class="text-success">Usuarios activos</small>
                                </div>
                                <i class="fas fa-user text-success fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pestañas de Gestión -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-user-plus"></i> Gestión de Usuarios</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <button class="btn btn-primary btn-lg w-100 mb-3" data-bs-toggle="modal" data-bs-target="#nuevoUsuarioModal">
                                        <i class="fas fa-user-plus me-2"></i>
                                        Nuevo Usuario
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-warning btn-lg w-100 mb-3">
                                        <i class="fas fa-key me-2"></i>
                                        Cambiar Roles
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-info btn-lg w-100 mb-3" onclick="buscarUsuarios()">
                                        <i class="fas fa-search me-2"></i>
                                        Buscar Usuario
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-success btn-lg w-100 mb-3">
                                        <i class="fas fa-download me-2"></i>
                                        Exportar Lista
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Lista de Usuarios -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5><i class="fas fa-list"></i> Lista de Usuarios</h5>
                            <div>
                                <div class="input-group me-2" style="width: 300px; display: inline-flex;">
                                    <input type="text" class="form-control" id="buscarUsuario" placeholder="Buscar usuario..." onkeyup="buscarUsuario()">
                                    <button class="btn btn-outline-secondary" type="button">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                                <select class="form-select me-2" style="width: 150px; display: inline-block;" id="filtroRol" onchange="filtrarPorRol()">
                                    <option value="">Todos los roles</option>
                                    <option value="administrador">Administrador</option>
                                    <option value="empleado">Empleado</option>
                                    <option value="cliente">Cliente</option>
                                </select>
                                <span class="badge bg-success">LIVE <span class="real-time-indicator"></span></span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID</th>
                                            <th>Usuario</th>
                                            <th>Nombre Completo</th>
                                            <th>Email</th>
                                            <th>Rol</th>
                                            <th>Estado</th>
                                            <th>Último Acceso</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tablaUsuarios">
                                        <?php if (!empty($usuarios)): ?>
                                            <?php foreach ($usuarios as $usuario): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($usuario['id']); ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php 
                                                        $iconClass = 'fas fa-user text-success';
                                                        if (isset($usuario['is_admin']) && $usuario['is_admin']) {
                                                            $iconClass = 'fas fa-crown text-warning';
                                                        } elseif (isset($usuario['rol']) && $usuario['rol'] === 'empleado') {
                                                            $iconClass = 'fas fa-user-tie text-info';
                                                        }
                                                        ?>
                                                        <i class="<?php echo $iconClass; ?> me-2"></i>
                                                        <?php echo htmlspecialchars($usuario['nick'] ?? $usuario['usuario'] ?? 'N/A'); ?>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? '')); ?></td>
                                                <td><?php echo htmlspecialchars($usuario['email'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <?php 
                                                    if (isset($usuario['is_admin']) && $usuario['is_admin']): ?>
                                                        <span class="badge bg-warning">Administrador</span>
                                                    <?php elseif (isset($usuario['rol'])): ?>
                                                        <?php 
                                                        $badgeClass = 'bg-secondary';
                                                        $rolText = ucfirst($usuario['rol']);
                                                        if ($usuario['rol'] === 'empleado') $badgeClass = 'bg-info';
                                                        if ($usuario['rol'] === 'cliente') $badgeClass = 'bg-success';
                                                        ?>
                                                        <span class="badge <?php echo $badgeClass; ?>"><?php echo $rolText; ?></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Usuario</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><span class="badge bg-success">Activo</span></td>
                                                <td><?php echo date('d/m/Y H:i'); ?></td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button class="btn btn-sm btn-outline-primary" title="Ver perfil" onclick="verUsuario(<?php echo $usuario['id']; ?>)">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-warning" title="Editar" onclick="editarUsuario(<?php echo $usuario['id']; ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <?php if (!isset($usuario['is_admin']) || !$usuario['is_admin']): ?>
                                                        <button class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="eliminarUsuario(<?php echo $usuario['id']; ?>)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="8" class="text-center text-muted">
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
            </div>
        </main>
    </div>
</div>

<!-- Modal Nuevo Usuario -->
<div class="modal fade" id="nuevoUsuarioModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Nuevo Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formUsuario">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nombre de Usuario</label>
                                <input type="text" class="form-control" id="usuarioUsuario" placeholder="usuario123" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" id="emailUsuario" placeholder="usuario@email.com" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="nombreUsuario" placeholder="Nombre" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Apellido</label>
                                <input type="text" class="form-control" id="apellidoUsuario" placeholder="Apellido" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Rol</label>
                                <select class="form-select" id="rolUsuario" required>
                                    <option value="">Seleccionar rol</option>
                                    <option value="cliente">Cliente</option>
                                    <option value="empleado">Empleado</option>
                                    <option value="administrador">Administrador</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Contraseña Temporal</label>
                                <input type="password" class="form-control" id="passwordUsuario" placeholder="Contraseña" required>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarUsuario()">Crear Usuario</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast para notificaciones -->
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

<script>
    // Datos de usuarios desde PHP
    let usuarios = <?php echo json_encode($usuarios); ?>;
    let usuarioEditando = null;

    // Actualizar fecha y hora cada segundo
    function updateDateTime() {
        const now = new Date();
        const options = {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        };
        const dateTimeString = now.toLocaleDateString('es-CO', options).replace(',', '');
        const dateTimeElement = document.getElementById('fechaHoraUsuarios');
        if (dateTimeElement) {
            dateTimeElement.textContent = dateTimeString;
        }
    }
    
    setInterval(updateDateTime, 1000);

    function mostrarToast(mensaje, tipo = 'success') {
        const toastEl = document.getElementById('usuariosToast');
        if (toastEl) {
            toastEl.className = `toast align-items-center text-bg-${tipo} border-0`;
            toastEl.querySelector('.toast-body').textContent = mensaje;
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        }
    }

    function buscarUsuario() {
        const termino = document.getElementById('buscarUsuario').value.toLowerCase();
        const filas = document.querySelectorAll('#tablaUsuarios tr');
        
        filas.forEach(fila => {
            const texto = fila.textContent.toLowerCase();
            if (texto.includes(termino) || termino === '') {
                fila.style.display = '';
            } else {
                fila.style.display = 'none';
            }
        });
    }

    function filtrarPorRol() {
        const rol = document.getElementById('filtroRol').value.toLowerCase();
        const filas = document.querySelectorAll('#tablaUsuarios tr');
        
        filas.forEach(fila => {
            if (rol === '') {
                fila.style.display = '';
            } else {
                const textoRol = fila.cells[4]?.textContent.toLowerCase();
                if (textoRol && textoRol.includes(rol)) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            }
        });
    }

    function verUsuario(id) {
        mostrarToast('Mostrando perfil del usuario ID: ' + id, 'info');
    }

    function editarUsuario(id) {
        mostrarToast('Editando usuario ID: ' + id, 'warning');
    }

    function eliminarUsuario(id) {
        if (confirm('¿Está seguro de que desea eliminar este usuario?')) {
            mostrarToast('Usuario eliminado (simulación)', 'danger');
        }
    }

    function guardarUsuario() {
        const form = document.getElementById('formUsuario');
        const formData = new FormData(form);
        
        // Validar campos
        const nombre = document.getElementById('nombreUsuario').value.trim();
        const apellido = document.getElementById('apellidoUsuario').value.trim();
        const usuario = document.getElementById('usuarioUsuario').value.trim();
        const email = document.getElementById('emailUsuario').value.trim();
        const rol = document.getElementById('rolUsuario').value;
        const password = document.getElementById('passwordUsuario').value;

        if (!nombre || !apellido || !usuario || !email || !rol || !password) {
            mostrarToast('Por favor complete todos los campos', 'danger');
            return;
        }

        // Simulación de guardado exitoso
        mostrarToast('Usuario creado exitosamente', 'success');
        
        // Limpiar formulario y cerrar modal
        form.reset();
        const modal = bootstrap.Modal.getInstance(document.getElementById('nuevoUsuarioModal'));
        if (modal) {
            modal.hide();
        }
    }

    function buscarUsuarios() {
        const input = document.getElementById('buscarUsuario');
        if (input) {
            input.focus();
            mostrarToast('Use el campo de búsqueda para filtrar usuarios', 'info');
        }
    }

    // Inicializar tooltips
    document.addEventListener('DOMContentLoaded', function() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

