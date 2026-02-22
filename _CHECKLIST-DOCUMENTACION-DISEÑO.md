# Checklist: Proceso de Documentación y Diseño de Proyecto MVP

**Objetivo:** Guía paso a paso para definir, documentar y diseñar un proyecto MVP. Un programador nuevo puede usar este checklist para verificar completitud del proceso de documentación o como plantilla para proyectos futuros.

**Nota sobre nombres de archivo:** En este proyecto, algunos archivos en la raíz usan prefijo `_` (ej: `_PROJECT_CONTEXT.md`, `_MANUAL-PROGRAMADOR.MD`, `_CHECKLIST-DOCUMENTACION-DISEÑO.md`).

---

## FASE 1: DEFINICIÓN Y CONTEXTO DEL PROYECTO

### 1.1 Definición del Proyecto
- [ ] Definir objetivo conceptual del sistema
- [ ] Identificar usuarios principales y roles
- [ ] Establecer alcance del MVP (qué SÍ y qué NO incluir)
- [ ] Documentar propósito principal y valor de negocio
- [ ] Crear archivo `_PROJECT_CONTEXT.md` o `docs/producto.md`

### 1.2 Stack Tecnológico
- [ ] Definir framework backend (Laravel, Django, etc.)
- [ ] Definir framework frontend (React, Vue, Angular)
- [ ] Elegir base de datos (SQL Server, PostgreSQL, MySQL)
- [ ] Definir sistema de autenticación (Sanctum, JWT, etc.)
- [ ] Elegir herramientas de testing (PHPUnit, Playwright, etc.)
- [ ] Documentar decisiones en `docs/producto.md` o `README.md`

### 1.3 Consignas y Requisitos (si aplica)
- [ ] Revisar consignas del proyecto
- [ ] Identificar entregables obligatorios
- [ ] Definir criterios de aceptación del MVP
- [ ] Crear archivo `docs/consignas-mvp.md`

---

## FASE 2: FLUJO E2E Y PLANIFICACIÓN

### 2.1 Flujo End-to-End Prioritario
- [ ] Definir flujo E2E con principio y fin claros
- [ ] Identificar pasos del flujo (ej: Login → Registro → Visualización)
- [ ] Validar que el flujo aporte valor completo
- [ ] Documentar en `AGENTS.md` o `docs/consignas-mvp.md`
- [ ] Crear especificación detallada en `specs/flows/e2e-core-flow.md`

### 2.2 Priorización de Historias
- [ ] Identificar 3-5 historias MUST-HAVE para el flujo E2E
- [ ] Identificar 1-2 historias SHOULD-HAVE (opcionales)
- [ ] Validar que las MUST-HAVE cubren el flujo completo
- [ ] Documentar priorización en `docs/historias-y-tickets.md`

---

## FASE 3: DISEÑO DEL MODELO DE DATOS

### 3.1 Identificación de Entidades
- [ ] Listar entidades principales del dominio
- [ ] Identificar relaciones entre entidades
- [ ] Definir cardinalidad de relaciones (1:1, 1:N, N:M)
- [ ] Documentar en borrador o notas

### 3.2 Diseño de Tablas
- [ ] Definir campos de cada entidad
- [ ] Identificar claves primarias (PK)
- [ ] Identificar claves foráneas (FK)
- [ ] Definir campos únicos (UK)
- [ ] Establecer valores por defecto
- [ ] Definir campos obligatorios vs opcionales
- [ ] Establecer convenciones de nombres (prefijos, etc.)

### 3.3 Restricciones y Reglas de Negocio
- [ ] Definir validaciones de dominio (ej: duración múltiplo de 15)
- [ ] Establecer reglas de integridad referencial
- [ ] Definir reglas de soft delete (si aplica)
- [ ] Establecer reglas de estado (activo/inhabilitado)
- [ ] Documentar restricciones en `docs/modelo-datos.md`

### 3.4 Documentación del Modelo
- [ ] Crear `docs/modelo-datos.md` con:
  - [ ] Descripción de cada entidad
  - [ ] Campos y tipos de datos
  - [ ] Relaciones documentadas
  - [ ] Restricciones y validaciones
  - [ ] Decisiones de diseño
- [ ] Crear diagrama ER (Mermaid o DBML)
- [ ] Crear `database/modelo-datos.dbml` para visualización gráfica
- [ ] Agregar diagrama Mermaid a `docs/modelo-datos.md`

### 3.5 Especificaciones de Modelos
- [ ] Crear especificaciones detalladas en `specs/models/`:
  - [ ] Un archivo por modelo (ej: `usuario-model.md`)
  - [ ] Campos, tipos, validaciones
  - [ ] Relaciones Eloquent/ORM
  - [ ] Índices y optimizaciones

---

## FASE 4: ARQUITECTURA DEL SISTEMA

### 4.1 Diseño de Arquitectura
- [ ] Definir arquitectura general (Frontend + Backend + DB)
- [ ] Decidir patrón de API (REST, GraphQL)
- [ ] Definir estructura de capas (Controllers, Services, Repositories)
- [ ] Establecer convenciones de organización de código
- [ ] Documentar en `docs/arquitectura.md`

### 4.2 Autenticación y Autorización
- [ ] Diseñar flujo de autenticación
- [ ] Definir sistema de tokens/sesiones
- [ ] Establecer roles y permisos
- [ ] Documentar middleware y policies
- [ ] Actualizar `docs/modelo-datos.md` con tabla de usuarios

### 4.3 Contratos de API
- [ ] Definir formato estándar de respuesta (envelope)
- [ ] Establecer códigos de error
- [ ] Definir estructura de requests
- [ ] Crear `specs/contracts/response-envelope.md`
- [ ] Crear `docs/api/CONTRATO_BASE.md`
- [ ] Documentar códigos de error en `specs/errors/domain-error-codes.md`

---

## FASE 5: HISTORIAS DE USUARIO

### 5.1 Elaboración de Historias
- [ ] Escribir historias de usuario (HU) con formato:
  - [ ] Como [rol]
  - [ ] Quiero [acción]
  - [ ] Para [beneficio]
- [ ] Agregar criterios de aceptación detallados
- [ ] Clasificar como MUST-HAVE o SHOULD-HAVE
- [ ] Agrupar en épicas (si aplica)
- [ ] Documentar en `docs/historias-y-tickets.md`

### 5.2 Validación de Historias
- [ ] Validar que las MUST-HAVE cubren el flujo E2E
- [ ] Verificar que cada historia tiene criterios de aceptación claros
- [ ] Asegurar que las historias son testeables
- [ ] Revisar coherencia entre historias

### 5.3 Reglas de Negocio
- [ ] Extraer reglas de negocio de las historias
- [ ] Documentar reglas explícitas
- [ ] Crear `specs/rules/business-rules.md`
- [ ] Crear `specs/rules/validation-rules.md`
- [ ] Referenciar reglas en historias correspondientes

---

## FASE 6: TICKETS TÉCNICOS

### 6.1 Derivación de Tickets
- [ ] Derivar tickets técnicos (TK) de las historias
- [ ] Clasificar tickets por módulo (Backend, Frontend, Testing, Infra)
- [ ] Asociar cada ticket a historia(es) relacionada(s)
- [ ] Priorizar tickets según MUST-HAVE/SHOULD-HAVE
- [ ] Documentar en `docs/historias-y-tickets.md`

### 6.2 Detalle de Tickets
- [ ] Describir tareas técnicas específicas
- [ ] Identificar dependencias entre tickets
- [ ] Estimar complejidad (si aplica)
- [ ] Asignar a módulos (Backend/Frontend/Testing/Infra)

---

## FASE 7: ESPECIFICACIONES TÉCNICAS

### 7.1 Especificaciones de Endpoints
- [ ] Crear especificación para cada endpoint en `specs/endpoints/`:
  - [ ] Método HTTP (GET, POST, PUT, DELETE)
  - [ ] Ruta y parámetros
  - [ ] Request body (si aplica)
  - [ ] Response exitosa (200)
  - [ ] Responses de error (400, 401, 404, 500)
  - [ ] Validaciones
  - [ ] Operaciones de base de datos
  - [ ] Ejemplos de requests/responses

### 7.2 Mapeo API-Datos
- [ ] Documentar mapeo entre endpoints y tablas
- [ ] Identificar operaciones CRUD por endpoint
- [ ] Documentar en `architecture/api-to-data-mapping.md`

### 7.3 Especificaciones de UI
- [ ] Definir especificaciones de pantallas en `specs/ui/screen-specifications.md`
- [ ] Documentar componentes necesarios
- [ ] Definir estructura de datos en frontend

---

## FASE 8: DOCUMENTACIÓN DE PRODUCTO

### 8.1 Documento de Producto
- [ ] Completar `docs/producto.md` con:
  - [ ] Objetivo del sistema
  - [ ] Público objetivo
  - [ ] Características principales
  - [ ] Funcionalidades del MVP
  - [ ] Alcance y no-alcance

### 8.2 Documentación de Arquitectura
- [ ] Completar `docs/arquitectura.md` con:
  - [ ] Diagrama de arquitectura
  - [ ] Decisiones técnicas
  - [ ] Stack tecnológico detallado
  - [ ] Estructura de carpetas

---

## FASE 9: CONFIGURACIÓN DE HERRAMIENTAS

### 9.1 Testing
- [ ] Instalar herramientas de testing (Playwright, PHPUnit, etc.)
- [ ] Configurar archivos de configuración
- [ ] Crear estructura de carpetas para tests
- [ ] Documentar en `docs/testing.md`:
  - [ ] Estrategia de testing
  - [ ] Cómo ejecutar tests
  - [ ] Estructura de tests E2E

### 9.2 Documentación de Testing
- [ ] Crear especificaciones de tests E2E en `specs/tests/e2e/`
- [ ] Documentar tests de integración en `specs/tests/integral/`
- [ ] Documentar tests unitarios en `specs/tests/individual/`

### 9.3 CI/CD (si aplica)
- [ ] Configurar pipeline básico
- [ ] Documentar en `docs/deploy-ci-cd.md`
- [ ] Configurar gestión de secretos

---

## FASE 10: DOCUMENTACIÓN DE SOPORTE

### 10.1 Manual del Programador
- [ ] Crear `_MANUAL-PROGRAMADOR.MD` con:
  - [ ] Objetivo del proyecto
  - [ ] Stack tecnológico
  - [ ] Ruta de lectura recomendada
  - [ ] Estructura del proyecto
  - [ ] Convenciones y reglas
  - [ ] Cómo empezar a trabajar

### 10.2 README Principal
- [ ] Actualizar `README.md` con:
  - [ ] Descripción del proyecto
  - [ ] Flujo E2E prioritario
  - [ ] Checklist de validación del MVP
  - [ ] Documentación técnica (referencias)
  - [ ] Estructura del repositorio
  - [ ] Instrucciones de instalación

### 10.3 READMEs por Carpeta
- [ ] Crear `docs/README.md` (índice de documentación)
- [ ] Crear `specs/README.md` (índice de especificaciones)
- [ ] Crear `backend/README.md` (si aplica)
- [ ] Crear `specs/endpoints/README.md` (si aplica)

---

## FASE 11: REGLAS Y CONVENCIONES

### 11.1 Reglas para IA (Cursor)
- [ ] Crear reglas en `.cursor/rules/`:
  - [ ] Contexto del proyecto
  - [ ] Reglas de backend
  - [ ] Reglas de frontend
  - [ ] Contrato de API
  - [ ] Testing
  - [ ] i18n y test-ids
  - [ ] Otras reglas específicas

### 11.2 Documentación de Reglas
- [ ] Documentar convenciones de código
- [ ] Establecer normas de nomenclatura
- [ ] Definir estructura de carpetas
- [ ] Documentar en reglas de Cursor o en `docs/`

---

## FASE 12: REGISTRO DE USO DE IA (si aplica)

### 12.1 Plantilla de IA Log
- [ ] Crear plantilla en `docs/plantillas/ia-log.md`
- [ ] Definir formato de registro:
  - [ ] Fecha
  - [ ] Etapa del proyecto
  - [ ] Herramientas de IA utilizadas
  - [ ] Prompt o instrucción
  - [ ] Resultado generado
  - [ ] Ajustes humanos
  - [ ] Motivo del ajuste

### 12.2 Registro Inicial
- [ ] Crear `docs/ia-log.md`
- [ ] Registrar actividad inicial de documentación
- [ ] Documentar decisiones importantes

---

## FASE 13: VALIDACIÓN Y REVISIÓN

### 13.1 Revisión de Completitud
- [ ] Verificar que todas las fases están completas
- [ ] Validar coherencia entre documentos
- [ ] Revisar que no hay duplicaciones
- [ ] Asegurar que todas las referencias están actualizadas

### 13.2 Validación del Flujo E2E
- [ ] Verificar que el flujo E2E está completamente documentado
- [ ] Validar que las historias MUST-HAVE cubren el flujo
- [ ] Asegurar que hay especificaciones técnicas para cada paso

### 13.3 Organización de Archivos
- [ ] Eliminar archivos obsoletos o duplicados
- [ ] Organizar estructura de carpetas
- [ ] Verificar que los READMEs están actualizados
- [ ] Asegurar que los índices de documentación son útiles

---

## FASE 14: COMMIT Y VERSIONADO

### 14.1 Control de Versiones
- [ ] Crear rama de documentación (ej: `feature-entrega1-docs`)
- [ ] Hacer commit de documentación
- [ ] Crear Pull Request con descripción completa
- [ ] Documentar cambios en el PR

---

## FASE 15: CODIFICACIÓN Y DOCUMENTACIÓN DE CÓDIGO

### 15.1 Reglas de Documentación Durante la Codificación
- [ ] Establecer que **TODAS las clases** deben documentarse (públicas, privadas, internas)
- [ ] Establecer que **TODOS los métodos** deben documentarse (públicos, privados, protegidos, estáticos)
- [ ] Establecer que **TODAS las propiedades** deben documentarse (públicas, privadas, protegidas, constantes)
- [ ] Definir formato de documentación según lenguaje:
  - [ ] Backend PHP/Laravel: PHPDoc con `@param`, `@return`, `@throws`
  - [ ] Frontend TypeScript/React: JSDoc con tipos y descripciones
  - [ ] Backend C#: Comentarios XML con `<summary>`, `<param>`, `<returns>`
- [ ] Documentar reglas en `specs/governance/code-documentation-rules.md`
- [ ] Incluir ejemplos de documentación correcta en las reglas
- [ ] Establecer que código sin documentación se considera incompleto

### 15.2 Validación de Documentación
- [ ] Configurar herramientas de validación (si aplica):
  - [ ] Linters que verifiquen presencia de documentación
  - [ ] Herramientas de análisis estático
- [ ] Incluir revisión de documentación en proceso de code review
- [ ] Establecer criterio: "Sin documentación = cambio incompleto"

---

## NOTAS IMPORTANTES

### Orden de Prioridad
1. **Fases 1-3 son FUNDAMENTALES**: Sin estas, no se puede avanzar
2. **Fases 4-6 son CRÍTICAS**: Definen la implementación
3. **Fases 7-9 son IMPORTANTES**: Detallan la implementación
4. **Fases 10-12 son DE SOPORTE**: Facilitan el trabajo futuro
5. **Fases 13-14 son DE VALIDACIÓN**: Aseguran calidad

### Iteración
- Este proceso puede ser iterativo
- Se puede refinar el modelo de datos después de definir historias
- Las especificaciones técnicas pueden ajustarse durante el desarrollo

### Uso de IA
- Documentar TODOS los usos significativos de IA en `docs/ia-log.md`
- Incluir prompts, herramientas, resultados y ajustes humanos
- Ser crítico con los resultados de IA

---

## Referencias

Este checklist está basado en el proceso seguido en este proyecto. Para más detalles, consultar:
- `AGENTS.md` - Guía del proyecto para el Agente IA
- `_MANUAL-PROGRAMADOR.MD` - Manual de onboarding del programador
- `docs/consignas-mvp.md` - Consignas del MVP
- `docs/consignas-mvp-cursor.md` - Consignas para Cursor
- `docs/historias-y-tickets.md` - Historias y tickets del proyecto

--------------------------------------------------------------------------------------

# CheckList : Paso a Paso para Proyectos nuevos (según Pablo Quarracino)

## Definiciones

- [ ] MegaPrompt a ChatGpt para definición conceptual.
      Hacer MegaPrompt para ChatGPT con la definición conceptual del nuevo proyecto : objetivo, alcances, inputs/outputs básicos, reglas de negocio básicas, para construir prompts para cursor.
- [ ] Armar documentación.
      cursor/rules, .cursor/docs, agents.md, backend, api, frontend, optimización código, tests, documentaciòn
      en 09-data-access-orm-sql.md aclarar el prefijo de las tablas de la base de datos
      Verificar se incluyan normativas para backend, frontend, apis y testing unitario/integral/e2e.
- [ ] Diseñar procesos.
      Opciones de menú, validaciones y reglas de negocios básicas, modelo de base de datos.
      diseño de base de datos.
- [ ] Definir MCPs necesarios
- [ ] Armar HU (MH y SH)
      Que configure la IA todo lo que encuentre para definir.
      Revisar uno a uno.
      Confirmar Reglas de Negocio 
      Confirmar validaciones
- [ ] Verificar con Proyecto Base que no falten reglas y contextos
- [ ] Armar tareas y specs.

## Programación y Testing

- [ ] Generar Tarea 00 : Armado de Base de datos
- [ ] Generar datos de Prueba (manualmente o con IA)
- [ ] Generar código (con documentación)
- [ ] revisar Código (en detalle)
- [ ] Testing IA (unit / integrador / e2e)
- [ ] Correcciònes al còdigo (desde spec -> codifica IA).
- [ ] Testing manualmente
- [ ] Reprocesar puntos 2 al 4

  ### Comandos de setup de servidores

  **Backend:**
  ```bash
  cd backend
  composer install
  cp .env.example .env
  php artisan key:generate
  php artisan migrate
  php artisan serve
  ```

  **Frontend:**
  ```bash
  cd frontend
  npm install
  npm run dev
  ```

  ### Comandos de test
  
  **Backend:**
  ```bash
  cd backend
  php artisan test                    # Todos los tests
  php artisan test --filter=Logout     # Test específico
  php artisan test --filter Unit      # Solo unitarios
  php artisan test --filter Feature   # Solo integración
  ```

  **Frontend (unitarios + E2E):**
  ```bash
  cd frontend
  npm run test:all             # Recomendado: Vitest + Playwright (al cerrar tarea)
  npm run test:run             # Solo Vitest (unitarios)
  npm run test:e2e             # Solo Playwright E2E
  npm run test:e2e:ui          # E2E con UI interactiva
  npm run test:e2e:headed      # E2E con navegador visible
  ```

  **Playwright – comandos útiles:**
  - `npx playwright show-report` – Visualizar reporte
  - `npm run test:e2e:ui` o `npx playwright test --ui` – UI interactiva
  - `npx playwright test --reporter=html` – Tests con reporte HTML
  - `npx playwright show-trace path/to/trace.zip` – Ver trace de test fallido
  - `npx playwright test auth-login.spec.ts` – Ejecutar test específico
  - `npm run test:e2e:debug` o `npx playwright test --debug` – Modo debug

## Implementación

- [ ] README.md y _MANUAL-PROGRAMADOR.MD
- [ ] Manual del usuario
- [ ] PR con Testing (Github Action o CodeRabbit)
- [ ] Deploy
- [ ] Seguimiento

---

**Última actualización:** 2025-02-15


