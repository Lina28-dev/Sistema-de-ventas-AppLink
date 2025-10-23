<?php
// Logout de emergencia - funciona desde cualquier ubicación
session_start();
$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// JavaScript para limpiar y redireccionar
echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cerrando sesión...</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .logout-container {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            max-width: 400px;
        }
        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .success-icon {
            color: #28a745;
            font-size: 3rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="success-icon">✓</div>
        <h2>Sesión Cerrada</h2>
        <p>Su sesión ha sido cerrada correctamente</p>
        <div class="spinner"></div>
        <p>Redirigiendo a la página principal...</p>
    </div>
    
    <script>
        // Limpiar storage del navegador
        if (typeof(Storage) !== "undefined") {
            localStorage.clear();
            sessionStorage.clear();
        }
        
        // Redireccionar después de 2 segundos
        setTimeout(function() {
            window.location.replace("/Sistema-de-ventas-AppLink-main/public/");
        }, 2000);
    </script>
</body>
</html>';
exit();
?>