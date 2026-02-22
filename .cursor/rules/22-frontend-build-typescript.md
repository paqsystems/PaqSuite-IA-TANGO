---
alwaysApply: true
---
# Regla: Frontend Build y TypeScript - Compatibilidad con Deploy

## Contexto

El frontend se despliega en Vercel u otros entornos (AWS, etc.) que ejecutan `npm run build` (tsc + vite build). Cualquier error de TypeScript impide el deploy. Esta regla documenta requisitos y buenas prácticas para garantizar que el build siempre pase.

**Origen:** Errores recurrentes durante deploy (ver `lidr - frontend.txt`). La rama `main` es la que despliega Vercel; todo merge a main debe tener build exitoso.

---

## Tabla Rápida: Errores Comunes en Deploy y Solución

| Error | Archivo típico | Solución |
|-------|----------------|----------|
| `Property 'env' does not exist on type 'ImportMeta'` | `auth.service.ts`, `*.service.ts` | Crear/mantener `src/vite-env.d.ts` con `/// <reference types="vite/client" />` |
| `Cannot find module './X.module.css'` | `Button.tsx`, `DataTable.tsx`, etc. | Declarar `*.module.css` en `vite-env.d.ts` |
| `'getToken' is declared but its value is never read` | `*.service.test.ts` | Excluir `**/*.test.ts` en `tsconfig.json` |
| `'ERROR_TIENE_TAREAS' is declared but its value is never read` | `EmpleadosPage.tsx` | Eliminar import no usado |
| `Property 'Authorization' does not exist on type 'HeadersInit'` | `empleado.service.ts` | Usar `Record<string, string>` para headers |
| `Cannot find name 'hasData'` / `'handleExportExcel'` | `TareasPorFechaPage.tsx` | Definir variables antes de usarlas (no eliminar por error) |
| `All imports in import declaration are unused` | `TareasPorFechaPage.tsx` | Usar o eliminar el import |
| `'getTasks' / 'key' is declared but its value is never read` | `task.service.test.ts` | Excluir tests del build |
| `types 'number' and 'string' have no overlap` | `task.service.ts` | Usar `!= null` en lugar de `!== ''` para IDs |
| `Property 'testIdPrefix' does not exist` | `TiposClientePage.tsx` | Agregar `testIdPrefix?: string` a `TaskPaginationProps` |
| `'FECHA_DESDE' / 'FECHA_HASTA' is declared but never read` | `tareas-por-cliente.spec.ts` | Excluir `**/*.spec.ts` o usar/eliminar variables |
| `Property 'checkValidity' does not exist on type 'Element'` | `task-create.spec.ts` | Cast: `form as HTMLFormElement \| null` |

---

## 1. Tipos Vite e ImportMeta.env

### Problema
`Property 'env' does not exist on type 'ImportMeta'` - TypeScript no reconoce `import.meta.env` sin las definiciones de Vite.

### Solución obligatoria
Mantener el archivo `frontend/src/vite-env.d.ts` con:

```typescript
/// <reference types="vite/client" />

declare module '*.module.css' {
  const classes: { [key: string]: string };
  export default classes;
}
```

### Reglas
- **NO** eliminar `vite-env.d.ts`
- **NO** usar `process.env` en el frontend; usar `import.meta.env.VITE_*`
- Variables de entorno públicas: prefijo `VITE_` (ej: `VITE_API_URL`)

---

## 2. Módulos CSS (*.module.css)

### Problema
`Cannot find module './Button.module.css' or its corresponding type declarations` - TypeScript no sabe manejar imports de CSS modules.

### Solución
La declaración en `vite-env.d.ts` (punto 1) cubre esto. Si se agregan nuevos tipos de archivo (ej: `*.scss`), declararlos en ese archivo.

### Reglas
- Los archivos `.module.css` **deben** existir junto al componente
- Import: `import styles from './Component.module.css'`
- NO usar rutas incorrectas o nombres que no coincidan

---

## 3. Exclusión de Tests en el Build

### Problema
Los archivos `*.test.ts`, `*.test.tsx`, `*.spec.ts` pueden tener variables no usadas, mocks o imports que provocan TS6133 durante `tsc`.

### Solución obligatoria
En `frontend/tsconfig.json`:

```json
{
  "exclude": ["**/*.test.ts", "**/*.test.tsx", "**/*.spec.ts", "**/*.spec.tsx"]
}
```

### Reglas
- Los tests **NO** deben incluirse en el chequedo de tipos del build de producción
- Los tests se ejecutan con Vitest/Playwright que tienen su propia configuración
- Mantener `exclude` actualizado si se agregan nuevos patrones de archivos de test

---

## 4. Imports y Variables No Usadas

### Problema
`'X' is declared but its value is never read` (TS6133) - Imports o variables declaradas pero no utilizadas.

### Reglas obligatorias
- **Eliminar** imports que no se usan (ej: `useLocation` si no se usa)
- **Eliminar** variables que no se usan o prefijarlas con `_` si son intencionales (ej: `_key` en callbacks)
- **No** importar constantes como `ERROR_TIENE_TAREAS` si solo se usan en el servicio, no en el componente
- Revisar con `npm run build` antes de hacer push/deploy

### Ejemplo incorrecto
```typescript
import { NavLink, useLocation } from 'react-router-dom';  // useLocation no usado
```

### Ejemplo correcto
```typescript
import { NavLink } from 'react-router-dom';
```

---

## 5. Tipado de Headers en Fetch

### Problema
`Property 'Authorization' does not exist on type 'HeadersInit'` - `HeadersInit` puede ser `Headers | string[][] | Record<string, string>`, y asignar propiedades dinámicamente falla.

### Solución
Usar `Record<string, string>` cuando se construyan headers dinámicamente:

```typescript
const headers: Record<string, string> = {
  'Content-Type': 'application/json',
  ...(options.headers && typeof options.headers === 'object' && !Array.isArray(options.headers)
    ? (options.headers as Record<string, string>)
    : {}),
};
if (token) {
  headers['Authorization'] = `Bearer ${token}`;
}
```

### Reglas
- **NO** usar `HeadersInit` cuando se vaya a asignar `headers['Authorization']` u otras propiedades dinámicamente
- **SÍ** usar `Record<string, string>` para objetos de headers construidos en código
- Cuidado al hacer spread de `options.headers` si puede ser `undefined` o `Headers`

---

## 6. Comparaciones de Tipos (Number vs String)

### Problema
`This comparison appears to be unintentional because the types 'number' and 'string' have no overlap` - Comparar `number | null` con `''` (string) es incorrecto.

### Reglas
- Parámetros de API como `cliente_id`, `tipo_tarea_id`, `usuario_id` suelen ser `number | null | undefined`
- **NO** comparar con `!== ''` cuando el tipo es numérico
- **SÍ** usar `!= null` para filtrar `null` y `undefined`:

```typescript
// Incorrecto
if (params.cliente_id != null && params.cliente_id !== '' && params.cliente_id !== undefined)

// Correcto
if (params.cliente_id != null)
```

---

## 7. Componentes Reutilizables - Props Opcionales

### Problema
`Property 'testIdPrefix' does not exist on type 'TaskPaginationProps'` - Un componente reutilizado en distintos contextos requiere props que no existen en la interfaz.

### Reglas
- Al reutilizar un componente (ej: `TaskPagination`) en páginas diferentes:
  - Si una página pasa props nuevas (ej: `testIdPrefix`), **agregarlas a la interfaz** como opcionales
  - Propiedades que pueden faltar: definir **valores por defecto** en la desestructuración
  - Documentar qué props son obligatorias y cuáles opcionales

### Ejemplo
```typescript
export interface TaskPaginationProps {
  currentPage: number;
  lastPage: number;
  total?: number;           // opcional con default
  perPage?: number;         // opcional con default
  onPageChange: (page: number) => void;
  testIdPrefix?: string;    // nueva prop opcional
}
```

---

## 8. Tests E2E - Tipado DOM en page.evaluate

### Problema
`Property 'checkValidity' does not exist on type 'Element'` - `document.querySelector` retorna `Element | null`, pero `checkValidity()` existe solo en `HTMLFormElement`.

### Solución
Hacer cast explícito al tipo correcto:

```typescript
const form = document.querySelector('form') as HTMLFormElement | null;
return form ? form.checkValidity() : false;
```

### Reglas
- Dentro de `page.evaluate()`, los métodos específicos de elementos (checkValidity, value, etc.) requieren cast al tipo correcto
- Usar `as HTMLFormElement`, `as HTMLInputElement`, etc. cuando se sepa el tipo real

---

## 9. Verificación Pre-Deploy

### Checklist obligatorio antes de push/deploy

1. **Ejecutar** `npm run build` en `frontend/` y **confirmar que pasa** sin errores
2. Revisar que no queden imports no usados
3. Si se agregan nuevos servicios con `fetch`, verificar tipado de headers
4. Si se reutilizan componentes, verificar que las props sean compatibles en todos los usos
5. Si se modifican parámetros de API (number, string), verificar comparaciones en los servicios
6. **No hacer merge a `main`** hasta que el build pase en local (Vercel despliega desde `main`)

### Comando
```bash
cd frontend && npm run build
```

---

## 10. TareasPorFechaPage: hasData y handleExportExcel

### Problema
`Cannot find name 'hasData'` / `Cannot find name 'handleExportExcel'` - Variables referenciadas en el JSX pero indefinidas o eliminadas por error.

### Reglas
- `hasData` y `handleExportExcel` (u otras variables similares para exportar) deben definirse en el componente **antes** del `return`
- **NO** eliminar estas definiciones al refactorizar; son necesarias para el botón de exportar
- Si se usa `exportGroupedToExcel`, el import de `buildExportFileName`, `exportGroupedToExcel` y `GroupedExportGroup` debe mantenerse

### Ejemplo
```typescript
const hasData = grupos.length > 0;
const handleExportExcel = () => { /* ... */ };

return (
  // ...
  {!hasData && !loading && <span>No hay datos</span>}
  <button onClick={handleExportExcel} disabled={!hasData || loading}>Exportar</button>
);
```

---

## Referencias

- `docs/frontend/frontend-specifications.md` - Especificaciones generales
- `.cursor/rules/07-frontend-norms.md` - Normas de frontend
- `docs/ia-log.md` - Entrada "Migración SQL Server → MySQL" y correcciones de deploy
- Vite env variables: https://vitejs.dev/guide/env-and-mode.html

---

## Historial

| Fecha     | Cambio |
|-----------|--------|
| 2026-02-11 | Creación de la regla tras corrección de errores de deploy en Vercel |
| 2026-02-11 | Recurrente: mismos errores en nuevo deploy. Añadida tabla de errores comunes y sección 10 (TareasPorFechaPage). Reforzada regla 07 con aviso obligatorio. |
