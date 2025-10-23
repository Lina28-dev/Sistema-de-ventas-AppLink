<?php
// Test básico para verificar la carga de ventas
session_start();

echo "<h1>Test de Ventas</h1>";
echo "<p>Sesión iniciada: " . (session_status() == PHP_SESSION_ACTIVE ? "SÍ" : "NO") . "</p>";
echo "<p>Usuario autenticado: " . (isset($_SESSION['authenticated']) ? ($_SESSION['authenticated'] ? "SÍ" : "NO") : "NO DEFINIDO") . "</p>";
echo "<p>ID de sesión: " . session_id() . "</p>";
echo "<p>Variables de sesión: " . print_r($_SESSION, true) . "</p>";

echo "<h2>Información del servidor</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Documento raíz: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>Script actual: " . $_SERVER['SCRIPT_NAME'] . "</p>";
echo "<p>URI actual: " . $_SERVER['REQUEST_URI'] . "</p>";

echo "<h2>Test de archivos</h2>";
$archivos_test = [
    'ventas.php' => 'src/Views/ventas.php',
    'paths.php' => 'config/paths.php',
    'index.php' => 'public/index.php'
];

foreach ($archivos_test as $nombre => $ruta) {
    $ruta_completa = __DIR__ . '/' . $ruta;
    echo "<p>$nombre: " . (file_exists($ruta_completa) ? "EXISTE" : "NO EXISTE") . " ($ruta_completa)</p>";
}

// Test de carga de ventas
echo "<h2>Test de carga directa</h2>";
try {
    require_once __DIR__ . '/config/paths.php';
    echo "<p>Paths cargado correctamente</p>";
    echo "<p>VIEWS_PATH: " . VIEWS_PATH . "</p>";
    
    if (file_exists(VIEWS_PATH . '/ventas.php')) {
        echo "<p>ventas.php EXISTE en la ruta correcta</p>";
        echo "<a href='/Sistema-de-ventas-AppLink-main/public/ventas' target='_blank'>Ir a ventas (con autenticación)</a><br>";
        echo "<a href='javascript:void(0)' onclick='window.location.href=\"/Sistema-de-ventas-AppLink-main/src/Views/ventas.php\"'>Ir a ventas (directo)</a>";
    } else {
        echo "<p>ventas.php NO EXISTE en " . VIEWS_PATH . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>