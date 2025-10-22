<?php
echo "=== VERIFICACIÓN COMPLETA DE POSTGRESQL ===\n\n";

// 1. Verificar extensiones PHP
echo "1. EXTENSIONES PHP:\n";
echo "- PDO: " . (extension_loaded('pdo') ? "✅ Instalada" : "❌ No instalada") . "\n";
echo "- PDO PostgreSQL: " . (extension_loaded('pdo_pgsql') ? "✅ Instalada" : "❌ No instalada") . "\n";
echo "- PostgreSQL: " . (extension_loaded('pgsql') ? "✅ Instalada" : "❌ No instalada") . "\n\n";

// 2. Verificar conexión PostgreSQL
echo "2. CONEXIÓN A POSTGRESQL:\n";
try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=ventas_applink', 'applink_user', 'applink_2024!');
    echo "✅ Conexión exitosa a PostgreSQL\n";
    
    // 3. Verificar versión
    $version = $pdo->query('SELECT version()')->fetchColumn();
    echo "- Versión: " . $version . "\n\n";
    
    // 4. Verificar base de datos
    echo "3. BASE DE DATOS 'ventas_applink':\n";
    $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) > 0) {
        echo "✅ Base de datos encontrada con " . count($tables) . " tablas:\n";
        foreach ($tables as $table) {
            echo "  - $table\n";
        }
        echo "\n";
    } else {
        echo "❌ No se encontraron tablas\n\n";
    }
    
    // 5. Verificar datos migrados
    echo "4. DATOS MIGRADOS:\n";
    
    // Usuarios
    $userCount = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
    echo "- Usuarios: $userCount registros\n";
    
    // Clientes  
    $clientCount = $pdo->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
    echo "- Clientes: $clientCount registros\n";
    
    // Ventas
    $salesCount = $pdo->query("SELECT COUNT(*) FROM ventas")->fetchColumn();
    echo "- Ventas: $salesCount registros\n\n";
    
    // 6. Verificar APIs
    echo "5. PRUEBA DE APIS:\n";
    
    // Test usuarios API
    $stmt = $pdo->query("SELECT id, nombre, email FROM usuarios LIMIT 1");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        echo "✅ API Usuarios: Datos accesibles\n";
        echo "  Ejemplo: {$user['nombre']} ({$user['email']})\n";
    }
    
    // Test clientes API
    $stmt = $pdo->query("SELECT id, nombre_completo, email FROM clientes LIMIT 1");
    $client = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($client) {
        echo "✅ API Clientes: Datos accesibles\n";
        echo "  Ejemplo: {$client['nombre_completo']} ({$client['email']})\n";
    }
    
    // Test ventas API
    $stmt = $pdo->query("SELECT id, numero_venta, total FROM ventas LIMIT 1");
    $sale = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($sale) {
        echo "✅ API Ventas: Datos accesibles\n";
        echo "  Ejemplo: Venta #{$sale['numero_venta']} - ${$sale['total']}\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DE VERIFICACIÓN ===\n";
?>