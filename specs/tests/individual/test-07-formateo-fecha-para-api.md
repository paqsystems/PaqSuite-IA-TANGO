# Test Individual: Formateo de Fecha para API

## Descripción

Test unitario para validar el formateo de fechas al formato esperado por la API (YYYY-MM-DD).

## Objetivo

Verificar que las fechas se formateen correctamente antes de enviarse a la API.

## Componente/Function a Testear

Función de formateo: `formatDateForAPI(date: Date | string): string`

## Casos de Prueba

### Caso 1: Formateo desde Objeto Date
- **Input:** new Date(2025, 0, 20) (20 de enero de 2025)
- **Resultado Esperado:** "2025-01-20"

### Caso 2: Formateo desde String Válido
- **Input:** "2025-01-20"
- **Resultado Esperado:** "2025-01-20"

### Caso 3: Formateo con Mes y Día de Un Dígito
- **Input:** new Date(2025, 0, 5) (5 de enero de 2025)
- **Resultado Esperado:** "2025-01-05"

### Caso 4: Formateo de Fecha Actual
- **Input:** new Date() (fecha actual)
- **Resultado Esperado:** Formato YYYY-MM-DD de la fecha actual

### Caso 5: Manejo de String Inválido
- **Input:** "fecha-invalida"
- **Resultado Esperado:** Error o null

## Validaciones

- Formato correcto: YYYY-MM-DD
- Padding de ceros en mes y día
- Maneja objetos Date
- Maneja strings en formato correcto
- Maneja errores de formato inválido

