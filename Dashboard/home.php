<?php
session_start();
if (!isset($_SESSION['usuario_nombre'] )) {
  header("Location: ../login/index.php");
  exit();
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Dashboard</title>
</head>
<body>
  <h1>Bienvenido, <?php echo $_SESSION['usuario_nombre'] ; ?> </h1>
  <a href="../login/logout.php">Cerrar Sesión</a>
</body>
</html>
