<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Intentar incluir la configuración desde diferentes rutas posibles
    $config_paths = [
        __DIR__ . "/../../config/config.php",
        __DIR__ . "/../config/config.php", 
        "../../config/config.php",
        "../config/config.php"
    ];
    
    $config_loaded = false;
    foreach ($config_paths as $path) {
        if (file_exists($path)) {
            include $path;
            $config_loaded = true;
            break;
        }
    }
    
    // Si no se pudo cargar la config, usar valores por defecto
    if (!$config_loaded) {
        define('DB_HOST', 'localhost');
        define('DB_USER', 'root');
        define('DB_PASS', '');
        define('DB_NAME', 'fs_clientes');
    }

    // Conectar a la base de datos
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }

    $email = trim($_POST['email']);
    $dni = trim($_POST['dni']);
    
    // Validar que el usuario exista con el email y DNI proporcionados
    $check_user = $conn->prepare("SELECT id_usuario, nombre, apellido FROM fs_usuarios WHERE email = ? AND dni = ? AND is_visitor = 1");
    $check_user->bind_param("ss", $email, $dni);
    $check_user->execute();
    $result = $check_user->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Generar nueva contraseña temporal (más segura)
        $nueva_password_temporal = bin2hex(random_bytes(6)); // 12 caracteres hexadecimales
        $nueva_password_hash = password_hash($nueva_password_temporal, PASSWORD_BCRYPT);
        
        // Actualizar la contraseña en la base de datos
        $update_password = $conn->prepare("UPDATE fs_usuarios SET password = ?, password_changed_at = CURRENT_TIMESTAMP WHERE id_usuario = ?");
        $update_password->bind_param("si", $nueva_password_hash, $user['id_usuario']);
        
        if ($update_password->execute()) {
            $success_message = "Contraseña restablecida exitosamente para " . htmlspecialchars($user['nombre'] . ' ' . $user['apellido']);
            $temp_password = $nueva_password_temporal;
        } else {
            $error_message = "Error al actualizar la contraseña. Inténtalo nuevamente.";
        }
    } else {
        $error_message = "No se encontró un usuario con el correo electrónico y número de identificación proporcionados.";
    }
    
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - <?php echo APP_NAME ?? 'Sistema de Ventas'; ?></title>
    <link rel="stylesheet" href="../../public/css/login.css">
    <style>
        .message-box {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }
        .success {
            background: #e8f5e8;
            color: #2e7d2e;
            border-left-color: #4caf50;
        }
        .error {
            background: #ffebee;
            color: #c62828;
            border-left-color: #f44336;
        }
        .temp-password {
            background: #fff3cd;
            color: #856404;
            border: 2px solid #ffc107;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            text-align: center;
            font-weight: bold;
            font-size: 18px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .help-text {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>Restablecer Contraseña</h1>
        
        <?php if (isset($success_message)): ?>
            <div class="message-box success">
                <strong>✅ Éxito:</strong> <?php echo $success_message; ?>
            </div>
            
            <?php if (isset($temp_password)): ?>
                <div class="temp-password">
                    <p>Tu nueva contraseña temporal es:</p>
                    <code style="font-size: 24px; color: #d32f2f;"><?php echo htmlspecialchars($temp_password); ?></code>
                    <p style="font-size: 14px; margin-top: 10px;">
                        <strong>⚠️ Por seguridad, cambia esta contraseña después de iniciar sesión</strong>
                    </p>
                </div>
                
                <div style="text-align: center; margin-top: 20px;">
                    <a href="login.php" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">
                        Ir a Iniciar Sesión
                    </a>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            
            <?php if (isset($error_message)): ?>
                <div class="message-box error">
                    <strong>❌ Error:</strong> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <p style="color: #666; margin-bottom: 20px; text-align: center;">
                Para restablecer tu contraseña, necesitamos verificar tu identidad con tu correo electrónico y número de identificación.
            </p>
            
            <form method="POST" action="" id="resetForm">
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" name="email" id="email" required 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                           placeholder="ejemplo@correo.com">
                    <div class="help-text">Ingresa el correo electrónico asociado a tu cuenta</div>
                </div>

                <div class="form-group">
                    <label for="dni">Número de Identificación</label>
                    <input type="text" name="dni" id="dni" required pattern="[0-9]{7,11}" maxlength="20"
                           value="<?php echo isset($_POST['dni']) ? htmlspecialchars($_POST['dni']) : ''; ?>"
                           placeholder="12345678">
                    <div class="help-text">Ingresa tu número de identificación (DNI) registrado</div>
                </div>

                <button type="submit">Restablecer Contraseña</button>
            </form>
            
            <div style="text-align: center; margin-top: 20px;">
                <a href="login.php" style="color: #007bff; text-decoration: none; font-size: 14px;">
                    ← Volver al inicio de sesión
                </a>
            </div>
            
        <?php endif; ?>
    </div>

    <script>
        document.getElementById('resetForm')?.addEventListener('submit', function(e) {
            const dni = document.getElementById('dni').value;
            const email = document.getElementById('email').value;
            
            // Validar formato del DNI
            if (!/^\d{7,11}$/.test(dni)) {
                e.preventDefault();
                alert('El número de identificación debe contener entre 7 y 11 dígitos numéricos');
                return;
            }
            
            // Validar email
            if (!email.includes('@') || !email.includes('.')) {
                e.preventDefault();
                alert('Por favor ingresa un correo electrónico válido');
                return;
            }
            
            // Confirmar acción
            if (!confirm('¿Estás seguro de que deseas restablecer tu contraseña?')) {
                e.preventDefault();
                return;
            }
        });
        
        // Validar que solo se ingresen números en el DNI
        document.getElementById('dni')?.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>
</html>

