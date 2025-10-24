<?php
/**
 * Test de conexión y creación de cliente
 */

// Iniciar sesión si no está activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Simular autenticación para test
$_SESSION['authenticated'] = true;

header('Content-Type: application/json');

// Obtener configuración de base de datos
$config = require __DIR__ . '/../../config/app.php';

try {
    echo "Intentando conectar a la base de datos...\n";
    
    $pdo = new PDO(
        "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset={$config['db']['charset']}",
        $config['db']['user'],
        $config['db']['pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    echo "✓ Conexión exitosa a la base de datos\n";
    
    // Verificar estructura de tabla
    echo "Verificando estructura de tabla fs_clientes...\n";
    
    $columns = $pdo->query("SHOW COLUMNS FROM fs_clientes")->fetchAll(PDO::FETCH_COLUMN);
    echo "Columnas encontradas: " . implode(', ', $columns) . "\n";
    
    $hasNewStructure = in_array('identificacion', $columns);
    $hasOldStructure = in_array('CC', $columns);
    
    echo "Estructura nueva (identificacion): " . ($hasNewStructure ? 'SÍ' : 'NO') . "\n";
    echo "Estructura antigua (CC): " . ($hasOldStructure ? 'SÍ' : 'NO') . "\n";
    
    // Test de inserción
    echo "Probando inserción de cliente de prueba...\n";
    
    $clienteTest = [
        'nombres' => 'Test',
        'apellidos' => 'Usuario',
        'identificacion' => 'TEST_' . time(),
        'telefono' => '3000000000',
        'email' => 'test@test.com',
        'ciudad' => 'Bogotá'
    ];
    
    if ($hasNewStructure) {
        $sql = "
            INSERT INTO fs_clientes (
                nombres, apellidos, identificacion, telefono, email, ciudad
            ) VALUES (?, ?, ?, ?, ?, ?)
        ";
        
        $stmt = $pdo->prepare($sql);
        $resultado = $stmt->execute([
            $clienteTest['nombres'],
            $clienteTest['apellidos'],
            $clienteTest['identificacion'],
            $clienteTest['telefono'],
            $clienteTest['email'],
            $clienteTest['ciudad']
        ]);
    } else {
        $sql = "
            INSERT INTO fs_clientes (
                nombres, apellidos, nombre_completo, CC, telefono, email, ciudad
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ";
        
        $stmt = $pdo->prepare($sql);
        $resultado = $stmt->execute([
            $clienteTest['nombres'],
            $clienteTest['apellidos'],
            $clienteTest['nombres'] . ' ' . $clienteTest['apellidos'],
            $clienteTest['identificacion'],
            $clienteTest['telefono'],
            $clienteTest['email'],
            $clienteTest['ciudad']
        ]);
    }
    
    if ($resultado) {
        $nuevoId = $pdo->lastInsertId();
        echo "✓ Cliente de prueba creado exitosamente con ID: $nuevoId\n";
        
        // Limpiar - eliminar cliente de prueba
        $deleteSql = $hasNewStructure 
            ? "DELETE FROM fs_clientes WHERE identificacion = ?"
            : "DELETE FROM fs_clientes WHERE CC = ?";
        $deleteStmt = $pdo->prepare($deleteSql);
        $deleteStmt->execute([$clienteTest['identificacion']]);
        echo "✓ Cliente de prueba eliminado\n";
        
        echo json_encode([
            'success' => true,
            'message' => 'Todas las pruebas pasaron correctamente',
            'database_structure' => $hasNewStructure ? 'nueva' : 'antigua',
            'test_id' => $nuevoId
        ]);
    } else {
        throw new Exception('Error al insertar cliente de prueba');
    }
    
} catch (PDOException $e) {
    echo "✗ Error de PDO: " . $e->getMessage() . "\n";
    echo json_encode([
        'success' => false,
        'error' => 'Error de base de datos: ' . $e->getMessage(),
        'code' => $e->getCode()
    ]);
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>