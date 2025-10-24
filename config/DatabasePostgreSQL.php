<?php
/**
 * 🔧 CLASE DATABASE ADAPTADA PARA POSTGRESQL
 * Sistema de Ventas AppLink - Soporte para PostgreSQL
 */

namespace App\Config;

class DatabasePostgreSQL {
    private static $config;
    private static $connection;
    
    public static function getConfig() {
        if (!self::$config) {
            self::$config = require __DIR__ . '/app_postgresql.php';
        }
        return self::$config['db'];
    }
    
    /**
     * Obtener DSN para PostgreSQL
     */
    public static function getDSN() {
        $config = self::getConfig();
        return sprintf(
            "pgsql:host=%s;port=%s;dbname=%s;",
            $config['host'],
            $config['port'],
            $config['name']
        );
    }
    
    /**
     * Obtener nombre de usuario
     */
    public static function getUsername() {
        return self::getConfig()['user'];
    }
    
    /**
     * Obtener contraseña
     */
    public static function getPassword() {
        return self::getConfig()['pass'];
    }
    
    /**
     * Obtener opciones de PDO para PostgreSQL
     */
    public static function getOptions() {
        $config = self::getConfig();
        return $config['options'] ?? [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
            \PDO::ATTR_STRINGIFY_FETCHES => false
        ];
    }
    
    /**
     * Obtener conexión singleton a PostgreSQL
     */
    public static function getConnection() {
        if (!self::$connection) {
            try {
                self::$connection = new \PDO(
                    self::getDSN(),
                    self::getUsername(),
                    self::getPassword(),
                    self::getOptions()
                );
                
                // Configurar PostgreSQL específicamente
                self::configurePostgreSQL();
                
            } catch (\PDOException $e) {
                throw new \Exception("Error de conexión PostgreSQL: " . $e->getMessage());
            }
        }
        
        return self::$connection;
    }
    
    /**
     * Configurar sesión PostgreSQL
     */
    private static function configurePostgreSQL() {
        $config = self::getConfig();
        $pgConfig = require __DIR__ . '/app_postgresql.php';
        
        $statements = [
            "SET timezone = '{$pgConfig['postgresql']['timezone']}'",
            "SET search_path TO {$pgConfig['postgresql']['search_path']}",
            "SET application_name = '{$pgConfig['postgresql']['application_name']}'",
            "SET statement_timeout = {$pgConfig['postgresql']['statement_timeout']}",
            "SET lock_timeout = {$pgConfig['postgresql']['lock_timeout']}"
        ];
        
        foreach ($statements as $sql) {
            try {
                self::$connection->exec($sql);
            } catch (\PDOException $e) {
                // Log pero no fallar por configuraciones opcionales
                error_log("PostgreSQL config warning: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Ejecutar query con logging
     */
    public static function query($sql, $params = []) {
        $connection = self::getConnection();
        
        try {
            if (empty($params)) {
                $result = $connection->query($sql);
            } else {
                $stmt = $connection->prepare($sql);
                $stmt->execute($params);
                $result = $stmt;
            }
            
            return $result;
            
        } catch (\PDOException $e) {
            self::logError($sql, $params, $e);
            throw $e;
        }
    }
    
    /**
     * Ejecutar transacción
     */
    public static function transaction($callback) {
        $connection = self::getConnection();
        
        try {
            $connection->beginTransaction();
            $result = $callback($connection);
            $connection->commit();
            return $result;
            
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }
    
    /**
     * Obtener último ID insertado (usando RETURNING)
     */
    public static function getLastInsertId($table, $idColumn = 'id') {
        // En PostgreSQL, es mejor usar RETURNING en el INSERT
        // Esta es una función auxiliar para casos especiales
        $sql = "SELECT CURRVAL(pg_get_serial_sequence(?, ?))";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute([$table, $idColumn]);
        return $stmt->fetchColumn();
    }
    
    /**
     * Verificar si una tabla existe
     */
    public static function tableExists($tableName) {
        $sql = "
            SELECT EXISTS (
                SELECT FROM information_schema.tables 
                WHERE table_schema = 'public' 
                AND table_name = ?
            )
        ";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute([$tableName]);
        return $stmt->fetchColumn();
    }
    
    /**
     * Obtener información de columnas
     */
    public static function getColumns($tableName) {
        $sql = "
            SELECT 
                column_name,
                data_type,
                is_nullable,
                column_default
            FROM information_schema.columns 
            WHERE table_schema = 'public' 
            AND table_name = ?
            ORDER BY ordinal_position
        ";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute([$tableName]);
        return $stmt->fetchAll();
    }
    
    /**
     * Logging de errores
     */
    private static function logError($sql, $params, $exception) {
        $config = require __DIR__ . '/app_postgresql.php';
        
        if ($config['app']['debug']) {
            $logData = [
                'timestamp' => date('Y-m-d H:i:s'),
                'sql' => $sql,
                'params' => $params,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ];
            
            $logFile = $config['logging']['path'] . '/postgresql_errors.log';
            file_put_contents($logFile, json_encode($logData) . "\n", FILE_APPEND | LOCK_EX);
        }
    }
    
    /**
     * Obtener estadísticas de la base de datos
     */
    public static function getStats() {
        $sql = "
            SELECT 
                schemaname,
                tablename,
                n_tup_ins as inserts,
                n_tup_upd as updates,
                n_tup_del as deletes,
                n_live_tup as live_tuples,
                n_dead_tup as dead_tuples
            FROM pg_stat_user_tables
            ORDER BY tablename
        ";
        
        return self::query($sql)->fetchAll();
    }
    
    /**
     * Vacuum y análisis de tablas
     */
    public static function maintenance() {
        $connection = self::getConnection();
        
        // Obtener lista de tablas
        $sql = "
            SELECT tablename 
            FROM pg_tables 
            WHERE schemaname = 'public'
        ";
        $tables = self::query($sql)->fetchAll(\PDO::FETCH_COLUMN);
        
        foreach ($tables as $table) {
            try {
                $connection->exec("VACUUM ANALYZE $table");
            } catch (\PDOException $e) {
                error_log("Error en mantenimiento de tabla $table: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Cerrar conexión
     */
    public static function close() {
        self::$connection = null;
    }
}