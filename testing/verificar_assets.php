<?php
/**
 * Script de verificación de assets
 * Verifica que todas las imágenes y assets existan y sean accesibles
 */

echo "<h2>🔍 Verificación de Assets - Sistema AppLink</h2>";
echo "<style>
    body { font-family: 'Segoe UI', Arial, sans-serif; margin: 20px; }
    .success { color: #28a745; font-weight: bold; }
    .error { color: #dc3545; font-weight: bold; }
    .warning { color: #ffc107; font-weight: bold; }
    .asset-item { 
        padding: 8px; 
        margin: 5px 0; 
        border-left: 4px solid #ccc; 
        background: #f8f9fa; 
    }
    .success-item { border-left-color: #28a745; }
    .error-item { border-left-color: #dc3545; }
    img.preview { max-width: 100px; max-height: 60px; margin-left: 10px; }
</style>";

$basePath = __DIR__ . '/../public/assets/';
$baseUrl = '/Sistema-de-ventas-AppLink-main/public/assets/';

// Assets críticos a verificar
$criticalAssets = [
    'images/logo.jpg' => 'Logo principal del sistema',
    'images/fondo.jpg' => 'Imagen de fondo/hero',
    'images/dashboard.jpg' => 'Preview del dashboard',
    'css/components/home.css' => 'Estilos de la página de inicio',
    'css/components/base.css' => 'Estilos base del sistema'
];

echo "<h3>📋 Verificando Assets Críticos:</h3>";

$allOk = true;
foreach ($criticalAssets as $asset => $description) {
    $filePath = $basePath . $asset;
    $exists = file_exists($filePath);
    
    $status = $exists ? 'success' : 'error';
    $statusText = $exists ? '✅ Encontrado' : '❌ No encontrado';
    $itemClass = $exists ? 'success-item' : 'error-item';
    
    echo "<div class='asset-item {$itemClass}'>";
    echo "<span class='{$status}'>{$statusText}</span> ";
    echo "<strong>{$asset}</strong> - {$description}";
    
    if ($exists) {
        $size = filesize($filePath);
        echo " <small>({$size} bytes)</small>";
        
        // Mostrar preview para imágenes
        if (strpos($asset, 'images/') === 0) {
            echo "<img src='{$baseUrl}{$asset}' alt='Preview' class='preview'>";
        }
    } else {
        $allOk = false;
    }
    
    echo "</div>";
}

// Verificar estructura de directorios
echo "<h3>📁 Verificando Estructura de Directorios:</h3>";

$requiredDirs = [
    'images' => 'Directorio de imágenes',
    'css' => 'Directorio de hojas de estilo',
    'css/components' => 'Componentes CSS',
    'js' => 'Directorio de JavaScript'
];

foreach ($requiredDirs as $dir => $description) {
    $dirPath = $basePath . $dir;
    $exists = is_dir($dirPath);
    
    $status = $exists ? 'success' : 'error';
    $statusText = $exists ? '✅ Existe' : '❌ No existe';
    $itemClass = $exists ? 'success-item' : 'error-item';
    
    echo "<div class='asset-item {$itemClass}'>";
    echo "<span class='{$status}'>{$statusText}</span> ";
    echo "<strong>/{$dir}/</strong> - {$description}";
    echo "</div>";
    
    if (!$exists) {
        $allOk = false;
    }
}

// Verificar todas las imágenes en el directorio
echo "<h3>🖼️ Todas las Imágenes Disponibles:</h3>";

$imagesDir = $basePath . 'images/';
if (is_dir($imagesDir)) {
    $images = array_diff(scandir($imagesDir), array('.', '..'));
    
    foreach ($images as $image) {
        if (is_file($imagesDir . $image)) {
            $size = filesize($imagesDir . $image);
            echo "<div class='asset-item success-item'>";
            echo "<span class='success'>✅</span> ";
            echo "<strong>{$image}</strong> ({$size} bytes)";
            echo "<img src='{$baseUrl}images/{$image}' alt='{$image}' class='preview'>";
            echo "</div>";
        }
    }
} else {
    echo "<div class='asset-item error-item'>";
    echo "<span class='error'>❌ Directorio de imágenes no encontrado</span>";
    echo "</div>";
    $allOk = false;
}

// Test de acceso HTTP
echo "<h3>🌐 Test de Acceso HTTP:</h3>";

$testUrls = [
    $baseUrl . 'images/logo.jpg',
    $baseUrl . 'css/components/home.css'
];

foreach ($testUrls as $url) {
    $fullUrl = 'http://' . $_SERVER['HTTP_HOST'] . $url;
    
    // Usar file_get_contents con context para verificar acceso HTTP
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'method' => 'HEAD'
        ]
    ]);
    
    $headers = @get_headers($fullUrl, 1, $context);
    $accessible = $headers && strpos($headers[0], '200') !== false;
    
    $status = $accessible ? 'success' : 'error';
    $statusText = $accessible ? '✅ Accesible' : '❌ No accesible';
    $itemClass = $accessible ? 'success-item' : 'error-item';
    
    echo "<div class='asset-item {$itemClass}'>";
    echo "<span class='{$status}'>{$statusText}</span> ";
    echo "<a href='{$fullUrl}' target='_blank'>{$url}</a>";
    echo "</div>";
}

// Resumen final
echo "<h3>📊 Resumen Final:</h3>";

if ($allOk) {
    echo "<div class='asset-item success-item'>";
    echo "<span class='success'>🎉 ¡Todos los assets están configurados correctamente!</span>";
    echo "<br><small>Las imágenes del logo deberían cargarse sin problemas ahora.</small>";
    echo "</div>";
} else {
    echo "<div class='asset-item error-item'>";
    echo "<span class='error'>⚠️ Hay algunos problemas con los assets.</span>";
    echo "<br><small>Revisa los elementos marcados en rojo arriba.</small>";
    echo "</div>";
}

echo "<h3>🔧 Soluciones Aplicadas:</h3>";
echo "<ul>";
echo "<li>✅ Corregidas las rutas de imágenes en home.php</li>";
echo "<li>✅ Corregidas las rutas en partials y auth/login.php</li>";
echo "<li>✅ Creado archivo CSS home.css</li>";
echo "<li>✅ Creado AssetHelper para gestión de rutas</li>";
echo "<li>✅ Todas las rutas ahora usan 'assets/images/logo.jpg'</li>";
echo "</ul>";

echo "<hr>";
echo "<p><strong>Nota:</strong> Este script verifica la configuración de assets. Si ves errores, asegúrate de que:</p>";
echo "<ul>";
echo "<li>El directorio public/assets/images/ existe</li>";
echo "<li>El archivo logo.jpg está en public/assets/images/</li>";
echo "<li>Los permisos de los archivos permiten lectura</li>";
echo "<li>El servidor web puede acceder a los assets</li>";
echo "</ul>";
?>