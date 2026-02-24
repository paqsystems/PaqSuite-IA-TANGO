@echo off
REM Script para establecer túnel SSH hacia MySQL en Laravel Forge (Windows CMD)
REM Este script crea un túnel SSH que redirige el puerto local 3306 al puerto 3306 del servidor remoto

echo ========================================
echo Túnel SSH para MySQL - Laravel Forge
echo ========================================
echo.
echo Configuración:
echo   SSH Host: 18.218.140.170
echo   SSH User: forge
echo   Local Port: 3306
echo   Remote: 127.0.0.1:3306
echo.
echo El túnel redirige:
echo   localhost:3306 -^> 127.0.0.1:3306 (via forge@18.218.140.170)
echo.
echo Presiona Ctrl+C para cerrar el túnel
echo ========================================
echo.

REM Establecer el túnel SSH usando la clave SSH
REM -L: redirección de puerto local
REM -N: no ejecutar comandos remotos, solo redirigir
REM -i: archivo de clave SSH

ssh -i "C:\Users\PabloQ\pablo-notebook" -L 3306:127.0.0.1:3306 -N forge@18.218.140.170

echo.
echo Túnel SSH cerrado.
pause
