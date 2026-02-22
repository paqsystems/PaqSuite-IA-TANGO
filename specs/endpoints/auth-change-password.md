# Endpoint: Cambio de contraseña (usuario autenticado)

## Información General

- **Método:** `POST`
- **Ruta:** `/api/v1/auth/change-password`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Permite al usuario autenticado cambiar su contraseña. Debe indicar la contraseña actual para autorizar el cambio, la nueva contraseña y su confirmación. El sistema valida la contraseña actual contra USERS, valida la nueva (longitud mínima 8 caracteres, coincidencia con confirmación) y actualiza `password_hash`. Tras el cambio exitoso se mantiene la sesión actual (no se invalida el token).

**TR:** TR-005(SH) – Cambio de contraseña (usuario autenticado).

---

## Request

### Headers

```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### Body (JSON)

| Campo                 | Tipo   | Requerido | Descripción                          |
|-----------------------|--------|-----------|--------------------------------------|
| `current_password`    | string | Sí        | Contraseña actual del usuario        |
| `password`            | string | Sí        | Nueva contraseña (mín. 8 caracteres) |
| `password_confirmation` | string | Sí      | Confirmación de la nueva contraseña  |

Ejemplo:

```json
{
  "current_password": "miContraseñaActual",
  "password": "miNuevaContraseña",
  "password_confirmation": "miNuevaContraseña"
}
```

---

## Response

### Success (200 OK)

```json
{
  "error": 0,
  "respuesta": "Contraseña actualizada correctamente.",
  "resultado": {}
}
```

### Campos de Respuesta

| Campo      | Tipo    | Descripción                          |
|------------|---------|--------------------------------------|
| `error`    | integer | Código de error (0 = éxito)           |
| `respuesta`| string  | Mensaje legible para el usuario      |
| `resultado` | object | Objeto vacío `{}` (no aplica para este endpoint) |

---

## Errores

### 401 Unauthorized - No autenticado

Cuando no se envía token o el token es inválido.

```json
{
  "error": 3001,
  "respuesta": "Usuario no autenticado",
  "resultado": {}
}
```

### 422 Unprocessable Entity - Contraseña actual incorrecta

```json
{
  "error": 3204,
  "respuesta": "La contraseña actual es incorrecta",
  "resultado": {}
}
```

### 422 Unprocessable Entity - Validación (nueva contraseña o confirmación)

Cuando la nueva contraseña no cumple longitud mínima (8 caracteres) o `password` y `password_confirmation` no coinciden.

```json
{
  "error": 422,
  "respuesta": "Los datos enviados no son válidos.",
  "resultado": {
    "errors": {
      "password": ["El campo password debe tener al menos 8 caracteres."],
      "password_confirmation": ["El campo password confirmation no coincide."]
    }
  }
}
```

### 500 Internal Server Error

```json
{
  "error": 9999,
  "respuesta": "Error inesperado del servidor",
  "resultado": {}
}
```

---

## Validaciones

- **current_password:** Requerido. Debe coincidir con el hash almacenado en USERS para el usuario autenticado.
- **password:** Requerido. Mínimo 8 caracteres. Debe coincidir con `password_confirmation`.
- **password_confirmation:** Requerido. Debe ser igual a `password`.

---

## Notas

- Tras un cambio exitoso, la sesión se mantiene (el token actual sigue siendo válido).
- Solo el usuario autenticado puede cambiar su propia contraseña (identificado por el token).
