<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Ventas AppLink</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/base.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="indexx.php">
                <img src="img/logo.jpg" alt="Logo" height="40" class="d-inline-block align-text-top">
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <span class="nav-link" id="infoPagina">Sistema de Ventas AppLink</span>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center">
                    <?php if (isset($_SESSION['usuario_nombre'])): ?>
                        <span class="me-3">
                            <i class="fas fa-user"></i> 
                            <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>
                        </span>
                        <a href="Login/logout.php" class="btn btn-outline-danger">
                            <i class="fas fa-sign-out-alt"></i> Cerrar sesión
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="container mt-3">
        <div id="infoGeneralText" class="alert alert-info" style="display: none;"></div>
    </div>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>