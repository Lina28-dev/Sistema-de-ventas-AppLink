<?php
/**
 * 🔧 CONFIGURACIÓN POSTGRESQL
 * Sistema de Ventas AppLink - Nueva configuración para PostgreSQL
 */

return [
    // Database Configuration - PostgreSQL
    'db' => [
        'driver' => 'pgsql',
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'port' => $_ENV['DB_PORT'] ?? '5432',
        'user' => $_ENV['DB_USER'] ?? 'applink_user',
        'pass' => $_ENV['DB_PASS'] ?? 'applink_2024!',
        'name' => $_ENV['DB_NAME'] ?? 'ventas_applink',
        'charset' => 'utf8',
        'schema' => 'public',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false
        ]
    ],

    // Application Configuration
    'app' => [
        'name' => 'Sistema de Ventas AppLink',
        'url' => $_ENV['APP_URL'] ?? 'http://localhost/Sistema-de-ventas-AppLink-main',
        'debug' => $_ENV['APP_DEBUG'] ?? false,
        'timezone' => 'America/Bogota',
        'locale' => 'es_CO',
        'version' => '2.0.0-postgresql'
    ],

    // Session Configuration
    'session' => [
        'lifetime' => 3600, // 1 hour
        'secure' => $_ENV['SECURE_COOKIES'] ?? false,
        'httponly' => true,
        'samesite' => 'Lax'
    ],

    // Security Configuration
    'security' => [
        'password_min_length' => 8,
        'max_login_attempts' => 5,
        'login_timeout' => 300, // 5 minutes
        'csrf_token_lifetime' => 3600,
        'encryption_key' => $_ENV['ENCRYPTION_KEY'] ?? 'applink-secret-key-2024'
    ],

    // Logging Configuration
    'logging' => [
        'path' => __DIR__ . '/../logs',
        'level' => $_ENV['LOG_LEVEL'] ?? 'ERROR',
        'max_files' => 30,
        'max_size' => '10MB'
    ],

    // PostgreSQL Specific Configuration
    'postgresql' => [
        'search_path' => 'public',
        'application_name' => 'AppLink_Sistema_Ventas',
        'connect_timeout' => 10,
        'statement_timeout' => 30000, // 30 seconds
        'lock_timeout' => 5000, // 5 seconds
        'timezone' => 'America/Bogota'
    ],

    // Migration Configuration
    'migration' => [
        'backup_mysql' => true,
        'preserve_ids' => true,
        'validate_data' => true,
        'batch_size' => 1000
    ],

    // Performance Configuration
    'performance' => [
        'enable_query_cache' => true,
        'cache_lifetime' => 3600,
        'enable_prepared_statements' => true,
        'connection_pool_size' => 10
    ]
];