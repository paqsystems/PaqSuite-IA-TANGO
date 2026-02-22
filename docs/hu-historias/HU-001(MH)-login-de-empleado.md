# HU-001 – Login de empleado

## Épica
Épica 1: Autenticación y Acceso


**Rol:** Empleado / Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como empleado quiero autenticarme en el sistema con mi código de usuario y contraseña para acceder a las funcionalidades del sistema.

**Criterios de aceptación:**
- El usuario puede ingresar su código de usuario y contraseña.
- El sistema valida que el código de usuario no esté vacío.
- El sistema valida que la contraseña no esté vacía.
- El sistema valida que el `User` exista en la tabla `USERS` (sin prefijo PQ_PARTES_).
- El sistema valida que el `User` esté activo (`activo = true`) y no inhabilitado (`inhabilitado = false`) en `USERS`.
- El sistema valida que la contraseña coincida con el hash almacenado en `USERS`.
- Después del login exitoso, el sistema busca el `User.code` en `PQ_PARTES_USUARIOS.code`.
- El sistema determina que `tipo_usuario = "usuario"`.
- El sistema obtiene el `usuario_id` del registro en `PQ_PARTES_USUARIOS`.
- El sistema verifica que el usuario esté activo y no inhabilitado en `PQ_PARTES_USUARIOS`.
- El sistema obtiene el valor de `supervisor` de `PQ_PARTES_USUARIOS` para determinar `es_supervisor`.
- Si las credenciales son válidas, el sistema genera un token de autenticación (Sanctum) que incluye: `user_id`, `user_code`, `tipo_usuario`, `usuario_id`, `cliente_id` (null), `es_supervisor`.
- El token se almacena en el frontend (localStorage o sessionStorage).
- Los valores de autenticación se conservan durante todo el ciclo del proceso (hasta logout).
- El usuario es redirigido al dashboard principal.
- Si las credenciales son inválidas, se muestra un mensaje de error claro.
- El mensaje de error no revela si el usuario existe o no (seguridad).

**Notas de reglas de negocio:**
- La autenticación se realiza contra la tabla `USERS` (sin prefijo PQ_PARTES_).
- Validar `activo = true` y `inhabilitado = false` en conjunto tanto en `USERS` como en `PQ_PARTES_USUARIOS`.
- El código de usuario debe existir y no ser NULL.
- Un `User.code` solo puede estar asociado a un Cliente O a un Usuario, no a ambos.
- El `code` en `PQ_PARTES_USUARIOS` debe coincidir con el `code` en `USERS`.

**Dependencias:** Ninguna (historia base del flujo E2E).

---

