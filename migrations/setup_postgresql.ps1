# Script PowerShell para configurar PostgreSQL
# Sistema de Ventas AppLink

Write-Host "🐘 CONFIGURANDO POSTGRESQL PARA SISTEMA DE VENTAS" -ForegroundColor Cyan
Write-Host "=================================================" -ForegroundColor Cyan

# Agregar PostgreSQL al PATH
$env:PATH += ";C:\Program Files\PostgreSQL\17\bin"

# Configurar variables de entorno para PostgreSQL
$env:PGUSER = "postgres"
$env:PGPASSWORD = "lina"
$env:PGHOST = "localhost"
$env:PGPORT = "5432"

Write-Host "🔧 Variables de entorno configuradas" -ForegroundColor Green

# Intentar conectar y ejecutar comandos
Write-Host "🔗 Conectando a PostgreSQL..." -ForegroundColor Yellow

try {
    # Verificar conexión
    $version = psql -d postgres -c "SELECT version();" -t
    Write-Host "✅ Conexión exitosa!" -ForegroundColor Green
    Write-Host "📊 Versión: $version" -ForegroundColor Gray
    
    # Verificar si la base de datos ya existe
    $dbExists = psql -d postgres -c "SELECT 1 FROM pg_database WHERE datname='ventas_applink';" -t
    
    if ($dbExists -match "1") {
        Write-Host "⚠️ La base de datos 'ventas_applink' ya existe" -ForegroundColor Yellow
    } else {
        Write-Host "🏗️ Creando base de datos ventas_applink..." -ForegroundColor Yellow
        psql -d postgres -c "CREATE DATABASE ventas_applink WITH OWNER = postgres ENCODING = 'UTF8';"
        Write-Host "✅ Base de datos creada" -ForegroundColor Green
    }
    
    # Verificar si el usuario ya existe
    $userExists = psql -d postgres -c "SELECT 1 FROM pg_roles WHERE rolname='applink_user';" -t
    
    if ($userExists -match "1") {
        Write-Host "⚠️ El usuario 'applink_user' ya existe" -ForegroundColor Yellow
    } else {
        Write-Host "👤 Creando usuario applink_user..." -ForegroundColor Yellow
        psql -d postgres -c "CREATE USER applink_user WITH PASSWORD 'applink_2024!' CREATEDB NOSUPERUSER NOCREATEROLE;"
        Write-Host "✅ Usuario creado" -ForegroundColor Green
    }
    
    # Otorgar permisos
    Write-Host "🔐 Configurando permisos..." -ForegroundColor Yellow
    psql -d postgres -c "GRANT ALL PRIVILEGES ON DATABASE ventas_applink TO applink_user;"
    Write-Host "✅ Permisos configurados" -ForegroundColor Green
    
    # Conectar a la nueva base de datos y habilitar extensiones
    Write-Host "🔧 Configurando extensiones..." -ForegroundColor Yellow
    psql -d ventas_applink -c "CREATE EXTENSION IF NOT EXISTS uuid_ossp;"
    Write-Host "✅ Extensiones habilitadas" -ForegroundColor Green
    
    Write-Host ""
    Write-Host "🎉 ¡POSTGRESQL CONFIGURADO EXITOSAMENTE!" -ForegroundColor Green
    Write-Host "=================================================" -ForegroundColor Cyan
    Write-Host "📋 Información de conexión:" -ForegroundColor White
    Write-Host "   Host: localhost" -ForegroundColor Gray
    Write-Host "   Puerto: 5432" -ForegroundColor Gray
    Write-Host "   Base de datos: ventas_applink" -ForegroundColor Gray
    Write-Host "   Usuario: applink_user" -ForegroundColor Gray
    Write-Host "   Contraseña: applink_2024!" -ForegroundColor Gray
    Write-Host ""
    Write-Host "📋 Próximo paso: Ejecutar migración de esquemas" -ForegroundColor Yellow
    Write-Host "   Comando: psql -U applink_user -d ventas_applink -f schema_migration.sql" -ForegroundColor Gray
    
} catch {
    Write-Host "❌ Error al configurar PostgreSQL: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host ""
    Write-Host "🔧 Posibles soluciones:" -ForegroundColor Yellow
    Write-Host "   1. Verificar que la contraseña 'lina' sea correcta" -ForegroundColor Gray
    Write-Host "   2. Verificar que PostgreSQL esté corriendo en puerto 5432" -ForegroundColor Gray
    Write-Host "   3. Verificar configuración en pg_hba.conf" -ForegroundColor Gray
}

Write-Host ""
Write-Host "Presiona cualquier tecla para continuar..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")