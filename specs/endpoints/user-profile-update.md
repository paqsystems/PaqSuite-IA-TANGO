# Endpoint: Actualizar perfil de usuario

## Información General

- **Método:** `PUT`
- **Ruta:** `/api/v1/user/profile`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Permite al usuario autenticado actualizar su nombre y email. El código de usuario no es modificable. Empleados actualizan PQ_PARTES_USUARIOS; clientes actualizan PQ_PARTES_CLIENTES. El email debe ser único (excluyendo al propio usuario).

**TR:** TR-007(SH) – Edición de perfil de usuario.

---

## Request

### Headers

```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### Body (JSON)

| Campo   | Tipo   | Requerido | Descripción        |
|---------|--------|-----------|--------------------|
| nombre  | string | Sí        | Nombre completo    |
| email   | string | No        | Email (único)      |

Ejemplo:

```json
{
  "nombre": "Juan Pérez Actualizado",
  "email": "nuevo@ejemplo.com"
}
```

---

## Response

### Success (200 OK)

```json
{
  "error": 0,
  "respuesta": "Perfil actualizado correctamente",
  "resultado": {
    "user_code": "JPEREZ",
    "nombre": "Juan Pérez Actualizado",
    "email": "nuevo@ejemplo.com",
    "tipo_usuario": "usuario",
    "es_supervisor": false,
    "created_at": "2026-01-27T10:30:00.000Z"
  }
}
```

### Errores

**401 Unauthorized:** No autenticado.

**422 Unprocessable Entity:** Validación (nombre vacío, email inválido, email duplicado).

```json
{
  "error": 1000,
  "respuesta": "Los datos enviados no son válidos.",
  "resultado": {
    "errors": {
      "nombre": ["El nombre es obligatorio."],
      "email": ["El email ya está en uso por otro usuario."]
    }
  }
}
```

---

## Validaciones

- **nombre:** Requerido, string, min 1, max 255.
- **email:** Opcional, formato email, único (excluyendo al usuario actual).
