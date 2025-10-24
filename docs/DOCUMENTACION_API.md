# 🔌 Documentación de APIs - Sistema de Ventas AppLink

## 🎯 Visión General de la API

La API REST del Sistema de Ventas AppLink proporciona acceso programático a todas las funcionalidades del sistema. Está diseñada siguiendo estándares REST y utiliza JSON para el intercambio de datos.

## 📋 Tabla de Contenidos

1. [Información General](#general)
2. [Autenticación](#autenticación)
3. [Endpoints de Usuarios](#usuarios)
4. [Endpoints de Clientes](#clientes)
5. [Endpoints de Productos](#productos)
6. [Endpoints de Pedidos](#pedidos)
7. [Endpoints de Ventas](#ventas)
8. [Códigos de Error](#errores)
9. [Rate Limiting](#rate-limiting)
10. [Ejemplos de Implementación](#ejemplos)

---

## 📡 Información General {#general}

### **Base URL**
```
# Desarrollo
http://localhost/Sistema-de-ventas-AppLink-main/public/api/v1/

# Producción
https://tu-dominio.com/api/v1/
```

### **Formato de Respuesta**
Todas las respuestas de la API siguen este formato estándar:

#### **Respuesta Exitosa**
```json
{
    "success": true,
    "data": {
        // Datos específicos del endpoint
    },
    "message": "Operación completada exitosamente",
    "timestamp": "2025-10-22T10:30:00Z",
    "version": "v1"
}
```

#### **Respuesta de Error**
```json
{
    "success": false,
    "error": {
        "code": "VALIDATION_ERROR",
        "message": "Datos de entrada inválidos",
        "details": {
            "field": ["Descripción específica del error"]
        }
    },
    "timestamp": "2025-10-22T10:30:00Z",
    "version": "v1"
}
```

### **Headers Requeridos**
```http
Content-Type: application/json
Accept: application/json
Authorization: Bearer {api_key}
```

### **Paginación**
Los endpoints que retornan listas implementan paginación:

```json
{
    "success": true,
    "data": {
        "items": [
            // Array de elementos
        ],
        "pagination": {
            "current_page": 1,
            "per_page": 20,
            "total_items": 150,
            "total_pages": 8,
            "has_next": true,
            "has_previous": false
        }
    }
}
```

---

## 🔐 Autenticación {#autenticación}

### **Tipos de Autenticación**

#### **1. API Key (Recomendado)**
```http
Authorization: Bearer your_api_key_here
```

#### **2. Session-based (Web)**
```http
Cookie: PHPSESSID=abc123...
```

#### **3. Basic Auth (Desarrollo)**
```http
Authorization: Basic base64(username:password)
```

### **Obtener API Key**

#### **Endpoint:** `POST /auth/api-key`
```json
// Request
{
    "username": "admin",
    "password": "password123"
}

// Response
{
    "success": true,
    "data": {
        "api_key": "ak_live_1234567890abcdef",
        "expires_at": "2025-11-22T10:30:00Z",
        "permissions": ["read", "write", "admin"]
    }
}
```

### **Renovar API Key**

#### **Endpoint:** `POST /auth/refresh`
```json
// Request Headers
Authorization: Bearer ak_live_1234567890abcdef

// Response
{
    "success": true,
    "data": {
        "api_key": "ak_live_new_key_here",
        "expires_at": "2025-12-22T10:30:00Z"
    }
}
```

---

## 👤 Endpoints de Usuarios {#usuarios}

### **Listar Usuarios**

#### **GET** `/users`

**Parámetros de Query:**
- `page` (integer, opcional): Número de página (default: 1)
- `per_page` (integer, opcional): Elementos por página (default: 20, max: 100)
- `search` (string, opcional): Búsqueda por nombre o email
- `role` (string, opcional): Filtrar por rol (admin, vendedor, supervisor)
- `active` (boolean, opcional): Filtrar por estado activo

**Ejemplo de Request:**
```http
GET /users?page=1&per_page=10&search=juan&role=vendedor
Authorization: Bearer ak_live_1234567890abcdef
```

**Ejemplo de Response:**
```json
{
    "success": true,
    "data": {
        "items": [
            {
                "id": 1,
                "nombre": "Juan",
                "apellido": "Pérez",
                "email": "juan@example.com",
                "usuario": "jperez",
                "rol": "vendedor",
                "activo": true,
                "ultimo_login": "2025-10-22T09:15:00Z",
                "created_at": "2025-01-15T10:30:00Z"
            }
        ],
        "pagination": {
            "current_page": 1,
            "per_page": 10,
            "total_items": 25,
            "total_pages": 3
        }
    }
}
```

### **Obtener Usuario Específico**

#### **GET** `/users/{id}`

**Ejemplo de Request:**
```http
GET /users/123
Authorization: Bearer ak_live_1234567890abcdef
```

**Ejemplo de Response:**
```json
{
    "success": true,
    "data": {
        "id": 123,
        "nombre": "María",
        "apellido": "García",
        "email": "maria@example.com",
        "usuario": "mgarcia",
        "rol": "admin",
        "activo": true,
        "permisos": ["read", "write", "admin"],
        "ultimo_login": "2025-10-22T08:30:00Z",
        "created_at": "2025-01-10T15:20:00Z",
        "updated_at": "2025-10-20T12:45:00Z"
    }
}
```

### **Crear Usuario**

#### **POST** `/users`

**Campos Requeridos:**
- `nombre` (string): Nombre del usuario
- `apellido` (string): Apellido del usuario
- `email` (string): Email único
- `usuario` (string): Nombre de usuario único
- `password` (string): Contraseña (mínimo 8 caracteres)
- `rol` (string): admin, vendedor, supervisor

**Ejemplo de Request:**
```json
{
    "nombre": "Carlos",
    "apellido": "Rodríguez",
    "email": "carlos@example.com",
    "usuario": "crodriguez",
    "password": "password123",
    "rol": "vendedor"
}
```

**Ejemplo de Response:**
```json
{
    "success": true,
    "data": {
        "id": 124,
        "nombre": "Carlos",
        "apellido": "Rodríguez",
        "email": "carlos@example.com",
        "usuario": "crodriguez",
        "rol": "vendedor",
        "activo": true,
        "created_at": "2025-10-22T10:30:00Z"
    },
    "message": "Usuario creado exitosamente"
}
```

### **Actualizar Usuario**

#### **PUT** `/users/{id}`

**Ejemplo de Request:**
```json
{
    "nombre": "Carlos Alberto",
    "email": "carlos.alberto@example.com",
    "rol": "supervisor"
}
```

### **Eliminar Usuario**

#### **DELETE** `/users/{id}`

**Response:**
```json
{
    "success": true,
    "message": "Usuario eliminado exitosamente"
}
```

---

## 👥 Endpoints de Clientes {#clientes}

### **Listar Clientes**

#### **GET** `/clients`

**Parámetros de Query:**
- `page`, `per_page`: Paginación
- `search`: Búsqueda por nombre, email o documento
- `city`: Filtrar por ciudad
- `active`: Filtrar por estado activo

**Ejemplo de Response:**
```json
{
    "success": true,
    "data": {
        "items": [
            {
                "id": 1,
                "tipo_documento": "cedula",
                "numero_documento": "12345678",
                "nombre": "Ana",
                "apellido": "López",
                "email": "ana@example.com",
                "telefono": "+57 300 123 4567",
                "direccion": "Calle 123 #45-67",
                "ciudad": "Bogotá",
                "departamento": "Cundinamarca",
                "activo": true,
                "total_compras": 450000,
                "ultima_compra": "2025-10-20T14:30:00Z",
                "created_at": "2025-08-15T10:00:00Z"
            }
        ]
    }
}
```

### **Crear Cliente**

#### **POST** `/clients`

**Campos Requeridos:**
- `tipo_documento`: cedula, nit, pasaporte
- `numero_documento`: Número único del documento
- `nombre`: Nombre del cliente
- `apellido`: Apellido del cliente
- `telefono`: Teléfono de contacto

**Campos Opcionales:**
- `email`: Correo electrónico
- `direccion`: Dirección física
- `ciudad`: Ciudad de residencia
- `departamento`: Departamento/Estado
- `codigo_postal`: Código postal

**Ejemplo de Request:**
```json
{
    "tipo_documento": "cedula",
    "numero_documento": "87654321",
    "nombre": "Pedro",
    "apellido": "Martínez",
    "email": "pedro@example.com",
    "telefono": "+57 301 987 6543",
    "direccion": "Carrera 50 #30-20",
    "ciudad": "Medellín",
    "departamento": "Antioquia"
}
```

### **Historial de Cliente**

#### **GET** `/clients/{id}/history`

**Response:**
```json
{
    "success": true,
    "data": {
        "cliente": {
            "id": 1,
            "nombre": "Ana López",
            // ... datos del cliente
        },
        "estadisticas": {
            "total_pedidos": 15,
            "total_comprado": 750000,
            "promedio_pedido": 50000,
            "producto_favorito": "Brasier Push-Up"
        },
        "pedidos_recientes": [
            {
                "id": 101,
                "fecha": "2025-10-20T14:30:00Z",
                "total": 85000,
                "estado": "entregado",
                "productos_count": 3
            }
        ]
    }
}
```

---

## 📦 Endpoints de Productos {#productos}

### **Listar Productos**

#### **GET** `/products`

**Parámetros de Query:**
- `category_id`: Filtrar por categoría
- `search`: Búsqueda por nombre o código
- `min_price`, `max_price`: Rango de precios
- `in_stock`: Solo productos con stock
- `featured`: Solo productos destacados

**Ejemplo de Response:**
```json
{
    "success": true,
    "data": {
        "items": [
            {
                "id": 1,
                "codigo": "BRA001",
                "nombre": "Brasier Push-Up",
                "descripcion": "Brasier con realce y copas moldeadas",
                "categoria": {
                    "id": 1,
                    "nombre": "Ropa Interior Femenina"
                },
                "precio_venta": 25000,
                "precio_compra": 15000,
                "stock_actual": 45,
                "stock_minimo": 10,
                "imagen_url": "/assets/images/brasier-pushup.jpg",
                "activo": true,
                "destacado": true,
                "created_at": "2025-01-15T10:00:00Z"
            }
        ]
    }
}
```

### **Crear Producto**

#### **POST** `/products`

**Campos Requeridos:**
- `codigo`: Código único del producto
- `nombre`: Nombre del producto
- `categoria_id`: ID de la categoría
- `precio_venta`: Precio de venta

**Ejemplo de Request:**
```json
{
    "codigo": "CAM001",
    "nombre": "Camiseta Manga Corta",
    "descripcion": "Camiseta 100% algodón, manga corta",
    "categoria_id": 2,
    "precio_compra": 8000,
    "precio_venta": 15000,
    "stock_inicial": 100,
    "stock_minimo": 20
}
```

### **Actualizar Stock**

#### **PATCH** `/products/{id}/stock`

**Request:**
```json
{
    "tipo": "entrada", // "entrada" o "salida"
    "cantidad": 50,
    "motivo": "Compra de mercancía",
    "referencia": "FAC-001"
}
```

### **Productos con Stock Bajo**

#### **GET** `/products/low-stock`

**Response:**
```json
{
    "success": true,
    "data": {
        "items": [
            {
                "id": 5,
                "codigo": "MED001",
                "nombre": "Medias Tobilleras",
                "stock_actual": 3,
                "stock_minimo": 10,
                "diferencia": -7,
                "estado": "crítico"
            }
        ],
        "resumen": {
            "total_productos_criticos": 8,
            "valor_stock_critico": 450000
        }
    }
}
```

---

## 📋 Endpoints de Pedidos {#pedidos}

### **Listar Pedidos**

#### **GET** `/orders`

**Parámetros de Query:**
- `status`: nuevo, confirmado, preparacion, transito, entregado, cancelado
- `cliente_id`: Filtrar por cliente
- `date_from`, `date_to`: Rango de fechas

**Ejemplo de Response:**
```json
{
    "success": true,
    "data": {
        "items": [
            {
                "id": 101,
                "numero": "PED-2025-000101",
                "cliente": {
                    "id": 1,
                    "nombre": "Ana López",
                    "telefono": "+57 300 123 4567"
                },
                "estado": "confirmado",
                "fecha_pedido": "2025-10-22T10:00:00Z",
                "fecha_entrega_estimada": "2025-10-25T10:00:00Z",
                "productos": [
                    {
                        "id": 1,
                        "nombre": "Brasier Push-Up",
                        "cantidad": 2,
                        "precio_unitario": 25000,
                        "subtotal": 50000
                    }
                ],
                "subtotal": 50000,
                "iva": 9500,
                "total": 59500,
                "observaciones": "Entrega en horario de oficina"
            }
        ]
    }
}
```

### **Crear Pedido**

#### **POST** `/orders`

**Request:**
```json
{
    "cliente_id": 1,
    "productos": [
        {
            "producto_id": 1,
            "cantidad": 2,
            "precio_unitario": 25000
        },
        {
            "producto_id": 5,
            "cantidad": 1,
            "precio_unitario": 12000
        }
    ],
    "observaciones": "Entrega urgente",
    "fecha_entrega_deseada": "2025-10-25"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 102,
        "numero": "PED-2025-000102",
        "estado": "nuevo",
        "total": 71500,
        "cliente": {
            "id": 1,
            "nombre": "Ana López"
        }
    },
    "message": "Pedido creado exitosamente"
}
```

### **Actualizar Estado de Pedido**

#### **PATCH** `/orders/{id}/status`

**Request:**
```json
{
    "estado": "en_preparacion",
    "comentario": "Pedido en proceso de empaque"
}
```

### **Convertir Pedido a Venta**

#### **POST** `/orders/{id}/convert-to-sale`

**Request:**
```json
{
    "metodo_pago": "efectivo",
    "monto_recibido": 80000,
    "descuento": 0
}
```

---

## 💰 Endpoints de Ventas {#ventas}

### **Listar Ventas**

#### **GET** `/sales`

**Parámetros de Query:**
- `date_from`, `date_to`: Rango de fechas
- `vendedor_id`: Filtrar por vendedor
- `metodo_pago`: efectivo, tarjeta, transferencia
- `min_amount`, `max_amount`: Rango de montos

**Ejemplo de Response:**
```json
{
    "success": true,
    "data": {
        "items": [
            {
                "id": 201,
                "numero": "VEN-2025-000201",
                "fecha": "2025-10-22T15:30:00Z",
                "cliente": {
                    "id": 1,
                    "nombre": "Ana López"
                },
                "vendedor": {
                    "id": 2,
                    "nombre": "Juan Pérez"
                },
                "productos": [
                    {
                        "id": 1,
                        "nombre": "Brasier Push-Up",
                        "cantidad": 1,
                        "precio_unitario": 25000,
                        "subtotal": 25000
                    }
                ],
                "subtotal": 25000,
                "descuento": 0,
                "iva": 4750,
                "total": 29750,
                "metodo_pago": "efectivo",
                "monto_recibido": 30000,
                "cambio": 250,
                "estado": "pagado"
            }
        ],
        "resumen": {
            "total_ventas": 125000,
            "cantidad_ventas": 8,
            "venta_promedio": 15625
        }
    }
}
```

### **Crear Venta (Punto de Venta)**

#### **POST** `/sales`

**Request:**
```json
{
    "cliente_id": 1,
    "vendedor_id": 2,
    "productos": [
        {
            "producto_id": 1,
            "cantidad": 1,
            "precio_unitario": 25000
        }
    ],
    "metodo_pago": "efectivo",
    "monto_recibido": 30000,
    "descuento": 0,
    "observaciones": "Venta de mostrador"
}
```

### **Reportes de Ventas**

#### **GET** `/sales/reports/daily`

**Parámetros:**
- `date`: Fecha específica (YYYY-MM-DD)
- `vendedor_id`: Reporte por vendedor

**Response:**
```json
{
    "success": true,
    "data": {
        "fecha": "2025-10-22",
        "resumen": {
            "total_ventas": 450000,
            "cantidad_transacciones": 25,
            "venta_promedio": 18000,
            "productos_vendidos": 67
        },
        "por_metodo_pago": {
            "efectivo": 270000,
            "tarjeta": 150000,
            "transferencia": 30000
        },
        "por_vendedor": [
            {
                "vendedor": "Juan Pérez",
                "ventas": 180000,
                "transacciones": 10
            }
        ],
        "productos_mas_vendidos": [
            {
                "producto": "Brasier Push-Up",
                "cantidad": 15,
                "total": 375000
            }
        ]
    }
}
```

---

## ❌ Códigos de Error {#errores}

### **Códigos HTTP**
- `200` - OK: Operación exitosa
- `201` - Created: Recurso creado exitosamente
- `400` - Bad Request: Datos inválidos
- `401` - Unauthorized: No autenticado
- `403` - Forbidden: Sin permisos
- `404` - Not Found: Recurso no encontrado
- `409` - Conflict: Conflicto (ej: duplicado)
- `422` - Unprocessable Entity: Errores de validación
- `429` - Too Many Requests: Rate limit excedido
- `500` - Internal Server Error: Error del servidor

### **Códigos de Error Específicos**

#### **Errores de Validación**
```json
{
    "success": false,
    "error": {
        "code": "VALIDATION_ERROR",
        "message": "Los datos proporcionados no son válidos",
        "details": {
            "email": ["El email es requerido", "El formato del email es inválido"],
            "password": ["La contraseña debe tener al menos 8 caracteres"]
        }
    }
}
```

#### **Errores de Autenticación**
```json
{
    "success": false,
    "error": {
        "code": "AUTH_ERROR",
        "message": "API key inválida o expirada",
        "details": {
            "expires_at": "2025-10-20T10:30:00Z",
            "current_time": "2025-10-22T10:30:00Z"
        }
    }
}
```

#### **Errores de Negocio**
```json
{
    "success": false,
    "error": {
        "code": "INSUFFICIENT_STOCK",
        "message": "Stock insuficiente para el producto solicitado",
        "details": {
            "producto_id": 5,
            "stock_disponible": 3,
            "cantidad_solicitada": 10
        }
    }
}
```

---

## ⏱️ Rate Limiting {#rate-limiting}

### **Límites por Defecto**
- **Usuarios autenticados:** 1000 requests/hora
- **Endpoints de consulta:** 500 requests/hora
- **Endpoints de escritura:** 200 requests/hora
- **Usuarios no autenticados:** 60 requests/hora

### **Headers de Rate Limit**
```http
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 975
X-RateLimit-Reset: 1698336000
```

### **Respuesta cuando se excede el límite**
```json
{
    "success": false,
    "error": {
        "code": "RATE_LIMIT_EXCEEDED",
        "message": "Has excedido el límite de requests",
        "details": {
            "limit": 1000,
            "reset_at": "2025-10-22T11:00:00Z"
        }
    }
}
```

---

## 💻 Ejemplos de Implementación {#ejemplos}

### **JavaScript (Frontend)**

#### **Configuración del Cliente API**
```javascript
class AppLinkAPI {
    constructor(baseUrl, apiKey) {
        this.baseUrl = baseUrl;
        this.apiKey = apiKey;
    }
    
    async request(endpoint, options = {}) {
        const url = `${this.baseUrl}${endpoint}`;
        const config = {
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${this.apiKey}`,
                ...options.headers
            },
            ...options
        };
        
        try {
            const response = await fetch(url, config);
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.error?.message || 'Error en la API');
            }
            
            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }
    
    // Métodos específicos
    async getProducts(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/products?${queryString}`);
    }
    
    async createSale(saleData) {
        return this.request('/sales', {
            method: 'POST',
            body: JSON.stringify(saleData)
        });
    }
}

// Uso
const api = new AppLinkAPI('http://localhost/api/v1', 'your_api_key');

// Obtener productos
api.getProducts({ category_id: 1, in_stock: true })
    .then(response => {
        console.log('Productos:', response.data.items);
    })
    .catch(error => {
        console.error('Error:', error);
    });
```

### **PHP (Backend/Integración)**

#### **Cliente API en PHP**
```php
<?php

class AppLinkAPIClient
{
    private string $baseUrl;
    private string $apiKey;
    
    public function __construct(string $baseUrl, string $apiKey)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
    }
    
    public function request(string $endpoint, array $options = []): array
    {
        $url = $this->baseUrl . $endpoint;
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ],
            CURLOPT_TIMEOUT => 30,
        ]);
        
        if (isset($options['method']) && $options['method'] === 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            if (isset($options['data'])) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($options['data']));
            }
        }
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        $data = json_decode($response, true);
        
        if ($httpCode >= 400) {
            throw new Exception($data['error']['message'] ?? 'Error en la API');
        }
        
        return $data;
    }
    
    public function getClients(array $params = []): array
    {
        $queryString = http_build_query($params);
        return $this->request("/clients?{$queryString}");
    }
    
    public function createOrder(array $orderData): array
    {
        return $this->request('/orders', [
            'method' => 'POST',
            'data' => $orderData
        ]);
    }
}

// Uso
$api = new AppLinkAPIClient('http://localhost/api/v1', 'your_api_key');

try {
    // Crear pedido
    $orderData = [
        'cliente_id' => 1,
        'productos' => [
            [
                'producto_id' => 1,
                'cantidad' => 2,
                'precio_unitario' => 25000
            ]
        ]
    ];
    
    $result = $api->createOrder($orderData);
    echo "Pedido creado: " . $result['data']['numero'];
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

### **Python (Análisis de Datos)**

#### **Cliente para Reportes**
```python
import requests
import pandas as pd
from datetime import datetime, timedelta

class AppLinkReporter:
    def __init__(self, base_url, api_key):
        self.base_url = base_url.rstrip('/')
        self.headers = {
            'Authorization': f'Bearer {api_key}',
            'Content-Type': 'application/json'
        }
    
    def get_sales_data(self, days_back=30):
        end_date = datetime.now()
        start_date = end_date - timedelta(days=days_back)
        
        params = {
            'date_from': start_date.strftime('%Y-%m-%d'),
            'date_to': end_date.strftime('%Y-%m-%d'),
            'per_page': 1000
        }
        
        response = requests.get(
            f'{self.base_url}/sales',
            headers=self.headers,
            params=params
        )
        
        if response.status_code == 200:
            data = response.json()
            return pd.DataFrame(data['data']['items'])
        else:
            raise Exception(f"Error: {response.status_code}")
    
    def generate_sales_report(self):
        df = self.get_sales_data()
        
        # Análisis básico
        total_sales = df['total'].sum()
        avg_sale = df['total'].mean()
        top_products = df.groupby('producto_nombre')['cantidad'].sum().sort_values(ascending=False)
        
        report = {
            'total_ventas': total_sales,
            'venta_promedio': avg_sale,
            'productos_top': top_products.head(10).to_dict()
        }
        
        return report

# Uso
reporter = AppLinkReporter('http://localhost/api/v1', 'your_api_key')
report = reporter.generate_sales_report()
print(f"Ventas totales: ${report['total_ventas']:,.2f}")
```

---

## 🔄 Webhooks (Próximamente)

### **Eventos Disponibles**
- `order.created` - Nuevo pedido creado
- `order.status_changed` - Estado de pedido cambió
- `sale.completed` - Venta completada
- `product.low_stock` - Stock bajo detectado
- `user.login` - Usuario inició sesión

### **Configuración de Webhook**
```json
{
    "url": "https://tu-app.com/webhook",
    "events": ["order.created", "sale.completed"],
    "secret": "webhook_secret_key"
}
```

---

## 📱 SDKs Oficiales (En Desarrollo)

- **JavaScript/TypeScript SDK**
- **PHP SDK**
- **Python SDK**
- **React Hooks**
- **Vue.js Plugin**

---

**🎯 Esta API está en constante evolución. Mantente al día con las actualizaciones en nuestro [repositorio de GitHub](https://github.com/Lina28-dev/Sistema-de-ventas-AppLink).**

**💡 ¿Necesitas ayuda?** Contacta nuestro equipo de soporte técnico o crea un issue en GitHub.

---

*📝 Documentación actualizada el: Octubre 2025 | Versión: v1.0*  
*👩‍💻 Desarrollado por: Lina28-dev*