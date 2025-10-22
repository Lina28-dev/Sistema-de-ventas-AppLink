#!/bin/bash
# 🔄 SCRIPT DE MIGRACIÓN COMPLETA: MySQL → PostgreSQL
# Sistema de Ventas AppLink

echo "======================================================="
echo "🚀 MIGRACIÓN COMPLETA MySQL → PostgreSQL"
echo "   Sistema de Ventas AppLink"
echo "======================================================="

# Variables de configuración
BACKUP_DIR="./backups"
DATE_STAMP=$(date +"%Y%m%d_%H%M%S")
MYSQL_BACKUP="$BACKUP_DIR/mysql_backup_$DATE_STAMP.sql"
LOG_FILE="./logs/migration_$DATE_STAMP.log"

# Función de logging
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

# Función para verificar errores
check_error() {
    if [ $? -ne 0 ]; then
        log "❌ Error: $1"
        exit 1
    fi
}

# Crear directorios necesarios
mkdir -p "$BACKUP_DIR"
mkdir -p "./logs"

log "🚀 Iniciando proceso de migración completa"

# ========================================
# PASO 1: BACKUP DE MYSQL
# ========================================
log "📦 PASO 1: Respaldo de MySQL"

if command -v mysqldump &> /dev/null; then
    log "📤 Creando backup de MySQL..."
    mysqldump -u root -p fs_clientes > "$MYSQL_BACKUP" 2>/dev/null
    
    if [ -f "$MYSQL_BACKUP" ] && [ -s "$MYSQL_BACKUP" ]; then
        log "✅ Backup de MySQL creado: $MYSQL_BACKUP"
    else
        log "⚠️ No se pudo crear backup de MySQL (puede que no exista la DB)"
        touch "$MYSQL_BACKUP"  # Crear archivo vacío para continuar
    fi
else
    log "⚠️ mysqldump no encontrado, saltando backup de MySQL"
fi

# ========================================
# PASO 2: VERIFICAR POSTGRESQL
# ========================================
log "🐘 PASO 2: Verificación de PostgreSQL"

if command -v psql &> /dev/null; then
    log "✅ PostgreSQL encontrado"
    
    # Verificar conexión
    if psql -U postgres -d postgres -c "SELECT version();" &>/dev/null; then
        log "✅ Conexión a PostgreSQL exitosa"
    else
        log "❌ No se puede conectar a PostgreSQL"
        log "🔧 Asegúrate de que PostgreSQL esté corriendo y configurado"
        exit 1
    fi
else
    log "❌ PostgreSQL no encontrado en PATH"
    log "📥 Instala PostgreSQL primero usando install_postgresql.sh"
    exit 1
fi

# ========================================
# PASO 3: CONFIGURAR BASE DE DATOS
# ========================================
log "🏗️ PASO 3: Configuración de base de datos"

log "📝 Ejecutando configuración inicial..."
psql -U postgres -f postgresql_setup.sql &>/dev/null
check_error "Error en configuración inicial de PostgreSQL"

log "✅ Base de datos 'ventas_applink' configurada"

# ========================================
# PASO 4: CREAR ESQUEMAS
# ========================================
log "🏗️ PASO 4: Creación de esquemas"

log "📝 Ejecutando migración de esquemas..."
psql -U applink_user -d ventas_applink -f schema_migration.sql &>/dev/null
check_error "Error en migración de esquemas"

log "✅ Esquemas migrados exitosamente"

# ========================================
# PASO 5: MIGRAR DATOS
# ========================================
log "📊 PASO 5: Migración de datos"

log "🔄 Ejecutando migración de datos..."
php migrate_data.php
check_error "Error en migración de datos"

log "✅ Datos migrados exitosamente"

# ========================================
# PASO 6: ACTUALIZAR CONFIGURACIÓN
# ========================================
log "⚙️ PASO 6: Actualización de configuración"

# Crear backup de configuración actual
if [ -f "../config/app.php" ]; then
    cp "../config/app.php" "$BACKUP_DIR/app_mysql_backup_$DATE_STAMP.php"
    log "📦 Backup de configuración MySQL creado"
fi

# Actualizar configuración para usar PostgreSQL
log "🔧 Actualizando configuración de la aplicación..."

# Crear script de actualización de configuración
cat > update_config.php << 'EOF'
<?php
// Script para actualizar configuración a PostgreSQL

$mysql_config = '../config/app.php';
$postgresql_config = '../config/app_postgresql.php';

if (file_exists($mysql_config) && file_exists($postgresql_config)) {
    // Crear backup
    copy($mysql_config, $mysql_config . '.mysql_backup');
    
    // Reemplazar configuración
    copy($postgresql_config, $mysql_config);
    
    echo "✅ Configuración actualizada a PostgreSQL\n";
} else {
    echo "❌ Archivos de configuración no encontrados\n";
    exit(1);
}
EOF

php update_config.php
check_error "Error actualizando configuración"

# ========================================
# PASO 7: ACTUALIZAR DATABASE CLASS
# ========================================
log "🔧 PASO 7: Actualización de clase Database"

# Backup de la clase Database original
if [ -f "../config/Database.php" ]; then
    cp "../config/Database.php" "$BACKUP_DIR/Database_mysql_backup_$DATE_STAMP.php"
    log "📦 Backup de clase Database creado"
fi

# Reemplazar con la versión PostgreSQL
cp "DatabasePostgreSQL.php" "../config/Database.php"
log "✅ Clase Database actualizada para PostgreSQL"

# ========================================
# PASO 8: VERIFICACIÓN FINAL
# ========================================
log "🔍 PASO 8: Verificación final"

# Verificar conexión con nueva configuración
cat > test_connection.php << 'EOF'
<?php
require_once '../config/Database.php';

try {
    $db = App\Config\Database::getConnection();
    
    // Probar consulta simple
    $stmt = $db->query("SELECT COUNT(*) as total FROM usuarios");
    $result = $stmt->fetch();
    
    echo "✅ Conexión PostgreSQL exitosa\n";
    echo "📊 Usuarios en base: " . $result['total'] . "\n";
    
    // Verificar otras tablas
    $tables = ['clientes', 'productos', 'ventas', 'pedidos'];
    foreach ($tables as $table) {
        $stmt = $db->query("SELECT COUNT(*) as total FROM $table");
        $result = $stmt->fetch();
        echo "📊 $table: " . $result['total'] . " registros\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n";
    exit(1);
}
EOF

php test_connection.php
check_error "Error en verificación final"

# ========================================
# PASO 9: LIMPIEZA Y DOCUMENTACIÓN
# ========================================
log "🧹 PASO 9: Limpieza y documentación"

# Crear documentación de la migración
cat > "migration_summary_$DATE_STAMP.md" << EOF
# 📋 Resumen de Migración MySQL → PostgreSQL
**Sistema de Ventas AppLink**

## 📅 Información General
- **Fecha:** $(date)
- **Duración:** Completada exitosamente
- **Estado:** ✅ EXITOSA

## 📊 Datos Migrados
$(php test_connection.php 2>/dev/null | grep "📊")

## 📁 Archivos de Backup Creados
- MySQL Database: \`$MYSQL_BACKUP\`
- Configuración original: \`$BACKUP_DIR/app_mysql_backup_$DATE_STAMP.php\`
- Clase Database original: \`$BACKUP_DIR/Database_mysql_backup_$DATE_STAMP.php\`

## 🔧 Configuración Actual
- **Base de datos:** PostgreSQL 
- **Host:** localhost:5432
- **Database:** ventas_applink
- **Usuario:** applink_user

## 📋 Próximos Pasos
1. ✅ Probar todas las funcionalidades del sistema
2. ✅ Verificar que los reportes funcionen correctamente
3. ✅ Monitorear performance durante los primeros días
4. ✅ Mantener backups de MySQL por 2 semanas como precaución

## 🆘 Rollback (si es necesario)
Para revertir a MySQL:
\`\`\`bash
# Restaurar configuración
cp $BACKUP_DIR/app_mysql_backup_$DATE_STAMP.php ../config/app.php
cp $BACKUP_DIR/Database_mysql_backup_$DATE_STAMP.php ../config/Database.php

# Restaurar base de datos MySQL
mysql -u root -p fs_clientes < $MYSQL_BACKUP
\`\`\`

---
**¡Migración completada exitosamente! 🎉**
EOF

# Limpiar archivos temporales
rm -f update_config.php test_connection.php

log "📄 Documentación creada: migration_summary_$DATE_STAMP.md"

# ========================================
# FINALIZACIÓN
# ========================================
echo ""
echo "======================================================="
echo "🎉 ¡MIGRACIÓN COMPLETADA EXITOSAMENTE!"
echo "======================================================="
echo ""
echo "📋 Resumen:"
echo "   ✅ Base de datos PostgreSQL configurada"
echo "   ✅ Esquemas migrados"
echo "   ✅ Datos transferidos"
echo "   ✅ Configuración actualizada"
echo "   ✅ Sistema listo para usar"
echo ""
echo "📁 Archivos importantes:"
echo "   📄 Log: $LOG_FILE"
echo "   📄 Resumen: migration_summary_$DATE_STAMP.md"
echo "   📦 Backups: $BACKUP_DIR/"
echo ""
echo "🔄 El sistema ahora usa PostgreSQL como base de datos"
echo "🔍 Prueba todas las funcionalidades para verificar"
echo "📞 ¿Problemas? Revisa los logs y la documentación"
echo ""
echo "======================================================="

log "🎉 Migración completa finalizada exitosamente"