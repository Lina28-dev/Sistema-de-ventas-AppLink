<?php
// Logout mejorado - versión Auth
// Iniciar sesión si no está activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si hay una sesión activa
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    // Si no hay sesión, redirigir directamente a inicio
    header('Location: /Sistema-de-ventas-AppLink-main/public/');
    exit();
}

// Log de cierre de sesión
if (isset($_SESSION['user_name'])) {
    error_log("Usuario '{$_SESSION['user_name']}' cerró sesión desde Auth en " . date('Y-m-d H:i:s'));
}

// Destruir todas las variables de sesión
$_SESSION = array();

// Destruir la cookie de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir la sesión
session_destroy();

// Crear nueva sesión para mostrar mensaje
session_start();
$_SESSION['logout_success'] = 'Has cerrado sesión exitosamente.';

// Redirigir a la página de inicio (home.php)
header('Location: /Sistema-de-ventas-AppLink-main/public/');
exit();
?>
