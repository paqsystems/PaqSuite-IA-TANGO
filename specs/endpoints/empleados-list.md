# Endpoint: Listar Empleados

## Información General

- **Método:** `GET`
- **Ruta:** `/api/v1/empleados`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Obtiene una lista paginada de empleados. Solo accesible para supervisores.

**Permisos:**
- **Solo supervisores** pueden acceder a este endpoint

---

## Request

### Query Parameters

| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|-----------|-------------|
| `page` | integer | No | Número de página | >= 1, default: 1 |
| `page_size` | integer | No | Tamaño de página | 1-100, default: 20 |
| `search` | string | No | Búsqueda por código, nombre o email | Máximo 100 caracteres |
| `supervisor` | boolean | No | Filtrar por rol supervisor | true/false |
| `activo` | boolean | No | Filtrar por estado activo | true/false |
| `inhabilitado` | boolean | No | Filtrar por estado inhabilitado | true/false |

---

## Response

### Success (200 OK)

```json
{
  "error": 0,
  "respuesta": "Empleados obtenidos correctamente",
  "resultado": {
    "items": [
      {
        "id": 1,
        "code": "JPEREZ",
        "nombre": "Juan Pérez",
        "email": "juan@ejemplo.com",
        "supervisor": false,
        "activo": true,
        "inhabilitado": false,
        "created_at": "2025-01-15T10:00:00Z"
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

## Notas

- Solo supervisores pueden acceder
- No se expone `password_hash` en la respuesta

---

**Última actualización:** 2025-01-20

