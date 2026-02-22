# Test Integral: Consulta Agrupada con Expansión

## Descripción

Test de integración de consultas agrupadas (por asistente, cliente, tipo, fecha) con funcionalidad de expansión para ver detalles.

## Objetivo

Verificar que las consultas agrupadas funcionen correctamente, mostrando totales y permitiendo expandir para ver detalles.

## Componentes Involucrados

- Página de consulta (`QueryPage`)
- Filtros de consulta (`QueryFilters`)
- Componente de agrupación (`GroupedResults`)
- Componente de expansión (`ExpandableRow`)
- Servicio de API (`TaskService`)

## Flujo de Prueba

### Paso 1: Aplicación de Filtros
1. Seleccionar filtros (período, tipo de cliente, cliente, asistente)
2. Aplicar filtros
3. Verificar carga de resultados agrupados

### Paso 2: Visualización Agrupada
1. Verificar que los resultados estén agrupados por criterio
2. Verificar que se muestren totales en formato decimal
3. Verificar formato de horas (decimal)

### Paso 3: Expansión de Grupo
1. Hacer clic en botón de expandir de un grupo
2. Verificar que se muestren detalles del grupo
3. Verificar formato de horas en detalles

### Paso 4: Colapso de Grupo
1. Hacer clic nuevamente para colapsar
2. Verificar que los detalles se oculten

## Casos de Prueba

### Caso 1: Consulta por Asistente
- **Precondición:** Filtros aplicados
- **Acción:** Cargar consulta "Tareas por Asistente"
- **Resultado Esperado:**
  - Resultados agrupados por asistente
  - Total de horas en decimal por asistente
  - Botón de expandir en cada grupo

### Caso 2: Expansión de Grupo
- **Precondición:** Consulta cargada con grupos
- **Acción:** Expandir un grupo de asistente
- **Resultado Esperado:**
  - Detalle de tareas del asistente visible
  - Horas en formato decimal
  - Datos correctos del asistente

### Caso 3: Múltiples Grupos Expandidos
- **Precondición:** Consulta con múltiples grupos
- **Acción:** Expandir varios grupos simultáneamente
- **Resultado Esperado:**
  - Todos los grupos expandidos muestran detalles
  - No hay conflictos de estado

### Caso 4: Formato de Horas Decimal
- **Precondición:** Consulta con resultados
- **Acción:** Verificar formato de horas
- **Resultado Esperado:**
  - Horas en formato decimal (ej: 1.5, 2.25)
  - Conversión correcta de minutos a decimal
  - Precisión de 2 decimales

### Caso 5: Consulta Vacía
- **Precondición:** Filtros que no retornan resultados
- **Acción:** Aplicar filtros sin resultados
- **Resultado Esperado:**
  - Mensaje "No hay resultados"
  - Sin grupos visibles

## Validaciones

- Agrupación correcta por criterio
- Totales calculados correctamente
- Expansión/colapso funciona
- Formato de horas decimal correcto
- Integración con API correcta

