<?php
/**
 * Configuración de Assets para Sistema de Ventas AppLink
 * Gestión centralizada de CSS, JS e imágenes
 */

class AssetManager {
    
    private static $baseUrl = '/Sistema-de-ventas-AppLink-main/public/assets/';
    private static $version = '1.0.0';
    
    /**
     * Generar URL de CSS
     */
    public static function css($file, $page = false) {
        $path = $page ? 'css/pages/' : 'css/components/';
        return self::$baseUrl . $path . $file . '?v=' . self::$version;
    }
    
    /**
     * Generar URL de JavaScript
     */
    public static function js($file, $page = false) {
        $path = $page ? 'js/pages/' : 'js/components/';
        return self::$baseUrl . $path . $file . '?v=' . self::$version;
    }
    
    /**
     * Generar URL de imagen
     */
    public static function image($file, $type = 'ui') {
        $path = $type === 'icon' ? 'images/icons/' : 'images/ui/';
        return self::$baseUrl . $path . $file;
    }
    
    /**
     * Cargar CSS de componentes básicos
     */
    public static function loadBasicCSS() {
        $files = [
            'base.css',
            'header.css'
        ];
        
        foreach ($files as $file) {
            echo '<link rel="stylesheet" href="' . self::css($file) . '">' . "\n";
        }
    }
    
    /**
     * Cargar CSS de página específica
     */
    public static function loadPageCSS($page) {
        $file = $page . '.css';
        echo '<link rel="stylesheet" href="' . self::css($file, true) . '">' . "\n";
    }
    
    /**
     * Cargar JavaScript de página específica
     */
    public static function loadPageJS($page) {
        $file = $page . '.js';
        echo '<script src="' . self::js($file, true) . '"></script>' . "\n";
    }
    
    /**
     * Cargar JavaScript de componentes básicos
     */
    public static function loadBasicJS() {
        $file = 'scripts.js';
        echo '<script src="' . self::js($file) . '"></script>' . "\n";
    }
}

// Función helper global para facilitar el uso
function asset($type, $file, $subtype = null) {
    switch ($type) {
        case 'css':
            return AssetManager::css($file, $subtype === 'page');
        case 'js':
            return AssetManager::js($file, $subtype === 'page');
        case 'image':
            return AssetManager::image($file, $subtype);
        default:
            return '';
    }
}
?>