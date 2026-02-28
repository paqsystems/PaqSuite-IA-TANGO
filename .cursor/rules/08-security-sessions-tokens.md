# 02 — Seguridad (Sanctum) — obligatorio, OWASP-aligned

## 0) Modelo multiempresa y tenant (OBLIGATORIO)

El ERP opera con modelo multiempresa. En cada request de gestión:

- **Header `X-Company-Id`:** Identificador de la empresa activa (tenant).
- **Validación obligatoria:** El usuario debe tener asignación y permisos sobre esa empresa.
- **Dictionary DB:** Contiene usuarios, empresas, roles, permisos, asignaciones.
- **Company DB:** Contiene datos operativos de cada empresa (clientes, tareas, etc.).

**Regla:** Nunca confiar en `X-Company-Id` sin validar contra las asignaciones del usuario en Dictionary DB.

**Documentación detallada:** `docs/01-arquitectura/06-mapa-visual-seguridad-roles-permisos-menu.md`, `docs/01-arquitectura/07-mapa-visual-tenancy-resolucion-db.md`

## 1) Estándares adoptados
- OWASP API Security Top 10
- OWASP ASVS (nivel 2 recomendado)
- NIST 800-63 (principios de autenticación digital)
- Buenas prácticas de SDLC (seguridad por diseño)

## 2) Autenticación con Laravel Sanctum
### Modalidades posibles
- Tokens personales (PAT) para integraciones/clients (mobile, terceros, etc.)
- SPA con cookies (si se usa sesión/cookie; requiere CSRF)

### Regla del proyecto (API)
- Todas las APIs requieren autenticación vía Sanctum, salvo endpoints públicos explícitos (login/health).
- En cada request autenticado debe existir identidad (user) comprobable.
- Los tokens deben poder revocarse.

### Requisitos mínimos
- Definir política de expiración/rotación de tokens.
- Si se usan abilities/scopes: cada token debe tener abilities mínimas.
- No confiar en el frontend para permisos.

## 2.1) Gestión de Contraseñas (OBLIGATORIO)

### Regla Fundamental: Todas las Contraseñas Deben Estar Hasheadas

**OBLIGATORIO:** Todas las contraseñas de usuarios **DEBEN** almacenarse hasheadas en la base de datos. **NUNCA** almacenar contraseñas en texto plano.

### Protocolo de Hasheado

**Algoritmo:** **bcrypt** (algoritmo por defecto de Laravel)

**Configuración:**
- Usar la configuración por defecto de Laravel (`config/hashing.php`)
- Cost factor: 10 (por defecto, ajustable según necesidades de performance)
- Salt automático: bcrypt genera salt automáticamente para cada hash

**Implementación Obligatoria:**

1. **Para crear/actualizar contraseñas:**
   ```php
   // ✅ CORRECTO - Usar Hash::make()
   $passwordHash = Hash::make($password);
   $user->password_hash = $passwordHash;
   $user->save();
   ```

2. **Para validar contraseñas:**
   ```php
   // ✅ CORRECTO - Usar Hash::check()
   if (Hash::check($password, $user->password_hash)) {
       // Contraseña válida
   } else {
       // Contraseña inválida
   }
   ```

3. **NUNCA hacer:**
   ```php
   // ❌ PROHIBIDO - Almacenar en texto plano
   $user->password_hash = $password;  // NUNCA
   
   // ❌ PROHIBIDO - Comparar directamente
   if ($user->password_hash === $password) {  // NUNCA
   
   // ❌ PROHIBIDO - Usar md5, sha1, sha256 (no son adecuados para contraseñas)
   $user->password_hash = md5($password);  // NUNCA
   ```

### Requisitos de Contraseñas

**Longitud mínima:** 8 caracteres (validar antes de hashear)

**Validaciones obligatorias:**
- Campo `password` no puede estar vacío
- Campo `password` debe tener mínimo 8 caracteres
- Validar en FormRequest antes de procesar

**Almacenamiento:**
- Campo en BD: `password_hash` (tipo: `varchar` o `text`, suficiente para hash bcrypt ~60 caracteres)
- Tabla: `USERS` (base DICCIONARIO, no en bases de empresas)
- **NUNCA** exponer `password_hash` en respuestas de API

### Regeneración de Contraseñas

Cuando se actualiza una contraseña:
1. Validar la nueva contraseña (mínimo 8 caracteres)
2. Hashear con `Hash::make()`
3. Actualizar `password_hash` en tabla `USERS`
4. Actualizar contraseñas solo en tabla `USERS`

### Ejemplo Completo

```php
// En FormRequest
public function rules(): array
{
    return [
        'password' => ['required', 'string', 'min:8'],
    ];
}

// En Service
public function updatePassword(User $user, string $newPassword): void
{
    // Validar longitud (ya validado en FormRequest, pero verificar aquí también)
    if (strlen($newPassword) < 8) {
        throw new ValidationException('La contraseña debe tener al menos 8 caracteres');
    }
    
    // Hashear contraseña
    $user->password_hash = Hash::make($newPassword);
    $user->save();
}

// En Login Service
public function validateCredentials(string $code, string $password): ?User
{
    $user = User::where('code', $code)
        ->where('activo', true)
        ->where('inhabilitado', false)
        ->first();
    
    if (!$user || !Hash::check($password, $user->password_hash)) {
        return null;  // Credenciales inválidas
    }
    
    return $user;
}
```

## 3) Autorización (BOLA / Function Level)
- Implementar policies/gates por acción.
- Validar pertenencia del recurso (BOLA): un usuario no accede/modifica tickets ajenos.
- Prohibido “autorizar” solo por estar logueado.

## 4) Input validation & sanitización
- Validar **antes** de persistir (FormRequest).
- Limitar tamaños máximos (strings, arrays, payload).
- Sanitizar campos de texto si aceptan HTML/markdown (definir estrategia).

## 5) Anti-inyección (SQL/Command/Template)
- Prohibido concatenar SQL con input del usuario.
- Usar bindings/params en Query Builder/DB::select.
- `whereRaw` solo con bindings y sin interpolación.
- Campos dinámicos (sort/filter) solo por whitelist.

## 6) Rate limiting / brute force
- Rate limit global y por endpoints sensibles (login, búsquedas pesadas).
- Responder 429 manteniendo `error/respuesta/resultado`.

## 7) Errores y logs seguros
- Nunca exponer stacktrace en producción.
- No loguear: tokens, passwords, connection strings, datos sensibles.
- Usar correlation id por request (`X-Request-Id` generado o recibido).

## 8) TLS y headers
- TLS obligatorio en producción.
- Definir headers de seguridad a nivel infra (HSTS, nosniff, frame-options, etc.).

## 9) Adjuntos
- Validar MIME real, tamaño máximo, extensiones.
- Almacenar fuera del webroot.
- Servir por endpoints autorizados o URLs firmadas.

## 10) CSRF (si se usa modo SPA con cookies)
- Proteger con CSRF tokens y SameSite.
- Separar claramente APIs cookie-based vs token-based.

## 11) Gestión de Secretos y Credenciales (OBLIGATORIO)

### Variables de Entorno
- **NUNCA** hardcodear secretos, contraseñas, API keys, o connection strings en el código
- Usar archivo `.env` para configuración sensible
- Agregar `.env` a `.gitignore` (nunca commitear)
- Proporcionar `.env.example` con placeholders (sin valores reales)
- Documentar todas las variables de entorno requeridas en README

### Secretos en Base de Datos
- Connection strings: usar variables de entorno
- Credenciales de BD: nunca en código, siempre en `.env`
- Si se requieren credenciales encriptadas: usar Laravel Encryption (`Crypt::encrypt()` / `Crypt::decrypt()`)

### API Keys y Tokens Externos
- Almacenar en variables de entorno
- Nunca exponer en logs, respuestas de error, o código fuente
- Rotar periódicamente según política de seguridad

## 12) Validación y Sanitización de Input (OBLIGATORIO)

### Validación Obligatoria
- **TODOS** los inputs del usuario deben validarse antes de procesar
- Usar FormRequest de Laravel para validación estructurada
- Validar tipos de datos, rangos, formatos, y longitud
- Validar en backend (nunca confiar solo en validación frontend)

### Sanitización
- Sanitizar campos de texto que aceptan HTML (usar `strip_tags()` o librería HTMLPurifier)
- Escapar output en vistas (Laravel Blade lo hace automáticamente)
- Para campos que aceptan markdown: validar y sanitizar antes de almacenar

### Whitelist para Campos Dinámicos
- Campos como `sort`, `order_by`, `filter` deben validarse contra whitelist
- Nunca permitir que el usuario especifique campos arbitrarios para ordenar/filtrar
- Ejemplo:
  ```php
  // ✅ CORRECTO - Whitelist
  $allowedSortFields = ['nombre', 'fecha', 'cliente_id'];
  $sortField = in_array($request->sort, $allowedSortFields) 
      ? $request->sort 
      : 'fecha';
  
  // ❌ PROHIBIDO - Sin validación
  $sortField = $request->sort;  // NUNCA
  ```

## 13) Protección contra Ataques Comunes

### XSS (Cross-Site Scripting)
- Escapar output en vistas (Blade lo hace automáticamente)
- Usar `{{ $variable }}` en lugar de `{!! $variable !!}` (a menos que sea HTML seguro)
- Validar y sanitizar input que se renderiza como HTML
- Headers de seguridad: `X-Content-Type-Options: nosniff`

### CSRF (Cross-Site Request Forgery)
- Si se usa modo SPA con cookies: implementar CSRF tokens
- Si se usa tokens (Sanctum): CSRF no es necesario (tokens en header)
- Verificar origen de requests cuando sea crítico

### Clickjacking
- Header: `X-Frame-Options: DENY` o `SAMEORIGIN`
- Configurar en middleware o a nivel de servidor web

### Inyección de Comandos
- **NUNCA** ejecutar comandos del sistema con input del usuario sin validación extrema
- Si es necesario ejecutar comandos: usar whitelist de comandos permitidos
- Validar y sanitizar parámetros de comandos
- Preferir APIs/librerías sobre ejecución de comandos cuando sea posible

## 14) Headers de Seguridad (OBLIGATORIO)

### Headers Mínimos Requeridos
Configurar en middleware o servidor web:

```
X-Content-Type-Options: nosniff
X-Frame-Options: DENY
X-XSS-Protection: 1; mode=block
Strict-Transport-Security: max-age=31536000; includeSubDomains (solo en producción con TLS)
Content-Security-Policy: default-src 'self' (ajustar según necesidades)
Referrer-Policy: strict-origin-when-cross-origin
```

### Implementación
- Configurar en middleware de Laravel o a nivel de servidor web (nginx/apache)
- En desarrollo: headers opcionales
- En producción: **OBLIGATORIO**

## 15) Logging Seguro (OBLIGATORIO)

### Datos que NUNCA se deben loguear
- Contraseñas (ni en texto plano ni hasheadas)
- Tokens de autenticación (JWT, Sanctum tokens)
- Connection strings completos
- API keys
- Datos personales sensibles (sin consentimiento)
- Números de tarjeta de crédito (si aplica)
- Cualquier dato que pueda comprometer la seguridad si se expone

### Qué SÍ loguear
- IDs de usuario (no contraseñas)
- Timestamps de acciones
- IPs de origen (con cuidado de privacidad)
- Códigos de error
- Correlation IDs para trazabilidad
- Acciones de usuario (login, logout, operaciones críticas)

### Formato de Logs
- Usar correlation ID (`X-Request-Id`) para agrupar logs de un request
- Formato estructurado (JSON) para facilitar análisis
- Niveles apropiados (debug, info, warning, error)

## 16) Gestión de Sesiones y Tokens

### Tokens Sanctum
- **Expiración:** **24 horas** (configuración obligatoria del proyecto)
- **Rotación:** Implementar refresh tokens si se requiere sesión larga 
- **Revocación:** Permitir revocar tokens individuales o todos los tokens de un usuario
- **Almacenamiento:** Tokens en BD (tabla `personal_access_tokens` de Sanctum)

### Política de Tokens
- Un usuario puede tener múltiples tokens activos (diferentes dispositivos)
- **Al cambiar contraseña: REVOCAR TODOS los tokens del usuario** (obligatorio)
- Al logout: revocar el token actual
- Tokens expirados: limpiar periódicamente (job programado)

### Implementación de Expiración de Tokens

**Configuración en Laravel Sanctum:**
```php
// config/sanctum.php
'expiration' => 24 * 60, // 24 horas en minutos
```

**Implementación de revocación al cambiar contraseña:**
```php
// En servicio de cambio de contraseña
public function updatePassword(User $user, string $newPassword): void
{
    // Validar y hashear nueva contraseña
    $user->password_hash = Hash::make($newPassword);
    $user->save();
    
    // REVOCAR TODOS los tokens del usuario
    $user->tokens()->delete();
}
```

## 17) Auditoría y Trazabilidad

### Eventos a Auditar
- Login exitoso y fallido
- Cambios de contraseña
- Operaciones críticas (crear/editar/eliminar recursos importantes)
- Cambios de permisos/roles
- Accesos a datos sensibles

### Información a Registrar
- Usuario que realizó la acción
- Timestamp
- Tipo de acción
- Recurso afectado (ID)
- IP de origen (opcional, considerar privacidad)
- Resultado (éxito/fallo)

### Almacenamiento
- Considerar tabla de auditoría separada si el volumen es alto
- **Retención: 1 año** (política obligatoria del proyecto)
- No almacenar datos sensibles en logs de auditoría
- Implementar job programado para limpiar registros de auditoría mayores a 1 año

## 18) Configuración de Producción

### Checklist de Seguridad para Producción
- [ ] TLS/HTTPS habilitado y configurado correctamente
- [ ] Headers de seguridad configurados
- [ ] Variables de entorno seguras (no en código)
- [ ] Rate limiting activado
- [ ] Logs configurados (sin datos sensibles)
- [ ] Stacktraces deshabilitados en producción
- [ ] Debug mode deshabilitado (`APP_DEBUG=false`)
- [ ] Base de datos con credenciales seguras
- [ ] Backups encriptados
- [ ] Política de expiración de tokens configurada
- [ ] CSRF habilitado (si aplica)
- [ ] Validación de input en todos los endpoints
- [ ] Todas las contraseñas hasheadas (verificar en BD)

---
