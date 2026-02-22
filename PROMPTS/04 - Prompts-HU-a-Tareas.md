# Modelos de Prompts para pasar Historias de Usuario a Tareas

## HU Simple

Actuá como ingeniero senior responsable del diseño del MVP.

Usá SOLO la regla
".cursor/rules/13-user-story-to-task-breakdown.md"
como fuente de verdad.

Tarea:
A partir de la Historia de Usuario provista,
generar el plan completo de tareas/tickets
y guardarlo como archivo Markdown.
El TR generado debe incluir al final las secciones de trazabilidad (archivos/comandos/notas/pendientes), inicialmente vacías.

Archivo:
- Ruta: docs/hu-tareas/
- Nombre: igual al nombre del HU, reemplazando "HU" por "TR".
- Si existe, regenerarlo desde cero (overwrite total).

Prohibido:
- modificar otros archivos,
- inventar features,
- omitir tests, docs o tareas de calidad.

Permitido:
- declarar supuestos explícitos.

Historia de Usuario:
---
[HU]
---

## HU Complejas

### Paso 1
Actuá como ingeniero senior responsable del refinamiento funcional del MVP.

Usá exclusivamente la regla
".cursor/rules/13-user-story-to-task-breakdown.md"
como fuente de verdad.

Objetivo:
Refinar la siguiente Historia de Usuario compleja
antes de descomponerla en tareas.

Instrucciones:
- NO generar tareas todavía.
- NO crear archivos aún.
- Refinar únicamente:
  1) HU refinada
  2) Criterios de aceptación (claros y verificables)
  3) Reglas de negocio
  4) Roles y permisos (cliente / empleado / supervisor)
  5) Supuestos explícitos
  6) Preguntas abiertas (si las hay)

Restricciones:
- No inventar funcionalidades fuera del alcance.
- Si falta información, declararla como supuesto o pregunta.
- Mantener coherencia con el MVP de "Lidr - Trabajo Final".

Historia de Usuario:
---
[TÍTULO EXACTO DE LA HU]
[DESCRIPCIÓN COMPLETA DE LA HU]
---

### Paso 2

Usando EXCLUSIVAMENTE el refinamiento validado
de la Historia de Usuario anterior,
generá el plan completo de tareas/tickets del MVP.

Regla obligatoria:
".cursor/rules/13-user-story-to-task-breakdown.md"

Salida requerida:
- Crear (o regenerar si ya existe) un archivo Markdown.
- Ruta: docs/hu-tareas/
- Nombre del archivo:
  "TR-[TITULO_DE_LA_HISTORIA_DE_USUARIO].md"
  (reemplazar espacios por guiones).

Reglas del archivo:
- Si el archivo existe, sobrescribirlo completamente.
- Seguir estrictamente el orden y las secciones de la regla.
- El documento debe ser usable como checklist de validación del MVP.

Cobertura mínima obligatoria:
- Impacto en datos (tablas, migraciones, seed).
- Backend (APIs, validaciones, errores).
- Frontend (pantallas, estados, validaciones).
- Tests:
  - Unit tests
  - Integration tests
  - ≥1 flujo E2E con Playwright (sin waits ciegos).
- Documentación:
  - OpenAPI/Swagger si aplica
  - Actualización de docs afectadas
  - Registro en ia-log.md
- DevOps/CI-CD si aplica.

Restricciones:
- No inventar funcionalidades.
- Declarar supuestos explícitos dentro del archivo.
- No modificar otros archivos del proyecto.

Historia de Usuario:
---
[TÍTULO EXACTO DE LA HU]
---


