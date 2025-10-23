# 📋 Documentación Técnica - Sistema de Ventas AppLink

## 🎯 Información General

**Nombre del Proyecto:** Sistema de Ventas AppLink  
**Versión:** 2.0  
**Fecha:** Octubre 2025  
**Desarrollador:** Lina28-dev  
**Licencia:** MIT  

## 🏗️ Arquitectura del Sistema

### **Stack Tecnológico**
- **Backend:** PHP 8.0+
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
- **Base de Datos:** PostgreSQL 17 (Migrado desde MySQL)
- **Servidor Web:** Apache (XAMPP)
- **Autoloader:** PSR-4 Compatible
- **Containerización:** Docker

### **Patrón de Arquitectura**
- **Patrón MVC:** Model-View-Controller
- **Service Layer:** Lógica de negocio separada
- **Repository Pattern:** Acceso a datos abstrato
- **Middleware Pattern:** Autenticación y autorización
- **PSR-4 Autoloading:** Carga automática de clases

## 📁 Estructura del Proyecto

```
Sistema-de-ventas-AppLink-main/
├── README.md                           # 📖 Documentación principal
├── autoload.php                        # 🔧 Autoloader PSR-4
├── composer.json                       # 📦 Dependencias
├── .env                                # 🔐 Variables de entorno
│
├── 📁 app/                             # 🏗️ ARQUITECTURA MODERNA
│   ├── Helpers/
│   │   └── AssetHelper.php             # 🎨 Helper para assets
│   ├── Middleware/
│   │   └── AuthMiddleware.php          # 🔐 Middleware de autenticación
│   └── Services/
│       ├── BaseService.php             # 📋 Servicio base
│       ├── Business/                   # 💼 Lógica de negocio
│       │   ├── ClientService.php       # 👥 Servicio de clientes
│       │   ├── OrderService.php        # 📦 Servicio de pedidos
│       │   ├── SalesService.php        # 💰 Servicio de ventas
│       │   └── UserService.php         # 👤 Servicio de usuarios
│       └── Validation/
│           └── ValidationService.php   # ✅ Servicio de validación
│
├── 📁 api/                             # 🔌 APIs LEGACY
│   ├── clientes.php                    # 👥 API clientes (legacy)
│   ├── pedidos.php                     # 📦 API pedidos (legacy)
│   ├── usuarios.php                    # 👤 API usuarios (legacy)
│   └── ventas.php                      # 💰 API ventas (legacy)
│
├── 📁 config/                          # ⚙️ CONFIGURACIÓN
│   ├── app.php                         # 🔧 Configuración general
│   ├── app_postgresql.php              # 🐘 Configuración PostgreSQL
│   ├── Database.php                    # 🗄️ Clase de conexión DB
│   ├── DatabasePostgreSQL.php          # 🐘 Conexión PostgreSQL
│   ├── api_router.php                  # 🔌 Router de APIs
│   └── assets.php                      # 🎨 Configuración de assets
│
├── 📁 src/                             # 💻 CÓDIGO FUENTE LEGACY
│   ├── Controllers/                    # 🎮 Controladores
│   ├── Models/                         # 📊 Modelos de datos
│   ├── Views/                          # 🖼️ Vistas y templates
│   ├── Auth/                           # 🔐 Autenticación
│   └── Utils/                          # 🔧 Utilidades
│
├── 📁 public/                          # 🌐 ARCHIVOS PÚBLICOS
│   ├── index.php                       # 🏠 Punto de entrada
│   ├── assets/                         # 🎨 Assets organizados
│   │   ├── css/                        # 🎨 Hojas de estilo
│   │   ├── js/                         # 📜 JavaScript
│   │   └── images/                     # 🖼️ Imágenes
│   └── api/v1/                         # 🔌 APIs REST v1
│       ├── clients.php                 # 👥 API clientes v1
│       ├── orders.php                  # 📦 API pedidos v1
│       ├── sales.php                   # 💰 API ventas v1
│       └── users.php                   # 👤 API usuarios v1
│
├── 📁 database/                        # 🗄️ BASE DE DATOS
│   ├── Migration.php                   # 🔄 Clase base migraciones
│   ├── migrate_structure.php           # 🔄 Migración de estructura
│   ├── dashboard_auditoria.php         # 📊 Dashboard de auditoría
│   └── *.sql                           # 📝 Scripts SQL
│
├── 📁 docs/                            # 📚 DOCUMENTACIÓN
├── 📁 testing/                         # 🧪 ARCHIVOS DE PRUEBA
├── 📁 deployment/                      # 🚀 DEPLOYMENT
└── 📁 migrations/                      # 🔄 MIGRACIONES HISTÓRICAS
```

## 🗄️ Base de Datos

### **PostgreSQL Schema**

#### **Tablas Principales:**
1. **usuarios** - Gestión de usuarios del sistema
2. **fs_clientes** - Información de clientes
3. **fs_productos** - Catálogo de productos
4. **fs_pedidos** - Pedidos realizados
5. **fs_ventas** - Registro de ventas
6. **fs_categorias** - Categorías de productos

#### **Tablas de Auditoría:**
- **auditoria_general** - Log de cambios generales
- **auditoria_sesiones** - Log de sesiones de usuario
- **metricas_diarias** - Métricas del sistema

#### **Configuración de Conexión:**
```php
// config/app_postgresql.php
$config = [
    'host' => 'localhost',
    'port' => '5432',
    'database' => 'ventas_applink',
    'username' => 'postgres',
    'password' => 'tu_password',
    'charset' => 'utf8'
];
```

## 🔧 Servicios y Componentes

### **BaseService** (`app/Services/BaseService.php`)
Clase abstracta que proporciona funcionalidad común:
- Conexión a base de datos
- Manejo de errores estandarizado
- Respuestas JSON consistentes
- Validaciones básicas

### **Servicios de Negocio:**

#### **UserService** (`app/Services/Business/UserService.php`)
- Gestión de usuarios
- Autenticación y autorización
- Encriptación de contraseñas
- Validación de datos de usuario

#### **ClientService** (`app/Services/Business/ClientService.php`)
- CRUD de clientes
- Validación de datos de cliente
- Búsqueda y filtrado
- Reportes de clientes

#### **OrderService** (`app/Services/Business/OrderService.php`)
- Gestión de pedidos
- Cálculos de totales
- Estados de pedidos
- Integración con inventario

#### **SalesService** (`app/Services/Business/SalesService.php`)
- Procesamiento de ventas
- Cálculos financieros
- Reportes de ventas
- Métricas y estadísticas

### **Middleware de Autenticación** (`app/Middleware/AuthMiddleware.php`)
- Verificación de sesiones
- Autenticación por API key
- Autorización por roles
- Protección de rutas

### **Validación** (`app/Services/Validation/ValidationService.php`)
- Validación de formularios
- Sanitización de datos
- Reglas de validación customizables
- Mensajes de error localizados

## 🔌 APIs REST v1

### **Endpoints Disponibles:**

#### **Usuarios** (`/public/api/v1/users.php`)
```
GET    /users           # Listar usuarios
POST   /users           # Crear usuario
GET    /users/{id}      # Obtener usuario
PUT    /users/{id}      # Actualizar usuario
DELETE /users/{id}      # Eliminar usuario
```

#### **Clientes** (`/public/api/v1/clients.php`)
```
GET    /clients         # Listar clientes
POST   /clients         # Crear cliente
GET    /clients/{id}    # Obtener cliente
PUT    /clients/{id}    # Actualizar cliente
DELETE /clients/{id}    # Eliminar cliente
```

#### **Pedidos** (`/public/api/v1/orders.php`)
```
GET    /orders          # Listar pedidos
POST   /orders          # Crear pedido
GET    /orders/{id}     # Obtener pedido
PUT    /orders/{id}     # Actualizar pedido
DELETE /orders/{id}     # Eliminar pedido
```

#### **Ventas** (`/public/api/v1/sales.php`)
```
GET    /sales           # Listar ventas
POST   /sales           # Crear venta
GET    /sales/{id}      # Obtener venta
PUT    /sales/{id}      # Actualizar venta
DELETE /sales/{id}      # Eliminar venta
```

### **Autenticación API:**
```http
Authorization: Bearer {api_key}
Content-Type: application/json
```

## 🛡️ Seguridad

### **Implementaciones de Seguridad:**
1. **SQL Injection Protection:** Prepared statements
2. **XSS Protection:** Sanitización de inputs
3. **CSRF Protection:** Tokens CSRF
4. **Session Security:** Configuración segura de sesiones
5. **Password Hashing:** bcrypt con salt
6. **Input Validation:** Validación estricta de datos

### **Variables de Entorno:**
```env
# Database
DB_HOST=localhost
DB_PORT=5432
DB_NAME=ventas_applink
DB_USER=postgres
DB_PASS=password

# Security
APP_KEY=your_secret_key
SESSION_LIFETIME=7200
CSRF_TOKEN_NAME=_token

# API
API_VERSION=v1
API_RATE_LIMIT=100
```

## 🔄 Autoloader PSR-4

### **Configuración:**
```php
// autoload.php
class AppAutoloader {
    private $namespaces = [
        'App\\Services\\' => 'app/Services/',
        'App\\Middleware\\' => 'app/Middleware/',
        'App\\Helpers\\' => 'app/Helpers/',
    ];
}
```

### **Uso:**
```php
require_once 'autoload.php';

// Auto-carga de clases
$userService = new App\Services\Business\UserService();
$auth = new App\Middleware\AuthMiddleware();
```

## 🧪 Testing

### **Archivos de Prueba:**
- `testing/test_connection.php` - Test de conexión DB
- `testing/test_postgresql_complete.php` - Test completo PostgreSQL
- `testing/check_usuarios_table.php` - Verificación tabla usuarios
- `tests/test_services.php` - Tests unitarios de servicios

### **Ejecución de Tests:**
```bash
cd testing/
php test_connection.php
php test_postgresql_complete.php
```

## 📊 Logs y Monitoreo

### **Sistema de Logs:**
- `logs/` - Directorio de logs
- Rotación automática de logs
- Niveles: ERROR, WARNING, INFO, DEBUG

### **Auditoría:**
- Todas las operaciones CRUD son auditadas
- Dashboard de auditoría disponible
- Métricas en tiempo real

## 🚀 Performance

### **Optimizaciones Implementadas:**
1. **Autoloading:** Carga bajo demanda de clases
2. **Database Connections:** Pool de conexiones
3. **Caching:** Cache de consultas frecuentes
4. **Asset Optimization:** CSS/JS minificados
5. **Image Optimization:** Imágenes optimizadas

## 📱 Compatibilidad

### **Navegadores Soportados:**
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

### **Versiones PHP:**
- PHP 8.0+ (Recomendado)
- PHP 7.4+ (Mínimo)

### **Bases de Datos:**
- PostgreSQL 12+ (Principal)
- MySQL 8.0+ (Legacy, deprecado)

---

**📝 Nota:** Esta documentación se actualiza regularmente. Para la versión más reciente, consulta el repositorio oficial en GitHub.