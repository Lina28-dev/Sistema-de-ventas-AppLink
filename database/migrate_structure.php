<?php
/**
 * SCRIPT DE MIGRACIÓN A NUEVA ESTRUCTURA
 * Este script reorganizará el proyecto actual a la nueva estructura propuesta
 */

class ProjectRestructure {
    
    private $basePath;
    private $backupPath;
    
    public function __construct($basePath) {
        $this->basePath = rtrim($basePath, '/');
        $this->backupPath = $this->basePath . '_backup_' . date('Y-m-d_H-i-s');
    }
    
    /**
     * Ejecutar la reestructuración completa
     */
    public function migrate() {
        echo "🚀 INICIANDO REESTRUCTURACIÓN DEL PROYECTO\n\n";
        
        // 1. Crear backup
        $this->createBackup();
        
        // 2. Crear nueva estructura de carpetas
        $this->createNewStructure();
        
        // 3. Mover archivos existentes
        $this->moveExistingFiles();
        
        // 4. Crear archivos de configuración
        $this->createConfigFiles();
        
        // 5. Actualizar autoloader
        $this->updateAutoloader();
        
        // 6. Crear archivos de documentación
        $this->createDocumentation();
        
        echo "\n✅ REESTRUCTURACIÓN COMPLETADA EXITOSAMENTE\n";
        echo "📁 Backup creado en: {$this->backupPath}\n";
    }
    
    /**
     * Crear backup completo del proyecto actual
     */
    private function createBackup() {
        echo "📦 Creando backup del proyecto actual...\n";
        
        if (!file_exists($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }
        
        // Copiar todo el contenido actual
        $this->copyDirectory($this->basePath, $this->backupPath);
        
        echo "✅ Backup creado exitosamente\n\n";
    }
    
    /**
     * Crear la nueva estructura de carpetas
     */
    private function createNewStructure() {
        echo "📁 Creando nueva estructura de carpetas...\n";
        
        $folders = [
            // App
            'app/Controllers/API/v1',
            'app/Controllers/API/v2', 
            'app/Controllers/Web',
            'app/Models/Entities',
            'app/Models/Repositories',
            'app/Models/Factories',
            'app/Services/Auth',
            'app/Services/Business',
            'app/Services/External',
            'app/Services/Validation',
            'app/Middleware',
            'app/Helpers',
            
            // Config
            'config/environments',
            
            // Database
            'database/migrations/postgresql',
            'database/migrations/mysql',
            'database/seeders',
            'database/backups',
            
            // Public
            'public/assets/css/components',
            'public/assets/css/pages',
            'public/assets/js/components',
            'public/assets/js/pages',
            'public/assets/js/vendors',
            'public/assets/images/icons',
            'public/assets/images/products',
            'public/assets/images/ui',
            'public/assets/fonts',
            'public/api/v1',
            'public/api/v2',
            'public/uploads/documents',
            'public/uploads/images',
            'public/uploads/temp',
            
            // Resources
            'resources/views/layouts',
            'resources/views/pages/dashboard',
            'resources/views/pages/sales',
            'resources/views/pages/clients',
            'resources/views/pages/reports',
            'resources/views/components',
            'resources/views/auth',
            'resources/emails',
            'resources/lang/es',
            'resources/lang/en',
            
            // Storage
            'storage/logs/app',
            'storage/logs/api',
            'storage/logs/auth',
            'storage/logs/errors',
            'storage/cache/views',
            'storage/cache/data',
            'storage/sessions',
            'storage/uploads',
            
            // Tests
            'tests/Unit/Models',
            'tests/Unit/Services',
            'tests/Unit/Helpers',
            'tests/Integration/API',
            'tests/Integration/Database',
            'tests/Feature',
            
            // Docs
            'docs/api',
            'docs/deployment',
            'docs/development',
            
            // Scripts
            'scripts/deployment',
            'scripts/migration',
            'scripts/backup',
            'scripts/maintenance'
        ];
        
        foreach ($folders as $folder) {
            $path = $this->basePath . '/' . $folder;
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
                echo "  📁 Creada: $folder\n";
            }
        }
        
        echo "✅ Estructura de carpetas creada\n\n";
    }
    
    /**
     * Mover archivos existentes a la nueva estructura
     */
    private function moveExistingFiles() {
        echo "📦 Moviendo archivos existentes...\n";
        
        $moves = [
            // Controllers
            'src/Controllers/ApiController.php' => 'app/Controllers/API/v1/BaseApiController.php',
            'src/Controllers/AuthController.php' => 'app/Controllers/Web/AuthController.php',
            'src/Controllers/ClienteController.php' => 'app/Controllers/API/v1/ClientController.php',
            'src/Controllers/VentaController.php' => 'app/Controllers/API/v1/SaleController.php',
            'src/Controllers/PedidoController.php' => 'app/Controllers/API/v1/OrderController.php',
            'src/Controllers/ProductoController.php' => 'app/Controllers/API/v1/ProductController.php',
            'src/Controllers/UsuarioController.php' => 'app/Controllers/API/v1/UserController.php',
            
            // Models
            'src/Models/Cliente.php' => 'app/Models/Entities/Client.php',
            'src/Models/Usuario.php' => 'app/Models/Entities/User.php',
            'src/Models/Venta.php' => 'app/Models/Entities/Sale.php',
            'src/Models/Pedido.php' => 'app/Models/Entities/Order.php',
            'src/Models/Producto.php' => 'app/Models/Entities/Product.php',
            'src/Models/ClienteRepository.php' => 'app/Models/Repositories/ClientRepository.php',
            
            // Utils
            'src/Utils/Database.php' => 'app/Services/Database/DatabaseService.php',
            'src/Utils/ErrorHandler.php' => 'app/Services/ErrorHandler.php',
            'src/Utils/Logger.php' => 'app/Services/LoggerService.php',
            'src/Utils/ValidadorService.php' => 'app/Services/Validation/ValidationService.php',
            'src/Utils/CSRFToken.php' => 'app/Services/Auth/CSRFService.php',
            
            // Views
            'src/Views/dashboard.php' => 'resources/views/pages/dashboard/index.php',
            'src/Views/clientes.php' => 'resources/views/pages/clients/index.php',
            'src/Views/ventas.php' => 'resources/views/pages/sales/index.php',
            'src/Views/pedidos.php' => 'resources/views/pages/orders/index.php',
            'src/Views/usuarios.php' => 'resources/views/pages/users/index.php',
            'src/Views/reportes.php' => 'resources/views/pages/reports/index.php',
            'src/Views/auth/login.php' => 'resources/views/auth/login.php',
            'src/Views/partials/header.php' => 'resources/views/components/header.php',
            'src/Views/partials/sidebar.php' => 'resources/views/components/sidebar.php',
            
            // Config
            'config/app.php' => 'config/app.php',
            'config/Database.php' => 'config/database.php',
            
            // Public assets
            'public/css/' => 'public/assets/css/',
            'public/js/' => 'public/assets/js/',
            'public/img/' => 'public/assets/images/',
            
            // APIs
            'api/usuarios.php' => 'public/api/v1/users.php',
            'api/clientes.php' => 'public/api/v1/clients.php',
            'api/ventas.php' => 'public/api/v1/sales.php',
            'api/pedidos.php' => 'public/api/v1/orders.php',
            
            // Database
            'migrations/' => 'database/migrations/postgresql/',
            'tests/' => 'tests/',
            
            // Docs
            'README.md' => 'docs/README.md'
        ];
        
        foreach ($moves as $from => $to) {
            $fromPath = $this->basePath . '/' . $from;
            $toPath = $this->basePath . '/' . $to;
            
            if (file_exists($fromPath)) {
                // Crear directorio destino si no existe
                $toDir = dirname($toPath);
                if (!file_exists($toDir)) {
                    mkdir($toDir, 0755, true);
                }
                
                if (is_dir($fromPath)) {
                    $this->copyDirectory($fromPath, $toPath);
                } else {
                    copy($fromPath, $toPath);
                }
                echo "  📦 Movido: $from → $to\n";
            }
        }
        
        echo "✅ Archivos movidos exitosamente\n\n";
    }
    
    /**
     * Crear archivos de configuración necesarios
     */
    private function createConfigFiles() {
        echo "⚙️ Creando archivos de configuración...\n";
        
        // Crear composer.json
        $this->createComposerJson();
        
        // Crear .env.example
        $this->createEnvExample();
        
        // Crear .gitignore
        $this->createGitignore();
        
        // Crear phpunit.xml
        $this->createPhpunitXml();
        
        echo "✅ Archivos de configuración creados\n\n";
    }
    
    /**
     * Copiar directorio recursivamente
     */
    private function copyDirectory($src, $dst) {
        if (!file_exists($dst)) {
            mkdir($dst, 0755, true);
        }
        
        $dir = opendir($src);
        while (($file = readdir($dir)) !== false) {
            if ($file != '.' && $file != '..') {
                $srcFile = $src . '/' . $file;
                $dstFile = $dst . '/' . $file;
                
                if (is_dir($srcFile)) {
                    $this->copyDirectory($srcFile, $dstFile);
                } else {
                    copy($srcFile, $dstFile);
                }
            }
        }
        closedir($dir);
    }
    
    /**
     * Crear composer.json
     */
    private function createComposerJson() {
        $composer = [
            'name' => 'applink/sistema-ventas',
            'description' => 'Sistema de Ventas AppLink - Reestructurado',
            'type' => 'project',
            'require' => [
                'php' => '>=7.4'
            ],
            'require-dev' => [
                'phpunit/phpunit' => '^9.0'
            ],
            'autoload' => [
                'psr-4' => [
                    'App\\' => 'app/',
                    'Database\\' => 'database/'
                ]
            ],
            'autoload-dev' => [
                'psr-4' => [
                    'Tests\\' => 'tests/'
                ]
            ]
        ];
        
        file_put_contents(
            $this->basePath . '/composer.json',
            json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }
    
    /**
     * Crear .env.example
     */
    private function createEnvExample() {
        $envContent = <<<ENV
# Configuración de la aplicación
APP_NAME="Sistema de Ventas AppLink"
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost

# Base de datos
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=ventas_applink
DB_USERNAME=applink_user
DB_PASSWORD=applink_2024!

# Configuración de sesión
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Configuración de correo
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls

# Configuración de logs
LOG_CHANNEL=daily
LOG_LEVEL=debug

# JWT Secret (para futuras implementaciones)
JWT_SECRET=your-secret-key-here
ENV;
        
        file_put_contents($this->basePath . '/.env.example', $envContent);
    }
    
    /**
     * Crear .gitignore
     */
    private function createGitignore() {
        $gitignoreContent = <<<GITIGNORE
# Environment files
.env
.env.local
.env.production

# Vendor
/vendor/

# Storage
/storage/logs/*
/storage/cache/*
/storage/sessions/*
!/storage/logs/.gitkeep
!/storage/cache/.gitkeep
!/storage/sessions/.gitkeep

# Uploads
/public/uploads/*
!/public/uploads/.gitkeep

# Composer
composer.lock

# IDE
.vscode/
.idea/
*.swp
*.swo

# OS
.DS_Store
Thumbs.db

# Backups
*_backup_*

# Temp files
*.tmp
*.temp
GITIGNORE;
        
        file_put_contents($this->basePath . '/.gitignore', $gitignoreContent);
    }
    
    /**
     * Crear phpunit.xml
     */
    private function createPhpunitXml() {
        $phpunitContent = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="vendor/autoload.php"
         colors="true"
         verbose="true">
    <testsuites>
        <testsuite name="Unit">
            <directory suffix="Test.php">./tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory suffix="Test.php">./tests/Feature</directory>
        </testsuite>
        <testsuite name="Integration">
            <directory suffix="Test.php">./tests/Integration</directory>
        </testsuite>
    </testsuites>
    <coverage>
        <include>
            <directory suffix=".php">./app</directory>
        </include>
    </coverage>
</phpunit>
XML;
        
        file_put_contents($this->basePath . '/phpunit.xml', $phpunitContent);
    }
    
    /**
     * Actualizar autoloader
     */
    private function updateAutoloader() {
        echo "🔄 Configurando autoloader...\n";
        
        // Crear autoloader básico si no existe Composer
        $autoloaderContent = <<<PHP
<?php
/**
 * Autoloader para el Sistema de Ventas AppLink
 */

spl_autoload_register(function (\$className) {
    \$prefix = 'App\\\\';
    \$baseDir = __DIR__ . '/app/';
    
    \$len = strlen(\$prefix);
    if (strncmp(\$prefix, \$className, \$len) !== 0) {
        return;
    }
    
    \$relativeClass = substr(\$className, \$len);
    \$file = \$baseDir . str_replace('\\\\', '/', \$relativeClass) . '.php';
    
    if (file_exists(\$file)) {
        require \$file;
    }
});

// Autoloader para Database
spl_autoload_register(function (\$className) {
    \$prefix = 'Database\\\\';
    \$baseDir = __DIR__ . '/database/';
    
    \$len = strlen(\$prefix);
    if (strncmp(\$prefix, \$className, \$len) !== 0) {
        return;
    }
    
    \$relativeClass = substr(\$className, \$len);
    \$file = \$baseDir . str_replace('\\\\', '/', \$relativeClass) . '.php';
    
    if (file_exists(\$file)) {
        require \$file;
    }
});
PHP;
        
        file_put_contents($this->basePath . '/autoload.php', $autoloaderContent);
        echo "✅ Autoloader configurado\n\n";
    }
    
    /**
     * Crear documentación
     */
    private function createDocumentation() {
        echo "📚 Creando documentación...\n";
        
        // README principal
        $readmeContent = <<<MD
# 🚀 Sistema de Ventas AppLink - Reestructurado

## 📋 Descripción
Sistema de ventas completo con PostgreSQL, APIs RESTful y dashboard administrativo.

## 🏗️ Arquitectura
- **Backend**: PHP 7.4+
- **Base de Datos**: PostgreSQL
- **Frontend**: HTML5, CSS3, JavaScript
- **APIs**: RESTful con versionado

## 📁 Estructura del Proyecto
```
app/              # Core de la aplicación
config/           # Configuraciones
database/         # Migraciones y seeders
public/           # Archivos públicos y APIs
resources/        # Vistas y recursos
storage/          # Logs y cache
tests/            # Tests automatizados
docs/             # Documentación
scripts/          # Scripts de automatización
```

## 🚀 Instalación
1. Clonar el repositorio
2. Copiar `.env.example` a `.env` y configurar
3. Ejecutar `composer install`
4. Configurar base de datos PostgreSQL
5. Ejecutar migraciones

## 📖 Documentación
- [Instalación](docs/INSTALLATION.md)
- [API Reference](docs/API_REFERENCE.md)
- [Desarrollo](docs/development/)

## 🧪 Testing
```bash
./vendor/bin/phpunit
```

## 📝 Licencia
Propietario - AppLink
MD;
        
        file_put_contents($this->basePath . '/README.md', $readmeContent);
        echo "✅ Documentación creada\n\n";
    }
}

// Ejecutar si se llama directamente
if (php_sapi_name() === 'cli') {
    $basePath = __DIR__;
    $restructure = new ProjectRestructure($basePath);
    $restructure->migrate();
}
?>