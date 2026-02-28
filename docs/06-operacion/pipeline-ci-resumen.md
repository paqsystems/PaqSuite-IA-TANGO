# Pipeline CI/CD – Resumen de Implementación

Documento generado a partir de la configuración del pipeline con GitHub Actions. **No incluye deploy automático a producción** (a decisión del equipo).

---

## Lo que se configuró

Se creó un pipeline básico de **CI (Integración Continua)** con GitHub Actions.

### Archivos creados o modificados

| Archivo | Cambio |
|---------|--------|
| `.github/workflows/ci.yml` | Pipeline principal con 4 jobs (backend, frontend, swagger, e2e) |
| `docs/06-operacion/deploy-infraestructura.md` | Sección CI/CD ampliada con descripción del pipeline |
| `.cursor/Docs/ci.yml.md` | Documentación del workflow |
| `backend/database/seeders/TestUsersSeeder.php` | `GETDATE()` → `now()` (compatibilidad MySQL) |
| `backend/database/seeders/TestTasksSeeder.php` | `GETDATE()` → `now()` (compatibilidad MySQL) |
| `backend/tests/Feature/Api/V1/Auth/LoginTest.php` | `GETDATE()` → `now()` |
| `backend/tests/Unit/Services/TaskServiceTest.php` | `GETDATE()` → `now()` |
| `backend/tests/Feature/Api/V1/TaskControllerTest.php` | `GETDATE()` → `now()` |

---

## Jobs del pipeline

| Job | Descripción |
|-----|-------------|
| **backend** | Tests Laravel (PHPUnit) con MySQL 8.0 como servicio |
| **frontend** | Tests unitarios (Vitest) + build (Vite) |
| **swagger** | Generación de documentación OpenAPI (`php artisan l5-swagger:generate`) |
| **e2e** | Tests E2E (Playwright, Chromium) con backend y frontend en ejecución |

### Orden de ejecución

1. **backend** y **frontend** se ejecutan en paralelo
2. **swagger** depende de backend
3. **e2e** depende de backend y frontend (inicia `php artisan serve`, Playwright inicia el frontend)

---

## Rama principal y disparadores

La rama principal del proyecto es **main**.

**Estado actual:** El pipeline **no se ejecuta automáticamente** en push/PR (para no bloquear merge). Solo corre con ejecución manual (`workflow_dispatch` en GitHub Actions). Para habilitar la ejecución automática, descomentar `push` y `pull_request` en `.github/workflows/ci.yml`.

---

## Herramientas necesarias (en el runner de GitHub)

No requiere instalación adicional en tu máquina. GitHub Actions usa runners con:

- **PHP:** 8.2 (shivammathur/setup-php)
- **Node.js:** 20
- **MySQL:** 8.0 (contenedor)
- **Composer** y **npm** con caché

---

## Cómo probar el pipeline

1. Haz **push** a la rama `main`
2. O crea un **pull request** hacia `main`

El workflow se ejecutará automáticamente.

---

## Lo que NO se implementó

- **Deploy automático a producción** (Render, Vercel, Fly.io, etc.)  
- Se decidió no configurar deploy automático. El despliegue se realiza de forma manual según el flujo actual del equipo.

---

## Comandos para validar localmente

```bash
# Backend (con BD MySQL/SQL Server disponible)
cd backend && php artisan test

# Frontend – tests unitarios
cd frontend && npm run test:run

# Frontend – tests E2E (requiere backend en http://localhost:8000)
cd frontend && npm run test:e2e
```
## Aclaraciones Conceptuales

* CI y CD son independientes
CI (ci.yml): tests (backend, frontend, Swagger, E2E). No hace deploy.
CD (cd.yml): build y push de imágenes Docker a ghcr.io. No ejecuta tests.
Puedes activar solo CI copiando el workflow correspondiente:
    `cp docs/futuro/workflows/ci.yml .github/workflows/ci.yml`

* Qué hace el CI (sin CD)
Backend: PHPUnit con MySQL 8.0
Frontend: Vitest + build de Vite
Swagger: generación de OpenAPI
E2E: Playwright con backend y frontend en ejecución
No hay deploy ni URL externa; solo validación en cada push/PR.

* Rama configurada en CI
El CI está configurado para main:
on:
  push:
    branches: [main]
  pull_request:
    branches: [main]

* Posibles ajustes
- TestUsersSeeder / TestTasksSeeder: el CI los usa; comprueba que existan en el backend.
- l5-swagger: el job swagger usa php artisan l5-swagger:generate; si no usas Swagger, ese job puede fallar. Podrías quitarlo o condicionarlo.

---

## Referencias

- `docs/06-operacion/deploy-infraestructura.md` – Infraestructura y despliegue
- `.cursor/rules/12-testing.md` – Estrategia de tests
- `.cursor/Docs/ci.yml.md` – Documentación del workflow
