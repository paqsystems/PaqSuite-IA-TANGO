# Testing Frontend - Sistema de Registro de Tareas

## Descripción General

Este documento define la estrategia de testing para el frontend del MVP Sistema de Registro de Tareas.

---

## Tipos de Tests

### 1. Tests Unitarios

**Propósito:** Probar componentes y funciones de forma aislada.

**Cobertura Objetivo:** 70% mínimo

**Herramientas:**
- **React:** Jest + React Testing Library
- **Vue:** Vitest + Vue Test Utils
- **Angular:** Jasmine/Karma + Angular Testing Utilities

**Qué Testear:**
- Componentes individuales
- Funciones de utilidad
- Validadores de formularios
- Formateadores de datos
- Hooks personalizados (si React)

**Ejemplo (React):**

```typescript
import { render, screen, fireEvent } from '@testing-library/react';
import { LoginForm } from './LoginForm';

describe('LoginForm', () => {
  it('should render login form', () => {
    render(<LoginForm />);
    expect(screen.getByTestId('login-form')).toBeInTheDocument();
  });
  
  it('should call onSubmit with credentials', () => {
    const onSubmit = jest.fn();
    render(<LoginForm onSubmit={onSubmit} />);
    
    fireEvent.change(screen.getByTestId('login-form-usuario-input'), {
      target: { value: 'JPEREZ' }
    });
    fireEvent.change(screen.getByTestId('login-form-password-input'), {
      target: { value: 'password123' }
    });
    fireEvent.click(screen.getByTestId('login-form-submit-button'));
    
    expect(onSubmit).toHaveBeenCalledWith({
      usuario: 'JPEREZ',
      password: 'password123'
    });
  });
});
```

---

### 2. Tests de Integración

**Propósito:** Probar la interacción entre componentes y servicios.

**Cobertura Objetivo:** Funcionalidades críticas

**Herramientas:**
- Mismas que tests unitarios
- Mock de servicios API

**Qué Testear:**
- Integración componente ↔ servicio API
- Flujos de formularios completos
- Manejo de estados (loading, error, success)
- Navegación entre páginas

**Ejemplo:**

```typescript
import { render, screen, waitFor } from '@testing-library/react';
import { TaskEntryForm } from './TaskEntryForm';
import * as tasksService from '../services/tasks.service';

jest.mock('../services/tasks.service');

describe('TaskEntryForm Integration', () => {
  it('should create task and show success message', async () => {
    const mockCreate = jest.spyOn(tasksService, 'create')
      .mockResolvedValue({
        error: 0,
        respuesta: 'Tarea registrada correctamente',
        resultado: { id: 1, ... }
      });
    
    render(<TaskEntryForm />);
    
    // Llenar formulario
    fireEvent.change(screen.getByTestId('task-entry-date-input'), {
      target: { value: '2025-01-20' }
    });
    // ... más campos
    
    fireEvent.click(screen.getByTestId('task-entry-submit-button'));
    
    await waitFor(() => {
      expect(mockCreate).toHaveBeenCalled();
      expect(screen.getByText('Tarea registrada correctamente')).toBeInTheDocument();
    });
  });
});
```

---

### 3. Tests End-to-End (E2E) - TDD con Playwright

**Propósito:** Probar flujos completos desde la perspectiva del usuario usando **TDD (Test-Driven Development)**.

**Cobertura Objetivo:** Flujo E2E principal

**Herramienta instalada y configurada:**
- ✅ **Playwright** (instalado, versión 1.57.0, obligatorio para TDD)
- Configuración: `frontend/playwright.config.ts`
- Tests: `frontend/tests/e2e/`
- Reglas: `.cursor/rules/11-playwright-testing-rules.md`

**Estrategia TDD:**
1. **Red:** Escribir test primero usando `data-testid` planificados
2. **Green:** Implementar componente con los `data-testid` requeridos
3. **Refactor:** Mejorar código manteniendo tests verdes

**Flujo a Testear:**
1. Login
2. Registro de tarea
3. Visualización de resumen

**Regla Obligatoria:** TODOS los controles interactivos DEBEN tener `data-testid` para testing.

**Ejemplo (Playwright - TDD):**

```typescript
import { test, expect } from '@playwright/test';

test.describe('Flujo E2E Principal - TDD', () => {
  test('Login → Registrar Tarea → Ver Resumen', async ({ page }) => {
    // 1. Login
    await page.goto('/login');
    
    // Verificar que los elementos existen (TDD)
    await expect(page.locator('[data-testid="login-form"]')).toBeVisible();
    await expect(page.locator('[data-testid="login-form-usuario-input"]')).toBeVisible();
    await expect(page.locator('[data-testid="login-form-password-input"]')).toBeVisible();
    
    // Interactuar usando data-testid
    await page.fill('[data-testid="login-form-usuario-input"]', 'JPEREZ');
    await page.fill('[data-testid="login-form-password-input"]', 'password123');
    await page.click('[data-testid="login-form-submit-button"]');
    
    await expect(page).toHaveURL('/');
    
    // 2. Registrar Tarea
    await expect(page.locator('[data-testid="task-entry-form"]')).toBeVisible();
    
    await page.fill('[data-testid="task-entry-date-input"]', '2025-01-20');
    await page.selectOption('[data-testid="task-entry-cliente-select"]', '1');
    await page.selectOption('[data-testid="task-entry-tipo-select"]', '1');
    await page.fill('[data-testid="task-entry-duracion-input"]', '120');
    await page.check('[data-testid="task-entry-presencial-checkbox"]');
    await page.click('[data-testid="task-entry-submit-button"]');
    
    // Verificar mensaje de éxito
    await expect(page.locator('[data-testid="success-message"]')).toBeVisible();
    await expect(page.locator('[data-testid="task-list"]')).toContainText('2025-01-20');
    
    // 3. Ver Resumen
    await page.click('[data-testid="nav-resumen-link"]');
    await expect(page.locator('[data-testid="task-summary"]')).toBeVisible();
    await expect(page.locator('[data-testid="task-summary-total-hours"]')).toContainText('2');
  });
  
  test('Validación de campos requeridos', async ({ page }) => {
    await page.goto('/');
    
    // Intentar enviar sin llenar campos
    await page.click('[data-testid="task-entry-submit-button"]');
    
    // Verificar mensajes de error usando data-testid
    await expect(page.locator('[data-testid="task-entry-date-input"]')).toHaveAttribute('aria-invalid', 'true');
    await expect(page.locator('[data-testid="task-entry-error-message"]')).toBeVisible();
    await expect(page.locator('[data-testid="task-entry-error-message"]')).toContainText('obligatorio');
  });
});
```

**Optimizaciones de Testing con data-testid:**

1. **Selectores estables:** No se rompen con cambios de CSS
2. **Tests más rápidos:** Selectores por atributo son más eficientes
3. **TDD facilitado:** Puedes escribir tests antes de implementar
4. **Debugging mejorado:** Fácil identificar elementos en DevTools

---

## Estructura de Tests

```
frontend/
├── src/
└── tests/
    ├── unit/
    │   ├── components/
    │   │   ├── LoginForm.test.tsx
    │   │   ├── TaskEntryForm.test.tsx
    │   │   └── TaskList.test.tsx
    │   ├── services/
    │   │   ├── auth.service.test.ts
    │   │   └── tasks.service.test.ts
    │   ├── utils/
    │   │   ├── formatters.test.ts
    │   │   └── validators.test.ts
    │   └── hooks/
    │       └── useAuth.test.ts
    ├── integration/
    │   ├── LoginFlow.test.tsx
    │   ├── TaskEntryFlow.test.tsx
    │   └── TaskListFlow.test.tsx
    └── e2e/
        ├── login.spec.ts
        ├── task-entry.spec.ts
        └── task-summary.spec.ts
```

---

## Configuración

### Jest (React)

```json
// package.json
{
  "scripts": {
    "test": "jest",
    "test:watch": "jest --watch",
    "test:coverage": "jest --coverage"
  },
  "jest": {
    "testEnvironment": "jsdom",
    "setupFilesAfterEnv": ["<rootDir>/tests/setup.ts"],
    "moduleNameMapper": {
      "^@/(.*)$": "<rootDir>/src/$1"
    },
    "collectCoverageFrom": [
      "src/**/*.{ts,tsx}",
      "!src/**/*.d.ts",
      "!src/**/*.stories.{ts,tsx}"
    ]
  }
}
```

### Playwright

```typescript
// playwright.config.ts
import { defineConfig } from '@playwright/test';

export default defineConfig({
  testDir: './tests/e2e',
  use: {
    baseURL: 'http://localhost:3000',
    screenshot: 'only-on-failure',
  },
  webServer: {
    command: 'npm run dev',
    url: 'http://localhost:3000',
    reuseExistingServer: !process.env.CI,
  },
});
```

---

## Mocks y Fixtures

### Mock de API

```typescript
// tests/mocks/api.mock.ts
export const mockApiResponse = {
  success: <T>(data: T) => ({
    error: 0,
    respuesta: 'Éxito',
    resultado: data
  }),
  
  error: (code: number, message: string) => ({
    error: code,
    respuesta: message,
    resultado: null
  })
};

export const mockTask = {
  id: 1,
  fecha: '2025-01-20',
  cliente_id: 1,
  tipo_tarea_id: 1,
  duracion_minutos: 120,
  sin_cargo: false,
  presencial: false,
  observacion: 'Test task'
};
```

### Mock de Servicios

```typescript
// tests/mocks/tasks.service.mock.ts
import * as tasksService from '@/services/tasks.service';

export const mockTasksService = {
  create: jest.spyOn(tasksService, 'create'),
  list: jest.spyOn(tasksService, 'list'),
  update: jest.spyOn(tasksService, 'update'),
  delete: jest.spyOn(tasksService, 'delete')
};
```

---

## Cobertura de Código

### Objetivos

- **Cobertura mínima:** 70%
- **Cobertura objetivo:** 80%
- **Componentes críticos:** 90%+

### Comando

```bash
npm run test:coverage
```

### Reporte

Los reportes de cobertura se generan en `coverage/` y pueden visualizarse en HTML.

---

## Testing de Accesibilidad

### Herramientas

- **@testing-library/jest-dom** - Matchers para accesibilidad
- **jest-axe** - Tests de accesibilidad automatizados

### Ejemplo

```typescript
import { axe, toHaveNoViolations } from 'jest-axe';

expect.extend(toHaveNoViolations);

it('should have no accessibility violations', async () => {
  const { container } = render(<LoginForm />);
  const results = await axe(container);
  expect(results).toHaveNoViolations();
});
```

---

## Testing de Performance

### Herramientas

- **Lighthouse CI** - Performance testing automatizado
- **Web Vitals** - Métricas de performance

### Métricas Objetivo

- **LCP (Largest Contentful Paint):** < 2.5s
- **FID (First Input Delay):** < 100ms
- **CLS (Cumulative Layout Shift):** < 0.1

---

## Snapshot Testing

### Uso Limitado

Los snapshots solo deben usarse para:
- Componentes de UI puros (sin lógica)
- Componentes que cambian raramente

### Ejemplo

```typescript
it('should match snapshot', () => {
  const { container } = render(<Button>Click me</Button>);
  expect(container).toMatchSnapshot();
});
```

---

## Best Practices

### 1. Usar Test IDs

```typescript
// ✅ Bueno
screen.getByTestId('login-form-submit-button')

// ❌ Evitar
screen.getByText('Iniciar Sesión') // Puede cambiar
```

### 2. Testear Comportamiento, No Implementación

```typescript
// ✅ Bueno
expect(screen.getByText('Tarea registrada')).toBeInTheDocument();

// ❌ Evitar
expect(component.state.isSuccess).toBe(true);
```

### 3. Aislar Tests

Cada test debe ser independiente y no depender de otros tests.

### 4. Usar Arrange-Act-Assert

```typescript
// Arrange
const onSubmit = jest.fn();
render(<LoginForm onSubmit={onSubmit} />);

// Act
fireEvent.click(screen.getByTestId('login-form-submit-button'));

// Assert
expect(onSubmit).toHaveBeenCalled();
```

### 5. Limpiar Mocks

```typescript
afterEach(() => {
  jest.clearAllMocks();
});
```

---

## CI/CD Integration

### GitHub Actions

```yaml
name: Frontend Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - uses: actions/setup-node@v3
      - run: npm install
      - run: npm run test:coverage
      - run: npm run test:e2e
```

---

## Reglas Obligatorias

### Test IDs (data-testid) - OBLIGATORIO

**TODOS los controles interactivos DEBEN tener `data-testid`.**

Esto es esencial para:
- ✅ Testing TDD con Playwright
- ✅ Tests E2E robustos y mantenibles
- ✅ Optimización de velocidad de tests
- ✅ Debugging facilitado

**NO usar selectores CSS/XPath frágiles:**
```typescript
// ❌ Evitar
await page.click('.btn-primary');
await page.click('form > div > button');

// ✅ Usar siempre
await page.click('[data-testid="login-form-submit-button"]');
```

### Accesibilidad - OBLIGATORIA

**Todos los componentes deben ser accesibles y testearse.**

**Checklist de testing de accesibilidad:**
- [ ] Tests de violaciones de accesibilidad (jest-axe)
- [ ] Verificación de atributos ARIA
- [ ] Tests de navegación por teclado
- [ ] Verificación de contraste
- [ ] Tests E2E de accesibilidad con Playwright

## Referencias

- `specs/flows/e2e-core-flow.md` - Flujo E2E a testear
- `docs/frontend/frontend-specifications.md` - Especificaciones generales
- `.cursor/rules/10-i18n-and-testid.md` - Reglas de test-ids y accesibilidad

---

**Última actualización:** 2025-01-20

