# Documentación de Autenticación

## Descripción General

El sistema utiliza autenticación basada en tokens mediante Laravel Sanctum. La autenticación se realiza exclusivamente contra la tabla `USERS` (code, password_hash, name, email, activo, inhabilitado).

> **Ubicación de la tabla USERS:** La tabla `USERS` se encuentra en la **base de datos DICCIONARIO** (PQ_DICCIONARIO), **no** en las bases de datos de las empresas. Es una tabla centralizada compartida por todo el sistema.

## Flujo de Autenticación

### 1. Login

```
POST /api/v1/auth/login
```

**Flujo interno:**
1. Validar campos de entrada (usuario, password)
2. Buscar usuario en tabla `USERS` (base DICCIONARIO) por `code`
3. Validar estado activo y no inhabilitado en `USERS`
4. Verificar contraseña con `Hash::check()`
5. Generar token Sanctum
6. Retornar token y datos del usuario

### 2. Diagrama de Secuencia

```
Cliente          API              AuthService        USERS
   |               |                   |               |
   |--- POST /login -->|               |               |
   |               |--- validate() --->|               |
   |               |                   |--- find() --->|
   |               |                   |<-- user ------|
   |               |                   |--- check() -->|
   |               |                   |<- token ------|
   |<-- 200 OK ----|                   |               |
```

## Endpoint de Login

### Request

```http
POST /api/v1/auth/login
Content-Type: application/json
Accept: application/json

{
  "usuario": "JPEREZ",
  "password": "password123"
}
```

### Response Exitosa (200 OK)

```json
{
  "error": 0,
  "respuesta": "Autenticación exitosa",
  "resultado": {
    "token": "1|abcdef1234567890abcdef1234567890",
    "user": {
      "user_id": 1,
      "user_code": "JPEREZ",
      "tipo_usuario": "usuario",
      "usuario_id": 1,
      "cliente_id": null,
      "es_supervisor": false,
      "nombre": "Juan Pérez",
      "email": "juan.perez@ejemplo.com"
    }
  }
}
```

**Campos del usuario:**
| Campo | Descripción |
|-------|-------------|
| `tipo_usuario` | Siempre "usuario" (schema simplificado) |
| `usuario_id` | ID del usuario en tabla USERS |
| `cliente_id` | null |
| `es_supervisor` | false |

### Códigos de Error

| Código | HTTP | Descripción |
|--------|------|-------------|
| 1101 | 422 | Código de usuario requerido |
| 1102 | 422 | Código de usuario no puede estar vacío |
| 1103 | 422 | Contraseña requerida |
| 1104 | 422 | Contraseña muy corta (mínimo 8 caracteres) |
| 3201 | 401 | Credenciales inválidas |
| 4203 | 401 | Usuario inactivo |
| 9999 | 500 | Error inesperado del servidor |

## Endpoint de Logout

### Request

```http
POST /api/v1/auth/logout
Authorization: Bearer {token}
Accept: application/json
```

**Nota:** Este endpoint requiere autenticación.

### Response Exitosa (200 OK)

```json
{
  "error": 0,
  "respuesta": "Sesión cerrada correctamente",
  "resultado": {}
}
```

### Response Sin Autenticación (401)

```json
{
  "error": 4001,
  "respuesta": "No autenticado",
  "resultado": {}
}
```

### Comportamiento

1. El token actual del usuario es revocado (eliminado de `personal_access_tokens`)
2. Solo se revoca el token usado en la petición, no todos los tokens del usuario
3. Si el token ya era inválido, retorna 401 pero el frontend debe limpiar localStorage igual

### Códigos de Error de Logout

| Código | HTTP | Descripción |
|--------|------|-------------|
| 0 | 200 | Logout exitoso |
| 4001 | 401 | No autenticado (token inválido o no presente) |
| 9999 | 500 | Error inesperado del servidor |

## Endpoint de Perfil de Usuario

### Request

```http
GET /api/v1/user/profile
Authorization: Bearer {token}
Accept: application/json
```

**Nota:** Este endpoint requiere autenticación.

### Response Exitosa (200 OK)

```json
{
  "error": 0,
  "respuesta": "Perfil obtenido correctamente",
  "resultado": {
    "user_code": "JPEREZ",
    "nombre": "Juan Pérez",
    "email": "juan.perez@ejemplo.com",
    "tipo_usuario": "usuario",
    "es_supervisor": false,
    "created_at": "2026-01-27T10:30:00+00:00"
  }
}
```

### Response Sin Autenticación (401)

```json
{
  "error": 4001,
  "respuesta": "No autenticado",
  "resultado": {}
}
```

### Comportamiento

1. El endpoint retorna los datos del usuario autenticado desde la tabla `USERS` (base DICCIONARIO)
2. El campo `email` puede ser `null` si no está configurado
3. El campo `created_at` está en formato ISO8601

### Códigos de Error de Perfil

| Código | HTTP | Descripción |
|--------|------|-------------|
| 0 | 200 | Perfil obtenido correctamente |
| 4001 | 401 | No autenticado (token inválido o no presente) |
| 9999 | 500 | Error inesperado del servidor |

---

## Uso del Token

### En Frontend

```typescript
// Guardar token después del login
import { setToken } from '@/shared/utils/tokenStorage';
setToken(response.resultado.token);

// Obtener token para requests
import { getToken } from '@/shared/utils/tokenStorage';
const token = getToken();

// Logout (llama al API y limpia localStorage)
import { logout } from '@/features/auth/services/auth.service';
await logout();
```

### En Requests Subsiguientes

```http
GET /api/v1/protected-endpoint
Authorization: Bearer 1|abcdef1234567890abcdef1234567890
```

## Seguridad

### Mensajes de Error Genéricos

El sistema **NO revela** si un usuario existe o no. Tanto para "usuario no encontrado" como para "contraseña incorrecta", se retorna el mismo mensaje:

```json
{
  "error": 3201,
  "respuesta": "Credenciales inválidas"
}
```

### Validación de Estados

Se valida en tabla `USERS` (base DICCIONARIO): `activo = true` AND `inhabilitado = false`. Si falla, se retorna error 4203 (Usuario inactivo).

## Estructura de Archivos

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/V1/
│   │   │   ├── AuthController.php         # login() y logout()
│   │   │   └── UserProfileController.php  # show() - perfil de usuario
│   │   ├── Requests/Auth/
│   │   │   └── LoginRequest.php
│   │   └── Resources/Auth/
│   │       └── LoginResource.php
│   └── Services/
│       ├── AuthService.php                 # login() y logout()
│       └── UserProfileService.php         # getProfile()
├── routes/
│   └── api.php
└── tests/
    ├── Feature/Api/V1/
    │   ├── Auth/
    │   │   ├── LoginTest.php
    │   │   └── LogoutTest.php
    │   └── UserProfileTest.php
    └── Unit/Services/
        ├── AuthServiceTest.php
        └── UserProfileServiceTest.php
```

## Tests

### Ejecutar Tests de Autenticación

```bash
# Unit tests del servicio (login y logout)
php artisan test tests/Unit/Services/AuthServiceTest.php

# Integration tests del endpoint de login
php artisan test tests/Feature/Api/V1/Auth/LoginTest.php

# Integration tests del endpoint de logout
php artisan test tests/Feature/Api/V1/Auth/LogoutTest.php

# Todos los tests de autenticación
php artisan test --filter=Auth

# Todos los tests
php artisan test
```

## Referencias

- [TR-001(MH) - Login de Empleado](../_projects/SistemaPartes/hu-tareas/TR-001(MH)-login-de-empleado.md)
- [TR-002(SH) - Login de Cliente](../_projects/SistemaPartes/hu-tareas/TR-002(SH)-login-de-cliente.md)
- [TR-003(MH) - Logout](../_projects/SistemaPartes/hu-tareas/TR-003(MH)-logout.md)
- [TR-006(MH) - Visualización de Perfil de Usuario](../_projects/SistemaPartes/hu-tareas/TR-006(MH)-visualización-de-perfil-de-usuario.md)
- [Laravel Sanctum Documentation](https://laravel.com/docs/10.x/sanctum)
