# Documentación: Sidebar.tsx

## Ubicación
`frontend/src/app/Sidebar.tsx`

## Propósito
Componente del menú lateral de navegación (TR-056 / HU-056). Visible en todas las pantallas autenticadas; ítems mostrados según rol (empleado, supervisor, cliente). Orden: Inicio, Perfil | Archivos (supervisor) | Partes | Informes.

## Uso
- Usado en `AppLayout`: se renderiza junto al contenido principal; en desktop forma parte del layout flex; en móvil (< 769px) es overlay colapsable (botón hamburguesa en header).
- Props: `isOpen` (control del estado en móvil), `onClose` (al hacer clic en enlace o overlay).

## Estructura del menú
- **Inicio** (`/`), **Perfil** (`/perfil`).
- **Archivos** (solo supervisor): Clientes, Empleados, Tipos de Cliente, Tipos de Tarea.
- **Partes**: Cargar Tarea, Mis Tareas (empleado); Todas las Tareas, Proceso Masivo (supervisor).
- **Informes**: Consulta Detallada, Tareas por Cliente, Tareas por Fecha; + Tareas por Empleado, Tareas por Tipo (supervisor).

## data-testid
- `app.sidebar`, `app.sidebar.inicio`, `app.profileLink`, `app.clientesLink`, `app.empleadosLink`, `app.tiposClienteLink`, `app.tiposTareaLink`, `app.createTaskLink`, `app.myTasksLink`, `app.todasTareasLink`, `app.procesoMasivoLink`, `app.consultaDetalladaLink`, `app.tareasPorClienteLink`, `app.tareasPorFechaLink`, `app.tareasPorEmpleadoLink`, `app.tareasPorTipoLink`.

## Dependencias
- HU-056(SH), TR-056; `getUserData()` (tokenStorage); `AppLayout`.
