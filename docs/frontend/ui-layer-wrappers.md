# UI Layer Wrappers - Arquitectura Obligatoria

## Descripción General

Esta arquitectura define que **ninguna feature importa librerías UI externas directamente**. Todos los componentes visuales reutilizables deben ser wrappers en `src/shared/ui/` que encapsulan la funcionalidad y aplican las reglas de calidad del proyecto.

---

## Regla Fundamental

**Nadie importa librerías UI externas en features.**

**Todo componente visual reutilizable vive en:**
```
src/shared/ui/<ComponentName>/
```

**Las features solo importan desde:**
```typescript
import { Button, TextField, DataTable, Modal } from '@/shared/ui';
```

---

## Estructura de Archivos

```
src/
  shared/
    ui/
      Button/
        Button.tsx          # Componente
        Button.module.css   # Estilos (CSS Modules)
        index.ts            # Exportación
      TextField/
        TextField.tsx
        TextField.module.css
        index.ts
      DataTable/
        DataTable.tsx
        DataTable.module.css
        index.ts
      Modal/
        Modal.tsx
        Modal.module.css
        index.ts
      index.ts              # Exportación centralizada
    i18n/
      t.ts                  # Función de traducción
      index.ts
```

---

## Reglas de Calidad Obligatorias

### 1. Test ID Obligatorio

**TODOS los wrappers de controles DEBEN tener `testId` como prop obligatoria.**

```typescript
// ✅ CORRECTO
<Button testId="auth.login.submitButton" labelKey="auth.login.submit" />

// ❌ INCORRECTO - Sin testId
<Button labelKey="auth.login.submit" />
```

**Convención de testId:**
- Formato: `<feature>.<component>.<element>.<actionOrState>`
- Ejemplos:
  - `auth.login.submitButton`
  - `tasks.entry.dateInput`
  - `tasks.table.row.3.editButton`

### 2. i18n Obligatorio

**TODO texto visible DEBE usar `t(key, fallback)` con fallback obligatorio.**

```typescript
// ✅ CORRECTO
const label = t("auth.login.title", "Iniciar Sesión");

// ❌ INCORRECTO - Sin fallback
const label = t("auth.login.title");

// ❌ INCORRECTO - Texto hardcodeado
const label = "Iniciar Sesión";
```

**Los componentes wrappers deben:**
- Aceptar `labelKey` y `label` (fallback) como props
- Usar `t()` internamente para obtener el texto traducido
- Nunca exponer texto hardcodeado

---

## Componentes Disponibles

### Button

```typescript
import { Button } from '@/shared/ui';

<Button
  testId="auth.login.submitButton"
  labelKey="auth.login.submit"
  label="Iniciar Sesión"
  variant="primary"
  size="medium"
  onClick={handleSubmit}
/>
```

**Props:**
- `testId` (obligatorio): Test ID para E2E
- `labelKey`: Key de traducción
- `label`: Fallback del texto
- `variant`: 'primary' | 'secondary' | 'danger' | 'ghost'
- `size`: 'small' | 'medium' | 'large'
- `fullWidth`: boolean
- `disabled`: boolean
- Props estándar de `<button>`

### TextField

```typescript
import { TextField } from '@/shared/ui';

<TextField
  testId="tasks.entry.dateInput"
  labelKey="tasks.entry.dateLabel"
  label="Fecha"
  type="date"
  required
  errorKey="tasks.entry.dateError"
  error="La fecha es obligatoria"
/>
```

**Props:**
- `testId` (obligatorio): Test ID para E2E
- `labelKey`: Key de traducción para el label
- `label`: Fallback del label
- `errorKey`: Key de traducción para el error
- `error`: Fallback del mensaje de error
- `required`: boolean
- `size`: 'small' | 'medium' | 'large'
- `fullWidth`: boolean
- Props estándar de `<input>`

### DataTable

```typescript
import { DataTable } from '@/shared/ui';

<DataTable
  testId="tasks.table"
  data={tasks}
  columns={[
    {
      key: 'fecha',
      headerKey: 'tasks.table.headers.date',
      header: 'Fecha',
      render: (row) => row.fecha
    }
  ]}
  emptyMessageKey="tasks.table.empty"
  emptyMessage="No hay tareas"
  rowTestId="tasks.table.row"
/>
```

**Props:**
- `testId` (obligatorio): Test ID para E2E
- `data`: Array de datos
- `columns`: Array de definiciones de columnas
- `emptyMessageKey`: Key de traducción para estado vacío
- `emptyMessage`: Fallback del mensaje vacío
- `loading`: boolean
- `rowTestId`: Test ID base para filas

### Modal

```typescript
import { Modal } from '@/shared/ui';

<Modal
  testId="tasks.entry.confirmModal"
  isOpen={isOpen}
  onClose={handleClose}
  titleKey="tasks.entry.confirmTitle"
  title="Confirmar acción"
  size="medium"
>
  {content}
</Modal>
```

**Props:**
- `testId` (obligatorio): Test ID para E2E
- `isOpen`: boolean
- `onClose`: función
- `titleKey`: Key de traducción para el título
- `title`: Fallback del título
- `size`: 'small' | 'medium' | 'large' | 'full'
- `closeOnOverlayClick`: boolean
- `closeOnEsc`: boolean

---

## Separación de Responsabilidades

Cada componente wrapper mantiene separación clara:

1. **Estructura (JSX):** En `ComponentName.tsx`
2. **Estilos (CSS):** En `ComponentName.module.css`
3. **Lógica (TypeScript):** En `ComponentName.tsx` (secciones claramente delimitadas)

**NO usar:**
- Estilos inline (excepto casos muy específicos)
- Librerías UI externas directamente
- Textos hardcodeados sin `t()`

---

## Accesibilidad

Todos los wrappers deben cumplir con accesibilidad:

- `data-testid` en todos los controles
- `aria-label` para elementos sin texto visible
- `aria-required` para campos obligatorios
- `aria-invalid` para campos con error
- `aria-describedby` para asociar errores/helpers
- `role="alert"` para mensajes de error
- `aria-live` para actualizaciones dinámicas
- Navegación por teclado funcional

---

## Uso en Features

```typescript
// ✅ CORRECTO - Importar desde shared/ui
import { Button, TextField } from '@/shared/ui';

function LoginForm() {
  return (
    <form>
      <TextField
        testId="auth.login.usuarioInput"
        labelKey="auth.login.usuarioLabel"
        label="Código de Usuario"
      />
      <Button
        testId="auth.login.submitButton"
        labelKey="auth.login.submit"
        label="Iniciar Sesión"
      />
    </form>
  );
}
```

```typescript
// ❌ INCORRECTO - Importar librería externa directamente
import { Button } from 'some-ui-library';

function LoginForm() {
  return <Button>Iniciar Sesión</Button>;
}
```

---

## Extensión

Para agregar un nuevo componente wrapper:

1. Crear carpeta `src/shared/ui/<ComponentName>/`
2. Crear `ComponentName.tsx` con:
   - `testId` obligatorio
   - Props para `labelKey`/`label` (o equivalentes)
   - Uso de `t()` para textos
   - Accesibilidad completa
3. Crear `ComponentName.module.css` con estilos
4. Crear `index.ts` para exportación
5. Exportar en `src/shared/ui/index.ts`

---

## Referencias

- `.cursor/rules/10-i18n-and-testid.md` - Reglas de i18n y test-ids
- `.cursor/rules/07-frontend-norms.md` - Normas de frontend
- `docs/frontend/frontend-specifications.md` - Especificaciones generales

---

**Última actualización:** 2025-01-20

