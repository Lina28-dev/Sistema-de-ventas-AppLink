<?php
// 1. Manejo de Sesión
// Inicializar sesión si no está activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. Redirección de Autenticación (¡Corregido Bucle!)
// Si el usuario ya está autenticado, redirigir al dashboard
if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
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
    <link rel="stylesheet" href="assets/css/theme-styles.css">
    <meta name="theme-color" content="#ffffff">
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

        /* Estilos para el select de roles */
        .form-select {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23e91e63' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m1 6 7 7 7-7'/%3e%3c/svg%3e");
        }

        .form-select:focus {
            border-color: #e91e63;
            box-shadow: 0 0 0 0.2rem rgba(233, 30, 99, 0.25);
        }

        .form-select option {
            padding: 10px;
            color: #495057;
        }

        .btn-pink {
            background: linear-gradient(135deg, #e91e63, #ff4081);
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-pink:hover {
            background: linear-gradient(135deg, #c2185b, #e91e63);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(233, 30, 99, 0.4);
            color: white;
        }

        .input-group-text {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border: 2px solid #e9ecef;
            color: #e91e63;
            font-weight: 500;
        }

        .form-control:focus ~ .input-group-text,
        .form-select:focus ~ .input-group-text {
            border-color: #e91e63;
            background: rgba(233, 30, 99, 0.1);
        }

        /* Animación para el modal */
        .modal.fade .modal-dialog {
            transition: transform 0.3s ease-out;
        }

        .modal.show .modal-dialog {
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                transform: translate(0, -50px) scale(0.95);
                opacity: 0;
            }
            to {
                transform: translate(0, 0) scale(1);
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
    <div class="container">
        <a class="navbar-brand" href="#">
            <img src="/Sistema-de-ventas-AppLink-main/public/assets/images/logo.jpg" alt="AppLink Logo" height="40">
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
                    <img src="/Sistema-de-ventas-AppLink-main/public/assets/images/fondo.jpg" alt="Dashboard Preview" class="img-fluid hero-image">
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
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 bg-gradient" style="background: linear-gradient(135deg, #e91e63, #ff4081);">
                    <h5 class="modal-title text-white fw-bold" id="loginModalLabel">
                        <i class="fas fa-user-circle me-2"></i>Sistema AppLink
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <img src="assets/images/logo.jpg" alt="AppLink Logo" class="modal-logo rounded-circle" style="max-width: 120px; height: 120px; object-fit: cover; border: 3px solid #e91e63; box-shadow: 0 4px 15px rgba(233, 30, 99, 0.3);">
                        <h2 class="mt-3 fw-bold text-primary">Iniciar Sesión</h2>
                        <p class="text-muted">Accede a tu cuenta de AppLink</p>
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

                        <div class="mb-4">
                            <label for="rol" class="form-label">Tipo de Usuario</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                                <select class="form-select" id="rol" name="rol" required>
                                    <option value="">Seleccionar rol...</option>
                                    <option value="cliente">👤 Cliente - Gestionar mis pedidos</option>
                                    <option value="empleado">👔 Empleado - Pedidos y ventas</option>
                                    <option value="administrador">👑 Administrador - Acceso completo</option>
                                </select>
                            </div>
                            <div class="invalid-feedback">Por favor seleccione su tipo de usuario</div>
                            <div class="form-text">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Selecciona el tipo de cuenta con la que deseas acceder
                                </small>
                            </div>
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
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 bg-gradient" style="background: linear-gradient(135deg, #00bcd4, #e91e63);">
                    <h5 class="modal-title text-white fw-bold" id="registerModalLabel">
                        <i class="fas fa-user-plus me-2"></i>Registro de Usuario
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Logo del sistema -->
                    <div class="text-center mb-4">
                        <img src="assets/images/logo.jpg" alt="AppLink Logo" class="modal-logo rounded-circle" style="max-width: 80px; height: 80px; object-fit: cover; border: 2px solid #00bcd4; box-shadow: 0 4px 15px rgba(0, 188, 212, 0.3);">
                        <h4 class="mt-3 fw-bold text-primary">Únete a AppLink</h4>
                        <p class="text-muted">Crea tu cuenta y comienza a gestionar tus ventas</p>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-12 text-center">
                            <h5>¿Cómo deseas registrarte?</h5>
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

                    <form id="registerForm" action="register_process.php" method="POST" class="needs-validation" novalidate>
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

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="cc_reg" class="form-label">Cédula de Ciudadanía (CC)</label>
                                <input type="text" class="form-control" id="cc_reg" name="cc" required pattern="[0-9]{7,11}" maxlength="20"
                                       title="Debe contener entre 7 y 11 dígitos numéricos">
                                <div class="invalid-feedback">La CC debe contener entre 7 y 11 dígitos numéricos</div>
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
    <script src="assets/js/login-handler.js"></script>
    
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

            // Manejar el envío del formulario de login
            // COMENTADO: Se usa archivo externo login-handler.js
            /*document.getElementById('loginFormModal').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                // Validación personalizada del rol
                const rolSelect = document.getElementById('rol');
                const nick = document.getElementById('nick').value.trim();
                const password = document.getElementById('password').value.trim();
                
                // Validar campos requeridos
                if (!nick || !password || !rolSelect.value) {
                    this.classList.add('was-validated');
                    if (!rolSelect.value) {
                        rolSelect.setCustomValidity('Debe seleccionar un tipo de usuario');
                    }
                    return;
                } else {
                    rolSelect.setCustomValidity('');
                }
                
                // Validación de Bootstrap 5
                if (!this.checkValidity()) {
                    this.classList.add('was-validated');
                    return;
                }

                const formData = new FormData(this);
                loginAlert.style.display = 'none'; // Ocultar alerta previa
                
                // Mostrar indicador de carga
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Verificando...';
                submitBtn.disabled = true;

                try {
                    const response = await fetch(this.action, {
                        method: 'POST',
                        body: formData
                    });

                    // Asumimos que el backend de PHP responde con JSON
                    const data = await response.json(); 

                    if (data.success) {
                        // Mostrar mensaje de éxito con rol
                        const roleName = data.user?.rol ? data.user.rol.charAt(0).toUpperCase() + data.user.rol.slice(1) : 'Usuario';
                        loginAlert.className = 'alert alert-success';
                        loginAlert.innerHTML = `<i class="fas fa-check-circle me-2"></i>¡Bienvenido ${data.user?.nombre || ''}! Ingresando como ${roleName}...`;
                        loginAlert.style.display = 'block';
                        
                        // Redirigir inmediatamente
                        window.location.href = data.redirect || '/Sistema-de-ventas-AppLink-main/public/dashboard';
                    } else {
                        // Fracaso: Mostrar mensaje de error en el modal
                        loginAlert.className = 'alert alert-danger';
                        loginAlert.innerHTML = `<i class="fas fa-exclamation-circle me-2"></i>${data.message || 'Error desconocido al iniciar sesión. Intente nuevamente.'}`;
                        loginAlert.style.display = 'block';
                        this.classList.remove('was-validated'); // Quitar la validación si el error es de credenciales
                    }
                } catch (error) {
                    // Error de conexión o JSON
                    loginAlert.className = 'alert alert-danger';
                    loginAlert.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Error de conexión con el servidor. Por favor, revise su red.';
                    loginAlert.style.display = 'block';
                } finally {
                    // Restaurar botón en todos los casos
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            });

            // Validación en tiempo real del campo de rol
            document.getElementById('rol').addEventListener('change', function() {
                if (this.value) {
                    this.setCustomValidity('');
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.setCustomValidity('Debe seleccionar un tipo de usuario');
                }
            });
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

            // Validación de la CC (solo números)
            const ccInput = document.getElementById('cc_reg');
            ccInput.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
                
                if (this.value.length >= 7 && this.value.length <= 11) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                    this.classList.add('is-invalid');
                }
            });

            // Validación del formulario de registro
            document.getElementById('registerForm').addEventListener('submit', function(e) {
                e.preventDefault(); // Prevenir envío normal del formulario
                
                const cc = document.getElementById('cc_reg').value;
                
                // Validar formato de la CC
                if (!/^\d{7,11}$/.test(cc)) {
                    alert('La CC debe contener entre 7 y 11 dígitos numéricos');
                    return;
                }
                
                // Validar que las contraseñas coincidan
                if (passwordReg.value !== confirmPassword.value) {
                    alert('Las contraseñas no coinciden');
                    return;
                }
                
                // Enviar formulario via AJAX
                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                
                // Deshabilitar botón durante el envío
                submitBtn.disabled = true;
                submitBtn.textContent = 'Registrando...';
                
                fetch('register_process.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ ' + data.message);
                        // Cerrar modal y limpiar formulario
                        document.getElementById('registerModal').querySelector('.btn-close').click();
                        this.reset();
                    } else {
                        alert('❌ ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('❌ Error de conexión. Por favor, inténtalo nuevamente.');
                })
                .finally(() => {
                    // Rehabilitar botón
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Registrarse';
                });
            });
        });
    </script>
    
    <!-- Theme Switcher Inline -->
    <script>
        // Theme Switcher Implementation
        class ThemeSwitcher {
            constructor() {
                this.currentTheme = localStorage.getItem('theme') || 'light';
                this.init();
            }

            init() {
                this.applyTheme(this.currentTheme);
                this.createToggleButton();
                this.bindEvents();
            }

            createToggleButton() {
                const themeToggle = document.createElement('div');
                themeToggle.className = 'theme-toggle';
                themeToggle.innerHTML = `
                    <button class="theme-btn" id="themeToggle" title="Cambiar tema">
                        <i class="fas fa-sun theme-icon light-icon"></i>
                        <i class="fas fa-moon theme-icon dark-icon"></i>
                    </button>
                `;

                const styles = document.createElement('style');
                styles.textContent = `
                    .theme-toggle {
                        position: fixed;
                        top: 20px;
                        right: 20px;
                        z-index: 1050;
                    }

                    .theme-btn {
                        background: var(--theme-toggle-bg, #fff);
                        border: 2px solid #e91e63;
                        border-radius: 50%;
                        width: 50px;
                        height: 50px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        cursor: pointer;
                        transition: all 0.3s ease;
                        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                        position: relative;
                        overflow: hidden;
                    }

                    .theme-btn:hover {
                        transform: scale(1.1);
                        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
                    }

                    .theme-icon {
                        font-size: 1.2rem;
                        transition: all 0.3s ease;
                        position: absolute;
                    }

                    .light-icon {
                        color: #ffd700;
                        opacity: 1;
                        transform: rotate(0deg);
                    }

                    .dark-icon {
                        color: #4a90e2;
                        opacity: 0;
                        transform: rotate(180deg);
                    }

                    [data-theme="dark"] .theme-btn {
                        background: #2d3748;
                        border-color: #4a90e2;
                    }

                    [data-theme="dark"] .light-icon {
                        opacity: 0;
                        transform: rotate(180deg);
                    }

                    [data-theme="dark"] .dark-icon {
                        opacity: 1;
                        transform: rotate(0deg);
                    }

                    @media (max-width: 768px) {
                        .theme-toggle {
                            top: 10px;
                            right: 10px;
                        }
                        .theme-btn {
                            width: 45px;
                            height: 45px;
                        }
                    }
                `;

                document.head.appendChild(styles);
                document.body.appendChild(themeToggle);
            }

            bindEvents() {
                document.addEventListener('click', (e) => {
                    if (e.target.closest('#themeToggle')) {
                        this.toggleTheme();
                    }
                });
            }

            toggleTheme() {
                this.currentTheme = this.currentTheme === 'light' ? 'dark' : 'light';
                this.applyTheme(this.currentTheme);
                localStorage.setItem('theme', this.currentTheme);
                
                document.body.style.transition = 'all 0.3s ease';
                setTimeout(() => {
                    document.body.style.transition = '';
                }, 300);
            }

            applyTheme(theme) {
                document.documentElement.setAttribute('data-theme', theme);
                this.currentTheme = theme;
                
                let metaThemeColor = document.querySelector('meta[name="theme-color"]');
                if (!metaThemeColor) {
                    metaThemeColor = document.createElement('meta');
                    metaThemeColor.name = 'theme-color';
                    document.head.appendChild(metaThemeColor);
                }
                metaThemeColor.content = theme === 'dark' ? '#2d3748' : '#ffffff';
            }
        }

        // Inicializar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', () => {
            new ThemeSwitcher();
        });
    </script>
</body>
</html>
