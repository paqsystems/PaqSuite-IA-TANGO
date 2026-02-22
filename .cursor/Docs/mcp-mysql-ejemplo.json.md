# mcp-mysql-ejemplo.json

## Propósito

Ejemplo de configuración del servidor MCP **mysql** en Cursor. Usa Cursor Toolbox (`toolbox.exe`) con el prebuilt `mysql` y se conecta a MySQL en **127.0.0.1:3306**.

## Dependencia obligatoria: túnel SSH

`MYSQL_HOST=127.0.0.1` indica que el MCP espera MySQL en **localhost:3306**. La base real está en un servidor remoto (p. ej. Laravel Forge). Por tanto:

1. **Primero** hay que iniciar el túnel SSH (redirige el puerto remoto 3306 a local 3306).
2. **Después** el MCP mysql puede conectarse a `127.0.0.1:3306`.

Sin el túnel activo, el MCP mostrará **Error - Show Output** al intentar conectar.

## Uso

- Este archivo es **referencia** para el proyecto. La configuración activa está en tu `mcp.json` de usuario (p. ej. `C:\Users\PabloQ\.cursor\mcp.json`).
- Sustituye `TU_PASSWORD` por la contraseña real de MySQL (solo en tu mcp.json local; no subas credenciales al repo).
- Comprueba la ruta `command` (p. ej. `Pabloq` vs `PabloQ` según tu carpeta de usuario).

## Iniciar túnel antes de usar el MCP

Ver `.cursor/rules/00-Iniciar-tunel-SSH-para-MySql.md` y `scripts/ssh-tunnel-mysql.ps1`.

## Relación con otros archivos

- **MCP-MySQL-MSSQL-diagnostico.md**: explica la configuración del MCP mysql y por qué requiere el túnel SSH.
- **00-Iniciar-tunel-SSH-para-MySql.md**: regla para iniciar el túnel al abrir el proyecto.
