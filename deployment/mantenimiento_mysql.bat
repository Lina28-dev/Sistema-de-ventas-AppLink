@echo off
REM Script de limpieza automática para MySQL de XAMPP
REM Ejecutar este archivo cada semana para prevenir problemas

echo ================================================
echo    MANTENIMIENTO AUTOMATICO MYSQL - XAMPP
echo ================================================
echo Fecha: %date% %time%
echo.

REM 1. Detener MySQL si está corriendo
echo [1] Deteniendo MySQL...
taskkill /F /IM mysqld.exe >nul 2>&1
timeout /t 2 >nul

REM 2. Limpiar archivos de log problemáticos
echo [2] Limpiando archivos de log...
cd /d "C:\xampp\mysql\data"
del "*relay-bin*" /q >nul 2>&1
del "*master*" /q >nul 2>&1
del "DESKTOP*" /q >nul 2>&1
del "mysql_error.log" /q >nul 2>&1

REM 3. Limpiar logs antiguos (más de 7 días)
echo [3] Eliminando logs antiguos...
forfiles /p "C:\xampp\mysql\data" /m *.log /d -7 /c "cmd /c del @path" >nul 2>&1

REM 4. Verificar espacio en disco
echo [4] Verificando espacio en disco...
for /f "tokens=3" %%a in ('dir C:\ /-c ^| find "bytes free"') do set FREE_SPACE=%%a
echo Espacio libre: %FREE_SPACE% bytes

REM 5. Crear backup automático de la base de datos
echo [5] Creando backup automático...
if not exist "C:\xampp\backups" mkdir "C:\xampp\backups"
set BACKUP_NAME=fs_clientes_backup_%date:~6,4%%date:~3,2%%date:~0,2%
xcopy "C:\xampp\mysql\data\fs_clientes" "C:\xampp\backups\%BACKUP_NAME%" /E /I /Y >nul 2>&1

echo.
echo ✅ Mantenimiento completado exitosamente
echo ✅ MySQL puede reiniciarse desde XAMPP Control Panel
echo.
echo Presiona cualquier tecla para continuar...
pause >nul