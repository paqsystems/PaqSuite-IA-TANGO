# Test Individual: Validación de Tipo de Tarea Por Defecto Único

## Descripción

Test unitario para validar que solo un tipo de tarea pueda estar marcado como "por defecto" en el sistema.

## Objetivo

Verificar que la lógica de negocio que garantiza un solo tipo de tarea por defecto funcione correctamente.

## Componente/Function a Testear

Función de validación: `validateSingleDefaultTaskType(taskTypes: TaskType[], newDefaultId: number): boolean`

## Casos de Prueba

### Caso 1: Establecer Primer Por Defecto
- **Input:**
  - Tipos: [{id: 1, is_default: false}, {id: 2, is_default: false}]
  - Nuevo por defecto: 1
- **Resultado Esperado:** `true`

### Caso 2: Cambiar Por Defecto Existente
- **Input:**
  - Tipos: [{id: 1, is_default: true}, {id: 2, is_default: false}]
  - Nuevo por defecto: 2
- **Resultado Esperado:** `true` (debe desmarcar el anterior)

### Caso 3: Intentar Establecer Segundo Por Defecto
- **Input:**
  - Tipos: [{id: 1, is_default: true}, {id: 2, is_default: false}]
  - Nuevo por defecto: 2 (sin desmarcar el 1)
- **Resultado Esperado:** `false` o debe desmarcar automáticamente el 1

### Caso 4: Desmarcar Por Defecto Actual
- **Input:**
  - Tipos: [{id: 1, is_default: true}, {id: 2, is_default: false}]
  - Nuevo por defecto: null (desmarcar)
- **Resultado Esperado:** `true`

## Validaciones

- Solo un tipo puede tener `is_default = true`
- Al establecer uno nuevo, el anterior se desmarca automáticamente
- Permite desmarcar el actual
- Maneja casos edge (sin tipos, con un solo tipo)

