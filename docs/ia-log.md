# Registro del Uso de IA

Este documento registra el uso consciente y controlado de herramientas de IA
durante el desarrollo del MVP.

# EJEMPLO
## Entrada #x
Fecha: 2026-01-18
Etapa: Backend – API tareas
Herramienta: Cursor + ChatGPT
Prompt: “Generar endpoint REST para registrar tareas diarias”
Resultado IA: CRUD inicial de tareas
Ajuste humano: Simplifiqué validaciones y eliminé estados no usados
Motivo: Mantener alcance MVP

# ENTREGA 1
---

## Entrada #1

### Fecha
2026-01-17

### Etapa del proyecto
Definición de contexto, alcance del MVP y documentación base

### Herramientas de IA utilizadas
- ChatGPT (asistente de diseño y revisión)
- Cursor IDE (generación inicial de archivos)

### Prompt o instrucción utilizada
Definir y alinear el contexto del proyecto, el alcance del MVP y los artefactos
de documentación requeridos, corrigiendo desalineaciones entre lo generado por
el IDE y el objetivo real del sistema.

### Resultado generado por IA
- Definición clara del producto como sistema de registro de tareas para consultorías.
- Revisión crítica de archivos generados automáticamente por el IDE.
- Propuesta y redacción de archivos de contexto y definición:
  - PROJECT_CONTEXT.md
  - AGENTS.md
  - Reescritura de documentación en /docs (producto, historias, arquitectura,
    modelo de datos, testing y deploy).

### Ajustes humanos realizados
- Se descartó el enfoque inicial orientado a integraciones (Jira, MCP, Playwright).
- Se redujo el alcance al flujo E2E mínimo necesario para el MVP.
- Se priorizó simplicidad y coherencia por sobre sobre–ingeniería.
- Se validó que toda la documentación reflejara un único producto consistente.

### Motivo del ajuste
Alinear el proyecto con las consignas del trabajo práctico, evitar dispersión
funcional y asegurar que el MVP sea realizable, defendible y evaluable dentro
del alcance previsto.

## Entrada #2

## Registro de Uso de IA — Generación de Specs y Revisión Humana (Iteración X)

### Fecha
2026-01-17

### Tarea
Generación de las especificaciones funcionales y técnicas (enfoque SDD) para el MVP “Sistema de Partes”, incluyendo:
- Definición del flujo E2E prioritario
- Especificaciones de endpoints
- Reglas de validación
- Códigos de error de dominio
- Contrato de respuesta de la API (response envelope)

### Aporte de la IA
El asistente de IA fue utilizado para:
- Proponer la estructura general de la carpeta `/specs`.
- Generar todos los documentos iniciales de especificación, incluyendo:
  - Flujo E2E principal
  - Specs de endpoints (autenticación, partes, reportes)
  - Reglas de validación
  - Códigos de error de dominio
  - Contrato estándar de respuesta de la API
- Alinear las especificaciones con principios de Spec-Driven Design (SDD) y buenas prácticas REST.

Todas las especificaciones fueron generadas **a partir de mis directivas funcionales y arquitectónicas**, no de manera autónoma.

### Revisión e Intervención Humana
Durante la revisión manual de las especificaciones generadas, detecté una inconsistencia de diseño:

- El flujo de autenticación y las especificaciones relacionadas utilizaban **login basado en correo electrónico**.
- La regla de negocio prevista para esta aplicación es un **login basado en código de usuario** (identificador interno), y no por email.

Esta discrepancia fue identificada durante la validación humana de las specs, antes de iniciar cualquier desarrollo.

### Decisión Humana y Ajuste
A partir de esta detección, instruí a la IA para que:
- Modificara el modelo de autenticación para utilizar **código de usuario** en lugar de correo electrónico.
- Actualizara **todas las especificaciones afectadas**, incluyendo:
  - `auth-login.md`
  - Referencias dentro del flujo E2E
  - Reglas de validación asociadas a la autenticación
  - Casos de error vinculados al login
- Garantizara la coherencia del cambio en todo el conjunto de specs.

Este ajuste refleja una **decisión de diseño tomada por el humano**, basada en conocimiento del dominio y en la experiencia real de uso de sistemas similares.

### Resultado
- Las especificaciones finales reflejan correctamente el mecanismo de autenticación definido.
- El cambio fue aplicado de forma consistente en todos los documentos.
- No se había generado código antes de esta corrección, evitando retrabajo.

### Reflexión
Este proceso evidencia el rol de la IA como **acelerador en la generación de especificaciones**, mientras que:
- Las decisiones arquitectónicas,
- La corrección respecto del dominio,
- Y la validación final  
permanecen bajo responsabilidad humana.

Las propuestas de la IA fueron revisadas críticamente y ajustadas cuando fue necesario.

## Entrada #3

### Fecha
2025-01-20

### Etapa del proyecto
Especificación de pantallas y procesos - Definición de UI

### Herramientas de IA utilizadas
- Cursor IDE (generación de especificaciones técnicas)

### Prompt o instrucción utilizada
Proporcionar al asistente de IA el menú de procesos con las pantallas de datos y solicitar que genere una definición contextual detallada de todas las pantallas y procesos del sistema, incluyendo elementos UI, validaciones, integraciones con API, test IDs y notas de implementación.

### Resultado generado por IA
- Generación completa del archivo `specs/ui/screen-specifications.md` con:
  - Estructura de navegación y menú principal
  - Procesos de usuario documentados (Gestión de Archivos, Carga de Tareas, Proceso Masivo, Consultas)
  - Especificaciones detalladas de cada pantalla:
    - Elementos UI con tipos y descripciones
    - Test IDs para todos los controles
    - Validaciones frontend
    - Estados de UI (loading, empty, error, success)
    - Integración con endpoints API
    - Notas de implementación
  - Convenciones técnicas (formato de horas, test IDs, accesibilidad)
  - Referencias a endpoints existentes y nuevos endpoints necesarios

### Ajustes humanos realizados
- Revisión completa de la estructura generada
- Ajuste de la organización del contenido para mejor legibilidad
- Verificación de coherencia con las especificaciones de endpoints existentes
- Validación de que los test IDs sigan las convenciones del proyecto
- Confirmación de que los permisos de supervisor estén correctamente documentados
- Verificación de que los flujos de usuario sean lógicos y completos

### Motivo del ajuste
Asegurar que las especificaciones de pantallas sean coherentes con el resto de la documentación del proyecto, que sigan las convenciones establecidas (test IDs, accesibilidad, formato de respuesta API) y que proporcionen suficiente detalle técnico para la implementación del frontend sin ambigüedades.

## Entrega 4 – Generación y validación de historias de usuario y tickets técnicos

### Objetivo
Definir el conjunto completo de historias de usuario (User Stories) y tickets técnicos del MVP, cubriendo:
- Todos los roles del sistema (Cliente, Empleado, Empleado Supervisor)
- Funcionalidades del lado del usuario y del lado de la empresa/aplicación
- Clasificación de alcance MUST-HAVE vs SHOULD-HAVE
- Trazabilidad entre funcionalidades, backend, frontend, testing e infraestructura

### Uso de IA
Se utilizó IA (Cursor / ChatGPT) para:
- Generar un catálogo exhaustivo de historias de usuario organizadas por épicas
- Clasificar cada historia como MUST-HAVE o SHOULD-HAVE
- Definir criterios de aceptación detallados
- Incorporar reglas de negocio explícitas
- Derivar tickets técnicos alineados con las historias (backend, frontend, tests, CI/CD, documentación)

### Prompt / Instrucción dada a la IA
Se solicitó explícitamente:
- Generar el archivo `docs/historias-y-tickets.md`
- Considerar tres roles: Cliente, Empleado y Empleado Supervisor
- Incluir historias del lado del usuario y del lado de la aplicación cuando corresponda
- Aplicar reglas de negocio específicas (tipos de tarea genéricos / por defecto, asociaciones cliente–tipo de tarea, validaciones de duración, etc.)
- Clasificar cada historia como MUST-HAVE o SHOULD-HAVE
- Generar una tabla resumen y tickets técnicos derivados

### Resultado generado por la IA
La IA generó:
- Un documento estructurado por épicas
- 55 historias de usuario con criterios de aceptación detallados
- Tabla resumen de historias
- 33 tickets técnicos derivados, cubriendo:
  - Base de datos
  - Backend (API, validaciones, reglas de negocio)
  - Frontend (componentes, UX)
  - Testing (unitarios, integración y E2E)
  - CI/CD, logging y documentación

### Revisión y control humano
El resultado fue revisado manualmente para:
- Verificar consistencia funcional y técnica
- Validar alineación con el alcance real del MVP
- Confirmar que las reglas de negocio definidas previamente estuvieran correctamente reflejadas
- Ajustar criterios de prioridad (MUST vs SHOULD)
- Evaluar exhaustividad sin sobredimensionar el MVP

No se detectaron inconsistencias críticas.  
Se validó que el documento es apto como:
- Base de implementación
- Checklist de validación del MVP
- Insumo para documentación final del proyecto

### Decisión final
El documento `docs/historias-y-tickets.md` se adopta como:
- Fuente única de verdad funcional del MVP
- Referencia para validar la completitud del desarrollo
- Insumo directo para el README final del proyecto

---

## Entrada #4

### Fecha
2025-01-20

### Etapa del proyecto
Rediseño de arquitectura de autenticación y actualización de documentación

### Herramientas de IA utilizadas
- Cursor IDE (generación y actualización de documentación técnica)

### Prompt o instrucción utilizada
1. **Cambio arquitectónico:** Introducir una tabla `USERS` (sin prefijo PQ_PARTES_) como tabla central de autenticación. Después del login exitoso, determinar si es un login de cliente (PQ_PARTES_CLIENTES) o de usuario (PQ_PARTES_USUARIOS), y conservar los valores de tipo de usuario, user_code, cliente_id/usuario_id y es_supervisor durante todo el ciclo del proceso.

2. **Actualización de documentación:** Actualizar todas las historias de usuario y tickets técnicos afectados por el cambio de autenticación.

3. **Revisión de permisos:** Revisar historias de Épicas 9 (Informes y consultas) y 10 (Dashboard) para asegurar que contemplen explícitamente que:
   - Los clientes solo pueden ver tareas donde `cliente_id` coincide con su `cliente_id`
   - Los empleados NO supervisores solo pueden ver tareas donde `usuario_id` coincide con su `usuario_id`

### Resultado generado por IA

#### Cambio arquitectónico de autenticación:
- **Modelo de datos actualizado:**
  - Agregada tabla `USERS` (sin prefijo PQ_PARTES_) con campos: `id`, `code`, `password_hash`, `activo`, `inhabilitado`
  - Actualizada tabla `PQ_PARTES_USUARIOS`: agregado `user_id` (FK → User, obligatorio), eliminado `password_hash`
  - Actualizada tabla `PQ_PARTES_CLIENTES`: agregado `user_id` (FK → User, opcional), eliminado `password_hash`
  - Documentado flujo de autenticación: validación contra `USERS`, determinación de tipo (cliente/usuario), obtención de datos adicionales
  - Documentados valores a conservar durante el ciclo: `tipo_usuario`, `user_code`, `usuario_id`/`cliente_id`, `es_supervisor`

- **Documentación técnica actualizada:**
  - `docs/modelo-datos.md`: Nueva estructura con tabla `USERS` y flujo de autenticación completo
  - `database/modelo-datos.dbml`: Actualizado con tabla `User` y relaciones
  - `specs/endpoints/auth-login.md`: Endpoint actualizado con nuevo flujo y ejemplos de respuesta
  - `specs/flows/e2e-core-flow.md`: Flujo E2E actualizado con nuevos valores de autenticación
  - `architecture/api-to-data-mapping.md`: Mapeo API actualizado con operaciones en `USERS`

- **Historias de usuario actualizadas (6):**
  - HU-001 (Login empleado): Flujo contra `USERS`, determinación de tipo, valores a conservar
  - HU-002 (Login cliente): Mismo flujo que HU-001
  - HU-009 (Creación cliente): Creación en `USERS` si se habilita acceso al sistema
  - HU-010 (Edición cliente): Sincronización de estados y contraseñas con `USERS`
  - HU-019 (Creación asistente): Creación simultánea en `USERS`
  - HU-020 (Edición asistente): Sincronización de estados y contraseñas con `USERS`

- **Tickets técnicos actualizados (5):**
  - TK-001: Migración de `USERS`, actualización de modelos y relaciones
  - TK-002: Endpoint unificado de login, determinación de tipo, middleware
  - TK-003: Creación/sincronización con `USERS` en gestión de clientes
  - TK-005: Creación/sincronización con `USERS` en gestión de asistentes
  - TK-016: Middleware para conservar valores de autenticación

- **Supuestos y Definiciones actualizados:**
  - Agregada entidad `User` en Entidades Principales
  - Actualizadas entidades `Usuario` y `Cliente` (eliminado `password_hash`, agregado `user_id`)
  - Actualizados Supuestos Adicionales con nuevo flujo de autenticación

#### Revisión de permisos de visualización:
- **Historias actualizadas en Épica 9 (7 historias):**
  - HU-044: Aclarado que "Empleado" se refiere a "Empleado (NO supervisor)"
  - HU-046: Agregados filtros automáticos según rol en notas de reglas de negocio
  - HU-048: Agregados filtros automáticos según rol en criterios de aceptación y notas
  - HU-049: Agregado que los datos exportados respetan permisos del usuario
  - HU-050: Agregado que los filtros automáticos se aplican antes de verificar resultados vacíos

- **Historias actualizadas en Épica 10 (5 historias):**
  - HU-051: Aclarado que "Empleado" se refiere a "Empleado (NO supervisor)" y agregados filtros automáticos
  - HU-052: Agregados filtros automáticos según permisos en criterios de aceptación y notas
  - HU-054: Aclarado que gráficos respetan permisos según rol
  - HU-055: Agregado que actualizaciones automáticas respetan filtros según rol

- **Reglas de permisos documentadas:**
  - Clientes: Solo tareas donde `cliente_id` coincide con su `cliente_id`
  - Empleados (NO supervisores): Solo tareas donde `usuario_id` coincide con su `usuario_id`
  - Supervisores: Todas las tareas de todos los usuarios

### Ajustes humanos realizados

1. **Análisis previo:**
   - Se generó archivo `docs/ANALISIS-HISTORIAS-AUTENTICACION.md` con análisis detallado de cambios necesarios
   - Se revisó y confirmó el análisis antes de proceder con las actualizaciones

2. **Validación de cambios:**
   - Se verificó que todos los cambios fueran consistentes entre documentos
   - Se aseguró que las referencias cruzadas entre documentos estuvieran actualizadas
   - Se validó que el flujo de autenticación fuera completo y coherente

3. **Eliminación de archivos temporales:**
   - Se eliminó `docs/ANALISIS-HISTORIAS-AUTENTICACION.md` después de aplicar los cambios

4. **Revisión de permisos:**
   - Se identificaron historias que no especificaban explícitamente los filtros por rol
   - Se agregaron aclaraciones en todas las historias afectadas para evitar ambigüedades

### Motivo del ajuste

1. **Cambio arquitectónico:**
   - Centralizar la autenticación en una única tabla `USERS` simplifica el modelo y permite un mejor control de acceso
   - Separar la autenticación de las entidades de negocio (Usuario/Cliente) permite mayor flexibilidad
   - Conservar valores de autenticación durante el ciclo del proceso es necesario para autorización y auditoría

2. **Documentación de permisos:**
   - Asegurar que todas las historias especifiquen explícitamente los filtros por rol evita ambigüedades en la implementación
   - Garantizar que clientes y empleados NO supervisores solo vean sus propios datos es un requisito de seguridad y privacidad
   - Documentar estos permisos en cada historia facilita la implementación y testing

### Archivos modificados

- `docs/modelo-datos.md`
- `database/modelo-datos.dbml`
- `specs/endpoints/auth-login.md`
- `specs/flows/e2e-core-flow.md`
- `architecture/api-to-data-mapping.md`
- `docs/historias-y-tickets.md` (múltiples actualizaciones)

### Archivos eliminados

- `docs/ANALISIS-HISTORIAS-AUTENTICACION.md` (archivo temporal de análisis)

---

# ENTREGA 2

---

## Entrada #6

### Fecha
2026-01-27

### Etapa del proyecto
Implementación de infraestructura de base de datos (TR-00)

### Herramientas de IA utilizadas
- Cursor IDE (Claude) - Implementación completa

### Prompt o instrucción utilizada

```
Implementá la TR completa ubicada en:
"docs/tareas/TR-00(MH)-Generacion-base-datos-inicial.md"

Reglas:
- Ejecutar estrictamente las tareas T2 a T15 como fuente de verdad.
- T1 (script SQL via MCP) solo si es necesario para validar rápidamente, pero la FUENTE DE VERDAD deben ser migraciones Laravel.
- Respetar nombres de tablas y prefijo PQ_PARTES_ (excepto USERS).
- No inventar tablas ni campos fuera del modelo indicado en la TR.
- Al terminar, actualizar el mismo archivo TR marcando cada tarea como DONE e incluyendo notas de implementación.

Deliverables mínimos:
- Migraciones (up/down) + seeders + modelos Eloquent + tests de migraciones y seed.
- Documentación del proceso (docs/deploy-ci-cd.md o archivo dedicado).
- Entrada en docs/ia-log.md con prompts usados y ajustes humanos.
```

### Resultado generado por IA

**Migraciones creadas (7 archivos):**
- `2026_01_27_000001_create_users_table.php`
- `2026_01_27_000002_create_tipos_cliente_table.php`
- `2026_01_27_000003_create_tipos_tarea_table.php`
- `2026_01_27_000004_create_usuarios_table.php`
- `2026_01_27_000005_create_clientes_table.php`
- `2026_01_27_000006_create_registro_tarea_table.php`
- `2026_01_27_000007_create_cliente_tipo_tarea_table.php`

**Seeders creados (6 archivos):**
- `DatabaseSeeder.php` - Orquestador principal
- `TipoClienteSeeder.php` - Tipos de cliente (CORP, PYME)
- `TipoTareaSeeder.php` - Tipos de tarea (GENERAL, SOPORTE, DESARROLLO)
- `UserSeeder.php` - Usuarios de autenticación (ADMIN, CLI001, EMP001)
- `UsuarioSeeder.php` - Empleados (Administrador, Empleado Demo)
- `ClienteSeeder.php` - Clientes (CLI001, CLI002)

**Modelos Eloquent creados/actualizados (7 archivos):**
- `User.php` - Tabla USERS (autenticación centralizada)
- `Usuario.php` - Tabla PQ_PARTES_USUARIOS
- `Cliente.php` - Tabla PQ_PARTES_CLIENTES
- `TipoCliente.php` - Tabla PQ_PARTES_TIPOS_CLIENTE
- `TipoTarea.php` - Tabla PQ_PARTES_TIPOS_TAREA
- `RegistroTarea.php` - Tabla PQ_PARTES_REGISTRO_TAREA
- `ClienteTipoTarea.php` - Tabla PQ_PARTES_CLIENTE_TIPO_TAREA

**Tests creados (2 archivos):**
- `MigrationTest.php` - Tests de estructura de tablas y foreign keys
- `SeederTest.php` - Tests de datos mínimos

**Documentación actualizada:**
- `docs/deploy-ci-cd.md` - Agregada sección de migraciones y BD

### Ajustes humanos realizados
Leído las tareas realizadas documentadas en el mismo archivo "docs/tareas/TR-00(MH)-Generacion-base-datos-inicial.md", se procedió a realizar todos los pasos indicados en el tópico "Tareas pendientes", obviamente realizados por la IA, siguiendo el cumplimiento del paso a paso, hasta verificar la generación de todas las tablas a través del versionado Laravel.

En función de los problemas generados durante la migración Laravel, se agregó una regla para que en SQL Server se trabaje con el formato de fecha YMD y la programación se adapte a este formato

### Motivo del ajuste
N/A - Primera implementación sin ajustes.

### Decisiones técnicas tomadas por la IA

1. **Eliminación de migraciones parciales existentes:** Se eliminaron 4 migraciones parciales que no seguían el modelo de datos completo.

2. **Orden de migraciones:** Se estableció un orden estricto para respetar dependencias de foreign keys.

3. **Seeders idempotentes:** Se implementaron con `updateOrInsert` para evitar duplicados en ejecuciones repetidas.

4. **Modelo User separado:** Se creó un modelo `User.php` para la tabla `USERS` (autenticación), separado de `Usuario.php` (empleados).

5. **Nomenclatura de tablas:** Se respetó el prefijo `PQ_PARTES_` para todas las tablas excepto `USERS`.

### Archivos creados

**Backend/Database/Migrations:**
- `backend/database/migrations/2026_01_27_000001_create_users_table.php`
- `backend/database/migrations/2026_01_27_000002_create_tipos_cliente_table.php`
- `backend/database/migrations/2026_01_27_000003_create_tipos_tarea_table.php`
- `backend/database/migrations/2026_01_27_000004_create_usuarios_table.php`
- `backend/database/migrations/2026_01_27_000005_create_clientes_table.php`
- `backend/database/migrations/2026_01_27_000006_create_registro_tarea_table.php`
- `backend/database/migrations/2026_01_27_000007_create_cliente_tipo_tarea_table.php`

**Backend/Database/Seeders:**
- `backend/database/seeders/DatabaseSeeder.php`
- `backend/database/seeders/TipoClienteSeeder.php`
- `backend/database/seeders/TipoTareaSeeder.php`
- `backend/database/seeders/UserSeeder.php`
- `backend/database/seeders/UsuarioSeeder.php`
- `backend/database/seeders/ClienteSeeder.php`

**Backend/App/Models:**
- `backend/app/Models/User.php`
- `backend/app/Models/Usuario.php`
- `backend/app/Models/Cliente.php`
- `backend/app/Models/TipoCliente.php`
- `backend/app/Models/TipoTarea.php`
- `backend/app/Models/RegistroTarea.php`
- `backend/app/Models/ClienteTipoTarea.php`

**Backend/Tests:**
- `backend/tests/Feature/Database/MigrationTest.php`
- `backend/tests/Feature/Database/SeederTest.php`

### Archivos modificados

- `docs/deploy-ci-cd.md` - Agregada sección de migraciones y base de datos

### Archivos eliminados

- `backend/database/migrations/2025_01_20_000001_add_fields_to_tipo_tarea_table.php`
- `backend/database/migrations/2025_01_20_000002_create_cliente_tipo_tarea_table.php`
- `backend/database/migrations/2025_01_20_000003_create_tipo_cliente_table.php`
- `backend/database/migrations/2025_01_20_000004_add_tipo_cliente_id_to_cliente_table.php`

---

## Entrada #7

### Fecha
2026-01-27

### Etapa del proyecto
Configuración del proyecto Laravel y verificación de migraciones

### Herramientas de IA utilizadas
- Cursor IDE (Claude) - Configuración y debugging

### Prompt o instrucción utilizada

```
podés realizar la configuración del proyecto Laravel?
```

### Resultado generado por IA

1. **Proyecto Laravel 10.x creado** mediante `composer create-project`
2. **Archivos personalizados preservados** (migraciones, seeders, modelos, tests)
3. **Conexión SQL Server configurada** en `.env`
4. **Migraciones y seeders ejecutados exitosamente**

### Ajustes técnicos para compatibilidad SQL Server

Durante la ejecución, se encontraron y resolvieron los siguientes problemas:

1. **`ON DELETE RESTRICT` no soportado:**
   - Error: "Sintaxis incorrecta cerca de la palabra clave 'restrict'"
   - Solución: Cambiar a `ON DELETE NO ACTION` (mismo comportamiento en SQL Server)

2. **IDENTITY_INSERT OFF:**
   - Error: "No se puede insertar un valor explícito en la columna de identidad"
   - Solución: Eliminar IDs explícitos de los seeders, dejar que SQL Server genere los IDs

3. **Formato de fecha fuera de rango:**
   - Error: "La conversión del tipo de datos nvarchar en datetime produjo un valor fuera de intervalo"
   - Solución: Usar `DB::raw('GETDATE()')` en lugar de `now()` en los seeders

### Archivos modificados

- `backend/database/migrations/2026_01_27_000004_create_usuarios_table.php` - `restrict` → `no action`
- `backend/database/migrations/2026_01_27_000005_create_clientes_table.php` - `restrict` → `no action`
- `backend/database/migrations/2026_01_27_000006_create_registro_tarea_table.php` - `restrict` → `no action`
- `backend/database/seeders/TipoClienteSeeder.php` - Timestamps con GETDATE()
- `backend/database/seeders/TipoTareaSeeder.php` - Timestamps con GETDATE()
- `backend/database/seeders/UserSeeder.php` - Timestamps con GETDATE()
- `backend/database/seeders/UsuarioSeeder.php` - IDs dinámicos + GETDATE()
- `backend/database/seeders/ClienteSeeder.php` - IDs dinámicos + GETDATE()
- `backend/.env` - Configuración SQL Server
- `docs/hu-tareas/TR-00(MH)-Generacion-base-datos-inicial.md` - Actualizado con estado final

### Archivos creados

- `backend/.env.example` - Template de variables de entorno

### Comandos ejecutados

```bash
composer create-project laravel/laravel backend
php artisan migrate
php artisan db:seed
php artisan migrate:fresh --seed  # Verificación completa ✅
```

### Verificación final

- ✅ 10 tablas creadas (7 del modelo + 3 de Laravel)
- ✅ Datos de seed insertados correctamente
- ✅ `migrate:fresh --seed` funciona sin errores
- ✅ Reproducibilidad completa verificada

---

## Entrada #8

### Fecha
2026-01-27

### Etapa del proyecto
Implementación de Login de Empleado (TR-001)

### Herramientas de IA utilizadas
- Cursor IDE (Claude) - Implementación completa

### Prompt o instrucción utilizada

```
Implementá la TR funcional ubicada en:
"docs/tareas/TR-001(MH)-login-de-empleado.md"

Esta TR es la FUENTE DE VERDAD del alcance.

Reglas generales:
- Implementar estrictamente las tareas definidas en la TR.
- No inventar funcionalidades fuera del alcance.
- Backend, Frontend, Tests y Documentación según lo indicado en la TR.
```

### Resultado generado por IA

**Tareas implementadas: 18 de 18**

**Backend (T1-T6):**
- `database/seeders/TestUsersSeeder.php` - Seeder con usuarios de prueba
- `app/Http/Requests/Auth/LoginRequest.php` - FormRequest de validación
- `app/Services/AuthService.php` - Servicio de autenticación con lógica completa
- `app/Http/Controllers/Api/V1/AuthController.php` - Controller
- `routes/api.php` - Ruta POST /api/v1/auth/login
- `app/Http/Resources/Auth/LoginResource.php` - Resource de respuesta

**Frontend (T7-T11):**
- `src/shared/utils/tokenStorage.ts` - Utilidad de almacenamiento de token
- `src/features/auth/services/auth.service.ts` - Servicio de autenticación
- `src/features/auth/components/LoginForm.tsx` - Componente de formulario
- `src/features/auth/components/LoginForm.css` - Estilos
- `src/routes/ProtectedRoute.tsx` - Protección de rutas
- `src/routes/PublicRoute.tsx` - Rutas públicas
- `src/app/App.tsx` - Componente raíz con router
- `src/app/Dashboard.tsx` - Dashboard básico

**Tests (T12-T14):**
- `tests/Unit/Services/AuthServiceTest.php` - 10 unit tests
- `tests/Feature/Api/V1/Auth/LoginTest.php` - 11 integration tests
- `tests/e2e/auth-login.spec.ts` - 10 E2E tests con Playwright

**Documentación (T15-T17):**
- `docs/backend/autenticacion.md` - Documentación del flujo de autenticación
- Actualización de `docs/ia-log.md`

### Ajustes humanos realizados
- Ninguno hasta el momento.

### Decisiones técnicas tomadas por la IA

1. **Estructura de carpetas:**
   - Backend: Controllers en `Api/V1/` para versionado
   - Frontend: Features organizados por dominio (`features/auth/`)

2. **Seguridad:**
   - Mensajes de error genéricos para no revelar existencia de usuarios
   - Validación en dos niveles (USERS y PQ_PARTES_USUARIOS)

3. **Frontend:**
   - React con TypeScript
   - localStorage para persistencia de token
   - Rutas protegidas con redirección automática

4. **Tests E2E:**
   - Uso exclusivo de `data-testid` como selectores
   - Sin esperas ciegas (waitForTimeout)
   - Verificación de localStorage

### Archivos creados

**Backend:**
- `backend/database/seeders/TestUsersSeeder.php`
- `backend/app/Http/Requests/Auth/LoginRequest.php`
- `backend/app/Services/AuthService.php`
- `backend/app/Http/Controllers/Api/V1/AuthController.php`
- `backend/app/Http/Resources/Auth/LoginResource.php`
- `backend/tests/Unit/Services/AuthServiceTest.php`
- `backend/tests/Feature/Api/V1/Auth/LoginTest.php`

**Frontend:**
- `frontend/src/shared/utils/tokenStorage.ts`
- `frontend/src/features/auth/services/auth.service.ts`
- `frontend/src/features/auth/components/LoginForm.tsx`
- `frontend/src/features/auth/components/LoginForm.css`
- `frontend/src/features/auth/components/index.ts`
- `frontend/src/features/auth/services/index.ts`
- `frontend/src/features/auth/index.ts`
- `frontend/src/routes/ProtectedRoute.tsx`
- `frontend/src/routes/PublicRoute.tsx`
- `frontend/src/routes/index.ts`
- `frontend/src/app/App.tsx`
- `frontend/src/app/App.css`
- `frontend/src/app/Dashboard.tsx`
- `frontend/src/app/Dashboard.css`
- `frontend/src/main.tsx`
- `frontend/index.html`
- `frontend/tests/e2e/auth-login.spec.ts`

**Docs:**
- `docs/backend/autenticacion.md`

### Archivos modificados

- `backend/routes/api.php` - Agregada ruta de login

---

## Entrada #9

**Fecha:** 2026-01-28  
**Herramienta:** Cursor (Claude)  
**HU/TR relacionada:** TR-003(MH)-logout.md

### Prompt principal

```
Implementá la TR funcional ubicada en:
"docs/tareas/TR-003(MH)-logout.md"

Esta TR es la FUENTE DE VERDAD del alcance.
[...]
```

### Tareas ejecutadas

1. **T1** - Backend: Endpoint POST /api/v1/auth/logout
2. **T2** - Backend: Tests unitarios AuthService::logout()
3. **T3** - Backend: Tests de integración del endpoint
4. **T4** - Frontend: Integrar logout con API
5. **T5** - Frontend: Actualizar Dashboard con estado loading
6. **T6** - Frontend: Manejo de errores fail-safe
7. **T7** - E2E: Tests Playwright para logout
8. **T8** - Docs: Actualizar autenticacion.md
9. **T9** - Docs: Registrar en ia-log.md

### Decisiones técnicas

1. **Comportamiento fail-safe:** Si el API de logout falla (401, error de red), el frontend igual limpia localStorage y redirige. Esto garantiza que el usuario siempre pueda "cerrar sesión" localmente.

2. **Solo revoca token actual:** El logout solo elimina el token usado en la petición, no todos los tokens del usuario. Esto permite sesiones en múltiples dispositivos.

3. **Estado loading en botón:** El botón de logout se deshabilita y muestra "Cerrando..." durante la petición para evitar doble clic.

4. **Respuesta con objeto vacío:** `resultado: {}` en lugar de `null` para mantener consistencia con el envelope de la API.

### Archivos creados

**Backend:**
- `backend/tests/Feature/Api/V1/Auth/LogoutTest.php`

**Frontend:**
*(ninguno nuevo, solo modificaciones)*

### Archivos modificados

**Backend:**
- `backend/app/Services/AuthService.php` - Agregado método logout()
- `backend/app/Http/Controllers/Api/V1/AuthController.php` - Agregado método logout()
- `backend/routes/api.php` - Agregada ruta POST /api/v1/auth/logout
- `backend/tests/Unit/Services/AuthServiceTest.php` - Agregados tests de logout

**Frontend:**
- `frontend/src/features/auth/services/auth.service.ts` - logout() ahora llama al API
- `frontend/src/app/Dashboard.tsx` - handleLogout async con estado loading
- `frontend/tests/e2e/auth-login.spec.ts` - Actualizados tests E2E de logout

**Docs:**
- `docs/backend/autenticacion.md` - Agregada sección de logout
- `docs/hu-tareas/TR-003(MH)-logout.md` - Actualizado estado de tareas

---

## Entrada #10

**Fecha:** 2026-01-28  
**Herramienta:** Cursor (Claude)  
**HU/TR relacionada:** TR-001(MH)-login-de-empleado.md (Corrección de tests)

### Problema identificado

Después de ejecutar `migrate:fresh --seed`, los tests de autenticación fallaban con error `UniqueConstraintViolationException`:

```
Cannot insert duplicate key row in object 'dbo.USERS' with unique index 'idx_users_code'. 
The duplicate key value is (JPEREZ).
```

### Causa raíz

Los tests usan `DatabaseTransactions` para mejor rendimiento con SQL Server remoto. Sin embargo:
1. El `TestUsersSeeder` insertaba usuarios con códigos fijos ("JPEREZ", "MGARCIA", etc.) en la base de datos real
2. Los tests intentaban insertar los mismos usuarios dentro de las transacciones
3. Aunque `DatabaseTransactions` hace rollback después de cada test, los datos del seeder permanecían

### Solución implementada

Modificar el método `seedTestUsers()` en los archivos de test para que **limpie los datos existentes antes de insertar**:

```php
protected function seedTestUsers(): void
{
    // Limpiar usuarios existentes que podrían causar conflictos
    $testCodes = ['JPEREZ', 'MGARCIA', 'INACTIVO', 'INHABILITADO', 'USUINACTIVO'];
    
    // Eliminar de PQ_PARTES_USUARIOS primero (por FK)
    DB::table('PQ_PARTES_USUARIOS')->whereIn('code', $testCodes)->delete();
    
    // Eliminar tokens asociados
    $userIds = DB::table('USERS')->whereIn('code', $testCodes)->pluck('id');
    if ($userIds->isNotEmpty()) {
        DB::table('personal_access_tokens')
            ->where('tokenable_type', 'App\\Models\\User')
            ->whereIn('tokenable_id', $userIds)
            ->delete();
    }
    
    // Eliminar de USERS
    DB::table('USERS')->whereIn('code', $testCodes)->delete();
    
    // ... continúa con los inserts normales
}
```

### Archivos modificados

- `backend/tests/Unit/Services/AuthServiceTest.php` - Agregada limpieza de datos antes de inserts
- `backend/tests/Feature/Api/V1/Auth/LoginTest.php` - Agregada limpieza de datos antes de inserts
- `backend/tests/Feature/Api/V1/Auth/LogoutTest.php` - Agregada limpieza de datos antes de inserts

### Resultado

31 tests pasando correctamente:
- `AuthServiceTest`: 13 tests
- `LoginTest`: 11 tests  
- `LogoutTest`: 7 tests

### Decisión técnica

Se optó por limpiar datos existentes en lugar de:
1. Usar `RefreshDatabase` (más lento con SQL Server remoto)
2. Cambiar códigos de usuarios de test (requiere cambiar más código)
3. Eliminar el `TestUsersSeeder` (útil para desarrollo manual)

Esta solución permite que los tests sean idempotentes y puedan ejecutarse independientemente del estado de la base de datos.

---

## Entrada #11

### Fecha
2026-01-28

### Etapa del proyecto
Backend + Tests - TR-002 Login de Cliente

### Herramientas de IA utilizadas
- Cursor IDE (Claude)

### Prompt o instrucción utilizada
```
Implementá la TR funcional ubicada en:
"docs/tareas/TR-002(SH)-login-de-cliente.md"

Esta TR es la FUENTE DE VERDAD del alcance.
...
```

### Resultado generado por IA

**TR-002 generado y ejecutado:**

1. **AuthService extendido** para soportar login de clientes:
   - Busca primero en `PQ_PARTES_USUARIOS` (empleado)
   - Si no existe, busca en `PQ_PARTES_CLIENTES` (cliente)
   - Retorna `tipo_usuario="cliente"`, `es_supervisor=false`, `cliente_id`

2. **Seed de clientes de prueba:**
   - CLI001 (cliente activo)
   - CLIINACTIVO (cliente inactivo)
   - SINPERFIL (usuario sin perfil en ninguna tabla)

3. **Tests unitarios** (6 nuevos tests en AuthServiceTest):
   - `login_exitoso_con_cliente`
   - `login_cliente_retorna_es_supervisor_false`
   - `login_cliente_retorna_usuario_id_null`
   - `login_fallido_cliente_inactivo_en_pq_partes_clientes`
   - `login_fallido_usuario_sin_perfil_en_ninguna_tabla`
   - `login_cliente_retorna_todos_los_campos_requeridos`

4. **Tests de integración** (6 nuevos tests en LoginTest):
   - `login_exitoso_cliente_retorna_200`
   - `login_cliente_retorna_tipo_usuario_cliente`
   - `login_cliente_retorna_es_supervisor_false`
   - `login_cliente_retorna_usuario_id_null_y_cliente_id_valido`
   - `login_fallido_cliente_inactivo_retorna_401_error_4203`
   - `login_cliente_genera_token_valido`

5. **Tests E2E Playwright** (4 nuevos tests):
   - `debe autenticar cliente y redirigir al dashboard`
   - `debe almacenar tipo_usuario cliente en localStorage`
   - `cliente NO debe tener badge de supervisor`
   - `cliente puede hacer logout igual que empleado`

6. **Documentación actualizada:**
   - `docs/backend/autenticacion.md` - Agregada información de login de cliente
   - `docs/hu-tareas/TR-002(SH)-login-de-cliente.md` - TR completo creado

### Archivos creados/modificados

**Backend:**
- `backend/app/Services/AuthService.php` - Extendido con métodos `loginEmpleado()` y `loginCliente()`
- `backend/database/seeders/TestUsersSeeder.php` - Agregados clientes de prueba

**Tests:**
- `backend/tests/Unit/Services/AuthServiceTest.php` - 6 tests de cliente agregados
- `backend/tests/Feature/Api/V1/Auth/LoginTest.php` - 6 tests de cliente agregados
- `frontend/tests/e2e/auth-login.spec.ts` - 4 tests de cliente agregados

**Docs:**
- `docs/hu-tareas/TR-002(SH)-login-de-cliente.md` - Creado
- `docs/backend/autenticacion.md` - Actualizado

### Ajustes humanos realizados

Ninguno requerido. La implementación siguió estrictamente la TR generada.

### Decisiones técnicas

1. **Refactoring del AuthService**: Se extrajo la lógica de login de empleado y cliente a métodos privados separados (`loginEmpleado()` y `loginCliente()`) para mantener el código limpio y fácil de mantener.

2. **Prioridad empleado vs cliente**: Si un código existe en ambas tablas (caso no permitido por reglas de negocio), se prioriza `PQ_PARTES_USUARIOS` por seguridad.

3. **Tests idempotentes**: Los tests limpian datos existentes incluyendo los códigos de clientes antes de insertar, manteniendo consistencia con el patrón establecido en TR-001.

4. **Frontend sin cambios**: El formulario de login ya soportaba el flujo completo; solo se agregaron tests E2E para verificar el comportamiento con clientes.

---

## Entrada #12

### Fecha
2026-01-28

### Etapa del proyecto
Backend + Frontend + Tests - TR-006 Visualización de Perfil de Usuario

### Herramientas de IA utilizadas
- Cursor IDE (Claude)

### Prompt o instrucción utilizada
```
Implementá la TR funcional ubicada en:
"docs/tareas/TR-006(MH)-visualización-de-perfil-de-usuario.md"

Esta TR es la FUENTE DE VERDAD del alcance.
...
```

### Resultado generado por IA

**TR-006 implementada completamente:**

1. **Backend - UserProfileService:**
   - Servicio que obtiene datos del perfil según tipo de usuario (empleado/cliente)
   - Métodos privados `buildEmpleadoProfile()`, `buildClienteProfile()`, `buildMinimalProfile()`
   - Maneja casos edge (usuario sin perfil)

2. **Backend - UserProfileController:**
   - Endpoint GET `/api/v1/user/profile`
   - Requiere autenticación (middleware auth:sanctum)
   - Retorna datos formateados en formato envelope estándar

3. **Backend - Tests unitarios** (8 tests en UserProfileServiceTest):
   - `getProfile_empleado_normal_retorna_datos_correctos`
   - `getProfile_empleado_supervisor_retorna_es_supervisor_true`
   - `getProfile_cliente_retorna_datos_correctos`
   - `getProfile_usuario_sin_perfil_retorna_perfil_minimo`
   - `getProfile_empleado_sin_email_retorna_null`
   - `getProfile_retorna_todos_los_campos_requeridos`
   - `getProfile_fecha_creacion_formato_iso8601`

4. **Backend - Tests de integración** (7 tests en UserProfileTest):
   - `get_profile_empleado_normal_retorna_200`
   - `get_profile_empleado_supervisor_retorna_es_supervisor_true`
   - `get_profile_cliente_retorna_tipo_cliente`
   - `get_profile_sin_autenticacion_retorna_401`
   - `get_profile_empleado_sin_email_retorna_null`
   - `respuesta_tiene_formato_envelope_correcto`

5. **Frontend - Servicio de usuario:**
   - `user.service.ts` con función `getProfile()`
   - Manejo de errores y tipos TypeScript

6. **Frontend - Componente ProfileView:**
   - Componente React con estados loading/error/success
   - Muestra todos los campos requeridos
   - Badge de supervisor si aplica
   - Botón volver al dashboard
   - Todos los data-testid requeridos

7. **Frontend - Navegación:**
   - Ruta `/perfil` agregada en App.tsx
   - Enlace "Ver Mi Perfil" agregado en Dashboard
   - Estilos CSS para ProfileView

8. **Tests E2E Playwright** (7 tests):
   - `debe mostrar el perfil del empleado después de login`
   - `debe mostrar badge de supervisor si el usuario es supervisor`
   - `debe mostrar "No configurado" si el email es null`
   - `debe mostrar el perfil del cliente correctamente`
   - `debe permitir volver al dashboard desde el perfil`
   - `debe mostrar loading mientras carga el perfil`
   - `debe redirigir a login si no está autenticado`

9. **Documentación actualizada:**
   - `docs/backend/autenticacion.md` - Agregado endpoint de perfil
   - `docs/hu-tareas/TR-006(MH)-visualización-de-perfil-de-usuario.md` - TR actualizado con trazabilidad

### Archivos creados/modificados

**Backend:**
- `backend/app/Services/UserProfileService.php` (CREADO)
- `backend/app/Http/Controllers/Api/V1/UserProfileController.php` (CREADO)
- `backend/routes/api.php` (MODIFICADO) - Ruta GET /api/v1/user/profile agregada

**Frontend:**
- `frontend/src/features/user/services/user.service.ts` (CREADO)
- `frontend/src/features/user/services/index.ts` (CREADO)
- `frontend/src/features/user/components/ProfileView.tsx` (CREADO)
- `frontend/src/features/user/components/ProfileView.css` (CREADO)
- `frontend/src/features/user/components/index.ts` (CREADO)
- `frontend/src/features/user/index.ts` (CREADO)
- `frontend/src/app/App.tsx` (MODIFICADO) - Ruta /perfil agregada
- `frontend/src/app/Dashboard.tsx` (MODIFICADO) - Enlace a perfil agregado
- `frontend/src/app/Dashboard.css` (MODIFICADO) - Estilos para botón de perfil

**Tests:**
- `backend/tests/Unit/Services/UserProfileServiceTest.php` (CREADO) - 8 tests
- `backend/tests/Feature/Api/V1/UserProfileTest.php` (CREADO) - 7 tests
- `frontend/tests/e2e/user-profile.spec.ts` (CREADO) - 7 tests

**Docs:**
- `docs/backend/autenticacion.md` (MODIFICADO) - Endpoint de perfil documentado
- `docs/hu-tareas/TR-006(MH)-visualización-de-perfil-de-usuario.md` (MODIFICADO) - Trazabilidad completada

### Ajustes humanos realizados

Ninguno requerido. La implementación siguió estrictamente la TR.

### Decisiones técnicas

1. **Estructura del servicio**: Se separó la lógica en métodos privados (`buildEmpleadoProfile`, `buildClienteProfile`, `buildMinimalProfile`) para mantener el código limpio y fácil de mantener.

2. **Manejo de email null**: El frontend muestra "No configurado" cuando el email es null, cumpliendo con AC-04.

3. **Formato de fecha**: Se usa `toIso8601String()` en el backend y `toLocaleDateString()` en el frontend para formatear la fecha de creación.

4. **Badge de supervisor**: Se muestra solo si `es_supervisor` es true, usando el mismo estilo que en el Dashboard.

5. **Navegación**: El botón "Volver" redirige al dashboard (`/`), cumpliendo con AC-11.

6. **Tests idempotentes**: Los tests limpian datos existentes antes de insertar, manteniendo consistencia con el patrón establecido.

---

## Entrada #13

### Fecha
2026-01-28

### Etapa del proyecto
Diseño - Generación de TR-028 Carga de Tarea Diaria

### Herramientas de IA utilizadas
- Cursor IDE (Claude)

### Prompt o instrucción utilizada
```
Actuá como ingeniero senior responsable del diseño del MVP.

Usá SOLO la regla
".cursor/rules/13-user-story-to-task-breakdown.md"
como fuente de verdad.

Tarea:
A partir de la Historia de Usuario provista,
generar el plan completo de tareas/tickets
y guardarlo como archivo Markdown.
...
Historia de Usuario:
---
HU-028(MH)-carga-de-tarea-diaria.md
---
```

### Resultado generado por IA

**TR-028 generado completamente** siguiendo la estructura de la regla:

1. **HU Refinada** con título, narrativa, contexto, suposiciones, in/out scope
2. **21 Criterios de Aceptación** con 7 escenarios Gherkin
3. **10 Reglas de Negocio** con permisos y validaciones
4. **Impacto en Datos** con tablas afectadas y seed mínimo
5. **4 Contratos de API** documentados (POST /api/v1/tasks, GET endpoints para clientes, tipos, empleados)
6. **Cambios Frontend** con 6 componentes nuevos, estados UI, validaciones, accesibilidad
7. **15 Tareas Planificadas** con DoD específico
8. **Estrategia de Tests** (Unit, Integration, E2E)
9. **Riesgos y Edge Cases**
10. **Checklist Final** con 20 items

**Problema detectado:** El TR generado inicialmente especificaba formato de fechas sin considerar la regla establecida del proyecto:
- El TR especificaba conversión DMY ↔ YMD en múltiples puntos del código
- No consideraba que los componentes de fecha manejan internamente YMD y solo requieren formateo de visualización

### Ajustes humanos realizados

**Primera modificación solicitada:**
El usuario detectó que el formato de fechas no estaba acorde con las reglas del proyecto:
- **Regla establecida:** Base de datos YMD, Frontend DMY para interacción con usuario
- **Problema:** El TR no especificaba claramente esta separación de formatos
- **Ajuste:** Se actualizó el TR para reflejar:
  - Frontend muestra/captura en formato DMY (DD/MM/YYYY)
  - Frontend convierte DMY → YMD antes de enviar al API
  - Backend/BD trabajan exclusivamente con YMD (YYYY-MM-DD)
  - Se agregaron funciones de conversión en `dateUtils.ts`
  - Se actualizaron todas las secciones relevantes (AC, Reglas, Plan de Tareas, Tests)

**Segunda modificación solicitada:**
El usuario sugirió una aproximación más práctica y eficiente:
- **Observación:** Los controles de fecha pueden formatear la presentación sin requerir conversión en el código
- **Problema:** El TR especificaba conversiones innecesarias en múltiples puntos
- **Ajuste:** Se refinó el TR para reflejar:
  - Todo el sistema maneja fechas internamente en formato YMD (YYYY-MM-DD)
  - Solo la visualización al usuario se formatea a DMY (DD/MM/YYYY)
  - Los componentes de fecha (`<input type="date">` o librerías) manejan internamente YMD
  - No se requiere conversión de formato, solo formateo de visualización
  - Se simplificaron las tareas del plan (T9, T10) para usar formateo en lugar de conversión
  - Se actualizó la estrategia de tests para verificar formateo de visualización
  - Se documentaron opciones recomendadas de componentes de fecha

### Motivo del ajuste

**Primera modificación:**
Alinear el TR con la regla establecida del proyecto sobre formato de fechas, asegurando consistencia entre frontend (DMY para usuario) y backend/BD (YMD interno).

**Segunda modificación:**
Simplificar la implementación aprovechando las capacidades nativas de los componentes de fecha, evitando conversiones innecesarias y reduciendo complejidad del código. Esta aproximación es más práctica, eficiente y mantenible.

### Decisiones técnicas

1. **Formato interno único:** Todo el sistema maneja fechas en formato YMD internamente, simplificando el código y evitando errores de conversión.

2. **Formateo de visualización:** Solo se formatea la visualización a DMY para el usuario, usando funciones de formato (date-fns, dayjs) o configuración del componente de fecha.

3. **Componentes de fecha:** Se recomienda usar `<input type="date">` nativo HTML5 o librerías como react-datepicker que manejan internamente YMD y permiten configurar formato de visualización.

4. **Sin conversiones:** Se eliminaron todas las conversiones de formato del código, solo se mantiene formateo de visualización.

### Archivos creados/modificados

**Docs:**
- `docs/hu-tareas/TR-028(MH)-carga-de-tarea-diaria.md` (CREADO y MODIFICADO 2 veces)
- `prompts.md` (MODIFICADO) - Agregados Prompt 10 y Prompt 11
- `docs/ia-log.md` (MODIFICADO) - Agregada Entrada #13

### Referencias

- `docs/hu-tareas/TR-028(MH)-carga-de-tarea-diaria.md` - TR completo con formato de fechas corregido
- `prompts.md` - Prompts 10 y 11 sobre formato de fechas

---

## Entrada #14

### Fecha
2026-01-28

### Etapa del proyecto
Implementación completa de TR-028(MH) - Carga de Tarea Diaria

### Herramientas de IA utilizadas
- Cursor IDE (generación de código completo)

### Prompt o instrucción utilizada
"Implementá la TR funcional ubicada en: 'docs/tareas/TR-028(MH)-carga-de-tarea-diaria.md'"

### Resultado generado por IA
Implementación completa del módulo de carga de tareas diarias incluyendo:

**Backend:**
- `CreateTaskRequest` con validaciones completas (fecha YMD, cliente, tipo de tarea, duración múltiplo de 15, observación, permisos)
- `TaskService` con lógica de negocio (validación de cliente activo, tipo de tarea válido para cliente, permisos de supervisor)
- `TaskController` con 4 endpoints (POST /tasks, GET /tasks/clients, GET /tasks/task-types, GET /tasks/employees)
- Rutas API configuradas
- `TestTasksSeeder` para datos de prueba
- Unit tests para `TaskService` (9 tests)
- Integration tests para `TaskController` (12 tests)

**Frontend:**
- `task.service.ts` con funciones para crear tarea y obtener listas (clientes, tipos, empleados)
- `dateUtils.ts` con funciones de formateo DMY para visualización y parsing YMD
- `TaskForm` componente completo con todos los campos, validaciones y manejo de estados
- `ClientSelector`, `TaskTypeSelector`, `EmployeeSelector` componentes reutilizables
- Rutas y navegación agregadas (`/tareas/nueva`)
- Botón "Cargar Tarea" en Dashboard
- E2E Playwright tests (9 tests)

**Documentación:**
- `docs/backend/tareas.md` con documentación completa de API

### Ajustes humanos realizados
- Verificación de estructura de tabla existente antes de crear código
- Ajuste de seeder para asegurar datos de prueba correctos (CLI002, ESPECIAL, asignaciones)
- Validación de formato de fecha YMD en FormRequest y tests
- Implementación de validaciones custom para tipo de tarea asignado al cliente
- Manejo de permisos de supervisor en backend y frontend
- Creación de componentes de selectores con carga dinámica y manejo de estados
- Implementación de advertencia de fecha futura (no bloquea)
- Tests E2E con verificación de formato YMD en requests al API

### Motivo del ajuste
Asegurar que la implementación cumpla estrictamente con el TR, respete las reglas de negocio establecidas (formato de fechas YMD interno, DMY visualización), y mantenga consistencia con TRs ya implementadas (TR-001, TR-002, TR-006).

### Decisiones técnicas

1. **Formato de fechas:** Implementación siguiendo la decisión de Entrada #13: YMD interno, DMY solo para visualización usando `dateUtils.ts`.

2. **Validaciones:** Validaciones en frontend (UX inmediata) y backend (seguridad), incluyendo validación custom de tipo de tarea asignado al cliente.

3. **Selectores dinámicos:** Los selectores cargan datos desde el API y se actualizan dinámicamente (tipos de tarea según cliente seleccionado).

4. **Permisos:** Selector de empleado solo visible para supervisores, validación de permisos en backend.

5. **Tests:** Cobertura completa con unit tests, integration tests y E2E tests verificando formato de fechas.

### Archivos creados/modificados

**Backend:**
- `backend/app/Http/Requests/Api/V1/CreateTaskRequest.php` (CREADO)
- `backend/app/Services/TaskService.php` (CREADO)
- `backend/app/Http/Controllers/Api/V1/TaskController.php` (CREADO)
- `backend/routes/api.php` (MODIFICADO)
- `backend/database/seeders/TestTasksSeeder.php` (CREADO)
- `backend/tests/Unit/Services/TaskServiceTest.php` (CREADO)
- `backend/tests/Feature/Api/V1/TaskControllerTest.php` (CREADO)

**Frontend:**
- `frontend/src/shared/utils/dateUtils.ts` (CREADO)
- `frontend/src/features/tasks/services/task.service.ts` (CREADO)
- `frontend/src/features/tasks/services/index.ts` (CREADO)
- `frontend/src/features/tasks/components/TaskForm.tsx` (CREADO)
- `frontend/src/features/tasks/components/TaskForm.css` (CREADO)
- `frontend/src/features/tasks/components/ClientSelector.tsx` (CREADO)
- `frontend/src/features/tasks/components/TaskTypeSelector.tsx` (CREADO)
- `frontend/src/features/tasks/components/EmployeeSelector.tsx` (CREADO)
- `frontend/src/features/tasks/components/index.ts` (CREADO)
- `frontend/src/features/tasks/index.ts` (CREADO)
- `frontend/src/app/App.tsx` (MODIFICADO)
- `frontend/src/app/Dashboard.tsx` (MODIFICADO)
- `frontend/tests/e2e/task-create.spec.ts` (CREADO)

**Docs:**
- `docs/backend/tareas.md` (CREADO)
- `docs/ia-log.md` (MODIFICADO) - Agregada Entrada #14

### Referencias

- `docs/hu-tareas/TR-028(MH)-carga-de-tarea-diaria.md` - TR completo implementado
- `docs/backend/tareas.md` - Documentación de API de tareas

---

## Entrada #15

### Fecha
2026-01-28

### Etapa del proyecto
Planificación - Generación masiva de TRs desde HU (HU-029 a HU-038)

### Herramientas de IA utilizadas
- Cursor IDE (Claude) - Generación masiva controlada

### Prompt o instrucción utilizada
```
Aplicá el flujo definido para conversión de Historias de Usuario a tareas
sobre las Historias de Usuario numeradas del 29 al 38 inclusive,
ubicadas en la carpeta docs/hu-historias/.

[... ver prompt completo en PROMPTS/07 - Generaciòn HU a TR masivo.md ...]
```

### Resultado generado por IA
Procesamiento masivo de 10 Historias de Usuario (HU-029 a HU-038) para generar los TRs correspondientes:

**HU procesadas:**
- HU-029: Edición de tarea propia
- HU-030: Eliminación de tarea propia
- HU-031: Edición de tarea (supervisor)
- HU-032: Eliminación de tarea (supervisor)
- HU-033: Visualización de lista de tareas propias
- HU-034: Visualización de lista de todas las tareas (supervisor)
- HU-035: Validación de duración en tramos de 15 minutos
- HU-036: Advertencia de fecha futura
- HU-037: Filtrado de tipos de tarea por cliente
- HU-038: Selección de empleado propietario (supervisor)

**Proceso aplicado:**
1. Determinación automática de complejidad (SIMPLE vs COMPLEJA) para cada HU
2. Generación de TRs completos siguiendo la estructura estándar del proyecto
3. Clasificación de tareas por capa (Backend, Frontend, DB, Testing)
4. Identificación de dependencias entre tareas
5. Verificación de coherencia con MVP y entregables
6. Documentación consolidada en archivo de resumen

### Ajustes humanos realizados
- Revisión de clasificación SIMPLE/COMPLEJA según criterios del proyecto
- Validación de coherencia con TRs ya implementadas (especialmente TR-028)
- Verificación de dependencias entre HU y TRs
- Control de calidad sobre la estructura y completitud de cada TR generado

### Motivo del ajuste
Asegurar que la generación masiva mantenga la calidad y consistencia con el resto del proyecto, validando que cada TR sea ejecutable y completo según los estándares establecidos.

### Decisiones técnicas
1. **Criterio conservador:** En caso de duda sobre complejidad, se trató la HU como COMPLEJA para asegurar refinamiento adecuado.
2. **Reutilización:** Se identificaron funcionalidades ya implementadas parcialmente en TR-028 (HU-035, HU-036, HU-037, HU-038) para evitar duplicación.
3. **Dependencias explícitas:** Se documentaron claramente las dependencias entre HU y TRs para facilitar la planificación de implementación.

### Archivos creados/modificados

**Docs:**
- `docs/hu-tareas/TR-029(MH)-edición-de-tarea-propia.md` (GENERADO)
- `docs/hu-tareas/TR-030(MH)-eliminación-de-tarea-propia.md` (GENERADO)
- `docs/hu-tareas/TR-031(MH)-edición-de-tarea-supervisor.md` (GENERADO)
- `docs/hu-tareas/TR-032(MH)-eliminación-de-tarea-supervisor.md` (GENERADO)
- `docs/hu-tareas/TR-033(MH)-visualización-de-lista-de-tareas-propias.md` (GENERADO)
- `docs/hu-tareas/TR-034(MH)-visualización-de-lista-de-todas-las-tareas-supervisor.md` (GENERADO)
- `docs/hu-tareas/TR-035(MH)-validación-de-duración-en-tramos-de-15-minutos.md` (GENERADO)
- `docs/hu-tareas/TR-036(MH)-advertencia-de-fecha-futura.md` (GENERADO)
- `docs/hu-tareas/TR-037(MH)-filtrado-de-tipos-de-tarea-por-cliente.md` (GENERADO)
- `docs/hu-tareas/TR-038(MH)-selección-de-empleado-propietario-supervisor.md` (GENERADO)
- `docs/tareas-generadas-hu-29-a-38.md` (GENERADO) - Resumen consolidado

### Referencias
- `PROMPTS/07 - Generaciòn HU a TR masivo.md` - Prompt utilizado
- `prompts.md` - Prompt 12 documentado

---

## Entrada — Implementación TR-033 (Visualización de Lista de Tareas Propias)

### Fecha
2026-01-29

### Etapa del proyecto
Implementación de TR-033(MH): Visualización de Lista de Tareas Propias (empleado ve sus tareas con filtros, paginación y totales).

### Herramientas de IA utilizadas
- Cursor (agente IA)

### Prompt o instrucción utilizada
“Ejecutá la TR TR-033(MH)-visualización-de-lista-de-tareas-propias.md” — siguiendo PROMPTS/05 - Ejecucion-de-una-TR.md (implementar backend, frontend, tests y documentación según la TR).

### Resultado generado por IA
- **Backend:** TaskService::listTasks() con filtros (fecha, cliente, tipo, búsqueda), paginación y totales; TaskController::index() GET /api/v1/tasks; ruta registrada.
- **Frontend:** getTasks() en task.service.ts; TaskList, TaskFilters, TaskPagination, TaskTotals; ruta /tareas; enlace “Mis Tareas” en Dashboard; data-testid según TR.
- **Tests:** Unit (TaskServiceTest::test_list_tasks_*), integration (TaskControllerTest::index_*), E2E Playwright (task-list.spec.ts).
- **Docs:** TR actualizada con archivos creados/modificados, comandos, notas y pendientes; ia-log actualizado.

### Ajustes humanos realizados
- Ninguno en esta iteración (implementación directa según TR).

### Motivo del ajuste
Implementación completa de la TR dentro del alcance definido; editar/eliminar quedan como placeholders hasta TR-029/TR-030.

### Referencias
- `docs/hu-tareas/TR-033(MH)-visualización-de-lista-de-tareas-propias.md`
- `PROMPTS/05 - Ejecucion-de-una-TR.md`

---

## Entrada — Implementación TR-029 (Edición de Tarea Propia)

### Fecha
2026-01-29

### Etapa del proyecto
Implementación de TR-029(MH): Edición de Tarea Propia (empleado edita sus tareas no cerradas; permisos y validaciones según TR).

### Herramientas de IA utilizadas
- Cursor (agente IA)

### Prompt o instrucción utilizada
“Ejecutar la tarea TR-029(MH) – Edición de Tarea Propia” — siguiendo la TR (backend GET/PUT, frontend formulario modo edición, ruta /tareas/:id/editar, tests).

### Resultado generado por IA
- **Backend:** TaskService::getTask(id, user) y updateTask(id, datos, user); constantes ERROR_CLOSED (2110), ERROR_FORBIDDEN_EDIT (4030); UpdateTaskRequest; TaskController::show() y update(); rutas GET/PUT /api/v1/tasks/{id}; tests unitarios e integración.
- **Frontend:** getTask(id) y updateTask(id, payload) en task.service.ts; TaskForm con prop taskId (modo edición, carga con getTask, empleado solo lectura, submit updateTask); TaskEditPage; ruta /tareas/:id/editar; estilos .form-input-readonly.
- **Tests:** Vitest getTask/updateTask (mock API, 200/404/2110/4030/422); E2E task-edit.spec.ts (navegar a editar, título Editar Tarea).

### Ajustes humanos realizados
- Ninguno en esta iteración.

### Referencias
- `docs/hu-tareas/TR-029(MH)-edición-de-tarea-propia.md`

---

## Entrada — Implementación TR-030 (Eliminación de Tarea Propia)

### Fecha
2026-01-29

### Etapa del proyecto
Implementación de TR-030(MH): Eliminación de Tarea Propia (empleado elimina sus tareas no cerradas; diálogo de confirmación; errores 2111/4030/4040).

### Herramientas de IA utilizadas
- Cursor (agente IA)

### Prompt o instrucción utilizada
“Ejecutà la tarea TR-030(MH)-eliminación-de-tarea-propia.md” — siguiendo la TR (backend DELETE, frontend DeleteTaskModal, TaskList integrado, tests).

### Resultado generado por IA
- **Backend:** TaskService::deleteTask(id, user); constantes ERROR_CLOSED_DELETE (2111), ERROR_FORBIDDEN_DELETE (4030); TaskController::destroy(); ruta DELETE /api/v1/tasks/{id}; handleTaskException para 2111 y 4030; tests unitarios (4) e integración (4).
- **Frontend:** deleteTask(id) y DeleteTaskResult en task.service.ts; DeleteTaskModal (fecha, cliente, tipo, duración; Confirmar/Cancelar); TaskList con taskToDelete, deleteLoading, deleteError, successMessage; mensaje “Tarea eliminada correctamente” y recarga de lista.
- **Tests:** Vitest deleteTask (200, 404, 2111, 4030); E2E task-delete.spec.ts (modal visible, cancelar, confirmar eliminación).

### Ajustes humanos realizados
- Ninguno en esta iteración.

### Referencias
- `docs/hu-tareas/TR-030(MH)-eliminación-de-tarea-propia.md`

---

## Entrada — Implementación TR-034 (Visualización de Lista de Todas las Tareas — Supervisor)

### Fecha
2026-01-29

### Etapa del proyecto
Implementación de TR-034(MH): Visualización de Lista de Todas las Tareas (supervisor ve todas las tareas en tabla paginada con filtros por empleado/cliente y ordenamiento).

### Herramientas de IA utilizadas
- Cursor (agente IA)

### Prompt o instrucción utilizada
“Ejecutar la tarea TR-034(MH) – Visualización de Lista de Todas las Tareas (Supervisor)” — backend GET /api/v1/tasks/all, frontend ruta /tareas/todas, TaskListAll, SupervisorRoute, tests E2E.

### Resultado generado por IA
- **Backend:** Ruta GET /api/v1/tasks/all; TaskController::indexAll() (solo supervisor, 403 empleado); TaskService::listTasks() extendido con ordenamiento por empleado/cliente y soporte para listar todas las tareas; tests de integración indexAll_supervisor e indexAll_empleado_403.
- **Frontend:** getAllTasks() en task.service.ts; SupervisorRoute (protege rutas solo supervisor); TaskListAll (tabla con columna Empleado, filtros, paginación); ruta /tareas/todas; botón “Todas las Tareas” en Dashboard (solo supervisores); returnPath en edición para volver a /tareas/todas.
- **Tests:** E2E task-list-all.spec.ts (supervisor accede a lista, ve columna Empleado y filtros; empleado redirigido a /).

### Ajustes humanos realizados
- Ninguno en esta iteración.

### Referencias
- `docs/hu-tareas/TR-034(MH)-visualización-de-lista-de-todas-las-tareas-supervisor.md`

---

## Entrada — Implementación TR-044 (Consulta Detallada de Tareas)

### Fecha
2026-01-30

### Etapa del proyecto
Implementación de TR-044(MH): Consulta Detallada de Tareas (empleado, supervisor y cliente consultan tareas con filtros por rol; período, total horas, ordenamiento, paginación; error 1305 período inválido).

### Herramientas de IA utilizadas
- Cursor (agente IA)

### Prompt o instrucción utilizada
“Generá la tarea tr-044(MH)” — implementar backend (listDetailReport, ReportController, GET /reports/detail), frontend (getDetailReport, ConsultaDetalladaPage, filtros según rol, tabla, paginación, total horas), rutas y navegación.

### Resultado generado por IA
- **Backend:** TaskService::listDetailReport(user, filters) con filtros por rol (empleado solo sus tareas, supervisor todas con opcionales tipo_cliente_id/cliente_id/usuario_id, cliente solo su cliente_id); validación período 1305; constante ERROR_PERIODO_INVALIDO; ReportController::detail(); ruta GET /api/v1/reports/detail; respuesta data[], pagination, total_horas (decimal).
- **Frontend:** getDetailReport(params), DetailReportItem, DetailReportParams, GetDetailReportResult en task.service.ts; ConsultaDetalladaPage (filtros período y para supervisor cliente/empleado, tabla con columnas empleado/cliente/fecha/tipo/horas/sin_cargo/presencial/descripción, ordenamiento por cabeceras, paginación, total horas, estado vacío); ruta /informes/consulta-detallada; enlace “Consulta Detallada” en Dashboard (todos los usuarios).
- **Estilos:** ConsultaDetalladaPage.css.

### Ajustes humanos realizados
- Ninguno en esta iteración.

### Referencias
- `docs/hu-tareas/TR-044(MH)-consulta-detallada-de-tareas.md`

---

## Entrada — Corrección TR-044: filtro por cliente para empleado

### Fecha
2026-01-30

### Etapa del proyecto
Corrección en TR-044: que el empleado (no supervisor) pueda filtrar por cliente en Consulta Detallada de Tareas.

### Herramientas de IA utilizadas
- Cursor (agente IA)

### Prompt o instrucción utilizada
Corregir en el documento de la tarea y en la programación que cuando el empleado NO supervisor procesa "Consulta detallada de tareas" también pueda filtrar por cliente.

### Resultado generado por IA
- **Documento TR-044:** ya contemplaba filtro cliente para empleado (AC-06, RN-06, RN-07, Permisos por Rol).
- **Frontend ConsultaDetalladaPage.tsx:** filtro "Cliente" visible para todo usuario no cliente (`!isCliente`), no solo supervisor; `buildParams` envía `cliente_id` cuando `!isCliente` y hay valor; carga de lista de clientes cuando `!isCliente`; filtro "Empleado" sigue solo para supervisor.
- **E2E:** aserción en test de empleado para comprobar que ve el combobox Cliente.
- **Docs:** actualización de `.cursor/Docs/ConsultaDetalladaPage.tsx.md`.

### Ajustes humanos realizados
- Ninguno.

### Referencias
- `docs/hu-tareas/TR-044(MH)-consulta-detallada-de-tareas.md`

---

## Entrada — Implementación TR-046 (Consulta Agrupada por Cliente)

### Fecha
2026-01-30

### Etapa del proyecto
Implementación de TR-046(MH): Consulta Agrupada por Cliente (empleado, supervisor y cliente consultan tareas agrupadas por cliente; filtros por período; accordion con detalle; total general; error 1305 período inválido).

### Herramientas de IA utilizadas
- Cursor (agente IA)

### Prompt o instrucción utilizada
"Ejecuta la tarea tr-046" — implementar backend (listByClientReport, ReportController::byClient, GET /reports/by-client), frontend (getReportByClient, TareasPorClientePage con filtros período, accordion por cliente, total general), ruta, Dashboard, tests y documentación.

### Resultado generado por IA
- **Backend:** TaskService::listByClientReport(user, filters) con filtros por rol; validación período 1305; agregación por cliente_id; grupos con nombre, tipo_cliente, total_horas, cantidad_tareas, tareas[]; orden por total_horas desc. ReportController::byClient(); ruta GET /api/v1/reports/by-client.
- **Frontend:** getReportByClient(params), TareasPorClientePage (filtros período, accordion por cliente, total general); ruta /informes/tareas-por-cliente; botón "Tareas por Cliente" en Dashboard.
- **Tests:** Unit listByClientReport; Feature by_client; Vitest getReportByClient; E2E tareas-por-cliente.spec.ts.
- **Docs:** docs/backend/tareas.md GET /reports/by-client; .cursor/Docs/TareasPorClientePage.tsx.md; TR-046 Archivos y Comandos.

### Ajustes humanos realizados
- Ninguno en esta iteración.

### Referencias
- `docs/hu-tareas/TR-046(MH)-consulta-agrupada-por-cliente.md`

---

## Entrada — Implementación TR-050 (Manejo de resultados vacíos en consultas)

### Fecha
2026-01-31

### Etapa del proyecto
Implementación de TR-050(MH): Manejo de resultados vacíos en consultas (HU-050). Mensaje único cuando no hay resultados; no tabla/lista vacía; accesibilidad; E2E estado vacío.

### Herramientas de IA utilizadas
- Cursor (agente IA)

### Prompt o instrucción utilizada
"Ejecutá la tarea TR-050(MH)" — implementar según TR: revisar ConsultaDetalladaPage y TareasPorClientePage estado vacío, unificar mensaje, role="status", E2E estado vacío, docs y ia-log.

### Resultado generado por IA
- **Frontend ConsultaDetalladaPage:** Añadido `role="status"` al bloque de estado vacío (data-testid report.detail.empty). Mensaje ya correcto.
- **Frontend TareasPorClientePage:** Mensaje unificado a "No se encontraron tareas para los filtros seleccionados" (clave report.detail.empty); añadido `role="status"` (data-testid report.byClient.empty).
- **T3 Exportar:** No existe botón Exportar en Consulta Detallada ni Tareas por Cliente; N/A.
- **E2E:** consulta-detallada.spec.ts: test TR-050 con período 2030, verifica report.detail.empty y texto. tareas-por-cliente.spec.ts: test TR-050 con período 2030, verifica report.byClient.empty y texto.
- **Docs:** docs/testing.md — subsección "Estado vacío en consultas (HU-050 / TR-050)".

### Ajustes humanos realizados
- Ninguno en esta iteración.

### Referencias
- `docs/hu-tareas/TR-050(MH)-manejo-de-resultados-vacíos-en-consultas.md`

---

## Entrada — Implementación TR-051 (Dashboard principal)

### Fecha
2026-01-31

### Etapa del proyecto
Implementación de TR-051(MH): Dashboard principal. Backend ya existía (GET /api/v1/dashboard, TaskService::getDashboardData). Frontend: servicio getDashboard, componente Dashboard con selector de período (mes actual por defecto), KPIs (total horas, cantidad tareas, promedio horas/día), Top clientes (empleado/supervisor), Top empleados (solo supervisor), Distribución por tipo (solo cliente). Estados loading/error/vacío (HU-050). E2E dashboard.spec.ts por rol.

### Herramientas de IA utilizadas
- Cursor (agente IA)

### Prompt o instrucción utilizada
"Ejecutá la TR TR-051(MH) y si pasa todos los tests Ejecutá la TR TR-052(MH)".

### Resultado generado por IA
- **Frontend task.service.ts:** Interfaces DashboardData, DashboardTopCliente, DashboardTopEmpleado, DashboardDistribucionTipo, DashboardParams, GetDashboardResult; función getDashboard(fecha_desde, fecha_hasta).
- **Frontend Dashboard.tsx:** Selector de período (fecha desde/hasta, botón "Mes actual"); llamada a getDashboard al montar y al cambiar período; KPIs; bloques Top clientes (empleado/supervisor), Top empleados (supervisor), Distribución por tipo (cliente); loading/error/empty; data-testid según TR-051.
- **Frontend Dashboard.css:** Estilos para secciones, período, KPIs, listas, estado vacío, error, responsive.
- **E2E dashboard.spec.ts:** Empleado (KPIs + Top clientes, sin Top empleados); Supervisor (Top clientes + Top empleados); Cliente (Distribución por tipo, sin Top empleados); cambio de período; botón Mes actual.
- **Docs:** .cursor/Docs/Dashboard.tsx.md; ia-log.md entrada TR-051.

### Ajustes humanos realizados
- Ninguno en esta iteración.

### Referencias
- `docs/hu-tareas/TR-051(MH)-dashboard-principal.md`

---

## Entrada — Implementación TR-052 (Resumen de dedicación por cliente en dashboard)

### Fecha
2026-01-31

### Etapa del proyecto
Implementación de TR-052(MH): Resumen de dedicación por cliente en dashboard. Sección "Dedicación por Cliente" en el Dashboard que reutiliza top_clientes del mismo endpoint; total general; enlace "Ver detalle" por cliente a Tareas por Cliente con query params (cliente_id, fecha_desde, fecha_hasta). TareasPorClientePage lee esos params para prellenar filtros y expandir el cliente. E2E en dashboard.spec.ts.

### Herramientas de IA utilizadas
- Cursor (agente IA)

### Prompt o instrucción utilizada
"Ejecutá la TR TR-051(MH) y si pasa todos los tests Ejecutá la TR TR-052(MH)".

### Resultado generado por IA
- **Dashboard.tsx:** Sección "Dedicación por Cliente" con lista (nombre, horas, cantidad, porcentaje), total general, enlace "Ver detalle" por fila a /informes/tareas-por-cliente?cliente_id=X&fecha_desde=...&fecha_hasta=...
- **Dashboard.css:** Estilos dashboard-list-item-with-action, dashboard-link-detalle, dashboard-total-general, responsive.
- **TareasPorClientePage.tsx:** useSearchParams para leer cliente_id, fecha_desde, fecha_hasta; inicializar filtros y expandedClienteId desde URL.
- **dashboard.spec.ts:** Test E2E TR-052 (sección dedicación, total general, clic Ver detalle).
- **Docs:** .cursor/Docs/TR-052(MH)-resumen-de-dedicación-por-cliente-en-dashboard.md; actualización TR-052 y ia-log.

### Ajustes humanos realizados
- Ninguno en esta iteración.

### Referencias
- `docs/hu-tareas/TR-052(MH)-resumen-de-dedicación-por-cliente-en-dashboard.md`

---

## Entrada #XX+1

### Fecha
2026-02-05

### Etapa del proyecto
Backend y Frontend – Implementación TR-019(MH) Creación de Empleado

### Herramientas de IA utilizadas
- Cursor (agente IA)

### Prompt o instrucción utilizada
"Ejecuta la tarea TR-019(mh)"

### Resultado generado por IA
- **EmpleadoService.php:** Agregado método create() con validaciones (code, nombre, email, password), creación primero User en USERS (code, password_hash, activo, inhabilitado) y luego empleado en PQ_PARTES_USUARIOS con user_id y mismo code, validación de code único en USERS y email único, manejo de errores 422/409.
- **EmpleadoController.php:** Agregado método store() para POST /api/v1/empleados, validación de request, manejo de respuestas 201/422/409/403/500.
- **api.php:** Ruta POST /api/v1/empleados agregada.
- **EmpleadoControllerTest.php:** Agregados 10 tests de integración (creación correcta, 403 empleado, 401 sin token, código duplicado 409, email duplicado 409, validaciones 422).
- **empleado.service.ts:** Agregada función createEmpleado() con interfaces CreateEmpleadoBody, EmpleadoCreadoItem, CreateEmpleadoResult, manejo de respuestas 201/422/409/403.
- **EmpleadosNuevoPage.tsx:** Componente de formulario con campos código, nombre, email, contraseña, confirmar contraseña, supervisor (checkbox), activo (checkbox), inhabilitado (checkbox), validaciones frontend (contraseñas coinciden, mínimo 8 caracteres), manejo de errores por campo, mensaje de éxito y redirección.
- **EmpleadosNuevoPage.css:** Estilos consistentes con ClientesNuevaPage.
- **App.tsx:** Ruta /empleados/nuevo protegida por SupervisorRoute agregada.
- **empleado.service.test.ts:** Agregados 5 tests unitarios con Vitest (éxito 201, validación 422, conflicto 409, 403 no supervisor, valores por defecto).
- **empleados-create.spec.ts:** Tests E2E con Playwright (acceso al formulario, creación correcta, validación contraseñas no coinciden, validación contraseña corta, código duplicado).
- **Docs:** Actualización TR-019 con archivos creados/modificados, comandos ejecutados, notas y decisiones.

### Ajustes humanos realizados
- Ninguno en esta iteración. La implementación siguió el patrón establecido de TR-009 (creación de clientes) adaptado para empleados donde siempre se crea el User.

### Referencias
- `docs/hu-tareas/TR-019(MH)-creación-de-empleado.md`

---

## Entrada #XX

### Fecha
2026-02-05

### Etapa del proyecto
Backend y Frontend – Implementación TR-018(MH) Listado de Empleados

### Herramientas de IA utilizadas
- Cursor (agente IA)

### Prompt o instrucción utilizada
"Ejecuta la tarea TR-018(MH)"

### Resultado generado por IA
- **EmpleadoService.php:** Servicio con método list() para búsqueda (código, nombre, email), filtros (supervisor, activo, inhabilitado), paginación y ordenamiento.
- **EmpleadoController.php:** Controller con método index() para GET /api/v1/empleados, validación de permisos (solo supervisores), manejo de query params.
- **api.php:** Ruta GET /api/v1/empleados agregada.
- **EmpleadoControllerTest.php:** Tests de integración (supervisor accede, empleado 403, sin token 401, filtros, búsqueda, paginación).
- **empleado.service.ts:** Servicio frontend con función getEmpleados(), construcción de query params, manejo de respuestas 200/401/403.
- **EmpleadosPage.tsx:** Componente de listado con tabla (código, nombre, email, supervisor, estado, inhabilitado), búsqueda, filtros (supervisor, activo, inhabilitado), paginación, total, indicador visual para inhabilitados, acciones editar/eliminar (navegación a otras HU).
- **EmpleadosPage.css:** Estilos consistentes con ClientesPage.
- **App.tsx:** Ruta /empleados protegida por SupervisorRoute agregada.
- **empleado.service.test.ts:** Tests unitarios con Vitest (éxito, construcción params, errores 403/401, manejo de resultados vacíos).
- **empleados-list.spec.ts:** Tests E2E con Playwright (supervisor accede, tabla/filtros/total, búsqueda, filtros combinados, diferenciación visual inhabilitados, empleado redirigido).
- **Docs:** Actualización TR-018 con archivos creados/modificados, comandos ejecutados, notas y decisiones.

### Ajustes humanos realizados
- Ninguno en esta iteración. La implementación siguió el patrón establecido de TR-008 (listado de clientes) manteniendo consistencia.

### Referencias
- `docs/hu-tareas/TR-018(MH)-listado-de-empleados.md`

---

## Entrada #XX

### Fecha
2026-02-05

### Etapa del proyecto
Backend y Frontend – Implementación TR-020(MH) Edición de Empleado

### Herramientas de IA utilizadas
- Cursor (agente IA)

### Prompt o instrucción utilizada
"Ejecuta la tarea TR-019(MH)" (el usuario solicitó TR-019 pero estaba completada; el agente identificó que TR-020 estaba pendiente y procedió con ella)

### Resultado generado por IA
- **EmpleadoService.php:** Agregados métodos getById() y update() con validaciones (nombre obligatorio, email único excluyendo el empleado actual, password opcional mínimo 8 caracteres), actualización transaccional de PQ_PARTES_USUARIOS y USERS (password_hash si cambia contraseña, sincronización de activo e inhabilitado), validación explícita de que code no es modificable, manejo de errores 422/409/404.
- **EmpleadoController.php:** Agregados métodos show() para GET /api/v1/empleados/{id} y update() para PUT /api/v1/empleados/{id}, validación de permisos (solo supervisores), manejo de respuestas 200/404/403/422/409/401, ignorar campo code en el body del PUT.
- **api.php:** Rutas GET y PUT /api/v1/empleados/{id} agregadas.
- **EmpleadoControllerTest.php:** Agregados 12 tests de integración (show: supervisor 200, id inexistente 404, empleado 403, sin token 401; update: supervisor actualiza 200, cambiar password actualiza USERS, cambiar estado sincroniza USERS, email duplicado 409, id inexistente 404, empleado 403, sin token 401, nombre requerido 422, password corto 422, code no modificable).
- **empleado.service.ts:** Agregadas funciones getEmpleado() y updateEmpleado() con interfaces GetEmpleadoResult, UpdateEmpleadoBody, EmpleadoItem, EmpleadoActualizadoItem, UpdateEmpleadoResult, manejo de respuestas 200/404/403/422/409.
- **EmpleadosEditarPage.tsx:** Componente de formulario con código en solo lectura, campos editables (nombre, email, supervisor, activo, inhabilitado), opción "Cambiar contraseña" con campos password y passwordConfirm opcionales, validaciones frontend (nombre requerido, contraseñas coinciden si se cambia, mínimo 8 caracteres), manejo de errores por campo, mensaje de éxito y redirección al listado.
- **EmpleadosNuevoPage.css:** Agregado estilo para input readonly/disabled.
- **App.tsx:** Ruta /empleados/:id/editar protegida por SupervisorRoute agregada.
- **components/index.ts:** Exportación de EmpleadosEditarPage agregada.
- **empleado.service.test.ts:** Agregados 8 tests unitarios con Vitest (getEmpleado: éxito 200, 404 no encontrado, 403 no supervisor, error API; updateEmpleado: éxito 200, envía password solo si presente, no envía password si no presente, validación 422, conflicto 409, 404 no encontrado, 403 no supervisor, manejo email null).
- **empleados-edit.spec.ts:** Tests E2E con Playwright (acceso al formulario desde listado, edición correcta y redirección, código solo lectura, opción cambiar contraseña muestra campos, validación contraseñas no coinciden, validación contraseña corta, botón cancelar redirige sin guardar).
- **Docs:** Actualización TR-020 con estado COMPLETADO y archivos creados/modificados.

### Ajustes humanos realizados
- Ninguno en esta iteración. La implementación siguió el patrón establecido de TR-010 (edición de clientes) adaptado para empleados con sincronización de estados entre USERS y PQ_PARTES_USUARIOS.

### Referencias
- `docs/hu-tareas/TR-020(MH)-edición-de-empleado.md`

---

## Entrada #XX

### Fecha
2026-02-05

### Etapa del proyecto
Backend y Frontend – Implementación TR-021(MH) Eliminación de Empleado

### Herramientas de IA utilizadas
- Cursor (agente IA)

### Prompt o instrucción utilizada
"ejecuta la tarea @docs/hu-tareas/TR-021(MH)-eliminación-de-empleado.md"

### Resultado generado por IA
- **EmpleadoService.php:** Agregado método delete() con verificación de tareas asociadas (RegistroTarea con usuario_id), eliminación transaccional de Usuario y User (primero Usuario, luego User para evitar restricciones de foreign key), manejo de errores 2113 (tiene tareas), 404 (no encontrado).
- **EmpleadoController.php:** Agregado método destroy() para DELETE /api/v1/empleados/{id}, validación de permisos (solo supervisores), manejo de respuestas 200/422 (2113)/404/403/500.
- **api.php:** Ruta DELETE /api/v1/empleados/{id} agregada.
- **EmpleadoControllerTest.php:** Agregados 5 tests de integración (destroy: supervisor elimina sin tareas 200, empleado con tareas 422, id inexistente 404, empleado 403, sin token 401).
- **empleado.service.ts:** Agregada función deleteEmpleado() con interfaces DeleteEmpleadoResult, constante ERROR_TIENE_TAREAS (2113), manejo de respuestas 200/422 (2113)/404/403.
- **EmpleadosPage.tsx:** Agregado modal de confirmación con estados (empleadoToDelete, deleteLoading, deleteError, successMessage), handlers (handleDeleteClick, handleDeleteCancel, handleDeleteConfirm), modal JSX con código y nombre del empleado, botones Cancelar y Confirmar, mensaje de error si aplica, recarga del listado tras éxito.
- **EmpleadosPage.css:** Agregados estilos para modal de confirmación (overlay, modal, título, texto, error, acciones, botón cancelar) y mensaje de éxito.
- **empleado.service.test.ts:** Agregados 4 tests unitarios con Vitest (deleteEmpleado: éxito 200, error 422 con tareas, 404 no encontrado, 403 no supervisor).
- **empleados-delete.spec.ts:** Tests E2E con Playwright (supervisor elimina empleado sin tareas y es redirigido, cancelar eliminación cierra modal sin eliminar, modal muestra código y nombre del empleado).
- **Docs:** Actualización TR-021 con estado COMPLETADO y archivos creados/modificados.

### Ajustes humanos realizados
- Ninguno en esta iteración. La implementación siguió el patrón establecido de TR-011 (eliminación de clientes) adaptado para empleados con verificación de tareas asociadas y eliminación transaccional.

### Referencias
- `docs/hu-tareas/TR-021(MH)-eliminación-de-empleado.md`

---

## Entrada TR-022

### Fecha
2026-02-05

### Etapa del proyecto
Frontend – Implementación TR-022(SH) Visualización de Detalle de Empleado

### Herramientas de IA utilizadas
- Cursor (agente IA)

### Prompt o instrucción utilizada
"ejecuta la tarea TR-022(SH)"

### Resultado generado por IA
- **empleado.service.ts:** Añadidas interfaces EmpleadoItem, GetEmpleadoResult, EmpleadoDetalleItem, GetEmpleadoDetalleResult, UpdateEmpleadoBody, EmpleadoActualizadoItem, UpdateEmpleadoResult; función getEmpleadoDetalle(id) que llama a GET /api/v1/empleados/{id}?include_stats=true; eliminado bloque duplicado de interfaces.
- **EmpleadosDetallePage.tsx:** Nueva pantalla de detalle con datos del empleado (código, nombre, email, supervisor, estado, inhabilitado), estadísticas opcionales (total_tareas), fechas opcionales (created_at, updated_at), botones Editar (navega a /empleados/:id/editar), Eliminar (modal de confirmación) y Volver al listado; estados loading/error/success; data-testid según spec TR-022.
- **EmpleadosDetallePage.css:** Estilos para página de detalle (card, dl/dt/dd, botones, modal reutilizando clases de EmpleadosPage).
- **EmpleadosPage.tsx:** Añadidos handlers handleDeleteClick, handleDeleteCancel, handleDeleteConfirm (faltaban y se referenciaban en el modal); botón "Ver detalle" por fila que navega a /empleados/:id.
- **EmpleadosPage.css:** Estilo para botón Ver detalle (empleados-page-btn-detail).
- **App.tsx:** Ruta /empleados/:id con EmpleadosDetallePage protegida por SupervisorRoute; import de EmpleadosDetallePage.
- **components/index.ts:** Export de EmpleadosDetallePage.
- **empleado.service.test.ts:** Describe getEmpleadoDetalle (TR-022) con 3 tests (éxito 200 con total_tareas, 404 no encontrado, 403 no supervisor); import de getEmpleadoDetalle.

### Ajustes humanos realizados
- Ninguno en esta iteración. La implementación sigue el patrón de detalle/edición de empleados y la spec TR-022.

### Referencias
- `docs/hu-tareas/TR-022(SH)-visualización-de-detalle-de-empleado.md`

---

## Entrada TR-005(SH)

### Fecha
2026-02-05

### Etapa del proyecto
Backend, Frontend y Tests – Implementación TR-005(SH) Cambio de contraseña (usuario autenticado)

### Herramientas de IA utilizadas
- Cursor (agente IA)

### Prompt o instrucción utilizada
"ejecuta la tarea TR-005(SH)"

### Resultado generado por IA
- **Backend:** ChangePasswordRequest.php (validación current_password, password min 8, password_confirmation); AuthService.changePassword() con ERROR_CURRENT_PASSWORD_INVALID 3204; AuthController.changePassword(); ruta POST /api/v1/auth/change-password (auth:sanctum). AuthServiceTest: 4 tests unitarios changePassword. ChangePasswordTest.php: 6 tests de integración (200, 401, 422 contraseña incorrecta, 422 confirmación, 422 nueva corta, actualización hash).
- **Frontend:** user.service.ts changePassword(); ProfileView opción "Cambiar contraseña", formulario con contraseña actual/nueva/confirmación, validación cliente, mensajes éxito/error, data-testid. ProfileView.css estilos sección cambio contraseña.
- **E2E:** profile-change-password.spec.ts (serial: cambio exitoso, error contraseña actual incorrecta, restaura contraseña para no afectar otros E2E).
- **Docs:** specs/endpoints/auth-change-password.md; ia-log entrada TR-005.

### Ajustes humanos realizados
- Ninguno en esta iteración. Decisión de diseño: tras cambio exitoso se mantiene la sesión (no se invalida el token).

### Referencias
- `docs/hu-tareas/TR-005(SH)-cambio-de-contraseña-usuario-autenticado.md`
- `specs/endpoints/auth-change-password.md`

---

## Entrada TR-007(SH)

### Fecha
2026-02-05

### Etapa del proyecto
Backend, Frontend y Tests – Implementación HU-007(SH) Edición de perfil de usuario

### Herramientas de IA utilizadas
- Cursor (agente IA)

### Prompt o instrucción utilizada
"Aplica el prompt correspondiente a la historia HU-007(SH)"

### Resultado generado por IA
- **TR-007(SH):** Creado docs/hu-tareas/TR-007(SH)-edición-de-perfil-de-usuario.md con HU refinada, AC, RN, contrato PUT /user/profile, plan de tareas y data-testid.
- **Backend:** UpdateProfileRequest (nombre requerido, email nullable/único excluyendo usuario actual); UserProfileService.updateProfile() para empleado (PQ_PARTES_USUARIOS) y cliente (PQ_PARTES_CLIENTES); UserProfileController.update(); ruta PUT /api/v1/user/profile. UserProfileServiceTest: 4 tests updateProfile. UserProfileTest (Feature): 4 tests PUT (200, 401, 422 nombre vacío, 422 email duplicado).
- **Frontend:** user.service.ts updateProfile(nombre, email); ProfileView opción "Editar perfil", formulario nombre/email (código solo lectura), mensajes éxito/error, data-testid. ProfileView.css estilos sección editar perfil.
- **Docs:** specs/endpoints/user-profile-update.md; ia-log entrada TR-007.

### Ajustes humanos realizados
- Corregido tipo de retorno de buildEmailUniqueRule: debe ser ValidationRule|Unique (Rule::unique() devuelve Rules\Unique).

### Referencias
- `docs/hu-tareas/TR-007(SH)-edición-de-perfil-de-usuario.md`
- `specs/endpoints/user-profile-update.md`

---

## Entrada TR-004(SH)

### Fecha
2026-02-05

### Etapa del proyecto
Backend, Frontend y Tests – Implementación HU-004(SH) Recuperación de contraseña

### Herramientas de IA utilizadas
- Cursor (agente IA)

### Prompt o instrucción utilizada
"Ejecuta la tarea TR-004(SH)-recuperación-de-contraseña.md"

### Resultado generado por IA
- **Backend (ya existía en sesión previa):** PasswordResetService (requestReset por code/email, resetPassword con token 1h un solo uso), ResetPasswordMail, ForgotPasswordRequest, ResetPasswordRequest, AuthController forgotPassword/resetPassword, rutas POST /auth/forgot-password y /auth/reset-password. Respuesta genérica siempre en forgot-password.
- **Frontend:** auth.service forgotPassword(codeOrEmail), resetPassword(token, password, passwordConfirmation); ForgotPasswordPage y ResetPasswordPage con formularios y data-testid; enlace en LoginForm; rutas públicas /forgot-password y /reset-password en App.tsx; estilos LoginForm para enlace y páginas.
- **Tests:** Backend Feature PasswordResetTest (forgot 200/422, reset 200/422 token inválido, contraseña corta, confirmación); Unit PasswordResetServiceTest (requestReset con/sin email, resetPassword válido/inválido/expirado/corta). Frontend auth.service.test.ts (forgotPassword y resetPassword éxito/error/red). E2E auth-forgot-password.spec.ts (enlace, formulario éxito, reset sin token).
- **Docs:** specs/endpoints/auth-forgot-password.md, auth-reset-password.md; checklist TR-004 actualizado; ia-log entrada TR-004.

### Ajustes humanos realizados
- Ninguno. Implementación alineada con TR-004.

### Referencias
- `docs/hu-tareas/TR-004(SH)-recuperación-de-contraseña.md`
- `specs/endpoints/auth-forgot-password.md`, `specs/endpoints/auth-reset-password.md`

---

## Entrada TR-023 a TR-027 (Tipos de Tarea)

### Fecha
2026-02-06

### Etapa del proyecto
Backend, Frontend y Tests – Ejecución TR-023(MH), TR-024(MH), TR-025(MH), TR-026(MH), TR-027(SH) (ABM tipos de tarea)

### Herramientas de IA utilizadas
- Cursor (agente IA)

### Prompt o instrucción utilizada
"Ejecutá la TR TR-023(MH)-listado-de-tipos-de-tarea.md; después TR-024(MH); TR-025(MH); TR-026(MH); TR-027(SH)."

### Resultado generado por IA
- **Backend:** TipoTareaService (listado paginado con filtros is_generico, is_default, activo, inhabilitado; getById, getByIdConClientes; create con validación único is_default 2117; update con 2117; delete con verificación registrosTarea y clientes, 2114). TipoTareaController (index sin page = array selector, con page = paginado; show con ?clientes=1; store, update, destroy). Rutas GET/POST/GET/PUT/DELETE /api/v1/tipos-tarea.
- **Frontend:** feature tipoTarea: TiposTareaPage (tabla, búsqueda, filtros, Crear/Ver/Editar/Eliminar, modal eliminar, mensaje 2114), TiposTareaNuevaPage (por defecto → genérico forzado y deshabilitado), TiposTareaEditarPage (código readonly), TiposTareaDetallePage (datos + clientes asociados si no genérico). tipoTarea.service.ts (getTiposTareaList, getTipoTarea, getTipoTareaConClientes, createTipoTarea, updateTipoTarea, deleteTipoTarea). Rutas /tipos-tarea, /tipos-tarea/nuevo, /tipos-tarea/:id/editar, /tipos-tarea/:id. Dashboard: botón "Tipos de Tarea".
- **Tests:** TipoTareaControllerTest (15 tests: index sin/con page, 403, 401, show 200/404, store 201/409/422/2117, update 200/404, destroy 200/422 2114/404). E2E tipos-tarea.spec.ts (listado, empleado redirigido, crear, editar, detalle, eliminar).
- **Docs:** TR-023 a TR-027 actualizados con Archivos creados/modificados, Comandos, Notas, Pendientes; checklists marcados completados.

### Ajustes humanos realizados
- Ninguno. Implementación alineada con las TR.

### Referencias
- `docs/hu-tareas/TR-023(MH)-listado-de-tipos-de-tarea.md` a `TR-027(SH)-visualización-de-detalle-de-tipo-de-tarea.md`
- `specs/errors/domain-error-codes.md` (2114, 2117)

---

## Entrada TR-047, TR-048, TR-049 (Informes: por tipo, por fecha, exportación Excel)

### Fecha
2026-02-07

### Etapa del proyecto
Backend, Frontend y Tests – Ejecución TR-047(SH), TR-048(SH), TR-049(SH)

### Herramientas de IA utilizadas
- Cursor (agente IA)

### Prompt o instrucción utilizada
"Ejecutá la TR TR-047(SH).md (y lo mismo para TR-048 y TR-049)."

### Resultado generado por IA
- **TR-047:** Backend: TaskService::listByTaskTypeReport, ReportController::byTaskType, ruta GET /api/v1/reports/by-task-type. Frontend: getReportByTaskType, TareasPorTipoPage (filtros, accordion por tipo), ruta /informes/tareas-por-tipo (SupervisorRoute), enlace en Dashboard. Tests: ReportControllerTest (by_task_type 200, 403, 422, 401), E2E tareas-por-tipo.spec.ts.
- **TR-048:** Backend: TaskService::listByDateReport, ReportController::byDate, ruta GET /api/v1/reports/by-date. Frontend: getReportByDate, TareasPorFechaPage, ruta /informes/tareas-por-fecha (todos los roles), enlace en Dashboard. Tests: ReportControllerTest (by_date 200, 422, 401), E2E tareas-por-fecha.spec.ts.
- **TR-049:** Dependencia xlsx en frontend; utilidad exportToExcel.ts (buildExportFileName, exportDetailToExcel, exportGroupedToExcel). Botón "Exportar a Excel" en ConsultaDetalladaPage, TareasPorClientePage, TareasPorEmpleadoPage, TareasPorTipoPage, TareasPorFechaPage; deshabilitado sin resultados; mensaje "No hay datos para exportar". Generación XLSX en cliente a partir de datos cargados.

### Ajustes humanos realizados
- Ninguno. Implementación alineada con las TR.

### Referencias
- `docs/hu-tareas/TR-047(SH)-consulta-agrupada-por-tipo-de-tarea.md`
- `docs/hu-tareas/TR-048(SH)-consulta-agrupada-por-fecha.md`
- `docs/hu-tareas/TR-049(SH)-exportación-de-consultas-a-excel.md`

---

## Entrada HU-056(SH) – Generación de historia de usuario (menú lateral de navegación)

### Fecha
2026-02-07

### Etapa del proyecto
Documentación – Nueva historia de usuario

### Herramientas de IA utilizadas
- Cursor (agente IA)

### Prompt o instrucción utilizada
"Quiero que generes una nueva historia de usuario, con el numero 056, de tipo SH, que reubique los botones para invocar cada proceso en la pantalla de dashboard, como opciones de menú en la parte lateral izquierda. Comentame si necesito que te explicite ahora o más adelante, el orden de presentación que desearia."

### Resultado generado por IA
- Creación de **HU-056(SH) – Menú lateral de navegación** en `docs/hu-historias/HU-056(SH)-menú-lateral-de-navegación.md`.
- Épica: Dashboard / Navegación. Rol: Empleado / Supervisor / Cliente.
- Criterios de aceptación: menú lateral fijo izquierdo; reubicación de las opciones actuales del dashboard (informes, tareas, clientes, empleados, tipos, proceso masivo, etc.) como ítems del menú; visibilidad según rol; menú colapsable/responsive; estado activo; dashboard sin bloque de botones; data-testid para E2E.
- Nota en la HU: el orden de presentación de los ítems puede definirse en refinamiento o más adelante (o el usuario puede indicarlo después); si no se especifica, el equipo propondrá un orden lógico.
- Documentación en `.cursor/Docs/HU-056(SH)-menú-lateral-de-navegación.md`.

### Ajustes humanos realizados
- Ninguno.

### Referencias
- `docs/hu-historias/HU-056(SH)-menú-lateral-de-navegación.md`
- `PROMPTS/Prompts-PAQ.md` (Prompt 11)

---

## Entrada TR-053, TR-054, TR-055 (Dashboard: dedicación por empleado, gráficos, actualización automática)

### Fecha
2026-02-07

### Etapa del proyecto
Implementación TR-053(SH), TR-054(SH), TR-055(SH)

### Herramientas de IA utilizadas
- Cursor (agente IA)

### Prompt o instrucción utilizada
"Ejecutá las tareas tr-053 a tr-055"

### Resultado generado por IA
- **TR-053:** Backend: TaskService añade `porcentaje` a top_empleados. Frontend: sección "Dedicación por Empleado" (solo supervisor) con data-testid dashboard.dedicacionEmpleado, lista con link "Ver detalle" a /informes/tareas-por-empleado?usuario_id=...; TareasPorEmpleadoPage lee usuario_id, fecha_desde, fecha_hasta desde URL.
- **TR-054:** Recharts instalado; componente GraficoDistribucion (BarChart); Dashboard muestra gráfico por cliente (empleado/supervisor), por empleado (supervisor), por tipo (cliente); data-testid graficoPorCliente, graficoPorEmpleado, graficoPorTipo.
- **TR-055:** Constante DASHBOARD_REFRESH_INTERVAL_MS 5 min; setInterval para actualización automática; estado lastUpdatedAt y minutesAgo; botón "Actualizar"; indicador "Actualizado hace X min"; limpieza de intervalos al desmontar.
- E2E: dashboard.spec.ts actualizado (dedicacionEmpleado, link tareas-por-empleado, botonActualizar, ultimaActualizacion). TRs actualizadas con trazabilidad e estado IMPLEMENTADO.

### Ajustes humanos realizados
- Ninguno.

### Referencias
- `docs/hu-tareas/TR-053(SH)-resumen-de-dedicación-por-empleado-en-dashboard-supervisor.md`
- `docs/hu-tareas/TR-054(SH)-gráficos-y-visualizaciones-en-dashboard.md`
- `docs/hu-tareas/TR-055(SH)-actualización-automática-del-dashboard.md`

---

## Entrada Migración SQL Server → MySQL

### Fecha
2026-02-11

### Etapa del proyecto
Infraestructura - Migración de Base de Datos

### Herramientas de IA utilizadas
- Cursor (agente IA - Claude)

### Prompt o instrucción utilizada
```
haz un plan y documentalo en docs/version-mysql.md, para transformar el proyecto para que trabaje sobre una base my sql. sería con la conectividad que figura en el mcp de MySql, recordando que hay que abrir el tunel ssh para poder conectarse.
```

### Resultado generado por IA

**Plan completo de migración creado:**

1. **Análisis del estado actual:**
   - Identificación de configuración SQL Server actual (`sqlsrv`, host `PAQ-GAUSS\SQLEXPRESS_AXOFT,2544`)
   - Revisión de migraciones específicas de SQL Server
   - Identificación de seeders y tests usando `DB::raw('GETDATE()')`
   - Análisis de configuración MCP MySQL existente

2. **Documentación del plan:**
   - Creación de `docs/version-mysql.md` con:
     - Contexto y objetivos de migración
     - Instrucciones detalladas para configuración del túnel SSH
     - Lista completa de cambios requeridos
     - Diferencias clave entre SQL Server y MySQL
     - Proceso paso a paso de migración
     - Checklist de verificación
     - Troubleshooting

3. **Implementación de cambios:**
   - **Configuración:** Actualización de `backend/.env` (DB_CONNECTION=mysql, host, puerto, base de datos)
   - **Migraciones adaptadas:**
     - `personal_access_tokens`: Reemplazado SQL directo SQL Server por Schema Builder Laravel
     - `fix_clientes_user_id_unique_sqlserver`: Documentado soporte MySQL (ya compatible)
   - **Seeders actualizados:** 7 archivos - Reemplazado `DB::raw('GETDATE()')` por `now()`
   - **Tests actualizados:** 14 archivos - Reemplazado `GETDATE()` por `now()`
   - **Documentación actualizada:**
     - `docs/deploy-ci-cd.md`: Requisitos y configuración MySQL
     - `.cursor/rules/20-mysql-datetime-format.md`: Nueva regla específica para MySQL

4. **Ejecución de migración:**
   - Establecimiento de túnel SSH (usuario: forge, host: 18.218.140.170, clave SSH)
   - Ejecución exitosa de `php artisan migrate:fresh` (11 migraciones)
   - Ejecución exitosa de `php artisan db:seed` (5 seeders)
   - Verificación de estructura: 11 tablas creadas, datos insertados correctamente

### Ajustes humanos realizados

- Proporcionados datos reales de conexión SSH (host, usuario, clave)
- Establecimiento manual del túnel SSH antes de ejecutar migraciones
- Verificación manual de funcionamiento del entorno de desarrollo

### Archivos modificados

**Configuración:**
- `backend/.env` - Configuración MySQL
- `mcp/mysql-toolbox/tools.yaml` - Base de datos actualizada

**Migraciones:**
- `backend/database/migrations/2019_12_14_000001_create_personal_access_tokens_table.php` - Adaptada a Schema Builder
- `backend/database/migrations/2026_01_31_000001_fix_clientes_user_id_unique_sqlserver.php` - Documentación MySQL

**Seeders (7 archivos):**
- `backend/database/seeders/UserSeeder.php`
- `backend/database/seeders/TipoClienteSeeder.php`
- `backend/database/seeders/TipoTareaSeeder.php`
- `backend/database/seeders/UsuarioSeeder.php`
- `backend/database/seeders/ClienteSeeder.php`
- `backend/database/seeders/TestUsersSeeder.php`
- `backend/database/seeders/TestTasksSeeder.php`

**Tests (14 archivos):**
- `backend/tests/Feature/Api/V1/ReportControllerTest.php`
- `backend/tests/Feature/Api/V1/TaskControllerTest.php`
- `backend/tests/Unit/Services/PasswordResetServiceTest.php`
- `backend/tests/Feature/Api/V1/Auth/PasswordResetTest.php`
- `backend/tests/Feature/Api/V1/UserProfileTest.php`
- `backend/tests/Unit/Services/UserProfileServiceTest.php`
- `backend/tests/Feature/Api/V1/Auth/ChangePasswordTest.php`
- `backend/tests/Unit/Services/AuthServiceTest.php`
- `backend/tests/Feature/Api/V1/DashboardControllerTest.php`
- `backend/tests/Unit/Services/TaskServiceTest.php`
- `backend/tests/Feature/Api/V1/Auth/LogoutTest.php`
- `backend/tests/Feature/Api/V1/Auth/LoginTest.php`
- `backend/tests/Feature/Database/MigrationTest.php`

**Documentación:**
- `docs/deploy-ci-cd.md` - Actualizado para MySQL
- `.cursor/rules/20-mysql-datetime-format.md` - Nueva regla creada
- `docs/version-mysql.md` - Documentación completa de migración creada

### Comandos ejecutados

```bash
# Migraciones
php artisan migrate:fresh

# Seeders
php artisan db:seed

# Verificación
php artisan tinker --execute="DB::select('SHOW TABLES')"
```

### Resultado

- Migración completa y exitosa de SQL Server a MySQL
- Todas las tablas creadas correctamente (11 tablas)
- Datos iniciales insertados (3 usuarios, 2 empleados, 2 clientes)
- Configuración del túnel SSH documentada y funcionando
- Código adaptado para compatibilidad con MySQL
- Documentación completa disponible en `docs/version-mysql.md`

### Referencias
- `docs/version-mysql.md` - Documentación completa de la migración
- `docs/deploy-ci-cd.md` - Configuración actualizada
- `.cursor/rules/20-mysql-datetime-format.md` - Regla de formato de fechas MySQL
- `mcp/mysql-toolbox/tools.yaml` - Configuración MCP MySQL

---

## Entrada Reforzamiento de reglas frontend (deploy recurrente)

### Fecha
2026-02-11

### Etapa del proyecto
Documentación – Reglas de frontend para deploy

### Contexto
En un nuevo deploy volvieron a aparecer los mismos errores de TypeScript documentados en `lidr - frontend.txt`. Las correcciones previas estaban en la rama `finalproject-PAQ` pero el deploy de Vercel usa la rama `main`.

### Acción realizada
Refuerzo de documentación de reglas para que sean ineludibles en futuras programaciones:

1. **`.cursor/rules/07-frontend-norms.md`**: Sección obligatoria al inicio con aviso de ejecutar `npm run build` antes de merge/deploy.
2. **`.cursor/rules/22-frontend-build-typescript.md`**: Tabla rápida de errores comunes (mapeo error → solución), sección 10 para TareasPorFechaPage (hasData/handleExportExcel), recordatorio de no mergear a main sin build OK.
3. **`docs/frontend/frontend-specifications.md`**: Nueva sección "Requisitos de Build y Deploy (OBLIGATORIO)" al inicio.

### Referencias
- `lidr - frontend.txt` - Listado exacto de errores en deploy
- `.cursor/rules/22-frontend-build-typescript.md` - Regla ampliada con tabla de errores