# Endpoint: Solicitar recuperación de contraseña (forgot)

## Información General

- **Método:** `POST`
- **Ruta:** `/api/v1/auth/forgot-password`
- **Autenticación:** No requerida (endpoint público)
- **Versión:** v1

---

## Descripción

El usuario envía su código de usuario o email. Si existe un usuario con ese código/email y tiene email configurado, el sistema genera un token de recuperación (expiración 1 h), lo persiste y envía un correo con el enlace de restablecimiento. Por seguridad, la respuesta es **siempre genérica** (200 con el mismo mensaje), sin revelar si el usuario existe o si se envió el correo.

**Referencia:** TR-004(SH)-recuperación-de-contraseña.md

---

## Request

### Headers

```
Content-Type: application/json
Accept: application/json
```

### Body

```json
{
  "code_or_email": "JPEREZ"
}
```

o por email:

```json
{
  "code_or_email": "juan.perez@ejemplo.com"
}
```

### Parámetros

| Campo           | Tipo   | Requerido | Descripción                    | Validaciones      |
|----------------|--------|-----------|--------------------------------|-------------------|
| `code_or_email` | string | Sí        | Código de usuario o email       | required, string, min 1, max 255 |

---

## Response

### Success (200 OK) – Siempre el mismo mensaje por seguridad

```json
{
  "error": 0,
  "respuesta": "Si el usuario existe y tiene email configurado, recibirá un enlace para restablecer la contraseña.",
  "resultado": {}
}
```

### Error validación (422)

Campo vacío o inválido:

```json
{
  "error": 1000,
  "respuesta": "Error de validación",
  "resultado": {
    "errors": {
      "code_or_email": ["El campo code_or_email es obligatorio."]
    }
  }
}
```

### Error interno (500)

```json
{
  "error": 9999,
  "respuesta": "Error inesperado del servidor",
  "resultado": {}
}
```

---

## Notas

- No se debe revelar si el usuario existe o si se envió el correo.
- El token se almacena en `password_reset_tokens` (email, token, created_at).
- El enlace enviado por correo apunta al frontend: `{FRONTEND_URL}/reset-password?token=...`
