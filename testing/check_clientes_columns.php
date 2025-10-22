<?php
$pdo = new PDO('pgsql:host=localhost;port=5432;dbname=ventas_applink', 'applink_user', 'applink_2024!');
$stmt = $pdo->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'clientes' ORDER BY ordinal_position");
echo "Columnas de la tabla clientes:\n";
while ($row = $stmt->fetch()) { 
    echo $row['column_name'] . ' - ' . $row['data_type'] . "\n"; 
}
?>