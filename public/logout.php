<?php
// Logout simplificado y directo
session_start();

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

// Redirección absoluta
header('Location: http://localhost/Sistema-de-ventas-AppLink-main/public/');
exit();
?>