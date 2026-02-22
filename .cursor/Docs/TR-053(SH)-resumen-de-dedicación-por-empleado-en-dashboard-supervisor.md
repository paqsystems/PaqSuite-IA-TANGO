# Documentación: TR-053(SH)-resumen-de-dedicación-por-empleado-en-dashboard-supervisor.md

## Ubicación
`docs/hu-tareas/TR-053(SH)-resumen-de-dedicación-por-empleado-en-dashboard-supervisor.md`

## Propósito
Plan de tareas derivado de **HU-053(SH)**: resumen de dedicación por empleado en el dashboard, solo para supervisores. Generado con Prompt 8 (HU → TR) y la regla `.cursor/rules/13-user-story-to-task-breakdown.md`.

## Contenido resumido
- Sección "Dedicación por Empleado" en el dashboard (solo supervisor).
- Lista/tabla top N empleados (nombre, total horas, cantidad tareas, % opcional); orden por horas desc; total general.
- Enlace "Ver detalle" a Tareas por Empleado.
- Backend: asegurar `top_empleados` en GET /api/v1/dashboard para supervisor.
- Frontend: bloque condicional por rol; E2E por rol.

## Dependencias
- HU-051 / TR-051 (Dashboard principal).
