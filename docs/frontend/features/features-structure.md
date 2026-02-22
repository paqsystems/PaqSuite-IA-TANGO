# Estructura de Features - Frontend

## Descripción General

Este documento describe la estructura de features del frontend y las especificaciones de componentes principales.

---

## Estructura de Carpetas

```
frontend/src/
├── shared/
│   ├── ui/              # Componentes UI base (Button, TextField, DataTable, Modal)
│   └── i18n/            # Infraestructura de internacionalización
├── features/            # Features del dominio
│   ├── auth/            # Autenticación
│   ├── user/            # Configuración de usuario
│   ├── clientes/        # Gestión de clientes
│   ├── tipos-cliente/   # Gestión de tipos de cliente
│   ├── asistentes/      # Gestión de asistentes/empleados
│   ├── tipos-tarea/     # Gestión de tipos de tarea
│   ├── tareas/          # Registro de tareas
│   ├── proceso-masivo/  # Proceso masivo de tareas
│   ├── informes/        # Informes y consultas
│   └── dashboard/       # Dashboard
├── pages/               # Páginas/vistas principales
├── services/            # Servicios API
├── utils/               # Utilidades y helpers
├── hooks/               # Custom hooks
└── styles/              # Estilos globales
```

---

## Feature: Auth (Autenticación)

### Componentes

#### LoginForm
- **Ruta:** `/login`
- **Props:** Ninguna
- **Estado:** `usuario`, `password`, `loading`, `error`
- **Acciones:** `handleSubmit`, `handleChange`
- **Test ID:** `auth.login.form`, `auth.login.usuarioInput`, `auth.login.passwordInput`, `auth.login.submitButton`, `auth.login.errorMessage`

#### LogoutButton
- **Ubicación:** Header/Navbar
- **Test ID:** `auth.logout.button`
- **Acción:** Cierra sesión y redirige a login

#### LoginClienteForm (Opcional)
- **Ruta:** `/login-cliente`
- **Similar a LoginForm pero para clientes**

---

## Feature: User (Configuración de Usuario)

### Componentes

#### PerfilUsuario
- **Ruta:** `/perfil`
- **Muestra:** código, nombre, email, rol
- **Test ID:** `user.profile.container`, `user.profile.code`, `user.profile.nombre`, `user.profile.email`

#### EditarPerfilForm (Opcional)
- **Ruta:** `/perfil/editar`
- **Permite editar:** nombre, email
- **Test ID:** `user.profile.editForm`, `user.profile.editNombre`, `user.profile.editEmail`, `user.profile.saveButton`

---

## Feature: Clientes

### Componentes

#### ClientesList
- **Ruta:** `/clientes`
- **Funcionalidad:** Listado con tabla, filtros, paginación
- **Test ID:** `clientes.list.container`, `clientes.list.table`, `clientes.list.searchInput`, `clientes.list.filterTipoCliente`, `clientes.list.createButton`, `clientes.list.row.{id}`, `clientes.list.row.{id}.editButton`, `clientes.list.row.{id}.deleteButton`

#### ClienteForm
- **Ruta:** `/clientes/crear`, `/clientes/{id}/editar`
- **Funcionalidad:** Formulario de creación/edición
- **Test ID:** `clientes.form.container`, `clientes.form.codeInput`, `clientes.form.nombreInput`, `clientes.form.tipoClienteSelect`, `clientes.form.emailInput`, `clientes.form.passwordInput`, `clientes.form.activoCheckbox`, `clientes.form.submitButton`

#### ClienteDetail (Opcional)
- **Ruta:** `/clientes/{id}`
- **Muestra:** Detalle completo del cliente

#### AsignarTiposTarea
- **Ruta:** `/clientes/{id}/tipos-tarea`
- **Funcionalidad:** Gestión de tipos de tarea asignados
- **Test ID:** `clientes.tiposTarea.container`, `clientes.tiposTarea.availableList`, `clientes.tiposTarea.assignedList`, `clientes.tiposTarea.assignButton`, `clientes.tiposTarea.unassignButton`

---

## Feature: Tipos Cliente

### Componentes

#### TiposClienteList
- **Ruta:** `/tipos-cliente`
- **Test ID:** `tiposCliente.list.container`, `tiposCliente.list.table`, `tiposCliente.list.createButton`, `tiposCliente.list.row.{id}.editButton`, `tiposCliente.list.row.{id}.deleteButton`

#### TipoClienteForm
- **Ruta:** `/tipos-cliente/crear`, `/tipos-cliente/{id}/editar`
- **Test ID:** `tiposCliente.form.container`, `tiposCliente.form.codeInput`, `tiposCliente.form.descripcionInput`, `tiposCliente.form.submitButton`

---

## Feature: Asistentes

### Componentes

#### AsistentesList
- **Ruta:** `/asistentes`
- **Test ID:** `asistentes.list.container`, `asistentes.list.table`, `asistentes.list.createButton`, `asistentes.list.row.{id}.editButton`, `asistentes.list.row.{id}.deleteButton`

#### AsistenteForm
- **Ruta:** `/asistentes/crear`, `/asistentes/{id}/editar`
- **Test ID:** `asistentes.form.container`, `asistentes.form.codeInput`, `asistentes.form.nombreInput`, `asistentes.form.emailInput`, `asistentes.form.passwordInput`, `asistentes.form.supervisorCheckbox`, `asistentes.form.submitButton`

#### AsistenteDetail (Opcional)
- **Ruta:** `/asistentes/{id}`

---

## Feature: Tipos Tarea

### Componentes

#### TiposTareaList
- **Ruta:** `/tipos-tarea`
- **Test ID:** `tiposTarea.list.container`, `tiposTarea.list.table`, `tiposTarea.list.createButton`, `tiposTarea.list.row.{id}.editButton`, `tiposTarea.list.row.{id}.deleteButton`

#### TipoTareaForm
- **Ruta:** `/tipos-tarea/crear`, `/tipos-tarea/{id}/editar`
- **Lógica especial:** Validación de único tipo por defecto, forzar genérico si es por defecto
- **Test ID:** `tiposTarea.form.container`, `tiposTarea.form.codeInput`, `tiposTarea.form.descripcionInput`, `tiposTarea.form.genericoCheckbox`, `tiposTarea.form.defaultCheckbox`, `tiposTarea.form.submitButton`, `tiposTarea.form.errorMessage`

---

## Feature: Tareas

### Componentes

#### TareaForm
- **Ruta:** `/tareas/crear`, `/tareas/{id}/editar`
- **Funcionalidad:** Formulario completo de registro/edición de tarea
- **Campos:** fecha, cliente, tipo de tarea (dinámico), duración, sin cargo, presencial, observación, usuario (solo supervisor)
- **Validaciones:** duración en tramos de 15 min, fecha futura (advertencia), observación obligatoria
- **Test ID:** `tareas.form.container`, `tareas.form.dateInput`, `tareas.form.clienteSelect`, `tareas.form.tipoTareaSelect`, `tareas.form.duracionInput`, `tareas.form.sinCargoCheckbox`, `tareas.form.presencialCheckbox`, `tareas.form.observacionTextarea`, `tareas.form.usuarioSelect` (solo supervisor), `tareas.form.submitButton`, `tareas.form.errorMessage`, `tareas.form.warningMessage`

#### TareasList
- **Ruta:** `/tareas`
- **Funcionalidad:** Listado con filtros y paginación
- **Filtros:** fecha desde/hasta, cliente, tipo de tarea, asistente (solo supervisor)
- **Test ID:** `tareas.list.container`, `tareas.list.table`, `tareas.list.filterDateFrom`, `tareas.list.filterDateTo`, `tareas.list.filterCliente`, `tareas.list.filterAsistente` (solo supervisor), `tareas.list.filterTipoTarea`, `tareas.list.applyFiltersButton`, `tareas.list.row.{id}`, `tareas.list.row.{id}.editButton`, `tareas.list.row.{id}.deleteButton`, `tareas.list.emptyState`

---

## Feature: Proceso Masivo

### Componentes

#### BulkTaskProcessPage
- **Ruta:** `/procesos/proceso-masivo-tareas`
- **Permisos:** Solo supervisores
- **Test ID:** `tasks.bulk.page`, `tasks.bulk.accessDenied` (si no es supervisor)

#### BulkFilters
- **Componente:** Filtros para proceso masivo
- **Filtros:** fecha desde/hasta, cliente, asistente, estado (cerrado/abierto)
- **Test ID:** `tasks.bulk.filterDateFrom`, `tasks.bulk.filterDateTo`, `tasks.bulk.filterClient`, `tasks.bulk.filterAssistant`, `tasks.bulk.filterStatus`, `tasks.bulk.applyFiltersButton`

#### SelectableTaskTable
- **Componente:** Tabla con checkboxes para selección múltiple
- **Funcionalidad:** Seleccionar individual, seleccionar todos, deseleccionar todos
- **Test ID:** `tasks.bulk.table`, `tasks.bulk.table.row.{id}`, `tasks.bulk.table.row.{id}.checkbox`, `tasks.bulk.table.selectAllCheckbox`, `tasks.bulk.table.deselectAllButton`, `tasks.bulk.table.selectedCount`

#### ProcessButton
- **Componente:** Botón de procesamiento
- **Estado:** Deshabilitado si no hay selección
- **Test ID:** `tasks.bulk.processButton`, `tasks.bulk.processButton.disabled` (cuando no hay selección)

---

## Feature: Informes

### Componentes

#### ConsultaDetallePage
- **Ruta:** `/consultas/detalle`
- **Funcionalidad:** Consulta detallada de tareas
- **Test ID:** `informes.detalle.page`, `informes.detalle.filters`, `informes.detalle.table`, `informes.detalle.exportButton`

#### ConsultaAgrupadaPage
- **Ruta:** `/consultas/por-asistente`, `/consultas/por-cliente`, `/consultas/por-tipo`, `/consultas/por-fecha`
- **Funcionalidad:** Consulta agrupada con expansión
- **Test ID:** `informes.agrupado.page`, `informes.agrupado.group.{id}`, `informes.agrupado.group.{id}.expandButton`, `informes.agrupado.group.{id}.total`, `informes.agrupado.group.{id}.detail`, `informes.agrupado.exportButton`

#### FiltrosConsulta
- **Componente:** Filtros comunes para todas las consultas
- **Filtros:** período, tipo de cliente (solo supervisor), cliente (solo supervisor), asistente (solo supervisor), tipo de tarea
- **Test ID:** `informes.filters.dateFrom`, `informes.filters.dateTo`, `informes.filters.tipoCliente`, `informes.filters.cliente`, `informes.filters.asistente`, `informes.filters.tipoTarea`, `informes.filters.applyButton`

#### ExportarExcelButton
- **Componente:** Botón de exportación
- **Estado:** Deshabilitado si no hay resultados
- **Test ID:** `informes.exportButton`, `informes.exportButton.disabled`

---

## Feature: Dashboard

### Componentes

#### DashboardPage
- **Ruta:** `/dashboard`
- **Funcionalidad:** Dashboard principal con KPIs y resúmenes
- **Test ID:** `dashboard.page`, `dashboard.periodoSelector`, `dashboard.refreshButton`, `dashboard.lastUpdate`

#### KPICard
- **Componente:** Tarjeta de indicador clave
- **Props:** `title`, `value`, `unit`, `icon` (opcional)
- **Test ID:** `dashboard.kpi.{nombre}` (ej: `dashboard.kpi.totalHoras`, `dashboard.kpi.cantidadTareas`)

#### ResumenPorCliente
- **Componente:** Lista de top clientes
- **Test ID:** `dashboard.porCliente.container`, `dashboard.porCliente.list`, `dashboard.porCliente.item.{id}`, `dashboard.porCliente.item.{id}.total`, `dashboard.porCliente.item.{id}.link`

#### ResumenPorAsistente
- **Componente:** Lista de top asistentes (solo supervisor)
- **Test ID:** `dashboard.porAsistente.container`, `dashboard.porAsistente.list`, `dashboard.porAsistente.item.{id}`

#### GraficoDistribucion
- **Componente:** Gráfico de distribución (Chart.js, Recharts, etc.)
- **Props:** `data`, `type` ("bar", "pie", "line")
- **Test ID:** `dashboard.grafico.{tipo}` (ej: `dashboard.grafico.porCliente`, `dashboard.grafico.porTipo`)

---

## Convenciones de Estructura por Feature

Cada feature debe seguir esta estructura:

```
features/{feature-name}/
├── components/          # Componentes específicos del feature
│   ├── {ComponentName}/
│   │   ├── {ComponentName}.tsx
│   │   ├── {ComponentName}.module.css
│   │   ├── {ComponentName}.test.tsx
│   │   └── index.ts
├── hooks/               # Custom hooks del feature
│   └── use{FeatureName}.ts
├── services/            # Servicios API del feature (opcional, puede estar en /services)
│   └── {feature}Service.ts
├── types/              # Tipos TypeScript del feature
│   └── {feature}Types.ts
└── index.ts            # Exports públicos del feature
```

---

## Servicios API

Todos los servicios deben estar en `frontend/src/services/`:

- `AuthService.ts` - Autenticación
- `ClienteService.ts` - Gestión de clientes
- `TipoClienteService.ts` - Gestión de tipos de cliente
- `AsistenteService.ts` - Gestión de asistentes
- `TipoTareaService.ts` - Gestión de tipos de tarea
- `TareaService.ts` - Gestión de tareas
- `BulkTaskProcessService.ts` - Proceso masivo
- `InformeService.ts` - Informes y consultas
- `DashboardService.ts` - Dashboard

---

## Páginas Principales

Las páginas principales deben estar en `frontend/src/pages/`:

- `LoginPage.tsx` - Página de login
- `DashboardPage.tsx` - Dashboard (wrapper del componente)
- `ClientesPage.tsx` - Gestión de clientes
- `TareasPage.tsx` - Registro y listado de tareas
- `InformesPage.tsx` - Informes y consultas
- etc.

---

## Referencias

- Especificaciones de UI: `specs/ui/screen-specifications.md`
- UI Layer Wrappers: `docs/frontend/ui-layer-wrappers.md`
- i18n: `docs/frontend/i18n.md`
- Testing: `docs/frontend/testing.md`

---

**Última actualización:** 2025-01-20

