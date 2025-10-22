@echo off
echo ===================================================
echo 🐘 CONFIGURACION POSTGRESQL PARA WINDOWS
echo    Sistema de Ventas AppLink
echo ===================================================
echo.

REM Agregar PostgreSQL al PATH
set "PGPATH=C:\Program Files\PostgreSQL\17\bin"
set "PATH=%PATH%;%PGPATH%"

echo 📁 PostgreSQL encontrado en: %PGPATH%
echo 🔧 Agregado al PATH temporalmente

echo.
echo 🔑 CONFIGURACION DE USUARIOS Y BASE DE DATOS
echo.
echo IMPORTANTE: Si es la primera vez que usas PostgreSQL:
echo 1. La contraseña por defecto del usuario 'postgres' fue establecida durante la instalación
echo 2. Si no la recuerdas, podemos resetearla
echo.

echo 📋 Vamos a crear la configuración paso a paso...
echo.

REM Crear archivo SQL de configuración
echo -- 🏗️ Configuración inicial PostgreSQL > postgresql_config.sql
echo -- Sistema de Ventas AppLink >> postgresql_config.sql
echo. >> postgresql_config.sql
echo -- Crear base de datos >> postgresql_config.sql
echo CREATE DATABASE ventas_applink >> postgresql_config.sql
echo     WITH  >> postgresql_config.sql
echo     OWNER = postgres >> postgresql_config.sql
echo     ENCODING = 'UTF8' >> postgresql_config.sql
echo     LC_COLLATE = 'Spanish_Colombia.1252' >> postgresql_config.sql
echo     LC_CTYPE = 'Spanish_Colombia.1252' >> postgresql_config.sql
echo     TABLESPACE = pg_default >> postgresql_config.sql
echo     CONNECTION LIMIT = -1; >> postgresql_config.sql
echo. >> postgresql_config.sql
echo -- Crear usuario para la aplicación >> postgresql_config.sql
echo CREATE USER applink_user WITH >> postgresql_config.sql
echo     PASSWORD 'applink_2024!' >> postgresql_config.sql
echo     CREATEDB >> postgresql_config.sql
echo     NOSUPERUSER >> postgresql_config.sql
echo     NOCREATEROLE; >> postgresql_config.sql
echo. >> postgresql_config.sql
echo -- Otorgar permisos >> postgresql_config.sql
echo GRANT ALL PRIVILEGES ON DATABASE ventas_applink TO applink_user; >> postgresql_config.sql
echo. >> postgresql_config.sql
echo -- Verificar instalación >> postgresql_config.sql
echo SELECT 'Configuracion completada exitosamente' as mensaje; >> postgresql_config.sql

echo ✅ Archivo de configuración creado: postgresql_config.sql
echo.

echo 📋 PASOS SIGUIENTES:
echo.
echo 1. Ejecuta este comando para configurar PostgreSQL:
echo    psql -U postgres -f postgresql_config.sql
echo.
echo 2. Te pedirá la contraseña del usuario postgres
echo.
echo 3. Si no recuerdas la contraseña:
echo    - Busca "Services" en Windows
echo    - Encuentra "postgresql-x64-17"
echo    - Detén el servicio
echo    - Edita el archivo postgresql.conf
echo    - Cambia authentication_method a 'trust' temporalmente
echo    - Reinicia el servicio
echo    - Conecta sin contraseña y cambia la contraseña
echo.
echo 4. Después ejecuta el script de migración completa
echo.

echo 🔧 ¿Quieres intentar conectarte ahora? (S/N)
set /p respuesta="Respuesta: "

if /i "%respuesta%"=="S" (
    echo.
    echo 🔗 Intentando conectar a PostgreSQL...
    psql -U postgres -f postgresql_config.sql
    
    if %ERRORLEVEL% EQU 0 (
        echo.
        echo ✅ ¡Configuración completada exitosamente!
        echo 📋 Próximo paso: Ejecutar migración de datos
        echo.
        echo Ejecuta: php migrations/migrate_data.php
    ) else (
        echo.
        echo ❌ Error en la configuración
        echo 🔧 Revisa la contraseña de postgres
    )
) else (
    echo.
    echo 📋 Configuración manual:
    echo    1. psql -U postgres -f postgresql_config.sql
    echo    2. Ingresa la contraseña cuando se solicite
    echo    3. Ejecuta la migración: php migrations/migrate_data.php
)

echo.
echo ===================================================
echo 🐘 Configuración PostgreSQL completada
echo ===================================================
pause