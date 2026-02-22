# MCP Server - DB Diagram

Servidor MCP para visualizar el modelo de datos del proyecto desde el archivo DBML.

## Descripción

Este servidor MCP proporciona herramientas para:
- Visualizar el modelo de datos como diagrama Mermaid
- Leer el contenido del archivo DBML
- Verificar sincronización entre DBML y documentación Markdown

## Instalación

```bash
cd mcp/db-diagram
npm install
```

## Configuración en mcp.json

Agregar al archivo `mcp.json` en la raíz del proyecto:

```json
{
  "mcpServers": {
    "db-diagram": {
      "command": "node",
      "args": [
        "C:\\Programacion\\Lidr\\Lidr-AI4Devs2025-ProyectoFinal\\mcp\\db-diagram\\index.js"
      ]
    }
  }
}
```

**Nota:** Ajustar la ruta según tu sistema operativo y ubicación del proyecto.

## Herramientas Disponibles

### 1. `visualizar_modelo`
Genera un diagrama Mermaid del modelo de datos.

**Parámetros:**
- `formato` (opcional): `"mermaid"` o `"texto"` (default: `"mermaid"`)

**Uso:**
- Solicitar al agente IA: "Visualiza el modelo de datos"
- El agente usará esta herramienta y mostrará el diagrama Mermaid

### 2. `leer_dbml`
Lee el contenido completo del archivo `database/modelo-datos.dbml`.

**Uso:**
- Solicitar al agente IA: "Lee el archivo DBML del modelo"

### 3. `verificar_sincronizacion`
Verifica si el archivo DBML está sincronizado con la documentación Markdown.

**Uso:**
- Solicitar al agente IA: "Verifica la sincronización del modelo de datos"

## Recursos Disponibles

### `dbml://modelo-datos`
Recurso que expone el contenido del archivo DBML.

### `mermaid://modelo-diagrama`
Recurso que expone el diagrama Mermaid generado.

## Visualización en Cursor

Para ver el diagrama Mermaid renderizado en Cursor:

1. **Vista Previa de Markdown:**
   - Abre `docs/modelo-datos.md`
   - Presiona `Ctrl+Shift+V` (Windows/Linux) o `Cmd+Shift+V` (Mac)
   - El diagrama Mermaid se renderizará automáticamente

2. **Extensión (Opcional):**
   - Instala "Markdown Preview Mermaid Support" en Cursor/VS Code
   - Mejora el renderizado de diagramas Mermaid

## Uso con Agente IA

El agente IA puede usar estas herramientas automáticamente cuando:
- Se solicita visualizar el modelo de datos
- Se necesita verificar la estructura de la base de datos
- Se requiere información sobre relaciones entre tablas

**Ejemplos de prompts:**
- "Muéstrame el diagrama del modelo de datos"
- "¿Está sincronizado el DBML con el markdown?"
- "Lee el archivo DBML y dime cuántas tablas tiene"

## Notas

- El servidor lee el archivo `database/modelo-datos.dbml`
- El diagrama Mermaid se genera automáticamente desde el DBML
- La sincronización es una verificación básica (conteo de tablas/entidades)
