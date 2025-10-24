<?php
// Logout mejorado con mejor UX
session_start();

// Verificar si hay una sesión activa
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    // Si no hay sesión, redirigir directamente a inicio
    header('Location: /Sistema-de-ventas-AppLink-main/public/');
    exit();
}

// Log del usuario que cierra sesión
if (isset($_SESSION['user_name'])) {
    error_log("Usuario '{$_SESSION['user_name']}' cerró sesión en " . date('Y-m-d H:i:s'));
}

// Limpiar todas las variables de sesión
$_SESSION = array();

// Eliminar la cookie de sesión si existe
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir la sesión
session_destroy();

// Mensaje de sesión cerrada exitosamente
session_start();
$_SESSION['logout_success'] = 'Has cerrado sesión exitosamente.';

// Redirección a la página de inicio con mensaje
header('Location: /Sistema-de-ventas-AppLink-main/public/');
exit();
?>