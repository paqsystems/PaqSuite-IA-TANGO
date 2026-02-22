# Endpoint: Listar Tareas para Proceso Masivo

## Información General

- **Método:** `GET`
- **Ruta:** `/api/v1/tareas/proceso-masivo`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Obtiene una lista de tareas con filtros para el proceso masivo. Solo accesible para supervisores. Las tareas incluyen checkbox de selección en el frontend.

**Permisos:**
- **Solo supervisores** pueden acceder a este endpoint

---

## Request

### Headers

```
Authorization: Bearer {token}
Accept: application/json
```

### Query Parameters

| Parámetro | Tipo | Requerido | Descripción | Validaciones |
|-----------|------|-----------|-------------|--------------|
| `fecha_desde` | string (date) | No | Fecha inicial del período | Formato YYYY-MM-DD |
| `fecha_hasta` | string (date) | No | Fecha final del período | Formato YYYY-MM-DD, >= fecha_desde (1305) |
| `cliente_id` | integer | No | Filtrar por cliente | Debe existir y estar activo |
| `usuario_id` | integer | No | Filtrar por asistente | Debe existir y estar activo |
| `cerrado` | boolean | No | Filtrar por estado cerrado | true = cerradas, false = abiertas |
| `page` | integer | No | Número de página | >= 1, default: 1 |
| `page_size` | integer | No | Tamaño de página | 1-100, default: 20 |

---

## Response

### Success (200 OK)

```json
{
  "error": 0,
  "respuesta": "Tareas obtenidas correctamente",
  "resultado": {
    "items": [
      {
        "id": 1,
        "usuario": {
          "id": 1,
          "code": "JPEREZ",
          "nombre": "Juan Pérez"
        },
        "cliente": {
          "id": 1,
          "nombre": "Cliente A"
        },
        "fecha": "2025-01-20",
        "duracion_minutos": 120,
        "cerrado": false
      }
    ],
    "page": 1,
    "page_size": 20,
    "total": 1,
    "total_pages": 1
  }
}
```

---

## Errores

### 403 Forbidden

```json
{
  "error": 3101,
  "respuesta": "No tiene permisos para acceder a esta funcionalidad",
  "resultado": {}
}
```

---

## Notas

- Solo supervisores pueden acceder
- Los filtros se aplican en conjunto (AND lógico)
- La selección múltiple se maneja en el frontend

---

**Última actualización:** 2025-01-20

