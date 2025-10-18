<?php
$host = 'localhost';
$user = 'root';
$pass = 'tu_password';
$db = 'gestor_ventas_lilipink';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

echo "<h2>✅ Conexión exitosa a MySQL</h2>";
echo "<p>Base de datos: " . $db . "</p>";

$result = $conn->query("SELECT * FROM test_productos");
echo "<h3>Productos en la BD:</h3><ul>";
while($row = $result->fetch_assoc()) {
    echo "<li>" . $row['nombre'] . " - $" . $row['precio'] . "</li>";
}
echo "</ul>";
$conn->close();
?>
