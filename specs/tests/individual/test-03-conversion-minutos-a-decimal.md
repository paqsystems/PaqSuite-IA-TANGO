# Test Individual: Conversión de Minutos a Formato Decimal

## Descripción

Test unitario para validar la conversión de minutos a formato decimal (horas).

## Objetivo

Verificar que la función de conversión transforme correctamente minutos a horas en formato decimal.

## Componente/Function a Testear

Función de conversión: `convertMinutesToDecimal(minutes: number): number`

## Casos de Prueba

### Caso 1: Conversión Simple (60 minutos)
- **Input:** 60
- **Resultado Esperado:** 1.0

### Caso 2: Conversión con Decimal (90 minutos)
- **Input:** 90
- **Resultado Esperado:** 1.5

### Caso 3: Conversión con Decimal (45 minutos)
- **Input:** 45
- **Resultado Esperado:** 0.75

### Caso 4: Conversión de 15 minutos
- **Input:** 15
- **Resultado Esperado:** 0.25

### Caso 5: Conversión de 0 minutos
- **Input:** 0
- **Resultado Esperado:** 0.0

### Caso 6: Conversión de 1440 minutos (24 horas)
- **Input:** 1440
- **Resultado Esperado:** 24.0

### Caso 7: Redondeo (33 minutos)
- **Input:** 33
- **Resultado Esperado:** 0.55 (redondeado a 2 decimales)

## Validaciones

- Conversión correcta: minutos / 60
- Precisión de 2 decimales
- Manejo de valores extremos
- Manejo de valores no numéricos

