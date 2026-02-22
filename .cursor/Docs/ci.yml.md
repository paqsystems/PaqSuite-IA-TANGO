# Documentación: .github/workflows/ci.yml

## Propósito
Pipeline CI/CD básico con GitHub Actions para el MVP Lidr. Ejecuta tests, genera documentación Swagger y valida el build del frontend.

## Ubicación
`.github/workflows/ci.yml`

## Disparadores
- **Actual:** solo `workflow_dispatch` (ejecución manual). No corre en push/PR.
- Para habilitar automático: descomentar `push` y `pull_request` en el archivo.

## Jobs

### 1. backend
- **Runtime:** ubuntu-latest, PHP 8.2
- **Servicio:** MySQL 8.0 (lidr_test)
- **Pasos:** checkout → setup PHP → cache Composer → composer install → config .env → migrate → seed (base + TestUsersSeeder) → `php artisan test`

### 2. frontend
- **Runtime:** ubuntu-latest, Node 20
- **Pasos:** checkout → setup Node → npm ci → `npm run test:run` (Vitest) → `npm run build` (Vite)

### 3. swagger
- **Depende de:** backend
- **Pasos:** checkout → setup PHP → composer install → `php artisan l5-swagger:generate`

### 4. e2e
- **Depende de:** backend, frontend
- **Servicio:** MySQL 8.0
- **Pasos:** setup backend + frontend → migrate + seed (TestUsersSeeder, TestTasksSeeder) → instalar Playwright (chromium) → iniciar `php artisan serve` en background → `npx playwright test --project=chromium`

## Variables de entorno en CI
- **MySQL:** DB_HOST=127.0.0.1, DB_DATABASE=lidr_test, DB_USERNAME=root, DB_PASSWORD=root
- **Playwright:** CI=true, PLAYWRIGHT_BASE_URL=http://localhost:3000

## Artefactos
En caso de fallo de tests E2E se sube `playwright-report/` como artefacto (retention 7 días).

## Referencias
- `docs/deploy-ci-cd.md`
- `docs/testing.md`
- AGENTS.md §6.4 Trazabilidad
