<?php
require_once __DIR__ . '/../Models/Usuario.php';
require_once __DIR__ . '/../Utils/Database.php';
require_once __DIR__ . '/../Utils/CSRFToken.php';

class AuthController {
    public static function login($usuario, $password) {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM usuarios WHERE usuario = ?');
        $stmt->execute([$usuario]);
        $row = $stmt->fetch();
        if ($row && password_verify($password, $row['password'])) {
            // Iniciar sesión
            $_SESSION['authenticated'] = true;
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['nombre'];
            $_SESSION['is_admin'] = $row['rol'] === 'admin';
            return true;
        }
        return false;
    }
    public static function logout() {
        session_destroy();
    }
}

