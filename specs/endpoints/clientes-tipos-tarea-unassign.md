# Endpoint: Desasignar Tipo de Tarea de Cliente

## Información General

- **Método:** `DELETE`
- **Ruta:** `/api/v1/clientes/{id}/tipos-tarea/{tipo_tarea_id}`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Desasigna un tipo de tarea NO genérico de un cliente. Solo accesible para supervisores.

**Permisos:**
- **Solo supervisores** pueden desasignar tipos de tarea

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
| `tipo_tarea_id` | integer | Sí | ID del tipo de tarea a desasignar |

---

## Response

### Success (200 OK)

```json
{
  "error": 0,
  "respuesta": "Tipo de tarea desasignado correctamente",
  "resultado": {}
}
```

---

## Errores

### 422 Unprocessable Entity

```json
{
  "error": 2116,
  "respuesta": "El cliente debe tener al menos un tipo de tarea disponible",
  "resultado": {}
}
```

**Códigos de error posibles:**
- `2116`: Si al desasignar, el cliente queda sin tipos genéricos ni asignados

---

## Validaciones

### A Nivel de Negocio

1. **Regla de tipos de tarea:**
   - Después de desasignar, verificar que el cliente tenga al menos un tipo genérico disponible O otros tipos asignados
   - Si no se cumple, retornar error 2116

---

**Última actualización:** 2025-01-20

