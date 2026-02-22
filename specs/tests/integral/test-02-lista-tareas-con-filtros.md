# Test Integral: Lista de Tareas con Filtros

## Descripción

Test de integración de la pantalla de lista de tareas, incluyendo carga de datos, aplicación de filtros, y paginación.

## Objetivo

Verificar que la lista de tareas funcione correctamente con todos sus filtros y funcionalidades.

## Componentes Involucrados

- Lista de tareas (`TaskListPage`)
- Filtros de fecha (`DateRangeFilter`)
- Filtro de usuario (solo supervisores)
- Tabla de datos (`DataTable`)
- Servicio de API (`TaskService`)
- Paginación (`Pagination`)

## Flujo de Prueba

### Paso 1: Carga Inicial
1. Renderizar lista de tareas
2. Verificar carga automática de tareas del período actual
3. Verificar que se muestren correctamente

### Paso 2: Aplicación de Filtros
1. Seleccionar filtro de fecha desde
2. Seleccionar filtro de fecha hasta
3. Aplicar filtros
4. Verificar que se recarguen los datos con filtros aplicados

### Paso 3: Paginación
1. Verificar que aparezca paginación si hay más de una página
2. Cambiar de página
3. Verificar que se carguen los datos de la nueva página

### Paso 4: Permisos de Supervisor
1. Con usuario supervisor, verificar filtro de usuario
2. Seleccionar usuario específico
3. Verificar que se filtren solo tareas de ese usuario

## Casos de Prueba

### Caso 1: Carga Inicial
- **Precondición:** Usuario autenticado
- **Acción:** Cargar página de lista
- **Resultado Esperado:**
  - Tareas del período actual cargadas
  - Tabla visible con datos
  - Paginación visible si aplica

### Caso 2: Filtro por Fechas
- **Precondición:** Lista cargada
- **Acción:** Aplicar filtro de fechas
- **Resultado Esperado:**
  - Llamada a API con parámetros de fecha
  - Lista actualizada con tareas del período
  - Filtros mantienen valores seleccionados

### Caso 3: Cambio de Página
- **Precondición:** Lista con múltiples páginas
- **Acción:** Cambiar a página 2
- **Resultado Esperado:**
  - Llamada a API con parámetro `page=2`
  - Lista actualizada con datos de página 2
  - Paginación muestra página activa

### Caso 4: Filtro de Usuario (Supervisor)
- **Precondición:** Usuario supervisor autenticado
- **Acción:** Seleccionar usuario específico en filtro
- **Resultado Esperado:**
  - Llamada a API con `usuario_id`
  - Lista muestra solo tareas de ese usuario

### Caso 5: Estado Vacío
- **Precondición:** Filtros que no retornan resultados
- **Acción:** Aplicar filtros sin resultados
- **Resultado Esperado:**
  - Mensaje "No hay tareas"
  - Tabla vacía o no visible

## Validaciones

- Integración correcta entre filtros y API
- Paginación funciona correctamente
- Permisos de supervisor aplicados
- Estados de carga y error manejados
- Datos se actualizan correctamente

