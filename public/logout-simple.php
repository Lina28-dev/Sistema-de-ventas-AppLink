<?php
session_start();
$_SESSION = array();
session_destroy();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sesión Cerrada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .logout-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            max-width: 400px;
        }
        .check-animation {
            font-size: 3rem;
            color: #28a745;
            animation: checkmark 0.6s ease-in-out;
        }
        @keyframes checkmark {
            0% { transform: scale(0); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="logout-card">
        <div class="mb-3">
            <i class="fas fa-check-circle check-animation"></i>
        </div>
        <h4 class="mb-3">Sesión Cerrada</h4>
        <p class="text-muted mb-4">Su sesión ha sido cerrada exitosamente</p>
        <div class="mb-3">
            <div class="spinner-border spinner-border-sm text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <span class="ms-2">Redirigiendo...</span>
        </div>
        <a href="/Sistema-de-ventas-AppLink-main/public/" class="btn btn-primary">
            <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
        </a>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script>
        // Redireccionar después de 3 segundos
        setTimeout(() => {
            window.location.href = '/Sistema-de-ventas-AppLink-main/public/';
        }, 3000);
    </script>
</body>
</html>