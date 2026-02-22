# Guías de Prompting para IA

## Descripción General

Este documento proporciona guías y mejores prácticas para interactuar con herramientas de IA (como Cursor, ChatGPT, etc.) durante el desarrollo del proyecto MVP.

---

## Principios Generales

### 1. Contexto Completo

Siempre proporciona contexto suficiente:
- **Qué** quieres hacer
- **Por qué** lo necesitas
- **Dónde** debe implementarse
- **Restricciones** o limitaciones

### 2. Especificidad

Sé específico en tus solicitudes:
- ✅ "Agregar validación de fecha no futura en el formulario de registro de tarea"
- ❌ "Mejora el formulario"

### 3. Referencias a Documentación

Cuando sea relevante, referencia documentación existente:
- "Según `specs/endpoints/time-entries-create.md`, agregar..."
- "Siguiendo las reglas en `.cursor/rules/09-data-access-orm-sql.md`..."

---

## Estructura de Prompts Efectivos

### Template Básico

```
[CONTEXTO]
[OBJETIVO]
[ALCANCE]
[RESTRICCIONES]
[ENTREGABLES ESPERADOS]
```

### Ejemplo

```
CONTEXTO: Estoy trabajando en el MVP Sistema de Registro de Tareas, 
específicamente en el endpoint de creación de tareas.

OBJETIVO: Agregar validación de que la fecha no puede ser futura.

ALCANCE: 
- Backend: Validación en el Request y en el modelo
- Frontend: Validación en el formulario antes de enviar
- Tests: Agregar casos de prueba para esta validación

RESTRICCIONES:
- No cambiar la estructura de la API
- Mantener compatibilidad con código existente
- Seguir el formato de error estándar (código 1203)

ENTREGABLES ESPERADOS:
- Código de validación en backend
- Validación en frontend
- Tests unitarios e integración
```

---

## Tipos de Prompts

### 1. Generación de Código

**Cuándo usar:** Necesitas código nuevo o modificar existente.

**Estructura:**
```
Genera [tipo de código] para [funcionalidad] que:
- [requisito 1]
- [requisito 2]
- [requisito 3]

Sigue las convenciones del proyecto:
- [convención 1]
- [convención 2]

Referencias:
- [archivo/documento relevante]
```

**Ejemplo:**
```
Genera un componente React para el formulario de registro de tarea que:
- Use los test-ids definidos en .cursor/rules/10-i18n-and-testid.md
- Implemente validaciones según specs/rules/validation-rules.md
- Use i18n para todos los textos
- Maneje estados de loading y error

Sigue las convenciones:
- PascalCase para componentes
- camelCase para funciones
- Usar TypeScript

Referencias:
- docs/frontend/frontend-specifications.md
- specs/endpoints/time-entries-create.md
```

---

### 2. Refactorización

**Cuándo usar:** Necesitas mejorar código existente sin cambiar funcionalidad.

**Estructura:**
```
Refactoriza [archivo/función] para:
- [objetivo 1]
- [objetivo 2]

Manteniendo:
- [comportamiento que no debe cambiar]
- [interfaz que no debe cambiar]

Consideraciones:
- [consideración especial]
```

**Ejemplo:**
```
Refactoriza el servicio de tareas (tasks.service.ts) para:
- Usar async/await en lugar de Promises con .then()
- Agregar manejo de errores más robusto
- Separar lógica de transformación de datos

Manteniendo:
- La misma interfaz pública
- Compatibilidad con componentes existentes

Consideraciones:
- No romper tests existentes
```

---

### 3. Debugging

**Cuándo usar:** Necesitas encontrar y corregir un error.

**Estructura:**
```
Tengo un error en [ubicación]:
[Descripción del error]
[Stack trace o mensaje de error]

El código relevante es:
[código o referencia a archivo]

He intentado:
- [intento 1]
- [intento 2]

Contexto adicional:
- [información relevante]
```

**Ejemplo:**
```
Tengo un error en el endpoint POST /api/v1/tareas:
Error 500: "SQLSTATE[23000]: Integrity constraint violation"

El código relevante está en:
app/Http/Controllers/TaskController.php línea 45

He intentado:
- Verificar que el cliente_id existe
- Verificar que el tipo_tarea_id existe

Contexto adicional:
- La validación pasa en el Request
- El error ocurre al hacer create() en Eloquent
```

---

### 4. Documentación

**Cuándo usar:** Necesitas crear o actualizar documentación.

**Estructura:**
```
Genera documentación para [tema] que incluya:
- [sección 1]
- [sección 2]
- [sección 3]

Formato:
- [formato deseado: Markdown, etc.]

Audiencia:
- [para quién es la documentación]

Referencias:
- [documentos relacionados]
```

**Ejemplo:**
```
Genera documentación para el nuevo campo is_generico en TipoTarea que incluya:
- Descripción del campo
- Significado semántico
- Reglas de negocio asociadas (si las hay)
- Ejemplos de uso

Formato: Markdown

Audiencia: Desarrolladores del equipo

Referencias:
- docs/modelo-datos.md
- backend/app/Models/TipoTarea.php
```

---

### 5. Testing

**Cuándo usar:** Necesitas crear o mejorar tests.

**Estructura:**
```
Genera tests para [componente/función] que cubran:
- [caso de prueba 1]
- [caso de prueba 2]
- [caso de prueba 3]

Herramientas:
- [Jest, Vitest, Playwright, etc.]

Cobertura objetivo:
- [porcentaje o casos específicos]
```

**Ejemplo:**
```
Genera tests E2E con Playwright para el flujo completo:
- Login exitoso
- Registro de tarea
- Visualización de resumen

Herramientas: Playwright

Cobertura objetivo: Flujo E2E principal según specs/flows/e2e-core-flow.md
```

---

## Mejores Prácticas

### 1. Iteración Incremental

No pidas todo de una vez. Divide en pasos:

```
Paso 1: Genera la estructura básica del componente
Paso 2: Agrega la lógica de validación
Paso 3: Agrega el manejo de errores
Paso 4: Agrega los tests
```

### 2. Validación de Resultados

Siempre revisa el código generado:
- ¿Sigue las convenciones del proyecto?
- ¿Está completo?
- ¿Hay errores obvios?
- ¿Cumple con los requisitos?

### 3. Documentación de Cambios

Documenta los cambios importantes:
- Qué se generó
- Qué se ajustó manualmente
- Por qué se hizo el ajuste

### 4. Uso de Reglas del Proyecto

Referencia las reglas existentes:
- `.cursor/rules/` - Reglas de desarrollo
- `specs/` - Especificaciones
- `docs/` - Documentación

---

## Prompts por Fase del Proyecto

### Fase de Diseño

```
Genera [especificación/documento] para [funcionalidad] considerando:
- Alcance del MVP
- Flujo E2E prioritario
- Restricciones técnicas
```

### Fase de Implementación

```
Implementa [funcionalidad] siguiendo:
- Especificaciones en specs/
- Reglas en .cursor/rules/
- Convenciones del proyecto
```

### Fase de Testing

```
Genera tests para [componente] que:
- Cubran casos críticos
- Sigan la estrategia en .cursor/rules/12-testing.md
- Usen test-ids apropiados
```

---

## Errores Comunes a Evitar

### ❌ Prompts Vagos

```
"Mejora el código"
"Arregla el bug"
"Haz el formulario"
```

### ❌ Sin Contexto

```
"Agrega validación"
```

### ❌ Múltiples Objetivos

```
"Genera el backend, frontend, tests y documentación para todo el sistema"
```

### ✅ Prompts Mejorados

```
"Agrega validación de fecha no futura en el formulario de registro de tarea, 
siguiendo las reglas en specs/rules/validation-rules.md y usando el test-id 
'task-entry-date-input' definido en .cursor/rules/10-i18n-and-testid.md"
```

---

## Herramientas y Recursos

### Documentos de Referencia

- `PROJECT_CONTEXT.md` - Contexto general
- `.cursor/rules/` - Reglas de desarrollo
- `specs/` - Especificaciones técnicas
- `docs/` - Documentación del proyecto

### Comandos Útiles

```bash
# Buscar en documentación
grep -r "palabra clave" docs/ specs/

# Ver estructura del proyecto
tree -L 2

# Verificar convenciones
cat .cursor/rules/*.mdc
```

---

## Ejemplos de Prompts Exitosos

### Ejemplo 1: Generación de Componente

```
Genera un componente React TypeScript llamado TaskEntryForm que:
1. Implemente el formulario de registro de tarea según specs/endpoints/time-entries-create.md
2. Use todos los test-ids definidos en .cursor/rules/10-i18n-and-testid.md
3. Implemente validaciones del frontend según docs/frontend/frontend-specifications.md
4. Use i18n para todos los textos (locales/tasks.json)
5. Maneje estados de loading, error y success
6. Use el servicio tasksService para enviar datos a la API

Sigue las convenciones:
- PascalCase para el componente
- camelCase para funciones
- TypeScript estricto
- Hooks de React (no clases)

Referencias:
- docs/frontend/frontend-specifications.md
- specs/endpoints/time-entries-create.md
- .cursor/rules/07-frontend-norms.md
```

### Ejemplo 2: Corrección de Bug

```
Tengo un error 422 en el endpoint POST /api/v1/tareas cuando envío:
{
  "fecha": "2025-01-20",
  "cliente_id": 1,
  "tipo_tarea_id": 1,
  "duracion_minutos": 120,
  "sin_cargo": false,
  "presencial": false
}

El error dice: "El campo sin_cargo es requerido"

Revisa:
- El modelo RegistroTarea
- El Request de validación
- La migración de la tabla

El código está en:
- app/Http/Requests/CreateTaskRequest.php
- app/Models/RegistroTarea.php
- database/migrations/..._create_registro_tarea_table.php

Según specs/endpoints/time-entries-create.md, sin_cargo debería tener default false.
```

---

## Referencias

- `PROJECT_CONTEXT.md` - Contexto del proyecto
- `.cursor/rules/` - Reglas de desarrollo

---

**Última actualización:** 2025-01-20

