# Documentación: TR-055(SH)-actualización-automática-del-dashboard.md

## Ubicación
`docs/hu-tareas/TR-055(SH)-actualización-automática-del-dashboard.md`

## Propósito
Plan de tareas derivado de **HU-055(SH)**: actualización automática del dashboard. Generado con Prompt 8 (HU → TR) y la regla `.cursor/rules/13-user-story-to-task-breakdown.md`.

## Contenido resumido
- Actualización automática cada X minutos (configurable); indicador "Actualizado hace X min"; botón "Actualizar"; indicador de carga; refresco sin recargar página; filtros por rol respetados.
- Frontend: timer (setInterval), estado última actualización, botón y loading; limpieza del timer al desmontar.
- Sin cambios de API; reutiliza GET /api/v1/dashboard.

## Dependencias
- HU-051; TR-051 (Dashboard principal).
