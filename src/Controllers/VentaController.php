<?php
require_once __DIR__ . '/../Models/Venta.php';
require_once __DIR__ . '/../Utils/Database.php';

class VentaController {
    public static function obtenerTodas() {
        $db = Database::getConnection();
        $stmt = $db->query('SELECT * FROM ventas');
        $ventas = [];
        while ($row = $stmt->fetch()) {
            $ventas[] = new Venta($row['id'], $row['cliente_id'], $row['fecha'], $row['total']);
        }
        return $ventas;
    }
    // Métodos CRUD adicionales aquí
}

