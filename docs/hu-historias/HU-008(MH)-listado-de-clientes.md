# HU-008 – Listado de clientes

## Épica
Épica 3: Gestión de Clientes (ABM)


**Rol:** Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como supervisor quiero ver el listado de todos los clientes para gestionarlos.

**Criterios de aceptación:**
- El supervisor puede acceder a la sección "Clientes".
- Se muestra una tabla con todos los clientes.
- La tabla muestra: código, nombre, tipo de cliente, estado (activo/inactivo), inhabilitado (sí/no).
- Los clientes se listan paginados (si hay muchos).
- Se puede buscar clientes por código o nombre.
- Se puede filtrar por tipo de cliente.
- Se puede filtrar por estado (activo/inactivo).
- Se puede filtrar por inhabilitado (sí/no).
- Se muestra el total de clientes.
- Los clientes inhabilitados se muestran claramente diferenciados (opcional: con indicador visual).

**Notas de reglas de negocio:**
- Solo los supervisores pueden acceder a esta funcionalidad.
- Se deben listar todos los clientes, independientemente de su estado.

**Dependencias:** HU-001 (autenticación).

---

