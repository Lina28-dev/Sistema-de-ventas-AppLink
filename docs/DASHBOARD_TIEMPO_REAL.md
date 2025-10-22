# Dashboard de Ventas en Tiempo Real

## 🚀 Características Implementadas

### ✅ Ventas en Tiempo Real
- **Actualización automática cada 30 segundos**
- **Indicadores visuales en vivo** (puntos parpadeantes)
- **Gráfico dinámico** de ventas por hora (últimas 24h)
- **Estadísticas actualizadas** instantáneamente

### 📊 Métricas Disponibles
- **Ventas Hoy**: Monto total de ventas del día actual
- **Ventas del Mes**: Monto total de ventas del mes actual
- **Transacciones**: Número total de transacciones completadas
- **Ticket Promedio**: Valor promedio por venta

### 📈 Gráficos Interactivos
- **Chart.js** para visualización en tiempo real
- **Gráfico de líneas** con doble eje (monto y cantidad)
- **Actualizaciones suaves** sin perder el contexto

### 🔄 Actividad Reciente
- **Últimas 10 ventas** mostradas en tiempo real
- **Información del cliente** y monto de cada venta
- **Tiempo transcurrido** desde cada venta

## 🧪 Cómo Probar las Ventas en Tiempo Real

### Método 1: Script Automático
```bash
# Ejecutar desde la terminal en XAMPP
cd c:\xampp\htdocs\Sistema-de-ventas-AppLink-main
php scripts/generar_ventas_demo.php
```

### Método 2: API Manual
```javascript
// Desde la consola del navegador en el dashboard
fetch('../../api/ventas.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        cliente_id: 1,
        productos: [
            {nombre: 'Labial Rosa', precio: 25000, cantidad: 1}
        ],
        total: 25000,
        metodo_pago: 'efectivo'
    })
});
```

### Método 3: Interfaz de Ventas
1. Ir a la sección **Ventas**
2. Crear una nueva venta con cualquier producto
3. Completar la venta
4. Regresar al **Dashboard** y ver la actualización automática

## 🎯 Indicadores Visuales

### 🟢 Puntos Verdes Parpadeantes
- Indican que los datos se están actualizando en tiempo real
- Aparecen en las tarjetas de estadísticas y gráficos

### 📊 Badge "LIVE"
- Muestra que el gráfico y la actividad se actualizan automáticamente
- Color verde indica conexión activa

### ⚡ Animaciones Suaves
- Las actualizaciones de números incluyen transiciones
- Los gráficos se actualizan sin perder el contexto visual

## 🔧 Configuración Técnica

### Frecuencia de Actualización
- **Reloj**: Cada 1 segundo
- **Estadísticas**: Cada 30 segundos
- **Gráficos**: Cada 30 segundos
- **Actividad reciente**: Cada 30 segundos

### APIs Utilizadas
- `GET /api/ventas.php?action=estadisticas` - Obtiene todas las métricas
- `POST /api/ventas.php` - Crear nueva venta (para pruebas)

### Dependencias
- **Chart.js** - Para gráficos interactivos
- **Bootstrap 5** - Para componentes UI
- **Font Awesome** - Para iconos
- **JavaScript Fetch API** - Para comunicación con backend

## 🎨 Personalización

### Cambiar Frecuencia de Actualización
```javascript
// En dashboard.php, línea de setInterval
setInterval(loadDashboardStats, 15000); // Cambiar a 15 segundos
```

### Modificar Colores del Gráfico
```javascript
// En updateRealTimeChart()
borderColor: '#FF1493', // Color principal (rosa Lilipink)
backgroundColor: 'rgba(255, 20, 147, 0.1)', // Fondo transparente
```

### Agregar Nuevas Métricas
1. Modificar `obtenerEstadisticas()` en `api/ventas.php`
2. Agregar nueva tarjeta en el dashboard
3. Actualizar `updateDashboardStats()` en JavaScript

## 🚨 Troubleshooting

### Si no se ven actualizaciones:
1. **Verificar XAMPP**: Asegurar que Apache y MySQL están ejecutándose
2. **Revisar consola**: Abrir F12 y verificar errores en Console
3. **Verificar base de datos**: Confirmar que la tabla `fs_ventas` existe
4. **Probar API**: Visitar directamente `http://localhost/Sistema-de-ventas-AppLink-main/api/ventas.php?action=estadisticas`

### Si el gráfico no aparece:
1. **Chart.js**: Verificar que se carga correctamente
2. **Canvas**: Confirmar que el elemento existe en el DOM
3. **Datos**: Verificar que `ventasUltimas24h` contiene información

## 📱 Responsive Design

El dashboard funciona perfectamente en:
- 🖥️ **Desktop** (1920x1080+)
- 💻 **Laptop** (1366x768+)
- 📱 **Tablet** (768px+)
- 📱 **Mobile** (576px+)

## 🔐 Seguridad

- ✅ **Autenticación requerida** para acceder al dashboard
- ✅ **Validación de sesión** en cada llamada API
- ✅ **Sanitización de datos** en todas las consultas
- ✅ **Prepared statements** para prevenir SQL injection

---

¡El dashboard ahora muestra las ventas en tiempo real! 🎉