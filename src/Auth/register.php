<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    include "config.php"; // conexión a la BD

    $usuario = $_POST['usuario'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $sql = "INSERT INTO usuarios (usuario, email, password) VALUES ('$usuario', '$email', '$password')";
    if (mysqli_query($conn, $sql)) {
        header("Location: login.php?success=registered");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="login-box">
        <h1>Registro de Usuario</h1>
        <form method="POST" action="">
            <label for="usuario">Usuario</label>
            <input type="text" name="usuario" required>

            <label for="email">Correo</label>
            <input type="email" name="email" required>

            <label for="password">Contraseña</label>
            <input type="password" name="password" required>

            <button type="submit">Registrar</button>
        </form>
    </div>
</body>
</html>
