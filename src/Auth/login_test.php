<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba de Login - Lilipink</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body { background-color: #f8f9fa; }
        .login-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <h2 class="text-center mb-4">Prueba de Login</h2>
            
            <div id="messages"></div>

            <form id="loginForm" method="POST" action="auth.php">
                <div class="mb-3">
                    <label for="nick" class="form-label">Usuario</label>
                    <input type="text" class="form-control" id="nick" name="nick" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
                </div>
            </form>

            <div class="mt-3">
                <p class="text-muted">Credenciales de prueba:</p>
                <ul>
                    <li>Usuario: admin</li>
                    <li>Contraseña: admin123</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                
                $.ajax({
                    type: 'POST',
                    url: 'auth.php',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#messages').html('<div class="alert alert-success">Login exitoso! Redirigiendo...</div>');
                            setTimeout(function() {
                                window.location.href = 'dashboard.php';
                            }, 1500);
                        } else {
                            $('#messages').html('<div class="alert alert-danger">' + response.message + '</div>');
                        }
                    },
                    error: function(xhr) {
                        let message = 'Error en el servidor';
                        try {
                            message = JSON.parse(xhr.responseText).message;
                        } catch(e) {}
                        $('#messages').html('<div class="alert alert-danger">' + message + '</div>');
                    }
                });
            });
        });
    </script>
</body>
</html>
