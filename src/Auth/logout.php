<?php
session_start();

// Registrar logout en auditoría antes de destruir la sesión
if (isset($_SESSION['user_id']) && isset($_SESSION['user_name'])) {
    try {
        require_once __DIR__ . '/../Utils/AuditoriaLogger.php';
        $pdo = new PDO("mysql:host=localhost;dbname=fs_clientes;charset=utf8mb4", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $auditoria = new AuditoriaLogger($pdo);
        
        // Calcular duración de sesión
        $duracion_sesion = null;
        if (isset($_SESSION['login_time'])) {
            $duracion_sesion = time() - $_SESSION['login_time'];
        }
        
        // Registrar logout
        $auditoria->registrarLogout(
            $_SESSION['user_id'], 
            $_SESSION['user_name'], 
            $duracion_sesion
        );
        
    } catch (Exception $e) {
        // Si hay error con auditoría, no interrumpir el logout
        error_log("Error registrando logout: " . $e->getMessage());
    }
}

// Destruir todas las variables de sesión
$_SESSION = array();

// Destruir la sesión
session_destroy();

// Redirigir a la página de inicio
header('Location: ../../public/');
exit;
?>
