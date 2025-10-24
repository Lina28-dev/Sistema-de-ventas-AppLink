# 🚀 Guía de Deployment - Sistema de Ventas AppLink

## 🎯 Introducción al Deployment

Esta guía te ayudará a desplegar el Sistema de Ventas AppLink en diferentes entornos, desde desarrollo local hasta producción en la nube.

## 📋 Tabla de Contenidos

1. [Preparación Pre-Deployment](#preparación)
2. [Deployment Local (XAMPP)](#local)
3. [Deployment en Servidor Linux](#linux)
4. [Deployment con Docker](#docker)
5. [Deployment en la Nube](#nube)
6. [Configuración de Producción](#producción)
7. [Monitoreo y Mantenimiento](#monitoreo)
8. [Backup y Recuperación](#backup)
9. [SSL y Seguridad](#ssl)
10. [Troubleshooting](#troubleshooting)

---

## 🛠️ Preparación Pre-Deployment {#preparación}

### **Checklist Pre-Deployment**

#### **✅ Código**
```bash
# Verificar que todo esté en Git
git status
git log --oneline -5

# Verificar tests
php vendor/bin/phpunit
php testing/test_connection.php

# Verificar configuración
php -l config/app.php
php -l autoload.php
```

#### **✅ Base de Datos**
```sql
-- Verificar estructura
\d fs_usuarios
\d fs_clientes
\d fs_productos

-- Verificar datos críticos
SELECT COUNT(*) FROM fs_categorias;
SELECT COUNT(*) FROM usuarios WHERE rol = 'admin';
```

#### **✅ Assets y Archivos**
```bash
# Verificar imágenes
ls -la public/assets/images/
du -sh public/assets/

# Verificar permisos
find . -name "*.php" -exec php -l {} \;
```

### **Variables de Entorno**

#### **Archivo .env para Producción**
```env
# === CONFIGURACIÓN DE PRODUCCIÓN ===
APP_ENV=production
APP_DEBUG=false
APP_KEY=your_secure_production_key_here
APP_URL=https://tu-dominio.com

# === BASE DE DATOS ===
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=ventas_applink_prod
DB_USERNAME=applink_user
DB_PASSWORD=secure_password_here

# === SEGURIDAD ===
SESSION_LIFETIME=7200
SESSION_SECURE=true
SESSION_HTTPONLY=true
CSRF_TOKEN_NAME=_token

# === EMAIL ===
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-app-password
MAIL_ENCRYPTION=tls

# === LOGS ===
LOG_CHANNEL=file
LOG_LEVEL=error
LOG_MAX_FILES=30

# === API ===
API_RATE_LIMIT=1000
API_TIMEOUT=30
```

---

## 💻 Deployment Local (XAMPP) {#local}

### **Instalación Completa en XAMPP**

#### **Paso 1: Preparar XAMPP**
```bash
# Windows
# Descargar XAMPP 8.0+ desde apachefriends.org
# Instalar en C:\xampp\

# Iniciar servicios
C:\xampp\xampp-control.exe
# Activar: Apache, PostgreSQL (o MySQL)
```

#### **Paso 2: Clonar Proyecto**
```bash
cd C:\xampp\htdocs\
git clone https://github.com/Lina28-dev/Sistema-de-ventas-AppLink.git
cd Sistema-de-ventas-AppLink
```

#### **Paso 3: Configurar Base de Datos**
```sql
-- PostgreSQL
CREATE DATABASE ventas_applink_local;
CREATE USER applink_local WITH ENCRYPTED PASSWORD 'password123';
GRANT ALL PRIVILEGES ON DATABASE ventas_applink_local TO applink_local;
```

#### **Paso 4: Configurar Aplicación**
```bash
# Copiar configuración
cp deployment/.env.example .env

# Editar .env con datos locales
nano .env  # o usar editor preferido
```

#### **Paso 5: Ejecutar Migración**
```bash
# Abrir navegador
http://localhost/Sistema-de-ventas-AppLink/database/migrate_structure.php

# Seguir asistente de instalación
```

#### **Paso 6: Verificar Instalación**
```bash
# Acceder al sistema
http://localhost/Sistema-de-ventas-AppLink/

# Login de prueba
Usuario: admin
Password: (configurado durante instalación)
```

---

## 🐧 Deployment en Servidor Linux {#linux}

### **Ubuntu/Debian Server Setup**

#### **Paso 1: Preparar Servidor**
```bash
# Actualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar paquetes necesarios
sudo apt install -y nginx postgresql postgresql-contrib php8.0-fpm php8.0-pgsql php8.0-mbstring php8.0-xml php8.0-curl php8.0-json php8.0-zip php8.0-gd

# Instalar Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

#### **Paso 2: Configurar PostgreSQL**
```bash
# Configurar PostgreSQL
sudo -u postgres psql

-- En psql:
CREATE DATABASE ventas_applink_prod;
CREATE USER applink_prod WITH ENCRYPTED PASSWORD 'secure_password_here';
GRANT ALL PRIVILEGES ON DATABASE ventas_applink_prod TO applink_prod;
\q
```

#### **Paso 3: Clonar y Configurar Proyecto**
```bash
# Crear directorio web
sudo mkdir -p /var/www/applink
cd /var/www/applink

# Clonar proyecto
sudo git clone https://github.com/Lina28-dev/Sistema-de-ventas-AppLink.git .

# Configurar permisos
sudo chown -R www-data:www-data /var/www/applink
sudo chmod -R 755 /var/www/applink
sudo chmod -R 775 /var/www/applink/logs

# Configurar variables de entorno
sudo cp deployment/.env.example .env
sudo nano .env  # Editar con configuración de producción
```

#### **Paso 4: Configurar Nginx**
```bash
# Crear configuración de Nginx
sudo nano /etc/nginx/sites-available/applink
```

```nginx
# /etc/nginx/sites-available/applink
server {
    listen 80;
    server_name tu-dominio.com www.tu-dominio.com;
    root /var/www/applink/public;
    index index.php index.html;

    # Logs
    access_log /var/log/nginx/applink_access.log;
    error_log /var/log/nginx/applink_error.log;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;

    # PHP handling
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # Static files
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # API routes
    location /api/ {
        try_files $uri $uri/ /api/index.php?$query_string;
    }

    # Main application
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Deny access to sensitive files
    location ~ /\. {
        deny all;
    }
    
    location ~ /(config|deployment|testing|database)/ {
        deny all;
    }
}
```

#### **Paso 5: Activar Sitio**
```bash
# Activar configuración
sudo ln -s /etc/nginx/sites-available/applink /etc/nginx/sites-enabled/

# Verificar configuración
sudo nginx -t

# Reiniciar servicios
sudo systemctl restart nginx
sudo systemctl restart php8.0-fpm
```

#### **Paso 6: Ejecutar Migración**
```bash
# Ejecutar migración desde línea de comandos
cd /var/www/applink
php database/migrate_structure.php

# O via web
curl http://tu-dominio.com/database/migrate_structure.php
```

### **CentOS/RHEL Setup**

#### **Instalación en CentOS**
```bash
# Instalar repositorios
sudo dnf install -y epel-release
sudo dnf module enable postgresql:13 php:8.0

# Instalar paquetes
sudo dnf install -y nginx postgresql-server postgresql-contrib php php-fpm php-pgsql php-mbstring php-xml php-curl php-json php-zip php-gd

# Inicializar PostgreSQL
sudo postgresql-setup --initdb
sudo systemctl enable --now postgresql nginx php-fpm
```

---

## 🐳 Deployment con Docker {#docker}

### **Docker Compose para Producción**

#### **docker-compose.prod.yml**
```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: deployment/Dockerfile.prod
    container_name: applink_app
    restart: unless-stopped
    volumes:
      - ./logs:/var/www/html/logs
      - ./public/assets/images:/var/www/html/public/assets/images
    environment:
      - APP_ENV=production
      - DB_HOST=postgres
    depends_on:
      - postgres
      - redis
    networks:
      - applink_network

  nginx:
    image: nginx:alpine
    container_name: applink_nginx
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./deployment/nginx.conf:/etc/nginx/nginx.conf
      - ./deployment/ssl:/etc/nginx/ssl
      - ./public:/var/www/html/public
    depends_on:
      - app
    networks:
      - applink_network

  postgres:
    image: postgres:13-alpine
    container_name: applink_postgres
    restart: unless-stopped
    environment:
      POSTGRES_DB: ventas_applink_prod
      POSTGRES_USER: applink_prod
      POSTGRES_PASSWORD: ${DB_PASSWORD}
    volumes:
      - postgres_data:/var/lib/postgresql/data
      - ./database/backups:/backups
    networks:
      - applink_network

  redis:
    image: redis:7-alpine
    container_name: applink_redis
    restart: unless-stopped
    command: redis-server --appendonly yes
    volumes:
      - redis_data:/data
    networks:
      - applink_network

volumes:
  postgres_data:
  redis_data:

networks:
  applink_network:
    driver: bridge
```

#### **Dockerfile.prod**
```dockerfile
# deployment/Dockerfile.prod
FROM php:8.0-fpm-alpine

# Instalar dependencias del sistema
RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    nginx \
    supervisor

# Instalar extensiones PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        zip \
        gd \
        mbstring

# Configurar PHP para producción
COPY deployment/php.prod.ini /usr/local/etc/php/php.ini

# Copiar código de la aplicación
COPY . /var/www/html
WORKDIR /var/www/html

# Instalar dependencias de Composer
RUN composer install --no-dev --optimize-autoloader

# Configurar permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/logs

# Configurar supervisor
COPY deployment/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 9000

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
```

#### **Comandos de Deployment**
```bash
# Construir y desplegar
docker-compose -f docker-compose.prod.yml up -d --build

# Verificar estado
docker-compose -f docker-compose.prod.yml ps

# Ver logs
docker-compose -f docker-compose.prod.yml logs -f app

# Ejecutar migración
docker-compose -f docker-compose.prod.yml exec app php database/migrate_structure.php

# Acceder al contenedor
docker-compose -f docker-compose.prod.yml exec app sh
```

---

## ☁️ Deployment en la Nube {#nube}

### **AWS EC2 + RDS**

#### **Paso 1: Preparar Infraestructura**
```bash
# Lanzar instancia EC2
# - Amazon Linux 2 AMI
# - t3.medium o superior
# - Security Group: HTTP, HTTPS, SSH

# Crear RDS PostgreSQL
# - db.t3.micro o superior
# - PostgreSQL 13+
# - Multi-AZ para producción
```

#### **Paso 2: Script de Instalación AWS**
```bash
#!/bin/bash
# deployment/aws-install.sh

# Actualizar sistema
sudo yum update -y

# Instalar Docker
sudo amazon-linux-extras install docker -y
sudo service docker start
sudo usermod -a -G docker ec2-user

# Instalar Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/download/1.29.2/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Clonar proyecto
git clone https://github.com/Lina28-dev/Sistema-de-ventas-AppLink.git /home/ec2-user/applink
cd /home/ec2-user/applink

# Configurar variables de entorno
cp deployment/.env.aws .env
# Editar .env con datos de RDS

# Desplegar
docker-compose -f docker-compose.prod.yml up -d
```

### **Google Cloud Platform**

#### **App Engine Deployment**
```yaml
# app.yaml
runtime: php80

env_variables:
  APP_ENV: production
  DB_HOST: /cloudsql/PROJECT_ID:REGION:INSTANCE_ID
  DB_DATABASE: ventas_applink_prod
  DB_USERNAME: applink_prod
  DB_PASSWORD: secure_password

automatic_scaling:
  min_instances: 1
  max_instances: 10
  target_cpu_utilization: 0.6
```

```bash
# Desplegar en GCP
gcloud app deploy app.yaml
```

### **DigitalOcean Droplet**

#### **Instalación en DigitalOcean**
```bash
# Crear Droplet Ubuntu 20.04
# Configurar como servidor Linux estándar

# Script automatizado
curl -sSL https://raw.githubusercontent.com/Lina28-dev/Sistema-de-ventas-AppLink/main/deployment/digitalocean-install.sh | bash
```

---

## 🔧 Configuración de Producción {#producción}

### **Optimizaciones de Performance**

#### **PHP Configuration (php.ini)**
```ini
; deployment/php.prod.ini

; Memory and execution
memory_limit = 256M
max_execution_time = 60
max_input_time = 60

; File uploads
upload_max_filesize = 10M
post_max_size = 10M

; OPcache
opcache.enable = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 4000
opcache.revalidate_freq = 2
opcache.fast_shutdown = 1

; Security
expose_php = Off
display_errors = Off
log_errors = On
error_log = /var/log/php_errors.log

; Sessions
session.cookie_secure = 1
session.cookie_httponly = 1
session.use_strict_mode = 1
```

#### **PostgreSQL Optimización**
```sql
-- postgresql.conf
shared_buffers = 256MB
effective_cache_size = 1GB
maintenance_work_mem = 64MB
checkpoint_completion_target = 0.9
wal_buffers = 16MB
default_statistics_target = 100
random_page_cost = 1.1
effective_io_concurrency = 200
```

### **Configuración de Seguridad**

#### **Firewall (UFW)**
```bash
# Configurar firewall
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

#### **Fail2Ban**
```bash
# Instalar Fail2Ban
sudo apt install fail2ban

# Configurar
sudo nano /etc/fail2ban/jail.local
```

```ini
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 5

[nginx-http-auth]
enabled = true
port = http,https
logpath = /var/log/nginx/error.log

[php-url-fopen]
enabled = true
port = http,https
logpath = /var/log/nginx/access.log
```

---

## 📊 Monitoreo y Mantenimiento {#monitoreo}

### **Logs y Monitoreo**

#### **Configuración de Logs**
```bash
# Rotación de logs
sudo nano /etc/logrotate.d/applink
```

```
/var/www/applink/logs/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
}
```

#### **Monitoring Script**
```bash
#!/bin/bash
# deployment/monitor.sh

# Verificar servicios
systemctl is-active --quiet nginx || echo "Nginx DOWN"
systemctl is-active --quiet postgresql || echo "PostgreSQL DOWN"
systemctl is-active --quiet php8.0-fpm || echo "PHP-FPM DOWN"

# Verificar espacio en disco
DISK_USAGE=$(df / | awk 'NR==2{printf "%.0f", $5}')
if [ $DISK_USAGE -gt 80 ]; then
    echo "Disk usage high: ${DISK_USAGE}%"
fi

# Verificar memoria
MEM_USAGE=$(free | awk 'NR==2{printf "%.0f", $3*100/$2}')
if [ $MEM_USAGE -gt 80 ]; then
    echo "Memory usage high: ${MEM_USAGE}%"
fi

# Verificar aplicación
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://localhost)
if [ $HTTP_STATUS -ne 200 ]; then
    echo "Application not responding: HTTP $HTTP_STATUS"
fi
```

#### **Cron Jobs**
```bash
# Configurar cron
crontab -e

# Monitoreo cada 5 minutos
*/5 * * * * /var/www/applink/deployment/monitor.sh

# Backup diario a las 2 AM
0 2 * * * /var/www/applink/deployment/backup.sh

# Limpieza de logs semanal
0 0 * * 0 find /var/www/applink/logs -name "*.log" -mtime +30 -delete
```

---

## 💾 Backup y Recuperación {#backup}

### **Script de Backup Automatizado**

#### **backup.sh**
```bash
#!/bin/bash
# deployment/backup.sh

TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="/var/backups/applink"
DB_NAME="ventas_applink_prod"
DB_USER="applink_prod"

# Crear directorio de backup
mkdir -p $BACKUP_DIR

# Backup de base de datos
pg_dump -U $DB_USER -h localhost $DB_NAME | gzip > $BACKUP_DIR/db_backup_$TIMESTAMP.sql.gz

# Backup de archivos
tar -czf $BACKUP_DIR/files_backup_$TIMESTAMP.tar.gz \
    --exclude='logs' \
    --exclude='node_modules' \
    --exclude='.git' \
    /var/www/applink

# Backup de configuración
cp /var/www/applink/.env $BACKUP_DIR/env_backup_$TIMESTAMP

# Limpiar backups antiguos (mantener 7 días)
find $BACKUP_DIR -name "*backup*" -mtime +7 -delete

# Log del backup
echo "Backup completed: $TIMESTAMP" >> $BACKUP_DIR/backup.log
```

### **Restauración desde Backup**

#### **restore.sh**
```bash
#!/bin/bash
# deployment/restore.sh

BACKUP_DATE=$1
BACKUP_DIR="/var/backups/applink"

if [ -z "$BACKUP_DATE" ]; then
    echo "Usage: ./restore.sh YYYYMMDD_HHMMSS"
    exit 1
fi

# Restaurar base de datos
gunzip -c $BACKUP_DIR/db_backup_$BACKUP_DATE.sql.gz | psql -U applink_prod ventas_applink_prod

# Restaurar archivos
tar -xzf $BACKUP_DIR/files_backup_$BACKUP_DATE.tar.gz -C /

# Restaurar configuración
cp $BACKUP_DIR/env_backup_$BACKUP_DATE /var/www/applink/.env

# Reiniciar servicios
systemctl restart nginx php8.0-fpm

echo "Restore completed: $BACKUP_DATE"
```

---

## 🔒 SSL y Seguridad {#ssl}

### **Configurar SSL con Let's Encrypt**

#### **Instalación de Certbot**
```bash
# Ubuntu/Debian
sudo apt install certbot python3-certbot-nginx

# Obtener certificado
sudo certbot --nginx -d tu-dominio.com -d www.tu-dominio.com

# Verificar renovación automática
sudo certbot renew --dry-run
```

#### **Configuración Nginx con SSL**
```nginx
server {
    listen 80;
    server_name tu-dominio.com www.tu-dominio.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name tu-dominio.com www.tu-dominio.com;
    
    ssl_certificate /etc/letsencrypt/live/tu-dominio.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/tu-dominio.com/privkey.pem;
    
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    
    add_header Strict-Transport-Security "max-age=63072000" always;
    add_header X-Frame-Options DENY always;
    add_header X-Content-Type-Options nosniff always;
    
    root /var/www/applink/public;
    index index.php;
    
    # ... resto de la configuración
}
```

---

## 🔧 Troubleshooting {#troubleshooting}

### **Problemas Comunes**

#### **Error 500 - Internal Server Error**
```bash
# Verificar logs
tail -f /var/log/nginx/error.log
tail -f /var/log/php8.0-fpm.log

# Verificar permisos
ls -la /var/www/applink/
sudo chown -R www-data:www-data /var/www/applink/

# Verificar configuración PHP
php -m | grep pgsql
php --ini
```

#### **Error de Conexión a Base de Datos**
```bash
# Verificar PostgreSQL
sudo systemctl status postgresql
sudo -u postgres psql -l

# Testear conexión
php /var/www/applink/testing/test_connection.php

# Verificar configuración
cat /var/www/applink/.env | grep DB_
```

#### **Problemas de Performance**
```bash
# Verificar recursos
htop
df -h
free -h

# Verificar procesos PHP
ps aux | grep php-fpm

# Optimizar base de datos
sudo -u postgres psql ventas_applink_prod
VACUUM ANALYZE;
REINDEX DATABASE ventas_applink_prod;
```

### **Comandos de Diagnóstico**

#### **Health Check Script**
```bash
#!/bin/bash
# deployment/health-check.sh

echo "=== APPLINK HEALTH CHECK ==="

# Verificar servicios
echo "Services Status:"
systemctl is-active nginx && echo "✅ Nginx" || echo "❌ Nginx"
systemctl is-active postgresql && echo "✅ PostgreSQL" || echo "❌ PostgreSQL"
systemctl is-active php8.0-fpm && echo "✅ PHP-FPM" || echo "❌ PHP-FPM"

# Verificar conectividad
echo -e "\nConnectivity:"
curl -s http://localhost > /dev/null && echo "✅ HTTP" || echo "❌ HTTP"
curl -s https://localhost > /dev/null && echo "✅ HTTPS" || echo "❌ HTTPS"

# Verificar base de datos
echo -e "\nDatabase:"
php -r "
try {
    \$pdo = new PDO('pgsql:host=localhost;dbname=ventas_applink_prod', 'applink_prod', 'password');
    echo '✅ Database Connection\n';
} catch (Exception \$e) {
    echo '❌ Database Connection: ' . \$e->getMessage() . '\n';
}
"

# Verificar espacio
echo -e "\nDisk Usage:"
df -h / | awk 'NR==2{print $5 " used"}'

echo "=== END HEALTH CHECK ==="
```

---

## 📈 Escalabilidad

### **Load Balancer con Nginx**
```nginx
upstream applink_backend {
    server 127.0.0.1:9001;
    server 127.0.0.1:9002;
    server 127.0.0.1:9003;
}

server {
    listen 80;
    location / {
        proxy_pass http://applink_backend;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}
```

### **Database Read Replicas**
```php
// config/database-cluster.php
$config = [
    'write' => [
        'host' => 'db-master.example.com',
        'user' => 'applink_write',
        'password' => 'password'
    ],
    'read' => [
        [
            'host' => 'db-read-1.example.com',
            'user' => 'applink_read',
            'password' => 'password'
        ],
        [
            'host' => 'db-read-2.example.com',
            'user' => 'applink_read',
            'password' => 'password'
        ]
    ]
];
```

---

**🎯 ¡Tu Sistema de Ventas AppLink está listo para producción!**

**📞 Soporte:** Para asistencia durante el deployment, contacta nuestro equipo técnico.

**🔄 Actualizaciones:** Mantén el sistema actualizado siguiendo nuestras guías de versioning.

---

*📝 Guía actualizada el: Octubre 2025 | Versión: 2.0*  
*👩‍💻 Desarrollado por: Lina28-dev*