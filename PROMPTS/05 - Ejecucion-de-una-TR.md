Implementá la TR funcional ubicada en:
"docs/tareas/[NOMBRE_DEL_TR].md"

Esta TR es la FUENTE DE VERDAD del alcance.

Reglas generales:
- Implementar estrictamente las tareas definidas en la TR.
- No inventar funcionalidades fuera del alcance.
- No modificar HU ni TR sin documentarlo.
- Respetar las reglas del proyecto y de Cursor (.cursor/rules).

Implementación:
- Backend, Frontend, Tests y Documentación según lo indicado en la TR.
- Usar el layout de carpetas definido en el proyecto.
- Mantener consistencia con TRs ya implementadas.

Tests:
- Implementar unit tests, integration tests y E2E Playwright si la TR lo indica.
- Si la TR incluye cambios en frontend (servicios, componentes, utilidades): añadir también tests unitarios con Vitest en `frontend/src/` (p. ej. `*.test.ts` o `*.spec.ts` junto al código). Cubrir al menos servicios (llamadas API, transformación de datos) y, cuando aplique, utilidades o componentes aislados.
- En E2E:
  - Interacciones reales del usuario.
  - Assertions con expect sobre estado visible.
  - Prohibido usar waits ciegos (waitForTimeout, sleep, etc.).
  - Usar selectores estables (data-testid, roles accesibles).

Seguridad y calidad:
- Respetar validaciones, permisos y reglas de negocio.
- No revelar información sensible en mensajes de error.
- Mantener código claro y documentado.

Cierre obligatorio (trazabilidad):
- Actualizar el mismo archivo TR agregando o completando las secciones:
  - ## Archivos creados/modificados
  - ## Comandos ejecutados
  - ## Notas y decisiones
  - ## Pendientes / follow-ups
- Listar paths relativos al repositorio, agrupados por tipo (Backend, Frontend, DB, Tests, Docs).

Restricción:
- No ejecutar tareas fuera del alcance de esta TR.
