<?php
require_once '../autoload.php';

echo "🧪 Probando consulta corregida de usuarios...\n";

$config = require 'config/app_postgresql.php';

try {
    $pdo = new PDO(
        "pgsql:host={$config['db']['host']};port={$config['db']['port']};dbname={$config['db']['name']}",
        $config['db']['user'],
        $config['db']['pass'],
        $config['db']['options']
    );
    
    echo "✅ Conexión PostgreSQL exitosa\n";
    
    $stmt = $pdo->query('SELECT * FROM usuarios ORDER BY nombre, apellido');
    $usuarios = $stmt->fetchAll();
    
    echo "👤 " . count($usuarios) . " usuarios encontrados\n";
    
    foreach ($usuarios as $usuario) {
        echo "- ID: {$usuario['id']}, Nombre: {$usuario['nombre']} {$usuario['apellido']}, Nick: {$usuario['nick']}, Email: {$usuario['email']}\n";
    }
    
    echo "\n✅ La consulta funciona correctamente!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>