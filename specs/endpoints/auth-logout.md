# Endpoint: Cerrar Sesión

## Información General

- **Método:** `POST`
- **Ruta:** `/api/v1/auth/logout`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Cierra la sesión del usuario autenticado, invalidando el token de acceso actual. Después de este endpoint, el token no puede ser utilizado para realizar solicitudes autenticadas.

---

## Request

### Headers

```
Authorization: Bearer {token}
Accept: application/json
```

---

## Response

### Success (200 OK)

```json
{
  "error": 0,
  "respuesta": "Sesión cerrada correctamente",
  "resultado": {}
}
```

### Campos de Respuesta

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `error` | integer | Código de error (0 = éxito) |
| `respuesta` | string | Mensaje legible para el usuario |
| `resultado` | object | Objeto vacío `{}` (no aplica para logout) |

---

## Errores

### 401 Unauthorized - No Autenticado

```json
{
  "error": 3001,
  "respuesta": "Usuario no autenticado",
  "resultado": {}
}
```

**Códigos de error posibles:**
- `3001`: Usuario no autenticado
- `3002`: Token inválido

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

### A Nivel de Request

1. **Token:**
   - Debe estar presente en el header `Authorization`
   - Debe tener formato `Bearer {token}`
   - Debe ser válido según Sanctum

### A Nivel de Negocio

1. **Token válido:**
   - El token debe existir y no estar revocado
   - Si el token es inválido, retornar error 3001 o 3002

---

## Operaciones de Base de Datos

### Tablas Involucradas

- `personal_access_tokens` (Sanctum) - Revocación del token

### Consultas

```php
// Revocar el token actual
$user->currentAccessToken()->delete();
```

---

## Ejemplos de Uso

### cURL

```bash
curl -X POST "https://api.ejemplo.com/api/v1/auth/logout" \
  -H "Authorization: Bearer 1|abcdef1234567890" \
  -H "Accept: application/json"
```

### JavaScript (Fetch)

```javascript
const response = await fetch('/api/v1/auth/logout', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json'
  }
});

const data = await response.json();

if (data.error === 0) {
  // Eliminar token del almacenamiento local
  localStorage.removeItem('token');
  // Redirigir a login
  window.location.href = '/login';
} else {
  console.error('Error al cerrar sesión:', data.respuesta);
}
```

---

## Notas

- El token se revoca inmediatamente después de la llamada exitosa
- El frontend debe eliminar el token del almacenamiento local/sessionStorage
- El frontend debe redirigir al usuario a la página de login
- Si el token ya está revocado o es inválido, el endpoint puede retornar éxito o error según diseño (recomendado: retornar éxito para evitar exponer información)

---

**Última actualización:** 2025-01-20

