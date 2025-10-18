<?php
$ruta_conexion = __DIR__.'/../global/conexion.php';
if (!file_exists($ruta_conexion)) {
    die("<tr><td colspan='9' class='error'>Error: Archivo de conexión no encontrado</td></tr>");
}

include($ruta_conexion);

if ($con->connect_error) {
    die("<tr><td colspan='9' class='error'>Error de conexión: ".$con->connect_error."</td></tr>");
}

$cadena = isset($_GET['cadena']) ? trim($con->real_escape_string($_GET['cadena'])) : '';

if (empty($cadena)) {
    die("<tr><td colspan='9'>Ingrese un término de búsqueda</td></tr>");
}

$sql = "SELECT * FROM lenceria WHERE 
        modelo LIKE CONCAT('%', ?, '%') OR 
        color LIKE CONCAT('%', ?, '%') OR 
        material LIKE CONCAT('%', ?, '%') OR 
        talle LIKE CONCAT('%', ?, '%') OR
        variante LIKE CONCAT('%', ?, '%')
        ORDER BY modelo LIMIT 50";

$stmt = $con->prepare($sql);
if (!$stmt) {
    die("<tr><td colspan='9' class='error'>Error en la preparación de la consulta</td></tr>");
}

$stmt->bind_param("sssss", $cadena, $cadena, $cadena, $cadena, $cadena);

if (!$stmt->execute()) {
    die("<tr><td colspan='9' class='error'>Error al ejecutar la consulta</td></tr>");
}

$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        echo "<tr>
                <td>".htmlspecialchars($fila['codigo'])."</td>
                <td>".htmlspecialchars($fila['modelo'])."</td>
                <td>".htmlspecialchars($fila['color'])."</td>
                <td>".htmlspecialchars($fila['material'])."</td>
                <td>".htmlspecialchars($fila['talle'])."</td>
                <td>".htmlspecialchars($fila['stock'])."</td>
                <td>".htmlspecialchars($fila['variante'])."</td>
                <td>".htmlspecialchars($fila['detalles'])."</td>
                <td>$".number_format($fila['precio'], 2)."</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='9'>No se encontró lencería para '".htmlspecialchars($cadena)."'</td></tr>";
}

$stmt->close();
$con->close();
?>