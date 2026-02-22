# Test Individual: Cálculo de Total de Horas Agrupado

## Descripción

Test unitario para validar el cálculo de total de horas cuando se agrupan tareas por criterio (asistente, cliente, tipo, fecha).

## Objetivo

Verificar que la función de agregación calcule correctamente el total de horas en formato decimal.

## Componente/Function a Testear

Función de agregación: `calculateTotalHours(tasks: Task[]): number`

## Casos de Prueba

### Caso 1: Suma Simple
- **Input:** 
  - Tareas: [
      {duracion_minutos: 60},
      {duracion_minutos: 90},
      {duracion_minutos: 30}
    ]
- **Resultado Esperado:** 3.0 (180 minutos = 3 horas)

### Caso 2: Suma con Decimales
- **Input:**
  - Tareas: [
      {duracion_minutos: 45},
      {duracion_minutos: 15},
      {duracion_minutos: 30}
    ]
- **Resultado Esperado:** 1.5 (90 minutos = 1.5 horas)

### Caso 3: Lista Vacía
- **Input:** []
- **Resultado Esperado:** 0.0

### Caso 4: Una Sola Tarea
- **Input:** [{duracion_minutos: 120}]
- **Resultado Esperado:** 2.0

### Caso 5: Redondeo de Decimales
- **Input:**
  - Tareas: [
      {duracion_minutos: 33},
      {duracion_minutos: 17}
    ]
- **Resultado Esperado:** 0.83 (redondeado a 2 decimales)

## Validaciones

- Suma correcta de minutos
- Conversión a decimal (minutos / 60)
- Precisión de 2 decimales
- Maneja listas vacías
- Maneja valores extremos

