<?php
return [
    // Database Configuration - MySQL (para XAMPP)
    'db' => [
        'driver' => 'mysql',
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'port' => $_ENV['DB_PORT'] ?? '3306',
        'user' => $_ENV['DB_USER'] ?? 'root',
        'pass' => $_ENV['DB_PASS'] ?? '',
        'name' => $_ENV['DB_NAME'] ?? 'fs_clientes',
        'charset' => 'utf8mb4'
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
