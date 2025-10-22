<?php
require_once 'autoload.php';

echo "🧪 Probando conexión PostgreSQL...\n";

$config = require 'config/app_postgresql.php';

try {
    $pdo = new PDO(
        "pgsql:host={$config['db']['host']};port={$config['db']['port']};dbname={$config['db']['name']}",
        $config['db']['user'],
        $config['db']['pass'],
        $config['db']['options']
    );
    
    echo "✅ Conexión PostgreSQL exitosa\n";
    
    $result = $pdo->query('SELECT COUNT(*) as total FROM usuarios');
    $count = $result->fetch()['total'];
    echo "👤 Usuarios en BD: $count\n";
    
    // Probar AssetHelper
    require_once 'app/Helpers/AssetHelper.php';
    echo "🖼️ Imagen logo: " . asset_image('logo.jpg') . "\n";
    echo "🎨 CSS base: " . asset_css('components/base.css') . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>