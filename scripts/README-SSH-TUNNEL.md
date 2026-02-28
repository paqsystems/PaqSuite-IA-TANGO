# Configuración de Túnel SSH para MySQL

Este documento explica cómo configurar un túnel SSH manual para conectarse a MySQL en Laravel Forge a través del MCP.

## ¿Por qué un túnel SSH?

El servidor MySQL está protegido y solo acepta conexiones desde el servidor SSH. El túnel SSH redirige el puerto local 3306 al puerto 3306 del servidor remoto, permitiendo que el MCP se conecte como si fuera local.

## Configuración

**Servidor SSH:**
- Host: `18.218.140.170`
- Usuario: `forge`
- Clave SSH: `C:\Users\PabloQ\pablo-notebook`
- Contraseña SSH: `Go4QQnC3vmPQ8YwlOaw7`

**MySQL (en el servidor remoto):**
- Host: `127.0.0.1` (localhost en el servidor remoto)
- Puerto: `3306`
- Usuario: `forge`
- Contraseña: `Go4QQnC3vmPQ8YwlOaw7`

## Uso

### Opción 1: Script PowerShell (Recomendado)

1. Abre PowerShell
2. Navega a la carpeta del proyecto:
   ```powershell
   cd C:\Programacion\Lidr\Lidr-AI4Devs2025-ProyectoFinal
   ```
3. Ejecuta el script:
   ```powershell
   .\scripts\ssh-tunnel-mysql.ps1
   ```

### Opción 2: Script Batch (Windows CMD)

1. Abre CMD
2. Navega a la carpeta del proyecto:
   ```cmd
   cd C:\Programacion\Lidr\Lidr-AI4Devs2025-ProyectoFinal
   ```
3. Ejecuta el script:
   ```cmd
   scripts\ssh-tunnel-mysql.bat
   ```

### Opción 3: Comando Manual

Si prefieres ejecutar el comando directamente:

```bash
ssh -i "C:\Users\PabloQ\pablo-notebook" -L 3306:127.0.0.1:3306 -N forge@18.218.140.170
```

**Parámetros:**
- `-i`: Archivo de clave SSH
- `-L 3306:127.0.0.1:3306`: Redirige puerto local 3306 → remoto 127.0.0.1:3306
- `-N`: No ejecutar comandos remotos, solo redirigir
- `forge@18.218.140.170`: Usuario y host SSH

## Verificar que el túnel está activo

Una vez que el túnel esté establecido, deberías ver que el script está "esperando" (no devuelve el prompt). Esto es normal.

Para verificar que el túnel funciona, puedes probar conectarte desde otra terminal:

```bash
mysql -h 127.0.0.1 -P 3306 -u forge -p
```

O usar cualquier cliente MySQL apuntando a `127.0.0.1:3306`.

## Usar el MCP

Una vez que el túnel SSH esté activo:

1. **Mantén el túnel corriendo** (no cierres la ventana/terminal)
2. El MCP de MySQL se conectará a `127.0.0.1:3306` que está redirigido al servidor remoto
3. Puedes usar el MCP normalmente desde Cursor

## Cerrar el túnel

Para cerrar el túnel SSH:
- Presiona `Ctrl+C` en la terminal donde está corriendo
- O cierra la ventana/terminal

## Troubleshooting

### Error: "Puerto 3306 ya está en uso"

Si el puerto 3306 ya está en uso localmente:

1. **Opción A:** Cerrar la conexión existente
   ```powershell
   # Ver qué proceso usa el puerto
   Get-NetTCPConnection -LocalPort 3306
   
   # Cerrar el proceso (reemplaza PID con el número del proceso)
   Stop-Process -Id <PID> -Force
   ```

2. **Opción B:** Usar otro puerto local (ej: 3307)
   - Cambiar el script para usar `-L 3307:127.0.0.1:3306`
   - Actualizar `mcp.json` para usar `MYSQL_PORT: "3307"`

### Error: "Permission denied (publickey)"

Si la clave SSH no funciona:

1. Verifica que la ruta de la clave es correcta
2. Verifica los permisos de la clave (en Windows, puede necesitar permisos específicos)
3. Prueba usar contraseña en lugar de clave:
   ```bash
   ssh -L 3306:127.0.0.1:3306 -N forge@18.218.140.170
   ```

### Error: "Connection refused"

Si el túnel se establece pero no puedes conectar:

1. Verifica que el túnel está activo (debe estar "esperando")
2. Verifica que MySQL está corriendo en el servidor remoto
3. Verifica las credenciales de MySQL

## Notas Importantes

- **El túnel debe estar activo mientras uses el MCP**
- Si cierras el túnel, el MCP dejará de funcionar
- El túnel solo redirige tráfico, no almacena datos
- Para uso en producción, considera usar un servicio de túnel más robusto

## Automatización (Opcional)

Si quieres que el túnel se inicie automáticamente, puedes:

1. Crear un servicio de Windows
2. Usar un gestor de tareas para iniciar el script al inicio
3. Usar herramientas como `autossh` (si está disponible en Windows)
