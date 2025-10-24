<?php
/**
 * Test de creación de cliente usando el controlador real
 */

// Simular una petición POST con datos JSON
$_SERVER['REQUEST_METHOD'] = 'POST';
$_GET['accion'] = 'crear';

// Simular autenticación
session_start();
$_SESSION['authenticated'] = true;

// Simular datos del cliente
$clienteData = [
    'nombres' => 'Juan Carlos',
    'apellidos' => 'Pérez García',
    'identificacion' => '12345678',
    'tipo_identificacion' => 'CC',
    'telefono' => '3001234567',
    'email' => 'juan.perez@email.com',
    'direccion' => 'Calle 123 #45-67',
    'ciudad' => 'Bogotá',
    'descuento' => 5.0
];

// Simular el body JSON
$jsonData = json_encode($clienteData);
file_put_contents('php://input', $jsonData);

// Capturar la salida
ob_start();

try {
    // Incluir el controlador
    include __DIR__ . '/ClienteControllerAPI.php';
    
    $output = ob_get_clean();
    echo "Salida del controlador:\n";
    echo $output;
    
} catch (Exception $e) {
    ob_end_clean();
    echo "Error al ejecutar el controlador: " . $e->getMessage();
}
?>