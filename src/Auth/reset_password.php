<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    include "config.php";

    $email = $_POST['email'];
    $nuevoPassword = password_hash("123456", PASSWORD_BCRYPT); // Ejemplo: reset con contraseña fija
    $sql = "UPDATE usuarios SET password='$nuevoPassword' WHERE email='$email'";

    if (mysqli_query($conn, $sql)) {
        echo "Tu contraseña ha sido restablecida. Nueva contraseña: 123456";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Restablecer Contraseña</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="login-box">
        <h1>Restablecer Contraseña</h1>
        <form method="POST" action="">
            <label for="email">Correo asociado a tu cuenta</label>
            <input type="email" name="email" required>

            <button type="submit">Restablecer</button>
        </form>
    </div>
</body>
</html>

