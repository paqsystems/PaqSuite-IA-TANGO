# Endpoint: Restablecer contraseña (reset)

## Información General

- **Método:** `POST`
- **Ruta:** `/api/v1/auth/reset-password`
- **Autenticación:** No requerida (el token en el body actúa como autorización)
- **Versión:** v1

---

## Descripción

El usuario restablece la contraseña con el token recibido por email. Se valida que el token exista, no haya expirado (1 h) y no haya sido usado. Se actualiza `password_hash` en USERS y se invalida el token.

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
  "token": "abc123...",
  "password": "nuevaContraseña123",
  "password_confirmation": "nuevaContraseña123"
}
```

### Parámetros

| Campo                   | Tipo   | Requerido | Descripción           | Validaciones                    |
|-------------------------|--------|-----------|------------------------|----------------------------------|
| `token`                 | string | Sí        | Token del enlace email | required                        |
| `password`              | string | Sí        | Nueva contraseña       | required, min 8, confirmed       |
| `password_confirmation` | string | Sí        | Confirmación           | debe coincidir con password     |

---

## Response

### Success (200 OK)

```json
{
  "error": 0,
  "respuesta": "Contraseña restablecida correctamente.",
  "resultado": {}
}
```

### Error token inválido o expirado (422)

```json
{
  "error": 3205,
  "respuesta": "El enlace de recuperación no es válido o ha expirado.",
  "resultado": {}
}
```

```json
{
  "error": 3206,
  "respuesta": "El enlace de recuperación ha expirado. Solicite uno nuevo.",
  "resultado": {}
}
```

### Error validación contraseña (422)

```json
{
  "error": 1104,
  "respuesta": "La contraseña debe tener al menos 8 caracteres.",
  "resultado": {}
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

## Códigos de error

| Código | Significado                          |
|--------|--------------------------------------|
| 3205   | Token inválido o ya usado            |
| 3206   | Token expirado                       |
| 1104   | Contraseña no cumple longitud mínima |
