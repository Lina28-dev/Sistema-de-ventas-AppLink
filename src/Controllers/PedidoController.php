<?php
require_once __DIR__ . '/../Models/Pedido.php';
require_once __DIR__ . '/../Utils/Database.php';

class PedidoController {
    public static function obtenerTodos() {
        $db = Database::getConnection();
        $stmt = $db->query('SELECT * FROM pedidos');
        $pedidos = [];
        while ($row = $stmt->fetch()) {
            $pedidos[] = new Pedido($row['id'], $row['cliente_id'], $row['fecha'], $row['estado'], $row['total']);
        }
        return $pedidos;
    }
    // Métodos CRUD adicionales aquí
}

