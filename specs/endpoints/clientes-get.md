# Endpoint: Obtener Cliente

## Información General

- **Método:** `GET`
- **Ruta:** `/api/v1/clientes/{id}`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Obtiene el detalle de un cliente específico por su ID. Solo accesible para supervisores.

**Permisos:**
- **Solo supervisores** pueden acceder a este endpoint

---

## Request

### Headers

```
Authorization: Bearer {token}
Accept: application/json
```

### Path Parameters

| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|-----------|-------------|
| `id` | integer | Sí | ID del cliente |

---

## Response

### Success (200 OK)

```json
{
  "error": 0,
  "respuesta": "Cliente obtenido correctamente",
  "resultado": {
    "id": 1,
    "code": "CLI001",
    "nombre": "Cliente A",
    "tipo_cliente": {
      "id": 1,
      "code": "CORP",
      "descripcion": "Corporativo"
    },
    "email": "cliente@ejemplo.com",
    "activo": true,
    "inhabilitado": false,
    "tipos_tarea_asignados": [
      {
        "id": 2,
        "code": "TIPO002",
        "descripcion": "Tipo Específico"
      }
    ],
    "created_at": "2025-01-15T10:00:00Z",
    "updated_at": "2025-01-15T10:00:00Z"
  }
}
```

### Campos de Respuesta

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `error` | integer | Código de error (0 = éxito) |
| `respuesta` | string | Mensaje legible para el usuario |
| `resultado.id` | integer | ID del cliente |
| `resultado.code` | string | Código del cliente |
| `resultado.nombre` | string | Nombre del cliente |
| `resultado.tipo_cliente` | object | Tipo de cliente asociado |
| `resultado.email` | string\|null | Email del cliente |
| `resultado.activo` | boolean | Estado activo |
| `resultado.inhabilitado` | boolean | Estado inhabilitado |
| `resultado.tipos_tarea_asignados` | array | Tipos de tarea NO genéricos asignados al cliente |
| `resultado.created_at` | string | Fecha de creación |
| `resultado.updated_at` | string | Fecha de última actualización |

---

## Errores

### 404 Not Found - Cliente No Encontrado

```json
{
  "error": 4003,
  "respuesta": "Cliente no encontrado",
  "resultado": {}
}
```

### 403 Forbidden - No Autorizado

```json
{
  "error": 3101,
  "respuesta": "No tiene permisos para acceder a esta funcionalidad",
  "resultado": {}
}
```

---

## Validaciones

### A Nivel de Request

1. **Permisos:**
   - El usuario debe ser supervisor

2. **ID:**
   - Debe ser un entero válido
   - El cliente debe existir

---

## Operaciones de Base de Datos

### Tablas Involucradas

- `PQ_PARTES_cliente` (SELECT)
- `PQ_PARTES_tipo_cliente` (JOIN)
- `PQ_PARTES_cliente_tipo_tarea` (JOIN para tipos asignados)
- `PQ_PARTES_tipo_tarea` (JOIN para tipos asignados)

---

**Última actualización:** 2025-01-20

