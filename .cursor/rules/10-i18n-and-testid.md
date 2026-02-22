---
alwaysApply: true
---
# description: Internacionalización y Test IDs

## Internacionalización (i18n) - OBLIGATORIO

### Regla Fundamental

**TODOS los textos visibles al usuario DEBEN usar la función `t()` con fallback obligatorio.**

### Formato Obligatorio

```typescript
t("feature.scope.element.property", "Fallback readable text", params?)
```

**Parámetros:**
1. **Key (obligatorio):** Clave de traducción con notación de puntos
2. **Fallback (obligatorio):** Texto legible en español que se muestra si no hay traducción
3. **Params (opcional):** Parámetros para interpolación

### Ejemplos

```typescript
// Ejemplos correctos
t("auth.login.title", "Iniciar Sesión")
t("tasks.table.empty", "No hay tareas encontradas")
t("common.save", "Guardar")
t("tasks.summary.totalHours", "Total de horas: {{hours}}", { hours: 2.5 })
t("tasks.list.item.count", "{{count}} tareas", { count: 5 })
```

### Reglas Estrictas

1. **SIEMPRE pasar AMBOS parámetros:** key y fallback
2. **NUNCA omitir el fallback:** Es obligatorio
3. **NO intentar traducción ahora:** `t()` retorna el fallback por defecto
4. **Keys deben ser estables:** Usar notación de puntos, no cambiar una vez definidas
5. **Fallback debe ser legible:** Texto claro en español que tenga sentido sin contexto

### Estructura de Keys

**Formato:** `<feature>.<scope>.<element>.<property>`

**Ejemplos:**
- `auth.login.title` - Título de login
- `auth.login.submitButton` - Botón de envío
- `tasks.table.emptyState` - Estado vacío de tabla
- `tasks.filters.searchInput` - Input de búsqueda en filtros
- `tasks.table.row.editButton` - Botón editar en fila de tabla
- `common.actions.save` - Acción guardar común
- `common.actions.cancel` - Acción cancelar común

### Uso en Componentes

```typescript
// ✅ CORRECTO - Con fallback obligatorio
import { useTranslation } from 'react-i18next';

function LoginForm() {
  const { t } = useTranslation();
  
  return (
    <form data-testid="auth.login.form">
      <h1>{t("auth.login.title", "Iniciar Sesión")}</h1>
      <button 
        data-testid="auth.login.submitButton"
        type="submit"
      >
        {t("auth.login.submitButton", "Iniciar Sesión")}
      </button>
    </form>
  );
}

// ❌ INCORRECTO - Sin fallback
<button>{t("auth.login.submitButton")}</button>

// ❌ INCORRECTO - Texto hardcodeado
<button>Iniciar Sesión</button>
```

### Interpolación de Variables

```typescript
// Con parámetros
t("tasks.summary.totalHours", "Total: {{hours}} horas", { hours: 2.5 })
t("tasks.list.count", "{{count}} tareas encontradas", { count: 5 })
t("common.pagination.page", "Página {{current}} de {{total}}", { current: 1, total: 10 })
```

### Textos que NO se Traducen

Los siguientes elementos **NO** deben usar `t()`:
- Códigos de error técnicos (ej: `1101`, `3201`)
- IDs de base de datos
- Nombres de variables/campos técnicos
- Valores de `data-testid`
- Nombres de archivos o rutas técnicas

---

## Test IDs (data-testid) - OBLIGATORIO

### Regla Fundamental

**TODOS los controles interactivos y estados testables de la UI DEBEN incluir `data-testid`.**

### Elementos Requeridos

**OBLIGATORIO en:**
- ✅ **Botones** (submit, cancel, delete, edit, etc.)
- ✅ **Inputs** (text, date, number, email, password, etc.)
- ✅ **Selects/Dropdowns**
- ✅ **Textareas**
- ✅ **Enlaces** que actúan como acciones (no solo navegación)
- ✅ **Modales/Dialogs**
- ✅ **Tablas** donde se testean filas/acciones
- ✅ **Estados vacíos/carga/error** si se validan en E2E
- ✅ **Toasts/Alerts** si se validan en E2E
- ✅ **Formularios** (contenedores)
- ✅ **Checkboxes y Radio buttons**
- ✅ **Tabs/Pestañas**
- ✅ **Contenedores principales** (listas, secciones testables)

**NO usar en:**
- ❌ Elementos decorativos puros (divs vacíos, separadores visuales)
- ❌ Textos estáticos sin interacción (párrafos informativos, títulos decorativos)

### Convención de Nomenclatura

**Formato:** `<feature>.<component>.<element>.<actionOrState>`

**Reglas:**
- Usar notación de puntos (no guiones ni camelCase)
- Empezar con el feature/área funcional
- Ser descriptivo pero conciso
- Incluir acción o estado cuando sea relevante

**Ejemplos:**

```typescript
// Filtros
data-testid="tasks.filters.searchInput"
data-testid="tasks.filters.dateFromInput"
data-testid="tasks.filters.applyButton"

// Tabla
data-testid="tasks.table.container"
data-testid="tasks.table.emptyState"
data-testid="tasks.table.row.3"  // ID dinámico
data-testid="tasks.table.row.3.editButton"
data-testid="tasks.table.row.3.deleteButton"

// Autenticación
data-testid="auth.login.form"
data-testid="auth.login.usuarioInput"
data-testid="auth.login.passwordInput"
data-testid="auth.login.submitButton"
data-testid="auth.login.errorMessage"

// Formulario de tarea
data-testid="tasks.entry.form"
data-testid="tasks.entry.dateInput"
data-testid="tasks.entry.clienteSelect"
data-testid="tasks.entry.tipoSelect"
data-testid="tasks.entry.duracionInput"
data-testid="tasks.entry.sinCargoCheckbox"
data-testid="tasks.entry.presencialCheckbox"
data-testid="tasks.entry.observacionTextarea"
data-testid="tasks.entry.submitButton"
data-testid="tasks.entry.successMessage"
data-testid="tasks.entry.errorMessage"

// Resumen
data-testid="tasks.summary.container"
data-testid="tasks.summary.totalHours"
data-testid="tasks.summary.byClient.container"
data-testid="tasks.summary.client.1.hours"  // ID dinámico
```

### Uso en Componentes

```typescript
// Ejemplo completo: i18n + data-testid + accesibilidad
import { useTranslation } from 'react-i18next';

function TaskEntryForm() {
  const { t } = useTranslation();
  const [hasError, setHasError] = useState(false);
  
  return (
    <form 
      data-testid="tasks.entry.form"
      aria-label={t("tasks.entry.formLabel", "Formulario de registro de tarea")}
      onSubmit={handleSubmit}
    >
      <label htmlFor="task-date">
        {t("tasks.entry.dateLabel", "Fecha")}
      </label>
      <input 
        id="task-date"
        data-testid="tasks.entry.dateInput"
        type="date"
        aria-required="true"
        aria-invalid={hasError}
        aria-describedby={hasError ? "task-date-error" : undefined}
        aria-label={t("tasks.entry.dateLabel", "Fecha de la tarea")}
      />
      {hasError && (
        <div 
          id="task-date-error"
          data-testid="tasks.entry.dateError"
          role="alert"
          aria-live="polite"
        >
          {t("tasks.entry.dateError", "La fecha es obligatoria")}
        </div>
      )}
      
      <button 
        data-testid="tasks.entry.submitButton"
        type="submit"
        aria-label={t("tasks.entry.submitButton", "Registrar tarea")}
      >
        {t("tasks.entry.submitButton", "Registrar Tarea")}
      </button>
    </form>
  );
}
```

### Estados Dinámicos

Para elementos con IDs dinámicos (como filas de tabla):

```typescript
// Fila de tabla con ID dinámico
<tr data-testid={`tasks.table.row.${task.id}`}>
  <td>{task.fecha}</td>
  <td>
    <button 
      data-testid={`tasks.table.row.${task.id}.editButton`}
      aria-label={t("tasks.table.row.editButton", "Editar tarea")}
    >
      {t("tasks.table.row.editButton", "Editar")}
    </button>
  </td>
</tr>

// Estado vacío
{isEmpty && (
  <div data-testid="tasks.table.emptyState">
    {t("tasks.table.emptyState", "No hay tareas registradas")}
  </div>
)}

// Estado de carga
{isLoading && (
  <div data-testid="tasks.table.loadingState">
    {t("tasks.table.loadingState", "Cargando tareas...")}
  </div>
)}
```

---

## Regla de Combinación (i18n + data-testid)

### Principio Fundamental

**Los `data-testid` y los textos de UI están completamente separados:**

1. **`data-testid`:** NO debe incluir texto traducido
2. **Texto de UI:** DEBE venir de `t()` con fallback
3. **Separación clara:** Los test-ids son técnicos, los textos son de usuario

### Ejemplos Correctos

```typescript
// ✅ CORRECTO - Separación clara
<button 
  data-testid="tasks.entry.submitButton"  // Técnico, no traducido
  aria-label={t("tasks.entry.submitButton", "Registrar tarea")}  // Traducido
>
  {t("tasks.entry.submitButton", "Registrar Tarea")}  // Traducido
</button>

// ✅ CORRECTO - Mensaje de error
<div 
  data-testid="tasks.entry.errorMessage"  // Técnico
  role="alert"
>
  {t("tasks.entry.errorMessage", "Error al registrar la tarea")}  // Traducido
</div>

// ✅ CORRECTO - Input con label
<label htmlFor="task-date">
  {t("tasks.entry.dateLabel", "Fecha")}  // Traducido
</label>
<input 
  id="task-date"
  data-testid="tasks.entry.dateInput"  // Técnico
  type="date"
  aria-label={t("tasks.entry.dateLabel", "Fecha de la tarea")}  // Traducido
/>
```

### Ejemplos Incorrectos

```typescript
// ❌ INCORRECTO - data-testid con texto traducido
<button data-testid="boton-registrar-tarea">  // No usar español
  {t("tasks.entry.submitButton", "Registrar Tarea")}
</button>

// ❌ INCORRECTO - Texto hardcodeado sin t()
<button data-testid="tasks.entry.submitButton">
  Registrar Tarea  // Debe usar t()
</button>

// ❌ INCORRECTO - data-testid que depende de traducción
<button data-testid={`boton-${t("tasks.entry.submitButton", "registrar")}`}>
  {t("tasks.entry.submitButton", "Registrar Tarea")}
</button>
```

---

## Testing TDD con Playwright

### Estrategia TDD

Los `data-testid` son esenciales para Test-Driven Development:

1. **Red:** Escribir test primero usando `data-testid` planificados
2. **Green:** Implementar componente con los `data-testid` requeridos
3. **Refactor:** Mejorar código manteniendo tests verdes

### Uso en Playwright

```typescript
// tests/e2e/tasks-entry.spec.ts
import { test, expect } from '@playwright/test';

test.describe('Registro de Tarea - TDD', () => {
  test('debe registrar una tarea correctamente', async ({ page }) => {
    // Arrange: Login
    await page.goto('/login');
    await page.fill('[data-testid="auth.login.usuarioInput"]', 'JPEREZ');
    await page.fill('[data-testid="auth.login.passwordInput"]', 'password123');
    await page.click('[data-testid="auth.login.submitButton"]');
    
    // Act: Llenar formulario
    await page.fill('[data-testid="tasks.entry.dateInput"]', '2025-01-20');
    await page.selectOption('[data-testid="tasks.entry.clienteSelect"]', '1');
    await page.selectOption('[data-testid="tasks.entry.tipoSelect"]', '1');
    await page.fill('[data-testid="tasks.entry.duracionInput"]', '120');
    await page.check('[data-testid="tasks.entry.presencialCheckbox"]');
    await page.click('[data-testid="tasks.entry.submitButton"]');
    
    // Assert: Verificar resultado
    await expect(page.locator('[data-testid="tasks.entry.successMessage"]')).toBeVisible();
    await expect(page.locator('[data-testid="tasks.table.container"]')).toContainText('2025-01-20');
  });
  
  test('debe validar campos requeridos', async ({ page }) => {
    await page.goto('/');
    await page.click('[data-testid="tasks.entry.submitButton"]');
    
    // Verificar mensajes de error usando data-testid
    await expect(page.locator('[data-testid="tasks.entry.dateInput"]')).toHaveAttribute('aria-invalid', 'true');
    await expect(page.locator('[data-testid="tasks.entry.dateError"]')).toBeVisible();
  });
  
  test('debe mostrar estado vacío cuando no hay tareas', async ({ page }) => {
    await page.goto('/tareas');
    
    // Verificar estado vacío
    await expect(page.locator('[data-testid="tasks.table.emptyState"]')).toBeVisible();
    await expect(page.locator('[data-testid="tasks.table.emptyState"]')).toContainText('No hay tareas');
  });
});
```

### Optimizaciones de Testing

**Ventajas de usar `data-testid` con notación de puntos:**

1. **Selectores estables:** No se rompen con cambios de CSS o estructura HTML
2. **Tests más rápidos:** Selectores por atributo son más eficientes
3. **Mantenibilidad:** Fácil identificar qué elementos se testean
4. **TDD facilitado:** Puedes escribir tests antes de implementar
5. **Debugging:** Fácil identificar elementos en DevTools
6. **Organización:** La notación de puntos refleja la estructura de features

**Comparación:**

```typescript
// ❌ Frágil - se rompe con cambios de CSS
await page.click('.btn-primary.submit-button');

// ❌ Frágil - se rompe con cambios de estructura
await page.click('form > div > button');

// ❌ Frágil - depende de texto traducido
await page.click('button:has-text("Registrar Tarea")');

// ✅ Robusto - no se rompe, independiente de estilos y textos
await page.click('[data-testid="tasks.entry.submitButton"]');
```

---

## Accesibilidad (A11y) - OBLIGATORIA

### Principios

**TODOS los componentes deben ser accesibles.** Los `data-testid` complementan pero NO reemplazan atributos ARIA.

### Atributos Obligatorios

1. **Labels:**
   - `aria-label` para elementos sin texto visible (usar `t()` para el texto)
   - `aria-labelledby` para referenciar labels existentes
   - `<label>` asociado con `htmlFor` para inputs (texto con `t()`)

2. **Estados:**
   - `aria-required="true"` para campos obligatorios
   - `aria-invalid="true"` para campos con errores
   - `aria-disabled="true"` para elementos deshabilitados

3. **Mensajes:**
   - `role="alert"` para mensajes de error críticos
   - `aria-live="polite"` para actualizaciones dinámicas
   - `aria-live="assertive"` para mensajes urgentes

4. **Navegación:**
   - `role="navigation"` para menús
   - `aria-current="page"` para elemento activo
   - Navegación por teclado funcional

### Ejemplo Completo (i18n + data-testid + Accesibilidad)

```typescript
<form 
  data-testid="tasks.entry.form"
  aria-label={t("tasks.entry.formLabel", "Formulario de registro de tarea")}
  onSubmit={handleSubmit}
>
  <label htmlFor="task-date">
    {t("tasks.entry.dateLabel", "Fecha")}
  </label>
  <input 
    id="task-date"
    data-testid="tasks.entry.dateInput"
    type="date"
    aria-required="true"
    aria-invalid={hasError}
    aria-describedby={hasError ? "task-date-error" : undefined}
    aria-label={t("tasks.entry.dateLabel", "Fecha de la tarea")}
  />
  {hasError && (
    <div 
      id="task-date-error"
      data-testid="tasks.entry.dateError"
      role="alert"
      aria-live="polite"
    >
      {t("tasks.entry.dateError", "La fecha es obligatoria")}
    </div>
  )}
  
  <button 
    data-testid="tasks.entry.submitButton"
    type="submit"
    aria-label={t("tasks.entry.submitButton", "Registrar tarea")}
    disabled={isSubmitting}
    aria-disabled={isSubmitting}
  >
    {isSubmitting 
      ? t("tasks.entry.submitting", "Registrando...") 
      : t("tasks.entry.submitButton", "Registrar Tarea")
    }
  </button>
</form>
```

---

## Checklist de Implementación

### Para cada Componente

- [ ] Todos los textos visibles usan `t()` con fallback obligatorio
- [ ] Todos los controles interactivos tienen `data-testid`
- [ ] `data-testid` usa notación de puntos: `<feature>.<component>.<element>`
- [ ] `data-testid` NO incluye texto traducido
- [ ] Textos de UI vienen de `t()`, no están hardcodeados
- [ ] Atributos ARIA usan `t()` para textos descriptivos
- [ ] Labels asociados con `htmlFor` para inputs
- [ ] Campos requeridos tienen `aria-required="true"`
- [ ] Mensajes de error tienen `role="alert"` y `aria-live`
- [ ] Navegación por teclado funcional

---

## Referencias

- `docs/frontend/i18n.md` - Documentación completa de i18n
- `docs/frontend/testing.md` - Estrategia de testing y TDD
- `docs/frontend/frontend-specifications.md` - Especificaciones generales
