# Documentación: domain-error-codes.md

## Ubicación
`specs/errors/domain-error-codes.md`

## Propósito
Este archivo define el catálogo completo de códigos de error del dominio para el sistema de registro de tareas. Proporciona una referencia centralizada de todos los códigos de error organizados por categorías, con descripciones, códigos HTTP asociados, ejemplos de uso y convenciones de implementación.

## Contenido Principal

### Categorías de Códigos
- **0**: Éxito
- **1000-1999**: Validación (request/DTO)
- **2000-2999**: Reglas de negocio
- **3000-3999**: Autorización/Permisos
- **4000-4999**: Not Found/Conflictos
- **9000-9999**: Seguridad/Infraestructura

### Especificaciones por Código
- Código numérico
- Descripción del error
- Código HTTP asociado
- Contexto de uso
- Ejemplos prácticos

### Ejemplos de Uso
- Ejemplos de requests y responses
- Casos de error comunes
- Estructura de errores de validación múltiple

### Convenciones
- Formato de mensajes de error
- Estructura de respuestas
- Logging y debugging
- Extensibilidad futura

## Relación con Otros Documentos
- Alineado con el contrato de API en `.cursor/rules/06-api-contract.md`
- Referenciado en el flujo E2E en `specs/flows/e2e-core-flow.md`
- Usado por desarrolladores del backend para implementar manejo de errores
- Referencia para el frontend para mostrar mensajes apropiados

## Uso
Este documento debe ser consultado por:
- Desarrolladores del backend para implementar códigos de error consistentes
- Desarrolladores del frontend para manejar y mostrar errores apropiadamente
- QA para validar que los errores se manejan correctamente
- Arquitectos para mantener consistencia en el manejo de errores

## Mantenimiento
- Actualizar cuando se agreguen nuevos códigos de error
- Mantener sincronizado con el contrato de API
- Documentar cambios en el manejo de errores
- Revisar periódicamente para evitar duplicación o inconsistencias

