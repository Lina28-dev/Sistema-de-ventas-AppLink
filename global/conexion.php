<?php
define('SERVIDOR', 'localhost');  // Typically 'localhost'
define('USUARIO', 'root');        // Default XAMPP username is 'root'
define('PASSWORD', '');           // Default XAMPP password is empty
define('BD', 'fs_clientes');      // Your database name

// Use the defined constants in your connection
$con = new mysqli(SERVIDOR, USUARIO, PASSWORD, BD);

if ($con->connect_error) {
    die("Error de conexión: " . $con->connect_error);
}
?>