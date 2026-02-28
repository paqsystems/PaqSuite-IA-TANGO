# Documentación OpenAPI / Swagger de la API

## URL de acceso a la documentación (Swagger UI)

Con el backend en ejecución (`php artisan serve` o el servidor configurado):

| Entorno   | URL de la documentación (Swagger UI) |
|-----------|--------------------------------------|
| Local     | **http://localhost:8000/api/documentation** |
| Producción| **{URL_BASE_BACKEND}/api/documentation** |

Ejemplo: si el backend está en `https://api.miapp.com`, la documentación está en  
**https://api.miapp.com/api/documentation**.

## Especificación OpenAPI (JSON)

El archivo generado se encuentra en:

- **`backend/storage/api-docs/api-docs.json`**

La misma especificación se sirve vía ruta (para que Swagger UI la consuma):

- **`GET /docs`** (relativo al backend) → devuelve `api-docs.json`  
  Ejemplo local: `http://localhost:8000/docs`

## Regla del proyecto

- **Todas las APIs** bajo `/api/v1/*` deben estar documentadas en OpenAPI.
- La regla detallada está en **`.cursor/rules/06-api-contract.md`** (sección 8).
- Regla específica de generación y mantenimiento: **`.cursor/rules/06-openapi-documentacion.md`**.

## Actualización automática de api-docs.json

### En desarrollo (regenerar al abrir la doc)

Si en el **backend** tienes en `.env`:

```env
L5_SWAGGER_GENERATE_ALWAYS=true
```

cada vez que entras o recargas **`/api/documentation`**, Laravel regenera `api-docs.json`. No hace falta ejecutar el comando a mano; la doc refleja siempre los últimos cambios en rutas y anotaciones.

- **Recomendado** solo en entorno local (puede hacer la primera carga de la doc más lenta).
- En producción usar **`false`** o no definir la variable.

### Manual o por regla del agente

Desde la raíz del backend:

```bash
cd backend
php artisan l5-swagger:generate
```

Se actualiza `storage/api-docs/api-docs.json`. Debe ejecutarse:

- Tras añadir o modificar endpoints (el agente lo hace automáticamente si sigue la regla en `.cursor/rules/06-openapi-documentacion.md`).
- En el pipeline de CI/CD (según `docs/06-operacion/deploy-infraestructura.md`).

## Estructura de la especificación

- **Herramienta:** L5-Swagger (DarkaOnLine/L5-Swagger) 8.6.x.
- **Versión OpenAPI:** 3.0.3.
- **Origen:** Anotaciones en código (p. ej. `app/OpenApi.php`) y, en el futuro, anotaciones en controladores.
- **Envelope:** Todas las respuestas siguen el formato `error`, `respuesta`, `resultado` (ver `ApiEnvelope` en la spec).
- **Autenticación:** Bearer Token (Laravel Sanctum), scheme `bearerAuth` en la spec.

## Referencias

- Contrato de API: `.cursor/rules/06-api-contract.md`
- Playbook backend: `docs/backend/PLAYBOOK_BACKEND_LARAVEL.md` (sección Swagger/OpenAPI)
- Deploy/CI: `docs/06-operacion/deploy-infraestructura.md` (generación de documentación)
