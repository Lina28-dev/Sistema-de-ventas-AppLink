<?php
echo "PHP Info para Apache:\n";
echo "===================\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "php.ini path: " . php_ini_loaded_file() . "\n";
echo "Extensions dir: " . ini_get('extension_dir') . "\n";
echo "\nExtensiones cargadas:\n";
$extensions = get_loaded_extensions();
foreach ($extensions as $ext) {
    if (strpos(strtolower($ext), 'pdo') !== false || strpos(strtolower($ext), 'pgsql') !== false) {
        echo "✅ $ext\n";
    }
}

echo "\nExtensiones PostgreSQL:\n";
if (extension_loaded('pdo_pgsql')) {
    echo "✅ pdo_pgsql está cargada\n";
} else {
    echo "❌ pdo_pgsql NO está cargada\n";
}

if (extension_loaded('pgsql')) {
    echo "✅ pgsql está cargada\n";
} else {
    echo "❌ pgsql NO está cargada\n";
}
?>