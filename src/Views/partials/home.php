// Este archivo debe estar en src/Views, no en partials. Moviendo...
<?php
session_start();

// Si el usuario ya está autenticado, redirigir al dashboard
if (isset($_SESSION['usuario_nombre'])) {
    header("Location: indexx.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lili Pink - Sistema de Gestión de Ventas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/home.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="img/logo.jpg" alt="Lili Pink Logo" height="40">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#inicio">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#caracteristicas">Características</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contacto">Contacto</a>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="btn btn-link nav-link" data-bs-toggle="modal" data-bs-target="#loginModal">
                            <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="inicio">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1>Sistema de Gestión de Ventas</h1>
                    <p class="lead">Administra tu inventario, ventas y clientes de manera eficiente con nuestra plataforma integral.</p>
                    <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#loginModal">
                        Comenzar Ahora
                    </button>
                </div>
                <div class="col-lg-6">
                    <img src="img/hero-image.png" alt="Dashboard Preview" class="img-fluid hero-image">
                </div>
            </div>
        </div>
    </section>

    <!-- Modal de Login -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="loginModalLabel">Iniciar Sesión</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <img src="img/logo.jpg" alt="Logo" class="modal-logo" style="max-width: 150px;">
                        <h2 class="mt-3">Iniciar Sesión</h2>
                    </div>
                    <form action="Login/auth.php" method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="usuario" class="form-label">Usuario</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="usuario" name="usuario" 
                                       placeholder="Ingrese su usuario" required>
                            </div>
                            <div class="invalid-feedback">
                                Por favor ingrese su usuario
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Ingrese su contraseña" required>
                            </div>
                            <div class="invalid-feedback">
                                Por favor ingrese su contraseña
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                Entrar <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                        
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger mt-3">
                                <?php 
                                    echo htmlspecialchars($_SESSION['error']); 
                                    unset($_SESSION['error']);
                                ?>
                            </div>
                        <?php endif; ?>

                        <div class="text-center mt-3">
                            <a href="reset_password.php" class="text-muted">¿Olvidaste tu contraseña?</a>
                            <br>
                            <a href="#" class="text-primary mt-2 d-inline-block" data-bs-toggle="modal" data-bs-target="#registerModal">
                                ¿Eres nuevo? Regístrate aquí
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Registro -->
    <div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="registerModalLabel">Registro de Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-12 text-center">
                            <h4>¿Cómo deseas registrarte?</h4>
                            <div class="btn-group" role="group">
                                <input type="radio" class="btn-check" name="userType" id="clienteBtn" value="cliente" checked>
                                <label class="btn btn-outline-primary" for="clienteBtn">
                                    <i class="fas fa-user"></i> Cliente
                                </label>
                                <input type="radio" class="btn-check" name="userType" id="empleadoBtn" value="empleado">
                                <label class="btn btn-outline-primary" for="empleadoBtn">
                                    <i class="fas fa-id-card"></i> Empleado
                                </label>
                            </div>
                        </div>
                    </div>

                    <form id="registerForm" action="register.php" method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="tipo_usuario" id="tipo_usuario" value="cliente">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombre Completo</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                                <div class="invalid-feedback">Por favor ingrese su nombre completo</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="nick" class="form-label">Usuario</label>
                                <input type="text" class="form-control" id="nick" name="nick" required>
                                <div class="invalid-feedback">Por favor elija un nombre de usuario</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                                <div class="invalid-feedback">Por favor ingrese un correo válido</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono" required>
                                <div class="invalid-feedback">Por favor ingrese un teléfono válido</div>
                            </div>
                        </div>

                        <div class="row" id="campos_cliente">
                            <div class="col-md-6 mb-3">
                                <label for="direccion" class="form-label">Dirección</label>
                                <input type="text" class="form-control" id="direccion" name="direccion">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="ciudad" class="form-label">Ciudad</label>
                                <input type="text" class="form-control" id="ciudad" name="ciudad">
                            </div>
                        </div>

                        <div class="row" id="campos_empleado" style="display: none;">
                            <div class="col-md-6 mb-3">
                                <label for="codigo_empleado" class="form-label">Código de Empleado</label>
                                <input type="text" class="form-control" id="codigo_empleado" name="codigo_empleado">
                                <div class="invalid-feedback">Por favor ingrese el código de empleado</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="departamento" class="form-label">Departamento</label>
                                <select class="form-select" id="departamento" name="departamento">
                                    <option value="">Seleccione...</option>
                                    <option value="ventas">Ventas</option>
                                    <option value="inventario">Inventario</option>
                                    <option value="administracion">Administración</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password_reg" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="password_reg" name="password" required>
                                <div class="invalid-feedback">Por favor ingrese una contraseña</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                <div class="invalid-feedback">Las contraseñas no coinciden</div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                Registrarse <i class="fas fa-user-plus ms-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mostrar el modal si hay errores
            <?php if (isset($_SESSION['error']) || isset($_GET['error'])): ?>
                var loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
                loginModal.show();
            <?php endif; ?>

            // Mostrar/ocultar campos según el tipo de usuario
            document.querySelectorAll('[name="userType"]').forEach(function(radio) {
                radio.addEventListener('change', function() {
                    document.getElementById('tipo_usuario').value = this.value;
                    if (this.value === 'cliente') {
                        document.getElementById('campos_cliente').style.display = 'flex';
                        document.getElementById('campos_empleado').style.display = 'none';
                        // Hacer campos de empleado no requeridos
                        document.getElementById('codigo_empleado').required = false;
                        document.getElementById('departamento').required = false;
                    } else {
                        document.getElementById('campos_cliente').style.display = 'none';
                        document.getElementById('campos_empleado').style.display = 'flex';
                        // Hacer campos de empleado requeridos
                        document.getElementById('codigo_empleado').required = true;
                        document.getElementById('departamento').required = true;
                    }
                });
            });

            // Validación de contraseñas
            document.getElementById('confirm_password').addEventListener('input', function() {
                var password = document.getElementById('password_reg').value;
                if (this.value !== password) {
                    this.setCustomValidity('Las contraseñas no coinciden');
                } else {
                    this.setCustomValidity('');
                }
            });

            // Validación del formulario
            var forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                });
            });
        });
    </script>
</body>
</html>
