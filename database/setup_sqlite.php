<?php
// Configuración alternativa usando SQLite
define('DB_TYPE', 'sqlite');
define('DB_PATH', __DIR__ . '/../database/app.db');

try {
    // Crear conexión SQLite
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Crear tabla de usuarios si no existe
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS usuarios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            rol VARCHAR(20) DEFAULT 'usuario',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Insertar usuario admin por defecto
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = 'admin@admin.com'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Admin', 'admin@admin.com', password_hash('admin123', PASSWORD_DEFAULT), 'admin']);
    }
    
    echo "<div style='color: green; padding: 10px; background: #e8f5e8; border: 1px solid #4cae4c; margin: 10px;'>";
    echo "✅ <strong>Base de datos SQLite configurada correctamente</strong><br>";
    echo "📝 Usuario: admin@admin.com<br>";
    echo "🔑 Contraseña: admin123<br>";
    echo "<a href='public/' style='color: #0066cc;'>🚀 Ir al sistema</a>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div style='color: red; padding: 10px; background: #ffeaea; border: 1px solid #d43f3a; margin: 10px;'>";
    echo "❌ Error: " . $e->getMessage();
    echo "</div>";
}
?>