# Regla: Documentación OpenAPI de la API

## Objetivo

Mantener la API REST documentada con **OpenAPI 3.x** (Swagger) como fuente de verdad del contrato.

## Obligatorio

1. **Generar y exponer la documentación**
   - Usar **L5-Swagger** (paquete `darkaonline/l5-swagger`) en el backend Laravel.
   - Especificación generada en **`backend/storage/api-docs/api-docs.json`**.
   - **UI de Swagger** accesible en la ruta: **`/api/documentation`** (ver `docs/api/openapi.md` para la URL completa).

2. **Base de la especificación**
   - Archivo base de anotaciones: **`backend/app/OpenApi.php`** (info, servers, components, securitySchemes, schemas del envelope).
   - Versión OpenAPI: **3.0.3** como mínimo.

3. **Al añadir o modificar endpoints (regla automática para el agente)**
   - Siempre que se modifique **`backend/routes/api.php`** o cualquier controlador bajo **`backend/app/Http/Controllers/Api/`**, el agente **debe**:
     1. Añadir o actualizar las anotaciones OpenAPI en el controlador (o en `app/OpenApi.php`) para el endpoint afectado.
     2. Ejecutar **`php artisan l5-swagger:generate`** desde el directorio **`backend/`**.
     3. Incluir en el cambio el archivo **`backend/storage/api-docs/api-docs.json`** actualizado (para que la spec quede versionada).
   - Mantener alineados los archivos en `specs/endpoints/*.md` con la especificación OpenAPI si existen.

4. **Formato de respuestas documentadas**
   - Reflejar el envelope estándar: `error`, `respuesta`, `resultado` (schemas `ApiEnvelope` / `ApiErrorResponse` en la spec).
   - Autenticación: scheme **bearerAuth** (Bearer token, Sanctum).

## Regeneración automática en desarrollo

- Con **`L5_SWAGGER_GENERATE_ALWAYS=true`** en `.env` (solo desarrollo), cada vez que se abre o recarga `/api/documentation` se regenera `api-docs.json`. Así la doc se actualiza sin ejecutar el comando a mano.
- En producción dejar **`L5_SWAGGER_GENERATE_ALWAYS=false`** (o no definir la variable) por rendimiento.

## URL de acceso

- **Desarrollo (local):** `http://localhost:8000/api/documentation`
- **Otros entornos:** `{URL_BASE_DEL_BACKEND}/api/documentation`

Documentación detallada: **`docs/api/openapi.md`**.

## CI/CD

- Incluir en el build/deploy la generación de la documentación: `php artisan l5-swagger:generate` (según `docs/06-operacion/deploy-infraestructura.md`).
