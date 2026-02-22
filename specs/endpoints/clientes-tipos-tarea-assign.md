# Endpoint: Asignar Tipo de Tarea a Cliente

## Información General

- **Método:** `POST`
- **Ruta:** `/api/v1/clientes/{id}/tipos-tarea`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Asigna un tipo de tarea NO genérico a un cliente. Solo accesible para supervisores.

**Permisos:**
- **Solo supervisores** pueden asignar tipos de tarea

---

## Request

### Headers

```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### Path Parameters

| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|-----------|-------------|
| `id` | integer | Sí | ID del cliente |

### Body

```json
{
  "tipo_tarea_id": 2
}
```

### Parámetros

| Campo | Tipo | Requerido | Descripción | Validaciones |
|-------|------|-----------|-------------|--------------|
| `tipo_tarea_id` | integer | Sí | ID del tipo de tarea | Debe existir, estar activo, no inhabilitado, y NO ser genérico |

---

## Response

### Success (201 Created)

```json
{
  "error": 0,
  "respuesta": "Tipo de tarea asignado correctamente",
  "resultado": {
    "id": 1,
    "cliente_id": 1,
    "tipo_tarea_id": 2,
    "created_at": "2025-01-20T10:00:00Z"
  }
}
```

---

## Errores

### 422 Unprocessable Entity

```json
{
  "error": 2118,
  "respuesta": "No se puede asignar un tipo de tarea genérico a un cliente",
  "resultado": {}
}
```

**Códigos de error posibles:**
- `2118`: No se puede asignar tipo genérico (los genéricos están disponibles para todos)

---

## Validaciones

### A Nivel de Negocio

1. **Tipo NO genérico:**
   - El tipo de tarea debe tener `is_generico = false`
   - Si es genérico, retornar error 2118

2. **No duplicado:**
   - Verificar que el tipo no esté ya asignado al cliente

---

**Última actualización:** 2025-01-20

