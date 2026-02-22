# HU-011 – Eliminación de cliente

## Épica
Épica 3: Gestión de Clientes (ABM)


**Rol:** Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como supervisor quiero eliminar un cliente que ya no se utiliza para mantener el catálogo limpio.

**Criterios de aceptación:**
- El supervisor puede acceder a la opción de eliminar un cliente desde el listado o detalle.
- Antes de eliminar, el sistema verifica si el cliente tiene tareas asociadas.
- Si el cliente tiene tareas asociadas, se muestra un error y no se permite la eliminación.
- Si el cliente no tiene tareas asociadas, se muestra un diálogo de confirmación.
- El diálogo muestra el código y nombre del cliente a eliminar.
- El usuario debe confirmar la eliminación.
- Al confirmar, el sistema elimina el cliente de la base de datos.
- Se muestra un mensaje de confirmación.
- El cliente desaparece del listado.

**Notas de reglas de negocio:**
- No se puede eliminar un cliente si tiene tareas asociadas (integridad referencial).
- Código de error: 2112.

**Dependencias:** HU-010.

---

