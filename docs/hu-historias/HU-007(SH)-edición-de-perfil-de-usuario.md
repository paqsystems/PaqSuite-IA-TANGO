# HU-007 – Edición de perfil de usuario

## Épica
Épica 2: Configuración de Usuario


**Rol:** Empleado / Empleado Supervisor / Cliente  
**Clasificación:** SHOULD-HAVE  
**Historia:** Como usuario autenticado quiero editar mi nombre y email para mantener actualizada mi información personal.

**Criterios de aceptación:**
- El usuario puede acceder a la opción "Editar perfil".
- El usuario puede modificar su nombre.
- El usuario puede modificar su email (si aplica).
- El sistema valida que el email tenga formato válido (si se proporciona).
- El sistema valida que el email sea único (si se modifica).
- El código de usuario no es modificable.
- El usuario puede guardar los cambios.
- Se muestra un mensaje de confirmación al guardar.
- Los cambios se reflejan inmediatamente en el perfil.

**Notas de reglas de negocio:**
- El `code` no debe ser modificable.
- El email debe ser único si se proporciona.

**Dependencias:** HU-006.

---

## Épica 3: Gestión de Clientes (ABM)

