# 👨‍💻 Guía de Desarrollo - Sistema de Ventas AppLink

## 🎯 Introducción para Desarrolladores

Esta guía está dirigida a desarrolladores que desean contribuir, modificar o mantener el Sistema de Ventas AppLink. Incluye estándares de código, arquitectura, flujos de desarrollo y mejores prácticas.

## 📋 Tabla de Contenidos

1. [Setup del Entorno de Desarrollo](#setup)
2. [Arquitectura del Sistema](#arquitectura)
3. [Estándares de Código](#estándares)
4. [Flujo de Desarrollo](#flujo)
5. [Testing y Quality Assurance](#testing)
6. [Base de Datos](#database)
7. [APIs y Servicios](#apis)
8. [Frontend Guidelines](#frontend)
9. [Deployment y CI/CD](#deployment)
10. [Contribución al Proyecto](#contribución)

---

## 🛠️ Setup del Entorno de Desarrollo {#setup}

### **Prerequisitos**
```bash
# Herramientas requeridas
- Git 2.30+
- PHP 8.0+
- Composer 2.0+
- Node.js 16+ (para herramientas frontend)
- PostgreSQL 12+
- Docker & Docker Compose (opcional)
```

### **Instalación del Proyecto**

#### **1. Clonar el Repositorio**
```bash
git clone https://github.com/Lina28-dev/Sistema-de-ventas-AppLink.git
cd Sistema-de-ventas-AppLink
```

#### **2. Setup con Docker (Recomendado)**
```bash
# Copiar archivo de configuración
cp deployment/.env.example .env

# Levantar servicios
docker-compose up -d

# Instalar dependencias
docker-compose exec app composer install
```

#### **3. Setup Local**
```bash
# Instalar dependencias PHP
composer install

# Configurar base de datos
cp deployment/.env.example .env
# Editar .env con tus credenciales

# Ejecutar migraciones
php database/migrate_structure.php
```

### **Variables de Entorno de Desarrollo**
```env
# .env para desarrollo
APP_ENV=development
APP_DEBUG=true
APP_KEY=your_secret_key_here

# Database
DB_HOST=localhost
DB_PORT=5432
DB_NAME=ventas_applink_dev
DB_USER=postgres
DB_PASS=password

# APIs
API_VERSION=v1
API_DEBUG=true
API_RATE_LIMIT_DISABLED=true

# Logging
LOG_LEVEL=debug
LOG_CHANNEL=file
```

### **Herramientas de Desarrollo**

#### **IDE Recomendado: VS Code**
```json
// .vscode/extensions.json
{
    "recommendations": [
        "ms-vscode.vscode-php",
        "bmewburn.vscode-intelephense-client",
        "ms-vscode.vscode-json",
        "bradlc.vscode-tailwindcss",
        "ms-vscode.vscode-eslint"
    ]
}
```

#### **Configuración de PHP CS Fixer**
```php
// .php-cs-fixer.php
<?php
return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__)
            ->exclude(['vendor', 'node_modules'])
    );
```

---

## 🏗️ Arquitectura del Sistema {#arquitectura}

### **Patrones de Diseño Implementados**

#### **1. MVC (Model-View-Controller)**
```
src/
├── Models/          # Modelos de datos
├── Views/           # Templates y vistas
└── Controllers/     # Lógica de control
```

#### **2. Service Layer Pattern**
```
app/Services/
├── BaseService.php              # Servicio base abstracto
├── Business/                    # Lógica de negocio
│   ├── UserService.php
│   ├── ClientService.php
│   ├── OrderService.php
│   └── SalesService.php
└── Validation/
    └── ValidationService.php    # Validaciones centralizadas
```

#### **3. Repository Pattern**
```php
// Ejemplo de implementación
interface ClientRepositoryInterface
{
    public function find(int $id): ?Client;
    public function save(Client $client): bool;
    public function delete(int $id): bool;
}

class ClientRepository implements ClientRepositoryInterface
{
    // Implementación específica de PostgreSQL
}
```

#### **4. Middleware Pattern**
```php
// app/Middleware/AuthMiddleware.php
class AuthMiddleware
{
    public function check(): bool
    {
        return $this->validateSession() && $this->checkPermissions();
    }
}
```

### **Principios SOLID Aplicados**

#### **Single Responsibility Principle (SRP)**
```php
// ✅ Bueno - Una responsabilidad
class UserValidator
{
    public function validate(array $userData): array
    {
        // Solo validación de usuarios
    }
}

// ❌ Malo - Múltiples responsabilidades
class UserManager
{
    public function validate() { }
    public function save() { }
    public function sendEmail() { }
    public function generateReport() { }
}
```

#### **Dependency Injection**
```php
// ✅ Inyección de dependencias
class UserService extends BaseService
{
    private ValidationService $validator;
    
    public function __construct(ValidationService $validator)
    {
        $this->validator = $validator;
        parent::__construct();
    }
}
```

---

## 📏 Estándares de Código {#estándares}

### **Convenciones de Nomenclatura**

#### **Clases**
```php
// ✅ PascalCase para clases
class UserService extends BaseService { }
class ClientRepository { }
class OrderController { }
```

#### **Métodos y Variables**
```php
// ✅ camelCase para métodos y variables
public function getUserById(int $userId): ?User
{
    $userData = $this->database->query($sql);
    return $userData ? new User($userData) : null;
}
```

#### **Constantes**
```php
// ✅ UPPER_SNAKE_CASE para constantes
class ApiConstants
{
    public const API_VERSION = 'v1';
    public const MAX_RESULTS_PER_PAGE = 50;
    public const DEFAULT_TIMEOUT = 30;
}
```

### **Documentación de Código**

#### **PHPDoc Standards**
```php
/**
 * Obtiene un usuario por su ID
 * 
 * @param int $userId ID del usuario a buscar
 * @return User|null El usuario encontrado o null si no existe
 * @throws DatabaseException Si hay error de conexión
 * @throws ValidationException Si el ID no es válido
 * 
 * @since 2.0.0
 * @author Lina28-dev
 */
public function getUserById(int $userId): ?User
{
    if ($userId <= 0) {
        throw new ValidationException('ID de usuario inválido');
    }
    
    // ... implementación
}
```

### **Manejo de Errores**

#### **Estructura de Excepciones**
```php
// app/Exceptions/
├── BaseException.php           # Excepción base
├── ValidationException.php     # Errores de validación
├── DatabaseException.php       # Errores de base de datos
├── AuthenticationException.php # Errores de autenticación
└── BusinessLogicException.php  # Errores de lógica de negocio
```

#### **Implementación de Manejo de Errores**
```php
class BaseService
{
    protected function handleError(Exception $e, string $context = ''): array
    {
        $errorData = [
            'success' => false,
            'error' => $e->getMessage(),
            'context' => $context,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // Log del error
        error_log(json_encode($errorData));
        
        return $errorData;
    }
}
```

### **Estándares de Base de Datos**

#### **Naming Conventions**
```sql
-- ✅ Tablas en plural, snake_case
CREATE TABLE fs_productos (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    precio_venta DECIMAL(10,2),
    stock_actual INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ✅ Índices descriptivos
CREATE INDEX idx_productos_categoria ON fs_productos(categoria_id);
CREATE INDEX idx_productos_precio ON fs_productos(precio_venta);
```

#### **Queries Optimizadas**
```php
// ✅ Prepared Statements
public function getProductsByCategory(int $categoryId): array
{
    $sql = "
        SELECT p.*, c.nombre as categoria_nombre 
        FROM fs_productos p
        INNER JOIN fs_categorias c ON p.categoria_id = c.id
        WHERE p.categoria_id = :category_id 
        AND p.activo = true
        ORDER BY p.nombre ASC
    ";
    
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
```

---

## 🔄 Flujo de Desarrollo {#flujo}

### **Git Workflow**

#### **Branching Strategy**
```
main                 # Producción estable
├── develop          # Desarrollo principal
│   ├── feature/xxx  # Nuevas características
│   ├── bugfix/xxx   # Corrección de bugs
│   └── hotfix/xxx   # Correcciones urgentes
└── release/vX.X.X   # Preparación de releases
```

#### **Commits Convencionales**
```bash
# Formato: type(scope): description

# Tipos permitidos:
feat: nueva característica
fix: corrección de bug
docs: documentación
style: formato, espacios (no afecta código)
refactor: refactorización
test: pruebas
chore: tareas de mantenimiento

# Ejemplos:
feat(users): agregar validación de email
fix(database): corregir conexión PostgreSQL
docs(api): actualizar documentación de endpoints
refactor(services): simplificar UserService
test(orders): agregar tests unitarios
```

### **Code Review Process**

#### **Checklist para Pull Requests**
```markdown
## ✅ Code Review Checklist

### Funcionalidad
- [ ] El código resuelve el problema planteado
- [ ] No introduce bugs o regresiones
- [ ] Maneja casos edge correctamente

### Calidad
- [ ] Sigue estándares de código del proyecto
- [ ] Documentación PHPDoc actualizada
- [ ] Variables y métodos con nombres descriptivos
- [ ] No hay código duplicado

### Testing
- [ ] Tests unitarios incluidos
- [ ] Tests pasando en CI/CD
- [ ] Cobertura de código aceptable

### Seguridad
- [ ] No hay vulnerabilidades evidentes
- [ ] Validación de inputs implementada
- [ ] Sanitización de datos

### Performance
- [ ] No hay queries N+1
- [ ] Consultas optimizadas
- [ ] Recursos liberados correctamente
```

---

## 🧪 Testing y Quality Assurance {#testing}

### **Estructura de Tests**
```
tests/
├── Unit/                    # Tests unitarios
│   ├── Services/
│   ├── Models/
│   └── Utils/
├── Integration/             # Tests de integración
│   ├── Database/
│   └── API/
├── Feature/                 # Tests de características
└── fixtures/               # Datos de prueba
```

### **PHPUnit Configuration**
```xml
<!-- phpunit.xml -->
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="tests/bootstrap.php"
         colors="true"
         verbose="true">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Integration">
            <directory>tests/Integration</directory>
        </testsuite>
    </testsuites>
    
    <filter>
        <whitelist>
            <directory suffix=".php">app/</directory>
            <directory suffix=".php">src/</directory>
        </whitelist>
    </filter>
</phpunit>
```

### **Ejemplo de Test Unitario**
```php
// tests/Unit/Services/UserServiceTest.php
class UserServiceTest extends TestCase
{
    private UserService $userService;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->userService = new UserService();
    }
    
    public function testCreateUserWithValidData(): void
    {
        $userData = [
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'email' => 'juan@example.com',
            'password' => 'password123'
        ];
        
        $result = $this->userService->create($userData);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('user_id', $result);
    }
    
    public function testCreateUserWithInvalidEmail(): void
    {
        $userData = [
            'nombre' => 'Juan',
            'email' => 'invalid-email',
            'password' => 'password123'
        ];
        
        $this->expectException(ValidationException::class);
        $this->userService->create($userData);
    }
}
```

### **Tests de Integración con Base de Datos**
```php
// tests/Integration/Database/UserRepositoryTest.php
class UserRepositoryTest extends DatabaseTestCase
{
    public function testFindUserById(): void
    {
        // Arrange
        $userId = $this->createTestUser();
        
        // Act
        $user = $this->userRepository->find($userId);
        
        // Assert
        $this->assertNotNull($user);
        $this->assertEquals($userId, $user->getId());
    }
    
    private function createTestUser(): int
    {
        $sql = "INSERT INTO usuarios (nombre, email) VALUES (?, ?) RETURNING id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['Test User', 'test@example.com']);
        return $stmt->fetchColumn();
    }
}
```

---

## 🗄️ Base de Datos {#database}

### **Migraciones**

#### **Estructura de Migración**
```php
// database/migrations/2025_10_22_create_productos_table.php
class CreateProductosTable extends Migration
{
    public function up(): void
    {
        $sql = "
            CREATE TABLE fs_productos (
                id SERIAL PRIMARY KEY,
                codigo VARCHAR(50) UNIQUE NOT NULL,
                nombre VARCHAR(255) NOT NULL,
                descripcion TEXT,
                categoria_id INTEGER REFERENCES fs_categorias(id),
                precio_compra DECIMAL(10,2),
                precio_venta DECIMAL(10,2) NOT NULL,
                stock_actual INTEGER DEFAULT 0,
                stock_minimo INTEGER DEFAULT 1,
                activo BOOLEAN DEFAULT true,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
            
            CREATE INDEX idx_productos_codigo ON fs_productos(codigo);
            CREATE INDEX idx_productos_categoria ON fs_productos(categoria_id);
            CREATE INDEX idx_productos_activo ON fs_productos(activo);
        ";
        
        $this->execute($sql);
    }
    
    public function down(): void
    {
        $this->execute("DROP TABLE IF EXISTS fs_productos;");
    }
}
```

### **Seeders**
```php
// database/seeders/CategorySeeder.php
class CategorySeeder
{
    public function run(): void
    {
        $categories = [
            ['nombre' => 'Ropa Interior Femenina', 'descripcion' => 'Productos íntimos para mujeres'],
            ['nombre' => 'Ropa Interior Masculina', 'descripcion' => 'Productos íntimos para hombres'],
            ['nombre' => 'Medias y Calcetines', 'descripcion' => 'Medias para toda la familia'],
        ];
        
        foreach ($categories as $category) {
            $this->insert('fs_categorias', $category);
        }
    }
}
```

---

## 🔌 APIs y Servicios {#apis}

### **Estructura de Respuestas API**
```php
// Respuesta exitosa
{
    "success": true,
    "data": {
        "id": 123,
        "nombre": "Juan Pérez",
        "email": "juan@example.com"
    },
    "message": "Usuario creado exitosamente",
    "timestamp": "2025-10-22T10:30:00Z"
}

// Respuesta de error
{
    "success": false,
    "error": {
        "code": "VALIDATION_ERROR",
        "message": "Datos de entrada inválidos",
        "details": {
            "email": ["El email es requerido"],
            "password": ["La contraseña debe tener al menos 8 caracteres"]
        }
    },
    "timestamp": "2025-10-22T10:30:00Z"
}
```

### **Rate Limiting**
```php
// app/Middleware/RateLimitMiddleware.php
class RateLimitMiddleware
{
    private const RATE_LIMIT = 100; // requests per hour
    
    public function handle(Request $request): bool
    {
        $key = $this->getClientKey($request);
        $requests = $this->getRequestCount($key);
        
        if ($requests >= self::RATE_LIMIT) {
            throw new RateLimitException('Límite de requests excedido');
        }
        
        $this->incrementRequestCount($key);
        return true;
    }
}
```

### **Documentación OpenAPI**
```yaml
# api-docs.yml
openapi: 3.0.0
info:
  title: Sistema de Ventas AppLink API
  version: 1.0.0
  description: API REST para gestión de ventas

paths:
  /api/v1/users:
    get:
      summary: Listar usuarios
      parameters:
        - name: page
          in: query
          required: false
          schema:
            type: integer
            default: 1
      responses:
        '200':
          description: Lista de usuarios
          content:
            application/json:
              schema:
                type: object
                properties:
                  success:
                    type: boolean
                  data:
                    type: array
                    items:
                      $ref: '#/components/schemas/User'
```

---

## 🎨 Frontend Guidelines {#frontend}

### **Estructura de Assets**
```
public/assets/
├── css/
│   ├── components/          # Componentes reutilizables
│   │   ├── buttons.css
│   │   ├── forms.css
│   │   └── modals.css
│   ├── pages/              # Estilos específicos de página
│   │   ├── login.css
│   │   └── dashboard.css
│   └── vendor/             # Librerías externas
├── js/
│   ├── components/         # Scripts de componentes
│   ├── pages/             # Scripts específicos de página
│   └── utils/             # Utilidades JavaScript
└── images/                # Imágenes optimizadas
```

### **CSS Methodology - BEM**
```css
/* ✅ Bloque */
.product-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 1rem;
}

/* ✅ Elemento */
.product-card__title {
    font-size: 1.2rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

/* ✅ Modificador */
.product-card--featured {
    border-color: #007bff;
    box-shadow: 0 4px 8px rgba(0,123,255,0.1);
}
```

### **JavaScript Standards**
```javascript
// ✅ Módulos ES6
class ProductManager {
    constructor(apiUrl) {
        this.apiUrl = apiUrl;
        this.products = [];
    }
    
    async loadProducts() {
        try {
            const response = await fetch(`${this.apiUrl}/products`);
            if (!response.ok) {
                throw new Error('Error al cargar productos');
            }
            this.products = await response.json();
            this.renderProducts();
        } catch (error) {
            console.error('Error:', error);
            this.showError('No se pudieron cargar los productos');
        }
    }
    
    renderProducts() {
        const container = document.getElementById('products-container');
        container.innerHTML = this.products.map(product => 
            this.createProductCard(product)
        ).join('');
    }
}
```

---

## 🚀 Deployment y CI/CD {#deployment}

### **Docker Configuration**
```dockerfile
# Dockerfile
FROM php:8.0-apache

# Instalar extensiones PHP
RUN docker-php-ext-install pdo pdo_pgsql

# Copiar código
COPY . /var/www/html/

# Configurar permisos
RUN chown -R www-data:www-data /var/www/html/
RUN chmod -R 755 /var/www/html/

# Exponer puerto
EXPOSE 80
```

### **Docker Compose**
```yaml
# docker-compose.yml
version: '3.8'

services:
  app:
    build: .
    ports:
      - "8080:80"
    volumes:
      - .:/var/www/html
    environment:
      - DB_HOST=postgres
    depends_on:
      - postgres

  postgres:
    image: postgres:13
    environment:
      POSTGRES_DB: ventas_applink
      POSTGRES_USER: postgres
      POSTGRES_PASSWORD: password
    volumes:
      - postgres_data:/var/lib/postgresql/data

volumes:
  postgres_data:
```

### **GitHub Actions CI/CD**
```yaml
# .github/workflows/ci.yml
name: CI/CD

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main ]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      postgres:
        image: postgres:13
        env:
          POSTGRES_PASSWORD: password
          POSTGRES_DB: test_db
        options: >-
          --health-cmd pg_isready
          --health-interval 10s
          --health-timeout 5s
          --health-retries 5

    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.0'
        
    - name: Install dependencies
      run: composer install
      
    - name: Run tests
      run: vendor/bin/phpunit
      
    - name: Run code analysis
      run: vendor/bin/phpstan analyse
```

---

## 🤝 Contribución al Proyecto {#contribución}

### **Proceso de Contribución**

#### **1. Fork y Clone**
```bash
# Fork el repo en GitHub, luego:
git clone https://github.com/tu-usuario/Sistema-de-ventas-AppLink.git
cd Sistema-de-ventas-AppLink
git remote add upstream https://github.com/Lina28-dev/Sistema-de-ventas-AppLink.git
```

#### **2. Crear Feature Branch**
```bash
git checkout develop
git pull upstream develop
git checkout -b feature/nueva-funcionalidad
```

#### **3. Desarrollo**
```bash
# Hacer cambios, commits frecuentes
git add .
git commit -m "feat(users): agregar validación de email"

# Push cuando esté listo
git push origin feature/nueva-funcionalidad
```

#### **4. Pull Request**
- Crear PR desde tu fork hacia `develop`
- Usar template de PR
- Asignar reviewers
- Esperar aprobación

### **Código de Conducta**

#### **Nuestros Valores**
- **Respeto:** Trata a todos con cortesía y profesionalismo
- **Colaboración:** Trabaja en equipo hacia objetivos comunes
- **Calidad:** Busca siempre la excelencia en el código
- **Aprendizaje:** Comparte conocimiento y aprende de otros

#### **Comportamientos Esperados**
- ✅ Comentarios constructivos en code reviews
- ✅ Documentación clara de cambios
- ✅ Ayuda a otros desarrolladores
- ✅ Reportar bugs de manera descriptiva

#### **Comportamientos Inaceptables**
- ❌ Comentarios despectivos o personales
- ❌ Código sin documentar o tests
- ❌ Ignorar estándares del proyecto
- ❌ Spam o contenido irrelevante

### **Reconocimientos**

#### **Contributors**
```markdown
## 🙏 Contributors

- **Lina28-dev** - Creadora y mantenedora principal
- **[Tu nombre aquí]** - Contribución específica

¿Quieres aparecer aquí? ¡Contribuye al proyecto!
```

---

## 📚 Recursos Adicionales

### **Documentación Externa**
- [PHP 8.0 Documentation](https://www.php.net/docs.php)
- [PostgreSQL Documentation](https://www.postgresql.org/docs/)
- [PSR Standards](https://www.php-fig.org/psr/)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)

### **Tools y Librerías**
- **Composer:** Gestión de dependencias
- **PHPStan:** Análisis estático de código
- **PHP CS Fixer:** Formateo automático
- **Xdebug:** Debugging y profiling

### **Comunidad**
- **GitHub Issues:** Reportar bugs y sugerir features
- **Discussions:** Preguntas y discusiones generales
- **Wiki:** Documentación adicional y tutoriales

---

**🎯 Objetivo:** Mantener un código limpio, bien documentado y fácil de mantener.

**📈 Evolución:** Este proyecto está en constante mejora. ¡Tus contribuciones son bienvenidas!

**💡 Remember:** Code is read more than it's written. Write for humans, not just computers.

---

*📝 Guía actualizada el: Octubre 2025 | Versión: 2.0*  
*👩‍💻 Desarrollado por: Lina28-dev*