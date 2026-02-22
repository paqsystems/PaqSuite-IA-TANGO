# Test Integral: Proceso Masivo - Selección y Procesamiento

## Descripción

Test de integración del proceso masivo de tareas, incluyendo filtros, selección múltiple, y procesamiento.

## Objetivo

Verificar que el proceso masivo funcione correctamente desde la aplicación de filtros hasta el procesamiento de tareas seleccionadas.

## Componentes Involucrados

- Página de proceso masivo (`BulkTaskProcessPage`)
- Filtros múltiples (`BulkFilters`)
- Tabla con checkboxes (`SelectableTaskTable`)
- Botón de procesamiento (`ProcessButton`)
- Servicio de API (`TaskService`)

## Flujo de Prueba

### Paso 1: Verificación de Permisos
1. Intentar acceder como usuario normal
2. Verificar que se muestre error 403 o redirección

### Paso 2: Aplicación de Filtros
1. Seleccionar filtros (período, cliente, asistente, estado)
2. Aplicar filtros
3. Verificar carga de tareas filtradas

### Paso 3: Selección de Tareas
1. Seleccionar tareas individuales
2. Usar "Seleccionar todos"
3. Usar "Deseleccionar todos"
4. Verificar que los checkboxes se actualicen correctamente

### Paso 4: Procesamiento
1. Seleccionar tareas
2. Hacer clic en "Procesar"
3. Verificar confirmación (si aplica)
4. Verificar llamada a API
5. Verificar mensaje de éxito

## Casos de Prueba

### Caso 1: Acceso No Autorizado
- **Precondición:** Usuario normal (no supervisor)
- **Acción:** Intentar acceder a proceso masivo
- **Resultado Esperado:**
  - Error 403 o redirección
  - Mensaje de acceso denegado

### Caso 2: Filtros y Carga
- **Precondición:** Usuario supervisor
- **Acción:** Aplicar filtros y cargar tareas
- **Resultado Esperado:**
  - Tareas filtradas cargadas
  - Checkboxes visibles en cada fila

### Caso 3: Selección Individual
- **Precondición:** Tareas cargadas
- **Acción:** Seleccionar tareas individuales
- **Resultado Esperado:**
  - Checkboxes marcados correctamente
  - Contador de seleccionadas actualizado

### Caso 4: Seleccionar Todos
- **Precondición:** Tareas cargadas
- **Acción:** Hacer clic en "Seleccionar todos"
- **Resultado Esperado:**
  - Todos los checkboxes marcados
  - Contador muestra total de tareas

### Caso 5: Procesamiento Exitoso
- **Precondición:** Tareas seleccionadas
- **Acción:** Hacer clic en "Procesar"
- **Resultado Esperado:**
  - Llamada a API con IDs de tareas
  - Mensaje "Se procesaron X registros"
  - Lista actualizada con nuevos estados

### Caso 6: Procesamiento con Error
- **Precondición:** Tareas seleccionadas, API retorna error
- **Acción:** Hacer clic en "Procesar"
- **Resultado Esperado:**
  - Mensaje de error visible
  - Tareas mantienen estado anterior

## Validaciones

- Permisos de supervisor verificados
- Filtros funcionan correctamente
- Selección múltiple funciona
- Procesamiento envía datos correctos
- Manejo de errores adecuado

