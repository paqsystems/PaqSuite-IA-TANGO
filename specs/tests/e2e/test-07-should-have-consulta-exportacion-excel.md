# Test E2E Should-Have: Consulta y Exportación a Excel

## Descripción

Test End-to-End del flujo completo de consulta de tareas con diferentes criterios y exportación de resultados a Excel.

## Historia de Usuario

**Como** usuario del sistema  
**Quiero** consultar tareas con diferentes criterios y exportar los resultados a Excel  
**Para** analizar los datos fuera del sistema o compartirlos

## Criterios de Aceptación

- El usuario puede acceder a diferentes tipos de consultas
- El usuario puede aplicar filtros complejos
- El usuario puede ver resultados agrupados o detallados
- El usuario puede exportar resultados a Excel
- El archivo Excel contiene los datos correctos
- El formato de horas es decimal en el Excel

## Flujo Completo

### Paso 1: Acceso a Consulta
1. Usuario autenticado navega a `/consultas/{tipo-consulta}`
2. Verificar que la página se cargue
3. Verificar que se muestren filtros apropiados
4. Verificar que valores por defecto sean "Todos"

### Paso 2: Aplicación de Filtros
1. Usuario selecciona período
2. Usuario selecciona tipo de cliente (todos o específico)
3. Usuario selecciona cliente (todos o específico)
4. Usuario selecciona asistente (todos o específico)
5. Usuario hace clic en "Aplicar Filtros"
6. Verificar que se carguen resultados

### Paso 3: Visualización de Resultados
1. Verificar que se muestren resultados según tipo de consulta
2. Si es consulta agrupada, verificar agrupación y totales
3. Si es detalle, verificar lista plana
4. Verificar formato de horas decimal

### Paso 4: Expansión de Grupos (si aplica)
1. Usuario hace clic en expandir grupo
2. Verificar que se muestren detalles del grupo
3. Verificar formato de datos en detalles

### Paso 5: Exportación a Excel
1. Usuario hace clic en "Exportar a Excel"
2. Verificar que se genere el archivo
3. Verificar que se descargue el archivo
4. Abrir archivo Excel y verificar contenido

## Casos de Prueba

### Caso 1: Consulta Detalle de Tareas
- **Precondición:** Usuario autenticado
- **Acción:** Acceder a "Detalle de Tareas", aplicar filtros, ver resultados
- **Resultado Esperado:**
  - Lista plana de todas las tareas
  - Datos correctos en cada columna
  - Horas en formato decimal

### Caso 2: Consulta Agrupada por Asistente
- **Precondición:** Usuario autenticado
- **Acción:** Acceder a "Tareas por Asistente", aplicar filtros
- **Resultado Esperado:**
  - Resultados agrupados por asistente
  - Total de horas por asistente
  - Posibilidad de expandir cada grupo

### Caso 3: Expansión de Grupo
- **Precondición:** Consulta agrupada cargada
- **Acción:** Expandir un grupo (ej: asistente)
- **Resultado Esperado:**
  - Detalle de tareas del grupo visible
  - Horas en formato decimal
  - Datos correctos

### Caso 4: Exportación de Consulta Simple
- **Precondición:** Consulta con resultados
- **Acción:** Hacer clic en "Exportar a Excel"
- **Resultado Esperado:**
  - Archivo Excel descargado
  - Nombre de archivo descriptivo
  - Datos correctos en el archivo
  - Formato de horas decimal

### Caso 5: Exportación de Consulta Agrupada
- **Precondición:** Consulta agrupada con resultados
- **Acción:** Exportar a Excel
- **Resultado Esperado:**
  - Archivo con estructura de agrupación
  - Totales incluidos
  - Detalles expandidos incluidos
  - Formato preservado

### Caso 6: Exportación sin Resultados
- **Precondición:** Consulta sin resultados
- **Acción:** Intentar exportar
- **Resultado Esperado:**
  - Mensaje: "No hay datos para exportar"
  - No se genera archivo

### Caso 7: Filtros Complejos en Consulta
- **Precondición:** Usuario autenticado
- **Acción:** Aplicar múltiples filtros (período + tipo cliente + cliente + asistente)
- **Resultado Esperado:**
  - Solo resultados que cumplen todos los filtros
  - Datos correctos y actualizados

## Validaciones

- Consultas se cargan correctamente
- Filtros funcionan
- Agrupación funciona (si aplica)
- Expansión funciona (si aplica)
- Exportación genera archivo correcto
- Formato de horas decimal en Excel
- Datos exportados son correctos

## Elementos UI a Verificar

- Filtros comunes (`queries.common.filterDateFrom`, `queries.common.filterDateTo`, `queries.common.filterClientType`, `queries.common.filterClient`, `queries.common.filterAssistant`)
- Botón aplicar filtros (`queries.common.applyFiltersButton`)
- Lista de resultados (`queries.{tipo}.table` o `queries.{tipo}.groupedList`)
- Botón expandir (`queries.{tipo}.expand.{id}`)
- Botón exportar (`queries.common.exportButton`)
- Estado vacío (`queries.{tipo}.emptyState`)

