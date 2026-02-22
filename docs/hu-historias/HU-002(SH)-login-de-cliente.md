# HU-002 – Login de cliente

## Épica
Épica 1: Autenticación y Acceso


**Rol:** Cliente  
**Clasificación:** SHOULD-HAVE  
**Historia:** Como cliente quiero autenticarme en el sistema con mi código y contraseña para consultar las tareas realizadas para mí.

**Criterios de aceptación:**
- El cliente puede ingresar su código y contraseña.
- El sistema valida que el código no esté vacío.
- El sistema valida que la contraseña no esté vacía.
- El sistema valida que el `User` exista en la tabla `USERS` (sin prefijo PQ_PARTES_).
- El sistema valida que el `User` esté activo (`activo = true`) y no inhabilitado (`inhabilitado = false`) en `USERS`.
- El sistema valida que la contraseña coincida con el hash almacenado en `USERS`.
- Después del login exitoso, el sistema busca el `User.code` en `PQ_PARTES_CLIENTES.code`.
- El sistema determina que `tipo_usuario = "cliente"`.
- El sistema obtiene el `cliente_id` del registro en `PQ_PARTES_CLIENTES`.
- El sistema verifica que el cliente esté activo y no inhabilitado en `PQ_PARTES_CLIENTES`.
- El sistema establece `es_supervisor = false` (siempre para clientes).
- Si las credenciales son válidas, el sistema genera un token de autenticación que incluye: `user_id`, `user_code`, `tipo_usuario`, `usuario_id` (null), `cliente_id`, `es_supervisor` (false).
- El token se almacena en el frontend.
- Los valores de autenticación se conservan durante todo el ciclo del proceso (hasta logout).
- El cliente es redirigido a su vista de consulta de tareas.
- Si las credenciales son inválidas, se muestra un mensaje de error claro.

**Notas de reglas de negocio:**
- La autenticación se realiza contra la tabla `USERS` (sin prefijo PQ_PARTES_).
- Solo los clientes con `user_id` configurado (relación con `USERS`) pueden autenticarse.
- Validar `activo = true` y `inhabilitado = false` en conjunto tanto en `USERS` como en `PQ_PARTES_CLIENTES`.
- El `code` en `PQ_PARTES_CLIENTES` debe coincidir con el `code` en `USERS` si tiene `user_id`.

**Dependencias:** HU-001 (comparte lógica de autenticación).

---

