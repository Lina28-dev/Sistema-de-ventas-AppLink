<?php
// Archivo para evitar error 404 de Auth/logout.php
session_start();
$_SESSION = array();
session_destroy();
header('Location: /Sistema-de-ventas-AppLink-main/public/');
exit();
?>