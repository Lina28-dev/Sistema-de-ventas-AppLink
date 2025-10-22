<?php
namespace App\Config;

class Database {
    private static $config;
    
    public static function getConfig() {
        if (!self::$config) {
            self::$config = require __DIR__ . '/app.php';
        }
        return self::$config['db'];
    }
    
    public static function getDSN() {
        $config = self::getConfig();
        if (isset($config['driver']) && $config['driver'] === 'pgsql') {
            return sprintf(
                "pgsql:host=%s;port=%s;dbname=%s",
                $config['host'],
                $config['port'] ?? '5432',
                $config['name']
            );
        } else {
            // Fallback a MySQL
            return sprintf(
                "mysql:host=%s;dbname=%s;charset=%s",
                $config['host'],
                $config['name'],
                $config['charset']
            );
        }
    }
    
    public static function getUsername() {
        return self::getConfig()['user'];
    }
    
    public static function getPassword() {
        return self::getConfig()['pass'];
    }
    
    public static function getOptions() {
        $config = self::getConfig();
        if (isset($config['driver']) && $config['driver'] === 'pgsql') {
            return [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::ATTR_STRINGIFY_FETCHES => false
            ];
        } else {
            // Opciones para MySQL
            return [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];
        }
    }
}
