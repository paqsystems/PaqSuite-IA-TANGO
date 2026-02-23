# HU-009 – Creación de cliente

## Épica
Épica 3: Gestión de Clientes (ABM)


**Rol:** Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como supervisor quiero crear un nuevo cliente para poder asociar tareas a él.

**Criterios de aceptación:**
- El supervisor puede acceder al formulario de creación de cliente.
- El formulario tiene los siguientes campos:
  - Código (obligatorio, único)
  - Nombre/Descripción (obligatorio)
  - Tipo de Cliente (obligatorio, selector)
  - Email (opcional, único si se proporciona)
  - Habilitar acceso al sistema (checkbox, por defecto: false) - Si se marca, se debe proporcionar contraseña
  - Contraseña (obligatorio si se habilita acceso al sistema)
  - Activo (checkbox, por defecto: true)
  - Inhabilitado (checkbox, por defecto: false)
- El sistema valida que el código no esté vacío.
- El sistema valida que el código sea único.
- El sistema valida que el nombre no esté vacío.
- El sistema valida que el tipo de cliente esté seleccionado y exista.
- El sistema valida que el tipo de cliente esté activo y no inhabilitado.
- El sistema valida que el email tenga formato válido (si se proporciona).
- El sistema valida que el email sea único (si se proporciona).
- Si se habilita el acceso al sistema, el sistema valida que el código no exista en `USERS`.
- Si se habilita el acceso al sistema, el sistema valida que se proporcione contraseña.
- El sistema valida la regla: debe existir al menos un tipo de tarea genérico O el cliente debe tener al menos un tipo de tarea asignado (validación post-creación o durante creación si se asignan tipos).
- Si se habilita el acceso al sistema, al guardar, el sistema crea primero un registro en `USERS` con: `code` (del cliente), `password_hash` (de la contraseña proporcionada), `activo` (del cliente), `inhabilitado` (del cliente).
- Al guardar, el sistema crea el cliente en `PQ_PARTES_CLIENTES` con: `user_id` (FK al `USERS` creado, si se habilita acceso), `code` (debe coincidir con `User.code` si tiene `user_id`), y los demás campos.
- El sistema valida que el `code` del cliente coincida con el `code` del `User` creado (si se habilita acceso).
- Se muestra un mensaje de confirmación.
- El usuario es redirigido al listado de clientes o puede crear otro.

**Notas de reglas de negocio:**
- `code` es obligatorio y único.
- `tipo_cliente_id` es obligatorio.
- Si se habilita el acceso al sistema, se debe crear un registro en `USERS`.
- El `user_id` en `PQ_PARTES_CLIENTES` es opcional pero si existe debe referenciar a `USERS.id`.
- El `code` en `PQ_PARTES_CLIENTES` debe coincidir exactamente con el `code` en `USERS` si tiene `user_id`.
- Regla de tipos de tarea: el cliente debe tener al menos un tipo de tarea genérico disponible o un tipo asignado.

**Dependencias:** HU-008, HU-015 (tipos de cliente deben existir), HU-020 (tipos de tarea deben existir).

---

