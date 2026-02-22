# 01 — Contrato Base de API (obligatorio)

## 1) Formato estándar de respuesta (SIEMPRE)
Todas las respuestas HTTP (éxito o error) deben ser JSON con:

```json
{
  "error": 0,
  "respuesta": "mensaje para UI",
  "resultado": {}
}
```

- `error`:
  - `0` = OK
  - `!= 0` = error controlado (validación/negocio/autorización/etc.)
- `respuesta`: texto legible, apto para mostrar.
- `resultado`: **siempre** un objeto JSON. Nunca `null` ni ausente.
  - Objeto con datos (recurso, lista paginada, etc.)
  - Objeto vacío `{}` cuando no hay datos que retornar (éxito sin cuerpo o errores sin detalle adicional)
  - Ver `specs/contracts/response-envelope.md` como referencia.

### Regla
El frontend **nunca** debe depender de formatos alternativos.

## 2) Códigos HTTP + `error`
- Usar códigos HTTP coherentes:
  - 200/201 OK
  - 400 Request inválido
  - 401 No autenticado
  - 403 No autorizado
  - 404 No encontrado
  - 409 Conflicto
  - 422 Validación (Laravel usual)
  - 429 Rate limit
  - 500 Error inesperado
- Aun así, el cuerpo **siempre** mantiene `error/respuesta/resultado`.

## 3) Catálogo mínimo de códigos `error` (sugerido)
- 0: OK
- 1000–1999: Validación (request/DTO)
- 2000–2999: Reglas de negocio
- 3000–3999: Autorización/Permisos
- 4000–4999: Not found/conflictos/estado inválido
- 9000–9999: Seguridad/Infra/Errores inesperados
  - 9001: Token no provisto
  - 9002: Token inválido/expirado/revocado
  - 9999: Excepción no controlada

## 4) Paginación, orden y filtros (estándar)
Listados deben soportar:
- `page` (1..n)
- `page_size` (máximo, ej. 100)
- `sort` (campo permitido), `sort_dir` (`asc|desc`)
- filtros por querystring (ej. `status=EN_PROCESO`)

Respuesta en `resultado` debe incluir metadata:

```json
{
  "items": [],
  "page": 1,
  "page_size": 20,
  "total": 123,
  "total_pages": 7
}
```

## 5) Idempotencia y concurrencia (mínimo)
- PUT/PATCH/DELETE deben ser idempotentes cuando aplique.
- Para updates críticos, contemplar control optimista (ej. `updated_at` o `row_version`) a definir.

## 6) Versionado
- Base path: `/api/v1/...`
- Breaking changes -> `/api/v2/...`

## 7) Contenido y formatos
- JSON UTF-8.
- Fechas en ISO-8601.
- Montos en decimal (evitar float).
- Campos dinámicos (sort/filter) solo por whitelist (anti-inyección).

## 8) Documentación de API (Swagger/OpenAPI)

### Requisito Obligatorio
- Todas las APIs del sistema deben estar documentadas mediante Swagger/OpenAPI.
- La documentación debe generarse automáticamente desde el código (anotaciones/comentarios en controladores).

### Versión y Estándar
- **Versión OpenAPI:** 3.0.3 (mínimo) o 3.1.0 (recomendado)
- **Formato:** YAML o JSON (definir estándar del proyecto)
- **Herramienta:** L5-Swagger (DarkaOnLine/L5-Swagger) para Laravel

### Alcance
- **Todos los endpoints** definidos en `/api/v1/*` deben estar documentados
- Incluir endpoints públicos (login) y protegidos
- Incluir ejemplos de request y response para cada endpoint

### Configuración de Seguridad
- **Autenticación:** Documentar autenticación Bearer Token (Sanctum)
- **Security Scheme:** `bearerAuth` (tipo: http, scheme: bearer, bearerFormat: JWT)
- **Aplicación:** Todos los endpoints protegidos deben requerir `bearerAuth` en la especificación

### Formato de Respuestas
- **Envelope estándar:** Todas las respuestas documentadas deben reflejar el formato `error/respuesta/resultado`
- **Schemas:** Definir schemas reutilizables para:
  - `ApiResponse<T>` (envelope genérico)
  - `ApiErrorResponse` (respuestas de error)
  - `PaginatedResponse<T>` (respuestas paginadas)

### Versionado
- **Base Path:** Documentar `/api/v1` como base path en la especificación
- **Info:** Incluir versión de la API en el campo `info.version` del OpenAPI spec
- **Breaking changes:** Al crear `/api/v2`, generar nueva especificación OpenAPI separada

### Mantenimiento y Sincronización
- **Contrato único:** La especificación OpenAPI debe ser la fuente de verdad para el contrato de API
- **Sincronización:** Los archivos en `specs/endpoints/*.md` deben mantenerse alineados con la especificación OpenAPI
- **Validación:** Implementar validación en CI/CD para verificar que los endpoints implementados coincidan con la especificación OpenAPI
- **Generación:** La documentación debe regenerarse automáticamente en cada build/deploy

### Ubicación y Acceso
- **Archivo generado:** `storage/api-docs/api-docs.json` o `public/api-docs/swagger.json`
- **UI de Swagger:** Accesible en `/api/documentation` (ruta configurable)
- **Entorno:** Disponible en desarrollo y producción (con autenticación si es necesario)

---
