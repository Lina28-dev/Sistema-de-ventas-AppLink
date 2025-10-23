# 👥 Manual de Usuario - Sistema de Ventas AppLink

## 🎯 Bienvenido al Sistema de Ventas AppLink

Este manual te guiará paso a paso para usar todas las funcionalidades del Sistema de Ventas AppLink, desde la instalación hasta el uso diario del sistema.

## 📋 Tabla de Contenidos

1. [Requisitos del Sistema](#requisitos)
2. [Instalación](#instalación)
3. [Primer Inicio](#primer-inicio)
4. [Panel Principal](#panel-principal)
5. [Gestión de Usuarios](#usuarios)
6. [Gestión de Clientes](#clientes)
7. [Gestión de Productos](#productos)
8. [Procesamiento de Pedidos](#pedidos)
9. [Registro de Ventas](#ventas)
10. [Reportes y Consultas](#reportes)
11. [Configuración](#configuración)
12. [Solución de Problemas](#problemas)

---

## 💻 Requisitos del Sistema {#requisitos}

### **Requisitos Mínimos:**
- **Sistema Operativo:** Windows 10/11, Linux, macOS
- **Navegador Web:** Chrome 90+, Firefox 88+, Safari 14+, Edge 90+
- **Servidor Web:** XAMPP 8.0+ o Apache 2.4+
- **PHP:** Versión 8.0 o superior
- **Base de Datos:** PostgreSQL 12+ o MySQL 8.0+
- **Memoria RAM:** 4 GB mínimo (8 GB recomendado)
- **Espacio en Disco:** 1 GB disponible

### **Requisitos Recomendados:**
- **CPU:** Dual-core 2.5 GHz o superior
- **RAM:** 8 GB o más
- **Conexión a Internet:** Para actualizaciones y sincronización

---

## 🚀 Instalación {#instalación}

### **Opción 1: Instalación con XAMPP (Recomendada)**

#### **Paso 1: Descargar XAMPP**
1. Ve a [https://www.apachefriends.org](https://www.apachefriends.org)
2. Descarga XAMPP para tu sistema operativo
3. Instala XAMPP siguiendo el asistente

#### **Paso 2: Descargar el Sistema**
1. Descarga el proyecto desde GitHub:
   ```
   https://github.com/Lina28-dev/Sistema-de-ventas-AppLink
   ```
2. Extrae el archivo en `C:\xampp\htdocs\`

#### **Paso 3: Configurar la Base de Datos**
1. Abre el Panel de Control de XAMPP
2. Inicia **Apache** y **PostgreSQL** (o MySQL)
3. Crea una nueva base de datos llamada `ventas_applink`

#### **Paso 4: Configurar Variables de Entorno**
1. Copia el archivo `deployment/.env.example` a la raíz del proyecto como `.env`
2. Edita el archivo `.env` con tus datos:
   ```env
   DB_HOST=localhost
   DB_PORT=5432
   DB_NAME=ventas_applink
   DB_USER=postgres
   DB_PASS=tu_password
   ```

#### **Paso 5: Ejecutar Migración**
1. Abre tu navegador
2. Ve a: `http://localhost/Sistema-de-ventas-AppLink-main/database/migrate_structure.php`
3. Sigue las instrucciones en pantalla

### **Opción 2: Instalación con Docker**

```bash
# Clonar el repositorio
git clone https://github.com/Lina28-dev/Sistema-de-ventas-AppLink.git
cd Sistema-de-ventas-AppLink

# Construir y ejecutar con Docker
docker-compose up -d
```

---

## 🔐 Primer Inicio {#primer-inicio}

### **Paso 1: Acceder al Sistema**
1. Abre tu navegador web
2. Ve a: `http://localhost/Sistema-de-ventas-AppLink-main/`
3. Serás redirigido a la pantalla de login

### **Paso 2: Crear Primer Usuario Administrador**
1. Accede a: `http://localhost/Sistema-de-ventas-AppLink-main/src/Auth/register.php`
2. Completa el formulario:
   - **Nombre:** Tu nombre completo
   - **Email:** Tu correo electrónico
   - **Usuario:** admin
   - **Contraseña:** Una contraseña segura (mínimo 8 caracteres)

### **Paso 3: Primer Login**
1. Regresa a la página de login
2. Ingresa tus credenciales
3. ¡Bienvenido al sistema! 🎉

---

## 🏠 Panel Principal {#panel-principal}

### **Vista General del Dashboard**

El panel principal te muestra:

#### **📊 Métricas Principales**
- **Ventas del Día:** Total de ventas realizadas hoy
- **Pedidos Pendientes:** Órdenes que requieren atención
- **Clientes Registrados:** Total de clientes en el sistema
- **Productos Disponibles:** Inventario actual

#### **🧭 Navegación Principal**
- **🏠 Inicio:** Dashboard principal
- **👥 Clientes:** Gestión de clientes
- **📦 Productos:** Catálogo de productos
- **📋 Pedidos:** Gestión de órdenes
- **💰 Ventas:** Registro de ventas
- **📊 Reportes:** Consultas y reportes
- **👤 Usuarios:** Gestión de usuarios (solo admin)
- **⚙️ Configuración:** Ajustes del sistema

#### **🔔 Notificaciones**
- Pedidos pendientes de confirmación
- Stock bajo en productos
- Ventas del día
- Alertas del sistema

---

## 👥 Gestión de Usuarios {#usuarios}

*⚠️ Nota: Esta sección solo está disponible para administradores*

### **Agregar Nuevo Usuario**

#### **Paso 1: Acceder al Módulo**
1. En el menú principal, haz clic en **"👤 Usuarios"**
2. Haz clic en **"➕ Nuevo Usuario"**

#### **Paso 2: Completar Información**
```
📝 Formulario de Nuevo Usuario:
┌─────────────────────────────────────┐
│ Nombre: [____________]              │
│ Apellido: [____________]            │
│ Email: [____________]               │
│ Usuario: [____________]             │
│ Contraseña: [____________]          │
│ Confirmar Contraseña: [____________]│
│ Rol: [▼ Seleccionar]               │
│   • Administrador                   │
│   • Vendedor                        │
│   • Supervisor                      │
└─────────────────────────────────────┘
```

#### **Paso 3: Guardar**
1. Verifica que todos los campos estén completos
2. Haz clic en **"💾 Guardar Usuario"**
3. El sistema confirmará la creación

### **Editar Usuario Existente**
1. En la lista de usuarios, haz clic en **"✏️ Editar"**
2. Modifica los campos necesarios
3. Haz clic en **"💾 Actualizar"**

### **Eliminar Usuario**
1. En la lista de usuarios, haz clic en **"🗑️ Eliminar"**
2. Confirma la acción en el diálogo
3. El usuario será desactivado (no eliminado permanentemente)

---

## 👤 Gestión de Clientes {#clientes}

### **Agregar Nuevo Cliente**

#### **Paso 1: Acceder al Módulo**
1. Haz clic en **"👥 Clientes"** en el menú
2. Haz clic en **"➕ Nuevo Cliente"**

#### **Paso 2: Información Personal**
```
📝 Datos del Cliente:
┌─────────────────────────────────────┐
│ Nombre: [____________]              │
│ Apellido: [____________]            │
│ Tipo ID: [▼ Cédula/NIT/Pasaporte]  │
│ Número ID: [____________]           │
│ Teléfono: [____________]            │
│ Email: [____________]               │
└─────────────────────────────────────┘
```

#### **Paso 3: Dirección**
```
📍 Información de Dirección:
┌─────────────────────────────────────┐
│ Dirección: [____________]           │
│ Ciudad: [____________]              │
│ Departamento: [____________]        │
│ Código Postal: [____________]       │
│ País: [____________]                │
└─────────────────────────────────────┘
```

#### **Paso 4: Guardar**
- Haz clic en **"💾 Guardar Cliente"**

### **Buscar Clientes**
1. Usa la **🔍 barra de búsqueda** en la parte superior
2. Puedes buscar por:
   - Nombre o apellido
   - Número de identificación
   - Teléfono
   - Email

### **Ver Historial de Cliente**
1. Haz clic en **"👁️ Ver"** junto al cliente
2. Verás:
   - **📊 Resumen:** Total de compras, última compra
   - **📋 Pedidos:** Historial de pedidos
   - **💰 Ventas:** Historial de compras
   - **📞 Contacto:** Información de contacto actualizada

---

## 📦 Gestión de Productos {#productos}

### **Agregar Nuevo Producto**

#### **Paso 1: Información Básica**
```
📝 Datos del Producto:
┌─────────────────────────────────────┐
│ Código: [____________]              │
│ Nombre: [____________]              │
│ Descripción: [____________]         │
│ Categoría: [▼ Seleccionar]         │
│ Marca: [____________]               │
│ Modelo: [____________]              │
└─────────────────────────────────────┘
```

#### **Paso 2: Precios e Inventario**
```
💰 Información Comercial:
┌─────────────────────────────────────┐
│ Precio Compra: $[____________]      │
│ Precio Venta: $[____________]       │
│ Stock Inicial: [____________]       │
│ Stock Mínimo: [____________]        │
│ Ubicación: [____________]           │
└─────────────────────────────────────┘
```

#### **Paso 3: Imagen del Producto**
1. Haz clic en **"📷 Subir Imagen"**
2. Selecciona una imagen (JPG, PNG, máx. 2MB)
3. La imagen se guardará automáticamente

### **Categorías de Productos**

El sistema incluye estas categorías predefinidas:
- **👕 Ropa Interior Femenina**
  - Brasieres
  - Panties
  - Conjuntos
- **👖 Ropa Interior Masculina**
  - Boxers
  - Camisetas
- **🧦 Medias y Calcetines**
- **👔 Ropa de Dormir**
- **👶 Ropa Infantil**

### **Gestión de Inventario**
- **📈 Entrada de Stock:** Registra nuevas existencias
- **📉 Salida de Stock:** Registra ventas o pérdidas
- **⚠️ Alertas de Stock Bajo:** El sistema te notifica automáticamente
- **📊 Reporte de Inventario:** Consulta el estado actual

---

## 📋 Procesamiento de Pedidos {#pedidos}

### **Crear Nuevo Pedido**

#### **Paso 1: Seleccionar Cliente**
1. En **"📋 Pedidos"**, haz clic en **"➕ Nuevo Pedido"**
2. **Buscar cliente existente:**
   - Escribe el nombre o ID del cliente
   - Selecciona de la lista
3. **Cliente nuevo:**
   - Haz clic en **"👤 Cliente Nuevo"**
   - Completa los datos básicos

#### **Paso 2: Agregar Productos**
```
🛍️ Selección de Productos:
┌─────────────────────────────────────┐
│ 🔍 Buscar producto: [____________]  │
│                                     │
│ Producto seleccionado:              │
│ 📦 Brasier Push-Up                  │
│ 💰 Precio: $25.000                  │
│ 📊 Stock: 15 unidades               │
│ 🔢 Cantidad: [___] ➕➖             │
│                                     │
│ [➕ Agregar al Pedido]             │
└─────────────────────────────────────┘
```

#### **Paso 3: Revisar Resumen**
```
📋 Resumen del Pedido:
┌─────────────────────────────────────┐
│ Cliente: María García               │
│ Fecha: 2025-10-22                  │
│                                     │
│ Productos:                          │
│ • Brasier Push-Up    x2  $50.000   │
│ • Panty Invisible    x3  $45.000   │
│                                     │
│ Subtotal:           $95.000         │
│ IVA (19%):          $18.050         │
│ TOTAL:             $113.050         │
│                                     │
│ [💾 Confirmar Pedido]              │
└─────────────────────────────────────┘
```

### **Estados de Pedidos**
- **🆕 Nuevo:** Recién creado, pendiente de confirmación
- **✅ Confirmado:** Aprobado para preparación
- **📦 En Preparación:** Siendo empacado
- **🚚 En Tránsito:** Enviado al cliente
- **✅ Entregado:** Completado exitosamente
- **❌ Cancelado:** Cancelado por el cliente o sistema

### **Gestionar Pedidos Existentes**
1. **Ver detalles:** Haz clic en **"👁️ Ver"**
2. **Editar pedido:** Solo disponible para pedidos "Nuevo"
3. **Cambiar estado:** Usa el dropdown de estado
4. **Imprimir:** Genera factura o guía de envío

---

## 💰 Registro de Ventas {#ventas}

### **Venta Rápida (Punto de Venta)**

#### **Paso 1: Modo POS**
1. Ve a **"💰 Ventas"**
2. Haz clic en **"⚡ Venta Rápida"**

#### **Paso 2: Escanear o Buscar Productos**
```
💳 Punto de Venta:
┌─────────────────────────────────────┐
│ 📱 Código: [____________] [SCAN]    │
│ 🔍 Buscar: [____________]           │
│                                     │
│ 🛒 Carrito:                         │
│ • Camiseta Algodón    x1   $15.000  │
│ • Boxer Clásico       x2   $30.000  │
│                                     │
│ Total: $45.000                      │
│                                     │
│ [💳 Pagar] [🗑️ Limpiar]            │
└─────────────────────────────────────┘
```

#### **Paso 3: Procesar Pago**
```
💳 Método de Pago:
┌─────────────────────────────────────┐
│ ○ Efectivo                          │
│ ○ Tarjeta de Crédito                │
│ ○ Transferencia                     │
│ ○ Mixto                             │
│                                     │
│ Recibido: $[____________]           │
│ Cambio: $0                          │
│                                     │
│ [✅ Confirmar Venta]               │
└─────────────────────────────────────┘
```

### **Venta desde Pedido**
1. Ve a **"📋 Pedidos"**
2. Busca el pedido confirmado
3. Haz clic en **"💰 Convertir a Venta"**
4. Confirma el método de pago

### **Consultar Ventas**
- **📅 Por fecha:** Filtra ventas por rango de fechas
- **👤 Por vendedor:** Ve ventas de un empleado específico
- **💰 Por monto:** Filtra por rangos de valor
- **📊 Resumen diario:** Ve totales del día actual

---

## 📊 Reportes y Consultas {#reportes}

### **Reportes Disponibles**

#### **📈 Reporte de Ventas**
```
📊 Configurar Reporte:
┌─────────────────────────────────────┐
│ Tipo: [▼ Ventas Diarias]           │
│ Desde: [📅 01/10/2025]             │
│ Hasta: [📅 22/10/2025]             │
│ Vendedor: [▼ Todos]                │
│ Producto: [▼ Todos]                │
│                                     │
│ [📊 Generar] [📄 PDF] [📊 Excel]   │
└─────────────────────────────────────┘
```

**Información incluida:**
- Total de ventas por día/mes
- Productos más vendidos
- Ventas por vendedor
- Métodos de pago utilizados
- Gráficos de tendencias

#### **👥 Reporte de Clientes**
- Nuevos clientes registrados
- Clientes más frecuentes
- Análisis de comportamiento de compra
- Clientes inactivos

#### **📦 Reporte de Inventario**
- Stock actual por producto
- Productos con stock bajo
- Movimientos de inventario
- Valorización del inventario

#### **💰 Reporte Financiero**
- Ingresos por período
- Costos y márgenes
- Flujo de efectivo
- Rentabilidad por producto

### **Exportar Reportes**
- **📄 PDF:** Para imprimir o archivar
- **📊 Excel:** Para análisis adicional
- **📧 Email:** Envío automático por correo
- **☁️ Nube:** Sincronización con servicios cloud

---

## ⚙️ Configuración {#configuración}

### **Configuración General**

#### **🏢 Información de la Empresa**
```
🏢 Datos de la Empresa:
┌─────────────────────────────────────┐
│ Nombre: AppLink Ventas              │
│ NIT: 123456789-0                    │
│ Dirección: Calle 123 #45-67         │
│ Teléfono: +57 300 123 4567          │
│ Email: ventas@applink.com           │
│ Sitio Web: www.applink.com          │
└─────────────────────────────────────┘
```

#### **💰 Configuración de Impuestos**
- **IVA:** 19% (configurable)
- **Retención:** Según normativa
- **Descuentos:** Porcentajes permitidos

#### **📧 Configuración de Email**
- Servidor SMTP
- Plantillas de correo
- Notificaciones automáticas

### **👤 Configuración de Usuario**

#### **🔐 Cambiar Contraseña**
1. Ve a **"👤 Mi Perfil"**
2. Haz clic en **"🔐 Cambiar Contraseña"**
3. Ingresa contraseña actual y nueva
4. Confirma el cambio

#### **🔔 Notificaciones**
Configura qué notificaciones recibir:
- ✅ Nuevos pedidos
- ✅ Stock bajo
- ✅ Ventas importantes
- ❌ Mantenimiento del sistema

### **🛡️ Configuración de Seguridad**
- **🔒 Tiempo de sesión:** 2 horas por defecto
- **🔑 Política de contraseñas:** Mínimo 8 caracteres
- **📱 Autenticación 2FA:** Disponible próximamente
- **🛡️ Logs de auditoría:** Habilitados

---

## 🚨 Solución de Problemas {#problemas}

### **Problemas Comunes**

#### **❌ No puedo iniciar sesión**
```
🔍 Verificaciones:
1. ¿Usuario y contraseña correctos?
2. ¿Está activo tu usuario?
3. ¿El sistema está en mantenimiento?
4. Intenta: "¿Olvidaste tu contraseña?"
```

#### **🐌 El sistema está lento**
```
⚡ Optimizaciones:
1. Cierra pestañas innecesarias
2. Actualiza tu navegador
3. Verifica tu conexión a internet
4. Contacta al administrador si persiste
```

#### **📷 Las imágenes no se cargan**
```
🖼️ Soluciones:
1. Actualiza la página (F5)
2. Verifica que las imágenes existan
3. Verifica permisos de archivos
4. Contacta soporte técnico
```

#### **💾 Error al guardar datos**
```
🔧 Pasos a seguir:
1. Verifica tu conexión a internet
2. Comprueba que todos los campos estén completos
3. Intenta nuevamente en unos minutos
4. Si persiste, contacta al administrador
```

### **📞 Contacto de Soporte**

#### **🆘 Soporte Técnico**
- **📧 Email:** soporte@applink.com
- **📱 WhatsApp:** +57 300 123 4567
- **🕐 Horario:** Lunes a Viernes, 8:00 AM - 6:00 PM

#### **📚 Recursos Adicionales**
- **🎥 Videos Tutoriales:** [YouTube Channel]
- **📖 Base de Conocimiento:** [Knowledge Base]
- **💬 Foro de Usuarios:** [Community Forum]

### **🔄 Actualizaciones del Sistema**
- Las actualizaciones se aplican automáticamente
- Recibirás notificaciones de nuevas características
- Siempre haz backup antes de actualizaciones mayores

---

## ✅ Lista de Verificación Post-Instalación

```
📋 Verificar después de la instalación:
□ Sistema accesible desde navegador
□ Login funcionando correctamente
□ Base de datos conectada
□ Primer usuario administrador creado
□ Configuración de empresa completada
□ Categorías de productos creadas
□ Primer producto de prueba agregado
□ Primer cliente de prueba registrado
□ Pedido de prueba procesado
□ Venta de prueba realizada
□ Reportes generándose correctamente
□ Backup programado configurado
```

---

**🎉 ¡Felicidades!** Ahora estás listo para usar el Sistema de Ventas AppLink de manera eficiente. 

**💡 Tip:** Mantén este manual a mano durante tus primeras semanas de uso. ¡El sistema se volverá intuitivo muy rápidamente!

**📱 Próximamente:** App móvil para Android e iOS con sincronización en tiempo real.

---

*📝 Manual actualizado el: Octubre 2025 | Versión: 2.0*  
*👩‍💻 Desarrollado por: Lina28-dev*