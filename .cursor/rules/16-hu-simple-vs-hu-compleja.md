# Modelos de Prompts – Historias de Usuario a Tareas (HU → TR)

Este archivo define los prompts reutilizables para convertir
Historias de Usuario (HU) en Tareas de Implementación (TR).

⚠️ Este archivo **no se ejecuta directamente**.
Es utilizado por el dispatcher definido en:
`.cursor/rules/16-prompt-dispatcher.md`

---

## =========================
## PROMPT: HU SIMPLE
## =========================

Actuá como ingeniero senior responsable del diseño del MVP.

Usá **SOLO** la regla:
`.cursor/rules/13-user-story-to-task-breakdown.md`
como fuente de verdad para la descomposición.

Tarea:
A partir de la Historia de Usuario provista,
generar el plan completo de tareas/tickets del MVP
y guardarlo como archivo Markdown.

Archivo a generar:
- Ruta: `docs/04-tareas/` en la **misma subcarpeta** en que se encuentra la HU.
  Ejemplo: si la HU está en `docs/03-historias-usuario/001-Seguridad/HU-010-xxx.md`,
  el TR va en `docs/04-tareas/001-Seguridad/TR-010-xxx.md`.
- Nombre: igual al nombre del archivo HU,
  reemplazando el prefijo `HU-` por `TR-`.
- Si el archivo existe, **regenerarlo desde cero**
  (overwrite total).

Reglas obligatorias:
- No inventar funcionalidades fuera del alcance de la HU.
- Respetar estrictamente los criterios de aceptación.
- Incluir tareas de:
  - Base de Datos
  - Backend
  - Frontend
  - Tests (unit, integration, ≥1 E2E Playwright si aplica)
  - Documentación
- Mantener consistencia con las reglas del proyecto y `.cursor/rules`.

Trazabilidad obligatoria:
El TR generado debe incluir al final las secciones:
- `## Archivos creados/modificados`
- `## Comandos ejecutados`
- `## Notas y decisiones`
- `## Pendientes / follow-ups`

Estas secciones deben crearse inicialmente vacías.

Historia de Usuario:
---
[HU]
---

---

## =========================
## PROMPT: HU COMPLEJA
## =========================

### PASO 1 – REFINAMIENTO (SIN GENERAR TAREAS)

Actuá como ingeniero senior responsable del refinamiento funcional del MVP.

Usá **SOLO** la regla:
`.cursor/rules/13-user-story-to-task-breakdown.md`
como fuente de verdad.

Objetivo:
Refinar la siguiente Historia de Usuario compleja
antes de descomponerla en tareas.

Instrucciones:
- NO generar tareas.
- NO crear archivos.
- Refinar únicamente:
  1. Historia de Usuario refinada
  2. Criterios de aceptación (claros y verificables)
  3. Reglas de negocio
  4. Roles y permisos (cliente / empleado / supervisor)
  5. Suposiciones explícitas
  6. Preguntas abiertas (si las hay)

Restricciones:
- No inventar funcionalidades fuera del alcance.
- Mantener coherencia con el MVP del proyecto.
- Si falta información, declararla explícitamente.

Historia de Usuario:
---
[HU]
---

---

### PASO 2 – GENERACIÓN DE TR

Usando **EXCLUSIVAMENTE** el refinamiento validado
de la Historia de Usuario anterior,
generar el plan completo de tareas/tickets del MVP.

Usar como regla obligatoria:
`.cursor/rules/13-user-story-to-task-breakdown.md`

Archivo a generar:
- Ruta: `docs/04-tareas/` en la **misma subcarpeta** en que se encuentra la HU.
  Ejemplo: si la HU está en `docs/03-historias-usuario/001-Seguridad/HU-010-xxx.md`,
  el TR va en `docs/04-tareas/001-Seguridad/TR-010-xxx.md`.
- Nombre: igual al nombre del archivo HU,
  reemplazando el prefijo `HU-` por `TR-`.
- Si el archivo existe, **regenerarlo completamente**.

Cobertura mínima obligatoria:
- Impacto en datos:
  - tablas afectadas
  - migraciones (up / down)
  - seeds mínimos
- Backend:
  - endpoints
  - validaciones
  - manejo de errores
- Frontend:
  - pantallas/componentes
  - estados (loading, error, empty)
  - validaciones
- Tests:
  - unit tests
  - integration tests
  - ≥1 flujo E2E con Playwright
    - interacciones reales
    - assertions con `expect`
    - prohibido usar waits ciegos
- Documentación:
  - OpenAPI / Swagger si aplica
  - actualización de documentación afectada
- DevOps / CI-CD si aplica

Trazabilidad obligatoria:
El TR debe incluir y completar las secciones:
- `## Archivos creados/modificados`
- `## Comandos ejecutados`
- `## Notas y decisiones`
- `## Pendientes / follow-ups`

Restricciones finales:
- No inventar funcionalidades.
- No modificar HU ni TR fuera del alcance.
- Declarar supuestos explícitos dentro del TR.

Historia de Usuario:
---
[HU]
---
