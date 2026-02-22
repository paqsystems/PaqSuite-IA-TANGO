# Documentación: .cursor/rules/06-openapi-documentacion.md

## Propósito

Regla del agente que establece la **obligación de documentar la API con OpenAPI** y cómo mantener actualizado `api-docs.json` (de forma automática en desarrollo o por comando/agente).

## Contenido

- Uso de L5-Swagger; archivo generado en `backend/storage/api-docs/api-docs.json`.
- UI en `/api/documentation` (URL completa en `docs/api/openapi.md`).
- Archivo base de anotaciones: `backend/app/OpenApi.php`.
- **Regla automática para el agente:** al modificar `backend/routes/api.php` o controladores bajo `backend/app/Http/Controllers/Api/`, debe actualizar anotaciones, ejecutar `php artisan l5-swagger:generate` y dejar actualizado `api-docs.json`.
- **Regeneración automática en desarrollo:** `L5_SWAGGER_GENERATE_ALWAYS=true` en `.env` hace que cada visita a `/api/documentation` regenere la spec.

## Uso

El agente debe seguir esta regla al añadir o modificar endpoints (documentar en OpenAPI, ejecutar generate e incluir el json actualizado en el cambio).
