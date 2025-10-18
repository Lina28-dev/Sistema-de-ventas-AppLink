<?php
require_once 'classes/Database.php';
require_once 'classes/CSRFToken.php';
require_once 'classes/ValidadorService.php';
require_once 'config/config.php';

$token = $_GET['token'] ?? '';
$validToken = false;
$userId = null;

if (empty($token)) {
    header("Location: index.php");
    exit();
}

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Verificar token
    $stmt = $conn->prepare("
        SELECT pr.user_id, u.email 
        FROM password_resets pr 
        JOIN fs_usuarios u ON pr.user_id = u.id_usuario
        WHERE pr.token = ? 
        AND pr.expiry > NOW() 
        AND pr.used = FALSE
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $result = $stmt->fetch();
    
    if ($result) {
        $validToken = true;
        $userId = $result['user_id'];
    }
} catch (Exception $e) {
    error_log("Error en reset_password_confirm.php: " . $e->getMessage());
    $_SESSION['error'] = "Ha ocurrido un error. Por favor, intenta más tarde.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    if (!isset($_POST['csrf_token']) || !CSRFToken::verify($_POST['csrf_token'])) {
        $_SESSION['error'] = "Error de seguridad: token inválido";
        header("Location: reset_password_confirm.php?token=" . $token);
        exit();
    }
    
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Validar contraseña
    $validator = new ValidadorService();
    if (!$validator->validarPassword($password)) {
        $_SESSION['error'] = implode(", ", $validator->getErrores());
        header("Location: reset_password_confirm.php?token=" . $token);
        exit();
    }
    
    if ($password !== $confirmPassword) {
        $_SESSION['error'] = "Las contraseñas no coinciden";
        header("Location: reset_password_confirm.php?token=" . $token);
        exit();
    }
    
    try {
        $conn->beginTransaction();
        
        // Actualizar contraseña
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("
            UPDATE fs_usuarios 
            SET password = ?, password_changed_at = NOW() 
            WHERE id_usuario = ?
        ");
        $stmt->execute([$hashedPassword, $userId]);
        
        // Marcar token como usado
        $stmt = $conn->prepare("
            UPDATE password_resets 
            SET used = TRUE 
            WHERE token = ?
        ");
        $stmt->execute([$token]);
        
        $conn->commit();
        
        $_SESSION['success'] = "Tu contraseña ha sido actualizada exitosamente. Ya puedes iniciar sesión.";
        header("Location: index.php");
        exit();
        
    } catch (Exception $e) {
        $conn->rollBack();
        error_log("Error al actualizar contraseña: " . $e->getMessage());
        $_SESSION['error'] = "Error al actualizar la contraseña. Por favor, intenta nuevamente.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña - Lili Pink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/base.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-lg">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <img src="img/logo.jpg" alt="Logo" class="img-fluid mb-4" style="max-width: 150px;">
                            <h2>Nueva Contraseña</h2>
                        </div>

                        <?php if (!$validToken): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                El enlace ha expirado o no es válido. Por favor, solicita un nuevo enlace para restablecer tu contraseña.
                            </div>
                            <div class="text-center mt-4">
                                <a href="reset_password.php" class="btn btn-primary">Solicitar Nuevo Enlace</a>
                            </div>
                        <?php else: ?>
                            <?php if (isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger">
                                    <?php 
                                        echo $_SESSION['error'];
                                        unset($_SESSION['error']);
                                    ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" class="needs-validation" novalidate>
                                <input type="hidden" name="csrf_token" value="<?php echo CSRFToken::generate(); ?>">
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label">Nueva Contraseña</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" id="password" name="password" 
                                               required minlength="8">
                                    </div>
                                    <div class="form-text">
                                        La contraseña debe tener al menos 8 caracteres, incluir mayúsculas, minúsculas y números.
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" id="confirm_password" 
                                               name="confirm_password" required minlength="8">
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        Guardar Nueva Contraseña
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>