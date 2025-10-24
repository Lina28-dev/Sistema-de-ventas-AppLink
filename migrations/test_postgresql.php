<?php
// Test de conexión PostgreSQL
echo "🔍 PROBANDO NUEVA CONFIGURACIÓN POSTGRESQL\n";
echo "=========================================\n\n";

try {
    require_once '../config/Database.php';
    
    $dsn = App\Config\Database::getDSN();
    $user = App\Config\Database::getUsername();
    $options = App\Config\Database::getOptions();
    
    echo "📋 Configuración:\n";
    echo "   DSN: $dsn\n";
    echo "   Usuario: $user\n";
    echo "   Opciones: " . count($options) . " configuradas\n\n";
    
    // Crear conexión
    $pdo = new PDO($dsn, $user, App\Config\Database::getPassword(), $options);
    echo "✅ Conexión PostgreSQL exitosa!\n\n";
    
    // Probar consultas básicas
    echo "📊 Verificando datos migrados:\n";
    
    $tables = ['usuarios', 'clientes', 'productos', 'ventas'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
        $count = $stmt->fetch()['count'];
        echo "   $table: $count registros\n";
    }
    
    echo "\n🔍 Probando login de usuario admin:\n";
    $stmt = $pdo->prepare("SELECT id, nombre, nick, is_admin FROM usuarios WHERE nick = ? AND is_admin = true");
    $stmt->execute(['admin']);
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "✅ Usuario admin encontrado:\n";
        echo "   ID: {$admin['id']}\n";
        echo "   Nombre: {$admin['nombre']}\n";
        echo "   Nick: {$admin['nick']}\n";
        echo "   Es Admin: " . ($admin['is_admin'] ? 'Sí' : 'No') . "\n";
    } else {
        echo "❌ Usuario admin no encontrado\n";
    }
    
    echo "\n🎉 ¡Sistema listo para usar con PostgreSQL!\n";
    echo "=========================================\n";
    echo "📋 Próximos pasos:\n";
    echo "   1. Abrir el sistema en el navegador\n";
    echo "   2. Intentar login con: admin / [contraseña]\n";
    echo "   3. Verificar que todas las secciones funcionen\n";
    echo "   4. Probar crear/editar datos\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\n🔧 Posibles soluciones:\n";
    echo "   - Verificar que PostgreSQL esté corriendo\n";
    echo "   - Verificar credenciales en config/app.php\n";
    echo "   - Verificar que las extensiones PHP estén habilitadas\n";
}
?>