# Test Integral: Formulario de Carga de Tarea Completo

## Descripción

Test de integración del formulario completo de carga de tarea, incluyendo validaciones, carga de datos de selects, y envío a API.

## Objetivo

Verificar que el formulario de carga de tarea funcione correctamente en su totalidad, integrando todos sus componentes.

## Componentes Involucrados

- Formulario de tarea (`TaskEntryForm`)
- Select de clientes (`ClientSelect`)
- Select de tipos de tarea (`TaskTypeSelect`)
- Select de asistentes (solo para supervisores)
- Servicio de API (`TaskService`)
- Validaciones de formulario

## Flujo de Prueba

### Paso 1: Carga Inicial
1. Renderizar formulario
2. Verificar que se carguen los selects (clientes, tipos de tarea)
3. Verificar valores por defecto (fecha actual, tipo por defecto)

### Paso 2: Validación de Campos
1. Intentar enviar formulario vacío
2. Verificar mensajes de error en campos requeridos
3. Llenar campos con valores inválidos
4. Verificar mensajes de error específicos

### Paso 3: Envío Exitoso
1. Llenar formulario con datos válidos
2. Enviar formulario
3. Verificar llamada a API con datos correctos
4. Verificar mensaje de éxito
5. Verificar limpieza de formulario o redirección

## Casos de Prueba

### Caso 1: Carga Inicial Correcta
- **Precondición:** Usuario autenticado, API disponible
- **Acción:** Cargar formulario
- **Resultado Esperado:** 
  - Selects cargados con datos
  - Fecha actual seleccionada
  - Tipo de tarea por defecto seleccionado

### Caso 2: Validación de Campos Requeridos
- **Precondición:** Formulario cargado
- **Acción:** Intentar enviar sin completar campos requeridos
- **Resultado Esperado:** 
  - Mensajes de error visibles
  - Formulario no se envía

### Caso 3: Envío Exitoso
- **Precondición:** Formulario completo con datos válidos
- **Acción:** Enviar formulario
- **Resultado Esperado:**
  - Llamada a API con datos correctos
  - Mensaje de éxito
  - Formulario reseteado o redirección

### Caso 4: Error de API
- **Precondición:** Formulario completo, API retorna error
- **Acción:** Enviar formulario
- **Resultado Esperado:**
  - Mensaje de error visible
  - Formulario no se resetea
  - Datos del usuario preservados

## Validaciones

- Integración correcta entre componentes
- Flujo de datos desde API hasta formulario
- Validaciones frontend funcionan
- Manejo de errores de API
- Estados de carga (loading) visibles

