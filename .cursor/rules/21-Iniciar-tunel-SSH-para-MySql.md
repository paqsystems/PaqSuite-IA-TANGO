# Iniciar SSH para MySql

El túnel SSH es **obligatorio** para:
- Conectarse a la base MySQL remota (p. ej. Laravel Forge) en `127.0.0.1:3306`.
- Que el **MCP mysql** de Cursor funcione (el MCP usa `MYSQL_HOST=127.0.0.1`; sin túnel, la conexión falla).

Ejecutar **uno** de estos dos comandos cuando se abre el proyecto (y antes de usar el MCP mysql en Cursor):

```
cd "C:\Programacion\Lidr\Lidr-AI4Devs2025-ProyectoFinal"
.\scripts\ssh-tunnel-mysql.ps1
```
ó
```
ssh -i "C:\Users\Pabloq\pablo-notebook" -o StrictHostKeyChecking=no -L 3306:127.0.0.1:3306 -N forge@18.218.140.170
```

