# HU-001 – Login de usuario

## Épica
001 – Seguridad y Acceso

## Clasificación
MUST-HAVE

## Rol
Usuario del sistema (empleado, supervisor, administrador)

## Narrativa

Como usuario del sistema quiero autenticarme con mi código y contraseña para acceder a las funcionalidades según mis permisos.

## Criterios de aceptación

- El usuario puede ingresar código y contraseña en el formulario de login.
- El sistema valida que el código no esté vacío antes de enviar.
- El sistema valida que la contraseña no esté vacía antes de enviar.
- El sistema valida que el usuario exista en la tabla `users`.
- El sistema valida que el usuario esté activo (`activo = true`) y no inhabilitado (`inhabilitado = false`).
- El sistema valida que la contraseña coincida con el hash almacenado.
- Si las credenciales son válidas, el sistema genera un token de autenticación (ej. Sanctum).
- El token se almacena en el frontend (localStorage o sessionStorage).
- El token incluye información necesaria: `user_id`, `user_code`, y datos para resolver permisos.
- Si el usuario tiene una sola empresa asignada, se redirige al layout principal con esa empresa como activa.
- Si el usuario tiene varias empresas asignadas, se redirige al selector de empresa.
- Si el usuario no tiene empresas asignadas, se muestra mensaje apropiado y se impide el acceso.
- Si las credenciales son inválidas, se muestra un mensaje de error genérico (sin revelar si el usuario existe).
- El mensaje de error no revela información sensible (seguridad).

## Reglas de negocio

- Autenticación contra tabla `users` (Dictionary DB).
- Solo usuarios con `activo = true` e `inhabilitado = false` pueden acceder.
- El usuario debe tener al menos un permiso en `Pq_Permiso` para operar.
- No se usa `users_identities` (login social/externo no aplica por el momento).

## Dependencias

- Tabla `users` creada y poblada.
- Tabla `Pq_Permiso` con asignaciones usuario-empresa-rol.
- Endpoint POST de login configurado.

## Referencias

- `docs/modelo-datos/md-diccionario/md-diccionario.md` – Esquema users
- `docs/01-arquitectura/06-mapa-visual-seguridad-roles-permisos-menu.md` – Flujo de autorización
