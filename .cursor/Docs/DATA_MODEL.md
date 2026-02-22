# Documentación: DATA_MODEL.md

## Ubicación
`docs/domain/DATA_MODEL.md`

## Propósito
Este archivo contiene la especificación técnica detallada del modelo de datos del dominio para el MVP del sistema de registro de tareas. Proporciona definiciones completas de entidades, relaciones, índices, constraints y consideraciones de implementación.

## Contenido Principal

### Entidades Documentadas
- **Usuario**: Representa empleados/consultores que registran tareas
- **Cliente**: Representa clientes para los cuales se registran tareas
- **TipoTarea**: Representa tipos de tareas (ej: Desarrollo, Reunión, Análisis)
- **TipoCliente**: Representa tipos de clientes (ej: Abonados, Paquetes Horas, Libres)
- **RegistroTarea**: Representa el registro de una tarea realizada

### Información Técnica Incluida
- Definición de campos con tipos de datos
- Restricciones y constraints
- Índices para optimización
- Validaciones de negocio
- Diagrama de relaciones
- Esquema SQL de ejemplo (PostgreSQL)
- Consultas comunes
- Consideraciones de implementación

## Relación con Otros Documentos
- Complementa `docs/modelo-datos.md` con detalles técnicos más profundos
- Alineado con `docs/arquitectura.md` y `docs/producto.md`
- Refleja las decisiones de diseño del MVP

## Convención de nombres de tablas
Todas las tablas físicas del sistema deben utilizar el prefijo obligatorio:
PQ_PARTES_
Esta convención aplica a todas las entidades del dominio, catálogos e historiales.

## Uso
Este documento debe ser consultado por:
- Desarrolladores del backend para implementar el modelo de datos
- Diseñadores de base de datos para crear el esquema
- Desarrolladores de frontend para entender la estructura de datos
- Revisores del código para validar la implementación

## Mantenimiento
- Actualizar cuando se modifiquen entidades o relaciones
- Mantener sincronizado con el código de migraciones de base de datos
- Documentar cambios significativos en el modelo

