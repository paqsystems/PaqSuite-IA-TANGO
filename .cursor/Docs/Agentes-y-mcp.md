# Agents

## Descripción General

Este documento describe los agentes de IA y automatización utilizados en el proyecto, sus responsabilidades y configuración.

## Agentes Disponibles

### Agente de Desarrollo

**Propósito:** Asistencia en desarrollo de código, refactorización y resolución de problemas.

**Capacidades:**
- Generación de código
- Refactorización
- Resolución de errores
- Optimización de código
- Generación de tests

**Configuración:**
- Herramienta: Cursor AI Assistant
- Contexto: Todo el proyecto
- Acceso: Código fuente, documentación, configuración

---

### Agente de Documentación

**Propósito:** Generación y mantenimiento de documentación del proyecto.

**Capacidades:**
- Generación de documentación técnica
- Actualización de README
- Creación de guías de usuario
- Mantenimiento de documentación de API

**Configuración:**
- Herramienta: Cursor AI Assistant
- Contexto: Archivos de documentación
- Acceso: Código fuente para análisis

---

### Agente de Testing

**Propósito:** Generación y mantenimiento de tests.

**Capacidades:**
- Generación de tests unitarios
- Generación de tests de integración
- Análisis de cobertura
- Sugerencias de mejoras en tests

**Configuración:**
- Herramienta: Cursor AI Assistant
- Contexto: Código fuente y tests existentes
- Acceso: Estructura de tests, frameworks de testing

---

### Agente de CI/CD

**Propósito:** Configuración y mantenimiento de pipelines de CI/CD.

**Capacidades:**
- Generación de configuraciones de CI/CD
- Optimización de pipelines
- Resolución de problemas de build
- Sugerencias de mejoras en deployment

**Configuración:**
- Herramienta: Cursor AI Assistant
- Contexto: Archivos de configuración CI/CD
- Acceso: Dockerfiles, scripts de deployment

---

## Integraciones MCP

### MCP Jira Agent

**Propósito:** Interacción con Jira mediante Model Context Protocol.

**Capacidades:**
- Listar issues
- Crear/actualizar issues
- Consultar proyectos
- Obtener información de tickets

**Configuración:**
```json
{
  "atlassian": {
    "command": "npx",
    "args": [
      "-y",
      "mcp-remote@latest",
      "https://mcp.atlassian.com/v1/sse"
    ]
  }
}
```

---

### MCP Playwright Agent

**Propósito:** Automatización web mediante Playwright.

**Capacidades:**
- Navegación web
- Interacción con elementos
- Captura de screenshots
- Ejecución de scripts

**Configuración:**
```json
{
  "playwright": {
    "command": "npx",
    "args": [
      "-y",
      "@playwright/mcp"
    ]
  }
}
```

---

### MCP Database Agents

**Propósito:** Interacción con bases de datos mediante MCP.

**Capacidades:**
- Ejecutar consultas SQL
- Listar tablas
- Describir esquemas
- Verificar integridad

**Bases de Datos Soportadas:**
- MySQL
- MSSQL (SQL Server)

**Configuración:**
- Ver `mcp.json` para configuración detallada

---

## Flujo de Trabajo con Agentes

### Desarrollo de Nueva Funcionalidad

1. **Agente de Desarrollo:** Genera código inicial
2. **Agente de Testing:** Genera tests correspondientes
3. **Agente de Documentación:** Actualiza documentación
4. **Agente de CI/CD:** Valida configuración si es necesario

### Resolución de Bugs

1. **Agente de Desarrollo:** Analiza el problema
2. **Agente de Testing:** Genera tests para reproducir el bug
3. **Agente de Desarrollo:** Implementa la solución
4. **Agente de Testing:** Valida la solución

### Refactorización

1. **Agente de Desarrollo:** Identifica código a refactorizar
2. **Agente de Testing:** Verifica que los tests existentes cubran el código
3. **Agente de Desarrollo:** Realiza la refactorización
4. **Agente de Testing:** Valida que todos los tests pasen

## Configuración de Agentes

### Variables de Entorno

Los agentes pueden requerir variables de entorno específicas:

```env
# Jira
JIRA_URL=https://your-company.atlassian.net
JIRA_EMAIL=your-email@example.com
JIRA_API_TOKEN=your-api-token

# Databases
MYSQL_HOST=127.0.0.1
MYSQL_PORT=3306
MYSQL_DATABASE=your-database
MYSQL_USER=your-user
MYSQL_PASSWORD=your-password

MSSQL_HOST=your-server.database.windows.net
MSSQL_PORT=1433
MSSQL_DATABASE=your-database
MSSQL_USER=your-user
MSSQL_PASSWORD=your-password
```

### Permisos y Acceso

- Los agentes tienen acceso de lectura a todo el proyecto
- Los agentes pueden modificar archivos según su propósito
- Los agentes no deben modificar archivos de configuración críticos sin confirmación

## Mejores Prácticas

### Uso de Agentes

1. **Revisar siempre el código generado**
   - No confiar ciegamente en el código generado
   - Validar que cumple con los requisitos

2. **Proporcionar contexto suficiente**
   - Describir claramente lo que se necesita
   - Proporcionar ejemplos cuando sea posible

3. **Iterar y refinar**
   - Los agentes pueden necesitar múltiples iteraciones
   - Proporcionar feedback para mejorar resultados

### Seguridad

1. **No compartir credenciales sensibles**
   - Usar variables de entorno
   - No incluir credenciales en prompts

2. **Validar cambios en código crítico**
   - Revisar especialmente código de autenticación
   - Validar cambios en configuración de seguridad

## Troubleshooting

### Problemas Comunes

**Agente no responde:**
- Verificar que el servicio MCP esté corriendo
- Verificar configuración en `mcp.json`
- Revisar logs de errores

**Resultados inesperados:**
- Proporcionar más contexto
- Especificar requisitos más claramente
- Revisar documentación del agente

**Errores de conexión:**
- Verificar credenciales
- Verificar conectividad de red
- Revisar configuración de firewall

## Roadmap

### Mejoras Futuras

- [ ] Agente especializado en optimización de rendimiento
- [ ] Agente de análisis de seguridad
- [ ] Agente de internacionalización
- [ ] Integración con más servicios MCP

## Notas

- Este documento debe actualizarse conforme se agreguen nuevos agentes
- Documentar cualquier configuración especial requerida
- Mantener registro de problemas y soluciones

---

**Última actualización:** [Fecha]

