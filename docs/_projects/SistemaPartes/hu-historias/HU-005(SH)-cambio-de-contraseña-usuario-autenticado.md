# HU-005 – Cambio de contraseña (usuario autenticado)

## Épica
Épica 1: Autenticación y Acceso


**Rol:** Empleado / Empleado Supervisor / Cliente  
**Clasificación:** SHOULD-HAVE  
**Historia:** Como usuario autenticado quiero cambiar mi contraseña desde mi perfil para mantener la seguridad de mi cuenta.

**Criterios de aceptación:**
- El usuario autenticado puede acceder a una opción "Cambiar contraseña" en su perfil.
- El usuario debe ingresar su contraseña actual.
- El sistema valida que la contraseña actual sea correcta.
- El usuario ingresa la nueva contraseña y la confirma.
- El sistema valida que ambas contraseñas coincidan.
- El sistema valida la complejidad de la nueva contraseña (si aplica).
- Si todo es válido, el sistema actualiza el `password_hash`.
- Se muestra un mensaje de confirmación.
- El usuario debe volver a iniciar sesión con la nueva contraseña (opcional, según diseño).

**Notas de reglas de negocio:**
- La contraseña actual debe validarse antes de permitir el cambio.

**Dependencias:** HU-001, HU-002.

---

## Épica 2: Configuración de Usuario

