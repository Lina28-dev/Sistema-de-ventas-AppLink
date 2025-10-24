<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    include "../../config/config.php"; // conexión a la BD
    
    // Conectar a la base de datos
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }

    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $usuario = trim($_POST['usuario']);
    $dni = trim($_POST['dni']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Validar que el DNI no esté ya registrado
    $check_dni = $conn->prepare("SELECT id_usuario FROM fs_usuarios WHERE dni = ?");
    $check_dni->bind_param("s", $dni);
    $check_dni->execute();
    $dni_exists = $check_dni->get_result()->num_rows > 0;
    
    // Validar que el email no esté ya registrado
    $check_email = $conn->prepare("SELECT id_usuario FROM fs_usuarios WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $email_exists = $check_email->get_result()->num_rows > 0;
    
    // Validar que el nick no esté ya registrado
    $check_nick = $conn->prepare("SELECT id_usuario FROM fs_usuarios WHERE nick = ?");
    $check_nick->bind_param("s", $usuario);
    $check_nick->execute();
    $nick_exists = $check_nick->get_result()->num_rows > 0;

    if ($dni_exists) {
        $error = "El número de identificación ya está registrado";
    } elseif ($email_exists) {
        $error = "El correo electrónico ya está registrado";
    } elseif ($nick_exists) {
        $error = "El nombre de usuario ya está registrado";
    } else {
        $stmt = $conn->prepare("INSERT INTO fs_usuarios (nombre, apellido, nick, dni, email, password) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $nombre, $apellido, $usuario, $dni, $email, $password);
        
        if ($stmt->execute()) {
            header("Location: login.php?success=registered");
            exit();
        } else {
            $error = "Error al registrar usuario: " . $conn->error;
        }
    }
    
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="login-box">
        <h1>Registro de Usuario</h1>
        
        <?php if (isset($error)): ?>
            <div style="background: #ffebee; color: #c62828; padding: 10px; border-radius: 4px; margin-bottom: 15px; border-left: 4px solid #c62828;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" id="registerForm">
            <label for="nombre">Nombre</label>
            <input type="text" name="nombre" id="nombre" required maxlength="50" 
                   value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>">

            <label for="apellido">Apellido</label>
            <input type="text" name="apellido" id="apellido" required maxlength="50"
                   value="<?php echo isset($_POST['apellido']) ? htmlspecialchars($_POST['apellido']) : ''; ?>">

            <label for="usuario">Usuario</label>
            <input type="text" name="usuario" id="usuario" required maxlength="40"
                   value="<?php echo isset($_POST['usuario']) ? htmlspecialchars($_POST['usuario']) : ''; ?>">

            <label for="dni">Número de Identificación (DNI)</label>
            <input type="text" name="dni" id="dni" required maxlength="20" pattern="[0-9]{7,11}"
                   title="Debe contener entre 7 y 11 dígitos numéricos"
                   value="<?php echo isset($_POST['dni']) ? htmlspecialchars($_POST['dni']) : ''; ?>">

            <label for="email">Correo Electrónico</label>
            <input type="email" name="email" id="email" required maxlength="255"
                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">

            <label for="password">Contraseña</label>
            <input type="password" name="password" id="password" required minlength="8">

            <label for="confirm_password">Confirmar Contraseña</label>
            <input type="password" name="confirm_password" id="confirm_password" required minlength="8">

            <button type="submit">Registrar</button>
        </form>
        
        <div style="text-align: center; margin-top: 15px;">
            <a href="login.php" style="color: #007bff; text-decoration: none;">¿Ya tienes cuenta? Inicia sesión</a>
        </div>
    </div>

    <script>
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const dni = document.getElementById('dni').value;
            
            // Validar que las contraseñas coincidan
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Las contraseñas no coinciden');
                return;
            }
            
            // Validar formato del DNI
            if (!/^\d{7,11}$/.test(dni)) {
                e.preventDefault();
                alert('El DNI debe contener entre 7 y 11 dígitos numéricos');
                return;
            }
            
            // Validar longitud de contraseña
            if (password.length < 8) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 8 caracteres');
                return;
            }
        });
        
        // Validar DNI solo números
        document.getElementById('dni').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>
</html>

