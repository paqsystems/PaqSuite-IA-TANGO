# Especificaciones de Pantallas y Procesos

## Descripción General

Este documento define todos los procesos del sistema, la organización del menú, las pantallas y los datos a tratar. A partir de estas especificaciones se elaborarán:

- Componentes de frontend
- Flujos de usuario
- Validaciones de UI
- Integraciones con endpoints API
- Test IDs y accesibilidad
- Documentación técnica de componentes

---

## Estructura de Navegación

### Menú Principal

El sistema presenta un menú principal con las siguientes secciones:

1. **Archivos** - Gestión de catálogos y maestros
2. **Procesos** - Operaciones de negocio
3. **Consultas** - Reportes y visualizaciones
4. **Dashboard** - Resumen ejecutivo

### Opciones del Usuario

Botón de configuración del usuario con las siguientes opciones:
- Cambiar apariencia
- Cambiar contraseña
- Abrir en otra solapa
- Cerrar sesión

**Test ID:** `user.menu.configButton`

---

## Procesos de Usuario

### Proceso 1: Gestión de Archivos (Catálogos)

**Actor:** Supervisor / Administrador  
**Descripción:** Gestión de catálogos y maestros del sistema

**Subprocesos:**
1. Tipos de Clientes
2. Tipos de Tareas
3. Asistentes
4. Clientes

**Flujo general:**
1. Seleccionar opción del menú "Archivos"
2. Elegir catálogo específico
3. Visualizar lista de registros
4. Realizar acción (nuevo, modificar, eliminar, consultar, copiar)

**Pantallas involucradas:**
- Lista de registros
- Formulario de alta/edición
- Modal de confirmación de eliminación

---

### Proceso 2: Carga de Tareas

**Actor:** Empleado / Supervisor  
**Descripción:** Registro y gestión de tareas diarias

**Flujo:**
1. Seleccionar período
2. Visualizar tareas del período
3. Realizar acción (nuevo, modificar, eliminar, consultar, copiar, cerrar, reabrir)

**Pantallas involucradas:**
- Filtro de período
- Lista de tareas
- Formulario de alta/edición de tarea

**Endpoints API relacionados:**
- `GET /api/v1/tareas` - Listar tareas
- `POST /api/v1/tareas` - Crear tarea
- `PUT /api/v1/tareas/{id}` - Actualizar tarea
- `DELETE /api/v1/tareas/{id}` - Eliminar tarea

---

### Proceso 3: Proceso Masivo de Tareas

**Actor:** Supervisor (exclusivo)  
**Descripción:** Cierre/reapertura masiva de tareas

**Flujo:**
1. Aplicar filtros (período, cliente, asistente, estado)
2. Visualizar registros filtrados
3. Seleccionar registros (todos/ninguno/individual)
4. Procesar (invertir estado cerrado/abierto)

**Pantallas involucradas:**
- Filtros de búsqueda
- Lista de tareas con checkboxes
- Confirmación de procesamiento

---

### Proceso 4: Consultas y Reportes

**Actor:** Empleado / Supervisor / Cliente  
**Descripción:** Visualización de tareas con diferentes criterios de agrupación

**Subprocesos:**
1. Detalle de tareas
2. Tareas por asistente
3. Tareas por cliente
4. Tareas por tipo
5. Tareas por fecha

**Flujo general:**
1. Seleccionar tipo de consulta
2. Aplicar filtros
3. Visualizar resultados (agrupados o detallados)
4. Exportar a Excel (opcional)

**Pantallas involucradas:**
- Filtros de consulta
- Lista de resultados
- Vista detallada (expandible)
- Exportación

**Endpoints API relacionados:**
- `GET /api/v1/tareas` - Listar tareas con filtros
- `GET /api/v1/tareas/resumen` - Resumen de dedicación

---

## Especificaciones de Pantallas

### Pantalla: Menú Principal

- **Ruta:** `/`
- **Componente:** `MainLayout` / `NavigationMenu`
- **Tipo de usuario:** Todos
- **Autenticación requerida:** Sí

#### Elementos de la Pantalla

| Elemento | Tipo | Descripción | Test ID |
|----------|------|-------------|---------|
| Menú Archivos | MenuItem | Desplegable con submenús | `nav.menu.archivos` |
| Menú Procesos | MenuItem | Desplegable con submenús | `nav.menu.procesos` |
| Menú Consultas | MenuItem | Desplegable con submenús | `nav.menu.consultas` |
| Dashboard | MenuItem | Enlace directo | `nav.menu.dashboard` |
| Botón Configuración | Button | Menú de opciones de usuario | `user.menu.configButton` |

---

### Pantalla: Lista de Archivos (Patrón Común)

- **Ruta:** `/archivos/{tipo}`
- **Componente:** `CatalogListPage`
- **Tipo de usuario:** Supervisor / Administrador
- **Autenticación requerida:** Sí

#### Estructura Común

Todas las pantallas de archivos siguen el mismo patrón:
- Lista de todos los registros
- Selección de un registro (posición actual)
- Acciones disponibles: nuevo, eliminar, modificar, consultar, copiar

#### Elementos de la Pantalla

| Elemento | Tipo | Descripción | Test ID |
|----------|------|-------------|---------|
| Lista de registros | DataTable | Tabla con todos los registros | `archivos.{tipo}.table` |
| Botón Nuevo | Button | Crear nuevo registro | `archivos.{tipo}.newButton` |
| Botón Eliminar | Button | Eliminar registro seleccionado | `archivos.{tipo}.deleteButton` |
| Botón Modificar | Button | Editar registro seleccionado | `archivos.{tipo}.editButton` |
| Botón Consultar | Button | Ver detalles del registro | `archivos.{tipo}.viewButton` |
| Botón Copiar | Button | Duplicar registro (sin código) | `archivos.{tipo}.copyButton` |

#### Estados

- **Estado inicial:** Lista vacía o con datos cargados
- **Estado de carga:** Skeleton loader o spinner
- **Estado de error:** Mensaje de error con opción de reintentar
- **Estado vacío:** Mensaje "No hay registros" con botón "Nuevo"

---

### Pantalla: Tipos de Clientes

- **Ruta:** `/archivos/tipos-clientes`
- **Componente:** `ClientTypesListPage`

#### Datos por Registro

| Campo | Tipo | Descripción | Validaciones | Test ID |
|-------|------|-------------|--------------|---------|
| Código | string | Identificador único | Requerido, único | `archivos.tipos-clientes.table.code` |
| Descripción | string | Nombre del tipo | Requerido, max 200 caracteres | `archivos.tipos-clientes.table.description` |
| Inhabilitado | boolean | Indica si el tipo está inhabilitado | Default: false | `archivos.tipos-clientes.table.inhabilitado` |

#### Endpoints API

- `GET /api/v1/tipos-cliente` - Listar tipos
- `POST /api/v1/tipos-cliente` - Crear tipo
- `PUT /api/v1/tipos-cliente/{id}` - Actualizar tipo
- `DELETE /api/v1/tipos-cliente/{id}` - Eliminar tipo

---

### Pantalla: Tipos de Tareas

- **Ruta:** `/archivos/tipos-tareas`
- **Componente:** `TaskTypesListPage`

#### Datos por Registro

| Campo | Tipo | Descripción | Validaciones | Test ID |
|-------|------|-------------|--------------|---------|
| Código | string | Identificador único | Requerido, único | `archivos.tipos-tareas.table.code` |
| Descripción | string | Nombre del tipo | Requerido, max 200 caracteres | `archivos.tipos-tareas.table.description` |
| Por Defecto | boolean | Indica si es el tipo predeterminado | Solo uno puede ser true | `archivos.tipos-tareas.table.isDefault` |
| Genérico | boolean | Indica si está disponible para todos los clientes | - | `archivos.tipos-tareas.table.isGeneric` |
| Inhabilitado | boolean | Indica si el tipo está inhabilitado | Default: false | `archivos.tipos-tareas.table.inhabilitado` |

#### Validaciones

- Solo un tipo puede tener `por_defecto = true`
- Si es genérico, no puede asignarse a clientes específicos

#### Endpoints API

- `GET /api/v1/tipos-tarea` - Listar tipos
- `POST /api/v1/tipos-tarea` - Crear tipo
- `PUT /api/v1/tipos-tarea/{id}` - Actualizar tipo
- `DELETE /api/v1/tipos-tarea/{id}` - Eliminar tipo

---

### Pantalla: Asistentes

- **Ruta:** `/archivos/asistentes`
- **Componente:** `AssistantsListPage`

#### Datos por Registro

| Campo | Tipo | Descripción | Validaciones | Test ID |
|-------|------|-------------|--------------|---------|
| Código | string | Código de usuario para login | Requerido, único | `archivos.asistentes.table.code` |
| Descripción | string | Nombre completo | Requerido, max 200 caracteres | `archivos.asistentes.table.name` |
| Email | string | Email del asistente | Requerido, formato válido, único | `archivos.asistentes.table.email` |
| Es Supervisor | boolean | Indica si tiene permisos de supervisor | - | `archivos.asistentes.table.isSupervisor` |
| Inhabilitado | boolean | Indica si el asistente está inhabilitado | Default: false | `archivos.asistentes.table.inhabilitado` |

#### Endpoints API

- `GET /api/v1/usuarios` - Listar asistentes
- `POST /api/v1/usuarios` - Crear asistente
- `PUT /api/v1/usuarios/{id}` - Actualizar asistente
- `DELETE /api/v1/usuarios/{id}` - Eliminar asistente

---

### Pantalla: Clientes

- **Ruta:** `/archivos/clientes`
- **Componente:** `ClientsListPage`

#### Datos por Registro

| Campo | Tipo | Descripción | Validaciones | Test ID |
|-------|------|-------------|--------------|---------|
| Código | string | Identificador único | Requerido, único | `archivos.clientes.table.code` |
| Descripción | string | Nombre del cliente | Requerido, max 200 caracteres | `archivos.clientes.table.name` |
| Email | string | Email del cliente | Opcional, formato válido | `archivos.clientes.table.email` |
| Tipo de Cliente | Select | Relación con TipoCliente | Requerido, FK válida | `archivos.clientes.table.tipoCliente` |
| Tareas Asignadas | List | Lista de tipos de tarea NO genéricos asignados | - | `archivos.clientes.table.assignedTasks` |
| Inhabilitado | boolean | Indica si el cliente está inhabilitado | Default: false | `archivos.clientes.table.inhabilitado` |

#### Acciones Especiales

- **Asignación de tipos de tarea:** Modal o sección para asignar tipos de tarea NO genéricos al cliente

#### Endpoints API

- `GET /api/v1/clientes` - Listar clientes
- `POST /api/v1/clientes` - Crear cliente
- `PUT /api/v1/clientes/{id}` - Actualizar cliente
- `DELETE /api/v1/clientes/{id}` - Eliminar cliente
- `GET /api/v1/clientes/{id}/tipos-tarea` - Obtener tipos asignados
- `POST /api/v1/clientes/{id}/tipos-tarea` - Asignar tipo de tarea
- `DELETE /api/v1/clientes/{id}/tipos-tarea/{tipoId}` - Desasignar tipo de tarea

---

### Pantalla: Carga de Tareas

- **Ruta:** `/procesos/carga-tareas`
- **Componente:** `TaskEntryPage`
- **Tipo de usuario:** Empleado / Supervisor
- **Autenticación requerida:** Sí

#### Flujo

1. Usuario selecciona período (fecha desde - fecha hasta)
2. Sistema trae todas las tareas del período:
   - **Supervisor:** Todas las tareas de todos los asistentes
   - **Usuario normal:** Solo sus propias tareas
3. Se presenta lista de tareas
4. Usuario puede realizar acciones sobre las tareas

#### Elementos de la Pantalla

| Elemento | Tipo | Descripción | Test ID |
|----------|------|-------------|---------|
| Filtro Fecha Desde | DateInput | Fecha inicial del período | `tasks.entry.filterDateFrom` |
| Filtro Fecha Hasta | DateInput | Fecha final del período | `tasks.entry.filterDateTo` |
| Botón Aplicar Filtros | Button | Aplicar filtros y cargar tareas | `tasks.entry.applyFiltersButton` |
| Lista de Tareas | DataTable | Tabla con tareas del período | `tasks.entry.table` |
| Botón Nuevo | Button | Crear nueva tarea | `tasks.entry.newButton` |
| Botón Eliminar | Button | Eliminar tarea seleccionada | `tasks.entry.deleteButton` |
| Botón Modificar | Button | Editar tarea seleccionada | `tasks.entry.editButton` |
| Botón Consultar | Button | Ver detalles de tarea | `tasks.entry.viewButton` |
| Botón Copiar | Button | Duplicar tarea (sin código) | `tasks.entry.copyButton` |
| Botón Cerrar | Button | Marcar tarea como cerrada | `tasks.entry.closeButton` |
| Botón Reabrir | Button | Marcar tarea como abierta | `tasks.entry.reopenButton` |

#### Datos por Tarea

| Campo | Tipo | Descripción | Validaciones | Test ID | Visible para |
|-------|------|-------------|--------------|---------|--------------|
| Asistente | Select | Usuario propietario de la tarea | Requerido, debe existir | `tasks.entry.form.assistantSelect` | Solo Supervisor |
| Cliente | Select | Cliente asociado | Requerido, debe existir y estar activo | `tasks.entry.form.clientSelect` | Todos |
| Fecha | DateInput | Fecha de la tarea | Requerido, formato YYYY-MM-DD, no futura | `tasks.entry.form.dateInput` | Todos |
| Horario | NumberInput | Duración en minutos (rangos de 15 min) | Requerido, > 0, <= 1440, múltiplo de 15 | `tasks.entry.form.durationInput` | Todos |
| Tipo de Tarea | Select | Tipo de tarea realizada | Requerido, debe existir y estar activo | `tasks.entry.form.taskTypeSelect` | Todos |
| Descripción | Textarea | Observaciones sobre la tarea | Opcional, max 1000 caracteres | `tasks.entry.form.observacionTextarea` | Todos |
| Sin Cargo | Checkbox | Indica si la tarea es sin cargo | Boolean, default: false | `tasks.entry.form.sinCargoCheckbox` | Todos |
| Presencial | Checkbox | Indica si la tarea es presencial | Boolean, default: false | `tasks.entry.form.presencialCheckbox` | Todos |

#### Validaciones Frontend

- Fecha no puede ser futura
- Horario debe ser múltiplo de 15 minutos
- Horario máximo: 1440 minutos (24 horas)
- Descripción máximo 1000 caracteres
- Cliente debe estar activo y no inhabilitado
- Tipo de tarea debe estar activo y no inhabilitado

#### Estados

- **Estado inicial:** Filtros con valores por defecto (período actual)
- **Estado de carga:** Skeleton loader en tabla
- **Estado vacío:** Mensaje "No hay tareas en el período seleccionado"
- **Estado de error:** Mensaje de error con opción de reintentar

#### Integración con API

- **Endpoints utilizados:**
  - `GET /api/v1/tareas?fecha_desde={fecha_desde}&fecha_hasta={fecha_hasta}&usuario_id={usuario_id}` - Listar tareas
  - `POST /api/v1/tareas` - Crear tarea
  - `PUT /api/v1/tareas/{id}` - Actualizar tarea
  - `DELETE /api/v1/tareas/{id}` - Eliminar tarea
  - `GET /api/v1/clientes` - Listar clientes para select
  - `GET /api/v1/tipos-tarea` - Listar tipos de tarea para select
  - `GET /api/v1/usuarios` - Listar asistentes (solo para supervisores)

#### Notas de Implementación

- El campo "Asistente" solo es visible y editable para usuarios con `supervisor = true`
- Para usuarios normales, el `usuario_id` se asigna automáticamente desde el token
- El tipo de tarea por defecto debe seleccionarse automáticamente al crear nueva tarea
- La fecha por defecto es la fecha actual
- El horario debe presentarse en rangos de 15 minutos (dropdown o input con step=15)

---

### Pantalla: Proceso Masivo de Tareas

- **Ruta:** `/procesos/proceso-masivo-tareas`
- **Componente:** `BulkTaskProcessPage`
- **Tipo de usuario:** Supervisor (exclusivo)
- **Autenticación requerida:** Sí

#### Permisos

- Solo accesible para usuarios con `supervisor = true`
- Si un usuario normal intenta acceder, mostrar error 403

#### Elementos de la Pantalla

| Elemento | Tipo | Descripción | Test ID |
|----------|------|-------------|---------|
| Filtro Fecha Desde | DateInput | Fecha inicial del período | `tasks.bulk.filterDateFrom` |
| Filtro Fecha Hasta | DateInput | Fecha final del período | `tasks.bulk.filterDateTo` |
| Filtro Cliente | Select | Cliente específico o "Todos" | `tasks.bulk.filterClient` |
| Filtro Asistente | Select | Asistente específico o "Todos" | `tasks.bulk.filterAssistant` |
| Filtro Estado | RadioGroup | Cerrados / Abiertos | `tasks.bulk.filterStatus` |
| Botón Aplicar Filtros | Button | Aplicar filtros y cargar tareas | `tasks.bulk.applyFiltersButton` |
| Checkbox "Seleccionar Todos" | Checkbox | Seleccionar/deseleccionar todas las tareas | `tasks.bulk.selectAllCheckbox` |
| Lista de Tareas | DataTable | Tabla con checkboxes por fila | `tasks.bulk.table` |
| Checkbox por Fila | Checkbox | Seleccionar tarea individual | `tasks.bulk.table.row.{id}.checkbox` |
| Botón Procesar | Button | Invertir estado cerrado/abierto de tareas seleccionadas | `tasks.bulk.processButton` |

#### Validaciones

- Fecha desde <= Fecha hasta
- Al menos una tarea debe estar seleccionada para procesar
- El botón "Procesar" NO se debe activar si no hay ningún registro activo seleccionado

#### Acciones

1. **Aplicar Filtros:**
   - Validar filtros
   - Cargar tareas que cumplan los criterios
   - Mostrar en tabla con checkboxes

2. **Seleccionar Todos/Ninguno:**
   - Toggle de todos los checkboxes

3. **Procesar:**
   - Validar que hay tareas seleccionadas
   - Invertir estado `cerrado/abierto` de todas las tareas seleccionadas
   - Mostrar mensaje de éxito: "Se procesaron {cantidad} registros"
   - Si hay error, mostrar mensaje específico

#### Estados

- **Estado inicial:** Filtros vacíos o con valores por defecto
- **Estado de carga:** Skeleton loader
- **Estado vacío:** Mensaje "No hay tareas que cumplan los filtros"
- **Estado de éxito:** Toast con mensaje "Se procesaron X registros"
- **Estado de error:** Mensaje de error específico

#### Integración con API

- **Endpoints utilizados:**
  - `GET /api/v1/tareas?fecha_desde={fecha_desde}&fecha_hasta={fecha_hasta}&cliente_id={cliente_id}&usuario_id={usuario_id}` - Listar tareas con filtros
  - `PUT /api/v1/tareas/bulk` - Procesamiento masivo (nuevo endpoint a crear)

#### Notas de Implementación

- El endpoint de procesamiento masivo debe recibir un array de IDs de tareas y el estado objetivo
- Considerar implementar confirmación modal antes de procesar
- Mostrar progreso si el procesamiento es asíncrono

---

### Pantalla: Consultas (Patrón Común)

- **Ruta:** `/consultas/{tipo-consulta}`
- **Componente:** `QueryPage`
- **Tipo de usuario:** Empleado / Supervisor / Cliente
- **Autenticación requerida:** Sí

#### Estructura Común

Todas las consultas siguen el mismo patrón:
1. Seleccionar filtros
2. Aplicar filtros y cargar resultados
3. Visualizar resultados (agrupados o detallados)
4. Exportar a Excel (opcional)

#### Filtros Comunes

| Elemento | Tipo | Descripción | Test ID |
|----------|------|-------------|---------|
| Filtro Período Desde | DateInput | Fecha inicial | `queries.common.filterDateFrom` |
| Filtro Período Hasta | DateInput | Fecha final | `queries.common.filterDateTo` |
| Filtro Tipo de Cliente | Select | Tipo específico o "Todos" | `queries.common.filterClientType` |
| Filtro Cliente | Select | Cliente específico o "Todos" | `queries.common.filterClient` |
| Filtro Asistente | Select | Asistente específico o "Todos" | `queries.common.filterAssistant` |
| Botón Aplicar Filtros | Button | Aplicar y cargar resultados | `queries.common.applyFiltersButton` |
| Botón Exportar Excel | Button | Exportar resultados a Excel | `queries.common.exportButton` |

#### Validaciones

- Fecha desde <= Fecha hasta
- Valores por defecto: "Todos" en todos los filtros

---

### Pantalla: Detalle de Tareas

- **Ruta:** `/consultas/detalle-tareas`
- **Componente:** `TaskDetailQueryPage`

#### Características Especiales

- **No agrupa:** Muestra lista plana de todas las tareas que cumplen los filtros
- **Formato de horas:** Decimal (convertir minutos a decimales: minutos / 60)

#### Elementos de la Pantalla

| Elemento | Tipo | Descripción | Test ID |
|----------|------|-------------|---------|
| Filtros (comunes) | - | Ver filtros comunes | - |
| Lista de Resultados | DataTable | Tabla con todas las tareas | `queries.detail.table` |
| Columna Horas | Number | Horas en formato decimal | `queries.detail.table.hours` |

#### Columnas de la Tabla

- Asistente
- Cliente
- Fecha
- Tipo de Tarea
- Horas (decimal)
- Sin Cargo
- Presencial
- Descripción

---

### Pantalla: Tareas por Asistente

- **Ruta:** `/consultas/tareas-por-asistente`
- **Componente:** `TasksByAssistantQueryPage`

#### Características Especiales

- **Agrupa por:** Asistente
- **Totaliza:** Horas en formato decimal por asistente
- **Expandible:** Cada asistente puede expandirse para ver detalle de sus tareas

#### Elementos de la Pantalla

| Elemento | Tipo | Descripción | Test ID |
|----------|------|-------------|---------|
| Filtros (comunes) | - | Ver filtros comunes | - |
| Lista Agrupada | Accordion/Table | Agrupación por asistente | `queries.byAssistant.groupedList` |
| Total por Asistente | Number | Horas totales en decimal | `queries.byAssistant.total.{assistantId}` |
| Botón Expandir | Button | Ver detalle del asistente | `queries.byAssistant.expand.{assistantId}` |
| Detalle Expandido | DataTable | Lista de tareas del asistente | `queries.byAssistant.detail.{assistantId}` |

---

### Pantalla: Tareas por Cliente

- **Ruta:** `/consultas/tareas-por-cliente`
- **Componente:** `TasksByClientQueryPage`

#### Características Especiales

- **Agrupa por:** Cliente
- **Totaliza:** Horas en formato decimal por cliente
- **Expandible:** Cada cliente puede expandirse para ver detalle de sus tareas

#### Elementos de la Pantalla

| Elemento | Tipo | Descripción | Test ID |
|----------|------|-------------|---------|
| Filtros (comunes) | - | Ver filtros comunes | - |
| Lista Agrupada | Accordion/Table | Agrupación por cliente | `queries.byClient.groupedList` |
| Total por Cliente | Number | Horas totales en decimal | `queries.byClient.total.{clientId}` |
| Botón Expandir | Button | Ver detalle del cliente | `queries.byClient.expand.{clientId}` |
| Detalle Expandido | DataTable | Lista de tareas del cliente | `queries.byClient.detail.{clientId}` |

---

### Pantalla: Tareas por Tipo

- **Ruta:** `/consultas/tareas-por-tipo`
- **Componente:** `TasksByTypeQueryPage`

#### Características Especiales

- **Agrupa por:** Tipo de Tarea
- **Totaliza:** Horas en formato decimal por tipo
- **Expandible:** Cada tipo puede expandirse para ver detalle de tareas

#### Elementos de la Pantalla

| Elemento | Tipo | Descripción | Test ID |
|----------|------|-------------|---------|
| Filtros (comunes) | - | Ver filtros comunes | - |
| Lista Agrupada | Accordion/Table | Agrupación por tipo | `queries.byType.groupedList` |
| Total por Tipo | Number | Horas totales en decimal | `queries.byType.total.{typeId}` |
| Botón Expandir | Button | Ver detalle del tipo | `queries.byType.expand.{typeId}` |
| Detalle Expandido | DataTable | Lista de tareas del tipo | `queries.byType.detail.{typeId}` |

---

### Pantalla: Tareas por Fecha

- **Ruta:** `/consultas/tareas-por-fecha`
- **Componente:** `TasksByDateQueryPage`

#### Características Especiales

- **Agrupa por:** Fecha
- **Totaliza:** Horas en formato decimal por fecha
- **Expandible:** Cada fecha puede expandirse para ver detalle de tareas

#### Elementos de la Pantalla

| Elemento | Tipo | Descripción | Test ID |
|----------|------|-------------|---------|
| Filtros (comunes) | - | Ver filtros comunes | - |
| Lista Agrupada | Accordion/Table | Agrupación por fecha | `queries.byDate.groupedList` |
| Total por Fecha | Number | Horas totales en decimal | `queries.byDate.total.{date}` |
| Botón Expandir | Button | Ver detalle de la fecha | `queries.byDate.expand.{date}` |
| Detalle Expandido | DataTable | Lista de tareas de la fecha | `queries.byDate.detail.{date}` |

---

### Pantalla: Dashboard

- **Ruta:** `/dashboard`
- **Componente:** `DashboardPage`
- **Tipo de usuario:** Todos
- **Autenticación requerida:** Sí

#### Notas de Implementación

- **Pendiente de especificación detallada**
- Debe mostrar resumen ejecutivo del sistema

---

## Convenciones y Reglas Técnicas

### Test IDs

**Formato:** `{feature}.{component}.{element}.{actionOrState}`

**Ejemplos:**
- `tasks.entry.form.dateInput`
- `archivos.clientes.table.code`
- `queries.byAssistant.expand.123`

### Formato de Horas

- **Almacenamiento:** Minutos (integer)
- **Visualización:** Decimal (minutos / 60)
- **Ejemplo:** 90 minutos = 1.5 horas

### Validaciones Frontend vs Backend

- **Frontend:** Validaciones para UX (feedback inmediato)
- **Backend:** Validaciones como fuente de verdad (obligatorias)

### Accesibilidad

- Todos los controles deben tener `data-testid`
- Labels asociados con `htmlFor`
- Atributos ARIA apropiados (`aria-required`, `aria-invalid`, `role="alert"`)
- Navegación por teclado funcional

### Estados de UI

- **Loading:** Skeleton loader o spinner
- **Empty:** Mensaje descriptivo con acción sugerida
- **Error:** Mensaje claro con opción de reintentar
- **Success:** Toast o mensaje de confirmación

---

**Última actualización:** 2025-01-20  
**Versión:** 1.0
