# Endpoint: Eliminar Cliente

## Información General

- **Método:** `DELETE`
- **Ruta:** `/api/v1/clientes/{id}`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Elimina un cliente del sistema. Solo accesible para supervisores. No se puede eliminar un cliente que tiene tareas asociadas.

**Permisos:**
- **Solo supervisores** pueden eliminar clientes

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
| `id` | integer | Sí | ID del cliente a eliminar |

---

## Response

### Success (200 OK)

```json
{
  "error": 0,
  "respuesta": "Cliente eliminado correctamente",
  "resultado": {}
}
```

---

## Errores

### 404 Not Found

```json
{
  "error": 4003,
  "respuesta": "Cliente no encontrado",
  "resultado": {}
}
```

### 422 Unprocessable Entity - Integridad Referencial

```json
{
  "error": 2112,
  "respuesta": "No se puede eliminar un cliente que tiene tareas asociadas",
  "resultado": {}
}
```

**Códigos de error posibles:**
- `2112`: No se puede eliminar un cliente con tareas asociadas

---

## Validaciones

### A Nivel de Negocio

1. **Integridad referencial:**
   - Verificar que el cliente no tenga tareas asociadas en `PQ_PARTES_registro_tarea`
   - Si tiene tareas, retornar error 2112

---

**Última actualización:** 2025-01-20

