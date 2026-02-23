# PaqSuite-IA-ERP

Sistema de registro de tareas para consultorías y empresas de servicios. Evolución del proyecto Lidr-AI4Devs2025-ProyectoFinal.

# Enlaces

- **Repositorio:** https://github.com/paqsystems/PaqSuite-IA-ERP
- **URL pública (si aplica):** configurar en Vercel/Netlify u otro proveedor

# Reglas para Cursor — BackEnd Laravel (Sanctum) — Proyecto de módulos ERP

Este paquete contiene **solo reglas y especificaciones**. **NO** debe implementarse código todavía.

## Invocación de reglas en Cursor

Cursor carga automáticamente los archivos `.md` ubicados en `.cursor/rules/` y los aplica como contexto al agente IA. No es necesario referenciarlos manualmente en cada conversación.

Para invocar explícitamente una regla en un prompt, usar la sintaxis `@` con la ruta relativa, por ejemplo:
- `@.cursor/rules/01-project-context.md`
- `@.cursor/rules/06-api-contract.md`

## Reglas disponibles en `.cursor/rules/`

| Archivo | Propósito |
|---------|-----------|
| `01-project-context.md` | Contexto general del proyecto |
| `02-mvp-entregables.md` | Entregables y alcance del MVP |
| `03-general-quality.md` | Calidad general del código |
| `05-backend-policy.md` | Políticas del backend Laravel |
| `06-api-contract.md` | Contrato de la API REST |
| `06-openapi-documentacion.md` | Documentación OpenAPI |
| `07-frontend-norms.md` | Normas del frontend React |
| `08-security-sessions-tokens.md` | Seguridad, sesiones y tokens |
| `09-data-access-orm-sql.md` | Acceso a datos, ORM y SQL |
| `10-i18n-and-testid.md` | Internacionalización y test IDs |
| `11-playwright-testing-rules.md` | Reglas de testing con Playwright |
| `12-testing.md` | Estrategia de testing |
| `13-user-story-to-task-breakdown.md` | Desglose de historias a tareas |
| `14-dbml-sync-rule.md` | Sincronización con DBML |
| `15-task-execution-traceability.md` | Trazabilidad de ejecución |
| `16-hu-simple-vs-hu-compleja.md` | Historias simples vs complejas |
| `16-prompt-dispatcher.md` | Dispatcher de prompts |
| `20-mysql-datetime-format.md` | Formato datetime en MySQL |
| `20-mssql-server-datetime-format.md` | Formato datetime en MSSQL |
| `21-Iniciar-tunel-SSH-para-MySql.md` | Túnel SSH para MySQL |
| `22-frontend-build-typescript.md` | Build y TypeScript en frontend |

> Si tu instalación de Cursor usa un único archivo `.cursorrules`, concatená el contenido de los archivos anteriores en ese archivo (manteniendo el orden).

## Documentación fundamental

Antes de comenzar cualquier desarrollo, leer:
- `docs/00-contexto/contexto-global-erp.md`

## Documentación del repositorio (referencia humana)

- `/docs/api/CONTRATO_BASE.md`
- `/docs/backend/PLAYBOOK_BACKEND_LARAVEL.md`
- `/docs/api/TICKETING_API_SPEC.md`

## Orden recomendado de lectura para Cursor

1. `01-project-context.md`
2. `02-mvp-entregables.md`
3. `03-general-quality.md`
4. `05-backend-policy.md`
5. `06-api-contract.md`
6. `06-openapi-documentacion.md`
7. `07-frontend-norms.md`
8. `08-security-sessions-tokens.md`
9. `09-data-access-orm-sql.md`
10. `10-i18n-and-testid.md`
11. `11-playwright-testing-rules.md`
12. `12-testing.md`
13. `13-user-story-to-task-breakdown.md`
14. `14-dbml-sync-rule.md`
15. `15-task-execution-traceability.md`
16. `16-hu-simple-vs-hu-compleja.md`
17. `16-prompt-dispatcher.md`
18. `20-mysql-datetime-format.md`
19. `20-mssql-server-datetime-format.md`
20. `21-Iniciar-tunel-SSH-para-MySql.md`
21. `22-frontend-build-typescript.md`

Luego usar `/docs/*` como material ampliado.

---

## Alcance Funcional del MVP

El alcance funcional del MVP está definido formalmente en el documento:

📄 `docs/historias-y-tickets.md`

Dicho documento contiene:
- Historias de usuario clasificadas como MUST-HAVE y SHOULD-HAVE
- Criterios de aceptación detallados
- Reglas de negocio explícitas
- Tickets técnicos derivados (backend, frontend, testing e infraestructura)

El desarrollo del MVP se enfoca exclusivamente en las historias clasificadas como MUST-HAVE.

---

## Flujo End-to-End Prioritario

Los flujos E2E prioritarios se definirán por cada módulo que se desarrolle.

Y validado mediante tests E2E automatizados.

---

## Gestión del alcance (MUST vs SHOULD)

Las historias SHOULD-HAVE:
- No son necesarias para considerar completo el MVP
- Representan mejoras, optimizaciones o funcionalidades avanzadas
- Quedan documentadas como roadmap futuro

El MVP se considera funcionalmente completo cuando todas las historias MUST-HAVE están implementadas y validadas.

Vale aclarar que igualmente se realizaron el 100% de las historias de usaurios, MUST-HAVE y SHOULD-HAVE

## Checklist de Validación del MVP

Se irá actualizando este capítulo ante el desarrollo de cada módulo, titulando por cada uno de estos.

---

## Documentación Técnica

### Producto
📄 Ver `docs/projects/` los archivos tipo .md iniciados como "PROD-" para la descripción completa del producto, público objetivo y características principales.

### Arquitectura
📄 Ver `docs/arquitectura.md` para la arquitectura del sistema (Frontend, Backend, Base de Datos) y decisiones clave.

### Modelo de Datos
📄 Ver `docs/projects/` los archivos tipo .md iniciados como "MD-" para el modelo completo con entidades, relaciones y restricciones.
es muy factible que en estos documentos se hagan referencias al modelado general, que se encuentra en la carpeta `docs/modelo-datos/`  (son varios archivos).

**Arquitectura de Autenticación:**

<A DEfINIR>


### API

La API REST está documentada mediante especificaciones detalladas en `specs/endpoints/`. 

**Base URL:** `/api/v1`

**Autenticación:** Bearer Token (Sanctum)

**Formato de Respuesta:** Todas las respuestas siguen el formato estándar definido en `specs/contracts/response-envelope.md`:
```json
{
  "error": 0,
  "respuesta": "mensaje legible",
  "resultado": {}
}
```

#### Endpoints Principales

**Autenticación:**
- `POST /api/v1/auth/login` - Autenticación unificada contra tabla `USERS`, determina tipo de usuario (cliente/usuario) y rol (supervisor)
- `POST /api/v1/auth/logout` - Cerrar sesión

> **Nota:** El endpoint de login valida contra la tabla `USERS` (sin prefijo PQ_PARTES_) y luego determina si el usuario es un Cliente o un Usuario interno. Los valores de autenticación (`tipo_usuario`, `user_code`, `usuario_id`/`cliente_id`, `es_supervisor`) se conservan durante todo el ciclo de la sesión.

**Sistema de Partes**

*** Gestión de Clientes (Solo Supervisores):
- `GET /api/v1/clientes` - Listar clientes
- `POST /api/v1/clientes` - Crear cliente
- `GET /api/v1/clientes/{id}` - Obtener cliente
- `PUT /api/v1/clientes/{id}` - Actualizar cliente
- `DELETE /api/v1/clientes/{id}` - Eliminar cliente
- `GET /api/v1/clientes/{id}/tipos-tarea` - Listar tipos de tarea asignados
- `POST /api/v1/clientes/{id}/tipos-tarea` - Asignar tipo de tarea
- `DELETE /api/v1/clientes/{id}/tipos-tarea/{tipo_tarea_id}` - Desasignar tipo de tarea

*** Gestión de Tipos de Cliente (Solo Supervisores):
- `GET /api/v1/tipos-cliente` - Listar tipos de cliente
- `POST /api/v1/tipos-cliente` - Crear tipo de cliente
- `GET /api/v1/tipos-cliente/{id}` - Obtener tipo de cliente
- `PUT /api/v1/tipos-cliente/{id}` - Actualizar tipo de cliente
- `DELETE /api/v1/tipos-cliente/{id}` - Eliminar tipo de cliente

*** Gestión de Empleados (Solo Supervisores):
- `GET /api/v1/empleados` - Listar empleados
- `POST /api/v1/empleados` - Crear empleado
- `GET /api/v1/empleados/{id}` - Obtener empleado
- `PUT /api/v1/empleados/{id}` - Actualizar empleado
- `DELETE /api/v1/empleados/{id}` - Eliminar empleado

*** Gestión de Tipos de Tarea (Solo Supervisores):
- `GET /api/v1/tipos-tarea` - Listar tipos de tarea
- `POST /api/v1/tipos-tarea` - Crear tipo de tarea
- `GET /api/v1/tipos-tarea/{id}` - Obtener tipo de tarea
- `PUT /api/v1/tipos-tarea/{id}` - Actualizar tipo de tarea
- `DELETE /api/v1/tipos-tarea/{id}` - Eliminar tipo de tarea

*** Registro de Tareas:
- `POST /api/v1/tareas` - Crear registro de tarea
- `GET /api/v1/tareas` - Listar tareas (filtrado automático por rol: clientes ven solo sus tareas, empleados NO supervisores ven solo las propias)
- `GET /api/v1/tareas/{id}` - Obtener tarea
- `PUT /api/v1/tareas/{id}` - Actualizar tarea
- `DELETE /api/v1/tareas/{id}` - Eliminar tarea
- `GET /api/v1/tareas/tipos-disponibles?cliente_id={id}` - Obtener tipos de tarea disponibles para un cliente

*** Proceso Masivo (Solo Supervisores):
- `GET /api/v1/tareas/proceso-masivo` - Listar tareas para proceso masivo
- `POST /api/v1/tareas/proceso-masivo` - Procesar tareas masivamente (cerrar/reabrir)

*** Informes y Consultas:
- `GET /api/v1/informes/detalle` - Consulta detallada de tareas (filtrado automático por rol)
- `GET /api/v1/informes/por-empleado` - Consulta agrupada por empleado (filtrado automático por rol)
- `GET /api/v1/informes/por-cliente` - Consulta agrupada por cliente (filtrado automático por rol)
- `GET /api/v1/informes/por-tipo` - Consulta agrupada por tipo de tarea (filtrado automático por rol)
- `GET /api/v1/informes/por-fecha` - Consulta agrupada por fecha (filtrado automático por rol)
- `GET /api/v1/informes/exportar` - Exportar informe a Excel (respeta permisos del usuario)

> ** Nota: Todos los endpoints de informes aplican filtros automáticos según el rol del usuario autenticado:
> - **Clientes:** Solo ven tareas donde `cliente_id` coincide con su `cliente_id`
> - **Empleados NO supervisores:** Solo ven tareas donde `usuario_id` coincide con su `usuario_id`
> - **Supervisores:** Ven todas las tareas de todos los usuarios

** Dashboard:
- `GET /api/v1/dashboard/resumen` - Resumen ejecutivo del dashboard (filtrado automático por rol)
- `GET /api/v1/dashboard/por-cliente` - Resumen por cliente (filtrado automático por rol)
- `GET /api/v1/dashboard/por-empleado` - Resumen por empleado (filtrado automático por rol)

> **Nota: Todos los endpoints de dashboard aplican filtros automáticos según el rol del usuario autenticado (mismas reglas que Informes y Consultas).

**Documentación completa:** Ver `specs/endpoints/` para especificaciones detalladas de cada endpoint.

**Códigos de error:** Ver `specs/errors/domain-error-codes.md` para el catálogo completo de códigos de error.

**Reglas de validación:** Ver `specs/rules/validation-rules.md` para validaciones de formato y tipo.

**Reglas de negocio:** Ver `specs/rules/business-rules.md` para reglas específicas del dominio.

### Historias de Usuario

📄 Ver `docs/_projects/{modulo}/HU-historias.md` para el catálogo completo de historias de usuario.

**Resumen:**
- **Total de historias:** 55 (HU-001 a HU-055)
- **MUST-HAVE:** 25 historias (imprescindibles para el MVP)
- **SHOULD-HAVE:** 30 historias (mejoras y funcionalidades avanzadas)

**Historias que cubren el flujo E2E prioritario:**
- HU-001: Autenticación de empleado
- HU-028: Registro de tarea diaria
- HU-033: Visualización de tareas propias
- HU-044: Consulta detallada de tareas
- HU-046: Consulta agrupada por cliente
- HU-051: Dashboard principal

### Tickets de Trabajo

📄 Ver `docs/_projects/{modulo}/HU-tareas.md` para el catálogo completo de tickets técnicos.

**Resumen:**
- **Total de tickets:** 33 (TK-001 a TK-033)
- **Categorías:**
  - Migraciones y Modelos (TK-001)
  - Endpoints de Autenticación (TK-002)
  - Endpoints de Gestión (TK-003 a TK-014)
  - Servicios API Frontend (TK-015)
  - Componentes UI (TK-016 a TK-019)
  - Tests (TK-020 a TK-025)
  - Proceso Masivo (TK-026, TK-027)
  - Informes (TK-028, TK-029, TK-030)
  - Dashboard (TK-031, TK-032)
  - Optimizaciones (TK-033)

Cada ticket técnico referencia las historias de usuario relacionadas y está clasificado según su prioridad (MUST-HAVE o SHOULD-HAVE).

**Trazabilidad de ejecución:** Durante el desarrollo del MVP, la evidencia de ejecución y cambios se documenta al pie de cada TR para facilitar la trazabilidad y la evaluación.

### Testing

**Estrategia de Testing:**
- Tests unitarios: Lógica de negocio y componentes (ubicados en `backend/tests/Unit/`)
- Tests de integración: API + Base de datos (ubicados en `backend/tests/Feature/`)
- Tests E2E: Flujo principal completo con **Playwright** (ubicados en `frontend/tests/e2e/`)

**Playwright (E2E):**
- ✅ Instalado y configurado en `frontend/`
- Configuración: `frontend/playwright.config.ts`
- Tests ubicados en: `frontend/tests/e2e/`
- Documentación: `docs/frontend/testing.md`

**Instalación de Playwright:**
```bash
cd frontend
npm install
npx playwright install
```

**Ejecutar tests E2E:**
```bash
cd frontend
npm run test:e2e          # Todos los tests
npm run test:e2e:ui      # Con UI interactiva
npm run test:e2e:headed # Ver navegador
```

### Pull Requests

---

## Estructura del Repositorio

```
├── backend/              # Backend Laravel
│   ├── app/Models/       # Modelos Eloquent
│   ├── database/         # Migraciones
│   └── tests/            # Tests PHPUnit
│       ├── Unit/         # Tests unitarios
│       └── Feature/      # Tests de integración
├── frontend/             # Frontend React
│   └── src/
│       ├── shared/       # Componentes UI base e i18n
│       └── features/     # Features del dominio
├── docs/                 # Documentación del proyecto
│   ├── producto.md       # Descripción del producto
│   ├── arquitectura.md   # Arquitectura del sistema
│   ├── modelo-datos.md   # Modelo de datos
│   ├── historias-y-tickets.md  # Historias y tickets
│   └── ...
├── specs/                # Especificaciones técnicas
│   ├── endpoints/        # Especificaciones de endpoints (41 archivos)
│   ├── models/           # Especificaciones de modelos (6 archivos)
│   ├── rules/            # Reglas de validación y negocio
│   ├── contracts/        # Contratos de API
│   ├── errors/           # Códigos de error
│   └── flows/            # Flujos E2E
└── README.md             # Este archivo
```

---

## Referencias

- **Contexto del proyecto:** `PROJECT_CONTEXT.md`
- **Reglas para el agente IA:** `AGENTS.md`

