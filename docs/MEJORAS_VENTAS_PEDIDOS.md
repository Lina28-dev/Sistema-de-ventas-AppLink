# Mejoras en Ventas y Pedidos - Sistema AppLink

## Descripción de los Cambios

Se han mejorado las secciones de **Ventas** y **Pedidos** para proporcionar mayor flexibilidad al momento de realizar transacciones, permitiendo seleccionar diferentes tipos de destinatarios más allá de solo clientes o proveedores.

## Nuevas Funcionalidades

### 1. Selector de Tipo de Destinatario

#### En Ventas (`ventas.php`)
- **Cliente**: Para ventas regulares a clientes registrados o cliente general
- **Proveedor**: Para ventas o transacciones con proveedores
- **Uso Interno**: Para transferencias internas entre departamentos

#### En Pedidos (`pedidos.php`)
- **Proveedor**: Para órdenes de compra tradicionales
- **Cliente**: Para devoluciones o intercambios de clientes
- **Departamento Interno**: Para pedidos entre sucursales o departamentos

### 2. Selector Dinámico de Destinatarios

El sistema ahora actualiza automáticamente las opciones disponibles según el tipo seleccionado:

- **Clientes**: Se cargan desde la base de datos via API
- **Proveedores**: Lista predefinida de proveedores registrados
- **Departamentos**: Lista de departamentos internos de la empresa

### 3. Categorización de Transacciones

#### Para Ventas:
- **Venta**: Transacción regular de venta
- **Devolución**: Procesamiento de devoluciones
- **Intercambio**: Intercambio de productos
- **Muestra**: Entrega de muestras gratuitas

#### Para Pedidos:
- **Compra**: Orden de compra estándar
- **Reposición**: Reabastecimiento de inventario
- **Urgente**: Pedidos prioritarios
- **Especial**: Pedidos con condiciones especiales

## Características Técnicas

### Funciones JavaScript Implementadas

1. **`actualizarOpcionesDestinatario()`** (Ventas)
   - Carga dinámicamente las opciones según el tipo seleccionado
   - Maneja la visibilidad del botón "Agregar Cliente"
   - Incluye efectos visuales de carga

2. **`actualizarOpcionesPedido()`** (Pedidos)
   - Actualiza opciones para pedidos según el tipo
   - Integra iconos identificativos para cada tipo
   - Maneja errores de carga de datos

### Estilos CSS Personalizados

Se agregó el archivo `destinatarios.css` con:
- Estilos para los nuevos selectores
- Animaciones de transición
- Indicadores visuales por tipo
- Diseño responsivo
- Estados de carga

### Integración con APIs

- **Clientes**: Conecta con `/api/clientes.php` para cargar datos reales
- **Proveedores**: Lista simulada (preparada para integración futura)
- **Departamentos**: Configuración estática personalizable

## Beneficios del Sistema

1. **Mayor Flexibilidad**: Permite manejar diferentes tipos de transacciones
2. **Mejor Organización**: Categorización clara de operaciones
3. **Experiencia Mejorada**: Interfaz intuitiva con retroalimentación visual
4. **Escalabilidad**: Estructura preparada para futuras expansiones
5. **Trazabilidad**: Mejor seguimiento de transacciones por tipo y categoría

## Configuración Adicional

Para aprovechar completamente estas funcionalidades:

1. **Base de Datos**: Actualizar tablas para incluir campos de tipo y categoría
2. **APIs**: Implementar endpoint para proveedores
3. **Permisos**: Configurar accesos según tipo de usuario
4. **Reportes**: Actualizar reportes para incluir nuevas categorías

## Archivos Modificados

- `src/Views/ventas.php`: Interfaz de ventas mejorada
- `src/Views/pedidos.php`: Interfaz de pedidos expandida
- `public/css/destinatarios.css`: Estilos personalizados
- Este archivo de documentación

## Próximas Mejoras Sugeridas

1. Implementar validaciones específicas por tipo de transacción
2. Agregar histórico de transacciones por destinatario
3. Crear dashboard con métricas por tipo y categoría
4. Implementar notificaciones automáticas
5. Agregar plantillas de documentos por tipo de transacción