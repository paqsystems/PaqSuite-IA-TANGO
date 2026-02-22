---
alwaysApply: true
---
# description: Reglas de Testing E2E con Playwright

## Objetivo

Definir reglas obligatorias para el desarrollo de tests End-to-End (E2E) usando **Playwright** en el frontend del **Sistema de Registro de Tareas MVP**.

## Alcance

Estas reglas aplican a:
- Todos los tests E2E ubicados en `frontend/tests/e2e/`
- Configuración de Playwright (`frontend/playwright.config.ts`)
- Implementación de componentes que requieren `data-testid` para testing
- Estrategia de Test-Driven Development (TDD)

## Principios Fundamentales

1. **TDD es obligatorio:** Escribir tests antes de implementar componentes
2. **data-testid es obligatorio:** Todos los controles interactivos deben tener `data-testid`
3. **Selectores estables:** Usar `data-testid` en lugar de CSS/XPath/texto
4. **Tests independientes:** Cada test debe poder ejecutarse de forma aislada
5. **Flujo E2E prioritario:** El flujo Login → Registro de Tarea → Visualización debe estar siempre verde

---

## Reglas Obligatorias

### 1. Uso de data-testid (OBLIGATORIO)

**Regla:** TODOS los controles interactivos y estados testables DEBEN tener `data-testid`.

**Formato:** `<feature>.<component>.<element>.<actionOrState>`

**Ejemplos:**
```typescript
// ✅ CORRECTO
data-testid="auth.login.form"
data-testid="auth.login.usuarioInput"
data-testid="auth.login.submitButton"
data-testid="tasks.entry.dateInput"
data-testid="tasks.table.row.3.editButton"

// ❌ INCORRECTO
data-testid="boton-login"  // No usar español
data-testid="submit-btn"   // No usar camelCase
data-testid="form#1"       // No usar caracteres especiales
```

**Referencia completa:** Ver `.cursor/rules/10-i18n-and-testid.md` para convenciones detalladas.

### 2. Selectores en Tests (OBLIGATORIO)

**Regla:** SIEMPRE usar `data-testid` para seleccionar elementos en tests.

**Ejemplos:**
```typescript
// ✅ CORRECTO - Selector por data-testid
await page.click('[data-testid="tasks.entry.submitButton"]');
await page.fill('[data-testid="auth.login.usuarioInput"]', 'JPEREZ');
await expect(page.locator('[data-testid="tasks.table.container"]')).toBeVisible();

// ❌ INCORRECTO - Selector por CSS
await page.click('.btn-primary');
await page.fill('#usuario-input', 'JPEREZ');

// ❌ INCORRECTO - Selector por texto
await page.click('button:has-text("Registrar Tarea")');

// ❌ INCORRECTO - Selector por XPath
await page.click('//form//button[@type="submit"]');
```

**Razón:** Los selectores por `data-testid` son:
- Estables ante cambios de CSS
- Independientes de textos traducidos
- Más rápidos de ejecutar
- Más fáciles de mantener

### 3. Estructura de Tests (OBLIGATORIO)

**Regla:** Todos los tests deben seguir el patrón **Arrange-Act-Assert (AAA)**.

**Formato:**
```typescript
test('descripción clara del comportamiento', async ({ page }) => {
  // Arrange: Preparar el estado inicial
  await page.goto('/ruta');
  await page.fill('[data-testid="input"]', 'valor');
  
  // Act: Realizar la acción a testear
  await page.click('[data-testid="button"]');
  
  // Assert: Verificar el resultado esperado
  await expect(page.locator('[data-testid="resultado"]')).toBeVisible();
  await expect(page.locator('[data-testid="resultado"]')).toContainText('texto esperado');
});
```

**Ejemplo completo:**
```typescript
import { test, expect } from '@playwright/test';

test.describe('Registro de Tarea', () => {
  test('debe registrar una tarea correctamente', async ({ page }) => {
    // Arrange: Login y navegación
    await page.goto('/login');
    await page.fill('[data-testid="auth.login.usuarioInput"]', 'JPEREZ');
    await page.fill('[data-testid="auth.login.passwordInput"]', 'password123');
    await page.click('[data-testid="auth.login.submitButton"]');
    await expect(page).toHaveURL('/');
    
    // Act: Llenar y enviar formulario
    await page.fill('[data-testid="tasks.entry.dateInput"]', '2025-01-20');
    await page.selectOption('[data-testid="tasks.entry.clienteSelect"]', '1');
    await page.selectOption('[data-testid="tasks.entry.tipoSelect"]', '1');
    await page.fill('[data-testid="tasks.entry.duracionInput"]', '120');
    await page.click('[data-testid="tasks.entry.submitButton"]');
    
    // Assert: Verificar éxito
    await expect(page.locator('[data-testid="tasks.entry.successMessage"]')).toBeVisible();
    await expect(page.locator('[data-testid="tasks.table.container"]')).toContainText('2025-01-20');
  });
});
```

### 4. Nomenclatura de Tests (OBLIGATORIO)

**Regla:** Los nombres de tests deben ser descriptivos y seguir el formato: `debe [acción] [condición/resultado]`.

**Formato:**
```typescript
test('debe [verbo] [objeto] [condición/resultado]', async ({ page }) => {
  // ...
});
```

**Ejemplos:**
```typescript
// ✅ CORRECTO
test('debe registrar una tarea correctamente', async ({ page }) => { });
test('debe validar campos requeridos al enviar formulario vacío', async ({ page }) => { });
test('debe mostrar mensaje de error con credenciales inválidas', async ({ page }) => { });
test('debe redirigir al login cuando el token expira', async ({ page }) => { });

// ❌ INCORRECTO
test('test 1', async ({ page }) => { });
test('registro', async ({ page }) => { });
test('debe funcionar', async ({ page }) => { });
```

### 5. Organización de Tests (OBLIGATORIO)

**Regla:** Los tests deben organizarse por feature usando `test.describe()`.

**Estructura recomendada:**
```typescript
// frontend/tests/e2e/test-01-must-have-login-autenticacion.spec.ts
import { test, expect } from '@playwright/test';

test.describe('Autenticación - Flujo E2E Principal', () => {
  test('debe autenticar usuario con credenciales válidas', async ({ page }) => {
    // ...
  });
  
  test('debe rechazar credenciales inválidas', async ({ page }) => {
    // ...
  });
  
  test('debe redirigir al dashboard después del login exitoso', async ({ page }) => {
    // ...
  });
});
```

**Nomenclatura de archivos:**
- `test-01-must-have-login-autenticacion.spec.ts`
- `test-02-must-have-registro-tarea-completo.spec.ts`
- `test-03-must-have-visualizacion-tareas-propias.spec.ts`

### 6. Flujo E2E Prioritario (OBLIGATORIO)

**Regla:** El flujo E2E principal (Login → Registro de Tarea → Visualización) DEBE estar siempre funcionando.

**Flujo a testear:**
1. **Login:** Autenticación de usuario
2. **Registro de Tarea:** Crear un registro de tarea
3. **Visualización:** Ver resumen de dedicación

**Test obligatorio:**
```typescript
test.describe('Flujo E2E Principal - MUST-HAVE', () => {
  test('Login → Registrar Tarea → Ver Resumen', async ({ page }) => {
    // 1. Login
    await page.goto('/login');
    await page.fill('[data-testid="auth.login.usuarioInput"]', 'JPEREZ');
    await page.fill('[data-testid="auth.login.passwordInput"]', 'password123');
    await page.click('[data-testid="auth.login.submitButton"]');
    await expect(page).toHaveURL('/');
    
    // 2. Registrar Tarea
    await page.fill('[data-testid="tasks.entry.dateInput"]', '2025-01-20');
    await page.selectOption('[data-testid="tasks.entry.clienteSelect"]', '1');
    await page.selectOption('[data-testid="tasks.entry.tipoSelect"]', '1');
    await page.fill('[data-testid="tasks.entry.duracionInput"]', '120');
    await page.click('[data-testid="tasks.entry.submitButton"]');
    
    // 3. Ver Resumen
    await page.click('[data-testid="nav.resumen.link"]');
    await expect(page.locator('[data-testid="tasks.summary.container"]')).toBeVisible();
    await expect(page.locator('[data-testid="tasks.summary.totalHours"]')).toContainText('2');
  });
});
```

**Referencia:** Ver `specs/flows/e2e-core-flow.md` para el flujo completo documentado.

### 7. Manejo de Estados Asíncronos (OBLIGATORIO)

**Regla:** SIEMPRE esperar a que los elementos estén visibles/interactuables antes de interactuar.

**Ejemplos:**
```typescript
// ✅ CORRECTO - Esperar visibilidad
await expect(page.locator('[data-testid="tasks.table.container"]')).toBeVisible();
await page.click('[data-testid="tasks.entry.submitButton"]');

// ✅ CORRECTO - Esperar carga de datos
await page.waitForSelector('[data-testid="tasks.table.row.1"]', { state: 'visible' });

// ✅ CORRECTO - Esperar navegación
await expect(page).toHaveURL('/dashboard');

// ✅ CORRECTO - Esperar que un elemento desaparezca
await expect(page.locator('[data-testid="loading.spinner"]')).not.toBeVisible();

// ✅ CORRECTO - Esperar respuesta de red
await page.waitForResponse(response => response.url().includes('/api/tasks') && response.status() === 200);

// ❌ INCORRECTO - No esperar
await page.click('[data-testid="button"]');  // Puede fallar si el botón aún no está listo
```

### 7.1. Prohibición de Esperas Ciegas (OBLIGATORIO)

**Regla:** Está **PROHIBIDO** usar esperas ciegas (blind waits) o delays fijos en los tests.

**Prohibido:**
- `page.waitForTimeout(milliseconds)` - Espera ciega sin condición
- `setTimeout()` / `sleep()` / `delay()` - Delays fijos
- Cualquier espera basada en tiempo fijo sin verificar estado

**Razones:**
- Las esperas ciegas hacen los tests lentos e ineficientes
- Son frágiles: pueden fallar en máquinas lentas o pasar en máquinas rápidas cuando deberían fallar
- No verifican el estado real de la aplicación
- Dificultan la detección de problemas reales de performance

**Ejemplos de lo que NO se debe hacer:**
```typescript
// ❌ PROHIBIDO - Espera ciega con waitForTimeout
await page.waitForTimeout(2000);  // Esperar 2 segundos sin verificar nada
await page.click('[data-testid="button"]');

// ❌ PROHIBIDO - Delay fijo con setTimeout
await new Promise(resolve => setTimeout(resolve, 1000));
await page.fill('[data-testid="input"]', 'valor');

// ❌ PROHIBIDO - Sleep o delay de librerías externas
await sleep(500);
await page.click('[data-testid="submitButton"]');

// ❌ PROHIBIDO - Espera ciega antes de verificar
await page.waitForTimeout(3000);
await expect(page.locator('[data-testid="result"]')).toBeVisible();
```

**Alternativas correctas:**
```typescript
// ✅ CORRECTO - Esperar visibilidad del elemento
await expect(page.locator('[data-testid="button"]')).toBeVisible();
await page.click('[data-testid="button"]');

// ✅ CORRECTO - Esperar que un elemento esté en estado específico
await expect(page.locator('[data-testid="loading.spinner"]')).not.toBeVisible();
await expect(page.locator('[data-testid="result"]')).toBeVisible();

// ✅ CORRECTO - Esperar respuesta de red
await page.waitForResponse(response => 
  response.url().includes('/api/tasks') && response.status() === 200
);
await expect(page.locator('[data-testid="tasks.table"]')).toBeVisible();

// ✅ CORRECTO - Esperar que el texto aparezca
await expect(page.locator('[data-testid="message"]')).toContainText('Éxito');

// ✅ CORRECTO - Esperar cambio de URL
await expect(page).toHaveURL('/dashboard');

// ✅ CORRECTO - Esperar que un elemento sea interactuable
await expect(page.locator('[data-testid="submitButton"]')).toBeEnabled();
await page.click('[data-testid="submitButton"]');

// ✅ CORRECTO - Esperar múltiples condiciones
await Promise.all([
  expect(page.locator('[data-testid="table"]')).toBeVisible(),
  expect(page.locator('[data-testid="loading"]')).not.toBeVisible()
]);
```

**Excepciones (muy raras):**
- Solo se permite `waitForTimeout` en casos excepcionales y debe estar documentado con justificación clara:
```typescript
// ⚠️ EXCEPCIÓN - Solo si es absolutamente necesario y está documentado
// Justificación: Esperar animación CSS que no expone estado en DOM
await page.waitForTimeout(300);  // Duración mínima de animación CSS
// TODO: Reemplazar cuando se agregue data-testid para estado de animación
```

### 8. Validaciones y Mensajes de Error (OBLIGATORIO)

**Regla:** Los tests deben verificar mensajes de error usando `data-testid` y atributos ARIA.

**Ejemplos:**
```typescript
test('debe validar campos requeridos', async ({ page }) => {
  // Act: Intentar enviar sin llenar campos
  await page.goto('/');
  await page.click('[data-testid="tasks.entry.submitButton"]');
  
  // Assert: Verificar mensajes de error
  await expect(page.locator('[data-testid="tasks.entry.dateInput"]')).toHaveAttribute('aria-invalid', 'true');
  await expect(page.locator('[data-testid="tasks.entry.dateError"]')).toBeVisible();
  await expect(page.locator('[data-testid="tasks.entry.dateError"]')).toContainText('obligatorio');
});
```

### 9. Configuración de Playwright (OBLIGATORIO)

**Regla:** La configuración debe estar en `frontend/playwright.config.ts` y seguir el estándar del proyecto.

**Configuración mínima requerida:**
```typescript
import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './tests/e2e',
  use: {
    baseURL: process.env.PLAYWRIGHT_BASE_URL || 'http://localhost:3000',
    screenshot: 'only-on-failure',
    trace: 'on-first-retry',
  },
  webServer: {
    command: 'npm run dev',
    url: 'http://localhost:3000',
    reuseExistingServer: !process.env.CI,
  },
});
```

**Referencia:** Ver `frontend/playwright.config.ts` para la configuración completa.

---

## Estrategia TDD (Test-Driven Development)

### Flujo TDD Obligatorio

1. **Red (Red):** Escribir test primero usando `data-testid` planificados
2. **Green (Green):** Implementar componente con los `data-testid` requeridos
3. **Refactor (Refactor):** Mejorar código manteniendo tests verdes

### Ejemplo de TDD

**Paso 1: Red - Escribir test primero**
```typescript
// frontend/tests/e2e/test-login.spec.ts
test('debe autenticar usuario correctamente', async ({ page }) => {
  await page.goto('/login');
  await page.fill('[data-testid="auth.login.usuarioInput"]', 'JPEREZ');
  await page.fill('[data-testid="auth.login.passwordInput"]', 'password123');
  await page.click('[data-testid="auth.login.submitButton"]');
  await expect(page).toHaveURL('/');
});
```

**Paso 2: Green - Implementar componente con data-testid**
```typescript
// frontend/src/features/auth/components/LoginForm.tsx
<form data-testid="auth.login.form">
  <input data-testid="auth.login.usuarioInput" type="text" />
  <input data-testid="auth.login.passwordInput" type="password" />
  <button data-testid="auth.login.submitButton" type="submit">
    Iniciar Sesión
  </button>
</form>
```

**Paso 3: Refactor - Mejorar manteniendo tests verdes**
- Agregar validaciones
- Mejorar UX
- Optimizar código
- **Mantener todos los `data-testid` intactos**

---

## Mejores Prácticas

### 1. Fixtures y Helpers

**Crear helpers para acciones comunes:**
```typescript
// frontend/tests/e2e/helpers/auth.ts
import { Page } from '@playwright/test';

export async function login(page: Page, usuario: string, password: string) {
  await page.goto('/login');
  await page.fill('[data-testid="auth.login.usuarioInput"]', usuario);
  await page.fill('[data-testid="auth.login.passwordInput"]', password);
  await page.click('[data-testid="auth.login.submitButton"]');
  await expect(page).toHaveURL('/');
}
```

**Uso en tests:**
```typescript
import { login } from '../helpers/auth';

test('debe registrar tarea después de login', async ({ page }) => {
  await login(page, 'JPEREZ', 'password123');
  // ... resto del test
});
```

### 2. Page Object Model (Opcional pero Recomendado)

Para tests complejos, considerar usar Page Object Model:
```typescript
// frontend/tests/e2e/pages/LoginPage.ts
import { Page, Locator } from '@playwright/test';

export class LoginPage {
  readonly page: Page;
  readonly usuarioInput: Locator;
  readonly passwordInput: Locator;
  readonly submitButton: Locator;

  constructor(page: Page) {
    this.page = page;
    this.usuarioInput = page.locator('[data-testid="auth.login.usuarioInput"]');
    this.passwordInput = page.locator('[data-testid="auth.login.passwordInput"]');
    this.submitButton = page.locator('[data-testid="auth.login.submitButton"]');
  }

  async goto() {
    await this.page.goto('/login');
  }

  async login(usuario: string, password: string) {
    await this.usuarioInput.fill(usuario);
    await this.passwordInput.fill(password);
    await this.submitButton.click();
  }
}
```

### 3. Datos de Prueba

**Usar datos consistentes:**
```typescript
// frontend/tests/e2e/fixtures/test-data.ts
export const TEST_USERS = {
  EMPLEADO: {
    usuario: 'JPEREZ',
    password: 'password123',
  },
  SUPERVISOR: {
    usuario: 'MGARCIA',
    password: 'password456',
  },
};

export const TEST_CLIENTES = {
  CLIENTE_A: { id: 1, nombre: 'Cliente A' },
  CLIENTE_B: { id: 2, nombre: 'Cliente B' },
};
```

### 4. Limpieza de Estado

**Asegurar que cada test comience en un estado limpio:**
```typescript
test.beforeEach(async ({ page }) => {
  // Limpiar localStorage/sessionStorage si es necesario
  await page.goto('/');
  await page.evaluate(() => {
    localStorage.clear();
    sessionStorage.clear();
  });
});
```

---

## Comandos y Scripts

### Scripts Disponibles

```bash
# Ejecutar todos los tests E2E
npm run test:e2e

# Ejecutar con UI interactiva
npm run test:e2e:ui

# Ejecutar viendo el navegador
npm run test:e2e:headed

# Ejecutar en modo debug
npm run test:e2e:debug

# Ejecutar test específico
npx playwright test tests/e2e/test-login.spec.ts

# Ejecutar en navegador específico
npx playwright test --project=chromium
```

### Variables de Entorno

```bash
# Configurar URL base
PLAYWRIGHT_BASE_URL=http://localhost:3000 npm run test:e2e

# Modo CI
CI=true npm run test:e2e
```

---

## Checklist de Implementación

### Para cada Test E2E

- [ ] Test sigue el patrón Arrange-Act-Assert
- [ ] Nombre del test es descriptivo (`debe [acción] [resultado]`)
- [ ] Todos los selectores usan `data-testid`
- [ ] Test espera estados asíncronos correctamente
- [ ] Test verifica resultados con `expect()`
- [ ] Test puede ejecutarse de forma independiente
- [ ] Test está en el archivo correcto según feature

### Para cada Componente

- [ ] Todos los controles interactivos tienen `data-testid`
- [ ] `data-testid` sigue la convención: `<feature>.<component>.<element>`
- [ ] Estados testables (vacío, carga, error) tienen `data-testid`
- [ ] Componente tiene tests E2E correspondientes

### Para el Flujo E2E Principal

- [ ] Test del flujo completo (Login → Registro → Visualización) existe
- [ ] Test del flujo principal está siempre verde
- [ ] Test valida todos los criterios de aceptación del flujo

---

## Referencias

### Documentación del Proyecto

- `docs/frontend/testing.md` - Estrategia completa de testing
- `specs/flows/e2e-core-flow.md` - Flujo E2E documentado
- `specs/tests/e2e/` - Especificaciones de tests
- `frontend/tests/e2e/README.md` - Convenciones de tests E2E
- `.cursor/rules/10-i18n-and-testid.md` - Reglas de `data-testid` e i18n

### Documentación Externa

- [Playwright Documentation](https://playwright.dev)
- [Playwright Best Practices](https://playwright.dev/docs/best-practices)
- [Playwright Test API](https://playwright.dev/docs/api/class-test)

---

## Notas Importantes

1. **No romper el flujo E2E principal:** Cualquier cambio que rompa el flujo Login → Registro → Visualización es considerado breaking change.

2. **Mantener tests rápidos:** Los tests E2E deben ejecutarse en menos de 30 segundos cada uno (idealmente menos de 10 segundos).

3. **Evitar dependencias entre tests:** Cada test debe poder ejecutarse de forma independiente.

4. **Usar mocks cuando sea necesario:** Para tests que no requieren backend real, usar mocks de API.

5. **Documentar tests complejos:** Si un test tiene lógica compleja, agregar comentarios explicativos.

---

**Última actualización:** 2025-01-20

