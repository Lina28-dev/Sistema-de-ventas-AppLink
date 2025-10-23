<?php
session_start();

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['usuario_nombre'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Lili Pink</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            max-width: 400px;
            width: 100%;
        }
        .login-logo {
            max-width: 150px;
            margin-bottom: 20px;
        }
        .btn-primary {
            background-color: #FF1493;
            border-color: #FF1493;
        }
        .btn-primary:hover {
            background-color: #FF69B4;
            border-color: #FF69B4;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="login-container text-center">
                    <img src="/Sistema-de-ventas-AppLink-main/public/assets/images/logo.jpg" alt="Lili Pink Logo" class="login-logo">
                    <h2 class="mb-4">Iniciar Sesión</h2>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?php 
                                echo htmlspecialchars($_SESSION['error']); 
                                unset($_SESSION['error']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <form id="loginForm" action="/Sistema-de-ventas-AppLink-main/public/auth" method="POST" class="text-start">
                        <div class="mb-3">
                            <label for="nick" class="form-label">Usuario</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="nick" name="nick" 
                                       required placeholder="Ingrese su usuario">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" 
                                       required placeholder="Ingrese su contraseña">
                            </div>
                        </div>

                        <div id="errorMessage" class="alert alert-danger d-none"></div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                Ingresar <i class="fas fa-sign-in-alt ms-2"></i>
                            </button>
                        </div>

                        <div class="text-center mt-3">
                            <a href="/Sistema-de-ventas-AppLink-main/public/reset_password" class="text-muted">¿Olvidaste tu contraseña?</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const errorDiv = document.getElementById('errorMessage');
            
            try {
                const response = await fetch('/Sistema-de-ventas-AppLink-main/public/auth', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Redirigir al dashboard
                    window.location.href = '/Sistema-de-ventas-AppLink-main/public/dashboard';
                } else {
                    // Mostrar error
                    errorDiv.textContent = data.message || 'Error al iniciar sesión';
                    errorDiv.classList.remove('d-none');
                }
            } catch (error) {
                errorDiv.textContent = 'Error de conexión. Intente nuevamente.';
                errorDiv.classList.remove('d-none');
            }
        });
    </script>
</body>
</html>
