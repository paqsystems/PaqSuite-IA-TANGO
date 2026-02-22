# Test E2E Must-Have: Edición de Tarea Propia

## Descripción

Test End-to-End del flujo completo de edición de una tarea propia, desde la selección hasta la confirmación de actualización.

## Historia de Usuario

**Como** empleado del sistema  
**Quiero** editar una tarea que registré  
**Para** corregir errores o actualizar información

## Criterios de Aceptación

- El usuario puede seleccionar una tarea de la lista
- El usuario puede abrir el formulario de edición
- El formulario se pre-llena con los datos actuales de la tarea
- El usuario puede modificar los campos
- El sistema valida los cambios
- La tarea se actualiza correctamente
- El usuario solo puede editar sus propias tareas

## Flujo Completo

### Paso 1: Selección de Tarea
1. Usuario autenticado accede a lista de tareas
2. Usuario selecciona una tarea de la lista
3. Usuario hace clic en botón "Modificar"
4. Verificar que se abra formulario de edición

### Paso 2: Formulario Pre-llenado
1. Verificar que todos los campos estén pre-llenados con datos actuales
2. Verificar que los valores sean correctos
3. Verificar que campos editables estén habilitados

### Paso 3: Modificación de Campos
1. Usuario modifica uno o más campos
2. Verificar que los cambios se reflejen en el formulario
3. Verificar validaciones en tiempo real si aplican

### Paso 4: Guardado de Cambios
1. Usuario hace clic en botón "Guardar"
2. Verificar validaciones frontend
3. Verificar estado de carga
4. Verificar llamada a API con datos actualizados

### Paso 5: Confirmación
1. API retorna éxito
2. Verificar mensaje de éxito
3. Verificar que formulario se cierre
4. Verificar que la lista se actualice con datos modificados
5. Verificar que los cambios sean visibles en la lista

## Casos de Prueba

### Caso 1: Edición Exitosa de Todos los Campos
- **Precondición:** Usuario autenticado con tarea propia
- **Acción:** Modificar todos los campos y guardar
- **Resultado Esperado:**
  - Tarea actualizada exitosamente
  - Mensaje de éxito visible
  - Cambios reflejados en la lista

### Caso 2: Edición Parcial (Solo Algunos Campos)
- **Precondición:** Usuario autenticado con tarea propia
- **Acción:** Modificar solo algunos campos (ej: duración y descripción)
- **Resultado Esperado:**
  - Solo los campos modificados se actualizan
  - Campos no modificados mantienen valores originales
  - Tarea actualizada correctamente

### Caso 3: Cancelar Edición
- **Precondición:** Formulario de edición abierto con cambios
- **Acción:** Hacer clic en botón "Cancelar"
- **Resultado Esperado:**
  - Formulario se cierra sin guardar
  - Cambios no se aplican
  - Lista mantiene datos originales

### Caso 4: Validación de Campos en Edición
- **Precondición:** Formulario de edición abierto
- **Acción:** Modificar campo con valor inválido (ej: duración no múltiplo de 15)
- **Resultado Esperado:**
  - Mensaje de error visible
  - Formulario no se envía
  - No se realiza llamada a API

### Caso 5: Usuario Intenta Editar Tarea de Otro Usuario
- **Precondición:** Usuario normal autenticado, existe tarea de otro usuario
- **Acción:** Intentar acceder a edición de tarea de otro usuario (si es posible)
- **Resultado Esperado:**
  - Error 403 o mensaje de acceso denegado
  - No se puede editar la tarea
  - O la tarea no aparece en su lista

### Caso 6: Supervisor Edita Tarea de Otro Usuario
- **Precondición:** Usuario supervisor autenticado
- **Acción:** Seleccionar tarea de otro usuario y editar
- **Resultado Esperado:**
  - Formulario se abre correctamente
  - Tarea se puede editar
  - Cambios se guardan correctamente
  - Tarea actualizada pertenece al usuario original

### Caso 7: Edición con Datos Inválidos
- **Precondición:** Formulario de edición abierto
- **Acción:** Modificar fecha a fecha futura y guardar
- **Resultado Esperado:**
  - Mensaje de error: "Fecha futura no permitida"
  - Formulario no se envía
  - Datos originales se mantienen

## Validaciones

- Formulario se pre-llena correctamente
- Modificaciones se guardan correctamente
- Validaciones funcionan en edición
- Permisos de edición se respetan
- Lista se actualiza después de editar
- Cancelación funciona correctamente

## Elementos UI a Verificar

- Botón modificar (`tasks.entry.table.row.{id}.editButton`)
- Formulario de edición (`tasks.entry.editForm`)
- Campos pre-llenados (todos los campos del formulario)
- Botón guardar (`tasks.entry.editForm.submitButton`)
- Botón cancelar (`tasks.entry.editForm.cancelButton`)
- Mensaje de éxito (`tasks.entry.editForm.successMessage`)
- Mensajes de error (`tasks.entry.editForm.errorMessage`)

