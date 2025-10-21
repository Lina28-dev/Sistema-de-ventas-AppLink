<?php
// 1. Manejo de Sesión
// Inicializar sesión si no está activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. Redirección de Autenticación (¡Corregido Bucle!)
// Si el usuario ya está autenticado, redirigir al dashboard
if (isset($_SESSION['usuario_nombre'])) {
    // Redirección CORRECTA a la página principal del sistema (NO a sí mismo)
    header("Location: /Sistema-de-ventas-AppLink-main/public/dashboard");
    exit();
}

// 3. Manejo de Errores de Login
$login_error = null;
if (isset($_SESSION['error'])) {
    // Usamos htmlspecialchars() para prevenir XSS al mostrar el error
    $login_error = htmlspecialchars($_SESSION['error']);
    unset($_SESSION['error']); // Limpiamos la variable de sesión después de obtenerla
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AppLink - Sistema de Gestión de Ventas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/home.css">

    <style>
        /* Definición de Variables CSS para fácil mantenimiento de marca */
        :root {
            --lili-pink-primary: #e91e63; /* Rosa principal de AppLink */
            --lili-pink-dark: #ad1457;    /* Rosa para hover */
            --font-primary: 'Poppins', Arial, sans-serif;
        }

        body, html, .container, .navbar, .card, .form-control, .btn, h1, h2, h3, h4, h5, h6, p, ul, li, label, input, textarea, select {
            font-family: 'Poppins', Arial, sans-serif !important;
        }

        /* Navbar */
        .navbar {
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        }
        .navbar-brand img {
            margin-right: 8px;
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(120deg, #ffe4ec 0%, #fff 100%);
            padding: 100px 0 60px 0;
        }
        .hero-image {
            border-radius: 1rem;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        }

        /* Tarjetas y Efectos */
        .card {
            border-radius: 1rem;
        }
        .card .fa-star, .card .fa-users, .card .fa-shopping-cart, .card .fa-chart-line, .card .fa-user-shield, .card .fa-mobile-alt, .card .fa-bell {
            transition: transform 0.2s;
        }
        .card:hover .fa-star, .card:hover .fa-users, .card:hover .fa-shopping-cart, .card:hover .fa-chart-line, .card:hover .fa-user-shield, .card:hover .fa-mobile-alt, .card:hover .fa-bell {
            transform: scale(1.2);
        }

        /* Botón y Texto Rosa Personalizado */
        .btn-pink {
            background: var(--lili-pink-primary);
            color: #fff;
            border: none;
            transition: background-color 0.2s;
        }
        .btn-pink:hover {
            background: var(--lili-pink-dark);
            color: #fff;
        }
        .text-pink {
            color: var(--lili-pink-primary) !important; 
        }

        /* Modal */
        .modal-content {
            border-radius: 1rem;
            box-shadow: 0 4px 24px rgba(233,30,99,0.08);
        }
        .modal-backdrop {
            background: rgba(233,30,99,0.08);
        }

        /* Footer */
        .footer {
            background: #fff;
            color: var(--lili-pink-primary);
            text-align: center;
            padding: 18px 0 10px 0;
            font-size: 1rem;
            box-shadow: 0 -2px 8px rgba(0,0,0,0.07);
            position: fixed;
            left: 0; right: 0; bottom: 0;
            z-index: 100;
        }

        /* Media Queries */
        @media (max-width: 767px) {
            .hero { padding: 60px 0 30px 0; }
            .footer { font-size: 0.9rem; }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
    <div class="container">
        <a class="navbar-brand" href="#">
            <img src="img/logo.jpg" alt="AppLink Logo" height="40">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="#inicio"><strong>Inicio</strong></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#caracteristicas"><strong>Características</strong></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#contacto"><strong>Contacto</strong></a>
                </li>
                <li class="nav-item">
                    <button type="button" class="btn btn-pink btn-sm" data-bs-toggle="modal" data-bs-target="#loginModal">
                        <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                    </button>
                </li>
            </ul>
        </div>
    </div>
</nav>

    <section class="hero" id="inicio">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1>Sistema de Gestión de Ventas</h1>
                    <p class="lead">Administra tu inventario, ventas y clientes de manera eficiente con nuestra plataforma integral.</p>
                    <button type="button" class="btn btn-pink btn-lg" data-bs-toggle="modal" data-bs-target="#loginModal">
                        Comenzar Ahora
                    </button>
                </div>
                <div class="col-lg-6">
                    <img src="img/fondo.jpg" alt="Dashboard Preview" class="img-fluid hero-image">
                </div>
            </div>
        </div>
    </section>

    <section id="aspectos-tecnicos" class="py-5 bg-light">
        <div class="container">
            <h2 class="mb-4"><i class="fas fa-cogs text-pink"></i> Aspectos Técnicos de la App</h2>
            <div class="row">
                <div class="col-md-6">
                    <ul class="list-group mb-4">
                        <li class="list-group-item"><strong>Lenguaje Backend:</strong> PHP 7.4+</li>
                        <li class="list-group-item"><strong>Base de Datos:</strong> MySQL</li>
                        <li class="list-group-item"><strong>Frontend:</strong> HTML5, CSS3, Bootstrap 5, JavaScript ES6, Font Awesome</li>
                        <li class="list-group-item"><strong>Framework CSS:</strong> Bootstrap 5</li>
                        <li class="list-group-item"><strong>Gestión de sesiones y autenticación:</strong> PHP Sessions, validación de roles</li>
                        <li class="list-group-item"><strong>Arquitectura:</strong> MVC personalizado (Models, Views, Controllers)</li>
                        <li class="list-group-item"><strong>Rutas y Navegación:</strong> .htaccess, index.php, sidebar unificado</li>
                        <li class="list-group-item"><strong>Recursos:</strong> Imágenes en /img, estilos en /css, scripts en /js</li>
                        <li class="list-group-item"><strong>Dependencias externas:</strong> Bootstrap CDN, Font Awesome CDN</li>
                        <li class="list-group-item"><strong>Validaciones:</strong> Formularios con validación HTML5 y JS, feedback visual con Bootstrap Toasts y Alerts</li>
                        <li class="list-group-item"><strong>Responsive:</strong> Adaptado a móviles y tablets con Bootstrap y media queries</li>
                        <li class="list-group-item"><strong>Accesibilidad:</strong> Etiquetas semánticas, navegación por teclado, buen contraste</li>
                        <li class="list-group-item"><strong>Seguridad:</strong> CSRFToken, validación de datos, control de acceso por roles</li>
                        <li class="list-group-item"><strong>Buenas prácticas:</strong> Código modular, reutilización de componentes, separación de lógica y presentación</li>
                        <li class="list-group-item"><strong>Documentación:</strong> README.md con instrucciones de instalación y uso</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Requisitos para Desarrollar y Ejecutar</h5>
                            <ul class="list-group mb-4">
                                <li class="list-group-item">PHP 7.4 o superior</li>
                                <li class="list-group-item">MySQL Server</li>
                                <li class="list-group-item">Acceso a Composer para dependencias (si se usan)</li>
                                <li class="list-group-item">Navegador moderno (Chrome, Firefox, Edge, Safari)</li>
                                <li class="list-group-item">Permisos de escritura en carpetas /logs y /uploads</li>
                            </ul>
                            
                            <h3 class="mb-3"><i class="fas fa-code text-pink"></i> Ejemplos de Uso y Endpoints Backend</h3>
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="card-title">Autenticación</h5>
                                    <pre class="bg-light p-2"><code>POST /public/auth</code></pre>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="card-title">Gestión de Usuarios</h5>
                                    <pre class="bg-light p-2"><code>GET /UsuarioController.php -> obtenerTodos()</code></pre>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="card-title">Gestión de Pedidos</h5>
                                    <pre class="bg-light p-2"><code>GET /PedidoController.php -> obtenerTodos()</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="caracteristicas" class="py-5">
        <div class="container">
            <h2 class="mb-4"><i class="fas fa-star text-pink"></i> Características Principales</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-users fa-2x text-pink mb-3"></i>
                            <h5 class="card-title">Gestión de Clientes</h5>
                            <p class="card-text">Registra, edita y consulta clientes con historial, descuentos y exportación a Excel.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-shopping-cart fa-2x text-pink mb-3"></i>
                            <h5 class="card-title">Ventas y Pedidos</h5>
                            <p class="card-text">Carrito de compras, catálogo integrado, historial y métricas de ventas.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-chart-line fa-2x text-pink mb-3"></i>
                            <h5 class="card-title">Dashboard Informativo</h5>
                            <p class="card-text">Panel con métricas clave, accesos rápidos y visualización de actividad reciente.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-user-shield fa-2x text-pink mb-3"></i>
                            <h5 class="card-title">Seguridad y Roles</h5>
                            <p class="card-text">Control de acceso por roles, validación de sesión y protección contra CSRF.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-mobile-alt fa-2x text-pink mb-3"></i>
                            <h5 class="card-title">Responsive y Accesible</h5>
                            <p class="card-text">Diseño adaptable a móviles y tablets, navegación por teclado y etiquetas semánticas.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-bell fa-2x text-pink mb-3"></i>
                            <h5 class="card-title">Feedback y Ayuda</h5>
                            <p class="card-text">Toasts, alertas, tooltips y ayuda contextual en formularios y botones.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="contacto" class="py-5 bg-light">
        <div class="container">
            <h2 class="mb-4"><i class="fas fa-envelope text-pink"></i> Contacto</h2>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <form>
                                <div class="mb-3">
                                    <label for="nombreContacto" class="form-label">Nombre</label>
                                    <input type="text" class="form-control" id="nombreContacto" required data-bs-toggle="tooltip" title="Tu nombre completo">
                                </div>
                                <div class="mb-3">
                                    <label for="emailContacto" class="form-label">Correo Electrónico</label>
                                    <input type="email" class="form-control" id="emailContacto" required data-bs-toggle="tooltip" title="Correo para contactarte">
                                </div>
                                <div class="mb-3">
                                    <label for="mensajeContacto" class="form-label">Mensaje</label>
                                    <textarea class="form-control" id="mensajeContacto" rows="4" required data-bs-toggle="tooltip" title="Describe tu consulta o solicitud"></textarea>
                                </div>
                                <button type="submit" class="btn btn-pink btn-lg w-100">Enviar Mensaje</button>
                            </form>
                            <div class="mt-4 text-center text-muted">
                                <i class="fas fa-phone-alt"></i> Soporte: +57 3222346162
                                <span class="mx-2">|</span>
                                <i class="fas fa-envelope"></i> Email: lina.oviedomm28@gmail.com
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
    © 2025 AppLink - Sistema de Gestión de Ventas. Desarrollado por Lina Oviedo.
    </footer>

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
                    <form id="loginFormModal" action="/Sistema-de-ventas-AppLink-main/public/auth" method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="nick" class="form-label">Usuario</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="nick" name="nick" 
                                        placeholder="Ingrese su usuario" required>
                            </div>
                            <div class="invalid-feedback">Por favor ingrese su usuario</div>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" 
                                        placeholder="Ingrese su contraseña" required>
                            </div>
                            <div class="invalid-feedback">Por favor ingrese su contraseña</div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-pink btn-lg">
                                Entrar <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                        
                        <div class="alert alert-danger mt-3" id="loginAlert" 
                            style="display: <?php echo $login_error ? 'block' : 'none'; ?>">
                            <?php echo $login_error; ?>
                        </div>

                        <div class="text-center mt-3">
                            <a href="reset_password.php" class="text-muted">¿Olvidaste tu contraseña?</a>
                            <br>
                            <a href="#" class="text-pink mt-2 d-inline-block" data-bs-toggle="modal" data-bs-target="#registerModal">
                                ¿Eres nuevo? Regístrate aquí
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

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
                                <label for="nick_reg" class="form-label">Usuario</label>
                                <input type="text" class="form-control" id="nick_reg" name="nick" required> 
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
                            <button type="submit" class="btn btn-pink btn-lg">
                                Registrarse <i class="fas fa-user-plus ms-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar Tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Mostrar el modal si hay un error al cargar la página desde PHP
            const loginAlert = document.getElementById('loginAlert');
            if (loginAlert && loginAlert.style.display === 'block') {
                var loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
                loginModal.show();
            }

            // Manejar el envío del formulario de login (Usando Fetch API para AJAX)
            document.getElementById('loginFormModal').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                // Validación de Bootstrap 5
                if (!this.checkValidity()) {
                    this.classList.add('was-validated');
                    return;
                }

                const formData = new FormData(this);
                loginAlert.style.display = 'none'; // Ocultar alerta previa

                try {
                    const response = await fetch(this.action, {
                        method: 'POST',
                        body: formData
                    });

                    // Asumimos que el backend de PHP responde con JSON
                    const data = await response.json(); 

                    if (data.success) {
                        // Éxito: Redirigir al usuario
                        window.location.href = data.redirect_url || '/Sistema-de-ventas-AppLink-main/public/dashboard';
                    } else {
                        // Fracaso: Mostrar mensaje de error en el modal
                        loginAlert.innerHTML = data.message || 'Error desconocido al iniciar sesión. Intente nuevamente.';
                        loginAlert.style.display = 'block';
                        this.classList.remove('was-validated'); // Quitar la validación si el error es de credenciales
                    }
                } catch (error) {
                    // Error de conexión o JSON
                    loginAlert.innerHTML = 'Error de conexión con el servidor. Por favor, revise su red.';
                    loginAlert.style.display = 'block';
                }
            });

            // Lógica del Modal de Registro
            const camposCliente = document.getElementById('campos_cliente');
            const camposEmpleado = document.getElementById('campos_empleado');
            const codigoEmpleadoInput = document.getElementById('codigo_empleado');
            const departamentoInput = document.getElementById('departamento');
            const tipoUsuarioInput = document.getElementById('tipo_usuario');
            
            // Mostrar/Ocultar campos según el tipo de usuario
            document.querySelectorAll('[name="userType"]').forEach(function(radio) {
                radio.addEventListener('change', function() {
                    tipoUsuarioInput.value = this.value;
                    if (this.value === 'cliente') {
                        camposCliente.style.display = 'flex';
                        camposEmpleado.style.display = 'none';
                        codigoEmpleadoInput.required = false;
                        departamentoInput.required = false;
                    } else {
                        camposCliente.style.display = 'none';
                        camposEmpleado.style.display = 'flex';
                        codigoEmpleadoInput.required = true;
                        departamentoInput.required = true;
                    }
                });
            });

            // Validación de Contraseñas (Registro)
            const passwordReg = document.getElementById('password_reg');
            const confirmPassword = document.getElementById('confirm_password');
            
            confirmPassword.addEventListener('input', function() {
                if (this.value !== passwordReg.value) {
                    this.setCustomValidity('Las contraseñas no coinciden');
                    this.classList.add('is-invalid');
                } else {
                    this.setCustomValidity(''); // Campo válido
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                }
            });

            passwordReg.addEventListener('input', function() {
                 // Revisa si el campo de confirmación ya tiene texto para revalidarlo
                if (confirmPassword.value.length > 0) {
                    confirmPassword.reportValidity(); 
                }
            });
        });
    </script>
</body>
</html>
