# Documentación: 22-frontend-build-typescript.md

## Ubicación
`.cursor/rules/22-frontend-build-typescript.md`

## Propósito
Regla de Cursor que establece requisitos y buenas prácticas para que el build del frontend (TypeScript + Vite) pase correctamente en entornos de deploy como Vercel o AWS.

## Contenido resumido

1. **Tipos Vite e ImportMeta.env** - Archivo `vite-env.d.ts` obligatorio para `import.meta.env` y módulos CSS.
2. **Módulos CSS** - Declaración de `*.module.css` para TypeScript.
3. **Exclusión de tests** - Configuración en `tsconfig.json` para excluir archivos de test del build.
4. **Imports y variables no usadas** - Evitar TS6133 eliminando imports/variables innecesarios.
5. **Tipado de headers en fetch** - Usar `Record<string, string>` en lugar de `HeadersInit` cuando se asignan propiedades dinámicamente.
6. **Comparaciones de tipos** - No comparar `number` con `string`; usar `!= null` para filtrar null/undefined.
7. **Componentes reutilizables** - Props opcionales con valores por defecto cuando un componente se usa en múltiples contextos.
8. **Tests E2E y tipado DOM** - Cast explícito a `HTMLFormElement`, `HTMLInputElement`, etc. en `page.evaluate()`.
9. **Checklist pre-deploy** - Verificación con `npm run build` antes de push o deploy.

## Cuándo consultar
- Antes de hacer deploy del frontend
- Al agregar nuevos servicios con `fetch`
- Al reutilizar componentes en nuevas páginas
- Al modificar parámetros numéricos en servicios API
- Cuando el build falla con errores TS2339, TS6133, TS7053, TS2367, etc.

## Relación con otros documentos
- **07-frontend-norms.md** - Referencia cruzada; sección "Build y Deploy"
- **docs/frontend/frontend-specifications.md** - Referencia en sección Referencias
- **docs/ia-log.md** - Origen: correcciones de deploy 2026-02-11
