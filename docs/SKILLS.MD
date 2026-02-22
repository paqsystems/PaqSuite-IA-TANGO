# Skills y Herramientas - Guía Completa

**Versión:** 1.0  
**Última actualización:** 2025-01-20

---

## 📋 Tabla de Contenidos

1. [¿Qué es MCP (Model Context Protocol)?](#qué-es-mcp-model-context-protocol)
2. [Conceptos Clave](#conceptos-clave)
3. [Ejemplos de Uso en el Proyecto](#ejemplos-de-uso-en-el-proyecto)
4. [Flujo de Trabajo](#flujo-de-trabajo)
5. [Ejemplo Práctico Completo](#ejemplo-práctico-completo)
6. [Links Recomendados para Estudiar](#links-recomendados-para-estudiar)
7. [Cómo Crear tu Propio Servidor MCP](#cómo-crear-tu-propio-servidor-mcp)
8. [Ventajas de MCP](#ventajas-de-mcp)
9. [Skills/Tools: Más Allá de MCP](#skillstools-más-allá-de-mcp)
10. [Comparación de Enfoques](#comparación-de-enfoques)
11. [Recomendación: Cuándo Usar Cada Enfoque](#recomendación-cuándo-usar-cada-enfoque)

---

## ¿Qué es MCP (Model Context Protocol)?

**MCP** es un protocolo estándar que permite a aplicaciones de IA (como Cursor) conectarse con servicios externos y herramientas mediante servidores MCP. Los **"skills"** o **"tools"** son las capacidades que exponen estos servidores.

---

## Conceptos Clave

### 1. Servidores MCP
Programas que exponen herramientas, recursos y prompts a través del protocolo MCP.

### 2. Tools (Herramientas/Skills)
Funciones que el servidor MCP expone para que la IA las use. Cada tool tiene:
- **Nombre**
- **Descripción**
- **Parámetros** (inputs)
- **Retorno** (outputs)

### 3. Recursos (Resources)
Datos o archivos que el servidor expone para lectura.

### 4. Prompts
Plantillas de prompts reutilizables.

---

## Ejemplos de Uso en el Proyecto

### 1. MCP Database (MySQL/MSSQL)

**Configuración en `mcp.json`:**
```json
"mysql": {
  "command": "toolbox.exe",
  "args": ["--prebuilt", "mysql", "--stdio"]
}
```

**Herramientas disponibles:**
- `execute_sql`: Ejecutar consultas SQL
- `list_tables`: Listar tablas
- `describe_table`: Ver estructura de una tabla

**Ejemplo de uso:**
```
"Ejecuta una consulta para ver todos los usuarios activos"
→ El agente usa execute_sql con: SELECT * FROM usuarios WHERE activo = 1
```

### 2. MCP Playwright

**Configuración en `mcp.json`:**
```json
"playwright": {
  "command": "npx",
  "args": ["-y", "@playwright/mcp"]
}
```

**Herramientas disponibles:**
- `navigate`: Navegar a una URL
- `click`: Hacer clic en elementos
- `fill`: Llenar formularios
- `screenshot`: Capturar pantalla
- `evaluate`: Ejecutar JavaScript

**Ejemplo de uso:**
```
"Toma un screenshot de la página de login"
→ El agente usa navigate + screenshot
```

### 3. MCP Jira (Atlassian)

**Configuración en `mcp.json`:**
```json
"atlassian": {
  "command": "npx",
  "args": ["-y", "mcp-remote@latest", "https://mcp.atlassian.com/v1/sse"]
}
```

**Herramientas disponibles:**
- `list_issues`: Listar tickets
- `create_issue`: Crear ticket
- `update_issue`: Actualizar ticket
- `get_project`: Obtener info del proyecto

**Ejemplo de uso:**
```
"Lista los issues asignados a mí en Jira"
→ El agente usa list_issues con filtros
```

### 4. MCP DB Diagram (Custom)

**Configuración en `mcp.json`:**
```json
"db-diagram": {
  "command": "node",
  "args": ["mcp/db-diagram/index.js"]
}
```

**Herramientas disponibles:**
- `visualizar_modelo`: Generar diagrama Mermaid
- `leer_dbml`: Leer archivo DBML
- `verificar_sincronizacion`: Verificar sincronización

**Ejemplo de uso:**
```
"Visualiza el modelo de datos"
→ El agente usa visualizar_modelo y muestra el diagrama
```

---

## Flujo de Trabajo

```
Usuario → Cursor AI → MCP Server → Tool Execution → Resultado → Usuario
```

1. El usuario hace una petición en lenguaje natural
2. Cursor AI identifica qué tool usar
3. Se conecta al servidor MCP correspondiente
4. Ejecuta la herramienta con los parámetros necesarios
5. Retorna el resultado al usuario

---

## Ejemplo Práctico Completo

**Escenario:** "Necesito ver la estructura de la tabla usuarios en MySQL"

1. Cursor detecta que necesita acceso a base de datos
2. Identifica el servidor MCP `mysql` configurado
3. Usa la tool `describe_table` con parámetro `"usuarios"`
4. El servidor ejecuta: `DESCRIBE usuarios` o equivalente
5. Retorna la estructura al usuario

---

## Links Recomendados para Estudiar

### Documentación Oficial

1. **Model Context Protocol - Especificación Oficial**
   - https://modelcontextprotocol.io
   - https://github.com/modelcontextprotocol/specification
   - Especificación completa del protocolo

2. **MCP TypeScript SDK**
   - https://github.com/modelcontextprotocol/typescript-sdk
   - Para crear servidores MCP personalizados

3. **Cursor MCP Documentation**
   - https://docs.cursor.com/mcp
   - Guía específica de Cursor para MCP

### Tutoriales y Guías

4. **Building MCP Servers**
   - https://modelcontextprotocol.io/docs/guides/building-servers
   - Guía paso a paso para crear servidores

5. **MCP Tools Examples**
   - https://github.com/modelcontextprotocol/servers
   - Repositorio con ejemplos de servidores MCP

### Servidores MCP Populares

6. **Playwright MCP**
   - https://github.com/playwright-community/playwright-mcp
   - Automatización web

7. **Database MCP Servers**
   - https://github.com/modelcontextprotocol/servers/tree/main/src/database
   - Ejemplos de servidores de base de datos

8. **Atlassian/Jira MCP**
   - https://github.com/modelcontextprotocol/servers/tree/main/src/atlassian
   - Integración con Jira

### Recursos Adicionales

9. **Awesome MCP**
   - https://github.com/modelcontextprotocol/awesome-mcp
   - Lista de recursos y servidores MCP

10. **MCP Community**
    - Discord/Slack de la comunidad MCP
    - Para preguntas y discusiones

---

## Cómo Crear tu Propio Servidor MCP

### Estructura Básica (TypeScript)

```typescript
import { Server } from "@modelcontextprotocol/sdk/server/index.js";
import { StdioServerTransport } from "@modelcontextprotocol/sdk/server/stdio.js";

const server = new Server({
  name: "mi-servidor-mcp",
  version: "1.0.0",
});

// Registrar una tool
server.setRequestHandler(ListToolsRequestSchema, async () => ({
  tools: [
    {
      name: "mi_herramienta",
      description: "Descripción de la herramienta",
      inputSchema: {
        type: "object",
        properties: {
          parametro: {
            type: "string",
            description: "Descripción del parámetro"
          }
        },
        required: ["parametro"]
      }
    }
  ]
}));

// Implementar la tool
server.setRequestHandler(CallToolRequestSchema, async (request) => {
  if (request.params.name === "mi_herramienta") {
    // Lógica de la herramienta
    return {
      content: [
        {
          type: "text",
          text: "Resultado de la herramienta"
        }
      ]
    };
  }
});
```

---

## Ventajas de MCP

1. **Estandarización:** Protocolo común para todas las herramientas
2. **Extensibilidad:** Fácil agregar nuevas herramientas
3. **Seguridad:** Control de qué herramientas están disponibles
4. **Integración:** Funciona con múltiples clientes (Cursor, Claude Desktop, etc.)
5. **Modularidad:** Cada servidor es independiente

---

## Skills/Tools: Más Allá de MCP

### ¿Los Skills Funcionan Solo para MCP?

**Respuesta corta:** No. Los "skills" no están limitados solo a MCP. Hay múltiples formas de crear capacidades personalizadas.

### 1. Skills en MCP (Model Context Protocol)

- ✅ Específicos del protocolo MCP
- ✅ Se exponen a través de servidores MCP
- ✅ Funcionan en Cursor, Claude Desktop y otros clientes compatibles
- ⚠️ Requieren implementar un servidor MCP

### 2. Herramientas Nativas de Cursor

Cursor tiene herramientas integradas que **NO requieren MCP**:

#### A. Codebase Search (Búsqueda Semántica)
- Búsqueda semántica en el código
- No requiere configuración
- **Ejemplo:** "¿Dónde se valida el login?"

#### B. File Operations
- Leer, escribir, crear archivos
- Operaciones de sistema de archivos
- **Ejemplo:** "Crea un componente React para el login"

#### C. Terminal/Shell Commands
- Ejecutar comandos del sistema
- Scripts y comandos CLI
- **Ejemplo:** "Ejecuta los tests"

#### D. Git Operations
- Operaciones de Git
- Commits, branches, etc.
- **Ejemplo:** "Haz commit de estos cambios"

### 3. Extensiones de VS Code/Cursor

Puedes crear extensiones que añaden capacidades:

```typescript
// Ejemplo: Extensión personalizada
import * as vscode from 'vscode';

export function activate(context: vscode.ExtensionContext) {
    // Registrar un comando
    let disposable = vscode.commands.registerCommand(
        'mi-extension.ejecutar-skill',
        () => {
            // Tu lógica personalizada
            vscode.window.showInformationMessage('Skill ejecutado!');
        }
    );
    
    context.subscriptions.push(disposable);
}
```

### 4. Scripts y Comandos Personalizados

Puedes crear scripts que el agente puede ejecutar:

```bash
# scripts/mi-skill.sh
#!/bin/bash
# Tu lógica personalizada
echo "Ejecutando skill personalizado"
```

Luego el agente puede ejecutarlo:
```
"Ejecuta el script scripts/mi-skill.sh"
```

### 5. API REST/Servicios Externos

Puedes crear servicios que el agente puede consumir:

```python
# api/mi-skill-service.py
from flask import Flask, jsonify

app = Flask(__name__)

@app.route('/api/mi-skill', methods=['POST'])
def ejecutar_skill():
    # Tu lógica
    return jsonify({"resultado": "éxito"})
```

### 6. Composer/Agents en Cursor

Cursor tiene un modo "Composer" donde puedes definir agentes con capacidades específicas:

```markdown
# .cursor/rules/mi-agente-personalizado.md

## Agente de Optimización de Código

**Capacidades:**
- Analizar complejidad ciclomática
- Sugerir refactorizaciones
- Generar tests de cobertura

**Herramientas disponibles:**
- Análisis estático de código
- Generación de métricas
- Sugerencias de mejora
```

---

## Comparación de Enfoques

Ver /docs/skill.jp

---

## Recomendación: Cuándo Usar Cada Enfoque

### Usa MCP cuando:
- ✅ Necesitas integración profunda con la IA
- ✅ Quieres que funcione en múltiples clientes (Cursor, Claude Desktop)
- ✅ Necesitas un protocolo estándar
- ✅ Quieres que la IA invoque automáticamente las herramientas

### Usa Extensiones VS Code cuando:
- ✅ Necesitas integración con el editor
- ✅ Quieres UI personalizada
- ✅ Solo necesitas que funcione en VS Code/Cursor

### Usa Scripts cuando:
- ✅ La tarea es simple y puntual
- ✅ No necesitas integración profunda con la IA
- ✅ Quieres algo rápido de implementar

### Usa API REST cuando:
- ✅ Necesitas un servicio independiente
- ✅ Quieres que sea accesible desde múltiples lugares
- ✅ Necesitas escalabilidad

---

## Ejemplos Prácticos de Skills No-MCP

### Ejemplo 1: Skill de Validación de Código

```bash
# scripts/validar-codigo.sh
#!/bin/bash
echo "Validando código..."
npm run lint
npm run type-check
npm run test
echo "Validación completada"
```

**Uso:** "Valida el código antes de hacer commit"

### Ejemplo 2: Skill de Generación de Documentación

```python
# scripts/generar-docs.py
import os
import subprocess

def generar_documentacion():
    # Generar docs desde código
    subprocess.run(['npm', 'run', 'docs'])
    print("Documentación generada")

if __name__ == '__main__':
    generar_documentacion()
```

**Uso:** "Genera la documentación del proyecto"

### Ejemplo 3: Skill de Análisis de Dependencias

```javascript
// scripts/analizar-dependencias.js
const fs = require('fs');
const packageJson = JSON.parse(fs.readFileSync('package.json'));

console.log('Dependencias instaladas:', Object.keys(packageJson.dependencies).length);
console.log('DevDependencies:', Object.keys(packageJson.devDependencies).length);
```

**Uso:** "Analiza las dependencias del proyecto"

---

## Conclusión

Los **"skills"** no están limitados a MCP. Puedes crear capacidades personalizadas mediante:

1. ✅ **MCP Servers** (para integración profunda con IA)
2. ✅ **Extensiones VS Code/Cursor** (para integración con editor)
3. ✅ **Scripts y comandos** (para tareas simples)
4. ✅ **API REST/Servicios** (para servicios independientes)
5. ✅ **Herramientas nativas de Cursor** (ya disponibles)

La elección depende de tus necesidades específicas. Para integración profunda con la IA, **MCP es la mejor opción**. Para tareas simples, **scripts pueden ser suficientes**.

---

## Referencias en el Proyecto

- **Configuración MCP:** `mcp.json`
- **Servidor MCP Personalizado:** `mcp/db-diagram/`
- **Documentación de Agentes:** `.cursor/Docs/Agentes-y-mcp.md`
- **Herramientas Database:** `mcp/toolbox-db/tools.yaml`

---

**Última actualización:** 2025-01-20
