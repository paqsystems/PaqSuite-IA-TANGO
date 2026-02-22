# Test E2E Should-Have: Proceso Masivo de Tareas (Supervisor)

## Descripción

Test End-to-End del flujo completo de proceso masivo de tareas, incluyendo filtros, selección múltiple y procesamiento.

## Historia de Usuario

**Como** supervisor del sistema  
**Quiero** cerrar o reabrir múltiples tareas de forma masiva  
**Para** gestionar eficientemente el estado de las tareas

## Criterios de Aceptación

- Solo supervisores pueden acceder al proceso masivo
- El supervisor puede aplicar filtros complejos
- El supervisor puede seleccionar múltiples tareas
- El sistema procesa todas las tareas seleccionadas
- El supervisor recibe confirmación del procesamiento
- Los estados se actualizan correctamente

## Flujo Completo

### Paso 1: Verificación de Acceso
1. Usuario supervisor autenticado navega a `/procesos/proceso-masivo-tareas`
2. Verificar que la página sea accesible
3. Verificar que usuario normal no pueda acceder (redirección o error 403)

### Paso 2: Aplicación de Filtros
1. Supervisor selecciona período (fecha desde - fecha hasta)
2. Supervisor selecciona cliente (todos o específico)
3. Supervisor selecciona asistente (todos o específico)
4. Supervisor selecciona estado (Cerrados o Abiertos)
5. Supervisor hace clic en "Aplicar Filtros"
6. Verificar que se carguen tareas filtradas

### Paso 3: Selección de Tareas
1. Verificar que cada fila tenga checkbox
2. Supervisor selecciona tareas individuales
3. Supervisor usa "Seleccionar todos"
4. Supervisor usa "Deseleccionar todos"
5. Verificar que los checkboxes se actualicen correctamente

### Paso 4: Procesamiento
1. Supervisor selecciona tareas
2. Supervisor hace clic en "Procesar"
3. Verificar confirmación (si aplica)
4. Verificar estado de carga
5. Verificar llamada a API con IDs de tareas

### Paso 5: Confirmación
1. API retorna éxito
2. Verificar mensaje "Se procesaron X registros"
3. Verificar que lista se actualice con nuevos estados
4. Verificar que estados cambiaron correctamente

## Casos de Prueba

### Caso 1: Acceso Denegado para Usuario Normal
- **Precondición:** Usuario normal (no supervisor) autenticado
- **Acción:** Intentar acceder a `/procesos/proceso-masivo-tareas`
- **Resultado Esperado:**
  - Error 403 o redirección
  - Mensaje de acceso denegado
  - No se puede acceder a la funcionalidad

### Caso 2: Proceso Masivo Exitoso
- **Precondición:** Supervisor autenticado, tareas filtradas
- **Acción:** Seleccionar múltiples tareas y procesar
- **Resultado Esperado:**
  - Tareas procesadas exitosamente
  - Estados invertidos (cerrado ↔ abierto)
  - Mensaje de éxito con cantidad procesada

### Caso 3: Selección de Todas las Tareas
- **Precondición:** Lista de tareas cargada
- **Acción:** Hacer clic en "Seleccionar todos"
- **Resultado Esperado:**
  - Todos los checkboxes marcados
  - Contador muestra total de tareas

### Caso 4: Procesamiento sin Selección
- **Precondición:** Lista cargada, ninguna tarea seleccionada
- **Acción:** Intentar procesar sin seleccionar
- **Resultado Esperado:**
  - Mensaje: "Debe seleccionar al menos una tarea"
  - No se realiza procesamiento

### Caso 5: Filtros Complejos
- **Precondición:** Supervisor autenticado
- **Acción:** Aplicar filtros: período específico + cliente específico + asistente específico + estado
- **Resultado Esperado:**
  - Solo tareas que cumplen todos los filtros
  - Lista actualizada correctamente

### Caso 6: Procesamiento con Error
- **Precondición:** Tareas seleccionadas, API retorna error
- **Acción:** Procesar tareas
- **Resultado Esperado:**
  - Mensaje de error visible
  - Estados de tareas no cambian
  - Lista mantiene estado anterior

## Validaciones

- Permisos de supervisor verificados
- Filtros funcionan correctamente
- Selección múltiple funciona
- Procesamiento envía datos correctos
- Estados se actualizan
- Manejo de errores adecuado

## Elementos UI a Verificar

- Filtros (`tasks.bulk.filterDateFrom`, `tasks.bulk.filterDateTo`, `tasks.bulk.filterClient`, `tasks.bulk.filterAssistant`, `tasks.bulk.filterStatus`)
- Botón aplicar filtros (`tasks.bulk.applyFiltersButton`)
- Checkbox seleccionar todos (`tasks.bulk.selectAllCheckbox`)
- Tabla con checkboxes (`tasks.bulk.table`)
- Checkbox por fila (`tasks.bulk.table.row.{id}.checkbox`)
- Botón procesar (`tasks.bulk.processButton`)
- Mensaje de éxito (`tasks.bulk.successMessage`)
- Mensaje de error (`tasks.bulk.errorMessage`)

