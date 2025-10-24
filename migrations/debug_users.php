<?php
$pdo = new PDO('mysql:host=localhost;dbname=fs_clientes', 'root', '');
$stmt = $pdo->query('SELECT * FROM fs_usuarios LIMIT 2');
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Usuario:\n";
    foreach($row as $key => $value) {
        echo "  $key: '$value' (" . gettype($value) . ")\n";
    }
    echo "\n";
}
?>