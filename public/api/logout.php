<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Iniciar sesión
session_start();

// Lógica de logout
try {
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

    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => 'Sesión cerrada correctamente',
        'redirect' => '/Sistema-de-ventas-AppLink-main/public/'
    ]);

} catch (Exception $e) {
    // En caso de error, aún así responder exitosamente
    echo json_encode([
        'success' => true,
        'message' => 'Sesión cerrada',
        'redirect' => '/Sistema-de-ventas-AppLink-main/public/'
    ]);
}
exit();
?>