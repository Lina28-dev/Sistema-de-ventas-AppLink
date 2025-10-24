<?php
// Test simple de API con PostgreSQL
header('Content-Type: application/json');

try {
    require_once '../config/Database.php';
    
    $pdo = new PDO(
        App\Config\Database::getDSN(),
        App\Config\Database::getUsername(),
        App\Config\Database::getPassword(),
        App\Config\Database::getOptions()
    );
    
    // Probar consulta simple
    $stmt = $pdo->query("SELECT COUNT(*) as total_usuarios FROM usuarios");
    $result = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'message' => 'Conexión PostgreSQL exitosa desde web',
        'data' => [
            'usuarios_total' => $result['total_usuarios'],
            'servidor' => $_SERVER['SERVER_SOFTWARE'],
            'php_version' => PHP_VERSION
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>