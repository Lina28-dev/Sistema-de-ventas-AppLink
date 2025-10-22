<?php
require_once '../autoload.php';

echo "🔍 Verificando estructura de tabla usuarios...\n";

$config = require 'config/app_postgresql.php';

try {
    $pdo = new PDO(
        "pgsql:host={$config['db']['host']};port={$config['db']['port']};dbname={$config['db']['name']}",
        $config['db']['user'],
        $config['db']['pass'],
        $config['db']['options']
    );
    
    echo "📊 Estructura de tabla usuarios:\n";
    $result = $pdo->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'usuarios' ORDER BY ordinal_position");
    
    while ($row = $result->fetch()) {
        echo "- {$row['column_name']} ({$row['data_type']})\n";
    }
    
    echo "\n🔍 Datos de ejemplo:\n";
    $result = $pdo->query("SELECT * FROM usuarios LIMIT 2");
    $users = $result->fetchAll();
    
    if (!empty($users)) {
        echo "Columnas disponibles: " . implode(', ', array_keys($users[0])) . "\n";
        foreach ($users as $user) {
            echo "Usuario: " . json_encode($user) . "\n";
        }
    } else {
        echo "No hay usuarios en la tabla\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>