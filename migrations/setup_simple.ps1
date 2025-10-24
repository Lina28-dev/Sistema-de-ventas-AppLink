# Script PowerShell para configurar PostgreSQL
# Sistema de Ventas AppLink

Write-Host "CONFIGURANDO POSTGRESQL PARA SISTEMA DE VENTAS" -ForegroundColor Cyan
Write-Host "=================================================" -ForegroundColor Cyan

# Agregar PostgreSQL al PATH
$env:PATH += ";C:\Program Files\PostgreSQL\17\bin"

# Configurar variables de entorno para PostgreSQL
$env:PGUSER = "postgres"
$env:PGPASSWORD = "lina"
$env:PGHOST = "localhost"
$env:PGPORT = "5432"

Write-Host "Variables de entorno configuradas" -ForegroundColor Green

# Intentar conectar y ejecutar comandos
Write-Host "Conectando a PostgreSQL..." -ForegroundColor Yellow

try {
    # Verificar conexion
    $version = psql -d postgres -c "SELECT version();" -t
    Write-Host "Conexion exitosa!" -ForegroundColor Green
    
    # Crear base de datos
    Write-Host "Creando base de datos ventas_applink..." -ForegroundColor Yellow
    psql -d postgres -c "CREATE DATABASE ventas_applink WITH OWNER = postgres ENCODING = 'UTF8';" 2>$null
    
    # Crear usuario
    Write-Host "Creando usuario applink_user..." -ForegroundColor Yellow
    psql -d postgres -c "CREATE USER applink_user WITH PASSWORD 'applink_2024!' CREATEDB NOSUPERUSER NOCREATEROLE;" 2>$null
    
    # Otorgar permisos
    Write-Host "Configurando permisos..." -ForegroundColor Yellow
    psql -d postgres -c "GRANT ALL PRIVILEGES ON DATABASE ventas_applink TO applink_user;"
    
    # Habilitar extensiones
    Write-Host "Configurando extensiones..." -ForegroundColor Yellow
    psql -d ventas_applink -c "CREATE EXTENSION IF NOT EXISTS \"uuid-ossp\";" 2>$null
    
    Write-Host ""
    Write-Host "POSTGRESQL CONFIGURADO EXITOSAMENTE!" -ForegroundColor Green
    Write-Host "=================================================" -ForegroundColor Cyan
    Write-Host "Informacion de conexion:" -ForegroundColor White
    Write-Host "   Host: localhost" -ForegroundColor Gray
    Write-Host "   Puerto: 5432" -ForegroundColor Gray
    Write-Host "   Base de datos: ventas_applink" -ForegroundColor Gray
    Write-Host "   Usuario: applink_user" -ForegroundColor Gray
    Write-Host "   Contraseña: applink_2024!" -ForegroundColor Gray
    Write-Host ""
    Write-Host "Proximo paso: Ejecutar migracion de esquemas" -ForegroundColor Yellow
    
} catch {
    Write-Host "Error al configurar PostgreSQL: $($_.Exception.Message)" -ForegroundColor Red
}