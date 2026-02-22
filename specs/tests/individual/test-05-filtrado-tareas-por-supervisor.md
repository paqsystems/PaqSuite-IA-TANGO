# Test Individual: Filtrado de Tareas por Permisos de Supervisor

## Descripción

Test unitario para validar la lógica de filtrado de tareas según si el usuario es supervisor o no.

## Objetivo

Verificar que la función de filtrado muestre todas las tareas para supervisores y solo las propias para usuarios normales.

## Componente/Function a Testear

Función de filtrado: `filterTasksByUserRole(tasks: Task[], user: User): Task[]`

## Casos de Prueba

### Caso 1: Usuario Normal - Solo Sus Tareas
- **Input:** 
  - Tareas: [tarea1 (usuario_id: 1), tarea2 (usuario_id: 2), tarea3 (usuario_id: 1)]
  - Usuario: {id: 1, supervisor: false}
- **Resultado Esperado:** [tarea1, tarea3]

### Caso 2: Supervisor - Todas las Tareas
- **Input:**
  - Tareas: [tarea1 (usuario_id: 1), tarea2 (usuario_id: 2), tarea3 (usuario_id: 1)]
  - Usuario: {id: 1, supervisor: true}
- **Resultado Esperado:** [tarea1, tarea2, tarea3]

### Caso 3: Usuario Normal - Sin Tareas Propias
- **Input:**
  - Tareas: [tarea1 (usuario_id: 2), tarea2 (usuario_id: 3)]
  - Usuario: {id: 1, supervisor: false}
- **Resultado Esperado:** []

### Caso 4: Supervisor - Lista Vacía
- **Input:**
  - Tareas: []
  - Usuario: {id: 1, supervisor: true}
- **Resultado Esperado:** []

## Validaciones

- Usuario normal solo ve sus tareas
- Supervisor ve todas las tareas
- Maneja listas vacías correctamente
- No modifica el array original

