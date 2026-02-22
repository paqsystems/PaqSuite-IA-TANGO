# Test E2E Must-Have: Registro de Tarea Completo

## Descripción

Test End-to-End del flujo completo de registro de una tarea, desde el acceso a la pantalla hasta la confirmación del registro exitoso.

## Historia de Usuario

**Como** empleado del sistema  
**Quiero** registrar una tarea diaria realizada  
**Para** llevar un control de mi dedicación por cliente

## Criterios de Aceptación

- El usuario puede acceder a la pantalla de carga de tareas
- El usuario puede seleccionar período y ver tareas existentes
- El usuario puede crear una nueva tarea con todos los campos requeridos
- El sistema valida los campos antes de enviar
- La tarea se guarda correctamente en el sistema
- El usuario ve confirmación de éxito
- La nueva tarea aparece en la lista

## Flujo Completo

### Paso 1: Acceso a Carga de Tareas
1. Usuario autenticado navega a `/procesos/carga-tareas`
2. Verificar que la página se cargue correctamente
3. Verificar que se muestren filtros de período
4. Verificar que se carguen tareas del período actual por defecto

### Paso 2: Selección de Período
1. Usuario selecciona fecha desde
2. Usuario selecciona fecha hasta
3. Usuario hace clic en "Aplicar Filtros"
4. Verificar que se recarguen las tareas del período seleccionado

### Paso 3: Creación de Nueva Tarea
1. Usuario hace clic en botón "Nuevo"
2. Verificar que se abra formulario de tarea
3. Verificar que fecha actual esté pre-seleccionada
4. Verificar que tipo de tarea por defecto esté pre-seleccionado

### Paso 4: Completar Formulario
1. Usuario selecciona cliente del dropdown
2. Usuario selecciona tipo de tarea (si no es el por defecto)
3. Usuario ingresa duración en minutos (múltiplo de 15)
4. Usuario ingresa descripción (opcional)
5. Usuario marca/desmarca "Sin cargo" si aplica
6. Usuario marca/desmarca "Presencial" si aplica

### Paso 5: Envío y Validación
1. Usuario hace clic en botón "Guardar"
2. Verificar validaciones frontend (si hay errores)
3. Verificar que se muestre estado de carga
4. Verificar llamada a API con datos correctos

### Paso 6: Confirmación y Actualización
1. API retorna éxito
2. Verificar mensaje de éxito visible
3. Verificar que formulario se cierre o resetee
4. Verificar que la nueva tarea aparezca en la lista
5. Verificar que la lista se actualice correctamente

## Casos de Prueba

### Caso 1: Registro Exitoso de Tarea Completa
- **Precondición:** Usuario autenticado, cliente y tipo de tarea existen
- **Acción:** Completar formulario con todos los campos y guardar
- **Resultado Esperado:**
  - Tarea guardada exitosamente
  - Mensaje de éxito visible
  - Tarea aparece en lista
  - Datos correctos en la lista

### Caso 2: Registro con Campos Mínimos
- **Precondición:** Usuario autenticado
- **Acción:** Completar solo campos requeridos (cliente, tipo, fecha, duración)
- **Resultado Esperado:**
  - Tarea guardada exitosamente
  - Valores por defecto aplicados (sin_cargo: false, presencial: false)

### Caso 3: Validación de Campos Requeridos
- **Precondición:** Formulario abierto
- **Acción:** Intentar guardar sin completar campos requeridos
- **Resultado Esperado:**
  - Mensajes de error visibles en campos vacíos
  - Formulario no se envía
  - No se realiza llamada a API

### Caso 4: Validación de Duración (Múltiplo de 15)
- **Precondición:** Formulario abierto
- **Acción:** Ingresar duración que no sea múltiplo de 15 (ej: 17 minutos)
- **Resultado Esperado:**
  - Mensaje de error: "La duración debe ser múltiplo de 15 minutos"
  - Formulario no se envía

### Caso 5: Validación de Fecha Futura
- **Precondición:** Formulario abierto
- **Acción:** Seleccionar fecha futura
- **Resultado Esperado:**
  - Mensaje de advertencia o error
  - Formulario no se envía (o se permite con advertencia según reglas)

### Caso 6: Supervisor Crea Tarea para Otro Usuario
- **Precondición:** Usuario supervisor autenticado
- **Acción:** Crear tarea y seleccionar otro asistente en el dropdown
- **Resultado Esperado:**
  - Dropdown de asistente visible
  - Tarea guardada con usuario_id del asistente seleccionado
  - Tarea aparece en lista del asistente seleccionado

### Caso 7: Usuario Normal - Campo Asistente No Visible
- **Precondición:** Usuario normal (no supervisor) autenticado
- **Acción:** Abrir formulario de nueva tarea
- **Resultado Esperado:**
  - Campo de asistente NO visible
  - Tarea se asigna automáticamente al usuario autenticado

## Validaciones

- Formulario se completa correctamente
- Validaciones frontend funcionan
- Datos se envían correctamente a API
- Tarea se guarda en base de datos
- Lista se actualiza después de guardar
- Permisos de supervisor funcionan correctamente

## Elementos UI a Verificar

- Filtros de período (`tasks.entry.filterDateFrom`, `tasks.entry.filterDateTo`)
- Botón nuevo (`tasks.entry.newButton`)
- Formulario (`tasks.entry.form`)
- Select cliente (`tasks.entry.form.clientSelect`)
- Select tipo tarea (`tasks.entry.form.taskTypeSelect`)
- Input duración (`tasks.entry.form.durationInput`)
- Textarea descripción (`tasks.entry.form.observacionTextarea`)
- Checkbox sin cargo (`tasks.entry.form.sinCargoCheckbox`)
- Checkbox presencial (`tasks.entry.form.presencialCheckbox`)
- Select asistente (solo supervisor) (`tasks.entry.form.assistantSelect`)
- Botón guardar (`tasks.entry.submitButton`)
- Mensaje de éxito (`tasks.entry.successMessage`)
- Mensajes de error (`tasks.entry.errorMessage`)

