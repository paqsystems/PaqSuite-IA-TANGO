# Documentación: `frontend/vitest.config.ts`

## Propósito

Configuración de **Vitest** para tests unitarios e integración del frontend. Separa la ejecución de Vitest de los tests E2E de Playwright para evitar el error:

```text
Playwright Test did not expect test.describe() to be called here.
```

## Motivo

- `npm run test` ejecuta Vitest.
- Sin config, Vitest busca `**/*.{test,spec}.{ts,tsx}` en todo el proyecto y carga también `tests/e2e/*.spec.ts`.
- Esos archivos usan la API de Playwright (`test.describe`, `test()`, etc.). Al cargarlos con Vitest se produce el conflicto.

## Contenido de la configuración

| Sección      | Descripción |
|-------------|-------------|
| `plugins`   | React plugin (mismo que Vite). |
| `resolve.alias` | Alias `@` → `./src`. |
| `test.globals`   | Habilita `describe`, `it`, `expect` globales. |
| `test.environment` | `node` (sin DOM). Para tests de componentes React se puede cambiar a `jsdom` e instalar `jsdom`. |
| `test.include`    | Solo `src/**/*.{test,spec}.{ts,tsx}` — Vitest **solo** ejecuta tests bajo `src/`. |
| `test.exclude`    | Excluye `node_modules`, `dist` y **`tests/e2e/**`** por seguridad. |

## Comandos

- **Tests unitarios/integración (Vitest):** `npm run test` — solo archivos en `src/` que coincidan con el patrón.
- **Tests E2E (Playwright):** `npm run test:e2e` — usa `playwright.config.ts` y la carpeta `tests/e2e/`.

## Notas

- Para tests de componentes React con DOM, instalar `jsdom` y poner `environment: 'jsdom'` en `test`.
- Los tests E2E siguen siendo exclusivos de Playwright y no se cargan con Vitest.
