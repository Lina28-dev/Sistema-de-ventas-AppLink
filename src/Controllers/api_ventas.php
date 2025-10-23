<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/VentaController.php';

try {
    $resultado = VentaController::manejarSolicitud();
    echo json_encode($resultado);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
}
?>