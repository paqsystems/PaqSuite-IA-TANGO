# HU-006 – Visualización de perfil de usuario

## Épica
Épica 2: Configuración de Usuario


**Rol:** Empleado / Empleado Supervisor / Cliente  
**Clasificación:** MUST-HAVE  
**Historia:** Como usuario autenticado quiero ver mi información de perfil para verificar mis datos personales.

**Criterios de aceptación:**
- El usuario autenticado puede acceder a su perfil.
- Se muestra el código de usuario (solo lectura).
- Se muestra el nombre completo.
- Se muestra el email (si está configurado).
- Se muestra el rol (Empleado, Supervisor, Cliente).
- Se muestra la fecha de creación de la cuenta (opcional).
- Los campos son de solo lectura (excepto si hay funcionalidad de edición).

**Notas de reglas de negocio:**
- El código de usuario no debe ser modificable (es identificador único).

**Dependencias:** HU-001, HU-002.

---

