# Documentación: api-to-data-mapping.md

## Ubicación
`architecture/api-to-data-mapping.md`

## Propósito
Este archivo mapea cada endpoint de la API a sus operaciones correspondientes en la base de datos. Proporciona una referencia técnica detallada que incluye tablas involucradas, operaciones CRUD, validaciones, transformaciones de datos, relaciones cargadas e índices utilizados.

## Contenido Principal

### Mapeo de Endpoints
- **Autenticación**: POST /api/v1/auth/login
- **Catálogos**: GET /api/v1/clientes, GET /api/v1/tipos-tarea
- **Tareas**: POST, GET, PUT, DELETE /api/v1/tareas
- **Resumen**: GET /api/v1/tareas/resumen

### Especificaciones por Endpoint
- Tablas/entidades involucradas
- Operaciones SQL/ORM realizadas
- Validaciones aplicadas (con códigos de error)
- Transformaciones de datos (request → modelo → response)
- Relaciones cargadas (eager loading)
- Índices utilizados para optimización

### Convenciones Técnicas
- Tablas: `USERS` (auth, **base DICCIONARIO**), `PQ_*` (módulos del proyecto). Las tablas `PQ_PARTES_*` fueron eliminadas.
- La tabla `USERS` está en la base DICCIONARIO, no en las bases de empresas.
- Uso de LEFT JOIN en lugar de subqueries
- Eager loading para evitar N+1
- Paginación y filtros
- Patrones de consulta optimizados

### Resúmenes y Referencias
- Tabla resumen de mapeos por endpoint
- Índices críticos y su uso
- Patrones de consulta recomendados
- Consideraciones de performance

## Relación con Otros Documentos
- Implementa el flujo E2E definido en `specs/flows/e2e-core-flow.md`
- Sigue las reglas de acceso a datos en `.cursor/rules/09-data-access-orm-sql.md`
- Refleja el modelo de datos en `docs/domain/DATA_MODEL.md`
- Usa los códigos de error definidos en `specs/errors/domain-error-codes.md`
- Alineado con el contrato de API en `.cursor/rules/06-api-contract.md`

## Uso
Este documento debe ser consultado por:
- Desarrolladores del backend para implementar los endpoints
- Arquitectos para validar el diseño de acceso a datos
- Revisores de código para validar que las consultas sean eficientes
- QA para entender qué datos se consultan en cada operación

## Mantenimiento
- Actualizar cuando se agreguen nuevos endpoints
- Mantener sincronizado con cambios en el modelo de datos
- Documentar cambios en las operaciones de datos
- Revisar periódicamente las optimizaciones de consultas

