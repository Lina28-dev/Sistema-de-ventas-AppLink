<?php
return [
    // Database Configuration - PostgreSQL
    'db' => [
        'driver' => 'pgsql',
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'port' => $_ENV['DB_PORT'] ?? '5432',
        'user' => $_ENV['DB_USER'] ?? 'applink_user',
        'pass' => $_ENV['DB_PASS'] ?? 'applink_2024!',
        'name' => $_ENV['DB_NAME'] ?? 'ventas_applink',
        'charset' => 'utf8'
    ],

    // Application Configuration
    'app' => [
    'name' => 'Sistema de Ventas AppLink',
        'url' => $_ENV['APP_URL'] ?? 'http://localhost/Sistema-de-ventas-AppLink-main',
        'debug' => $_ENV['APP_DEBUG'] ?? false,
        'timezone' => 'America/Bogota'
    ],

    // Session Configuration
    'session' => [
        'lifetime' => 3600, // 1 hour
        'secure' => $_ENV['SECURE_COOKIES'] ?? false
    ],

    // Security Configuration
    'security' => [
        'password_min_length' => 8,
        'max_login_attempts' => 5,
        'login_timeout' => 300 // 5 minutes
    ],

    // Logging Configuration
    'logging' => [
        'path' => __DIR__ . '/../logs',
        'level' => $_ENV['LOG_LEVEL'] ?? 'ERROR'
    ]
];
