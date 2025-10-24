<?php
/**
 * Helper para gestión de assets (CSS, JS, imágenes)
 * Sistema de Ventas AppLink
 */

class AssetHelper {
    
    /**
     * URL base para assets
     */
    private static function getBaseUrl() {
        $base = str_replace('/public/index.php', '', $_SERVER['SCRIPT_NAME']);
        $base = str_replace('/index.php', '', $base);
        return $base . '/assets/';
    }
    
    /**
     * Generar URL para imagen
     */
    public static function image($filename) {
        return self::getBaseUrl() . 'images/' . $filename;
    }
    
    /**
     * Generar URL para CSS
     */
    public static function css($filename) {
        return self::getBaseUrl() . 'css/' . $filename;
    }
    
    /**
     * Generar URL para JavaScript
     */
    public static function js($filename) {
        return self::getBaseUrl() . 'js/' . $filename;
    }
    
    /**
     * Generar tag de imagen completo
     */
    public static function img($filename, $alt = '', $class = '', $style = '') {
        $src = self::image($filename);
        $alt = htmlspecialchars($alt);
        $class = htmlspecialchars($class);
        $style = htmlspecialchars($style);
        
        return "<img src=\"{$src}\" alt=\"{$alt}\" class=\"{$class}\" style=\"{$style}\">";
    }
    
    /**
     * Generar tag de CSS completo
     */
    public static function cssTag($filename) {
        $href = self::css($filename);
        return "<link rel=\"stylesheet\" href=\"{$href}\">";
    }
    
    /**
     * Generar tag de JavaScript completo
     */
    public static function jsTag($filename) {
        $src = self::js($filename);
        return "<script src=\"{$src}\"></script>";
    }
    
    /**
     * Verificar si un asset existe
     */
    public static function exists($path) {
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/Sistema-de-ventas-AppLink-main/public/assets/' . $path;
        return file_exists($fullPath);
    }
    
    /**
     * Obtener versión del asset para cache busting
     */
    public static function version($path) {
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/Sistema-de-ventas-AppLink-main/public/assets/' . $path;
        if (file_exists($fullPath)) {
            return '?v=' . filemtime($fullPath);
        }
        return '';
    }
    
    /**
     * Generar URL con versión
     */
    public static function versionedAsset($type, $filename) {
        $url = '';
        switch ($type) {
            case 'image':
                $url = self::image($filename);
                break;
            case 'css':
                $url = self::css($filename);
                break;
            case 'js':
                $url = self::js($filename);
                break;
        }
        
        $version = self::version($type . 's/' . $filename);
        return $url . $version;
    }
}

// Funciones helper globales para facilitar el uso
if (!function_exists('asset_image')) {
    function asset_image($filename) {
        return AssetHelper::image($filename);
    }
}

if (!function_exists('asset_css')) {
    function asset_css($filename) {
        return AssetHelper::css($filename);
    }
}

if (!function_exists('asset_js')) {
    function asset_js($filename) {
        return AssetHelper::js($filename);
    }
}

if (!function_exists('asset_img_tag')) {
    function asset_img_tag($filename, $alt = '', $class = '', $style = '') {
        return AssetHelper::img($filename, $alt, $class, $style);
    }
}