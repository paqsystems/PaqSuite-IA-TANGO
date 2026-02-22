# Test Individual: Validación de Fecha No Futura

## Descripción

Test unitario para validar que la función de validación de fecha rechace fechas futuras.

## Objetivo

Verificar que la validación de fecha funcione correctamente y rechace fechas posteriores a la fecha actual.

## Componente/Function a Testear

Función de validación: `validateDateNotFuture(date: string): boolean`

## Casos de Prueba

### Caso 1: Fecha Pasada
- **Input:** Fecha de ayer
- **Resultado Esperado:** `true` (válida)

### Caso 2: Fecha Actual
- **Input:** Fecha de hoy
- **Resultado Esperado:** `true` (válida)

### Caso 3: Fecha Futura (Mañana)
- **Input:** Fecha de mañana
- **Resultado Esperado:** `false` (inválida)

### Caso 4: Fecha Futura (1 mes)
- **Input:** Fecha dentro de 1 mes
- **Resultado Esperado:** `false` (inválida)

### Caso 5: Formato Inválido
- **Input:** String no válido como fecha
- **Resultado Esperado:** `false` (inválida)

## Validaciones

- La función debe retornar `true` para fechas pasadas o actuales
- La función debe retornar `false` para fechas futuras
- La función debe manejar formatos de fecha incorrectos

## Notas

- Considerar zona horaria en la comparación
- Validar formato de entrada (YYYY-MM-DD)

