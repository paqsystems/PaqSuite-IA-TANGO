# HU-004 – Recuperación de contraseña

## Épica
Épica 1: Autenticación y Acceso


**Rol:** Empleado / Empleado Supervisor / Cliente  
**Clasificación:** SHOULD-HAVE  
**Historia:** Como usuario quiero recuperar mi contraseña si la olvidé para poder acceder nuevamente al sistema.

**Criterios de aceptación:**
- El usuario puede acceder a un enlace "¿Olvidaste tu contraseña?" en la página de login.
- El usuario ingresa su código de usuario o email.
- El sistema valida que el usuario exista.
- El sistema envía un email con un enlace de recuperación (si el usuario tiene email configurado).
- El usuario puede establecer una nueva contraseña mediante el enlace.
- La nueva contraseña se valida (longitud mínima, complejidad si aplica).
- El sistema actualiza el `password_hash` del usuario.
- El usuario puede iniciar sesión con la nueva contraseña.

**Notas de reglas de negocio:**
- El enlace de recuperación debe tener un tiempo de expiración (ej: 1 hora).
- El enlace debe ser único y no reutilizable.

**Dependencias:** HU-001, HU-002.

---

