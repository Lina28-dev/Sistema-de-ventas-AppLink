<?php
// Ejemplo de endpoint para AJAX/autocompletado
require_once __DIR__ . '/../Models/Producto.php';
require_once __DIR__ . '/../Utils/Database.php';

class ApiController {
    public static function buscarProductos($termino) {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM productos WHERE nombre LIKE ?');
        $stmt->execute(['%' . $termino . '%']);
        $result = $stmt->fetchAll();
        $productos = [];
        foreach ($result as $row) {
            $productos[] = [
                'id' => $row['id'],
                'nombre' => $row['nombre'],
                'precio' => $row['precio'],
                'imagen' => $row['imagen']
            ];
        }
        return json_encode($productos);
    }
}

