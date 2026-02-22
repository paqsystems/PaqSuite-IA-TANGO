# Endpoint: Actualizar Empleado

## Información General

- **Método:** `PUT`
- **Ruta:** `/api/v1/empleados/{id}`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Actualiza un empleado existente. El código (`code`) no es modificable.

---

## Request

### Body

```json
{
  "nombre": "Juan Pérez Actualizado",
  "email": "nuevo@ejemplo.com",
  "password": "nueva_contraseña123",
  "supervisor": true,
  "activo": true,
  "inhabilitado": false
}
```

### Parámetros

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `nombre` | string | Sí | Nombre completo |
| `email` | string | No | Email (único si cambió) |
| `password` | string | No | Nueva contraseña (solo si se quiere cambiar) |
| `supervisor` | boolean | No | Rol supervisor |
| `activo` | boolean | No | Estado activo |
| `inhabilitado` | boolean | No | Estado inhabilitado |

---

## Response

### Success (200 OK)

```json
{
  "error": 0,
  "respuesta": "Empleado actualizado correctamente",
  "resultado": {
    "id": 1,
    "code": "JPEREZ",
    "nombre": "Juan Pérez Actualizado",
    "email": "nuevo@ejemplo.com",
    "supervisor": true,
    "activo": true,
    "inhabilitado": false,
    "updated_at": "2025-01-20T11:00:00Z"
  }
}
```

---

## Notas

- El código no es modificable
- Si se proporciona `password`, se hashea antes de guardar

---

**Última actualización:** 2025-01-20

