# PaqSuite-IA-ERP

Sistema de registro de tareas para consultorías y empresas de servicios. Evolución del proyecto Lidr-AI4Devs2025-ProyectoFinal.

# Enlaces

- **Repositorio:** https://github.com/paqsystems/PaqSuite-IA-ERP
- **URL pública (si aplica):** configurar en Vercel/Netlify u otro proveedor

# Reglas para Cursor — BackEnd Laravel (Sanctum) — Sistema de Partes de Atención

Este paquete contiene **solo reglas y especificaciones**. **NO** debe implementarse código todavía.

## Dónde colocar cada archivo en tu repo (recomendado)

### Reglas para Cursor (primario)
Copiar a:
- `/.cursor/rules/01-project-context.md`
- `/.cursor/rules/02-mvp-entregables.md`
- `/.cursor/rules/03-general-quality.md`
- `/.cursor/rules/04-custom-rules.md`
- `/.cursor/rules/05-backend-policy.md`
- `/.cursor/rules/06-api-contract.md`
- `/.cursor/rules/07-frontend-norms.md`
- `/.cursor/rules/08-security-sessions-tokens.md`
- `/.cursor/rules/09-data-access-orm-sql.md`
- `/.cursor/rules/10-i18n-and-testid.md`
- `/.cursor/rules/11-playwright-testing-rules.md`
- `/.cursor/rules/12-ticketing-domain.md`
- `/.cursor/rules/13-user-story-to-task-breakdown.md`
- `/.cursor/rules/14-dbml-sync-rule.md`
- `/.cursor/rules/15-task-execution-traceability.md`
- `/.cursor/rules/16-hu-simple-vs-hu-compleja.md`
- `/.cursor/rules/16-prompt-dispatcher.md`
- `/.cursor/rules/20-sql-server-datetime-format.md`
- `/.cursor/rules/21-Iniciar-tunel-SSH-para-MySql.md`

> Si tu instalación de Cursor usa un único archivo `.cursorrules`, concatená el contenido de los archivos anteriores en ese archivo (manteniendo el orden).

### Documentación del repositorio (referencia humana)
Copiar a:
- `/docs/api/CONTRATO_BASE.md`
- `/docs/backend/PLAYBOOK_BACKEND_LARAVEL.md`
- `/docs/api/TICKETING_API_SPEC.md`

## Orden recomendado de lectura para Cursor
1. 01-project-context.md
2. 02-mvp-entregables.md
3. 03-general-quality.md
4. 04-custom-rules.md
5. 05-backend-policy.md
6. 06-api-contract.md
7. 07-frontend-norms.md
8. 08-security-sessions-tokens.md
9. 09-data-access-orm-sql.md
10. 10-i18n-and-testid.md
11. 11-playwright-testing-rules.md
12. 12-ticketing-domain.md
13. 13-user-story-to-task-breakdown.md
14. 14-dbml-sync-rule.md
15. 15-task-execution-traceability.md
16. 16-hu-simple-vs-hu-compleja.md
17. 16-prompt-dispatcher.md
18. 20-sql-server-datetime-format.md
19. 21-Iniciar-tunel-SSH-para-MySql.md

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

El flujo E2E prioritario del MVP es:

1. Autenticación de empleado
2. Registro de una tarea diaria
3. Visualización de tareas propias
4. Consulta agrupada por cliente
5. Visualización del dashboard principal

Este flujo está cubierto por las historias:
- HU-001
- HU-028
- HU-033
- HU-044
- HU-046
- HU-051

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

### Autenticación
- [ ] Login de empleado (HU-001)
- [ ] Logout funcional (HU-003)

### Gestión base (Supervisor)
- [ ] ABM de Clientes (HU-008 a HU-012)
- [ ] ABM de Tipos de Cliente (HU-014 a HU-017)
- [ ] ABM de Empleados (HU-018 a HU-021)
- [ ] ABM de Tipos de Tarea con reglas de genérico y por defecto (HU-023 a HU-026)

### Registro de tareas
- [ ] Carga de tarea diaria (HU-028)
- [ ] Validación de duración en tramos de 15 minutos (HU-035)
- [ ] Advertencia de fecha futura (HU-036)
- [ ] Filtrado dinámico de tipos de tarea por cliente (HU-037)
- [ ] Edición y eliminación de tareas propias (HU-029, HU-030)
- [ ] Edición y eliminación de tareas por supervisor (HU-031, HU-032)

### Visualización
- [ ] Listado de tareas propias (HU-033)
- [ ] Listado de todas las tareas (supervisor) (HU-034)

### Informes
- [ ] Consulta detallada de tareas (HU-044)
- [ ] Consulta agrupada por cliente (HU-046)
- [ ] Manejo de resultados vacíos (HU-050)

### Dashboard
- [ ] Dashboard principal funcional (HU-051)
- [ ] Resumen de dedicación por cliente (HU-052)

### Calidad técnica
- [ ] Tests unitarios backend
- [ ] Tests de integración backend
- [ ] Al menos un test E2E del flujo principal
- [ ] CI/CD básico configurado
- [ ] Documentación de API disponible

---

## Documentación Técnica

### Producto
📄 Ver `docs/producto.md` para la descripción completa del producto, público objetivo y características principales.

### Arquitectura
📄 Ver `docs/arquitectura.md` para la arquitectura del sistema (Frontend, Backend, Base de Datos) y decisiones clave.

### Modelo de Datos
📄 Ver `docs/modelo-datos.md` para el modelo completo con entidades, relaciones y restricciones.

**Arquitectura de Autenticación:**
- **Tabla `USERS`** (sin prefijo PQ_PARTES_): Tabla central de autenticación del sistema
- **Flujo de autenticación:** Login se valida contra `USERS`, luego se determina si corresponde a un Cliente (`PQ_PARTES_CLIENTES`) o Usuario (`PQ_PARTES_USUARIOS`)
- **Valores conservados durante el ciclo:** `tipo_usuario`, `user_code`, `usuario_id`/`cliente_id`, `es_supervisor`
- **Visualización gráfica:** Ver `database/modelo-datos.dbml` para el modelo en formato DBML (compatible con dbdiagram.io)

📄 Ver `specs/models/` para especificaciones detalladas de cada modelo:
- `usuario-model.md` - Modelo de Empleado
- `registro-tarea-model.md` - Modelo de Registro de Tarea
- `cliente-model.md` - Modelo de Cliente
- `tipo-cliente-model.md` - Modelo de Tipo de Cliente
- `tipo-tarea-model.md` - Modelo de Tipo de Tarea
- `cliente-tipo-tarea-model.md` - Modelo de asociación Cliente-TipoTarea

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

**Gestión de Clientes (Solo Supervisores):**
- `GET /api/v1/clientes` - Listar clientes
- `POST /api/v1/clientes` - Crear cliente
- `GET /api/v1/clientes/{id}` - Obtener cliente
- `PUT /api/v1/clientes/{id}` - Actualizar cliente
- `DELETE /api/v1/clientes/{id}` - Eliminar cliente
- `GET /api/v1/clientes/{id}/tipos-tarea` - Listar tipos de tarea asignados
- `POST /api/v1/clientes/{id}/tipos-tarea` - Asignar tipo de tarea
- `DELETE /api/v1/clientes/{id}/tipos-tarea/{tipo_tarea_id}` - Desasignar tipo de tarea

**Gestión de Tipos de Cliente (Solo Supervisores):**
- `GET /api/v1/tipos-cliente` - Listar tipos de cliente
- `POST /api/v1/tipos-cliente` - Crear tipo de cliente
- `GET /api/v1/tipos-cliente/{id}` - Obtener tipo de cliente
- `PUT /api/v1/tipos-cliente/{id}` - Actualizar tipo de cliente
- `DELETE /api/v1/tipos-cliente/{id}` - Eliminar tipo de cliente

**Gestión de Empleados (Solo Supervisores):**
- `GET /api/v1/empleados` - Listar empleados
- `POST /api/v1/empleados` - Crear empleado
- `GET /api/v1/empleados/{id}` - Obtener empleado
- `PUT /api/v1/empleados/{id}` - Actualizar empleado
- `DELETE /api/v1/empleados/{id}` - Eliminar empleado

**Gestión de Tipos de Tarea (Solo Supervisores):**
- `GET /api/v1/tipos-tarea` - Listar tipos de tarea
- `POST /api/v1/tipos-tarea` - Crear tipo de tarea
- `GET /api/v1/tipos-tarea/{id}` - Obtener tipo de tarea
- `PUT /api/v1/tipos-tarea/{id}` - Actualizar tipo de tarea
- `DELETE /api/v1/tipos-tarea/{id}` - Eliminar tipo de tarea

**Registro de Tareas:**
- `POST /api/v1/tareas` - Crear registro de tarea
- `GET /api/v1/tareas` - Listar tareas (filtrado automático por rol: clientes ven solo sus tareas, empleados NO supervisores ven solo las propias)
- `GET /api/v1/tareas/{id}` - Obtener tarea
- `PUT /api/v1/tareas/{id}` - Actualizar tarea
- `DELETE /api/v1/tareas/{id}` - Eliminar tarea
- `GET /api/v1/tareas/tipos-disponibles?cliente_id={id}` - Obtener tipos de tarea disponibles para un cliente

**Proceso Masivo (Solo Supervisores):**
- `GET /api/v1/tareas/proceso-masivo` - Listar tareas para proceso masivo
- `POST /api/v1/tareas/proceso-masivo` - Procesar tareas masivamente (cerrar/reabrir)

**Informes y Consultas:**
- `GET /api/v1/informes/detalle` - Consulta detallada de tareas (filtrado automático por rol)
- `GET /api/v1/informes/por-empleado` - Consulta agrupada por empleado (filtrado automático por rol)
- `GET /api/v1/informes/por-cliente` - Consulta agrupada por cliente (filtrado automático por rol)
- `GET /api/v1/informes/por-tipo` - Consulta agrupada por tipo de tarea (filtrado automático por rol)
- `GET /api/v1/informes/por-fecha` - Consulta agrupada por fecha (filtrado automático por rol)
- `GET /api/v1/informes/exportar` - Exportar informe a Excel (respeta permisos del usuario)

> **Nota:** Todos los endpoints de informes aplican filtros automáticos según el rol del usuario autenticado:
> - **Clientes:** Solo ven tareas donde `cliente_id` coincide con su `cliente_id`
> - **Empleados NO supervisores:** Solo ven tareas donde `usuario_id` coincide con su `usuario_id`
> - **Supervisores:** Ven todas las tareas de todos los usuarios

**Dashboard:**
- `GET /api/v1/dashboard/resumen` - Resumen ejecutivo del dashboard (filtrado automático por rol)
- `GET /api/v1/dashboard/por-cliente` - Resumen por cliente (filtrado automático por rol)
- `GET /api/v1/dashboard/por-empleado` - Resumen por empleado (filtrado automático por rol)

> **Nota:** Todos los endpoints de dashboard aplican filtros automáticos según el rol del usuario autenticado (mismas reglas que Informes y Consultas).

**Documentación completa:** Ver `specs/endpoints/` para especificaciones detalladas de cada endpoint.

**Códigos de error:** Ver `specs/errors/domain-error-codes.md` para el catálogo completo de códigos de error.

**Reglas de validación:** Ver `specs/rules/validation-rules.md` para validaciones de formato y tipo.

**Reglas de negocio:** Ver `specs/rules/business-rules.md` para reglas específicas del dominio.

### Historias de Usuario

📄 Ver `docs/historias-y-tickets.md` para el catálogo completo de historias de usuario.

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

📄 Ver `docs/historias-y-tickets.md` (sección "Tickets Técnicos Derivados") para el catálogo completo de tickets técnicos.

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

**Documentación completa:** Ver `.cursor/rules/12-testing.md` y `docs/frontend/testing.md`

### Pull Requests

**Entrega 1 – Documentación Técnica**  
Los cambios están en la rama `feature-entrega1-PAQ`. PR hacia `main` con documentación técnica (producto, arquitectura, modelo de datos, historias, tickets, especificaciones de API, Playwright). Ver `docs/_ENTREGA-1/INSTRUCCIONES-PR-ENTREGA-1.md`.

**Entrega 2 – Código funcional (primer MVP ejecutable)**  
Los cambios están en la rama `feature-entrega2-PAQ`. PR hacia `main` con backend, frontend y BD conectados, flujo principal "casi" completo (auth, tareas, clientes, informes, dashboard) y suite de tests. Ver `docs/_ENTREGA-2/VERIFICACION-ENTREGA-2.md` y `docs/_ENTREGA-2/INSTRUCCIONES-PR-ENTREGA-2.md`.

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

- **Consignas del MVP:** `.cursor/consignas.md`
- **Contexto del proyecto:** `PROJECT_CONTEXT.md`
- **Reglas para el agente IA:** `AGENTS.md`
- **Prompts utilizados:** `prompts.md`

## Mi comportamiento con la IA

La operatoria básica fue : 
- hice megaprompts en ChatGpt para que me fabrique prompts apropiados para cursor.
- coloqué los requisitos del proyecto final en Cursor (.cursor/consignas.md)
- Realice las solicitudes a Cursor de generación de archivos de contextos y especificaciones:
  a) Generar las historias de usuario.
  b) Generar los planes de tarea a partir de cada historia de usuario, bajo las normativas especificadas en .cursor/rules/13-user-story-to-task-breakdown.md
  c) Ejecutar las tareas planificadas, incluyendo los testings unitarios y e2e.
- Revisé lo generado, e interactué con ChatGpt para entre ambos hacer las correcciones necesarias.
- Solicite mejoras y correcciones a Cursor.
- Hice una nueva revisión.
- Pedi que verifique el cumplimiento de los requisitos para cumplir la primer entrega.
- le solicité completar la documentación faltante.
- pedí revisar, explorar instrucciones duplicadas y ordenar toda la documentación.
- realicé el primer PR
- pedí generar un documento MANUAL-PROGRAMADOR.md para ver si un tercero puede seguir el proyecto sin explicación humana.
- Si en los testing manuales encontraba fallas, el proceso de corrección consistía : 
  a) Revisar los E2E para entender porqué no había encontrado la falla, y que corrija el testing en consecuencia.
  b) generar una HU aclarando la corrección a realizar
  c) 

## Cómo me sentí con el proceso : 
- me sentí muy cómodo.
- como soy más analista de sistemas que programador, creo entender bien la diferencia de funciones entre la IA y yo, y poder hacer las ordenes, seguimientos y controles como si la IA fuese un programador humano.
- Sólo siento que me sobrepasa la cantidad de información que genera. Debo contemplar reservar una importante suma de tiempo para poder controlar y revisar todo lo que genera, tanto código como documentación.
- Por normativa propia y ampliado ahora por el punto anterior, procuro realizar pasos pequeños cada vez, para poder hacer un mejor seguimiento y evitar "alucinaciones" de la IA
- en ningún momento generé código manualmente. Tampoco solicité cambios en el código desde el prompt, sino que siempre modifiqué especificaciones y le pedí que adecué el código a la misma.
- en forma muy artesanal, en casos de ajustes, correcciones o modificaciones procuré usar el concepto de spec-kit : generar un documento con el mismo nombre del TR a modificar con el agregado "-update"; documenté ahí todos los pasos como una tarea nueva; al quedar debidamente probado, solicité integrar ese documento al original. Desgraciadamente omití conservar una copia como ejemplo.