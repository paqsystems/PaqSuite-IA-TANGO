# Test Individual: Validación de Duración en Rangos de 15 Minutos

## Descripción

Test unitario para validar que la duración de tareas esté en rangos válidos de 15 minutos.

## Objetivo

Verificar que la función de validación de duración acepte solo valores múltiplos de 15 minutos, mayores a 0 y menores o iguales a 1440 minutos (24 horas).

## Componente/Function a Testear

Función de validación: `validateDurationIn15MinRanges(minutes: number): boolean`

## Casos de Prueba

### Caso 1: Duración Válida (15 minutos)
- **Input:** 15
- **Resultado Esperado:** `true`

### Caso 2: Duración Válida (30 minutos)
- **Input:** 30
- **Resultado Esperado:** `true`

### Caso 3: Duración Válida (1440 minutos - 24 horas)
- **Input:** 1440
- **Resultado Esperado:** `true`

### Caso 4: Duración Inválida (No múltiplo de 15)
- **Input:** 17
- **Resultado Esperado:** `false`

### Caso 5: Duración Inválida (Cero)
- **Input:** 0
- **Resultado Esperado:** `false`

### Caso 6: Duración Inválida (Negativa)
- **Input:** -15
- **Resultado Esperado:** `false`

### Caso 7: Duración Inválida (Mayor a 24 horas)
- **Input:** 1441
- **Resultado Esperado:** `false`

## Validaciones

- Acepta solo múltiplos de 15
- Rechaza valores <= 0
- Rechaza valores > 1440
- Maneja valores no numéricos

