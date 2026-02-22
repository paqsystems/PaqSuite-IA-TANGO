# Test E2E Must-Have: Visualización de Resumen de Dedicación

## Descripción

Test End-to-End del flujo de visualización del resumen de dedicación por cliente, incluyendo filtros y totales.

## Historia de Usuario

**Como** empleado del sistema  
**Quiero** ver un resumen de mi dedicación por cliente  
**Para** analizar cómo distribuyo mi tiempo

## Criterios de Aceptación

- El usuario puede acceder al resumen de dedicación
- El usuario puede aplicar filtros de período
- El sistema muestra totales por cliente
- El sistema muestra total general
- Las horas se muestran en formato decimal
- Los datos son correctos y actualizados

## Flujo Completo

### Paso 1: Acceso al Resumen
1. Usuario autenticado navega a `/consultas/resumen` o similar
2. Verificar que la página se cargue
3. Verificar que se muestren filtros de período
4. Verificar que se cargue resumen del período actual por defecto

### Paso 2: Visualización de Resumen
1. Verificar que se muestre resumen agrupado por cliente
2. Verificar que cada cliente muestre total de horas (decimal)
3. Verificar que se muestre total general
4. Verificar formato de horas (ej: 1.5, 2.25)

### Paso 3: Aplicación de Filtros
1. Usuario selecciona fecha desde
2. Usuario selecciona fecha hasta
3. Usuario hace clic en "Aplicar Filtros"
4. Verificar que el resumen se actualice con datos del período

### Paso 4: Verificación de Datos
1. Verificar que los totales sean correctos
2. Verificar que solo se incluyan tareas del usuario autenticado
3. Verificar que las horas se calculen correctamente (minutos a decimal)

## Casos de Prueba

### Caso 1: Resumen con Múltiples Clientes
- **Precondición:** Usuario con tareas en múltiples clientes
- **Acción:** Acceder a resumen
- **Resultado Esperado:**
  - Resumen agrupado por cliente
  - Total de horas por cliente en decimal
  - Total general correcto
  - Solo tareas del usuario autenticado

### Caso 2: Resumen con Filtro de Período
- **Precondición:** Usuario con tareas en diferentes períodos
- **Acción:** Aplicar filtro de período específico
- **Resultado Esperado:**
  - Resumen muestra solo tareas del período
  - Totales recalculados correctamente
  - Clientes sin tareas en período no aparecen

### Caso 3: Resumen Vacío
- **Precondición:** Usuario sin tareas en período seleccionado
- **Acción:** Aplicar filtros sin resultados
- **Resultado Esperado:**
  - Mensaje "No hay datos para el período seleccionado"
  - Sin clientes listados
  - Total general: 0.0

### Caso 4: Formato de Horas Decimal
- **Precondición:** Usuario con tareas de duraciones variadas
- **Acción:** Verificar formato de horas en resumen
- **Resultado Esperado:**
  - Horas en formato decimal (ej: 1.5, 2.25, 0.75)
  - Precisión de 2 decimales
  - Conversión correcta de minutos a decimal

### Caso 5: Cálculo de Totales
- **Precondición:** Usuario con tareas conocidas
- **Acción:** Verificar totales mostrados
- **Resultado Esperado:**
  - Total por cliente = suma de horas de ese cliente
  - Total general = suma de todos los totales
  - Cálculos matemáticamente correctos

### Caso 6: Supervisor Ve Resumen de Todos
- **Precondición:** Usuario supervisor autenticado
- **Acción:** Acceder a resumen
- **Resultado Esperado:**
  - Opción de filtrar por usuario (opcional)
  - Si no filtra, muestra resumen de todos los usuarios
  - Totales incluyen todas las tareas

## Validaciones

- Resumen se carga correctamente
- Filtros funcionan
- Totales son correctos
- Formato de horas es decimal
- Permisos de usuario aplicados
- Datos actualizados

## Elementos UI a Verificar

- Filtros de período (`reports.summary.filterDateFrom`, `reports.summary.filterDateTo`)
- Botón aplicar filtros (`reports.summary.applyFiltersButton`)
- Resumen por cliente (`reports.summary.clientSection.{id}`)
- Total por cliente (`reports.summary.clientSection.{id}.totalHours`)
- Total general (`reports.summary.totalHours`)
- Estado vacío (`reports.summary.emptyState`)

