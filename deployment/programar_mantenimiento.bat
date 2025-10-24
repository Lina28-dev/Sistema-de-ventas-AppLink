@echo off
REM Script para programar mantenimiento automático
echo Programando mantenimiento automático de MySQL...
echo.

REM Crear tarea programada para ejecutar cada domingo a las 2:00 AM
schtasks /create /tn "Mantenimiento MySQL XAMPP" /tr "C:\xampp\htdocs\Sistema-de-ventas-AppLink-main\mantenimiento_mysql.bat" /sc weekly /d SUN /st 02:00 /ru SYSTEM

if %errorlevel% == 0 (
    echo ✅ Mantenimiento automático programado exitosamente
    echo 📅 Se ejecutará cada domingo a las 2:00 AM
    echo.
    echo Para ver tareas programadas: schtasks /query /tn "Mantenimiento MySQL XAMPP"
    echo Para eliminar tarea: schtasks /delete /tn "Mantenimiento MySQL XAMPP" /f
) else (
    echo ❌ Error al programar mantenimiento automático
    echo Ejecuta este archivo como Administrador
)

echo.
pause