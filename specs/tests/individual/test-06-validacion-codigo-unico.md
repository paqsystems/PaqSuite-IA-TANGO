# Test Individual: Validación de Código Único

## Descripción

Test unitario para validar que los códigos de entidades (tipos de cliente, tipos de tarea, asistentes, clientes) sean únicos.

## Objetivo

Verificar que la función de validación de código único detecte duplicados correctamente.

## Componente/Function a Testear

Función de validación: `validateUniqueCode(code: string, existingCodes: string[]): boolean`

## Casos de Prueba

### Caso 1: Código Único
- **Input:** 
  - Código: "TIPO001"
  - Códigos existentes: ["TIPO002", "TIPO003"]
- **Resultado Esperado:** `true`

### Caso 2: Código Duplicado
- **Input:**
  - Código: "TIPO001"
  - Códigos existentes: ["TIPO001", "TIPO002"]
- **Resultado Esperado:** `false`

### Caso 3: Código Único en Lista Vacía
- **Input:**
  - Código: "TIPO001"
  - Códigos existentes: []
- **Resultado Esperado:** `true`

### Caso 4: Sensibilidad a Mayúsculas/Minúsculas
- **Input:**
  - Código: "tipo001"
  - Códigos existentes: ["TIPO001"]
- **Resultado Esperado:** `false` (si es case-sensitive) o `true` (si no lo es)

### Caso 5: Código Vacío
- **Input:**
  - Código: ""
  - Códigos existentes: ["TIPO001"]
- **Resultado Esperado:** `false`

## Validaciones

- Detecta duplicados correctamente
- Maneja listas vacías
- Considera sensibilidad a mayúsculas según reglas de negocio
- Rechaza códigos vacíos

