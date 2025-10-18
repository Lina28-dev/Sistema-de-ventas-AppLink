<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual de Usuario | AppLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1976d2;
            --primary-dark: #115293;
            --primary-light: #e3f2fd;
            --secondary-color: #fff;
            --text-dark: #333;
            --text-light: #666;
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.07);
            --shadow-md: 0 4px 24px rgba(0,0,0,0.08);
            --shadow-lg: 0 8px 32px rgba(25,118,210,0.15);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        body, html, .container, .navbar, .card, .form-control, .btn, h1, h2, h3, h4, h5, h6, p, ul, li, label, input, textarea, select {
            font-family: 'Poppins', Arial, sans-serif !important;
        }
        .navbar {
            background: #fff;
            box-shadow: var(--shadow-sm);
        }
        .navbar-brand img {
            margin-right: 8px;
        }
        .hero {
            background: linear-gradient(120deg, #e3f2fd 0%, #fff 100%);
            padding: 80px 0 40px 0;
        }
        .hero-content {
            position: relative;
            z-index: 2;
        }
        .hero h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 1.5rem;
        }
        .hero .lead {
            font-size: 1.1rem;
            color: var(--text-light);
            margin-bottom: 2rem;
        }
        .card {
            border-radius: 1rem;
            box-shadow: var(--shadow-sm);
        }
        .section-title {
            font-weight: 600;
            color: var(--primary-color);
        }
        .index-list a {
            text-decoration: none;
            color: var(--primary-color);
            font-weight: 500;
        }
        .index-list a:hover {
            text-decoration: underline;
        }
        .screenshot {
            border-radius: 0.75rem;
            box-shadow: var(--shadow-md);
            max-width: 100%;
            margin-bottom: 1rem;
        }
        .faq .accordion-button:not(.collapsed) {
            color: var(--primary-color);
            background: var(--primary-light);
        }
        .btn-applink {
            background: var(--primary-color);
            color: #fff;
            border: none;
        }
        .btn-applink:hover {
            background: var(--primary-dark);
        }
        .footer {
            background: linear-gradient(135deg, #f8f9fa 0%, white 100%);
            color: var(--text-dark);
            padding: 2rem 0 1rem 0;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.05);
            position: relative;
        }
        .footer-links a {
            color: var(--text-light);
            text-decoration: none;
            transition: var(--transition);
            margin: 0 1rem;
        }
        .footer-links a:hover {
            color: var(--primary-color);
        }
        .social-icons a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: var(--primary-light);
            color: var(--primary-color);
            margin: 0 0.5rem;
            transition: var(--transition);
        }
        .social-icons a:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-3px);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light mb-4">
        <div class="container">
            <a class="navbar-brand" href="home.php">
                <img src="img/logo.jpg" alt="AppLink Logo" height="40"> Manual de Usuario
            </a>
        </div>
    </nav>

    <!-- Título y bienvenida -->
    <div class="container mb-4">
        <div class="text-center">
            <h1 class="section-title mb-3">Manual de Usuario</h1>
            <p class="lead">Guía paso a paso para aprovechar todas las funciones del sistema de gestión de ventas AppLink.</p>
        </div>
    </div>

    <!-- Índice de contenidos -->
    <div class="container mb-5">
        <div class="card p-4">
            <h4 class="mb-3">Índice</h4>
            <ul class="index-list">
                <li><a href="#registro">Registro de usuario</a></li>
                <li><a href="#login">Inicio de sesión</a></li>
                <li><a href="#dashboard">Dashboard y navegación</a></li>
                <li><a href="#clientes">Gestión de clientes</a></li>
                <li><a href="#ventas">Gestión de ventas y pedidos</a></li>
                <li><a href="#reportes">Reportes y métricas</a></li>
                <li><a href="#faq">Preguntas frecuentes</a></li>
                <li><a href="#soporte">Soporte y contacto</a></li>
            </ul>
        </div>
    </div>

    <!-- Guía paso a paso -->
    <div class="container mb-5">
        <div id="registro" class="mb-5">
            <h3 class="section-title mb-3"><i class="fas fa-user-plus me-2"></i>Registro de usuario</h3>
            <p>Para crear una cuenta, haz clic en "Crear una cuenta" en la página principal y completa el formulario con tus datos.</p>
            <img src="img/manual/registro.png" alt="Pantalla de registro" class="screenshot">
        </div>
        <div id="login" class="mb-5">
            <h3 class="section-title mb-3"><i class="fas fa-sign-in-alt me-2"></i>Inicio de sesión</h3>
            <p>Ingresa tu usuario y contraseña en el formulario de acceso. Si olvidaste tu contraseña, puedes recuperarla desde el enlace correspondiente.</p>
            <img src="img/manual/login.png" alt="Pantalla de login" class="screenshot">
        </div>
        <div id="dashboard" class="mb-5">
            <h3 class="section-title mb-3"><i class="fas fa-tachometer-alt me-2"></i>Dashboard y navegación</h3>
            <p>El dashboard muestra métricas clave y accesos rápidos a las funciones principales. Usa el menú lateral para navegar entre módulos.</p>
            <img src="img/manual/dashboard.png" alt="Dashboard" class="screenshot">
        </div>
        <div id="clientes" class="mb-5">
            <h3 class="section-title mb-3"><i class="fas fa-users me-2"></i>Gestión de clientes</h3>
            <p>Registra, edita y consulta clientes desde el módulo correspondiente. Puedes ver el historial de compras y aplicar descuentos.</p>
            <img src="img/manual/clientes.png" alt="Gestión de clientes" class="screenshot">
        </div>
        <div id="ventas" class="mb-5">
            <h3 class="section-title mb-3"><i class="fas fa-shopping-cart me-2"></i>Gestión de ventas y pedidos</h3>
            <p>Agrega productos al carrito, genera pedidos y realiza ventas de forma rápida. El sistema permite múltiples métodos de pago y facturación automática.</p>
            <img src="img/manual/ventas.png" alt="Gestión de ventas" class="screenshot">
        </div>
        <div id="reportes" class="mb-5">
            <h3 class="section-title mb-3"><i class="fas fa-chart-line me-2"></i>Reportes y métricas</h3>
            <p>Accede a reportes personalizados y métricas en tiempo real para analizar el desempeño de tu negocio.</p>
            <img src="img/manual/reportes.png" alt="Reportes y métricas" class="screenshot">
        </div>
    </div>

    <!-- FAQ -->
    <div class="container mb-5" id="faq">
        <div class="card p-4 faq">
            <h4 class="mb-3">Preguntas frecuentes</h4>
            <div class="accordion" id="faqAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="faq1">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1" aria-expanded="true" aria-controls="collapse1">
                            ¿Cómo recupero mi contraseña?
                        </button>
                    </h2>
                    <div id="collapse1" class="accordion-collapse collapse show" aria-labelledby="faq1" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Haz clic en "¿Olvidaste tu contraseña?" en la pantalla de inicio de sesión y sigue los pasos para restablecerla.
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="faq2">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2" aria-expanded="false" aria-controls="collapse2">
                            ¿Cómo exporto los datos a Excel?
                        </button>
                    </h2>
                    <div id="collapse2" class="accordion-collapse collapse" aria-labelledby="faq2" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            En el módulo de clientes y ventas encontrarás la opción "Exportar a Excel" para descargar los datos en formato XLSX.
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="faq3">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3" aria-expanded="false" aria-controls="collapse3">
                            ¿Puedo acceder desde mi móvil?
                        </button>
                    </h2>
                    <div id="collapse3" class="accordion-collapse collapse" aria-labelledby="faq3" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Sí, la aplicación es 100% responsive y funciona perfectamente en smartphones y tablets.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Soporte y contacto -->
    <div class="container mb-5" id="soporte">
        <div class="card p-4">
            <h4 class="mb-3">Soporte y contacto</h4>
            <p>¿Tienes dudas o necesitas ayuda? Escríbenos a <a href="mailto:lina.oviedomm28@gmail.com">lina.oviedomm28@gmail.com</a> o llama al <strong>+57 3222346162</strong>. También puedes descargar el <a href="#" class="btn btn-applink btn-sm ms-2">Manual PDF</a>.</p>
        </div>
    </div>

    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

