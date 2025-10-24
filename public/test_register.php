<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test - Registro de Usuario</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="login-box">
        <h1>Test - Registro de Usuario</h1>
        
        <div id="message"></div>
        
        <form id="testRegisterForm" method="POST">
            <div class="form-group">
                <label for="nombre">Nombre Completo</label>
                <input type="text" name="nombre" id="nombre" required>
            </div>
            
            <div class="form-group">
                <label for="nick">Usuario</label>
                <input type="text" name="nick" id="nick" required>
            </div>
            
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" name="email" id="email" required>
            </div>
            
            <div class="form-group">
                <label for="telefono">Teléfono</label>
                <input type="tel" name="telefono" id="telefono" required>
            </div>
            
            <div class="form-group">
                <label for="cc">Cédula de Ciudadanía</label>
                <input type="text" name="cc" id="cc" required pattern="[0-9]{7,11}" maxlength="20">
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" name="password" id="password" required minlength="8">
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirmar Contraseña</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            </div>
            
            <input type="hidden" name="tipo_usuario" value="cliente">
            
            <button type="submit">Registrarse</button>
        </form>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="index.php">← Volver al inicio</a>
        </div>
    </div>

    <script>
        document.getElementById('testRegisterForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const messageDiv = document.getElementById('message');
            const submitBtn = this.querySelector('button[type="submit"]');
            
            // Validar CC
            const cc = document.getElementById('cc').value;
            if (!/^\d{7,11}$/.test(cc)) {
                messageDiv.innerHTML = '<div class="error">La CC debe contener entre 7 y 11 dígitos numéricos</div>';
                return;
            }
            
            // Validar contraseñas
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            if (password !== confirmPassword) {
                messageDiv.innerHTML = '<div class="error">Las contraseñas no coinciden</div>';
                return;
            }
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Registrando...';
            messageDiv.innerHTML = '<div style="color: blue;">Procesando registro...</div>';
            
            fetch('register_process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    messageDiv.innerHTML = '<div class="success">✅ ' + data.message + '</div>';
                    this.reset();
                } else {
                    messageDiv.innerHTML = '<div class="error">❌ ' + data.message + '</div>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                messageDiv.innerHTML = '<div class="error">❌ Error de conexión. Por favor, inténtalo nuevamente.</div>';
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Registrarse';
            });
        });
        
        // Validación de CC en tiempo real
        document.getElementById('cc').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>
</html>