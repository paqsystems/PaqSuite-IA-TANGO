# Endpoint: Listar Tipos de Cliente

## Información General

- **Método:** `GET`
- **Ruta:** `/api/v1/tipos-cliente`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Obtiene una lista paginada de tipos de cliente. Solo accesible para supervisores.

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
| `page` | integer | No | Número de página | >= 1, default: 1 |
| `page_size` | integer | No | Tamaño de página | 1-100, default: 20 |
| `search` | string | No | Búsqueda por código o descripción | Máximo 100 caracteres |
| `activo` | boolean | No | Filtrar por estado activo | true/false |
| `inhabilitado` | boolean | No | Filtrar por estado inhabilitado | true/false |

---

## Response

### Success (200 OK)

```json
{
  "error": 0,
  "respuesta": "Tipos de cliente obtenidos correctamente",
  "resultado": {
    "items": [
      {
        "id": 1,
        "code": "CORP",
        "descripcion": "Corporativo",
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
- Búsqueda case-insensitive en código y descripción

---

**Última actualización:** 2025-01-20

