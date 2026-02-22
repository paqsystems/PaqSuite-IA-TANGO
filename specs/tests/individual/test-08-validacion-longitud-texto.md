# Test Individual: Validación de Longitud de Texto

## Descripción

Test unitario para validar límites de longitud en campos de texto (descripción, observación, etc.).

## Objetivo

Verificar que los campos de texto respeten los límites máximos definidos.

## Componente/Function a Testear

Función de validación: `validateTextLength(text: string, maxLength: number): boolean`

## Casos de Prueba

### Caso 1: Texto Dentro del Límite
- **Input:** 
  - Texto: "Descripción válida"
  - MaxLength: 1000
- **Resultado Esperado:** `true`

### Caso 2: Texto en el Límite Exacto
- **Input:**
  - Texto: "A" * 1000 (1000 caracteres)
  - MaxLength: 1000
- **Resultado Esperado:** `true`

### Caso 3: Texto Excede el Límite
- **Input:**
  - Texto: "A" * 1001 (1001 caracteres)
  - MaxLength: 1000
- **Resultado Esperado:** `false`

### Caso 4: Texto Vacío
- **Input:**
  - Texto: ""
  - MaxLength: 1000
- **Resultado Esperado:** `true` (si es opcional) o `false` (si es requerido)

### Caso 5: Texto con Espacios
- **Input:**
  - Texto: "   " (solo espacios)
  - MaxLength: 1000
- **Resultado Esperado:** Depende de reglas de negocio (trim o no)

## Validaciones

- Respeta límite máximo
- Maneja texto vacío según contexto
- Considera espacios según reglas
- Maneja caracteres especiales y unicode

