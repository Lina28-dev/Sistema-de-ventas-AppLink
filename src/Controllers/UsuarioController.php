<?php
require_once __DIR__ . '/../Models/Usuario.php';
require_once __DIR__ . '/../Utils/Database.php';

class UsuarioController {
    public static function obtenerTodos() {
        $db = Database::getConnection();
        $stmt = $db->query('SELECT * FROM usuarios');
        $usuarios = [];
        while ($row = $stmt->fetch()) {
            $usuarios[] = new Usuario($row['id'], $row['nombre'], $row['apellido'], $row['usuario'], $row['email'], $row['rol'], $row['password']);
        }
        return $usuarios;
    }
    // Métodos CRUD adicionales aquí
}
