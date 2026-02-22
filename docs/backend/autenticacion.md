# Documentación de Autenticación

## Descripción General

El sistema utiliza autenticación basada en tokens mediante Laravel Sanctum. La autenticación se realiza contra la tabla centralizada `USERS`, y luego se determina si el usuario es un empleado (en `PQ_PARTES_USUARIOS`) o un cliente (en `PQ_PARTES_CLIENTES`).

## Flujo de Autenticación

### 1. Login de Empleado o Cliente

```
POST /api/v1/auth/login
```

**Flujo interno:**
1. Validar campos de entrada (usuario, password)
2. Buscar usuario en tabla `USERS` por `code`
3. Validar estado activo y no inhabilitado en `USERS`
4. Verificar contraseña con `Hash::check()`
5. Buscar **primero** en `PQ_PARTES_USUARIOS` por `code` (empleado)
6. Si **no es empleado**, buscar en `PQ_PARTES_CLIENTES` por `code` (cliente)
7. Validar estado activo y no inhabilitado en la tabla correspondiente
8. Determinar `tipo_usuario` ("usuario" para empleado, "cliente" para cliente)
9. Generar token Sanctum
10. Retornar token y datos del usuario

### 2. Diagrama de Secuencia

```
Cliente          API              AuthService        USERS        PQ_PARTES_USUARIOS
   |               |                   |               |               |
   |--- POST /login -->|               |               |               |
   |               |--- validate() --->|               |               |
   |               |                   |--- find() --->|               |
   |               |                   |<-- user ------|               |
   |               |                   |--- check() -->|               |
   |               |                   |               |--- find() --->|
   |               |                   |               |<-- empleado --|
   |               |                   |<- token ------|               |
   |<-- 200 OK ----|                   |               |               |
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

### Response Exitosa - Empleado (200 OK)

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
      "usuario_id": 5,
      "cliente_id": null,
      "es_supervisor": false,
      "nombre": "Juan Pérez",
      "email": "juan.perez@ejemplo.com"
    }
  }
}
```

### Response Exitosa - Cliente (200 OK)

```json
{
  "error": 0,
  "respuesta": "Autenticación exitosa",
  "resultado": {
    "token": "1|abcdef1234567890abcdef1234567890",
    "user": {
      "user_id": 10,
      "user_code": "CLI001",
      "tipo_usuario": "cliente",
      "usuario_id": null,
      "cliente_id": 5,
      "es_supervisor": false,
      "nombre": "Empresa ABC S.A.",
      "email": "contacto@empresaabc.com"
    }
  }
}
```

**Diferencias entre Empleado y Cliente:**

| Campo | Empleado | Cliente |
|-------|----------|---------|
| `tipo_usuario` | "usuario" | "cliente" |
| `usuario_id` | número (ID en PQ_PARTES_USUARIOS) | null |
| `cliente_id` | null | número (ID en PQ_PARTES_CLIENTES) |
| `es_supervisor` | true/false | siempre false |

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

### Response Exitosa - Empleado (200 OK)

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

### Response Exitosa - Supervisor (200 OK)

```json
{
  "error": 0,
  "respuesta": "Perfil obtenido correctamente",
  "resultado": {
    "user_code": "MGARCIA",
    "nombre": "María García",
    "email": "maria.garcia@ejemplo.com",
    "tipo_usuario": "usuario",
    "es_supervisor": true,
    "created_at": "2026-01-27T10:30:00+00:00"
  }
}
```

### Response Exitosa - Cliente (200 OK)

```json
{
  "error": 0,
  "respuesta": "Perfil obtenido correctamente",
  "resultado": {
    "user_code": "CLI001",
    "nombre": "Empresa ABC S.A.",
    "email": "contacto@empresaabc.com",
    "tipo_usuario": "cliente",
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

1. El endpoint retorna los datos del usuario autenticado según su tipo (empleado o cliente)
2. Si el usuario es empleado, busca en `PQ_PARTES_USUARIOS`
3. Si el usuario es cliente, busca en `PQ_PARTES_CLIENTES`
4. El campo `email` puede ser `null` si no está configurado
5. El campo `created_at` está en formato ISO8601

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

Se validan dos niveles de estado:
1. **En tabla USERS:** `activo = true` AND `inhabilitado = false`
2. **En tabla de perfil:**
   - Para empleados: `PQ_PARTES_USUARIOS.activo = true` AND `inhabilitado = false`
   - Para clientes: `PQ_PARTES_CLIENTES.activo = true` AND `inhabilitado = false`

Si alguno falla, se retorna error 4203 (Usuario inactivo).

### Prioridad Empleado vs Cliente

Si un código de usuario existe tanto en `PQ_PARTES_USUARIOS` como en `PQ_PARTES_CLIENTES` (caso no permitido por reglas de negocio pero manejado por seguridad), se prioriza `PQ_PARTES_USUARIOS` (empleado).

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

- [TR-001(MH) - Login de Empleado](../hu-tareas/TR-001(MH)-login-de-empleado.md)
- [TR-002(SH) - Login de Cliente](../hu-tareas/TR-002(SH)-login-de-cliente.md)
- [TR-003(MH) - Logout](../hu-tareas/TR-003(MH)-logout.md)
- [TR-006(MH) - Visualización de Perfil de Usuario](../hu-tareas/TR-006(MH)-visualización-de-perfil-de-usuario.md)
- [Laravel Sanctum Documentation](https://laravel.com/docs/10.x/sanctum)
