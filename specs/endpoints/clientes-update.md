# Endpoint: Actualizar Cliente

## Información General

- **Método:** `PUT`
- **Ruta:** `/api/v1/clientes/{id}`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Actualiza la información de un cliente existente. Solo accesible para supervisores. El código (`code`) no es modificable.

**Permisos:**
- **Solo supervisores** pueden actualizar clientes

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
| `id` | integer | Sí | ID del cliente a actualizar |

### Body

```json
{
  "nombre": "Cliente A Actualizado",
  "tipo_cliente_id": 2,
  "email": "nuevo@ejemplo.com",
  "password": "nueva_contraseña123",
  "activo": true,
  "inhabilitado": false
}
```

### Parámetros

| Campo | Tipo | Requerido | Descripción | Validaciones |
|-------|------|-----------|-------------|--------------|
| `nombre` | string | Sí | Nombre del cliente | No vacío, máximo 200 caracteres |
| `tipo_cliente_id` | integer | Sí | ID del tipo de cliente | Debe existir, estar activo y no inhabilitado |
| `email` | string | No | Email del cliente | Formato válido, único si cambió |
| `password` | string | No | Nueva contraseña | Mínimo 8 caracteres si se proporciona |
| `activo` | boolean | No | Estado activo | Default: mantener valor actual |
| `inhabilitado` | boolean | No | Estado inhabilitado | Default: mantener valor actual |

---

## Response

### Success (200 OK)

```json
{
  "error": 0,
  "respuesta": "Cliente actualizado correctamente",
  "resultado": {
    "id": 1,
    "code": "CLI001",
    "nombre": "Cliente A Actualizado",
    "tipo_cliente_id": 2,
    "email": "nuevo@ejemplo.com",
    "activo": true,
    "inhabilitado": false,
    "updated_at": "2025-01-20T11:00:00Z"
  }
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

### 422 Unprocessable Entity

```json
{
  "error": 2116,
  "respuesta": "El cliente debe tener al menos un tipo de tarea disponible",
  "resultado": {
    "errors": {
      "tipo_cliente_id": ["El cliente debe tener al menos un tipo de tarea disponible"]
    }
  }
}
```

---

## Validaciones

### A Nivel de Negocio

1. **Regla de tipos de tarea:**
   - Después de actualizar, verificar que exista al menos un tipo genérico O el cliente tenga tipos asignados
   - Si no se cumple, retornar error 2116

2. **Código no modificable:**
   - El campo `code` no se puede modificar (ignorar si se envía)

---

**Última actualización:** 2025-01-20

