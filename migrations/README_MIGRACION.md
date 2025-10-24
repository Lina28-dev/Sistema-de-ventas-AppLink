# 🛠️ GUÍA DE MIGRACIÓN PASO A PASO
## MySQL → PostgreSQL | Sistema de Ventas AppLink

---

## 🚀 **EJECUCIÓN RÁPIDA (Recomendada)**

Si tienes Git Bash instalado en Windows, ejecuta todo automáticamente:

```bash
# Ir al directorio de migraciones
cd migrations/

# Hacer ejecutables los scripts
chmod +x *.sh

# Ejecutar migración completa
./migrate_complete.sh
```

---

## 📋 **EJECUCIÓN MANUAL PASO A PASO**

### **Paso 1: Instalar PostgreSQL**
```bash
# Ejecutar script de instalación
./install_postgresql.sh

# O seguir las instrucciones manuales del script
```

### **Paso 2: Configurar PostgreSQL**
```bash
# Ejecutar configuración inicial
psql -U postgres -f postgresql_setup.sql
```

### **Paso 3: Migrar Esquemas**
```bash
# Crear estructura de tablas
psql -U applink_user -d ventas_applink -f schema_migration.sql
```

### **Paso 4: Migrar Datos**
```bash
# Transferir datos de MySQL a PostgreSQL
php migrate_data.php
```

### **Paso 5: Actualizar Configuración**
```bash
# Backup de configuración actual
cp ../config/app.php ../config/app_mysql_backup.php

# Aplicar nueva configuración
cp app_postgresql.php ../config/app.php

# Actualizar clase Database
cp DatabasePostgreSQL.php ../config/Database.php
```

---

## 🔍 **VERIFICACIÓN POST-MIGRACIÓN**

### **Probar Conexión**
```bash
# Verificar que todo funciona
psql -U applink_user -d ventas_applink -c "SELECT COUNT(*) FROM usuarios;"
```

### **Probar Sistema Web**
1. Abrir navegador en: `http://localhost/Sistema-de-ventas-AppLink-main`
2. Intentar login con: `admin` / `admin123`
3. Verificar que las secciones cargan correctamente
4. Probar crear/editar datos

---

## 📊 **COMANDOS ÚTILES POSTGRESQL**

### **Conexión y Consultas Básicas**
```sql
-- Conectar a la base de datos
psql -U applink_user -d ventas_applink

-- Ver todas las tablas
\dt

-- Describir una tabla
\d usuarios

-- Ver datos de una tabla
SELECT * FROM usuarios LIMIT 5;

-- Verificar conteos
SELECT 
    'usuarios' as tabla, COUNT(*) as registros FROM usuarios
UNION ALL
SELECT 'clientes', COUNT(*) FROM clientes
UNION ALL  
SELECT 'productos', COUNT(*) FROM productos
UNION ALL
SELECT 'ventas', COUNT(*) FROM ventas;
```

### **Administración de Base de Datos**
```sql
-- Ver tamaño de la base de datos
SELECT 
    pg_size_pretty(pg_database_size('ventas_applink')) as tamaño_db;

-- Ver estadísticas de tablas
SELECT 
    schemaname,
    tablename,
    n_live_tup as registros_activos,
    n_dead_tup as registros_muertos
FROM pg_stat_user_tables
ORDER BY n_live_tup DESC;

-- Vacuum y análisis (mantenimiento)
VACUUM ANALYZE;
```

---

## 🆘 **SOLUCIÓN DE PROBLEMAS**

### **Error: "psql: command not found"**
- Agregar PostgreSQL al PATH del sistema
- Ruta típica: `C:\Program Files\PostgreSQL\15\bin`

### **Error: "could not connect to server"**
- Verificar que PostgreSQL esté corriendo
- Servicios de Windows → PostgreSQL Database Server
- O reiniciar el servicio

### **Error: "password authentication failed"**
- Verificar credenciales en `app_postgresql.php`
- Usuario por defecto: `applink_user`
- Contraseña por defecto: `applink_2024!`

### **Error: "database does not exist"**
- Ejecutar primero: `postgresql_setup.sql`
- Verificar que la base `ventas_applink` se creó

### **Error en PHP: "could not find driver"**
- Instalar extensión `php-pgsql`
- En XAMPP: Activar en `php.ini` → `extension=pgsql`

---

## 🔄 **ROLLBACK A MYSQL (Emergencia)**

Si necesitas volver a MySQL temporalmente:

```bash
# 1. Restaurar configuración
cp ../config/app_mysql_backup.php ../config/app.php

# 2. Restaurar clase Database original
cp ../backups/Database_mysql_backup_[fecha].php ../config/Database.php

# 3. Verificar que MySQL esté corriendo
# 4. El sistema volverá a usar MySQL
```

---

## 📈 **BENEFICIOS POST-MIGRACIÓN**

### **Performance Mejorado**
- ✅ Consultas complejas más rápidas
- ✅ Mejor manejo de concurrencia
- ✅ Optimizador de consultas avanzado

### **Funcionalidades Nuevas**
- ✅ JSON nativo (JSONB) para mejor storage
- ✅ Arrays y tipos de datos avanzados
- ✅ Soporte completo para UTF-8
- ✅ Triggers y funciones personalizadas

### **Estabilidad y Confiabilidad**
- ✅ Transacciones ACID más robustas
- ✅ Mejor integridad referencial
- ✅ Recuperación ante fallos mejorada

---

## 📞 **SOPORTE TÉCNICO**

### **Logs de Diagnóstico**
- **Migración:** `logs/migration_[fecha].log`
- **PostgreSQL:** Verificar logs en pgAdmin
- **PHP:** `logs/postgresql_errors.log`

### **Monitoreo Continuo**
```sql
-- Query para monitorear performance
SELECT 
    query,
    calls,
    total_time,
    mean_time,
    rows
FROM pg_stat_statements 
ORDER BY total_time DESC 
LIMIT 10;
```

### **Backup Automático**
```bash
# Crear backup diario
pg_dump -U applink_user ventas_applink > backup_$(date +%Y%m%d).sql
```

---

## ✅ **CHECKLIST FINAL**

- [ ] PostgreSQL instalado y corriendo
- [ ] Base de datos `ventas_applink` creada
- [ ] Usuario `applink_user` configurado
- [ ] Esquemas migrados exitosamente
- [ ] Datos transferidos completamente
- [ ] Configuración PHP actualizada
- [ ] Sistema web funcionando
- [ ] Login de administrador exitoso
- [ ] Todas las secciones cargando
- [ ] Crear/editar datos funciona
- [ ] Reportes generando correctamente

---

**🎉 ¡Tu Sistema de Ventas AppLink ahora usa PostgreSQL!**

*Para cualquier consulta adicional, revisa los logs generados durante la migración.*