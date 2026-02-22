# Endpoint: Autenticación de Usuario

## Información General

- **Método:** `POST`
- **Ruta:** `/api/v1/auth/login`
- **Autenticación:** No requerida (endpoint público)
- **Versión:** v1

---

## Descripción

Autentica un usuario en el sistema mediante código y contraseña. La autenticación se realiza contra la tabla `USERS` (sin prefijo PQ_PARTES_). Si las credenciales son válidas:

1. Se determina si el usuario es un **Cliente** (tabla `PQ_PARTES_CLIENTES`) o un **Empleado** (tabla `PQ_PARTES_USUARIOS`)
2. Se obtienen los datos correspondientes (ID, nombre, email, etc.)
3. Si es usuario, se verifica si es supervisor
4. Se genera un token de acceso que incluye toda la información necesaria para el ciclo del proceso

**Nota:** Este mecanismo de autenticación utiliza código de usuario en lugar de correo electrónico, por decisión de diseño. Esta elección refleja el uso previsto del sistema en contextos internos o empresariales.

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
  "usuario": "JPEREZ",
  "password": "contraseña123"
}
```

### Parámetros

| Campo | Tipo | Requerido | Descripción | Validaciones |
|-------|------|-----------|-------------|--------------|
| `usuario` | string | Sí | code del usuario | no vacío y existente en tabla (1102) |
| `password` | string | Sí | Contraseña del usuario | No vacía (1103), mínimo 8 caracteres (1104) |

---

## Response

### Success (200 OK)

**Ejemplo 1: Empleado**
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

**Ejemplo 2: Cliente**
```json
{
  "error": 0,
  "respuesta": "Autenticación exitosa",
  "resultado": {
    "token": "1|abcdef1234567890abcdef1234567890",
    "user": {
      "user_id": 2,
      "user_code": "CLIENTE01",
      "tipo_usuario": "cliente",
      "usuario_id": null,
      "cliente_id": 10,
      "es_supervisor": false,
      "nombre": "Cliente Ejemplo S.A.",
      "email": "contacto@cliente-ejemplo.com"
    }
  }
}
```

**Ejemplo 3: Usuario Supervisor**
```json
{
  "error": 0,
  "respuesta": "Autenticación exitosa",
  "resultado": {
    "token": "1|abcdef1234567890abcdef1234567890",
    "user": {
      "user_id": 3,
      "user_code": "MGARCIA",
      "tipo_usuario": "usuario",
      "usuario_id": 8,
      "cliente_id": null,
      "es_supervisor": true,
      "nombre": "María García",
      "email": "maria.garcia@ejemplo.com"
    }
  }
}
```

### Campos de Respuesta

| Campo | Tipo | Descripción | Notas |
|-------|------|-------------|-------|
| `error` | integer | Código de error (0 = éxito) | |
| `respuesta` | string | Mensaje legible para el usuario | |
| `resultado.token` | string | Token de autenticación (Sanctum) | Debe incluirse en header `Authorization: Bearer {token}` |
| `resultado.user.user_id` | integer | ID del registro en tabla USERS | Obligatorio |
| `resultado.user.user_code` | string | Código del usuario autenticado | Valor de `User.code`, obligatorio |
| `resultado.user.tipo_usuario` | string | Tipo de usuario | `"cliente"` o `"usuario"`, obligatorio |
| `resultado.user.usuario_id` | integer \| null | ID del usuario (si tipo_usuario = "usuario") | ID de `PQ_PARTES_USUARIOS.id` o `null` |
| `resultado.user.cliente_id` | integer \| null | ID del cliente (si tipo_usuario = "cliente") | ID de `PQ_PARTES_CLIENTES.id` o `null` |
| `resultado.user.es_supervisor` | boolean | Indica si es supervisor | `false` para clientes, valor de `supervisor` para usuarios |
| `resultado.user.nombre` | string | Nombre completo | Nombre del cliente o usuario |
| `resultado.user.email` | string \| null | Email | Email del cliente o usuario (puede ser null) |

**Valores a conservar durante el ciclo del proceso:**
Todos los campos de `resultado.user` deben conservarse durante todo el ciclo del proceso (desde login hasta logout) y estar disponibles en cada request autenticado. Ver `docs/modelo-datos.md` para más detalles.

---

## Errores

### 422 Unprocessable Entity - Validación

```json
{
  "error": 1102,
  "respuesta": "El código de usuario no puede estar vacío",
  "resultado": {
    "errors": {
      "usuario": ["El código de usuario no puede estar vacío"]
    }
  }
}
```

**Códigos de error posibles:**
- `1101`: Código de usuario requerido
- `1102`: Código de usuario no puede estar vacío
- `1103`: Contraseña requerida
- `1104`: Contraseña muy corta

### 401 Unauthorized - Credenciales Inválidas

```json
{
  "error": 3201,
  "respuesta": "Credenciales inválidas",
  "resultado": {}
}
```

**Códigos de error posibles:**
- `3201`: Credenciales inválidas
- `3202`: Usuario no encontrado
- `3203`: Contraseña incorrecta
- `4203`: Usuario inactivo

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

1. **usuario:**
   - Debe estar presente (1101)
   - no debe estar vacío (1102)

2. **Contraseña:**
   - Debe estar presente (1103)
   - Debe tener al menos 8 caracteres (1104)

### A Nivel de Negocio

1. **User debe existir:**
   - Buscar registro en tabla `USERS` por `code`
   - Si no existe: Error 3202

2. **User debe estar activo y no inhabilitado:**
   - Verificar campo `activo = true` en `USERS`
   - Verificar campo `inhabilitado = false` en `USERS`
   - Si está inactivo o inhabilitado: Error 4203

3. **Contraseña debe coincidir:**
   - Verificar hash con `Hash::check($password, $user->password_hash)`
   - Si no coincide: Error 3203

4. **Determinar tipo de usuario:**
   - Buscar `User.code` en `PQ_PARTES_CLIENTES.code`
   - Buscar `User.code` en `PQ_PARTES_USUARIOS.code`
   - Si existe en `PQ_PARTES_CLIENTES`:
     - `tipo_usuario = "cliente"`
     - Obtener `cliente_id` del registro
     - Verificar que el cliente esté activo y no inhabilitado
     - `es_supervisor = false`
   - Si existe en `PQ_PARTES_USUARIOS`:
     - `tipo_usuario = "usuario"`
     - Obtener `usuario_id` del registro
     - Verificar que el usuario esté activo y no inhabilitado
     - Obtener `supervisor` del registro → `es_supervisor = supervisor`
   - Si no existe en ninguna tabla: Error 3202 (usuario no encontrado)

---

## Operaciones de Base de Datos

### Tablas Involucradas

- `USERS` (sin prefijo PQ_PARTES_) - Tabla de autenticación
- `PQ_PARTES_CLIENTES` - Si el usuario es cliente
- `PQ_PARTES_USUARIOS` - Si el usuario es empleado

### Consultas

```php
// 1. Buscar User por code en tabla USERS
$user = User::where('code', $code)
    ->where('activo', true)
    ->where('inhabilitado', false)
    ->first();

if (!$user || !Hash::check($password, $user->password_hash)) {
    throw new AuthenticationException('Credenciales inválidas', 3201);
}

// 2. Determinar tipo de usuario
$tipoUsuario = null;
$clienteId = null;
$usuarioId = null;
$esSupervisor = false;
$nombre = null;
$email = null;

// Buscar en PQ_PARTES_CLIENTES
$cliente = Cliente::where('code', $user->code)
    ->where('activo', true)
    ->where('inhabilitado', false)
    ->first();

if ($cliente) {
    $tipoUsuario = 'cliente';
    $clienteId = $cliente->id;
    $usuarioId = null;
    $esSupervisor = false;
    $nombre = $cliente->nombre;
    $email = $cliente->email;
} else {
    // Buscar en PQ_PARTES_USUARIOS
    $usuario = Usuario::where('code', $user->code)
        ->where('activo', true)
        ->where('inhabilitado', false)
        ->first();
    
    if (!$usuario) {
        throw new AuthenticationException('Usuario no encontrado', 3202);
    }
    
    $tipoUsuario = 'usuario';
    $clienteId = null;
    $usuarioId = $usuario->id;
    $esSupervisor = $usuario->supervisor;
    $nombre = $usuario->nombre;
    $email = $usuario->email;
}

// 3. Actualizar último login (opcional)
$user->last_login_at = now();
$user->save();

// 4. Generar token Sanctum asociado al User
$token = $user->createToken('auth-token')->plainTextToken;

// 5. Preparar respuesta con todos los valores a conservar
$userData = [
    'user_id' => $user->id,
    'user_code' => $user->code,
    'tipo_usuario' => $tipoUsuario,
    'usuario_id' => $usuarioId,
    'cliente_id' => $clienteId,
    'es_supervisor' => $esSupervisor,
    'nombre' => $nombre,
    'email' => $email,
];
```

### Índices Utilizados

- `USERS.code` (UNIQUE) - Búsqueda por código de usuario
- `USERS.activo` - Filtro de usuarios activos
- `PQ_PARTES_CLIENTES.code` (UNIQUE) - Búsqueda de cliente por código
- `PQ_PARTES_USUARIOS.code` (UNIQUE) - Búsqueda de usuario por código

---

## Seguridad

### Consideraciones

1. **Nunca exponer:**
   - `password_hash` en ninguna respuesta
   - Detalles internos del sistema en mensajes de error

2. **Rate Limiting:**
   - Aplicar límite de intentos de login por IP
   - Bloquear temporalmente después de múltiples intentos fallidos

3. **Tokens:**
   - Tokens generados con Laravel Sanctum
   - Expiración configurable
   - Tokens almacenados de forma segura

---

## Ejemplos de Uso

### cURL

```bash
curl -X POST https://api.ejemplo.com/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "usuario": "JPEREZ",
    "password": "contraseña123"
  }'
```

### JavaScript (Fetch)

```javascript
const response = await fetch('/api/v1/auth/login', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    usuario: 'JPEREZ',
    password: 'contraseña123'
  })
});

const data = await response.json();

if (data.error === 0) {
  // Guardar token
  localStorage.setItem('token', data.resultado.token);
  // Redirigir al dashboard
}
```

---

## Notas

- Este es el único endpoint público que no requiere autenticación
- El token recibido debe incluirse en el header `Authorization: Bearer {token}` en todas las solicitudes subsiguientes
- Los tokens pueden revocarse desde el backend
- Se recomienda implementar refresh tokens para aplicaciones de larga duración

---

**Última actualización:** 2025-01-20

