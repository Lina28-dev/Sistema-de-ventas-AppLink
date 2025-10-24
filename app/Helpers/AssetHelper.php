<?php
/**
 * Helper de Assets para Sistema de Ventas AppLink
 * Gestiona rutas de CSS, JS e imágenes
 */

class AssetHelper {
    
    private static $baseUrl = '/Sistema-de-ventas-AppLink-main/public/assets/';
    
    /**
     * Obtener URL de imagen
     */
    public static function image($filename) {
        return self::$baseUrl . 'images/' . $filename;
    }
    
    /**
     * Obtener URL de CSS
     */
    public static function css($filename) {
        return self::$baseUrl . 'css/' . $filename;
    }
    
    /**
     * Obtener URL de JavaScript
     */
    public static function js($filename) {
        return self::$baseUrl . 'js/' . $filename;
    }
    
    /**
     * Obtener ruta relativa de imagen
     */
    public static function imageRelative($filename, $levels = 2) {
        $prefix = str_repeat('../', $levels);
        return $prefix . 'public/assets/images/' . $filename;
    }
    
    /**
     * Obtener ruta absoluta de asset
     */
    public static function assetPath($type, $filename) {
        $basePath = $_SERVER['DOCUMENT_ROOT'] . '/Sistema-de-ventas-AppLink-main/public/assets/';
        return $basePath . $type . '/' . $filename;
    }
    
    /**
     * Verificar si un asset existe
     */
    public static function exists($type, $filename) {
        return file_exists(self::assetPath($type, $filename));
    }
    
    /**
     * Obtener lista de imágenes disponibles
     */
    public static function getAvailableImages() {
        $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/Sistema-de-ventas-AppLink-main/public/assets/images/';
        if (!is_dir($imagePath)) return [];
        
        $images = [];
        $files = scandir($imagePath);
        
        foreach ($files as $file) {
            if (in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $images[] = $file;
            }
        }
        
        return $images;
    }
    
    /**
     * URL completa del asset (con dominio)
     */
    public static function fullUrl($type, $filename) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        return $protocol . '://' . $host . self::$baseUrl . $type . '/' . $filename;
    }
}

/**
 * Funciones helper globales
 */
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

if (!function_exists('image_relative')) {
    function image_relative($filename, $levels = 2) {
        return AssetHelper::imageRelative($filename, $levels);
    }
}
?>