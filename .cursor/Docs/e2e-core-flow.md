# Documentación: e2e-core-flow.md

## Ubicación
`specs/flows/e2e-core-flow.md`

## Propósito
Este archivo especifica el flujo End-to-End (E2E) prioritario del MVP del sistema de registro de tareas. Define paso a paso el flujo completo desde la autenticación hasta la visualización del resumen, incluyendo endpoints, requests, responses, validaciones y criterios de aceptación.

## Contenido Principal

### Flujo E2E Completo
- **Paso 1**: Autenticación (Login)
- **Paso 2**: Registro de Tarea Diaria
- **Paso 3**: Visualización de Resumen

### Especificaciones Técnicas
- Endpoints involucrados en cada paso
- Estructura de requests y responses
- Validaciones y reglas de negocio
- Códigos de error y manejo de excepciones
- Diagrama de flujo

### Criterios de Aceptación
- Checklist completo de validaciones
- Criterios de éxito y falla para cada paso
- Consideraciones de seguridad y performance

## Relación con Otros Documentos
- Implementa el flujo definido en `docs/producto.md`
- Alineado con las historias de usuario en `docs/historias-y-tickets.md`
- Sigue el contrato de API definido en `.cursor/rules/06-api-contract.md`
- Refleja el modelo de datos en `docs/domain/DATA_MODEL.md`

## Uso
Este documento debe ser consultado por:
- Desarrolladores del backend para implementar los endpoints
- Desarrolladores del frontend para integrar con la API
- QA para crear tests E2E
- Arquitectos para validar el flujo completo

## Mantenimiento
- Actualizar cuando se modifiquen endpoints o validaciones
- Mantener sincronizado con el contrato de API
- Documentar cambios que afecten el flujo E2E
- Actualizar criterios de aceptación conforme avance el desarrollo

