# Especificaciones Técnicas

Esta carpeta contiene las especificaciones técnicas detalladas del proyecto, organizadas por tipo de artefacto.

## Estructura

### Contratos

- **`contracts/`** - Contratos de respuesta de API (envelope estándar)

### Endpoints

- **`endpoints/`** - 41 especificaciones detalladas de endpoints de la API
  - Autenticación (auth-login, auth-logout)
  - Clientes (CRUD completo)
  - Tipos de Cliente (CRUD completo)
  - Asistentes (CRUD completo)
  - Tipos de Tarea (CRUD completo)
  - Registro de Tareas (CRUD completo)
  - Proceso Masivo
  - Informes y Consultas
  - Dashboard

### Modelos

- **`models/`** - 6 especificaciones detalladas de modelos backend
  - Usuario
  - Cliente
  - TipoCliente
  - TipoTarea
  - RegistroTarea
  - ClienteTipoTarea

### Reglas

- **`rules/`** - Reglas técnicas del sistema
  - `business-rules.md` - Reglas de negocio específicas con código
  - `validation-rules.md` - Reglas de validación de formato y tipo

### Tests

- **`tests/`** - Especificaciones de tests
  - `e2e/` - 7 especificaciones de tests E2E
  - `individual/` - 10 especificaciones de tests unitarios
  - `integral/` - 5 especificaciones de tests de integración

### Flujos

- **`flows/`** - Flujos del sistema
  - `e2e-core-flow.md` - Flujo E2E prioritario completo

### Errores

- **`errors/`** - Códigos de error del dominio
  - `domain-error-codes.md` - Catálogo completo de códigos de error

### Gobernanza

- **`governance/`** - Reglas de gobernanza
  - `change-impact-rules.md` - Reglas de impacto de cambios
  - `code-documentation-rules.md` - Reglas de documentación de código

### UI

- **`ui/`** - Especificaciones de UI
  - `screen-specifications.md` - Especificaciones de pantallas

## Uso

Estas especificaciones son la fuente de verdad técnica para:
- Desarrollo de backend (endpoints, modelos, reglas)
- Desarrollo de frontend (contratos, UI)
- Testing (especificaciones de tests)
- Documentación de API

## Referencias

- **Documentación general:** Ver `docs/` para documentación de alto nivel
- **Reglas de desarrollo:** Ver `.cursor/rules/` para reglas obligatorias

