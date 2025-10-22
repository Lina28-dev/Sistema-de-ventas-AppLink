#!/bin/bash
# 🚀 Script de Instalación PostgreSQL para Windows
# Sistema de Ventas AppLink - Migración MySQL → PostgreSQL

echo "================================================="
echo "🐘 INSTALACIÓN POSTGRESQL PARA WINDOWS"
echo "   Sistema de Ventas AppLink"
echo "================================================="

# Verificar si estamos en Windows
if [[ "$OSTYPE" != "msys" && "$OSTYPE" != "cygwin" ]]; then
    echo "❌ Este script está diseñado para Windows con Git Bash/MSYS2"
    exit 1
fi

# Función para descargar PostgreSQL
download_postgresql() {
    echo "📥 Descargando PostgreSQL 15.x para Windows..."
    
    # URL de descarga oficial
    POSTGRES_URL="https://get.enterprisedb.com/postgresql/postgresql-15.4-1-windows-x64.exe"
    
    echo "🔗 URL: $POSTGRES_URL"
    echo "📋 Instrucciones:"
    echo "   1. Abrir el enlace en el navegador"
    echo "   2. Descargar el instalador"
    echo "   3. Ejecutar como administrador"
    echo "   4. Seguir las instrucciones del asistente"
    echo ""
    echo "⚙️  Configuración recomendada:"
    echo "   - Puerto: 5432"
    echo "   - Usuario: postgres"
    echo "   - Contraseña: [elegir una segura]"
    echo "   - Locale: Spanish, Colombia"
    echo ""
}

# Función para configurar variables de entorno
setup_environment() {
    echo "🔧 Configuración de variables de entorno..."
    
    # Rutas típicas de PostgreSQL en Windows
    POSTGRES_PATHS=(
        "C:\\Program Files\\PostgreSQL\\15\\bin"
        "C:\\Program Files\\PostgreSQL\\14\\bin"
        "C:\\Program Files\\PostgreSQL\\13\\bin"
    )
    
    echo "📁 Agregar a PATH del sistema:"
    for path in "${POSTGRES_PATHS[@]}"; do
        echo "   $path"
    done
    
    echo ""
    echo "🔧 Variables de entorno recomendadas:"
    echo "   PGHOST=localhost"
    echo "   PGPORT=5432"
    echo "   PGUSER=postgres"
    echo "   PGDATABASE=ventas_applink"
    echo ""
}

# Función para crear configuración PostgreSQL
create_postgresql_config() {
    echo "📝 Creando archivo de configuración PostgreSQL..."
    
    cat > postgresql_setup.sql << 'EOF'
-- 🏗️ Configuración inicial PostgreSQL
-- Sistema de Ventas AppLink

-- Crear base de datos
CREATE DATABASE ventas_applink
    WITH 
    OWNER = postgres
    ENCODING = 'UTF8'
    LC_COLLATE = 'Spanish_Colombia.1252'
    LC_CTYPE = 'Spanish_Colombia.1252'
    TABLESPACE = pg_default
    CONNECTION LIMIT = -1;

-- Conectar a la nueva base de datos
\c ventas_applink;

-- Crear usuario específico para la aplicación
CREATE USER applink_user WITH
    PASSWORD 'applink_2024!'
    CREATEDB
    NOSUPERUSER
    NOCREATEROLE;

-- Otorgar permisos
GRANT ALL PRIVILEGES ON DATABASE ventas_applink TO applink_user;
GRANT ALL ON SCHEMA public TO applink_user;

-- Configurar timezone
SET timezone = 'America/Bogota';

-- Habilitar extensiones útiles
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "unaccent";

-- Comentario
COMMENT ON DATABASE ventas_applink IS 'Base de datos del Sistema de Ventas AppLink - Migrado de MySQL';

-- Verificar instalación
SELECT version();
SELECT current_database();
SELECT current_user;

\echo '✅ PostgreSQL configurado exitosamente para Sistema de Ventas AppLink'
EOF

    echo "✅ Archivo 'postgresql_setup.sql' creado"
    echo "🔧 Para ejecutar: psql -U postgres -f postgresql_setup.sql"
}

# Función para verificar instalación
verify_installation() {
    echo "🔍 Verificando instalación PostgreSQL..."
    
    # Intentar conectar a PostgreSQL
    if command -v psql &> /dev/null; then
        echo "✅ psql encontrado en PATH"
        echo "📋 Versión:"
        psql --version
        
        echo ""
        echo "🔗 Probar conexión:"
        echo "   psql -U postgres -h localhost"
        
    else
        echo "❌ psql no encontrado en PATH"
        echo "🔧 Agregar PostgreSQL bin al PATH del sistema"
    fi
}

# Función para configurar pgAdmin
setup_pgadmin() {
    echo "🖥️ Configuración pgAdmin..."
    echo "📥 Descargar desde: https://www.pgadmin.org/download/pgadmin-4-windows/"
    echo ""
    echo "⚙️ Configuración de servidor en pgAdmin:"
    echo "   - Nombre: AppLink Local"
    echo "   - Host: localhost"
    echo "   - Puerto: 5432"
    echo "   - Base de datos: ventas_applink"
    echo "   - Usuario: postgres"
    echo ""
}

# Función principal
main() {
    echo "🚀 Iniciando configuración PostgreSQL..."
    echo ""
    
    download_postgresql
    echo ""
    
    setup_environment
    echo ""
    
    create_postgresql_config
    echo ""
    
    verify_installation
    echo ""
    
    setup_pgadmin
    echo ""
    
    echo "================================================="
    echo "✅ INSTALACIÓN COMPLETADA"
    echo ""
    echo "📋 Próximos pasos:"
    echo "   1. Instalar PostgreSQL desde el enlace proporcionado"
    echo "   2. Configurar variables de entorno"
    echo "   3. Ejecutar: psql -U postgres -f postgresql_setup.sql"
    echo "   4. Instalar pgAdmin (opcional)"
    echo "   5. Ejecutar script de migración de esquemas"
    echo ""
    echo "📧 ¿Dudas? Revisar el plan de migración completo"
    echo "================================================="
}

# Ejecutar función principal
main