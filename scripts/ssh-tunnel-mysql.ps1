# Script para establecer túnel SSH hacia MySQL en Laravel Forge
# Este script crea un túnel SSH que redirige el puerto local 3306 al puerto 3306 del servidor remoto

# Configuración
$SSH_HOST = "18.218.140.170"
$SSH_USER = "forge"
$SSH_KEY = "C:\Users\Pabloq\pablo-notebook"
$SSH_PASSWORD = "Go4QQnC3vmPQ8YwlOaw7"
$LOCAL_PORT = 3306
$REMOTE_HOST = "127.0.0.1"
$REMOTE_PORT = 3306

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Túnel SSH para MySQL - Laravel Forge" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Configuración:" -ForegroundColor Yellow
Write-Host "  SSH Host: $SSH_HOST" -ForegroundColor Gray
Write-Host "  SSH User: $SSH_USER" -ForegroundColor Gray
Write-Host "  Local Port: $LOCAL_PORT" -ForegroundColor Gray
Write-Host "  Remote: $REMOTE_HOST`:$REMOTE_PORT" -ForegroundColor Gray
Write-Host ""
Write-Host "El túnel redirige:" -ForegroundColor Yellow
Write-Host "  localhost:$LOCAL_PORT -> $REMOTE_HOST`:$REMOTE_PORT (via $SSH_USER@$SSH_HOST)" -ForegroundColor Gray
Write-Host ""
Write-Host "Presiona Ctrl+C para cerrar el túnel" -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Verificar si el puerto local ya está en uso
$portInUse = Get-NetTCPConnection -LocalPort $LOCAL_PORT -ErrorAction SilentlyContinue
if ($portInUse) {
    Write-Host "ADVERTENCIA: El puerto $LOCAL_PORT ya está en uso." -ForegroundColor Red
    Write-Host "Por favor, cierra la conexión existente o cambia el puerto local." -ForegroundColor Yellow
    Write-Host ""
    $response = Read-Host "¿Deseas continuar de todos modos? (S/N)"
    if ($response -ne "S" -and $response -ne "s") {
        exit
    }
}

# Establecer el túnel SSH
# Usando ssh con redirección de puerto local
# -L: redirección de puerto local
# -N: no ejecutar comandos remotos, solo redirigir
# -f: ejecutar en background (no funciona bien en PowerShell, así que no lo usamos)
# -i: archivo de clave SSH
# -o StrictHostKeyChecking=no: aceptar automáticamente la clave del host (solo para desarrollo)

Write-Host "Estableciendo túnel SSH..." -ForegroundColor Green

# Intentar usar la clave SSH primero
if (Test-Path $SSH_KEY) {
    Write-Host "Usando clave SSH: $SSH_KEY" -ForegroundColor Gray
    ssh -i $SSH_KEY -L ${LOCAL_PORT}:${REMOTE_HOST}:${REMOTE_PORT} -N $SSH_USER@$SSH_HOST
} else {
    Write-Host "Clave SSH no encontrada. Usando autenticación por contraseña..." -ForegroundColor Yellow
    Write-Host "NOTA: Esto puede requerir interacción manual." -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Si tienes problemas, puedes usar:" -ForegroundColor Yellow
    Write-Host "  ssh -L ${LOCAL_PORT}:${REMOTE_HOST}:${REMOTE_PORT} -N $SSH_USER@$SSH_HOST" -ForegroundColor Gray
    Write-Host ""
    
    # Para usar contraseña, necesitamos usar expect o similar, o hacerlo manualmente
    # Por ahora, ejecutamos el comando ssh directamente
    ssh -L ${LOCAL_PORT}:${REMOTE_HOST}:${REMOTE_PORT} -N $SSH_USER@$SSH_HOST
}

Write-Host ""
Write-Host "Túnel SSH cerrado." -ForegroundColor Yellow
