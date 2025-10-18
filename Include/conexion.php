<?php
$host = "localhost";  // O el host de tu base de datos
$usuario_db = "tu_usuario_db"; // Tu usuario de la base de datos
$contrasena_db = "tu_contrasena_db"; // Tu contraseña de la base de datos
$nombre_db = "tu_nombre_db"; // El nombre de tu base de datos

try {
    $conexion = new PDO("mysql:host=$host;dbname=$nombre_db;charset=utf8", $usuario_db, $contrasena_db);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
