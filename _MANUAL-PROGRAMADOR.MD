# Manual del Programador

**Versión:** 1.7  
**Última actualización:** 2026-02-28

---

## Tabla de Contenidos

**PARTE 1 – Aspecto técnico**
1. [Stack tecnológico](#parte-1--aspecto-técnico)
2. [Proceso Backend](#2-proceso-backend)
3. [Proceso Frontend](#3-proceso-frontend)
4. [Proceso API](#4-proceso-api)

**PARTE 2 – Seguimiento de documentación**
5. [Estructura documental](#parte-2--seguimiento-de-documentación)
6. [Contexto y arquitectura](#6-contexto-y-arquitectura)
7. [Historias de usuario y tareas](#7-historias-de-usuario-y-tareas)
8. [Testing y calidad](#8-testing-y-calidad)
9. [Cómo empezar a trabajar](#9-cómo-empezar-a-trabajar)
10. [Referencias rápidas](#10-referencias-rápidas)

---

# PARTE 1 – Aspecto técnico

## 1. Stack tecnológico

### Backend

| Componente | Tecnología |
|------------|------------|
| Framework | Laravel (PHP) |
| Autenticación | Laravel Sanctum (Bearer Token) |
| ORM | Eloquent |
| Base de datos | SQL Server / PostgreSQL / MySQL |
| API | REST con formato envelope estándar |
| Testing | PHPUnit |

### Frontend

| Componente | Tecnología |
|------------|------------|
| Framework | React (TypeScript) |
| Build | Vite |
| UI | Tailwind CSS + shadcn/ui (Radix UI) / DevExtreme |
| State | Context API o Redux/Zustand |
| Testing | Vitest (unitarios), Playwright (E2E) |
| i18n | Función `t()` obligatoria |

### Base de datos

- **Prefijo de tablas:** `PQ_{modulo}_` (excepto USERS para login inicial)
- **Autenticación:** Por código de usuario (campo `code`), no email

### Mobile (Capacitor)

El proyecto está preparado para **mobile**. Un solo código base (`frontend/`) sirve web y mobile; Capacitor empaqueta el build como app nativa iOS/Android.

| Aspecto | Detalle |
|---------|---------|
| Modelo | Mismo código React para web y mobile |
| Empaquetado | Capacitor (`@capacitor/core`, `@capacitor/android`, `@capacitor/ios`) |
| Documentación | `docs/mobile/README.md`, `.cursor/rules/07b-frontend-mobile-norms.md` |

### CI/CD

| Componente | Estado |
|------------|--------|
| **CI (Integración Continua)** | Implementado (GitHub Actions). Ejecuta tests (backend, frontend, E2E) en push/PR o manualmente |
| **CD (Despliegue Continuo)** | Manual por el momento. El deploy a producción se realiza manualmente |

Ver `docs/06-operacion/deploy-infraestructura.md` y `.cursor/rules/23-versioning-and-deploy.md` para detalles.

### Convenciones de código

- **Nomenclatura:** camelCase para variables, propiedades, métodos y funciones

---

## 2. Proceso Backend

### Dónde consultar

| Documento | Propósito |
|-----------|-----------|
| `backend/README.md` | Estructura, modelos implementados, comandos |
| `docs/backend/PLAYBOOK_BACKEND_LARAVEL.md` | Convenciones Laravel, estructura de código |
| `docs/backend/autenticacion.md` | Flujo de autenticación, endpoints |

### Flujo típico

1. **Migraciones:** `database/migrations/` – definir o modificar tablas
2. **Modelos:** `app/Models/` – Eloquent, relaciones, scopes
3. **Validación:** `app/Http/Requests/` – Form Requests
4. **Controladores:** `app/Http/Controllers/` – lógica de endpoints
5. **Rutas:** `routes/api.php` – registro de rutas API

### Reglas obligatorias

- Prefijo de tablas: `PQ_{modulo}_`
- Formato de respuesta: envelope estándar (ver `specs/contracts/response-envelope.md`)
- PHPDoc en todas las clases, métodos y propiedades

---

## 3. Proceso Frontend

### Dónde consultar

| Documento | Propósito |
|-----------|-----------|
| `docs/frontend/frontend-specifications.md` | Especificaciones generales |
| `docs/frontend/ui-layer-wrappers.md` | Reglas de UI Layer (OBLIGATORIO) |
| `docs/frontend/i18n.md` | Internacionalización |
| `docs/frontend/features/features-structure.md` | Organización de features |

### Flujo típico

1. **UI Layer:** Componentes reutilizables en `src/shared/ui/`
2. **Features:** Lógica por dominio en `src/features/`
3. **Servicios:** Llamadas API en `src/services/`
4. **i18n:** Todos los textos visibles con `t()` y fallback
5. **Test IDs:** `data-testid` en todos los controles interactivos

### Reglas obligatorias

- No importar librerías UI externas directamente en features; usar `shared/ui/`
- Formato i18n: `t("feature.scope.element", "Fallback text")`
- Formato testId: `data-testid="feature.component.element"`

---

## 4. Proceso API

### Dónde consultar

| Documento | Propósito |
|-----------|-----------|
| `docs/api/CONTRATO_BASE.md` | Formato estándar de respuestas |
| `specs/contracts/response-envelope.md` | Estructura envelope |
| `specs/endpoints/README.md` | Guía de especificaciones de endpoints |

### Flujo típico

1. Consultar especificación en `specs/endpoints/` para el endpoint
2. Implementar según contrato (envelope, códigos de error)
3. Validar con Form Requests en backend
4. Consumir desde frontend vía servicios

---

# PARTE 2 – Seguimiento de documentación

## 5. Estructura documental

La documentación sigue la estructura corporativa en `docs/`:

```
docs/
├── 00-contexto/           # Contexto institucional, guías, onboarding
├── 01-arquitectura/       # Arquitectura técnica, modelo de datos
├── 02-producto/           # Definición del producto
├── 03-historias-usuario/  # Historias de usuario (HU)
├── 04-tareas/             # Tareas técnicas (TR)
├── 05-testing/            # Estrategia y trazabilidad de tests
├── 06-operacion/          # Deploy, procedimientos
├── 07-seguridad/          # Análisis de amenazas
├── api/                   # Contrato API, OpenAPI
├── backend/               # Playbook Laravel, documentación backend
├── frontend/              # Especificaciones frontend
└── prompts/              # Prompts reutilizables (HU→TR, ejecución TR)
```

**Nota:** La carpeta `docs/_projects/` contiene material histórico para re-aplicar a futuro; no forma parte del flujo activo de documentación.

---

## 6. Contexto y arquitectura

### Orden de lectura recomendado

| Orden | Documento | Propósito |
|-------|-----------|-----------|
| 1 | `docs/00-contexto/00-contexto-global-erp.md` | Contexto del ERP |
| 2 | `docs/00-contexto/02-guia-onboarding-30-minutos.md` | Onboarding rápido |
| 3 | `docs/00-contexto/01-guia-estructura-documental-corporativa.md` | Organización de docs |
| 4 | `docs/01-arquitectura/01-arquitectura-proyecto.md` | Capas, responsabilidades |
| 5 | `docs/arquitectura.md` | Visión general MVP |

---

## 7. Historias de usuario y tareas

### Historias de usuario (HU)

- **Ubicación:** `docs/03-historias-usuario/`
- **Organización:** Por épica (ej. `000-Generalidades/`, `001-Seguridad/`)
- **Formato:** Una HU por archivo (ej. `HU-007-Parametros-generales.md`)

### Tareas técnicas (TR)

- **Ubicación:** `docs/04-tareas/`
- **Origen:** Derivadas de las HU (comando "Aplicá el prompt correspondiente a la historia")
- **Ejecución:** Comando "Ejecutá la TR TR-xxx.md" (dispatcher en `.cursor/rules/16-prompt-dispatcher.md`)

### Flujo HU → TR

1. Leer la HU en `docs/03-historias-usuario/`
2. Generar TR con el dispatcher o manualmente
3. Guardar TR en `docs/04-tareas/`
4. Ejecutar TR siguiendo el prompt en `docs/prompts/05-Ejecucion-de-una-TR.md`

### Documentos de apoyo

| Documento | Propósito |
|-----------|-----------|
| `docs/README.md` | Guía de navegación de documentación |
| `.cursor/rules/16-prompt-dispatcher.md` | Comandos HU→TR, ejecución TR |
| `.cursor/rules/16-hu-simple-vs-hu-compleja.md` | Clasificación HU Simple/Compleja |
| `docs/prompts/04-Prompts-HU-a-Tareas.md` | Prompt para generar TR desde HU |

---

## 8. Testing y calidad

### Estrategia

| Tipo | Herramienta | Ubicación |
|------|-------------|-----------|
| Unitarios (backend) | PHPUnit | `backend/tests/` |
| Integración (backend) | PHPUnit | `backend/tests/Feature/` |
| Unitarios (frontend) | Vitest | `frontend/src/` (*.test.ts) |
| E2E (frontend) | Playwright | `frontend/tests/e2e/` |

### Documentos

| Documento | Propósito |
|-----------|-----------|
| `.cursor/rules/12-testing.md` | Estrategia, cómo ejecutar tests |
| `docs/frontend/testing.md` | Vitest y Playwright |
| `.cursor/rules/11-playwright-testing-rules.md` | Reglas E2E |

### Comandos

```bash
# Backend
cd backend && php artisan test

# Frontend (unitarios + E2E)
cd frontend && npm run test:all
cd frontend && npm run test:run    # Solo Vitest
cd frontend && npm run test:e2e   # Solo Playwright
```

---

## 9. Cómo empezar a trabajar

### 1. Configuración del entorno

```bash
# Backend
cd backend
composer install
cp .env.example .env
# Configurar .env: DB_*, APP_KEY
php artisan key:generate
php artisan migrate
php artisan serve

# Frontend (otra terminal)
cd frontend
npm install
npm run dev
```

### 2. Túnel SSH (MySQL remoto)

Si la BD está en servidor remoto: ver `docs/migracion-mssql-a-mysql.md` y `.cursor/rules/21-Iniciar-tunel-SSH-para-MySql.md`.

### 3. Identificar la tarea

1. Revisar HU en `docs/03-historias-usuario/`
2. Buscar o generar TR en `docs/04-tareas/`
3. Ejecutar: "Ejecutá la TR TR-xxx.md" (o seguir el prompt manualmente)

### 4. Checklist antes de cerrar tarea

- [ ] Código según especificaciones
- [ ] Tests pasando (`npm run test:all` en frontend)
- [ ] i18n y data-testid (frontend)
- [ ] Documentación actualizada si aplica

---

## 10. Referencias rápidas

### Documentos clave

| Documento | Propósito |
|-----------|-----------|
| `docs/README.md` | Navegación de documentación |
| `docs/00-contexto/01-guia-estructura-documental-corporativa.md` | Estructura 00–07 |
| `docs/03-historias-usuario/` | Historias de usuario |
| `docs/04-tareas/` | Tareas técnicas |
| `backend/README.md` | Guía del backend |
| `docs/backend/PLAYBOOK_BACKEND_LARAVEL.md` | Convenciones Laravel |
| `docs/api/CONTRATO_BASE.md` | Contrato API |
| `docs/frontend/ui-layer-wrappers.md` | UI Layer |
| `.cursor/rules/12-testing.md` | Testing |

### Reglas Cursor

- `16-prompt-dispatcher.md` – Comandos HU→TR, ejecución TR
- `16-hu-simple-vs-hu-compleja.md` – Clasificación HU
- `12-testing.md` – Estrategia de tests

### Archivos con credenciales

| Archivo | Variables |
|---------|-----------|
| `backend/.env` | DB_*, APP_KEY, MAIL_* |
| `frontend/.env` | VITE_API_URL |
| `frontend/src/devextreme-license.ts` | Licencia DevExtreme |

---

## Objetivo conceptual del proyecto

Este es un **MVP web** para consultorías y empresas de servicios que permite a empleados registrar tareas diarias (fecha, cliente, tipo de tarea, duración), asociándolas a clientes, con el objetivo de analizar la dedicación y generar informes.

**Flujo E2E prioritario:** Login → Registro de tarea → Visualización → Informes → Dashboard

**Principios:** Simplicidad, valor completo del flujo E2E, trazabilidad, testing enfocado.

---

## Actualizaciones del manual

**v1.7 (2026-02-28):**
- Añadidas secciones Mobile (Capacitor) y CI/CD en PARTE 1 – Stack tecnológico

**v1.6 (2026-02-28):**
- Reestructurado en dos partes: (1) Aspecto técnico, (2) Seguimiento de documentación
- Actualizadas rutas: `docs/03-historias-usuario/`, `docs/04-tareas/`
- Eliminadas referencias a `docs/historias-y-tickets.md` y `docs/_projects/`
- Añadida sección explícita de flujo HU→TR

**v1.5:** Archivos con credenciales (BD, DevExtreme, API URL, túnel SSH)

**v1.4:** README docs/ y backend/, Ruta de Lectura, estructura del proyecto
