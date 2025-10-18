<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Prueba de inicio de sesión</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn = new mysqli('localhost', 'root', '', 'fs_clientes');
        if ($conn->connect_error) {
            throw new Exception("Error de conexión: " . $conn->connect_error);
        }

        $nick = $conn->real_escape_string($_POST['nick']);
        $password = $_POST['password'];

        $sql = "SELECT * FROM fs_usuarios WHERE nick = '$nick'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                echo "<p style='color: green;'>✓ Credenciales correctas! El usuario existe y la contraseña coincide.</p>";
                echo "<pre>Datos del usuario:\n";
                print_r($user);
                echo "</pre>";
            } else {
                echo "<p style='color: red;'>✗ Contraseña incorrecta</p>";
            }
        } else {
            echo "<p style='color: red;'>✗ Usuario no encontrado</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    }
}
?>

<form method="POST" style="margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
    <div style="margin-bottom: 15px;">
        <label for="nick">Usuario:</label><br>
        <input type="text" id="nick" name="nick" required style="padding: 5px;">
    </div>
    <div style="margin-bottom: 15px;">
        <label for="password">Contraseña:</label><br>
        <input type="password" id="password" name="password" required style="padding: 5px;">
    </div>
    <button type="submit" style="background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">Probar Login</button>
</form>

<div>
<a href="home.php" style="background-color: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Volver al inicio</a>
</div>
