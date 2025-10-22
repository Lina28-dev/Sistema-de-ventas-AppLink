<?php
require_once 'config/Database.php';
$pdo = new PDO(
    App\Config\Database::getDSN(),
    App\Config\Database::getUsername(),
    App\Config\Database::getPassword(),
    App\Config\Database::getOptions()
);

$stmt = $pdo->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'ventas' ORDER BY ordinal_position");
echo "Columnas de la tabla ventas:\n";
while ($row = $stmt->fetch()) { 
    echo $row['column_name'] . ' - ' . $row['data_type'] . "\n"; 
}
?>