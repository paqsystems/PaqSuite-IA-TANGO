# Especificaciones de Endpoints

Esta carpeta contiene las especificaciones detalladas de todos los endpoints de la API.

## Organización

Los archivos están organizados por funcionalidad:

### Autenticación
- `auth-login.md`
- `auth-logout.md`

### Clientes
- `clientes-list.md`
- `clientes-create.md`
- `clientes-get.md`
- `clientes-update.md`
- `clientes-delete.md`
- `clientes-tipos-tarea-list.md`
- `clientes-tipos-tarea-assign.md`
- `clientes-tipos-tarea-unassign.md`

### Tipos de Cliente
- `tipos-cliente-list.md`
- `tipos-cliente-create.md`
- `tipos-cliente-get.md`
- `tipos-cliente-update.md`
- `tipos-cliente-delete.md`

### Empleados
- `empleados-list.md`
- `empleados-create.md`
- `empleados-get.md`
- `empleados-update.md`
- `empleados-delete.md`

### Tipos de Tarea
- `tipos-tarea-create.md`
- `tipos-tarea-get.md`
- `tipos-tarea-update.md`
- `tipos-tarea-delete.md`

### Registro de Tareas
- `time-entries-create.md`
- `time-entries-list.md`
- `time-entries-get.md`
- `time-entries-update.md`
- `time-entries-delete.md`
- `time-entries-tipos-disponibles.md`

### Proceso Masivo
- `tareas-proceso-masivo-list.md`
- `tareas-proceso-masivo-process.md`

### Informes
- `informes-detalle.md`
- `informes-por-empleado.md`
- `informes-por-cliente.md`
- `informes-por-tipo.md`
- `informes-por-fecha.md`
- `informes-exportar.md`

### Dashboard
- `dashboard-resumen.md`
- `dashboard-por-cliente.md`
- `dashboard-por-empleado.md`

## Formato

Cada especificación incluye:
- Método HTTP y ruta
- Autenticación requerida
- Request (headers, body, parámetros)
- Response (éxito y errores)
- Validaciones
- Ejemplos de uso

## Referencias

- **Contrato base:** `specs/contracts/response-envelope.md`
- **Códigos de error:** `specs/errors/domain-error-codes.md`
- **Reglas de validación:** `specs/rules/validation-rules.md`

