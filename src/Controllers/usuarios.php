<?php
// La sesión ya está iniciada en index.php
require_once __DIR__ . '/../Utils/Database.php';
require_once __DIR__ . '/../Utils/CSRFToken.php';
require_once __DIR__ . '/../Utils/ValidadorService.php';

// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true || !$_SESSION['is_admin']) {
    header("Location: /gestor-ventas-lilipink-main/public/dashboard");
    exit();
}

$db = Database::getInstance();
$conn = $db->getConnection();
$mensaje = '';
$error = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !CSRFToken::verify($_POST['csrf_token'])) {
        $error = "Error de seguridad: token inválido";
    } else {
        $validador = new ValidadorService();
        
        switch ($_POST['action']) {
            case 'crear':
                $nombre = $validador->sanitizar($_POST['nombre']);
                $apellido = $validador->sanitizar($_POST['apellido']);
                $nick = $validador->sanitizar($_POST['nick']);
                $email = $validador->sanitizar($_POST['email']);
                $password = $_POST['password'];
                $is_admin = isset($_POST['is_admin']) ? 1 : 0;
                
                if ($validador->validarEmail($email) && strlen($password) >= 8) {
                    try {
                        $stmt = $conn->prepare("
                            INSERT INTO fs_usuarios (nombre, apellido, nick, email, password, is_admin) 
                            VALUES (?, ?, ?, ?, ?, ?)
                        ");
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        $stmt->execute([$nombre, $apellido, $nick, $email, $hashedPassword, $is_admin]);
                        $mensaje = "Usuario creado exitosamente";
                    } catch (PDOException $e) {
                        $error = "Error al crear usuario: " . ($e->getCode() == 23000 ? "El usuario o email ya existe" : "Error del sistema");
                    }
                } else {
                    $error = "Datos inválidos";
                }
                break;
                
            case 'actualizar':
                $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
                $nombre = $validador->sanitizar($_POST['nombre']);
                $apellido = $validador->sanitizar($_POST['apellido']);
                $email = $validador->sanitizar($_POST['email']);
                $is_admin = isset($_POST['is_admin']) ? 1 : 0;
                
                if ($id && $validador->validarEmail($email)) {
                    try {
                        $stmt = $conn->prepare("
                            UPDATE fs_usuarios 
                            SET nombre = ?, apellido = ?, email = ?, is_admin = ?
                            WHERE id_usuario = ?
                        ");
                        $stmt->execute([$nombre, $apellido, $email, $is_admin, $id]);
                        $mensaje = "Usuario actualizado exitosamente";
                    } catch (PDOException $e) {
                        $error = "Error al actualizar usuario: " . ($e->getCode() == 23000 ? "El email ya existe" : "Error del sistema");
                    }
                } else {
                    $error = "Datos inválidos";
                }
                break;
                
            case 'eliminar':
                $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
                if ($id) {
                    try {
                        $stmt = $conn->prepare("DELETE FROM fs_usuarios WHERE id_usuario = ?");
                        $stmt->execute([$id]);
                        $mensaje = "Usuario eliminado exitosamente";
                    } catch (PDOException $e) {
                        $error = "Error al eliminar usuario";
                    }
                }
                break;
        }
    }
}

// Obtener lista de usuarios
$usuarios = $conn->query("SELECT * FROM fs_usuarios ORDER BY nombre")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - AppLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/gestor-ventas-lilipink-main/public/css/base.css">
</head>
<body>
    <?php include __DIR__ . '/../Views/partials/header.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Gestión de Usuarios</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal">
                        <i class="fas fa-plus"></i> Nuevo Usuario
                    </button>
                </div>

                <?php if ($mensaje): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php echo $mensaje; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm">
                    <div class="card-body">
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
                                <tbody>
                                    <?php foreach ($usuarios as $usuario): ?>
                                    <tr>
                                        <td><?php echo $usuario['id_usuario']; ?></td>
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
                                            <button class="btn btn-sm btn-outline-primary me-1" 
                                                    onclick="editarUsuario(<?php echo htmlspecialchars(json_encode($usuario)); ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger"
                                                    onclick="confirmarEliminar(<?php echo $usuario['id_usuario']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Usuario -->
    <div class="modal fade" id="userModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="userForm" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo CSRFToken::generate(); ?>">
                    <input type="hidden" name="action" value="crear">
                    <input type="hidden" name="id" id="userId">

                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Nuevo Usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>

                        <div class="mb-3">
                            <label for="apellido" class="form-label">Apellido</label>
                            <input type="text" class="form-control" id="apellido" name="apellido" required>
                        </div>

                        <div class="mb-3">
                            <label for="nick" class="form-label">Usuario</label>
                            <input type="text" class="form-control" id="nick" name="nick" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="mb-3" id="passwordGroup">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   minlength="8" required>
                            <div class="form-text">
                                Mínimo 8 caracteres, debe incluir mayúsculas, minúsculas y números
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_admin" name="is_admin">
                                <label class="form-check-label" for="is_admin">Es administrador</label>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación de Eliminación -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo CSRFToken::generate(); ?>">
                    <input type="hidden" name="action" value="eliminar">
                    <input type="hidden" name="id" id="deleteUserId">

                    <div class="modal-header">
                        <h5 class="modal-title">Confirmar Eliminación</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        ¿Estás seguro de que deseas eliminar este usuario?
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Función para editar usuario
        function editarUsuario(usuario) {
            document.getElementById('modalTitle').textContent = 'Editar Usuario';
            document.getElementById('userForm').action.value = 'actualizar';
            document.getElementById('userId').value = usuario.id_usuario;
            document.getElementById('nombre').value = usuario.nombre;
            document.getElementById('apellido').value = usuario.apellido;
            document.getElementById('nick').value = usuario.nick;
            document.getElementById('nick').readOnly = true;
            document.getElementById('email').value = usuario.email;
            document.getElementById('is_admin').checked = usuario.is_admin == 1;
            document.getElementById('passwordGroup').style.display = 'none';
            document.getElementById('password').required = false;
            
            new bootstrap.Modal(document.getElementById('userModal')).show();
        }

        // Función para confirmar eliminación
        function confirmarEliminar(id) {
            document.getElementById('deleteUserId').value = id;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }

        // Resetear formulario al cerrar modal
        document.getElementById('userModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('userForm').reset();
            document.getElementById('modalTitle').textContent = 'Nuevo Usuario';
            document.getElementById('userForm').action.value = 'crear';
            document.getElementById('nick').readOnly = false;
            document.getElementById('passwordGroup').style.display = 'block';
            document.getElementById('password').required = true;
        });
    </script>
</body>
</html>