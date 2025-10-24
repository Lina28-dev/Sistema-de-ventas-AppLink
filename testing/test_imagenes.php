<?php
/**
 * Test de imágenes - Verificar que todas las rutas funcionen correctamente
 */

echo "<h1>🖼️ Verificación de Imágenes - Sistema AppLink</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; }
    .error { color: red; }
    .warning { color: orange; }
    .test-image { max-width: 100px; margin: 10px; border: 1px solid #ccc; }
</style>";

// Rutas de imágenes a verificar
$imagenes = [
    'logo.jpg' => '/Sistema-de-ventas-AppLink-main/public/assets/images/logo.jpg',
    'panty-invisible.jpg' => '/Sistema-de-ventas-AppLink-main/public/assets/images/panty-invisible.jpg',
    'brasier-pushup.jpg' => '/Sistema-de-ventas-AppLink-main/public/assets/images/brasier-pushup.jpg',
    'pijama-short.jpg' => '/Sistema-de-ventas-AppLink-main/public/assets/images/pijama-short.jpg',
    'camiseta-mc.jpg' => '/Sistema-de-ventas-AppLink-main/public/assets/images/camiseta-mc.jpg',
    'boxer-algodon.jpg' => '/Sistema-de-ventas-AppLink-main/public/assets/images/boxer-algodon.jpg',
    'medias-tobilleras.jpg' => '/Sistema-de-ventas-AppLink-main/public/assets/images/medias-tobilleras.jpg',
    'fondo.jpg' => '/Sistema-de-ventas-AppLink-main/public/assets/images/fondo.jpg'
];

echo "<h2>📂 Verificando archivos físicos:</h2>";

$basePhysicalPath = $_SERVER['DOCUMENT_ROOT'] . '/Sistema-de-ventas-AppLink-main/public/assets/images/';

foreach ($imagenes as $nombre => $url) {
    $physicalPath = $basePhysicalPath . $nombre;
    
    if (file_exists($physicalPath)) {
        echo "<span class='success'>✅ {$nombre} - Archivo existe físicamente</span><br>";
    } else {
        echo "<span class='error'>❌ {$nombre} - Archivo NO encontrado en: {$physicalPath}</span><br>";
    }
}

echo "<h2>🌐 Verificando URLs de acceso:</h2>";

foreach ($imagenes as $nombre => $url) {
    echo "<div style='margin: 10px 0;'>";
    echo "<strong>{$nombre}:</strong> ";
    echo "<span style='font-family: monospace; background: #f5f5f5; padding: 2px 5px;'>{$url}</span><br>";
    echo "<img src='{$url}' alt='{$nombre}' class='test-image' onerror=\"this.style.border='2px solid red'; this.alt='ERROR: No se puede cargar'\" onload=\"this.style.border='2px solid green'\">";
    echo "</div>";
}

echo "<h2>📊 Resumen de rutas corregidas:</h2>";
echo "<ul>";
echo "<li><strong>Sidebar:</strong> Logo actualizado a ruta absoluta</li>";
echo "<li><strong>Pedidos:</strong> Todas las imágenes de productos con rutas absolutas</li>";
echo "<li><strong>Ventas:</strong> Todas las imágenes de productos con rutas absolutas</li>";
echo "<li><strong>Patrón de URL:</strong> /Sistema-de-ventas-AppLink-main/public/assets/images/[archivo]</li>";
echo "</ul>";

echo "<h2>🔧 Estructura de carpetas verificada:</h2>";
$assetsPath = $_SERVER['DOCUMENT_ROOT'] . '/Sistema-de-ventas-AppLink-main/public/assets/';

if (is_dir($assetsPath)) {
    echo "<span class='success'>✅ Carpeta public/assets/ existe</span><br>";
    
    $imagesPath = $assetsPath . 'images/';
    if (is_dir($imagesPath)) {
        echo "<span class='success'>✅ Carpeta public/assets/images/ existe</span><br>";
        
        $files = scandir($imagesPath);
        $imageFiles = array_filter($files, function($file) {
            return in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
        });
        
        echo "<span class='success'>✅ " . count($imageFiles) . " archivos de imagen encontrados</span><br>";
        echo "<small>Archivos: " . implode(', ', $imageFiles) . "</small>";
    } else {
        echo "<span class='error'>❌ Carpeta public/assets/images/ NO existe</span><br>";
    }
} else {
    echo "<span class='error'>❌ Carpeta public/assets/ NO existe</span><br>";
}

?>

<script>
// JavaScript para verificar carga de imágenes en tiempo real
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('.test-image');
    let loaded = 0;
    let failed = 0;
    
    images.forEach(img => {
        img.addEventListener('load', () => {
            loaded++;
            updateStatus();
        });
        
        img.addEventListener('error', () => {
            failed++;
            updateStatus();
        });
    });
    
    function updateStatus() {
        if (loaded + failed === images.length) {
            const statusDiv = document.createElement('div');
            statusDiv.style.cssText = 'margin: 20px 0; padding: 15px; border-radius: 5px; font-weight: bold;';
            
            if (failed === 0) {
                statusDiv.style.backgroundColor = '#d4edda';
                statusDiv.style.color = '#155724';
                statusDiv.innerHTML = `🎉 ¡Éxito! Todas las ${loaded} imágenes se cargaron correctamente`;
            } else {
                statusDiv.style.backgroundColor = '#f8d7da';
                statusDiv.style.color = '#721c24';
                statusDiv.innerHTML = `⚠️ Resultados mixtos: ${loaded} exitosas, ${failed} fallidas`;
            }
            
            document.body.appendChild(statusDiv);
        }
    }
});
</script>