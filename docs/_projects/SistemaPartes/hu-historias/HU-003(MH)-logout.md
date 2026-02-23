# HU-003 – Logout

## Épica
Épica 1: Autenticación y Acceso


**Rol:** Empleado / Empleado Supervisor / Cliente  
**Clasificación:** MUST-HAVE  
**Historia:** Como usuario autenticado quiero cerrar sesión para proteger mi cuenta cuando termine de usar el sistema.

**Criterios de aceptación:**
- El usuario puede hacer clic en un botón de "Cerrar Sesión".
- Al hacer clic, el sistema invalida el token de autenticación (si aplica).
- El token se elimina del almacenamiento del frontend.
- El usuario es redirigido a la página de login.
- El usuario no puede acceder a rutas protegidas después del logout.

**Notas de reglas de negocio:**
- El logout debe ser seguro y limpiar toda la sesión del frontend.

**Dependencias:** HU-001.

---

