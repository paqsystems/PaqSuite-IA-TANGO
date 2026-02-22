# Endpoint: Crear Empleado

## Información General

- **Método:** `POST`
- **Ruta:** `/api/v1/empleados`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Crea un nuevo empleado. Solo accesible para supervisores.

---

## Request

### Body

```json
{
  "code": "JPEREZ",
  "nombre": "Juan Pérez",
  "email": "juan@ejemplo.com",
  "password": "contraseña123",
  "supervisor": false,
  "activo": true
}
```

### Parámetros

| Campo | Tipo | Requerido | Descripción | Validaciones |
|-------|------|-----------|-------------|--------------|
| `code` | string | Sí | Código único del empleado | No vacío, único (1105, 4101) |
| `nombre` | string | Sí | Nombre completo | No vacío, máximo 200 caracteres |
| `email` | string | No | Email del empleado | Formato válido, único si se proporciona (1108, 4102) |
| `password` | string | Sí | Contraseña | Mínimo 8 caracteres (1104) |
| `supervisor` | boolean | No | Indica si es supervisor | Default: false |
| `activo` | boolean | No | Estado activo | Default: true |

---

## Response

### Success (201 Created)

```json
{
  "error": 0,
  "respuesta": "Empleado creado correctamente",
  "resultado": {
    "id": 1,
    "code": "JPEREZ",
    "nombre": "Juan Pérez",
    "email": "juan@ejemplo.com",
    "supervisor": false,
    "activo": true,
    "inhabilitado": false,
    "created_at": "2025-01-20T10:00:00Z"
  }
}
```

---

## Notas

- La contraseña se hashea antes de guardar
- No se expone `password_hash` en la respuesta

---

**Última actualización:** 2025-01-20

