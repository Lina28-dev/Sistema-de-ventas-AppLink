<?php
/**
 * Restablecimiento de contraseña con validación por DNI + Email
 * Sistema de Ventas AppLink
 */

// Configuración de errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$success_message = '';
$error_message = '';
$temp_password = '';
$show_email_confirmation = false;
$email_pista = '';
$user_name = '';
$user_email = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        // Conectar a la base de datos
        $conn = new mysqli('localhost', 'root', '', 'fs_clientes');
        
        if ($conn->connect_error) {
            throw new Exception("Error de conexión: " . $conn->connect_error);
        }

        $cc = trim($_POST['cc']);
        
        // Validar que el campo no esté vacío
        if (empty($cc)) {
            $error_message = "Por favor, ingresa tu cédula de ciudadanía.";
        } else {
            // Buscar usuario por cédula
            $check_user = $conn->prepare("SELECT id_usuario, nombre, apellido, email FROM fs_usuarios WHERE cc = ?");
            $check_user->bind_param("s", $cc);
            $check_user->execute();
            $result = $check_user->get_result();
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                // Función para crear pistas del email
                function crearPistaEmail($email) {
                    $parts = explode('@', $email);
                    if (count($parts) != 2) return $email;
                    
                    $username = $parts[0];
                    $domain = $parts[1];
                    
                    // Mostrar primera y última letra del usuario
                    if (strlen($username) <= 2) {
                        $pista_username = $username[0] . '*';
                    } else {
                        $middle = str_repeat('*', strlen($username) - 2);
                        $pista_username = $username[0] . $middle . $username[strlen($username) - 1];
                    }
                    
                    // Mostrar pista del dominio
                    $domain_parts = explode('.', $domain);
                    if (count($domain_parts) >= 2) {
                        $main_domain = $domain_parts[0];
                        $extension = end($domain_parts);
                        
                        if (strlen($main_domain) <= 2) {
                            $pista_domain = $main_domain[0] . '*';
                        } else {
                            $middle_domain = str_repeat('*', strlen($main_domain) - 2);
                            $pista_domain = $main_domain[0] . $middle_domain . $main_domain[strlen($main_domain) - 1];
                        }
                        
                        return $pista_username . '@' . $pista_domain . '.' . $extension;
                    }
                    
                    return $pista_username . '@' . $domain;
                }
                
                $email_pista = crearPistaEmail($user['email']);
                
                // Si el usuario confirma que es su email, proceder con el reset
                if (isset($_POST['confirm_email'])) {
                    // Generar nueva contraseña temporal (más segura)
                    $nueva_password_temporal = bin2hex(random_bytes(6)); // 12 caracteres hexadecimales
                    $nueva_password_hash = password_hash($nueva_password_temporal, PASSWORD_BCRYPT);
                    
                    // Actualizar la contraseña en la base de datos
                    $update_password = $conn->prepare("UPDATE fs_usuarios SET password = ? WHERE id_usuario = ?");
                    $update_password->bind_param("si", $nueva_password_hash, $user['id_usuario']);
                    
                    if ($update_password->execute()) {
                        $success_message = "Contraseña restablecida exitosamente para " . htmlspecialchars($user['nombre'] . ' ' . $user['apellido']);
                        $temp_password = $nueva_password_temporal;
                        $user_email = $user['email'];
                    } else {
                        $error_message = "Error al actualizar la contraseña. Inténtalo nuevamente.";
                    }
                } else {
                    // Mostrar confirmación de email
                    $show_email_confirmation = true;
                    $user_name = $user['nombre'] . ' ' . $user['apellido'];
                }
            } else {
                $error_message = "No se encontró un usuario registrado con esta cédula de ciudadanía.";
            }
        }
        
        $conn->close();
        
    } catch (Exception $e) {
        $error_message = "Error del sistema: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - AppLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/theme-styles.css">
    <style>
        :root {
            --primary-color: #e91e63;
            --secondary-color: #00bcd4;
            --accent-color: #ff4081;
            --dark-color: #212529;
            --light-bg: #f8f9fa;
        }

        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .reset-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .reset-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
        }

        .reset-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
        }

        .logo-container {
            position: relative;
            z-index: 2;
            margin-bottom: 1rem;
        }

        .logo {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .logo i {
            font-size: 2.5rem;
            color: white;
        }

        .logo-img {
            width: 70px;
            height: 70px;
            object-fit: contain;
            border-radius: 50%;
            border: 3px solid rgba(255, 255, 255, 0.8);
            background: rgba(255, 255, 255, 0.1);
            padding: 5px;
        }

        .reset-title {
            font-size: 1.8rem;
            font-weight: 600;
            margin: 0;
            position: relative;
            z-index: 2;
        }

        .reset-subtitle {
            font-size: 0.95rem;
            opacity: 0.9;
            margin-top: 0.5rem;
            position: relative;
            z-index: 2;
        }

        .reset-body {
            padding: 2rem;
        }

        .form-floating {
            margin-bottom: 1.5rem;
        }

        .form-floating > .form-control {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            height: 60px;
            transition: all 0.3s ease;
        }

        .form-floating > .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(233, 30, 99, 0.25);
        }

        .form-floating > label {
            color: #6c757d;
            font-weight: 500;
        }

        .btn-reset {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            border: none;
            border-radius: 12px;
            padding: 15px 30px;
            font-weight: 600;
            font-size: 1.1rem;
            width: 100%;
            margin-top: 1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(233, 30, 99, 0.3);
        }

        .btn-reset:active {
            transform: translateY(0);
        }

        .message-box {
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
            border: none;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .message-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .message-error {
            background: linear-gradient(135deg, #dc3545, #fd7e14);
            color: white;
        }

        .temp-password-box {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
            color: #212529;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            margin: 1.5rem 0;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }

        .temp-password {
            font-size: 2rem;
            font-weight: bold;
            font-family: 'Courier New', monospace;
            background: rgba(255, 255, 255, 0.3);
            padding: 15px;
            border-radius: 10px;
            margin: 15px 0;
            letter-spacing: 3px;
        }

        .back-link {
            text-align: center;
            margin-top: 2rem;
        }

        .back-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .back-link a:hover {
            color: var(--accent-color);
            transform: translateX(-5px);
        }

        .help-text {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .input-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            z-index: 5;
        }

        .form-floating {
            position: relative;
        }

        .email-hint-box {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border: 2px dashed var(--primary-color);
            border-radius: 15px;
            padding: 20px;
            margin: 15px 0;
            font-family: 'Courier New', monospace;
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--dark-color);
            letter-spacing: 1px;
            animation: glow 2s ease-in-out infinite alternate;
        }

        @keyframes glow {
            from {
                box-shadow: 0 0 10px rgba(233, 30, 99, 0.3);
            }
            to {
                box-shadow: 0 0 20px rgba(233, 30, 99, 0.6);
            }
        }

        .btn-outline-secondary {
            border: 2px solid #6c757d;
            color: #6c757d;
            border-radius: 12px;
            padding: 15px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-outline-secondary:hover {
            background-color: #6c757d;
            border-color: #6c757d;
            color: white;
            transform: translateY(-2px);
        }

        @media (max-width: 576px) {
            .reset-container {
                margin: 10px;
                border-radius: 15px;
            }
            
            .reset-header, .reset-body {
                padding: 1.5rem;
            }
            
            .logo {
                width: 60px;
                height: 60px;
            }
            
            .logo i {
                font-size: 2rem;
            }
            
            .logo-img {
                width: 50px;
                height: 50px;
                padding: 3px;
            }
            
            .reset-title {
                font-size: 1.5rem;
            }
            
            .email-hint-box {
                font-size: 1rem;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <!-- Header con logo y branding -->
        <div class="reset-header">
            <div class="logo-container">
                <div class="logo">
                    <img src="assets/images/logo.jpg" alt="AppLink Logo" class="logo-img">
                </div>
                <h1 class="reset-title">Restablecer Contraseña</h1>
                <p class="reset-subtitle">Sistema de Ventas AppLink</p>
            </div>
        </div>

        <div class="reset-body">
            <?php if (!empty($success_message)): ?>
                <div class="message-box message-success">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong><?php echo $success_message; ?></strong>
                    </div>
                </div>
                
                <?php if (!empty($temp_password)): ?>
                    <div class="temp-password-box">
                        <h4 class="mb-3">
                            <i class="fas fa-key me-2"></i>
                            Tu nueva contraseña temporal
                        </h4>
                        <div class="temp-password">
                            <?php echo htmlspecialchars($temp_password); ?>
                        </div>
                        <div class="alert alert-warning mb-0" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Importante:</strong> Cambia esta contraseña después de iniciar sesión por tu seguridad
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <a href="http://localhost/Sistema-de-ventas-AppLink-main/public/" class="btn btn-reset">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            Ir a Iniciar Sesión
                        </a>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                
                <?php if (!empty($error_message)): ?>
                    <div class="message-box message-error">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <strong><?php echo $error_message; ?></strong>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ($show_email_confirmation): ?>
                    <!-- Pantalla de confirmación de email -->
                    <div class="text-center mb-4">
                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-user-check fa-2x mb-3 text-primary"></i>
                            <h5 class="mb-3">Usuario encontrado: <strong><?php echo htmlspecialchars($user_name); ?></strong></h5>
                            <p class="mb-3">Hemos encontrado tu cuenta asociada al correo:</p>
                            <div class="email-hint-box">
                                <code class="fs-5"><?php echo htmlspecialchars($email_pista); ?></code>
                            </div>
                            <p class="mt-3 mb-0">¿Es este tu correo electrónico?</p>
                        </div>
                    </div>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="cc" value="<?php echo htmlspecialchars($_POST['cc']); ?>">
                        <input type="hidden" name="confirm_email" value="1">
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-reset">
                                <i class="fas fa-check me-2"></i>
                                Sí, es mi correo - Restablecer contraseña
                            </button>
                            <a href="reset_password.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>
                                No es mi correo - Intentar de nuevo
                            </a>
                        </div>
                    </form>
                    
                <?php else: ?>
                    <!-- Formulario inicial de cédula -->
                    <div class="text-center mb-4">
                        <i class="fas fa-info-circle text-primary me-2"></i>
                        <span class="text-muted">
                            Ingresa tu cédula de ciudadanía para encontrar tu cuenta
                        </span>
                    </div>
                
                    <form method="POST" action="" id="resetForm">
                        <div class="form-floating">
                            <input type="text" 
                                   class="form-control" 
                                   id="cc" 
                                   name="cc" 
                                   placeholder="12345678"
                                   pattern="[0-9]{7,11}" 
                                   maxlength="20"
                                   value="<?php echo isset($_POST['cc']) ? htmlspecialchars($_POST['cc']) : ''; ?>"
                                   required>
                            <label for="cc">
                                <i class="fas fa-id-card me-2"></i>
                                Cédula de Ciudadanía
                            </label>
                            <span class="input-icon">
                                <i class="fas fa-hashtag"></i>
                            </span>
                            <div class="help-text">
                                <i class="fas fa-info-circle"></i>
                                Ingresa tu cédula registrada (7-11 dígitos)
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-reset">
                                <i class="fas fa-search me-2"></i>
                                Buscar mi cuenta
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
                
            <?php endif; ?>

            <div class="back-link">
                <a href="http://localhost/Sistema-de-ventas-AppLink-main/public/">
                    <i class="fas fa-arrow-left"></i>
                    Volver al inicio de sesión
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/theme-switcher.js"></script>
    <script>
        // Animaciones y efectos de interacción
        document.addEventListener('DOMContentLoaded', function() {
            // Efecto de typing en el título
            const title = document.querySelector('.reset-title');
            if (title) {
                title.style.opacity = '0';
                setTimeout(() => {
                    title.style.transition = 'opacity 0.8s ease-in-out';
                    title.style.opacity = '1';
                }, 300);
            }

            // Validación del formulario con efectos visuales
            const form = document.getElementById('resetForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const ccInput = document.getElementById('cc');
                    if (ccInput) {
                        const cc = ccInput.value;
                        const submitBtn = this.querySelector('button[type="submit"]');
                        
                        // Validar formato de la CC
                        if (!/^\d{7,11}$/.test(cc)) {
                            e.preventDefault();
                            showAlert('La cédula debe contener entre 7 y 11 dígitos numéricos', 'error');
                            shakeElement(ccInput);
                            return;
                        }
                        
                        // Efecto de carga en el botón
                        if (submitBtn.innerHTML.includes('Buscar')) {
                            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Buscando...';
                        } else {
                            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Procesando...';
                        }
                        submitBtn.disabled = true;
                    }
                });
            }

            // Validación en tiempo real de la CC
            const ccInput = document.getElementById('cc');
            if (ccInput) {
                ccInput.addEventListener('input', function(e) {
                    this.value = this.value.replace(/[^0-9]/g, '');
                    
                    // Validación visual
                    if (this.value.length >= 7 && this.value.length <= 11) {
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                    } else if (this.value.length > 0) {
                        this.classList.remove('is-valid');
                        this.classList.add('is-invalid');
                    } else {
                        this.classList.remove('is-valid', 'is-invalid');
                    }
                });

                ccInput.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'scale(1.02)';
                    this.parentElement.style.transition = 'transform 0.2s ease';
                });

                ccInput.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'scale(1)';
                });
            }

            // Animación para la caja de pista de email
            const emailHintBox = document.querySelector('.email-hint-box');
            if (emailHintBox) {
                emailHintBox.style.opacity = '0';
                emailHintBox.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    emailHintBox.style.transition = 'all 0.6s ease-out';
                    emailHintBox.style.opacity = '1';
                    emailHintBox.style.transform = 'translateY(0)';
                }, 200);
            }
        });

        // Función para mostrar alertas
        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type === 'error' ? 'danger' : 'success'} alert-dismissible fade show`;
            alertDiv.style.position = 'fixed';
            alertDiv.style.top = '20px';
            alertDiv.style.right = '20px';
            alertDiv.style.zIndex = '9999';
            alertDiv.style.minWidth = '300px';
            alertDiv.innerHTML = `
                <i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'check-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(alertDiv);
            
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }

        // Función para efecto shake
        function shakeElement(element) {
            element.style.animation = 'shake 0.5s ease-in-out';
            setTimeout(() => {
                element.style.animation = '';
            }, 500);
        }

        // CSS para las animaciones
        const shakeStyle = document.createElement('style');
        shakeStyle.textContent = `
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
                20%, 40%, 60%, 80% { transform: translateX(5px); }
            }
            
            .is-valid {
                border-color: #28a745 !important;
                box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25) !important;
            }
            
            .is-invalid {
                border-color: #dc3545 !important;
                box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
            }
        `;
        document.head.appendChild(shakeStyle);
    </script>
</body>
</html>