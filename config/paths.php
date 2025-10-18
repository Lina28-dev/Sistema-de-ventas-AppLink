<?php
// Definición de rutas base
define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', BASE_PATH . '/public');
define('SRC_PATH', BASE_PATH . '/src');

// Rutas de directorios principales
define('MODELS_PATH', SRC_PATH . '/Models');
define('VIEWS_PATH', SRC_PATH . '/Views');
define('CONTROLLERS_PATH', SRC_PATH . '/Controllers');
define('UTILS_PATH', SRC_PATH . '/Utils');

// Rutas de assets
define('CSS_PATH', '/public/css');
define('JS_PATH', '/public/js');
define('IMG_PATH', '/public/img');

// URLs base
define('BASE_URL', '/gestor-ventas-lilipink-main');
define('ASSETS_URL', BASE_URL . '/public');
?>