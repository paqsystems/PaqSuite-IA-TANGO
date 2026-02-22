# Endpoint: Listar Tipos de Tarea Asignados a Cliente

## Información General

- **Método:** `GET`
- **Ruta:** `/api/v1/clientes/{id}/tipos-tarea`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Obtiene la lista de tipos de tarea NO genéricos asignados a un cliente específico. Solo accesible para supervisores.

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
  "respuesta": "Tipos de tarea obtenidos correctamente",
  "resultado": [
    {
      "id": 2,
      "code": "TIPO002",
      "descripcion": "Tipo Específico",
      "is_generico": false,
      "is_default": false,
      "activo": true,
      "inhabilitado": false
    }
  ]
}
```

---

## Notas

- Solo muestra tipos NO genéricos (`is_generico = false`)
- Los tipos genéricos están disponibles para todos los clientes automáticamente

---

**Última actualización:** 2025-01-20

