# HU-010 – Edición de cliente

## Épica
Épica 3: Gestión de Clientes (ABM)


**Rol:** Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como supervisor quiero editar la información de un cliente existente para mantener actualizados sus datos.

**Criterios de aceptación:**
- El supervisor puede acceder a la edición de un cliente desde el listado.
- Se carga el formulario con los datos actuales del cliente.
- El código de cliente no es modificable (solo lectura).
- El supervisor puede modificar: nombre, tipo de cliente, email, estado activo, estado inhabilitado.
- Si el cliente tiene acceso al sistema (`user_id` configurado), se puede cambiar la contraseña.
- Se puede habilitar o deshabilitar el acceso al sistema (si se deshabilita, se elimina la relación con `USERS`).
- El sistema valida que el nombre no esté vacío.
- El sistema valida que el tipo de cliente exista y esté activo/no inhabilitado.
- El sistema valida que el email tenga formato válido (si se proporciona).
- El sistema valida que el email sea único (si se proporciona y cambió).
- Si se cambia la contraseña, el sistema actualiza el `password_hash` en `USERS` (no en `PQ_PARTES_CLIENTES`).
- Si se cambia el estado `activo` o `inhabilitado`, el sistema actualiza ambos: `USERS` (si tiene `user_id`) y `PQ_PARTES_CLIENTES`.
- El sistema valida la regla de tipos de tarea (igual que en creación).
- Al guardar, el sistema actualiza el cliente en la base de datos.
- Se muestra un mensaje de confirmación.
- Los cambios se reflejan en el listado.

**Notas de reglas de negocio:**
- El `code` no debe ser modificable.
- La contraseña se almacena en `USERS`, no en `PQ_PARTES_CLIENTES`.
- Los cambios de estado deben sincronizarse entre `USERS` y `PQ_PARTES_CLIENTES` (si tiene `user_id`).
- Las mismas validaciones que en creación aplican.

**Dependencias:** HU-009.

---

