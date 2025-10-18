<?php
require_once __DIR__ . '/../Models/Producto.php';
require_once __DIR__ . '/../Utils/Database.php';

class ProductoController {
    public static function obtenerTodos() {
        $db = Database::getConnection();
        $stmt = $db->query('SELECT * FROM productos');
        $productos = [];
        while ($row = $stmt->fetch()) {
            $productos[] = new Producto($row['id'], $row['nombre'], $row['descripcion'], $row['precio'], $row['stock'], $row['imagen']);
        }
        return $productos;
    }
    // Métodos CRUD adicionales aquí
}

