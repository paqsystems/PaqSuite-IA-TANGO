# Tests E2E con Playwright

## Estructura

Los tests E2E se organizan según el flujo principal del MVP:

- `test-01-must-have-login-autenticacion.spec.ts` - Tests de autenticación
- `test-02-must-have-registro-tarea-completo.spec.ts` - Tests de registro de tarea
- `test-03-must-have-visualizacion-tareas-propias.spec.ts` - Tests de visualización
- `test-04-must-have-edicion-tarea-propia.spec.ts` - Tests de edición
- `test-05-must-have-resumen-dedicacion.spec.ts` - Tests de resumen

## Convenciones

### Test IDs

Todos los controles interactivos deben usar `data-testid` con el formato:
```
feature.component.element.action
```

Ejemplos:
- `auth.login.form`
- `auth.login.usuarioInput`
- `auth.login.submitButton`
- `tasks.entry.dateInput`
- `tasks.entry.submitButton`

### Estructura de Tests

```typescript
import { test, expect } from '@playwright/test';

test.describe('Nombre del Feature', () => {
  test('debe realizar acción específica', async ({ page }) => {
    // Arrange: Preparar el estado
    await page.goto('/ruta');
    
    // Act: Realizar la acción
    await page.fill('[data-testid="input"]', 'valor');
    await page.click('[data-testid="button"]');
    
    // Assert: Verificar el resultado
    await expect(page.locator('[data-testid="resultado"]')).toBeVisible();
  });
});
```

## Ejecutar Tests

```bash
# Todos los tests
npm run test:e2e

# Con UI interactiva
npm run test:e2e:ui

# Modo headed (ver navegador)
npm run test:e2e:headed

# Debug
npm run test:e2e:debug

# Test específico
npx playwright test tests/e2e/test-01-must-have-login-autenticacion.spec.ts
```

## Verificar Instalación

Para verificar que Playwright está correctamente instalado:

```bash
npx playwright --version
npx playwright test --list
```

Si los navegadores no están instalados:

```bash
npx playwright install
npx playwright install chromium  # Solo Chromium
```

## Referencias

- [Documentación de Playwright](https://playwright.dev)
- `docs/frontend/testing.md` - Estrategia de testing
- `specs/flows/e2e-core-flow.md` - Flujo E2E a testear
- `specs/tests/e2e/` - Especificaciones de tests

