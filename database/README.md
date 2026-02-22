# Base de Datos - Archivos y Recursos

Esta carpeta contiene archivos relacionados con el diseño y visualización del modelo de base de datos.

## Archivos

### `modelo-datos.dbml`

**Descripción:** Modelo de datos en formato DBML (Database Markup Language) para visualización gráfica en dbdiagram.io.

**Contenido:**
- Definición completa de todas las tablas del sistema
- Relaciones entre tablas (Foreign Keys)
- Comentarios descriptivos de campos
- Restricciones y valores por defecto

**Uso:**
1. Abrir [dbdiagram.io](https://dbdiagram.io)
2. Importar el archivo `modelo-datos.dbml`
3. Visualizar el diagrama ER interactivo
4. Exportar a PNG, PDF o SQL según necesidad

**Sincronización:**
- Este archivo debe mantenerse sincronizado con `docs/modelo-datos.md`
- Ver regla de sincronización: `.cursor/rules/14-dbml-sync-rule.md`
- Cada modificación en `docs/modelo-datos.md` requiere actualizar este archivo

### `ejemplo.md`

**Descripción:** Ejemplo de diagrama ER usando Mermaid (solo referencia, no es parte del modelo actual).

## Herramientas Recomendadas

### dbdiagram.io (Recomendado)
- **Tipo:** Web (gratis)
- **Ventajas:**
  - Visualización automática desde `.dbml`
  - Exportación a múltiples formatos
  - Colaboración en tiempo real
  - Generación de SQL
- **Uso:** Importar `modelo-datos.dbml`

### Mermaid (Integrado en Markdown)
- **Tipo:** Sintaxis en Markdown
- **Ventajas:**
  - Se renderiza directamente en GitHub/GitLab
  - No requiere herramientas externas
  - Integrado en documentación
- **Nota:** Requiere actualización manual cuando cambia el modelo

### DBeaver (Aplicación Desktop)
- **Tipo:** Aplicación (gratis)
- **Ventajas:**
  - Genera diagramas desde base de datos existente
  - Soporte multi-DB (SQL Server, PostgreSQL, MySQL)
  - Ingeniería inversa desde BD

## Visualización en Cursor

### Opción 1: Diagrama Mermaid (Integrado)
El archivo `docs/modelo-datos.md` contiene un diagrama Mermaid que se renderiza directamente en Cursor:
1. Abre `docs/modelo-datos.md`
2. Presiona `Ctrl+Shift+V` (Windows/Linux) o `Cmd+Shift+V` (Mac)
3. El diagrama se renderizará automáticamente

### Opción 2: MCP Server (Avanzado)
Usa el servidor MCP `db-diagram` para visualizar el modelo desde el agente IA:
1. Instala dependencias: `cd mcp/db-diagram && npm install`
2. El servidor está configurado en `mcp.json`
3. Solicita al agente IA: "Visualiza el modelo de datos"
4. Ver `mcp/db-diagram/README.md` para más detalles

## Referencias

- **Documentación del modelo:** `docs/modelo-datos.md`
- **Regla de sincronización:** `.cursor/rules/14-dbml-sync-rule.md`
- **Sintaxis DBML:** https://dbdiagram.io/docs
- **MCP Server:** `mcp/db-diagram/README.md`