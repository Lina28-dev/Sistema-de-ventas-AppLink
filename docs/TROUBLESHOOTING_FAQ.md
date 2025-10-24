# 🚨 Troubleshooting y FAQ - Sistema de Ventas AppLink

## 🎯 Guía de Solución de Problemas

Esta guía te ayudará a resolver los problemas más comunes del Sistema de Ventas AppLink y responde a las preguntas frecuentes de usuarios y desarrolladores.

## 📋 Tabla de Contenidos

1. [Problemas de Instalación](#instalación)
2. [Problemas de Conexión](#conexión)
3. [Problemas de Login y Autenticación](#autenticación)
4. [Problemas de Base de Datos](#base-datos)
5. [Problemas de Performance](#performance)
6. [Problemas de Imágenes y Assets](#assets)
7. [Errores de PHP](#php-errors)
8. [Problemas de APIs](#apis)
9. [FAQ - Preguntas Frecuentes](#faq)
10. [Contacto de Soporte](#soporte)

---

## 🔧 Problemas de Instalación {#instalación}

### **❌ Error: "Could not find driver" (PostgreSQL)**

#### **Síntomas:**
```
Fatal error: Uncaught PDOException: could not find driver
```

#### **Soluciones:**

**Windows (XAMPP):**
```ini
# En php.ini (C:\xampp\php\php.ini)
# Descomenta estas líneas:
extension=pdo_pgsql
extension=pgsql

# Reinicia Apache
```

**Linux:**
```bash
# Ubuntu/Debian
sudo apt install php-pgsql php-pdo

# CentOS/RHEL
sudo dnf install php-pgsql

# Reiniciar servidor web
sudo systemctl restart nginx php-fpm
```

**Docker:**
```dockerfile
# Verificar en Dockerfile
RUN docker-php-ext-install pdo pdo_pgsql
```

### **❌ Error: "Permission denied" al acceder a archivos**

#### **Síntomas:**
```
Warning: file_get_contents(): failed to open stream: Permission denied
```

#### **Soluciones:**

**Linux:**
```bash
# Corregir permisos
sudo chown -R www-data:www-data /var/www/applink/
sudo chmod -R 755 /var/www/applink/
sudo chmod -R 775 /var/www/applink/logs/

# Para archivos específicos
sudo chmod 644 /var/www/applink/.env
sudo chmod 755 /var/www/applink/autoload.php
```

**Windows:**
```powershell
# Dar permisos completos a la carpeta
icacls "C:\xampp\htdocs\Sistema-de-ventas-AppLink-main" /grant Everyone:F /T
```

### **❌ Error: "Autoload.php not found"**

#### **Síntomas:**
```
Fatal error: require_once(): Failed opening required 'autoload.php'
```

#### **Soluciones:**
```php
// Verificar rutas relativas en archivos movidos
// Incorrecto:
require_once 'autoload.php';

// Correcto:
require_once __DIR__ . '/../autoload.php';
require_once '../autoload.php';
```

### **❌ Error en migración de base de datos**

#### **Síntomas:**
```
ERROR: database "ventas_applink" does not exist
```

#### **Soluciones:**
```sql
-- Conectar como postgres
sudo -u postgres psql

-- Crear base de datos
CREATE DATABASE ventas_applink;
CREATE USER applink_user WITH ENCRYPTED PASSWORD 'password';
GRANT ALL PRIVILEGES ON DATABASE ventas_applink TO applink_user;
\q
```

---

## 🌐 Problemas de Conexión {#conexión}

### **❌ Error: "This site can't be reached"**

#### **Verificaciones:**

**1. Verificar servicios:**
```bash
# XAMPP
# Verificar en Panel de Control que Apache esté corriendo

# Linux
sudo systemctl status nginx
sudo systemctl status apache2
```

**2. Verificar puertos:**
```bash
# Verificar que el puerto 80 esté abierto
netstat -tlnp | grep :80
sudo lsof -i :80
```

**3. Verificar firewall:**
```bash
# Ubuntu
sudo ufw status
sudo ufw allow 80
sudo ufw allow 443

# CentOS
sudo firewall-cmd --list-all
sudo firewall-cmd --add-service=http --permanent
sudo firewall-cmd --reload
```

### **❌ Error 404: "Page not found"**

#### **Síntomas:**
```
The requested URL was not found on this server
```

#### **Soluciones:**

**1. Verificar archivo .htaccess:**
```apache
# .htaccess en directorio raíz
RewriteEngine On

# Redireccionar al directorio public si existe
RewriteCond %{REQUEST_URI} !^/public/
RewriteRule ^(.*)$ public/$1 [L]

# Para APIs
RewriteRule ^api/(.*)$ public/api/$1 [L]
```

**2. Verificar configuración Nginx:**
```nginx
# En /etc/nginx/sites-available/applink
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location /api/ {
    try_files $uri $uri/ /api/index.php?$query_string;
}
```

### **❌ Error 500: "Internal Server Error"**

#### **Diagnóstico:**
```bash
# Verificar logs de error
tail -f /var/log/nginx/error.log
tail -f /var/log/apache2/error.log
tail -f C:\xampp\apache\logs\error.log
```

#### **Soluciones Comunes:**

**1. Error de sintaxis PHP:**
```bash
# Verificar sintaxis
php -l archivo.php
find . -name "*.php" -exec php -l {} \;
```

**2. Memoria insuficiente:**
```ini
# En php.ini
memory_limit = 256M
max_execution_time = 60
```

**3. Permisos incorrectos:**
```bash
# Corregir permisos
sudo chown -R www-data:www-data /var/www/applink/
```

---

## 🔐 Problemas de Login y Autenticación {#autenticación}

### **❌ Error: "Invalid credentials"**

#### **Verificaciones:**

**1. Verificar usuario en base de datos:**
```sql
-- Conectar a PostgreSQL
psql -U applink_user ventas_applink

-- Verificar usuarios
SELECT id, usuario, email, activo FROM usuarios WHERE activo = true;

-- Verificar hash de contraseña
SELECT usuario, password FROM usuarios WHERE usuario = 'admin';
```

**2. Verificar hash de contraseña:**
```php
// Verificar hash en PHP
$password = 'tu_password';
$hash = '$2y$10$ejemplo_hash_aqui';

if (password_verify($password, $hash)) {
    echo "Password correcto";
} else {
    echo "Password incorrecto";
}
```

**3. Crear nuevo usuario admin:**
```php
// Script para crear usuario de emergencia
<?php
require_once 'autoload.php';

$database = new Database();
$pdo = $database->getConnection();

$username = 'admin';
$password = password_hash('admin123', PASSWORD_DEFAULT);
$email = 'admin@applink.com';

$sql = "INSERT INTO usuarios (nombre, apellido, usuario, password, email, rol, activo) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $pdo->prepare($sql);
$result = $stmt->execute(['Admin', 'Sistema', $username, $password, $email, 'admin', true]);

echo $result ? "Usuario creado exitosamente" : "Error al crear usuario";
?>
```

### **❌ Error: "Session expired"**

#### **Soluciones:**

**1. Verificar configuración de sesiones:**
```ini
# En php.ini
session.gc_maxlifetime = 7200
session.cookie_lifetime = 0
session.save_path = "/tmp"
```

**2. Limpiar sesiones:**
```php
// Limpiar sesiones manualmente
<?php
session_start();
session_destroy();
echo "Sesiones limpiadas";
?>
```

### **❌ Error: "CSRF token mismatch"**

#### **Soluciones:**

**1. Verificar implementación CSRF:**
```php
// En formularios
if (!isset($_POST['_token']) || $_POST['_token'] !== $_SESSION['csrf_token']) {
    die('Token CSRF inválido');
}
```

**2. Regenerar token:**
```php
// Generar nuevo token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
```

---

## 🗄️ Problemas de Base de Datos {#base-datos}

### **❌ Error: "Connection refused"**

#### **Síntomas:**
```
SQLSTATE[08006] [7] could not connect to server: Connection refused
```

#### **Verificaciones:**

**1. Verificar que PostgreSQL esté corriendo:**
```bash
# Linux
sudo systemctl status postgresql
sudo systemctl start postgresql

# Windows (XAMPP)
# Verificar en Panel de Control
```

**2. Verificar configuración de conexión:**
```bash
# Verificar .env
cat .env | grep DB_

# Testear conexión
php testing/test_connection.php
```

**3. Verificar puerto PostgreSQL:**
```bash
# Verificar puerto 5432
netstat -tlnp | grep 5432
sudo lsof -i :5432
```

### **❌ Error: "Database does not exist"**

#### **Soluciones:**

**1. Crear base de datos:**
```sql
sudo -u postgres psql
CREATE DATABASE ventas_applink;
\l  -- Listar bases de datos
\q
```

**2. Restaurar desde backup:**
```bash
# Si tienes un backup
psql -U applink_user ventas_applink < backup.sql
```

### **❌ Error: "Column does not exist"**

#### **Síntomas:**
```
ERROR: column "name" does not exist
```

#### **Soluciones:**

**1. Verificar estructura de tabla:**
```sql
-- Verificar columnas existentes
\d usuarios
\d fs_clientes

-- Verificar datos específicos
SELECT column_name FROM information_schema.columns 
WHERE table_name = 'usuarios';
```

**2. Ejecutar migración:**
```bash
# Re-ejecutar migración
php database/migrate_structure.php
```

### **❌ Error: "Too many connections"**

#### **Soluciones:**

**1. Verificar conexiones activas:**
```sql
-- Ver conexiones actuales
SELECT * FROM pg_stat_activity;

-- Terminar conexiones específicas
SELECT pg_terminate_backend(pid) 
FROM pg_stat_activity 
WHERE state = 'idle';
```

**2. Configurar pool de conexiones:**
```php
// Implementar singleton para conexiones
class DatabaseConnection {
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new PDO($dsn, $user, $pass);
        }
        return self::$instance;
    }
}
```

---

## ⚡ Problemas de Performance {#performance}

### **❌ Sistema lento en general**

#### **Diagnóstico:**

**1. Verificar recursos del servidor:**
```bash
# CPU y memoria
htop
free -h
df -h

# Procesos PHP
ps aux | grep php
```

**2. Verificar queries lentas:**
```sql
-- Habilitar log de queries lentas
ALTER SYSTEM SET log_min_duration_statement = 1000;
SELECT pg_reload_conf();

-- Ver queries lentas en logs
tail -f /var/log/postgresql/postgresql-13-main.log
```

#### **Optimizaciones:**

**1. Optimizar PHP:**
```ini
# php.ini
opcache.enable = 1
opcache.memory_consumption = 128
opcache.max_accelerated_files = 4000
```

**2. Optimizar PostgreSQL:**
```sql
-- Actualizar estadísticas
ANALYZE;

-- Reconstruir índices
REINDEX DATABASE ventas_applink;

-- Configurar memoria
ALTER SYSTEM SET shared_buffers = '256MB';
```

**3. Implementar caché:**
```php
// Cache simple con archivos
class SimpleCache {
    public static function get($key) {
        $file = "cache/{$key}.cache";
        if (file_exists($file) && (time() - filemtime($file)) < 3600) {
            return unserialize(file_get_contents($file));
        }
        return null;
    }
    
    public static function set($key, $data) {
        file_put_contents("cache/{$key}.cache", serialize($data));
    }
}
```

### **❌ Queries muy lentas**

#### **Soluciones:**

**1. Añadir índices:**
```sql
-- Índices para búsquedas frecuentes
CREATE INDEX idx_productos_nombre ON fs_productos(nombre);
CREATE INDEX idx_clientes_documento ON fs_clientes(numero_documento);
CREATE INDEX idx_ventas_fecha ON fs_ventas(fecha_venta);
```

**2. Optimizar queries:**
```php
// ❌ Malo - Query N+1
foreach ($pedidos as $pedido) {
    $cliente = getClienteById($pedido['cliente_id']);
}

// ✅ Bueno - Join
$sql = "
    SELECT p.*, c.nombre as cliente_nombre
    FROM fs_pedidos p
    INNER JOIN fs_clientes c ON p.cliente_id = c.id
";
```

---

## 🖼️ Problemas de Imágenes y Assets {#assets}

### **❌ Error: "Images not loading"**

#### **Síntomas:**
Las imágenes muestran el icono de "imagen rota" o error 404.

#### **Verificaciones:**

**1. Verificar rutas de imágenes:**
```php
// Verificar rutas en código
// ❌ Incorrecto
echo '<img src="img/logo.jpg">';

// ✅ Correcto
echo '<img src="/Sistema-de-ventas-AppLink-main/public/assets/images/logo.jpg">';
```

**2. Verificar que las imágenes existan:**
```bash
# Verificar directorio de imágenes
ls -la public/assets/images/
find public/assets/images/ -name "*.jpg" -o -name "*.png"
```

**3. Verificar permisos:**
```bash
# Dar permisos de lectura
chmod 644 public/assets/images/*
chmod 755 public/assets/images/
```

### **❌ Error: "Failed to load CSS/JS"**

#### **Soluciones:**

**1. Verificar rutas de assets:**
```html
<!-- ❌ Incorrecto -->
<link rel="stylesheet" href="css/style.css">

<!-- ✅ Correcto -->
<link rel="stylesheet" href="/Sistema-de-ventas-AppLink-main/public/assets/css/style.css">
```

**2. Verificar configuración del servidor:**
```nginx
# Nginx - configurar tipos MIME
location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
    access_log off;
}
```

**3. Usar helper de assets:**
```php
// app/Helpers/AssetHelper.php
class AssetHelper {
    public static function asset($path) {
        $baseUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
        $basePath = '/Sistema-de-ventas-AppLink-main/public/assets/';
        return $baseUrl . $basePath . ltrim($path, '/');
    }
}

// Uso en templates
echo '<img src="' . AssetHelper::asset('images/logo.jpg') . '">';
```

---

## 🐛 Errores de PHP {#php-errors}

### **❌ Error: "Class not found"**

#### **Síntomas:**
```
Fatal error: Uncaught Error: Class 'UserService' not found
```

#### **Soluciones:**

**1. Verificar autoloader:**
```php
// Verificar que el autoloader esté incluido
require_once __DIR__ . '/autoload.php';

// Verificar namespace
use App\Services\Business\UserService;
$service = new UserService();
```

**2. Verificar estructura de directorios:**
```
app/Services/Business/UserService.php debe contener:
<?php
namespace App\Services\Business;
class UserService { ... }
```

### **❌ Error: "Call to undefined function"**

#### **Síntomas:**
```
Fatal error: Call to undefined function pg_connect()
```

#### **Soluciones:**

**1. Verificar extensiones PHP:**
```bash
php -m | grep pgsql
php -i | grep pgsql
```

**2. Instalar extensiones faltantes:**
```bash
# Ubuntu
sudo apt install php-pgsql php-mbstring php-curl

# CentOS
sudo dnf install php-pgsql php-mbstring php-curl
```

### **❌ Error: "Maximum execution time exceeded"**

#### **Soluciones:**

**1. Aumentar tiempo de ejecución:**
```ini
# php.ini
max_execution_time = 300
max_input_time = 300
```

**2. Optimizar código:**
```php
// ❌ Malo - loop infinito potencial
while (true) {
    // código sin break
}

// ✅ Bueno - con límite
$maxIterations = 1000;
$counter = 0;
while ($condition && $counter < $maxIterations) {
    // código
    $counter++;
}
```

---

## 🔌 Problemas de APIs {#apis}

### **❌ Error: "API key invalid"**

#### **Síntomas:**
```json
{
    "success": false,
    "error": {
        "code": "AUTH_ERROR",
        "message": "API key inválida"
    }
}
```

#### **Soluciones:**

**1. Verificar API key:**
```php
// Generar nueva API key
$apiKey = 'ak_live_' . bin2hex(random_bytes(16));
echo "Nueva API key: " . $apiKey;
```

**2. Verificar headers:**
```javascript
// Verificar que el header esté correcto
fetch('/api/v1/users', {
    headers: {
        'Authorization': 'Bearer ak_live_1234567890abcdef',
        'Content-Type': 'application/json'
    }
});
```

### **❌ Error: "CORS blocked"**

#### **Síntomas:**
```
Access to fetch at 'api/users' from origin 'localhost:3000' has been blocked by CORS policy
```

#### **Soluciones:**

**1. Configurar CORS en PHP:**
```php
// En api/index.php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
```

**2. Configurar CORS en Nginx:**
```nginx
location /api/ {
    add_header Access-Control-Allow-Origin *;
    add_header Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS";
    add_header Access-Control-Allow-Headers "Content-Type, Authorization";
    
    if ($request_method = 'OPTIONS') {
        return 200;
    }
    
    try_files $uri $uri/ /api/index.php?$query_string;
}
```

---

## ❓ FAQ - Preguntas Frecuentes {#faq}

### **🔧 Instalación y Configuración**

#### **P: ¿Puedo usar MySQL en lugar de PostgreSQL?**
**R:** Sí, aunque el sistema ha migrado oficialmente a PostgreSQL, aún mantiene compatibilidad con MySQL. Usa los archivos de configuración con sufijo `_mysql_backup`.

#### **P: ¿Funciona en PHP 7.4?**
**R:** El sistema está optimizado para PHP 8.0+, pero puede funcionar en PHP 7.4. Algunas características modernas podrían no estar disponibles.

#### **P: ¿Puedo instalarlo en hosting compartido?**
**R:** Sí, pero asegúrate de que tu hosting soporte:
- PHP 8.0+
- PostgreSQL o MySQL
- Permisos de escritura en directorios
- Posibilidad de editar .htaccess

### **🔐 Seguridad**

#### **P: ¿Cómo cambio las credenciales por defecto?**
**R:** 
```sql
-- Cambiar contraseña de admin
UPDATE usuarios SET password = ? WHERE usuario = 'admin';
-- Usar password_hash() para generar el hash
```

#### **P: ¿El sistema es seguro para producción?**
**R:** Sí, implementa:
- Prepared statements (previene SQL injection)
- Password hashing con bcrypt
- CSRF protection
- Session security
- Input validation

#### **P: ¿Cómo habilito HTTPS?**
**R:** Sigue la sección SSL en la Guía de Deployment. Para desarrollo local, HTTPS no es necesario.

### **💰 Funcionalidades de Ventas**

#### **P: ¿Puedo configurar diferentes tipos de impuestos?**
**R:** Sí, puedes modificar los porcentajes en la configuración. El sistema calcula IVA por defecto al 19%.

#### **P: ¿Soporta múltiples monedas?**
**R:** Actualmente está diseñado para una moneda (COP), pero se puede extender fácilmente.

#### **P: ¿Cómo genero reportes personalizados?**
**R:** Usa la sección de reportes en el admin o consulta directamente la base de datos con SQL personalizado.

### **🔧 Desarrollo**

#### **P: ¿Cómo añado nuevos campos a productos?**
**R:**
1. Modifica la tabla `fs_productos`
2. Actualiza el modelo `Producto.php`
3. Modifica las vistas correspondientes
4. Actualiza las validaciones

#### **P: ¿Cómo creo un nuevo módulo?**
**R:** Sigue la estructura MVC:
1. Crear modelo en `src/Models/`
2. Crear controlador en `src/Controllers/`
3. Crear vistas en `src/Views/`
4. Añadir rutas

#### **P: ¿Puedo usar el sistema como API únicamente?**
**R:** Sí, puedes usar solo los endpoints de la API en `/public/api/v1/` sin la interfaz web.

### **🛠️ Mantenimiento**

#### **P: ¿Cómo hago backup de la base de datos?**
**R:**
```bash
# PostgreSQL
pg_dump -U usuario basedatos > backup.sql

# MySQL
mysqldump -u usuario -p basedatos > backup.sql
```

#### **P: ¿Con qué frecuencia debo hacer mantenimiento?**
**R:**
- **Backup diario:** Automático con cron
- **Limpieza de logs:** Semanal
- **Optimización DB:** Mensual
- **Actualizaciones:** Según releases

#### **P: ¿Cómo actualizo a una nueva versión?**
**R:**
1. Hacer backup completo
2. Descargar nueva versión
3. Ejecutar migraciones si las hay
4. Probar funcionalidad
5. Restaurar desde backup si hay problemas

### **📱 Integración**

#### **P: ¿Puedo integrar con sistemas externos?**
**R:** Sí, usa la API REST para integrar con:
- Sistemas de inventario
- Plataformas de e-commerce
- Software contable
- CRM externos

#### **P: ¿Soporta plugins o extensiones?**
**R:** Actualmente no tiene un sistema formal de plugins, pero el código es extensible siguiendo los patrones establecidos.

### **🔍 Troubleshooting**

#### **P: El sistema está muy lento, ¿qué hago?**
**R:**
1. Verificar recursos del servidor (RAM, CPU)
2. Optimizar base de datos (VACUUM, REINDEX)
3. Habilitar OPcache
4. Revisar queries lentas
5. Implementar caché

#### **P: ¿Dónde encuentro los logs de error?**
**R:**
- **Sistema:** `logs/` en el directorio del proyecto
- **Servidor:** `/var/log/nginx/` o `/var/log/apache2/`
- **PHP:** Configurado en `php.ini`
- **Base de datos:** `/var/log/postgresql/`

---

## 📞 Contacto de Soporte {#soporte}

### **🆘 Soporte Técnico**

#### **GitHub Issues**
- **URL:** https://github.com/Lina28-dev/Sistema-de-ventas-AppLink/issues
- **Para:** Bugs, solicitudes de características, problemas técnicos

#### **Documentación**
- **Wiki:** Documentación adicional y tutoriales
- **Releases:** Notas de cambios y actualizaciones

### **📧 Contacto Directo**

#### **Desarrolladora Principal**
- **GitHub:** @Lina28-dev
- **Para:** Consultas de desarrollo, contribuciones

### **🤝 Comunidad**

#### **Cómo Contribuir**
1. Fork del repositorio
2. Crear branch para tu feature
3. Hacer commit de cambios
4. Crear Pull Request
5. Esperar review

#### **Reportar Problemas**
Cuando reportes un problema, incluye:
- **Versión del sistema**
- **Entorno** (OS, PHP, DB version)
- **Pasos para reproducir**
- **Logs de error**
- **Capturas de pantalla** (si aplica)

---

## 🔄 Scripts de Diagnóstico

### **Script de Diagnóstico Completo**

```bash
#!/bin/bash
# diagnosis.sh - Diagnóstico completo del sistema

echo "=== SISTEMA DE VENTAS APPLINK - DIAGNÓSTICO ==="
echo "Fecha: $(date)"
echo ""

# Información del sistema
echo "=== INFORMACIÓN DEL SISTEMA ==="
uname -a
echo "PHP Version: $(php -v | head -n1)"
echo ""

# Verificar servicios
echo "=== SERVICIOS ==="
systemctl is-active nginx 2>/dev/null && echo "✅ Nginx" || echo "❌ Nginx"
systemctl is-active postgresql 2>/dev/null && echo "✅ PostgreSQL" || echo "❌ PostgreSQL"
systemctl is-active php8.0-fpm 2>/dev/null && echo "✅ PHP-FPM" || echo "❌ PHP-FPM"
echo ""

# Verificar conectividad
echo "=== CONECTIVIDAD ==="
curl -s http://localhost >/dev/null && echo "✅ HTTP" || echo "❌ HTTP"
echo ""

# Verificar base de datos
echo "=== BASE DE DATOS ==="
php -r "
try {
    \$config = parse_ini_file('.env');
    \$dsn = 'pgsql:host=' . \$config['DB_HOST'] . ';dbname=' . \$config['DB_DATABASE'];
    \$pdo = new PDO(\$dsn, \$config['DB_USERNAME'], \$config['DB_PASSWORD']);
    echo '✅ Conexión PostgreSQL\n';
    
    \$tables = \$pdo->query('SELECT tablename FROM pg_tables WHERE schemaname = \'public\'')->fetchAll();
    echo 'Tablas encontradas: ' . count(\$tables) . '\n';
} catch (Exception \$e) {
    echo '❌ Error DB: ' . \$e->getMessage() . '\n';
}
"
echo ""

# Verificar archivos críticos
echo "=== ARCHIVOS CRÍTICOS ==="
[ -f "autoload.php" ] && echo "✅ autoload.php" || echo "❌ autoload.php"
[ -f ".env" ] && echo "✅ .env" || echo "❌ .env"
[ -f "public/index.php" ] && echo "✅ public/index.php" || echo "❌ public/index.php"
echo ""

# Verificar permisos
echo "=== PERMISOS ==="
[ -w "logs" ] && echo "✅ logs/ writable" || echo "❌ logs/ not writable"
[ -r ".env" ] && echo "✅ .env readable" || echo "❌ .env not readable"
echo ""

# Verificar espacio en disco
echo "=== RECURSOS ==="
echo "Espacio en disco:"
df -h / | grep -v Filesystem
echo "Memoria:"
free -h | grep -v total
echo ""

echo "=== FIN DEL DIAGNÓSTICO ==="
```

**🎯 ¡Esta guía de troubleshooting te ayudará a resolver la mayoría de problemas comunes!**

**💡 Tip:** Mantén este documento a mano y contribuye con nuevos problemas y soluciones que encuentres.

---

*📝 Documentación actualizada el: Octubre 2025 | Versión: 2.0*  
*👩‍💻 Desarrollado por: Lina28-dev*