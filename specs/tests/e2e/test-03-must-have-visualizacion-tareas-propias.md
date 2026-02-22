# Test E2E Must-Have: Visualización de Tareas Propias

## Descripción

Test End-to-End del flujo de visualización de tareas del usuario, incluyendo filtrado, paginación y visualización de detalles.

## Historia de Usuario

**Como** empleado del sistema  
**Quiero** ver mis tareas registradas  
**Para** revisar mi trabajo y hacer ajustes si es necesario

## Criterios de Aceptación

- El usuario puede ver sus tareas en una lista
- El usuario puede filtrar tareas por período
- El usuario puede ver detalles de cada tarea
- El usuario solo ve sus propias tareas (no las de otros)
- La paginación funciona correctamente
- Los datos se muestran correctamente

## Flujo Completo

### Paso 1: Acceso a Lista de Tareas
1. Usuario autenticado navega a `/procesos/carga-tareas`
2. Verificar que la página se cargue
3. Verificar que se muestren filtros de período
4. Verificar que se carguen tareas del período actual por defecto

### Paso 2: Visualización de Lista
1. Verificar que la tabla muestre las tareas
2. Verificar columnas: fecha, cliente, tipo de tarea, duración, sin cargo, presencial
3. Verificar que solo se muestren tareas del usuario autenticado
4. Verificar formato de datos (fechas, horas, booleanos)

### Paso 3: Aplicación de Filtros
1. Usuario selecciona fecha desde
2. Usuario selecciona fecha hasta
3. Usuario hace clic en "Aplicar Filtros"
4. Verificar que la lista se actualice con tareas del período

### Paso 4: Paginación
1. Si hay más de una página, verificar que aparezca paginación
2. Usuario hace clic en página 2
3. Verificar que se carguen tareas de la página 2
4. Verificar que la paginación muestre página activa

### Paso 5: Visualización de Detalles
1. Usuario hace clic en botón "Consultar" de una tarea
2. Verificar que se abra modal o vista de detalles
3. Verificar que se muestren todos los datos de la tarea
4. Verificar formato correcto de datos

## Casos de Prueba

### Caso 1: Visualización de Tareas del Período Actual
- **Precondición:** Usuario autenticado con tareas registradas
- **Acción:** Acceder a carga de tareas
- **Resultado Esperado:**
  - Lista muestra tareas del período actual
  - Solo tareas del usuario autenticado
  - Datos correctos en cada columna

### Caso 2: Filtrado por Período
- **Precondición:** Usuario con tareas en diferentes períodos
- **Acción:** Seleccionar período específico y aplicar filtros
- **Resultado Esperado:**
  - Lista muestra solo tareas del período seleccionado
  - Tareas fuera del período no aparecen

### Caso 3: Lista Vacía
- **Precondición:** Usuario sin tareas en el período seleccionado
- **Acción:** Aplicar filtros sin resultados
- **Resultado Esperado:**
  - Mensaje "No hay tareas en el período seleccionado"
  - Botón "Nuevo" visible para crear primera tarea

### Caso 4: Paginación con Múltiples Páginas
- **Precondición:** Usuario con más de 20 tareas (tamaño de página por defecto)
- **Acción:** Navegar entre páginas
- **Resultado Esperado:**
  - Paginación visible
  - Cambio de página carga datos correctos
  - Página activa se marca correctamente

### Caso 5: Ver Detalles de Tarea
- **Precondición:** Lista de tareas cargada
- **Acción:** Hacer clic en "Consultar" de una tarea
- **Resultado Esperado:**
  - Modal o vista de detalles se abre
  - Todos los datos de la tarea visibles
  - Formato correcto (fechas, horas, booleanos)

### Caso 6: Usuario Normal Solo Ve Sus Tareas
- **Precondición:** Usuario normal autenticado, existen tareas de otros usuarios
- **Acción:** Acceder a lista de tareas
- **Resultado Esperado:**
  - Solo se muestran tareas del usuario autenticado
  - No aparecen tareas de otros usuarios

### Caso 7: Supervisor Ve Todas las Tareas
- **Precondición:** Usuario supervisor autenticado
- **Acción:** Acceder a lista de tareas
- **Resultado Esperado:**
  - Se muestran tareas de todos los usuarios
  - Filtro de usuario visible (opcional)
  - Puede filtrar por usuario específico

## Validaciones

- Lista muestra datos correctos
- Filtros funcionan correctamente
- Paginación funciona
- Permisos de usuario aplicados correctamente
- Detalles se muestran correctamente
- Estados de carga y error manejados

## Elementos UI a Verificar

- Filtros de fecha (`tasks.entry.filterDateFrom`, `tasks.entry.filterDateTo`)
- Botón aplicar filtros (`tasks.entry.applyFiltersButton`)
- Tabla de tareas (`tasks.entry.table`)
- Filas de tabla (`tasks.entry.table.row.{id}`)
- Botón consultar (`tasks.entry.table.row.{id}.viewButton`)
- Paginación (`tasks.entry.pagination`)
- Estado vacío (`tasks.entry.emptyState`)
- Modal/vista de detalles (`tasks.entry.detailModal`)

