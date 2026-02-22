# Historias de Usuario y Tickets – MVP

## Introducción

Este documento contiene el catálogo completo de historias de usuario para el MVP del sistema de registro de tareas para consultorías y empresas de servicios. El documento está organizado por épicas funcionales y clasifica cada historia como **MUST-HAVE** (indispensable para el flujo E2E) o **SHOULD-HAVE** (mejoras opcionales).

Este documento cubre las siguientes áreas funcionales:
- Infraestructura y base del sistema
- Autenticación y acceso al sistema
- Configuración de usuario
- Gestión de clientes (ABM)
- Gestión de tipos de cliente (ABM)
- Gestión de empleados (ABM)
- Gestión de tipos de tarea (ABM)
- Registro de tareas
- Proceso masivo de tareas
- Informes y consultas
- Dashboard

---

## Supuestos y Definiciones

### Roles de Usuario

1. **Cliente**: Usuario externo que puede autenticarse para consultar las tareas realizadas para él. Solo tiene permisos de lectura sobre sus propias tareas.
2. **Empleado**: Usuario interno que registra tareas diarias. Puede crear, editar y eliminar solo sus propias tareas. Solo visualiza sus propias tareas.
3. **Empleado Supervisor**: Usuario interno con permisos ampliados. Puede ver todas las tareas de todos los usuarios. Puede crear, editar y eliminar tareas de cualquier usuario. Al crear una tarea, puede seleccionar el usuario propietario (por defecto aparece él mismo).

### Entidades Principales

- **User (Tabla de Autenticación)**: Tabla `USERS` (sin prefijo PQ_PARTES_). Es la tabla central de autenticación del sistema. Tiene atributos: `id`, `code` (único, obligatorio), `password_hash` (obligatorio), `activo`, `inhabilitado` (boolean), `created_at`, `updated_at`. Después del login exitoso, se determina si el `User` corresponde a un Cliente (tabla `PQ_PARTES_CLIENTES`) o a un Empleado (tabla `PQ_PARTES_USUARIOS`).
- **Empleado**: Representa a los empleados que cargan las tareas al sistema. Tabla física: `PQ_PARTES_USUARIOS`. Tiene atributos: `id`, `user_id` (FK → User, obligatorio, único), `code` (único, obligatorio, debe coincidir con User.code), `nombre`, `email` (único, opcional), `supervisor` (boolean), `activo`, `inhabilitado` (boolean), `created_at`, `updated_at`. Cada registro debe tener una relación 1:1 con la tabla `USERS`.
- **Cliente**: Representa a los clientes para los cuales se registran tareas. Tabla física: `PQ_PARTES_CLIENTES`. Tiene atributos: `id`, `user_id` (FK → User, opcional, único), `nombre`, `tipo_cliente_id` (FK), `code` (único, obligatorio, debe coincidir con User.code si tiene user_id), `email` (único, opcional), `activo`, `inhabilitado` (boolean), `created_at`, `updated_at`. Si tiene `user_id` configurado, puede autenticarse y consultar tareas relacionadas (solo lectura).
- **TipoCliente**: Catálogo de tipos de cliente (ej: "Corporativo", "PyME", "Startup"). Tiene atributos: `id`, `code` (único, obligatorio), `descripcion`, `activo`, `inhabilitado` (boolean).
- **TipoTarea**: Catálogo de tipos de tarea. Tiene atributos: `id`, `code` (único, obligatorio), `descripcion`, `is_generico` (boolean), `is_default` (boolean), `activo`, `inhabilitado` (boolean).
- **RegistroTarea**: Registro de una tarea realizada. Tiene atributos: `id`, `usuario_id` (FK), `cliente_id` (FK), `tipo_tarea_id` (FK), `fecha`, `duracion_minutos`, `sin_cargo` (boolean), `presencial` (boolean), `observacion` (obligatorio), `cerrado` (boolean), `created_at`, `updated_at`.
- **ClienteTipoTarea**: Tabla de asociación muchos-a-muchos entre Cliente y TipoTarea (para tipos NO genéricos).

### Reglas de Negocio Clave

- **Tipo de tarea por defecto**: Solo puede existir un tipo de tarea con `is_default = true` en todo el sistema. Si `is_default = true`, entonces `is_generico = true` (forzado).
- **Tipos genéricos**: Los tipos de tarea con `is_generico = true` están disponibles para todos los clientes. Los tipos NO genéricos solo están disponibles para clientes que tengan una asociación explícita en `ClienteTipoTarea`.
- **Cliente y tipos de tarea**: Al crear/actualizar un cliente, debe existir al menos un tipo de tarea genérico O el cliente debe tener al menos un tipo de tarea asignado.
- **Duración en tramos**: La duración de las tareas debe estar en tramos de 15 minutos (15, 30, 45, 60, ..., 1440 minutos máximo).
- **Tarea cerrada**: Una tarea con `cerrado = true` no se puede modificar ni eliminar.
- **Integridad referencial**: No se puede eliminar Cliente, Usuario, TipoTarea o TipoCliente si están referenciados en otras tablas.

### Supuestos Adicionales

- El sistema es mono-tenant (una sola empresa).
- La autenticación se realiza contra la tabla `USERS` (sin prefijo PQ_PARTES_) mediante código y contraseña.
- Después del login exitoso, se determina si el `User` corresponde a un Cliente (tabla `PQ_PARTES_CLIENTES`) o a un Usuario (tabla `PQ_PARTES_USUARIOS`).
- Los clientes pueden autenticarse si tienen `user_id` configurado (relación con `USERS`).
- Los valores de autenticación (`tipo_usuario`, `user_code`, `usuario_id`/`cliente_id`, `es_supervisor`) se conservan durante todo el ciclo del proceso (desde login hasta logout).
- Un `User.code` solo puede estar asociado a un Cliente O a un Usuario, no a ambos.
- Todos los campos de estado (`activo`, `inhabilitado`) se validan en conjunto: un registro debe estar `activo = true` y `inhabilitado = false` para ser considerado habilitado.

---

## Épica 0: Infraestructura y Base del Sistema

### HU-00 – Generación de la base de datos inicial a partir del modelo definido

**Rol:** Administrador del sistema (infraestructura / plataforma)  
**Clasificación:** MUST-HAVE  
**Historia:** Como administrador del sistema, quiero generar la base de datos inicial a partir del modelo de datos definido, para disponer de una estructura consistente, versionada y reproducible que habilite el desarrollo, prueba y validación del resto del MVP.

**Contexto / Justificación:**
Esta historia de usuario es una **HU técnica habilitadora**.

Su objetivo no es aportar funcionalidad de negocio directa, sino establecer la infraestructura mínima necesaria para:
- implementar historias funcionales,
- ejecutar tests automatizados,
- garantizar consistencia entre entornos,
- permitir la reproducción completa del sistema desde el repositorio.

Debe ejecutarse **antes** del desarrollo de las historias funcionales del sistema.

**In Scope:**
- Generación del esquema completo de base de datos según el modelo definido en `docs/modelo-datos.md` y `database/modelo-datos.dbml`.
- **Uso del MCP de SQL Server (mssql-toolbox o mssql) configurado** para ejecutar la creación de tablas, índices, foreign keys y restricciones directamente en la base de datos.
- Creación de migraciones Laravel versionadas (up / down) que reflejen el esquema generado, para mantener sincronización entre el código y la base de datos.
- Aplicación de las convenciones del proyecto:
  - Prefijo de tablas `PQ_PARTES_` (excepto la tabla `USERS` que no lleva prefijo).
  - Nomenclatura de campos en snake_case.
  - Índices con prefijo `idx_`.
- Generación de datos mínimos (seeders Laravel) para permitir la ejecución de tests automatizados:
  - Al menos un usuario administrador/supervisor.
  - Al menos un cliente de prueba.
  - Al menos un tipo de cliente.
  - Al menos un tipo de tarea genérico (con `is_default = true`).
- Verificación de que la base de datos puede recrearse desde cero en un entorno limpio.
- Documentación del proceso de creación y ejecución de migraciones.

**Out of Scope:**
- Implementación de lógica de negocio.
- Desarrollo de endpoints o pantallas funcionales.
- Optimización avanzada de performance (índices adicionales más allá de los requeridos por integridad referencial).
- Uso de datos reales de producción.
- Configuración de backups automáticos.

**Suposiciones:**
- El modelo de datos inicial ya fue definido y validado en `docs/modelo-datos.md`.
- El entorno dispone de acceso a la base de datos SQL Server mediante el MCP configurado (`mssql-toolbox` o `mssql` en `mcp.json`).
- El motor de base de datos es SQL Server (compatible con las herramientas de migración Laravel).
- Laravel está configurado y listo para generar migraciones.

**Criterios de aceptación:**
- La base de datos puede generarse completamente desde cero a partir del repositorio.
- Todas las tablas respetan las convenciones del proyecto (prefijo `PQ_PARTES_`, excepto `USERS`).
- Todas las tablas, campos, índices y foreign keys del modelo están implementados correctamente.
- Existen migraciones Laravel versionadas con capacidad de rollback (métodos `up()` y `down()`).
- Las migraciones pueden ejecutarse tanto mediante Laravel (`php artisan migrate`) como mediante el MCP (para verificación y ejecución directa).
- Existen seeders con datos mínimos para permitir la ejecución de tests automatizados.
- El proceso es reproducible en entornos local y de testing.
- La ejecución no requiere pasos manuales fuera del repositorio y el MCP configurado.
- Se documenta el proceso de creación y ejecución de migraciones.

**Notas técnicas:**
- **Uso de MCP:** Se utilizará el servidor MCP de SQL Server configurado (`mssql-toolbox` o `mssql`) para ejecutar las sentencias SQL de creación de tablas, índices y restricciones. Esto permite verificación directa y ejecución controlada desde el entorno de desarrollo.
- **Migraciones Laravel:** Aunque la creación inicial puede realizarse mediante MCP, las migraciones Laravel deben generarse para mantener versionado y permitir rollback. Las migraciones deben reflejar exactamente el esquema creado.
- **Sincronización:** El esquema en la base de datos debe estar sincronizado con el modelo definido en `docs/modelo-datos.md` y `database/modelo-datos.dbml`.

**Dependencias:**
- No depende de historias funcionales.
- Es bloqueante para el desarrollo del resto de las historias del MVP.
- Requiere que el modelo de datos esté completamente definido y validado.

**Resultado Esperado:**
- Base de datos inicial creada correctamente con todas las tablas, índices y restricciones del modelo.
- Migraciones Laravel versionadas en el repositorio (`database/migrations/`).
- Seeders con datos mínimos en el repositorio (`database/seeders/`).
- Infraestructura de datos lista para el desarrollo de historias funcionales.
- Documentación del proceso de creación y ejecución.
- Evidencia verificable para validación del MVP.

---

## Épica 1: Autenticación y Acceso

### HU-001 – Login de empleado

**Rol:** Empleado / Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como empleado quiero autenticarme en el sistema con mi código de usuario y contraseña para acceder a las funcionalidades del sistema.

**Criterios de aceptación:**
- El usuario puede ingresar su código de usuario y contraseña.
- El sistema valida que el código de usuario no esté vacío.
- El sistema valida que la contraseña no esté vacía.
- El sistema valida que el `User` exista en la tabla `USERS` (sin prefijo PQ_PARTES_).
- El sistema valida que el `User` esté activo (`activo = true`) y no inhabilitado (`inhabilitado = false`) en `USERS`.
- El sistema valida que la contraseña coincida con el hash almacenado en `USERS`.
- Después del login exitoso, el sistema busca el `User.code` en `PQ_PARTES_USUARIOS.code`.
- El sistema determina que `tipo_usuario = "usuario"`.
- El sistema obtiene el `usuario_id` del registro en `PQ_PARTES_USUARIOS`.
- El sistema verifica que el usuario esté activo y no inhabilitado en `PQ_PARTES_USUARIOS`.
- El sistema obtiene el valor de `supervisor` de `PQ_PARTES_USUARIOS` para determinar `es_supervisor`.
- Si las credenciales son válidas, el sistema genera un token de autenticación (Sanctum) que incluye: `user_id`, `user_code`, `tipo_usuario`, `usuario_id`, `cliente_id` (null), `es_supervisor`.
- El token se almacena en el frontend (localStorage o sessionStorage).
- Los valores de autenticación se conservan durante todo el ciclo del proceso (hasta logout).
- El usuario es redirigido al dashboard principal.
- Si las credenciales son inválidas, se muestra un mensaje de error claro.
- El mensaje de error no revela si el usuario existe o no (seguridad).

**Notas de reglas de negocio:**
- La autenticación se realiza contra la tabla `USERS` (sin prefijo PQ_PARTES_).
- Validar `activo = true` y `inhabilitado = false` en conjunto tanto en `USERS` como en `PQ_PARTES_USUARIOS`.
- El código de usuario debe existir y no ser NULL.
- Un `User.code` solo puede estar asociado a un Cliente O a un Usuario, no a ambos.
- El `code` en `PQ_PARTES_USUARIOS` debe coincidir con el `code` en `USERS`.

**Dependencias:** Ninguna (historia base del flujo E2E).

---

### HU-002 – Login de cliente

**Rol:** Cliente  
**Clasificación:** SHOULD-HAVE  
**Historia:** Como cliente quiero autenticarme en el sistema con mi código y contraseña para consultar las tareas realizadas para mí.

**Criterios de aceptación:**
- El cliente puede ingresar su código y contraseña.
- El sistema valida que el código no esté vacío.
- El sistema valida que la contraseña no esté vacía.
- El sistema valida que el `User` exista en la tabla `USERS` (sin prefijo PQ_PARTES_).
- El sistema valida que el `User` esté activo (`activo = true`) y no inhabilitado (`inhabilitado = false`) en `USERS`.
- El sistema valida que la contraseña coincida con el hash almacenado en `USERS`.
- Después del login exitoso, el sistema busca el `User.code` en `PQ_PARTES_CLIENTES.code`.
- El sistema determina que `tipo_usuario = "cliente"`.
- El sistema obtiene el `cliente_id` del registro en `PQ_PARTES_CLIENTES`.
- El sistema verifica que el cliente esté activo y no inhabilitado en `PQ_PARTES_CLIENTES`.
- El sistema establece `es_supervisor = false` (siempre para clientes).
- Si las credenciales son válidas, el sistema genera un token de autenticación que incluye: `user_id`, `user_code`, `tipo_usuario`, `usuario_id` (null), `cliente_id`, `es_supervisor` (false).
- El token se almacena en el frontend.
- Los valores de autenticación se conservan durante todo el ciclo del proceso (hasta logout).
- El cliente es redirigido a su vista de consulta de tareas.
- Si las credenciales son inválidas, se muestra un mensaje de error claro.

**Notas de reglas de negocio:**
- La autenticación se realiza contra la tabla `USERS` (sin prefijo PQ_PARTES_).
- Solo los clientes con `user_id` configurado (relación con `USERS`) pueden autenticarse.
- Validar `activo = true` y `inhabilitado = false` en conjunto tanto en `USERS` como en `PQ_PARTES_CLIENTES`.
- El `code` en `PQ_PARTES_CLIENTES` debe coincidir con el `code` en `USERS` si tiene `user_id`.

**Dependencias:** HU-001 (comparte lógica de autenticación).

---

### HU-003 – Logout

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

### HU-004 – Recuperación de contraseña

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

### HU-005 – Cambio de contraseña (usuario autenticado)

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

### HU-006 – Visualización de perfil de usuario

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

### HU-007 – Edición de perfil de usuario

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

### HU-008 – Listado de clientes

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

### HU-009 – Creación de cliente

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

### HU-010 – Edición de cliente

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

### HU-011 – Eliminación de cliente

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

### HU-012 – Asignación de tipos de tarea a cliente

**Rol:** Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como supervisor quiero asignar tipos de tarea específicos a un cliente para que solo ese cliente pueda usar esos tipos al registrar tareas.

**Criterios de aceptación:**
- El supervisor puede acceder a la gestión de tipos de tarea de un cliente (desde la edición o detalle del cliente).
- Se muestra una lista de tipos de tarea NO genéricos disponibles.
- El supervisor puede seleccionar múltiples tipos de tarea para asignar al cliente.
- El supervisor puede desasignar tipos de tarea ya asignados.
- El sistema valida que los tipos de tarea existan y estén activos/no inhabilitados.
- Al guardar, el sistema crea o elimina las asociaciones en la tabla `ClienteTipoTarea`.
- Se muestra un mensaje de confirmación.
- Los cambios se reflejan inmediatamente.

**Notas de reglas de negocio:**
- Solo se pueden asignar tipos de tarea NO genéricos (`is_generico = false`).
- Los tipos genéricos están disponibles para todos los clientes automáticamente.
- Esta funcionalidad es necesaria para cumplir la regla: el cliente debe tener al menos un tipo genérico disponible o un tipo asignado.

**Dependencias:** HU-010, HU-020 (tipos de tarea).

---

### HU-013 – Visualización de detalle de cliente

**Rol:** Empleado Supervisor  
**Clasificación:** SHOULD-HAVE  
**Historia:** Como supervisor quiero ver el detalle completo de un cliente incluyendo sus tipos de tarea asignados y estadísticas básicas.

**Criterios de aceptación:**
- El supervisor puede acceder al detalle de un cliente desde el listado.
- Se muestra toda la información del cliente: código, nombre, tipo de cliente, email, estado.
- Se muestra la lista de tipos de tarea asignados (NO genéricos).
- Se muestra la cantidad total de tareas registradas para el cliente (opcional).
- Se muestra la fecha de creación y última actualización (opcional).
- El supervisor puede editar el cliente desde el detalle.
- El supervisor puede eliminar el cliente desde el detalle (si no tiene tareas).

**Notas de reglas de negocio:**
- Mostrar información completa y contextual del cliente.

**Dependencias:** HU-010, HU-012.

---

## Épica 4: Gestión de Tipos de Cliente (ABM)

### HU-014 – Listado de tipos de cliente

**Rol:** Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como supervisor quiero ver el listado de todos los tipos de cliente para gestionarlos.

**Criterios de aceptación:**
- El supervisor puede acceder a la sección "Tipos de Cliente".
- Se muestra una tabla con todos los tipos de cliente.
- La tabla muestra: código, descripción, estado (activo/inactivo), inhabilitado (sí/no).
- Los tipos se listan paginados (si hay muchos).
- Se puede buscar tipos por código o descripción.
- Se puede filtrar por estado (activo/inactivo).
- Se puede filtrar por inhabilitado (sí/no).
- Se muestra el total de tipos de cliente.
- Los tipos inhabilitados se muestran claramente diferenciados.

**Notas de reglas de negocio:**
- Solo los supervisores pueden acceder a esta funcionalidad.

**Dependencias:** HU-001.

---

### HU-015 – Creación de tipo de cliente

**Rol:** Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como supervisor quiero crear un nuevo tipo de cliente para clasificar los clientes del sistema.

**Criterios de aceptación:**
- El supervisor puede acceder al formulario de creación de tipo de cliente.
- El formulario tiene los siguientes campos:
  - Código (obligatorio, único)
  - Descripción (obligatorio)
  - Activo (checkbox, por defecto: true)
  - Inhabilitado (checkbox, por defecto: false)
- El sistema valida que el código no esté vacío.
- El sistema valida que el código sea único.
- El sistema valida que la descripción no esté vacía.
- Al guardar, el sistema crea el tipo de cliente en la base de datos.
- Se muestra un mensaje de confirmación.
- El usuario es redirigido al listado de tipos de cliente o puede crear otro.

**Notas de reglas de negocio:**
- `code` es obligatorio y único.
- `descripcion` es obligatorio.

**Dependencias:** HU-014.

---

### HU-016 – Edición de tipo de cliente

**Rol:** Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como supervisor quiero editar la información de un tipo de cliente existente para mantener actualizados los datos del catálogo.

**Criterios de aceptación:**
- El supervisor puede acceder a la edición de un tipo de cliente desde el listado.
- Se carga el formulario con los datos actuales.
- El código del tipo de cliente no es modificable (solo lectura).
- El supervisor puede modificar: descripción, estado activo, estado inhabilitado.
- El sistema valida que la descripción no esté vacía.
- Al guardar, el sistema actualiza el tipo de cliente en la base de datos.
- Se muestra un mensaje de confirmación.
- Los cambios se reflejan en el listado.

**Notas de reglas de negocio:**
- El `code` no debe ser modificable.

**Dependencias:** HU-015.

---

### HU-017 – Eliminación de tipo de cliente

**Rol:** Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como supervisor quiero eliminar un tipo de cliente que ya no se utiliza para mantener el catálogo limpio.

**Criterios de aceptación:**
- El supervisor puede acceder a la opción de eliminar un tipo de cliente desde el listado o detalle.
- Antes de eliminar, el sistema verifica si el tipo de cliente tiene clientes asociados.
- Si el tipo tiene clientes asociados, se muestra un error y no se permite la eliminación.
- Si el tipo no tiene clientes asociados, se muestra un diálogo de confirmación.
- El diálogo muestra el código y descripción del tipo a eliminar.
- El usuario debe confirmar la eliminación.
- Al confirmar, el sistema elimina el tipo de cliente de la base de datos.
- Se muestra un mensaje de confirmación.
- El tipo desaparece del listado.

**Notas de reglas de negocio:**
- No se puede eliminar un tipo de cliente si tiene clientes asociados (integridad referencial).
- Código de error: 2115.

**Dependencias:** HU-016.

---

## Épica 5: Gestión de Empleados (ABM)

### HU-018 – Listado de empleados

**Rol:** Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como supervisor quiero ver el listado de todos los empleados para gestionarlos.

**Criterios de aceptación:**
- El supervisor puede acceder a la sección "Empleados".
- Se muestra una tabla con todos los usuarios/empleados.
- La tabla muestra: código, nombre, email, supervisor (sí/no), estado (activo/inactivo), inhabilitado (sí/no).
- Los usuarios se listan paginados (si hay muchos).
- Se puede buscar usuarios por código, nombre o email.
- Se puede filtrar por rol supervisor (sí/no).
- Se puede filtrar por estado (activo/inactivo).
- Se puede filtrar por inhabilitado (sí/no).
- Se muestra el total de usuarios.
- Los usuarios inhabilitados se muestran claramente diferenciados.

**Notas de reglas de negocio:**
- Solo los supervisores pueden acceder a esta funcionalidad.
- Se deben listar todos los usuarios, independientemente de su estado.

**Dependencias:** HU-001.

---

### HU-019 – Creación de empleado

**Rol:** Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como supervisor quiero crear un nuevo empleado para que pueda acceder al sistema y registrar tareas.

**Criterios de aceptación:**
- El supervisor puede acceder al formulario de creación de empleado.
- El formulario tiene los siguientes campos:
  - Código (obligatorio, único)
  - Nombre (obligatorio)
  - Email (opcional, único si se proporciona)
  - Contraseña (obligatorio)
  - Confirmar contraseña (obligatorio)
  - Supervisor (checkbox, por defecto: false)
  - Activo (checkbox, por defecto: true)
  - Inhabilitado (checkbox, por defecto: false)
- El sistema valida que el código no esté vacío.
- El sistema valida que el código sea único.
- El sistema valida que el código no exista en `USERS`.
- El sistema valida que el nombre no esté vacío.
- El sistema valida que el email tenga formato válido (si se proporciona).
- El sistema valida que el email sea único (si se proporciona).
- El sistema valida que la contraseña no esté vacía.
- El sistema valida que la contraseña y confirmación coincidan.
- El sistema valida la complejidad de la contraseña (si aplica).
- Al guardar, el sistema crea primero un registro en `USERS` con: `code` (del empleado), `password_hash` (de la contraseña proporcionada), `activo` (del empleado), `inhabilitado` (del empleado).
- Al guardar, el sistema crea el empleado en `PQ_PARTES_USUARIOS` con: `user_id` (FK al `USERS` creado), `code` (debe coincidir con `User.code`), y los demás campos.
- El sistema valida que el `code` del empleado coincida con el `code` del `User` creado.
- Se muestra un mensaje de confirmación.
- El usuario es redirigido al listado de empleados o puede crear otro.

**Notas de reglas de negocio:**
- `code` es obligatorio y único.
- `nombre` es obligatorio.
- La creación de un empleado requiere la creación simultánea de un registro en `USERS`.
- El `user_id` en `PQ_PARTES_USUARIOS` es obligatorio y debe referenciar a `USERS.id`.
- El `code` en `PQ_PARTES_USUARIOS` debe coincidir exactamente con el `code` en `USERS`.
- `password_hash` se genera a partir de la contraseña en texto plano y se almacena en `USERS`.

**Dependencias:** HU-018.

---

### HU-020 – Edición de empleado

**Rol:** Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como supervisor quiero editar la información de un empleado existente para mantener actualizados sus datos.

**Criterios de aceptación:**
- El supervisor puede acceder a la edición de un empleado desde el listado.
- Se carga el formulario con los datos actuales.
- El código del empleado no es modificable (solo lectura).
- El supervisor puede modificar: nombre, email, supervisor, estado activo, estado inhabilitado.
- El supervisor puede cambiar la contraseña (opcional, con campos separados).
- El sistema valida que el nombre no esté vacío.
- El sistema valida que el email tenga formato válido (si se proporciona).
- El sistema valida que el email sea único (si se proporciona y cambió).
- Si se cambia la contraseña, se validan las mismas reglas que en creación.
- Si se cambia la contraseña, el sistema actualiza el `password_hash` en `USERS` (no en `PQ_PARTES_USUARIOS`).
- Si se cambia el estado `activo` o `inhabilitado`, el sistema actualiza ambos: `USERS` y `PQ_PARTES_USUARIOS`.
- El sistema valida que el `code` no se pueda modificar (es identificador único y debe coincidir con `User.code`).
- Al guardar, el sistema actualiza el usuario en la base de datos.
- Se muestra un mensaje de confirmación.
- Los cambios se reflejan en el listado.

**Notas de reglas de negocio:**
- El `code` no debe ser modificable.
- La contraseña se almacena en `USERS`, no en `PQ_PARTES_USUARIOS`.
- Los cambios de estado deben sincronizarse entre `USERS` y `PQ_PARTES_USUARIOS`.
- La contraseña solo se actualiza si se proporciona una nueva.

**Dependencias:** HU-019.

---

### HU-021 – Eliminación de empleado

**Rol:** Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como supervisor quiero eliminar un empleado que ya no trabaja para mantener el catálogo actualizado.

**Criterios de aceptación:**
- El supervisor puede acceder a la opción de eliminar un empleado desde el listado o detalle.
- Antes de eliminar, el sistema verifica si el empleado tiene tareas asociadas.
- Si el empleado tiene tareas asociadas, se muestra un error y no se permite la eliminación.
- Si el empleado no tiene tareas asociadas, se muestra un diálogo de confirmación.
- El diálogo muestra el código y nombre del empleado a eliminar.
- El usuario debe confirmar la eliminación.
- Al confirmar, el sistema elimina el empleado de la base de datos.
- Se muestra un mensaje de confirmación.
- El empleado desaparece del listado.

**Notas de reglas de negocio:**
- No se puede eliminar un empleado si tiene tareas asociadas (integridad referencial).
- Código de error: 2113.

**Dependencias:** HU-020.

---

### HU-022 – Visualización de detalle de empleado

**Rol:** Empleado Supervisor  
**Clasificación:** SHOULD-HAVE  
**Historia:** Como supervisor quiero ver el detalle completo de un empleado incluyendo estadísticas básicas de tareas registradas.

**Criterios de aceptación:**
- El supervisor puede acceder al detalle de un empleado desde el listado.
- Se muestra toda la información del empleado: código, nombre, email, supervisor, estado.
- Se muestra la cantidad total de tareas registradas por el empleado (opcional).
- Se muestra la fecha de creación y última actualización (opcional).
- El supervisor puede editar el empleado desde el detalle.
- El supervisor puede eliminar el empleado desde el detalle (si no tiene tareas).

**Notas de reglas de negocio:**
- Mostrar información completa y contextual del empleado.

**Dependencias:** HU-020.

---

## Épica 6: Gestión de Tipos de Tarea (ABM)

### HU-023 – Listado de tipos de tarea

**Rol:** Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como supervisor quiero ver el listado de todos los tipos de tarea para gestionarlos.

**Criterios de aceptación:**
- El supervisor puede acceder a la sección "Tipos de Tarea".
- Se muestra una tabla con todos los tipos de tarea.
- La tabla muestra: código, descripción, genérico (sí/no), por defecto (sí/no), estado (activo/inactivo), inhabilitado (sí/no).
- Los tipos se listan paginados (si hay muchos).
- Se puede buscar tipos por código o descripción.
- Se puede filtrar por genérico (sí/no).
- Se puede filtrar por por defecto (sí/no).
- Se puede filtrar por estado (activo/inactivo).
- Se puede filtrar por inhabilitado (sí/no).
- Se muestra el total de tipos de tarea.
- Los tipos inhabilitados se muestran claramente diferenciados.
- El tipo por defecto se muestra claramente destacado.

**Notas de reglas de negocio:**
- Solo los supervisores pueden acceder a esta funcionalidad.

**Dependencias:** HU-001.

---

### HU-024 – Creación de tipo de tarea

**Rol:** Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como supervisor quiero crear un nuevo tipo de tarea para clasificar las tareas registradas en el sistema.

**Criterios de aceptación:**
- El supervisor puede acceder al formulario de creación de tipo de tarea.
- El formulario tiene los siguientes campos:
  - Código (obligatorio, único)
  - Descripción (obligatorio)
  - Genérico (checkbox, por defecto: false)
  - Por defecto (checkbox, por defecto: false)
  - Activo (checkbox, por defecto: true)
  - Inhabilitado (checkbox, por defecto: false)
- El sistema valida que el código no esté vacío.
- El sistema valida que el código sea único.
- El sistema valida que la descripción no esté vacía.
- El sistema valida la regla: si `por defecto = true`, entonces `genérico = true` (forzado automáticamente).
- El sistema valida la regla: solo puede haber un tipo de tarea con `por defecto = true` en todo el sistema.
- Si se marca "por defecto" y ya existe otro tipo por defecto, se muestra un error.
- Si se marca "por defecto", el checkbox "genérico" se marca automáticamente y se deshabilita.
- Al guardar, el sistema crea el tipo de tarea en la base de datos.
- Se muestra un mensaje de confirmación.
- El usuario es redirigido al listado de tipos de tarea o puede crear otro.

**Notas de reglas de negocio:**
- `code` es obligatorio y único.
- `descripcion` es obligatorio.
- Regla crítica: solo un tipo puede tener `is_default = true`.
- Si `is_default = true`, entonces `is_generico = true` (forzado).
- Código de error: 2117 (solo puede haber un tipo por defecto).

**Dependencias:** HU-023.

---

### HU-025 – Edición de tipo de tarea

**Rol:** Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como supervisor quiero editar la información de un tipo de tarea existente para mantener actualizados los datos del catálogo.

**Criterios de aceptación:**
- El supervisor puede acceder a la edición de un tipo de tarea desde el listado.
- Se carga el formulario con los datos actuales.
- El código del tipo de tarea no es modificable (solo lectura).
- El supervisor puede modificar: descripción, genérico, por defecto, estado activo, estado inhabilitado.
- El sistema valida que la descripción no esté vacía.
- El sistema valida la regla: si `por defecto = true`, entonces `genérico = true` (forzado automáticamente).
- El sistema valida la regla: solo puede haber un tipo de tarea con `por defecto = true` (verificar que no haya otro distinto al actual).
- Si se marca "por defecto" y ya existe otro tipo por defecto (distinto al actual), se muestra un error.
- Si se marca "por defecto", el checkbox "genérico" se marca automáticamente y se deshabilita.
- Si el tipo actual es "por defecto" y se desmarca, se permite (pero debe quedar al menos un tipo genérico disponible).
- Al guardar, el sistema actualiza el tipo de tarea en la base de datos.
- Se muestra un mensaje de confirmación.
- Los cambios se reflejan en el listado.

**Notas de reglas de negocio:**
- El `code` no debe ser modificable.
- Las mismas validaciones que en creación aplican.

**Dependencias:** HU-024.

---

### HU-026 – Eliminación de tipo de tarea

**Rol:** Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como supervisor quiero eliminar un tipo de tarea que ya no se utiliza para mantener el catálogo limpio.

**Criterios de aceptación:**
- El supervisor puede acceder a la opción de eliminar un tipo de tarea desde el listado o detalle.
- Antes de eliminar, el sistema verifica si el tipo de tarea tiene tareas asociadas o clientes asociados (en `ClienteTipoTarea`).
- Si el tipo tiene tareas asociadas o clientes asociados, se muestra un error y no se permite la eliminación.
- Si el tipo no tiene referencias, se muestra un diálogo de confirmación.
- El diálogo muestra el código y descripción del tipo a eliminar.
- El usuario debe confirmar la eliminación.
- Al confirmar, el sistema elimina el tipo de tarea de la base de datos.
- Se muestra un mensaje de confirmación.
- El tipo desaparece del listado.

**Notas de reglas de negocio:**
- No se puede eliminar un tipo de tarea si tiene tareas asociadas (en `RegistroTarea`).
- No se puede eliminar un tipo de tarea si tiene clientes asociados (en `ClienteTipoTarea`).
- Código de error: 2114.

**Dependencias:** HU-025.

---

### HU-027 – Visualización de detalle de tipo de tarea

**Rol:** Empleado Supervisor  
**Clasificación:** SHOULD-HAVE  
**Historia:** Como supervisor quiero ver el detalle completo de un tipo de tarea incluyendo clientes asociados y estadísticas básicas.

**Criterios de aceptación:**
- El supervisor puede acceder al detalle de un tipo de tarea desde el listado.
- Se muestra toda la información del tipo: código, descripción, genérico, por defecto, estado.
- Si el tipo NO es genérico, se muestra la lista de clientes asociados (desde `ClienteTipoTarea`).
- Se muestra la cantidad total de tareas registradas con este tipo (opcional).
- Se muestra la fecha de creación y última actualización (opcional).
- El supervisor puede editar el tipo desde el detalle.
- El supervisor puede eliminar el tipo desde el detalle (si no tiene referencias).

**Notas de reglas de negocio:**
- Mostrar información completa y contextual del tipo de tarea.

**Dependencias:** HU-025.

---

## Épica 7: Registro de Tareas

### HU-028 – Carga de tarea diaria

**Rol:** Empleado / Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como empleado quiero registrar una tarea realizada indicando fecha, cliente, tipo de tarea, duración y descripción para dejar constancia del trabajo efectuado.

**Criterios de aceptación:**
- El empleado puede acceder al formulario de registro de tarea.
- El formulario tiene los siguientes campos:
  - Fecha (obligatorio, selector de fecha, por defecto: fecha actual)
  - Cliente (obligatorio, selector)
  - Tipo de tarea (obligatorio, selector)
  - Duración (obligatorio, en minutos, con validación de tramos de 15 minutos)
  - Sin cargo (checkbox, por defecto: false)
  - Presencial (checkbox, por defecto: false)
  - Observación/Descripción (obligatorio, textarea)
- Si el usuario es supervisor, puede seleccionar el empleado propietario de la tarea (selector, por defecto: él mismo).
- El selector de clientes solo muestra clientes activos y no inhabilitados.
- El selector de tipos de tarea muestra:
  - Todos los tipos genéricos (`is_generico = true`) activos y no inhabilitados
  - Los tipos NO genéricos asignados al cliente seleccionado (desde `ClienteTipoTarea`) activos y no inhabilitados
- El sistema valida que la fecha no esté vacía.
- El sistema valida que la fecha tenga formato válido (YYYY-MM-DD).
- El sistema muestra una advertencia si la fecha es futura (no bloquea, solo advierte).
- El sistema valida que el cliente esté seleccionado y exista.
- El sistema valida que el cliente esté activo y no inhabilitado.
- El sistema valida que el tipo de tarea esté seleccionado y exista.
- El sistema valida que el tipo de tarea esté activo y no inhabilitado.
- El sistema valida que el tipo de tarea sea genérico o esté asignado al cliente seleccionado.
- El sistema valida que la duración sea mayor a cero.
- El sistema valida que la duración esté en tramos de 15 minutos (15, 30, 45, 60, ..., 1440).
- El sistema valida que la duración no exceda 1440 minutos (24 horas).
- El sistema valida que la observación no esté vacía.
- El sistema valida que `sin_cargo` y `presencial` no sean null (valores por defecto: false).
- Si el usuario es supervisor y selecciona otro empleado, el sistema valida que el empleado exista y esté activo/no inhabilitado.
- Al guardar, el sistema crea el registro de tarea en la base de datos.
- El registro queda asociado al usuario autenticado (o al seleccionado si es supervisor).
- Se muestra un mensaje de confirmación.
- El formulario se limpia o el usuario es redirigido a la lista de tareas.

**Notas de reglas de negocio:**
- La duración debe ser múltiplo de 15 minutos.
- La fecha futura genera advertencia pero no bloquea.
- `observacion` es obligatorio (no opcional).
- Los selectores solo muestran registros activos y no inhabilitados.
- Regla de visibilidad de tipos de tarea: genéricos + asignados al cliente.

**Dependencias:** HU-001 (autenticación), HU-009 (clientes), HU-024 (tipos de tarea), HU-012 (asignación de tipos a clientes).

---

### HU-029 – Edición de tarea propia

**Rol:** Empleado  
**Clasificación:** MUST-HAVE  
**Historia:** Como empleado quiero editar una tarea que registré para corregir errores de carga.

**Criterios de aceptación:**
- El empleado puede acceder a la edición de una tarea desde la lista de sus tareas.
- Solo puede editar tareas propias (donde `usuario_id` coincide con el usuario autenticado).
- El sistema valida que la tarea no esté cerrada (`cerrado = false`).
- Si la tarea está cerrada, se muestra un error y no se permite la edición.
- Se carga el formulario con los datos actuales de la tarea.
- El empleado puede modificar todos los campos: fecha, cliente, tipo de tarea, duración, sin cargo, presencial, observación.
- Se aplican las mismas validaciones que en la creación.
- El `usuario_id` no es modificable (solo lectura, muestra el nombre del empleado).
- Al guardar, el sistema actualiza el registro de tarea en la base de datos.
- Se muestra un mensaje de confirmación.
- Los cambios se reflejan en la lista de tareas.

**Notas de reglas de negocio:**
- Solo el autor puede editar su tarea.
- Una tarea cerrada no se puede modificar.
- Código de error: 2110 (no se puede modificar una tarea cerrada).

**Dependencias:** HU-028.

---

### HU-030 – Eliminación de tarea propia

**Rol:** Empleado  
**Clasificación:** MUST-HAVE  
**Historia:** Como empleado quiero eliminar una tarea que registré incorrectamente para mantener la precisión de los registros.

**Criterios de aceptación:**
- El empleado puede acceder a la opción de eliminar una tarea desde la lista de sus tareas.
- Solo puede eliminar tareas propias (donde `usuario_id` coincide con el usuario autenticado).
- El sistema valida que la tarea no esté cerrada (`cerrado = false`).
- Si la tarea está cerrada, se muestra un error y no se permite la eliminación.
- Se muestra un diálogo de confirmación.
- El diálogo muestra información de la tarea a eliminar (fecha, cliente, tipo, duración).
- El usuario debe confirmar la eliminación.
- Al confirmar, el sistema elimina el registro de tarea de la base de datos.
- Se muestra un mensaje de confirmación.
- La tarea desaparece de la lista.

**Notas de reglas de negocio:**
- Solo el autor puede eliminar su tarea.
- Una tarea cerrada no se puede eliminar.
- Código de error: 2111 (no se puede eliminar una tarea cerrada).

**Dependencias:** HU-028.

---

### HU-031 – Edición de tarea (supervisor)

**Rol:** Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como supervisor quiero editar cualquier tarea del sistema para corregir errores o ajustar información.

**Criterios de aceptación:**
- El supervisor puede acceder a la edición de cualquier tarea desde la lista de tareas.
- El sistema valida que la tarea no esté cerrada (`cerrado = false`).
- Si la tarea está cerrada, se muestra un error y no se permite la edición.
- Se carga el formulario con los datos actuales de la tarea.
- El supervisor puede modificar todos los campos: fecha, cliente, tipo de tarea, duración, sin cargo, presencial, observación.
- El supervisor puede cambiar el empleado propietario de la tarea (selector de usuarios).
- Se aplican las mismas validaciones que en la creación.
- Al guardar, el sistema actualiza el registro de tarea en la base de datos.
- Se muestra un mensaje de confirmación.
- Los cambios se reflejan en la lista de tareas.

**Notas de reglas de negocio:**
- El supervisor puede editar tareas de cualquier usuario.
- Una tarea cerrada no se puede modificar.
- Código de error: 2110.

**Dependencias:** HU-028, HU-019 (gestión de usuarios para selector).

---

### HU-032 – Eliminación de tarea (supervisor)

**Rol:** Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como supervisor quiero eliminar cualquier tarea del sistema para mantener la precisión de los registros.

**Criterios de aceptación:**
- El supervisor puede acceder a la opción de eliminar cualquier tarea desde la lista de tareas.
- El sistema valida que la tarea no esté cerrada (`cerrado = false`).
- Si la tarea está cerrada, se muestra un error y no se permite la eliminación.
- Se muestra un diálogo de confirmación.
- El diálogo muestra información de la tarea a eliminar (fecha, cliente, tipo, duración, empleado).
- El usuario debe confirmar la eliminación.
- Al confirmar, el sistema elimina el registro de tarea de la base de datos.
- Se muestra un mensaje de confirmación.
- La tarea desaparece de la lista.

**Notas de reglas de negocio:**
- El supervisor puede eliminar tareas de cualquier usuario.
- Una tarea cerrada no se puede eliminar.
- Código de error: 2111.

**Dependencias:** HU-028.

---

### HU-033 – Visualización de lista de tareas propias

**Rol:** Empleado  
**Clasificación:** MUST-HAVE  
**Historia:** Como empleado quiero ver la lista de mis tareas registradas para controlar lo que cargué y poder editarlas o eliminarlas.

**Criterios de aceptación:**
- El empleado puede acceder a la sección "Mis Tareas".
- Se muestra una tabla con todas las tareas del usuario autenticado.
- La tabla muestra: fecha, cliente, tipo de tarea, duración (en minutos y horas), sin cargo, presencial, observación (truncada), cerrado (sí/no), acciones (editar, eliminar).
- Las tareas se listan paginadas.
- Se puede filtrar por rango de fechas (fecha desde, fecha hasta).
- Se puede filtrar por cliente.
- Se puede filtrar por tipo de tarea.
- Se puede buscar por texto en la observación.
- Se puede ordenar por fecha (ascendente/descendente).
- Se muestra el total de tareas y el total de horas del período filtrado.
- Las tareas cerradas se muestran claramente diferenciadas (opcional: con indicador visual).
- Las acciones de editar/eliminar están deshabilitadas para tareas cerradas.

**Notas de reglas de negocio:**
- Solo se muestran tareas del usuario autenticado.
- Las tareas cerradas no se pueden editar ni eliminar.

**Dependencias:** HU-028.

---

### HU-034 – Visualización de lista de todas las tareas (supervisor)

**Rol:** Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como supervisor quiero ver la lista de todas las tareas de todos los usuarios para supervisar el trabajo realizado.

**Criterios de aceptación:**
- El supervisor puede acceder a la sección "Todas las Tareas" o "Supervisión".
- Se muestra una tabla con todas las tareas de todos los usuarios.
- La tabla muestra: fecha, empleado, cliente, tipo de tarea, duración (en minutos y horas), sin cargo, presencial, observación (truncada), cerrado (sí/no), acciones (editar, eliminar).
- Las tareas se listan paginadas.
- Se puede filtrar por rango de fechas (fecha desde, fecha hasta).
- Se puede filtrar por empleado.
- Se puede filtrar por cliente.
- Se puede filtrar por tipo de tarea.
- Se puede buscar por texto en la observación.
- Se puede ordenar por fecha, empleado, cliente (ascendente/descendente).
- Se muestra el total de tareas y el total de horas del período filtrado.
- Las tareas cerradas se muestran claramente diferenciadas.
- Las acciones de editar/eliminar están deshabilitadas para tareas cerradas.

**Notas de reglas de negocio:**
- Se muestran todas las tareas de todos los usuarios.
- El supervisor puede editar y eliminar cualquier tarea (excepto cerradas).

**Dependencias:** HU-028, HU-031, HU-032.

---

### HU-035 – Validación de duración en tramos de 15 minutos

**Rol:** Empleado / Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como empleado quiero que el sistema valide que la duración de las tareas esté en tramos de 15 minutos para mantener la consistencia en el registro de tiempo.

**Criterios de aceptación:**
- Al ingresar la duración en el formulario, el sistema valida que sea múltiplo de 15.
- Si se ingresa un valor que no es múltiplo de 15, se muestra un mensaje de error claro.
- El mensaje indica: "La duración debe estar en tramos de 15 minutos (15, 30, 45, 60, ...)".
- El sistema no permite guardar la tarea si la duración no es válida.
- El campo de duración puede tener un selector con valores predefinidos (15, 30, 45, 60, 75, 90, ..., 1440) o validar el input manual.
- Si es input manual, se puede redondear automáticamente al tramo más cercano (opcional, según diseño UX).

**Notas de reglas de negocio:**
- Duración válida: `duracion_minutos % 15 === 0` y `0 < duracion_minutos <= 1440`.
- Código de error: 1210.

**Dependencias:** HU-028.

---

### HU-036 – Advertencia de fecha futura

**Rol:** Empleado / Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como empleado quiero que el sistema me advierta si registro una tarea con fecha futura para evitar errores, pero sin bloquear la acción si es intencional.

**Criterios de aceptación:**
- Al seleccionar una fecha futura en el formulario, el sistema muestra una advertencia visual.
- La advertencia indica: "La fecha seleccionada es futura. ¿Está seguro de que desea continuar?"
- El usuario puede continuar con la acción (no se bloquea).
- La advertencia es clara pero no impide el guardado.
- Opcionalmente, se puede mostrar un checkbox de confirmación "Confirmo que la fecha es correcta".

**Notas de reglas de negocio:**
- La fecha futura genera advertencia pero no bloquea la creación/edición.
- Esta es una validación de advertencia, no de bloqueo.

**Dependencias:** HU-028.

---

### HU-037 – Filtrado de tipos de tarea por cliente

**Rol:** Empleado / Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como empleado quiero que el selector de tipos de tarea muestre solo los tipos disponibles para el cliente seleccionado para evitar selecciones incorrectas.

**Criterios de aceptación:**
- Al seleccionar un cliente en el formulario, el selector de tipos de tarea se actualiza dinámicamente.
- El selector muestra:
  - Todos los tipos genéricos (`is_generico = true`) activos y no inhabilitados
  - Los tipos NO genéricos asignados al cliente seleccionado (desde `ClienteTipoTarea`) activos y no inhabilitados
- Si no hay tipos disponibles para el cliente seleccionado, se muestra un mensaje informativo.
- Si se cambia el cliente, el tipo de tarea seleccionado se limpia si ya no está disponible para el nuevo cliente.
- El sistema valida al guardar que el tipo de tarea seleccionado sea válido para el cliente.

**Notas de reglas de negocio:**
- Regla de visibilidad: genéricos + asignados al cliente.
- Esta funcionalidad es crítica para cumplir la regla de negocio de tipos de tarea.

**Dependencias:** HU-028, HU-012 (asignación de tipos a clientes).

---

### HU-038 – Selección de empleado propietario (supervisor)

**Rol:** Empleado Supervisor  
**Clasificación:** MUST-HAVE  
**Historia:** Como supervisor quiero seleccionar el empleado propietario de una tarea al crearla para poder registrar tareas en nombre de otros empleados.

**Criterios de aceptación:**
- En el formulario de creación de tarea, si el usuario es supervisor, aparece un selector de "Empleado".
- El selector muestra todos los empleados activos y no inhabilitados.
- Por defecto, el selector muestra al supervisor mismo.
- El supervisor puede cambiar la selección a otro empleado.
- El sistema valida que el empleado seleccionado exista y esté activo/no inhabilitado.
- Al guardar, la tarea queda asociada al empleado seleccionado (no al supervisor).
- En la lista de tareas, se muestra el empleado propietario de cada tarea.

**Notas de reglas de negocio:**
- Solo los supervisores pueden seleccionar otro empleado.
- Los empleados normales siempre registran tareas como propias.

**Dependencias:** HU-028, HU-019 (gestión de usuarios).

---

## Épica 8: Proceso Masivo de Tareas

### HU-039 – Acceso al proceso masivo de tareas

**Rol:** Empleado Supervisor  
**Clasificación:** SHOULD-HAVE  
**Historia:** Como supervisor quiero acceder a la funcionalidad de proceso masivo de tareas para gestionar eficientemente el estado de múltiples tareas.

**Criterios de aceptación:**
- El supervisor puede acceder a la sección "Proceso Masivo" desde el menú principal.
- Solo los usuarios con `supervisor = true` pueden acceder a esta funcionalidad.
- Si un usuario normal intenta acceder, se muestra un error 403 o redirección.
- La página muestra los filtros y la tabla de tareas.
- Se muestra un mensaje claro si el usuario no tiene permisos.

**Notas de reglas de negocio:**
- Validación de permisos: solo supervisores (`supervisor = true`).
- Código de error: 403 si usuario no es supervisor.

**Dependencias:** HU-001 (autenticación).

---

### HU-040 – Filtrado de tareas para proceso masivo

**Rol:** Empleado Supervisor  
**Clasificación:** SHOULD-HAVE  
**Historia:** Como supervisor quiero aplicar filtros complejos para seleccionar las tareas que deseo procesar masivamente.

**Criterios de aceptación:**
- El supervisor puede filtrar por rango de fechas (fecha desde, fecha hasta).
- El supervisor puede filtrar por cliente (todos o cliente específico).
- El supervisor puede filtrar por empleado (todos o empleado específico).
- El supervisor puede filtrar por estado (Cerrados / Abiertos).
- El sistema valida que `fecha_desde <= fecha_hasta`.
- Al hacer clic en "Aplicar Filtros", se cargan las tareas que cumplen los criterios.
- Los filtros se mantienen al recargar la página (opcional, según UX).
- Se muestra el total de tareas filtradas.

**Notas de reglas de negocio:**
- Validación de rango de fechas: `fecha_desde <= fecha_hasta` (código de error: 1305).
- Los filtros se aplican en conjunto (AND lógico).

**Dependencias:** HU-039.

---

### HU-041 – Selección múltiple de tareas

**Rol:** Empleado Supervisor  
**Clasificación:** SHOULD-HAVE  
**Historia:** Como supervisor quiero seleccionar múltiples tareas de la lista para procesarlas en conjunto.

**Criterios de aceptación:**
- Cada fila de la tabla tiene un checkbox para selección.
- El supervisor puede seleccionar tareas individuales haciendo clic en los checkboxes.
- El supervisor puede usar "Seleccionar todos" para marcar todas las tareas visibles.
- El supervisor puede usar "Deseleccionar todos" para desmarcar todas las tareas.
- Se muestra un contador de tareas seleccionadas (ej: "5 tareas seleccionadas").
- Los checkboxes se actualizan correctamente al seleccionar/deseleccionar.
- Las tareas cerradas pueden seleccionarse (pero el procesamiento puede tener reglas específicas).

**Notas de reglas de negocio:**
- La selección es independiente del estado de la tarea (cerrado/abierto).
- El contador debe actualizarse en tiempo real.

**Dependencias:** HU-040.

---

### HU-042 – Procesamiento masivo de tareas (cerrar/reabrir)

**Rol:** Empleado Supervisor  
**Clasificación:** SHOULD-HAVE  
**Historia:** Como supervisor quiero procesar masivamente las tareas seleccionadas para cambiar su estado (cerrar o reabrir) de forma eficiente.

**Criterios de aceptación:**
- El botón "Procesar" está deshabilitado si no hay tareas seleccionadas.
- El botón "Procesar" se habilita cuando hay al menos una tarea seleccionada.
- Al hacer clic en "Procesar", el sistema invierte el estado de las tareas seleccionadas:
  - Si la tarea está cerrada (`cerrado = true`), se cambia a abierta (`cerrado = false`).
  - Si la tarea está abierta (`cerrado = false`), se cambia a cerrada (`cerrado = true`).
- Se muestra un diálogo de confirmación antes de procesar (opcional, según UX).
- El diálogo muestra la cantidad de tareas a procesar.
- Durante el procesamiento, se muestra un indicador de carga.
- El sistema procesa todas las tareas seleccionadas en una sola operación.
- Al finalizar, se muestra un mensaje de éxito: "Se procesaron X registros".
- La lista se actualiza automáticamente con los nuevos estados.
- Si hay un error, se muestra un mensaje de error y las tareas mantienen su estado anterior.

**Notas de reglas de negocio:**
- El procesamiento invierte el estado `cerrado` de las tareas.
- El botón debe validar que haya al menos una tarea seleccionada.
- El procesamiento debe ser atómico (todas o ninguna).

**Dependencias:** HU-041.

---

### HU-043 – Validación de selección para procesamiento

**Rol:** Empleado Supervisor  
**Clasificación:** SHOULD-HAVE  
**Historia:** Como supervisor quiero que el sistema valide que haya tareas seleccionadas antes de procesar para evitar errores.

**Criterios de aceptación:**
- Si no hay tareas seleccionadas y el supervisor intenta procesar, se muestra un mensaje: "Debe seleccionar al menos una tarea".
- El botón "Procesar" está visualmente deshabilitado cuando no hay selección.
- El mensaje de error es claro y visible.
- El sistema no realiza ninguna operación si no hay selección.

**Notas de reglas de negocio:**
- Validación en frontend (UX) y backend (seguridad).
- El botón debe estar deshabilitado cuando `selectedTasks.length === 0`.

**Dependencias:** HU-042.

---

## Épica 9: Informes y Consultas

### HU-044 – Consulta detallada de tareas

**Rol:** Empleado / Empleado Supervisor / Cliente  
**Clasificación:** MUST-HAVE  
**Historia:** Como usuario quiero consultar un listado detallado de tareas con filtros para analizar el trabajo realizado.

**Criterios de aceptación:**
- El usuario puede acceder a la sección "Consulta Detallada" o "Detalle de Tareas".
- Se muestra una tabla con todas las tareas según los permisos del usuario:
  - **Empleado (NO supervisor):** Solo sus propias tareas (donde `usuario_id` coincide con su `usuario_id`).
  - **Supervisor:** Todas las tareas de todos los usuarios.
  - **Cliente:** Solo tareas donde es el cliente (donde `cliente_id` coincide con su `cliente_id`).
- La tabla muestra: empleado (si supervisor), cliente, fecha, tipo de tarea, horas (decimal), sin cargo, presencial, descripción.
- El usuario puede aplicar filtros:
  - Período (fecha desde, fecha hasta)
  - Tipo de cliente (todos o específico, solo para supervisor)
  - Cliente (todos o específico, filtrado automático para cliente)
  - Empleado (todos o específico, solo para supervisor, filtrado automático para empleado normal)
- Los filtros se aplican con botón "Aplicar Filtros".
- La tabla se actualiza con los resultados filtrados.
- Se muestra el total de horas del período filtrado.
- Se puede ordenar por columnas (fecha, cliente, empleado, etc.).
- Se puede paginar si hay muchos resultados.

**Notas de reglas de negocio:**
- Filtros automáticos según tipo de usuario (ver reglas de negocio 8.2).
- Validación de período: `fecha_desde <= fecha_hasta` (1305).
- Formato de horas: decimal (minutos / 60).

**Dependencias:** HU-001 (autenticación), HU-033, HU-034.

---

### HU-045 – Consulta agrupada por empleado

**Rol:** Empleado Supervisor  
**Clasificación:** SHOULD-HAVE  
**Historia:** Como supervisor quiero consultar tareas agrupadas por empleado para analizar la dedicación de cada empleado.

**Criterios de aceptación:**
- El supervisor puede acceder a la sección "Tareas por Empleado".
- Se muestran los mismos filtros que en consulta detallada.
- Los resultados se agrupan por empleado.
- Cada grupo muestra:
  - Nombre del empleado
  - Total de horas en formato decimal
  - Cantidad de tareas
- Cada grupo es expandible (accordion o similar).
- Al expandir un grupo, se muestra el detalle de todas las tareas de ese empleado.
- El detalle muestra las mismas columnas que la consulta detallada.
- Se puede colapsar el grupo para ocultar el detalle.
- Se muestra el total general de horas y tareas.

**Notas de reglas de negocio:**
- Agrupación por `usuario_id`.
- Totalización de horas en formato decimal.
- Los filtros aplican a todas las tareas antes de agrupar.

**Dependencias:** HU-044.

---

### HU-046 – Consulta agrupada por cliente

**Rol:** Empleado / Empleado Supervisor / Cliente  
**Clasificación:** MUST-HAVE  
**Historia:** Como usuario quiero consultar tareas agrupadas por cliente para analizar la dedicación a cada cliente.

**Criterios de aceptación:**
- El usuario puede acceder a la sección "Tareas por Cliente" o "Resumen por Cliente".
- Se muestran filtros de período (fecha desde, fecha hasta).
- Los resultados se agrupan por cliente.
- Cada grupo muestra:
  - Nombre del cliente
  - Tipo de cliente (opcional)
  - Total de horas en formato decimal
  - Cantidad de tareas
- Cada grupo es expandible (accordion o similar).
- Al expandir un grupo, se muestra el detalle de todas las tareas de ese cliente.
- El detalle muestra: fecha, tipo de tarea, horas, empleado (si supervisor), descripción.
- Se puede colapsar el grupo para ocultar el detalle.
- Se muestra el total general de horas y tareas.
- Los grupos se ordenan por total de horas (mayor a menor).

**Notas de reglas de negocio:**
- Agrupación por `cliente_id`.
- Totalización de horas en formato decimal.
- Ordenamiento por dedicación total descendente.
- **Filtros automáticos según rol:**
  - **Cliente:** Solo tareas donde `cliente_id` coincide con su `cliente_id` (filtro automático).
  - **Empleado (NO supervisor):** Solo tareas donde `usuario_id` coincide con su `usuario_id` (filtro automático).
  - **Supervisor:** Todas las tareas (sin filtro automático).

**Dependencias:** HU-044.

---

### HU-047 – Consulta agrupada por tipo de tarea

**Rol:** Empleado Supervisor  
**Clasificación:** SHOULD-HAVE  
**Historia:** Como supervisor quiero consultar tareas agrupadas por tipo de tarea para analizar la distribución del trabajo por tipo.

**Criterios de aceptación:**
- El supervisor puede acceder a la sección "Tareas por Tipo".
- Se muestran los mismos filtros que en consulta detallada.
- Los resultados se agrupan por tipo de tarea.
- Cada grupo muestra:
  - Descripción del tipo de tarea
  - Total de horas en formato decimal
  - Cantidad de tareas
- Cada grupo es expandible.
- Al expandir un grupo, se muestra el detalle de todas las tareas de ese tipo.
- El detalle muestra las mismas columnas que la consulta detallada.
- Se puede colapsar el grupo.
- Se muestra el total general.

**Notas de reglas de negocio:**
- Agrupación por `tipo_tarea_id`.
- Totalización de horas en formato decimal.

**Dependencias:** HU-044.

---

### HU-048 – Consulta agrupada por fecha

**Rol:** Empleado / Empleado Supervisor / Cliente  
**Clasificación:** SHOULD-HAVE  
**Historia:** Como usuario quiero consultar tareas agrupadas por fecha para analizar la distribución del trabajo en el tiempo.

**Criterios de aceptación:**
- El usuario puede acceder a la sección "Tareas por Fecha".
- Se muestran filtros de período (fecha desde, fecha hasta).
- Los resultados se filtran automáticamente según los permisos del usuario:
  - **Empleado (NO supervisor):** Solo sus propias tareas (donde `usuario_id` coincide con su `usuario_id`).
  - **Supervisor:** Todas las tareas de todos los usuarios.
  - **Cliente:** Solo tareas donde es el cliente (donde `cliente_id` coincide con su `cliente_id`).
- Los resultados se agrupan por fecha.
- Cada grupo muestra:
  - Fecha (formato legible)
  - Total de horas en formato decimal
  - Cantidad de tareas
- Cada grupo es expandible.
- Al expandir un grupo, se muestra el detalle de todas las tareas de esa fecha (según permisos del usuario).
- El detalle muestra las mismas columnas que la consulta detallada.
- Se puede colapsar el grupo.
- Se muestra el total general.
- Las fechas se ordenan cronológicamente (más reciente primero o más antigua primero, según diseño).

**Notas de reglas de negocio:**
- Agrupación por `fecha`.
- Totalización de horas en formato decimal.
- Ordenamiento cronológico.
- **Filtros automáticos según rol:**
  - **Cliente:** Solo tareas donde `cliente_id` coincide con su `cliente_id`.
  - **Empleado (NO supervisor):** Solo tareas donde `usuario_id` coincide con su `usuario_id`.
  - **Supervisor:** Todas las tareas.

**Dependencias:** HU-044.

---

### HU-049 – Exportación de consultas a Excel

**Rol:** Empleado / Empleado Supervisor / Cliente  
**Clasificación:** SHOULD-HAVE  
**Historia:** Como usuario quiero exportar los resultados de las consultas a Excel para analizar los datos fuera del sistema o compartirlos.

**Criterios de aceptación:**
- El usuario puede hacer clic en un botón "Exportar a Excel" en cualquier consulta.
- El botón está habilitado solo si hay resultados para exportar.
- Si no hay resultados, se muestra un mensaje: "No hay datos para exportar" y el botón está deshabilitado.
- Los datos exportados respetan los permisos del usuario:
  - **Empleado (NO supervisor):** Solo sus propias tareas (donde `usuario_id` coincide con su `usuario_id`).
  - **Supervisor:** Todas las tareas de todos los usuarios.
  - **Cliente:** Solo tareas donde es el cliente (donde `cliente_id` coincide con su `cliente_id`).
- Al hacer clic, se genera un archivo Excel con los datos de la consulta (filtrados según permisos).
- El archivo se descarga automáticamente.
- El nombre del archivo es descriptivo (ej: "Tareas_2025-01-01_2025-01-31.xlsx").
- El archivo contiene:
  - Para consulta detallada: todas las columnas de la tabla
  - Para consulta agrupada: estructura de agrupación con totales y detalles expandidos
- Las horas se muestran en formato decimal en el Excel.
- Las fechas se formatean correctamente en el Excel.
- El formato del archivo es compatible con Excel (XLSX).

**Notas de reglas de negocio:**
- El botón debe estar deshabilitado si `resultados.length === 0`.
- El formato de horas debe ser decimal en el Excel.
- El nombre del archivo debe incluir información del período o filtros aplicados.
- **Los datos exportados deben respetar los mismos filtros automáticos que las consultas en pantalla.**

**Dependencias:** HU-044, HU-045, HU-046, HU-047, HU-048.

---

### HU-050 – Manejo de resultados vacíos en consultas

**Rol:** Empleado / Empleado Supervisor / Cliente  
**Clasificación:** MUST-HAVE  
**Historia:** Como usuario quiero recibir un mensaje claro cuando no hay resultados para los filtros aplicados para entender que la consulta funcionó pero no hay datos.

**Criterios de aceptación:**
- Si una consulta no devuelve resultados (después de aplicar filtros automáticos según rol), se muestra un mensaje informativo: "No se encontraron tareas para los filtros seleccionados".
- No se muestra una tabla vacía.
- El botón de exportar a Excel está deshabilitado.
- El mensaje es claro y sugiere ajustar los filtros.
- El mensaje se muestra en lugar de la tabla de resultados.
- Los filtros automáticos según rol se aplican antes de verificar si hay resultados:
  - **Cliente:** Solo tareas donde `cliente_id` coincide con su `cliente_id`.
  - **Empleado (NO supervisor):** Solo tareas donde `usuario_id` coincide con su `usuario_id`.
  - **Supervisor:** Todas las tareas.

**Notas de reglas de negocio:**
- Validación en backend: si `resultados.isEmpty()` después de aplicar filtros automáticos, retornar mensaje informativo.
- No mostrar lista vacía ni habilitar exportación.
- Los filtros automáticos según rol son obligatorios y se aplican siempre.

**Dependencias:** HU-044.

---

## Épica 10: Dashboard

### HU-051 – Dashboard principal

**Rol:** Empleado / Empleado Supervisor / Cliente  
**Clasificación:** MUST-HAVE  
**Historia:** Como usuario quiero ver un dashboard con resumen ejecutivo del sistema para tener una visión general rápida de mi actividad.

**Criterios de aceptación:**
- El usuario puede acceder al dashboard desde el menú principal o como página de inicio post-login.
- El dashboard muestra información según el rol del usuario:
  - **Empleado (NO supervisor):** Resumen de sus propias tareas (donde `usuario_id` coincide con su `usuario_id`) - total de horas del mes, cantidad de tareas, top clientes.
  - **Supervisor:** Resumen de todas las tareas de todos los empleados - total de horas del mes, cantidad de tareas, top clientes, top empleados.
  - **Cliente:** Resumen de tareas recibidas (donde `cliente_id` coincide con su `cliente_id`) - total de horas del mes, cantidad de tareas, distribución por tipo.
- Se muestra un período por defecto (mes actual o último mes).
- El usuario puede cambiar el período (selector de mes o rango de fechas).
- Los datos se actualizan automáticamente al cambiar el período.
- Se muestran indicadores clave (KPIs):
  - Total de horas del período
  - Cantidad de tareas del período
  - Promedio de horas por día (opcional)
- Se muestran gráficos o visualizaciones básicas (opcional, según diseño):
  - Distribución de horas por cliente (gráfico de barras o pie)
  - Evolución de horas en el tiempo (gráfico de línea, opcional)
- El dashboard es responsive y se adapta a diferentes tamaños de pantalla.

**Notas de reglas de negocio:**
- **Filtros automáticos según rol (obligatorios):**
  - **Cliente:** Solo tareas donde `cliente_id` coincide con su `cliente_id`.
  - **Empleado (NO supervisor):** Solo tareas donde `usuario_id` coincide con su `usuario_id`.
  - **Supervisor:** Todas las tareas (sin filtro automático).
- El período por defecto es el mes actual.
- Los KPIs se calculan en tiempo real desde la base de datos aplicando los filtros automáticos según rol.

**Dependencias:** HU-001 (autenticación), HU-044 (consultas base).

---

### HU-052 – Resumen de dedicación por cliente en dashboard

**Rol:** Empleado / Empleado Supervisor / Cliente  
**Clasificación:** MUST-HAVE  
**Historia:** Como usuario quiero ver un resumen de dedicación por cliente en el dashboard para identificar rápidamente los clientes con mayor dedicación.

**Criterios de aceptación:**
- El dashboard muestra una sección "Dedicación por Cliente".
- Los datos se filtran automáticamente según los permisos del usuario:
  - **Empleado (NO supervisor):** Solo sus propias tareas (donde `usuario_id` coincide con su `usuario_id`).
  - **Supervisor:** Todas las tareas de todos los usuarios.
  - **Cliente:** Solo tareas donde es el cliente (donde `cliente_id` coincide con su `cliente_id`).
- Se muestra una lista o tabla con los clientes y sus totales de horas del período (según permisos).
- Se muestran los top N clientes (ej: top 5 o top 10, según diseño).
- Cada cliente muestra:
  - Nombre del cliente
  - Total de horas en formato decimal
  - Cantidad de tareas
  - Porcentaje del total (opcional)
- Los clientes se ordenan por total de horas (mayor a menor).
- El usuario puede hacer clic en un cliente para ver el detalle (redirección a consulta por cliente).
- Se muestra un total general de horas (calculado según permisos del usuario).

**Notas de reglas de negocio:**
- Agrupación por `cliente_id`.
- Ordenamiento por dedicación descendente.
- Límite de top N para mantener el dashboard simple.
- **Filtros automáticos según rol (obligatorios):**
  - **Cliente:** Solo tareas donde `cliente_id` coincide con su `cliente_id`.
  - **Empleado (NO supervisor):** Solo tareas donde `usuario_id` coincide con su `usuario_id`.
  - **Supervisor:** Todas las tareas.

**Dependencias:** HU-051.

---

### HU-053 – Resumen de dedicación por empleado en dashboard (supervisor)

**Rol:** Empleado Supervisor  
**Clasificación:** SHOULD-HAVE  
**Historia:** Como supervisor quiero ver un resumen de dedicación por empleado en el dashboard para identificar rápidamente la carga de trabajo de cada empleado.

**Criterios de aceptación:**
- El dashboard del supervisor muestra una sección "Dedicación por Empleado".
- Se muestra una lista o tabla con los empleados y sus totales de horas del período.
- Se muestran los top N empleados (ej: top 5 o top 10).
- Cada empleado muestra:
  - Nombre del empleado
  - Total de horas en formato decimal
  - Cantidad de tareas
  - Porcentaje del total (opcional)
- Los empleados se ordenan por total de horas (mayor a menor).
- El supervisor puede hacer clic en un empleado para ver el detalle (redirección a consulta por empleado).
- Se muestra un total general de horas.

**Notas de reglas de negocio:**
- Agrupación por `usuario_id`.
- Ordenamiento por dedicación descendente.
- Solo visible para supervisores.

**Dependencias:** HU-051.

---

### HU-054 – Gráficos y visualizaciones en dashboard

**Rol:** Empleado / Empleado Supervisor / Cliente  
**Clasificación:** SHOULD-HAVE  
**Historia:** Como usuario quiero ver gráficos y visualizaciones en el dashboard para entender mejor la distribución de la dedicación.

**Criterios de aceptación:**
- El dashboard muestra gráficos según el rol:
  - **Empleado (NO supervisor):** Gráfico de distribución de horas por cliente (barras o pie) - solo de sus propias tareas (donde `usuario_id` coincide con su `usuario_id`).
  - **Supervisor:** Gráfico de distribución de horas por cliente y por empleado - todas las tareas de todos los empleados.
  - **Cliente:** Gráfico de distribución de horas por tipo de tarea - solo tareas donde es el cliente (donde `cliente_id` coincide con su `cliente_id`).
- Los datos de los gráficos se filtran automáticamente según los permisos del usuario.
- Los gráficos se actualizan al cambiar el período.
- Los gráficos son interactivos (opcional: tooltips, clics para filtrar).
- Los gráficos son responsive y se adaptan al tamaño de pantalla.
- Se usa una librería de gráficos estándar (Chart.js, Recharts, etc.).
- Los colores son consistentes y accesibles.

**Notas de reglas de negocio:**
- Los gráficos se generan a partir de los mismos datos que las consultas (con filtros automáticos según rol).
- **Filtros automáticos según rol (obligatorios):**
  - **Cliente:** Solo tareas donde `cliente_id` coincide con su `cliente_id`.
  - **Empleado (NO supervisor):** Solo tareas donde `usuario_id` coincide con su `usuario_id`.
  - **Supervisor:** Todas las tareas.
- Los gráficos deben ser accesibles (textos alternativos, contraste).

**Dependencias:** HU-051, HU-052.

---

### HU-055 – Actualización automática del dashboard

**Rol:** Empleado / Empleado Supervisor / Cliente  
**Clasificación:** SHOULD-HAVE  
**Historia:** Como usuario quiero que el dashboard se actualice automáticamente para ver información siempre actualizada.

**Criterios de aceptación:**
- El dashboard se actualiza automáticamente cada X minutos (ej: 5 minutos, configurable).
- Se muestra un indicador de última actualización (ej: "Actualizado hace 2 minutos").
- El usuario puede actualizar manualmente con un botón "Actualizar".
- Durante la actualización, se muestra un indicador de carga.
- Los datos se refrescan sin recargar toda la página (AJAX/fetch).
- Los datos actualizados respetan los filtros automáticos según rol:
  - **Cliente:** Solo tareas donde `cliente_id` coincide con su `cliente_id`.
  - **Empleado (NO supervisor):** Solo tareas donde `usuario_id` coincide con su `usuario_id`.
  - **Supervisor:** Todas las tareas.

**Notas de reglas de negocio:**
- La actualización automática es opcional y puede deshabilitarse (según UX).
- El intervalo de actualización debe ser configurable.
- Los filtros automáticos según rol se aplican siempre, incluso en actualizaciones automáticas.

**Dependencias:** HU-051.

---

## Tabla Resumen de Historias de Usuario

| ID | Épica | Rol | Clasificación | Breve Descripción |
|----|-------|-----|---------------|-------------------|
| HU-00 | Infraestructura | Administrador | MUST-HAVE | Generación de la base de datos inicial |
| HU-001 | Autenticación | Empleado / Supervisor | MUST-HAVE | Login de empleado |
| HU-002 | Autenticación | Cliente | SHOULD-HAVE | Login de cliente |
| HU-003 | Autenticación | Todos | MUST-HAVE | Logout |
| HU-004 | Autenticación | Todos | SHOULD-HAVE | Recuperación de contraseña |
| HU-005 | Autenticación | Todos | SHOULD-HAVE | Cambio de contraseña |
| HU-006 | Configuración | Todos | MUST-HAVE | Visualización de perfil |
| HU-007 | Configuración | Todos | SHOULD-HAVE | Edición de perfil |
| HU-008 | Gestión Clientes | Supervisor | MUST-HAVE | Listado de clientes |
| HU-009 | Gestión Clientes | Supervisor | MUST-HAVE | Creación de cliente |
| HU-010 | Gestión Clientes | Supervisor | MUST-HAVE | Edición de cliente |
| HU-011 | Gestión Clientes | Supervisor | MUST-HAVE | Eliminación de cliente |
| HU-012 | Gestión Clientes | Supervisor | MUST-HAVE | Asignación de tipos de tarea a cliente |
| HU-013 | Gestión Clientes | Supervisor | SHOULD-HAVE | Detalle de cliente |
| HU-014 | Gestión Tipos Cliente | Supervisor | MUST-HAVE | Listado de tipos de cliente |
| HU-015 | Gestión Tipos Cliente | Supervisor | MUST-HAVE | Creación de tipo de cliente |
| HU-016 | Gestión Tipos Cliente | Supervisor | MUST-HAVE | Edición de tipo de cliente |
| HU-017 | Gestión Tipos Cliente | Supervisor | MUST-HAVE | Eliminación de tipo de cliente |
| HU-018 | Gestión Empleados | Supervisor | MUST-HAVE | Listado de empleados |
| HU-019 | Gestión Empleados | Supervisor | MUST-HAVE | Creación de empleado |
| HU-020 | Gestión Empleados | Supervisor | MUST-HAVE | Edición de empleado |
| HU-021 | Gestión Empleados | Supervisor | MUST-HAVE | Eliminación de empleado |
| HU-022 | Gestión Empleados | Supervisor | SHOULD-HAVE | Detalle de empleado |
| HU-023 | Gestión Tipos Tarea | Supervisor | MUST-HAVE | Listado de tipos de tarea |
| HU-024 | Gestión Tipos Tarea | Supervisor | MUST-HAVE | Creación de tipo de tarea |
| HU-025 | Gestión Tipos Tarea | Supervisor | MUST-HAVE | Edición de tipo de tarea |
| HU-026 | Gestión Tipos Tarea | Supervisor | MUST-HAVE | Eliminación de tipo de tarea |
| HU-027 | Gestión Tipos Tarea | Supervisor | SHOULD-HAVE | Detalle de tipo de tarea |
| HU-028 | Registro Tareas | Empleado / Supervisor | MUST-HAVE | Carga de tarea diaria |
| HU-029 | Registro Tareas | Empleado | MUST-HAVE | Edición de tarea propia |
| HU-030 | Registro Tareas | Empleado | MUST-HAVE | Eliminación de tarea propia |
| HU-031 | Registro Tareas | Supervisor | MUST-HAVE | Edición de tarea (supervisor) |
| HU-032 | Registro Tareas | Supervisor | MUST-HAVE | Eliminación de tarea (supervisor) |
| HU-033 | Registro Tareas | Empleado | MUST-HAVE | Lista de tareas propias |
| HU-034 | Registro Tareas | Supervisor | MUST-HAVE | Lista de todas las tareas |
| HU-035 | Registro Tareas | Empleado / Supervisor | MUST-HAVE | Validación duración tramos 15 min |
| HU-036 | Registro Tareas | Empleado / Supervisor | MUST-HAVE | Advertencia fecha futura |
| HU-037 | Registro Tareas | Empleado / Supervisor | MUST-HAVE | Filtrado tipos de tarea por cliente |
| HU-038 | Registro Tareas | Supervisor | MUST-HAVE | Selección empleado propietario |
| HU-039 | Proceso Masivo | Supervisor | SHOULD-HAVE | Acceso al proceso masivo de tareas |
| HU-040 | Proceso Masivo | Supervisor | SHOULD-HAVE | Filtrado de tareas para proceso masivo |
| HU-041 | Proceso Masivo | Supervisor | SHOULD-HAVE | Selección múltiple de tareas |
| HU-042 | Proceso Masivo | Supervisor | SHOULD-HAVE | Procesamiento masivo de tareas |
| HU-043 | Proceso Masivo | Supervisor | SHOULD-HAVE | Validación de selección para procesamiento |
| HU-044 | Informes | Todos | MUST-HAVE | Consulta detallada de tareas |
| HU-045 | Informes | Supervisor | SHOULD-HAVE | Consulta agrupada por empleado |
| HU-046 | Informes | Todos | MUST-HAVE | Consulta agrupada por cliente |
| HU-047 | Informes | Supervisor | SHOULD-HAVE | Consulta agrupada por tipo de tarea |
| HU-048 | Informes | Todos | SHOULD-HAVE | Consulta agrupada por fecha |
| HU-049 | Informes | Todos | SHOULD-HAVE | Exportación de consultas a Excel |
| HU-050 | Informes | Todos | MUST-HAVE | Manejo de resultados vacíos en consultas |
| HU-051 | Dashboard | Todos | MUST-HAVE | Dashboard principal |
| HU-052 | Dashboard | Todos | MUST-HAVE | Resumen de dedicación por cliente |
| HU-053 | Dashboard | Supervisor | SHOULD-HAVE | Resumen de dedicación por empleado |
| HU-054 | Dashboard | Todos | SHOULD-HAVE | Gráficos y visualizaciones |
| HU-055 | Dashboard | Todos | SHOULD-HAVE | Actualización automática del dashboard |

---

## Tickets Técnicos Derivados (MUST-HAVE)

### TK-001 – Migraciones y Modelos de Base de Datos

**HU Relacionadas:** HU-00, HU-001, HU-002, HU-008, HU-009, HU-014, HU-015, HU-018, HU-019, HU-023, HU-024, HU-028, HU-039, HU-044, HU-051

**Descripción:**
- Crear migración para tabla `USERS` (sin prefijo PQ_PARTES_) con campos: `id`, `code` (único, obligatorio), `password_hash` (obligatorio), `activo`, `inhabilitado`, `created_at`, `updated_at`.
- Actualizar migración de `PQ_PARTES_USUARIOS`: agregar campo `user_id` (FK → USERS, obligatorio, único), eliminar campo `password_hash`.
- Actualizar migración de `PQ_PARTES_CLIENTES`: agregar campo `user_id` (FK → USERS, opcional, único), eliminar campo `password_hash`.
- Crear/actualizar migraciones para todas las tablas: `PQ_PARTES_TIPO_CLIENTE`, `PQ_PARTES_TIPO_TAREA`, `PQ_PARTES_REGISTRO_TAREA`, `PQ_PARTES_CLIENTE_TIPO_TAREA`.
- Asegurar que todos los campos requeridos estén definidos (incluyendo `code` en TipoTarea y TipoCliente, `cerrado` en RegistroTarea).
- Definir índices: `USERS.code` (UNIQUE), `PQ_PARTES_USUARIOS.user_id` (UNIQUE), `PQ_PARTES_CLIENTES.user_id` (UNIQUE), y otros índices para campos de búsqueda y relaciones.
- Definir foreign keys: `PQ_PARTES_USUARIOS.user_id → USERS.id`, `PQ_PARTES_CLIENTES.user_id → USERS.id`, y otras restricciones de integridad referencial.
- Implementar soft delete o campo `inhabilitado` según diseño.

**Entregables:**
- Archivos de migración Laravel (incluyendo migración de `USERS`).
- Modelo `User` en `backend/app/Models/User.php`.
- Actualizar modelo `Usuario` con relación `belongsTo(User::class)`.
- Actualizar modelo `Cliente` con relación `belongsTo(User::class)` (opcional).
- Modelos Eloquent con relaciones y validaciones básicas.
- Seeders para datos de prueba/demo.

---

### TK-002 – Endpoints de Autenticación

**HU Relacionadas:** HU-001, HU-002, HU-003

**Descripción:**
- Implementar `POST /api/v1/auth/login` unificado para empleados y clientes.
- Validar credenciales contra tabla `USERS` (sin prefijo PQ_PARTES_).
- Después del login exitoso, determinar tipo de usuario (cliente o usuario) buscando `User.code` en `PQ_PARTES_CLIENTES.code` o `PQ_PARTES_USUARIOS.code`.
- Obtener datos del usuario/cliente desde `PQ_PARTES_USUARIOS` o `PQ_PARTES_CLIENTES`.
- Generar token con todos los valores a conservar: `user_id`, `user_code`, `tipo_usuario`, `usuario_id`/`cliente_id`, `es_supervisor`.
- Implementar `POST /api/v1/auth/logout` para cerrar sesión.
- Integración con Laravel Sanctum para tokens.
- Validaciones de credenciales y estado de usuario (tanto en `USERS` como en entidad asociada).
- Manejo de errores con códigos de dominio.
- Implementar middleware para conservar valores de autenticación durante el ciclo del proceso.

**Entregables:**
- Controlador `AuthController`.
- Servicio `AuthService` con método `determineUserType(User $user)`.
- Servicio `AuthService` con método `getUserData(User $user, string $tipoUsuario)`.
- Middleware para extraer y hacer disponibles valores de autenticación del token.
- Requests de validación.
- Tests unitarios y de integración.

---

### TK-003 – Endpoints de Gestión de Clientes

**HU Relacionadas:** HU-008, HU-009, HU-010, HU-011, HU-012, HU-013

**Descripción:**
- `GET /api/v1/clientes` - Listado con filtros y paginación.
- `POST /api/v1/clientes` - Creación de cliente. Si se habilita acceso al sistema, crear también registro en `USERS`. Validar que el `code` no exista en `USERS` (si se habilita acceso).
- `GET /api/v1/clientes/{id}` - Detalle de cliente.
- `PUT /api/v1/clientes/{id}` - Edición de cliente. Si se cambia contraseña, actualizar `password_hash` en `USERS`. Sincronizar estados (`activo`, `inhabilitado`) entre `USERS` y `PQ_PARTES_CLIENTES` (si tiene `user_id`). Permitir habilitar/deshabilitar acceso al sistema.
- `DELETE /api/v1/clientes/{id}` - Eliminación de cliente (con validación de referencias). Si tiene `user_id`, eliminar también registro en `USERS` (o marcar como inhabilitado según diseño).
- `GET /api/v1/clientes/{id}/tipos-tarea` - Listado de tipos de tarea asignados.
- `POST /api/v1/clientes/{id}/tipos-tarea` - Asignación de tipos de tarea.
- `DELETE /api/v1/clientes/{id}/tipos-tarea/{tipo_tarea_id}` - Desasignación de tipos de tarea.
- Validaciones de reglas de negocio (tipos de tarea, integridad referencial).

**Entregables:**
- Controlador `ClienteController`.
- Requests de validación.
- Servicios de negocio para reglas complejas (incluyendo creación/sincronización con `USERS`).
- Tests unitarios y de integración.

---

### TK-004 – Endpoints de Gestión de Tipos de Cliente

**HU Relacionadas:** HU-014, HU-015, HU-016, HU-017

**Descripción:**
- `GET /api/v1/tipos-cliente` - Listado con filtros y paginación.
- `POST /api/v1/tipos-cliente` - Creación de tipo de cliente.
- `GET /api/v1/tipos-cliente/{id}` - Detalle de tipo de cliente.
- `PUT /api/v1/tipos-cliente/{id}` - Edición de tipo de cliente.
- `DELETE /api/v1/tipos-cliente/{id}` - Eliminación (con validación de referencias).

**Entregables:**
- Controlador `TipoClienteController`.
- Requests de validación.
- Tests unitarios y de integración.

---

### TK-005 – Endpoints de Gestión de Empleados

**HU Relacionadas:** HU-018, HU-019, HU-020, HU-021, HU-022

**Descripción:**
- `GET /api/v1/empleados` - Listado con filtros y paginación.
- `POST /api/v1/empleados` - Creación de empleado. Al crear un empleado, crear también registro en `USERS`. Validar que el `code` no exista en `USERS`. Sincronizar estados (`activo`, `inhabilitado`) entre `USERS` y `PQ_PARTES_USUARIOS`.
- `GET /api/v1/empleados/{id}` - Detalle de empleado.
- `PUT /api/v1/empleados/{id}` - Edición de empleado. Si se cambia contraseña, actualizar `password_hash` en `USERS` (no en `PQ_PARTES_USUARIOS`). Sincronizar estados (`activo`, `inhabilitado`) entre `USERS` y `PQ_PARTES_USUARIOS`.
- `DELETE /api/v1/empleados/{id}` - Eliminación (con validación de referencias). Al eliminar empleado, eliminar también registro en `USERS` (o marcar como inhabilitado según diseño).
- Validaciones de permisos (solo supervisores).

**Entregables:**
- Controlador `EmpleadoController` o `UsuarioController`.
- Requests de validación.
- Servicios de negocio para creación/sincronización con `USERS`.
- Middleware de permisos para supervisores.
- Tests unitarios y de integración.

---

### TK-006 – Endpoints de Gestión de Tipos de Tarea

**HU Relacionadas:** HU-023, HU-024, HU-025, HU-026, HU-027

**Descripción:**
- `GET /api/v1/tipos-tarea` - Listado con filtros y paginación.
- `POST /api/v1/tipos-tarea` - Creación de tipo de tarea.
- `GET /api/v1/tipos-tarea/{id}` - Detalle de tipo de tarea.
- `PUT /api/v1/tipos-tarea/{id}` - Edición de tipo de tarea.
- `DELETE /api/v1/tipos-tarea/{id}` - Eliminación (con validación de referencias).
- Validación de regla: solo un tipo por defecto.
- Validación de regla: si es por defecto, forzar genérico.

**Entregables:**
- Controlador `TipoTareaController`.
- Requests de validación.
- Servicios de negocio para reglas complejas.
- Tests unitarios y de integración.

---

### TK-007 – Endpoints de Registro de Tareas

**HU Relacionadas:** HU-028, HU-029, HU-030, HU-031, HU-032, HU-033, HU-034, HU-035, HU-036, HU-037, HU-038

**Descripción:**
- `GET /api/v1/tareas` - Listado con filtros y paginación (según rol: propias o todas).
- `POST /api/v1/tareas` - Creación de tarea.
- `GET /api/v1/tareas/{id}` - Detalle de tarea.
- `PUT /api/v1/tareas/{id}` - Edición de tarea (con validación de permisos y estado cerrado).
- `DELETE /api/v1/tareas/{id}` - Eliminación (con validación de permisos y estado cerrado).
- `GET /api/v1/tareas/tipos-disponibles?cliente_id={id}` - Tipos de tarea disponibles para un cliente.
- Validaciones: duración en tramos de 15 minutos, fecha futura (advertencia), observación obligatoria, tipos de tarea por cliente.
- Validación de permisos: empleado solo sus tareas, supervisor todas.

**Entregables:**
- Controlador `TareaController` o `RegistroTareaController`.
- Requests de validación.
- Servicios de negocio para validaciones complejas.
- Tests unitarios y de integración.

---

### TK-008 – Componentes UI de Autenticación

**HU Relacionadas:** HU-001, HU-002, HU-003, HU-004, HU-005

**Descripción:**
- Componente `LoginForm` para empleados.
- Componente `LoginClienteForm` para clientes (opcional).
- Componente de logout (botón/enlace).
- Componente `RecuperarPasswordForm` (opcional).
- Componente `CambiarPasswordForm` (opcional).
- Integración con i18n y data-testid.
- Manejo de estados (loading, error, success).
- Redirección post-login según rol.

**Entregables:**
- Componentes React en `frontend/src/features/auth/`.
- Tests unitarios de componentes.
- Integración con servicios API.

---

### TK-009 – Componentes UI de Configuración de Usuario

**HU Relacionadas:** HU-006, HU-007

**Descripción:**
- Componente `PerfilUsuario` para visualización.
- Componente `EditarPerfilForm` (opcional).
- Integración con i18n y data-testid.
- Manejo de estados.

**Entregables:**
- Componentes React en `frontend/src/features/user/`.
- Tests unitarios de componentes.

---

### TK-010 – Componentes UI de Gestión de Clientes

**HU Relacionadas:** HU-008, HU-009, HU-010, HU-011, HU-012, HU-013

**Descripción:**
- Componente `ClientesList` con tabla, filtros y paginación.
- Componente `ClienteForm` (crear/editar).
- Componente `ClienteDetail` (opcional).
- Componente `AsignarTiposTarea` para gestión de tipos asignados.
- Integración con i18n y data-testid.
- Validaciones en frontend (complementarias a backend).
- Manejo de estados y errores.

**Entregables:**
- Componentes React en `frontend/src/features/clientes/`.
- Tests unitarios de componentes.
- Integración con servicios API.

---

### TK-011 – Componentes UI de Gestión de Tipos de Cliente

**HU Relacionadas:** HU-014, HU-015, HU-016, HU-017

**Descripción:**
- Componente `TiposClienteList` con tabla, filtros y paginación.
- Componente `TipoClienteForm` (crear/editar).
- Integración con i18n y data-testid.
- Validaciones en frontend.

**Entregables:**
- Componentes React en `frontend/src/features/tipos-cliente/`.
- Tests unitarios de componentes.
- Integración con servicios API.

---

### TK-012 – Componentes UI de Gestión de Empleados

**HU Relacionadas:** HU-018, HU-019, HU-020, HU-021, HU-022

**Descripción:**
- Componente `EmpleadosList` con tabla, filtros y paginación.
- Componente `EmpleadoForm` (crear/editar).
- Componente `EmpleadoDetail` (opcional).
- Integración con i18n y data-testid.
- Validaciones en frontend.

**Entregables:**
- Componentes React en `frontend/src/features/empleados/`.
- Tests unitarios de componentes.
- Integración con servicios API.

---

### TK-013 – Componentes UI de Gestión de Tipos de Tarea

**HU Relacionadas:** HU-023, HU-024, HU-025, HU-026, HU-027

**Descripción:**
- Componente `TiposTareaList` con tabla, filtros y paginación.
- Componente `TipoTareaForm` (crear/editar) con lógica de "por defecto" y "genérico".
- Componente `TipoTareaDetail` (opcional).
- Integración con i18n y data-testid.
- Validaciones en frontend (especialmente regla de único por defecto).

**Entregables:**
- Componentes React en `frontend/src/features/tipos-tarea/`.
- Tests unitarios de componentes.
- Integración con servicios API.

---

### TK-014 – Componentes UI de Registro de Tareas

**HU Relacionadas:** HU-028, HU-029, HU-030, HU-031, HU-032, HU-033, HU-034, HU-035, HU-036, HU-037, HU-038

**Descripción:**
- Componente `TareaForm` (crear/editar) con:
  - Selector de fecha con advertencia de fecha futura.
  - Selector de cliente.
  - Selector dinámico de tipos de tarea (filtrado por cliente).
  - Input de duración con validación de tramos de 15 minutos.
  - Checkboxes de sin cargo y presencial.
  - Textarea de observación (obligatorio).
  - Selector de empleado (solo para supervisores).
- Componente `TareasList` con tabla, filtros y paginación (propias o todas según rol).
- Integración con i18n y data-testid.
- Validaciones en frontend (complementarias a backend).
- Manejo de estados y errores.
- Lógica de filtrado dinámico de tipos de tarea por cliente.

**Entregables:**
- Componentes React en `frontend/src/features/tareas/`.
- Tests unitarios de componentes.
- Integración con servicios API.

---

### TK-015 – Servicios API Frontend

**HU Relacionadas:** Todas las HU

**Descripción:**
- Servicio `AuthService` para autenticación.
- Servicio `ClienteService` para gestión de clientes.
- Servicio `TipoClienteService` para gestión de tipos de cliente.
- Servicio `EmpleadoService` para gestión de empleados.
- Servicio `TipoTareaService` para gestión de tipos de tarea.
- Servicio `TareaService` para gestión de tareas.
- Servicio `BulkTaskProcessService` para proceso masivo.
- Servicio `InformeService` para consultas e informes.
- Servicio `DashboardService` para datos del dashboard.
- Interceptor para agregar token a requests.
- Manejo de errores centralizado.
- Formato de respuesta estándar (envelope).

**Entregables:**
- Servicios en `frontend/src/services/`.
- Configuración de axios/fetch.
- Tests unitarios de servicios.

---

### TK-016 – Middleware y Permisos Backend

**HU Relacionadas:** Todas las HU que requieren autenticación y roles

**Descripción:**
- Middleware de autenticación (Sanctum).
- Middleware para extraer valores de autenticación del token: `tipo_usuario`, `user_code`, `usuario_id`/`cliente_id`, `es_supervisor`.
- Middleware para hacer disponibles estos valores en el request (ej: `request()->user_type`, `request()->user_code`, etc.).
- Validar que el `User` asociado al token esté activo y no inhabilitado en `USERS`.
- Validar que el usuario/cliente asociado esté activo y no inhabilitado en su tabla correspondiente (`PQ_PARTES_USUARIOS` o `PQ_PARTES_CLIENTES`).
- Middleware de permisos para supervisores.
- Middleware de validación de roles.
- Políticas de autorización (Laravel Policies) para tareas (empleado solo sus tareas, supervisor todas).

**Entregables:**
- Middleware en `backend/app/Http/Middleware/`.
- Middleware para conservar valores de autenticación durante el ciclo del proceso.
- Policies en `backend/app/Policies/`.
- Tests de middleware y políticas.

---

### TK-017 – Validaciones y Reglas de Negocio Backend

**HU Relacionadas:** Todas las HU

**Descripción:**
- Form Requests de validación para todos los endpoints.
- Servicios de negocio para reglas complejas:
  - Validación de único tipo por defecto.
  - Validación de tipos de tarea por cliente.
  - Validación de integridad referencial.
  - Validación de duración en tramos de 15 minutos.
  - Validación de fecha futura (advertencia).
- Códigos de error de dominio centralizados.

**Entregables:**
- Form Requests en `backend/app/Http/Requests/`.
- Servicios en `backend/app/Services/`.
- Excepciones de dominio personalizadas.
- Tests unitarios de validaciones y reglas.

---

### TK-018 – Tests Unitarios Backend

**HU Relacionadas:** Todas las HU

**Descripción:**
- Tests unitarios de modelos (validaciones, relaciones).
- Tests unitarios de servicios de negocio.
- Tests unitarios de validaciones (Form Requests).
- Tests unitarios de políticas de autorización.

**Entregables:**
- Tests PHPUnit en `backend/tests/Unit/`.
- Cobertura mínima del 70% en lógica crítica.

---

### TK-019 – Tests de Integración Backend

**HU Relacionadas:** Todas las HU

**Descripción:**
- Tests de integración de endpoints (API).
- Tests de integración de autenticación.
- Tests de integración de permisos y roles.
- Tests de integración de reglas de negocio complejas.
- Uso de base de datos de prueba y factories.

**Entregables:**
- Tests PHPUnit en `backend/tests/Feature/`.
- Factories para modelos.
- Seeders para datos de prueba.

---

### TK-020 – Tests E2E del Flujo Principal

**HU Relacionadas:** HU-001, HU-028, HU-033, HU-044, HU-046, HU-051 (flujo E2E prioritario)

**Estado:** ✅ **Playwright instalado y configurado**

**Descripción:**
- Test E2E con Playwright del flujo: Login → Carga de tarea → Visualización de lista.
- Validación de autenticación, creación de tarea, y listado.
- Uso de data-testid para selectores.
- Validación de mensajes de error y éxito.

**Entregables completados:**
- ✅ Playwright instalado en `frontend/` (versión 1.57.0)
- ✅ Configuración de Playwright en `frontend/playwright.config.ts`
- ✅ Estructura de tests en `frontend/tests/e2e/`
- ✅ Test de ejemplo en `frontend/tests/e2e/example.spec.ts`
- ✅ Documentación en `frontend/tests/e2e/README.md`
- ✅ Reglas de testing en `.cursor/rules/11-playwright-testing-rules.md`
- ✅ Scripts npm configurados (`test:e2e`, `test:e2e:ui`, `test:e2e:headed`)

**Pendiente:**
- ⏳ Implementar tests reales del flujo E2E principal según `specs/flows/e2e-core-flow.md`

---

### TK-021 – Tests Unitarios Frontend

**HU Relacionadas:** Todas las HU de UI

**Descripción:**
- Tests unitarios de componentes React.
- Tests de hooks personalizados.
- Tests de servicios API.
- Tests de utilidades y helpers.

**Entregables:**
- Tests con Jest/Vitest en `frontend/src/__tests__/` o `frontend/tests/`.
- Configuración de testing library.

---

### TK-022 – Configuración CI/CD Básica

**HU Relacionadas:** Todas las HU (infraestructura)

**Descripción:**
- Pipeline de CI para ejecutar tests (unit, integración, E2E).
- Pipeline de CD para deploy en ambiente de staging/producción.
- Configuración de secretos (tokens, credenciales de BD).
- Configuración de variables de entorno.

**Entregables:**
- Archivos de configuración CI/CD (GitHub Actions, GitLab CI, etc.).
- Documentación de despliegue.
- Archivo `.env.example` con placeholders.

---

### TK-023 – Logging y Auditoría Básica

**HU Relacionadas:** Todas las HU (trazabilidad)

**Descripción:**
- Logging de acciones críticas (creación, edición, eliminación de entidades).
- Logging de autenticaciones (login, logout).
- Logging de errores y excepciones.
- Auditoría básica de cambios (opcional: tabla de auditoría).

**Entregables:**
- Configuración de logging en Laravel.
- Middleware de auditoría (opcional).
- Tests de logging.

---

### TK-024 – Seeders y Datos de Demo

**HU Relacionadas:** Todas las HU (datos de prueba)

**Descripción:**
- Seeder de tipos de cliente básicos.
- Seeder de tipos de tarea (incluyendo uno por defecto y genéricos).
- Seeder de clientes de ejemplo.
- Seeder de empleados (incluyendo supervisores).
- Seeder de tareas de ejemplo.
- Comando `php artisan db:seed` para poblar datos de prueba.

**Entregables:**
- Seeders en `backend/database/seeders/`.
- Documentación de uso.

---

### TK-025 – Documentación de API

**HU Relacionadas:** Todas las HU (documentación)

**Descripción:**
- Documentación de endpoints (Swagger/OpenAPI o Markdown).
- Ejemplos de requests y responses.
- Códigos de error documentados.
- Autenticación documentada.

**Entregables:**
- Archivo OpenAPI/Swagger o documentación Markdown en `docs/api/`.
- Ejemplos de uso.

---

### TK-026 – Endpoints de Proceso Masivo de Tareas

**HU Relacionadas:** HU-039, HU-040, HU-041, HU-042, HU-043

**Descripción:**
- `GET /api/v1/tareas/proceso-masivo` - Listado de tareas con filtros para proceso masivo.
- `POST /api/v1/tareas/proceso-masivo` - Procesamiento masivo (cambiar estado cerrado/abierto).
- Validación de permisos (solo supervisores).
- Validación de selección (al menos una tarea).
- Procesamiento atómico (transaccional).

**Entregables:**
- Controlador `BulkTaskProcessController`.
- Requests de validación.
- Servicio de negocio para procesamiento masivo.
- Tests unitarios y de integración.

---

### TK-027 – Componentes UI de Proceso Masivo

**HU Relacionadas:** HU-039, HU-040, HU-041, HU-042, HU-043

**Descripción:**
- Componente `BulkTaskProcessPage` con filtros y tabla.
- Componente `BulkFilters` para filtros complejos.
- Componente `SelectableTaskTable` con checkboxes.
- Componente `ProcessButton` con validación de selección.
- Integración con i18n y data-testid.
- Manejo de estados (loading, error, success).
- Lógica de selección múltiple (seleccionar todos, deseleccionar todos).

**Entregables:**
- Componentes React en `frontend/src/features/proceso-masivo/`.
- Tests unitarios de componentes.
- Integración con servicios API.

---

### TK-028 – Endpoints de Informes y Consultas

**HU Relacionadas:** HU-044, HU-045, HU-046, HU-047, HU-048, HU-049, HU-050

**Descripción:**
- `GET /api/v1/informes/detalle` - Consulta detallada de tareas con filtros.
- `GET /api/v1/informes/por-empleado` - Consulta agrupada por empleado.
- `GET /api/v1/informes/por-cliente` - Consulta agrupada por cliente.
- `GET /api/v1/informes/por-tipo` - Consulta agrupada por tipo de tarea.
- `GET /api/v1/informes/por-fecha` - Consulta agrupada por fecha.
- `GET /api/v1/informes/exportar` - Exportación a Excel (query params para tipo de consulta y filtros).
- Validación de permisos según rol (filtros automáticos).
- Validación de período (`fecha_desde <= fecha_hasta`).
- Manejo de resultados vacíos.

**Entregables:**
- Controlador `InformeController` o controladores específicos por tipo.
- Requests de validación.
- Servicios de negocio para agrupación y agregación.
- Servicio de exportación a Excel (Laravel Excel o similar).
- Tests unitarios y de integración.

---

### TK-029 – Componentes UI de Informes y Consultas

**HU Relacionadas:** HU-044, HU-045, HU-046, HU-047, HU-048, HU-049, HU-050

**Descripción:**
- Componente `ConsultaDetallePage` para consulta detallada.
- Componente `ConsultaAgrupadaPage` genérico para consultas agrupadas (por empleado, cliente, tipo, fecha).
- Componente `FiltrosConsulta` común para todos los tipos de consulta.
- Componente `TablaResultados` con paginación y ordenamiento.
- Componente `GrupoExpandible` (accordion) para consultas agrupadas.
- Componente `ExportarExcelButton` con validación de resultados.
- Integración con i18n y data-testid.
- Manejo de estados vacíos.
- Lógica de expansión/colapso de grupos.

**Entregables:**
- Componentes React en `frontend/src/features/informes/`.
- Tests unitarios de componentes.
- Integración con servicios API.

---

### TK-030 – Servicio de Exportación a Excel

**HU Relacionadas:** HU-049

**Descripción:**
- Servicio backend para generar archivos Excel (Laravel Excel/Maatwebsite Excel).
- Formato de archivo XLSX.
- Estructura de datos según tipo de consulta (detallada o agrupada).
- Formato de horas en decimal.
- Formato de fechas correcto.
- Nombre de archivo descriptivo con período/filtros.
- Streaming de archivo para descarga.

**Entregables:**
- Servicio `ExcelExportService`.
- Export classes para cada tipo de consulta.
- Tests unitarios de exportación.

---

### TK-031 – Endpoints de Dashboard

**HU Relacionadas:** HU-051, HU-052, HU-053, HU-054, HU-055

**Descripción:**
- `GET /api/v1/dashboard/resumen` - Resumen ejecutivo del dashboard.
- `GET /api/v1/dashboard/por-cliente` - Resumen por cliente (top N).
- `GET /api/v1/dashboard/por-empleado` - Resumen por empleado (top N, solo supervisor).
- Query parameters: `fecha_desde`, `fecha_hasta`, `limit` (para top N).
- Validación de permisos según rol (filtros automáticos).
- Cálculo de KPIs (totales, promedios).
- Respuesta optimizada para dashboard (datos agregados, no detalle completo).

**Entregables:**
- Controlador `DashboardController`.
- Requests de validación.
- Servicios de negocio para cálculo de KPIs y agregaciones.
- Tests unitarios y de integración.

---

### TK-032 – Componentes UI de Dashboard

**HU Relacionadas:** HU-051, HU-052, HU-053, HU-054, HU-055

**Descripción:**
- Componente `DashboardPage` principal.
- Componente `KPICard` para indicadores clave (total horas, cantidad tareas, promedio).
- Componente `ResumenPorCliente` para lista de top clientes.
- Componente `ResumenPorEmpleado` para lista de top empleados (solo supervisor).
- Componente `SelectorPeriodo` para cambiar período.
- Componente `GraficoDistribucion` para gráficos (Chart.js, Recharts, etc.).
- Integración con i18n y data-testid.
- Actualización automática (polling o WebSockets opcional).
- Botón de actualización manual.
- Indicador de última actualización.

**Entregables:**
- Componentes React en `frontend/src/features/dashboard/`.
- Configuración de librería de gráficos.
- Tests unitarios de componentes.
- Integración con servicios API.

---

### TK-033 – Optimización de Consultas para Informes

**HU Relacionadas:** HU-044, HU-045, HU-046, HU-047, HU-048

**Descripción:**
- Optimización de queries SQL para consultas agrupadas.
- Uso de índices apropiados (`idx_registro_usuario_fecha`, `idx_registro_cliente_fecha`).
- Eager loading de relaciones para evitar N+1 queries.
- Caché de resultados (opcional, según performance).
- Paginación eficiente para grandes volúmenes de datos.
- Query builder optimizado para agregaciones.

**Entregables:**
- Optimización de queries en controladores y servicios.
- Índices en base de datos.
- Tests de performance (opcional).

---

**Última actualización:** 2025-01-20
