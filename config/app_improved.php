<?php
/**
 * Archivo de configuración principal mejorado
 * Sistema de Ventas AppLink
 */

// Cargar variables de entorno si existe el archivo .env
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $env = parse_ini_file($envFile);
    foreach ($env as $key => $value) {
        $_ENV[$key] = $value;
    }
}

return [
    // =====================================
    // CONFIGURACIÓN DE BASE DE DATOS
    // =====================================
    'db' => [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'user' => $_ENV['DB_USER'] ?? 'root',
        'pass' => $_ENV['DB_PASS'] ?? '',
        'name' => $_ENV['DB_NAME'] ?? 'fs_clientes',
        'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
        'port' => $_ENV['DB_PORT'] ?? 3306,
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ]
    ],

    // =====================================
    // CONFIGURACIÓN DE LA APLICACIÓN
    // =====================================
    'app' => [
        'name' => 'Sistema de Ventas AppLink',
        'version' => '2.0.0',
        'url' => $_ENV['APP_URL'] ?? 'http://localhost/Sistema-de-ventas-AppLink-main',
        'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'timezone' => $_ENV['APP_TIMEZONE'] ?? 'America/Bogota',
        'locale' => $_ENV['APP_LOCALE'] ?? 'es_ES',
        'maintenance' => filter_var($_ENV['APP_MAINTENANCE'] ?? false, FILTER_VALIDATE_BOOLEAN)
    ],

    // =====================================
    // CONFIGURACIÓN DE SESIONES
    // =====================================
    'session' => [
        'lifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 7200), // 2 horas
        'name' => $_ENV['SESSION_NAME'] ?? 'APPLINK_SESSION',
        'secure' => filter_var($_ENV['SESSION_SECURE'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'httponly' => true,
        'samesite' => 'Strict',
        'save_path' => $_ENV['SESSION_SAVE_PATH'] ?? '',
        'regenerate_interval' => 1800 // 30 minutos
    ],

    // =====================================
    // CONFIGURACIÓN DE SEGURIDAD
    // =====================================
    'security' => [
        'password_min_length' => 8,
        'password_require_uppercase' => true,
        'password_require_lowercase' => true,
        'password_require_numbers' => true,
        'password_require_symbols' => false,
        'max_login_attempts' => 5,
        'lockout_duration' => 900, // 15 minutos
        'csrf_token_lifetime' => 3600, // 1 hora
        'session_timeout' => 7200, // 2 horas
        'ip_validation' => true,
        'user_agent_validation' => true
    ],

    // =====================================
    // CONFIGURACIÓN DE LOGGING
    // =====================================
    'logging' => [
        'enabled' => filter_var($_ENV['LOG_ENABLED'] ?? true, FILTER_VALIDATE_BOOLEAN),
        'path' => $_ENV['LOG_PATH'] ?? __DIR__ . '/../logs',
        'level' => $_ENV['LOG_LEVEL'] ?? 'ERROR',
        'max_files' => (int)($_ENV['LOG_MAX_FILES'] ?? 30),
        'max_size' => $_ENV['LOG_MAX_SIZE'] ?? '10MB',
        'channels' => [
            'error' => 'error.log',
            'access' => 'access.log',
            'security' => 'security.log',
            'database' => 'database.log'
        ]
    ],

    // =====================================
    // CONFIGURACIÓN DE EMAIL
    // =====================================
    'mail' => [
        'driver' => $_ENV['MAIL_DRIVER'] ?? 'smtp',
        'host' => $_ENV['MAIL_HOST'] ?? 'localhost',
        'port' => (int)($_ENV['MAIL_PORT'] ?? 587),
        'username' => $_ENV['MAIL_USERNAME'] ?? '',
        'password' => $_ENV['MAIL_PASSWORD'] ?? '',
        'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
        'from_address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@applink.local',
        'from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'Sistema AppLink'
    ],

    // =====================================
    // CONFIGURACIÓN DE ARCHIVOS
    // =====================================
    'files' => [
        'upload_path' => $_ENV['UPLOAD_PATH'] ?? __DIR__ . '/../public/uploads',
        'max_upload_size' => $_ENV['MAX_UPLOAD_SIZE'] ?? '5MB',
        'allowed_extensions' => [
            'images' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'documents' => ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
            'general' => ['txt', 'csv']
        ],
        'image_quality' => 85,
        'thumbnail_size' => [150, 150]
    ],

    // =====================================
    // CONFIGURACIÓN DE CACHE
    // =====================================
    'cache' => [
        'driver' => $_ENV['CACHE_DRIVER'] ?? 'file',
        'path' => $_ENV['CACHE_PATH'] ?? __DIR__ . '/../storage/cache',
        'default_ttl' => (int)($_ENV['CACHE_TTL'] ?? 3600),
        'prefix' => $_ENV['CACHE_PREFIX'] ?? 'applink_'
    ],

    // =====================================
    // CONFIGURACIÓN DEL SISTEMA DE VENTAS
    // =====================================
    'ventas' => [
        'moneda' => $_ENV['CURRENCY'] ?? 'COP',
        'simbolo_moneda' => $_ENV['CURRENCY_SYMBOL'] ?? '$',
        'decimales' => (int)($_ENV['CURRENCY_DECIMALS'] ?? 2),
        'impuesto_default' => (float)($_ENV['DEFAULT_TAX'] ?? 0.19), // 19% IVA
        'descuento_maximo' => (float)($_ENV['MAX_DISCOUNT'] ?? 0.50), // 50%
        'stock_minimo_alert' => (int)($_ENV['LOW_STOCK_ALERT'] ?? 5),
        'facturacion' => [
            'prefijo' => $_ENV['INVOICE_PREFIX'] ?? 'FAC',
            'longitud_numero' => (int)($_ENV['INVOICE_NUMBER_LENGTH'] ?? 6),
            'reiniciar_anual' => filter_var($_ENV['INVOICE_RESET_YEARLY'] ?? true, FILTER_VALIDATE_BOOLEAN)
        ]
    ],

    // =====================================
    // CONFIGURACIÓN DE REPORTES
    // =====================================
    'reportes' => [
        'items_per_page' => (int)($_ENV['REPORTS_PER_PAGE'] ?? 50),
        'export_formats' => ['pdf', 'excel', 'csv'],
        'cache_duration' => (int)($_ENV['REPORTS_CACHE'] ?? 1800) // 30 minutos
    ],

    // =====================================
    // CONFIGURACIÓN DE API (para futuras integraciones)
    // =====================================
    'api' => [
        'enabled' => filter_var($_ENV['API_ENABLED'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'version' => $_ENV['API_VERSION'] ?? 'v1',
        'rate_limit' => (int)($_ENV['API_RATE_LIMIT'] ?? 100), // requests per hour
        'auth_token_ttl' => (int)($_ENV['API_TOKEN_TTL'] ?? 86400) // 24 horas
    ],

    // =====================================
    // CONFIGURACIÓN DE BACKUP
    // =====================================
    'backup' => [
        'enabled' => filter_var($_ENV['BACKUP_ENABLED'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'path' => $_ENV['BACKUP_PATH'] ?? __DIR__ . '/../storage/backups',
        'frequency' => $_ENV['BACKUP_FREQUENCY'] ?? 'daily',
        'retention_days' => (int)($_ENV['BACKUP_RETENTION'] ?? 30),
        'compress' => filter_var($_ENV['BACKUP_COMPRESS'] ?? true, FILTER_VALIDATE_BOOLEAN)
    ]
];