# Test Individual: Validación de Email

## Descripción

Test unitario para validar formato de email en campos de usuario y cliente.

## Objetivo

Verificar que la función de validación de email acepte solo formatos válidos de correo electrónico.

## Componente/Function a Testear

Función de validación: `validateEmail(email: string): boolean`

## Casos de Prueba

### Caso 1: Email Válido Simple
- **Input:** "usuario@ejemplo.com"
- **Resultado Esperado:** `true`

### Caso 2: Email Válido con Subdominio
- **Input:** "usuario@sub.ejemplo.com"
- **Resultado Esperado:** `true`

### Caso 3: Email Inválido (Sin @)
- **Input:** "usuarioejemplo.com"
- **Resultado Esperado:** `false`

### Caso 4: Email Inválido (Sin Dominio)
- **Input:** "usuario@"
- **Resultado Esperado:** `false`

### Caso 5: Email Inválido (Sin Usuario)
- **Input:** "@ejemplo.com"
- **Resultado Esperado:** `false`

### Caso 6: Email Inválido (Espacios)
- **Input:** "usuario @ejemplo.com"
- **Resultado Esperado:** `false`

### Caso 7: Email Vacío
- **Input:** ""
- **Resultado Esperado:** `false` (o `true` si es opcional)

## Validaciones

- Formato RFC 5322 básico
- Rechaza emails con espacios
- Maneja strings vacíos según contexto (requerido/opcional)

